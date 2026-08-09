<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * Asisten AI untuk panel admin - READ ONLY.
 *
 * PENTING:
 * - AI TIDAK PERNAH menghitung angka sendiri. Semua omzet, jumlah pesanan,
 *   stok, dsb. dihitung di PHP lalu disuapkan sebagai fakta. AI hanya
 *   menerjemahkan/menjelaskan/memberi saran naratif.
 * - Data harga modal (cost_price) dan margin SENGAJA TIDAK DIKIRIM ke Groq
 *   sesuai keputusan pemilik toko.
 * - AI tidak diberi kemampuan mengubah data (tidak ada tool call, tidak ada
 *   endpoint mutasi). Kalau admin minta "ubah stok X", AI hanya boleh
 *   menyarankan buka menu Produk secara manual.
 */
class AiAssistantController extends Controller
{
    public function chat(Request $request)
    {
        $request->validate(['message' => 'required|string|max:500']);

        $apiKey = config('services.groq.api_key');
        if (!$apiKey) {
            return response()->json(['reply' => 'Asisten AI belum dikonfigurasi (GROQ_API_KEY kosong).'], 503);
        }

        $context = $this->gatherContext();
        $systemPrompt = $this->buildSystemPrompt($context);

        $history = $request->input('history', []);
        $apiMessages = [['role' => 'system', 'content' => $systemPrompt]];
        foreach (array_slice($history, -6) as $h) {
            $apiMessages[] = [
                'role'    => ($h['from'] ?? '') === 'user' ? 'user' : 'assistant',
                'content' => strip_tags($h['text'] ?? ''),
            ];
        }
        $apiMessages[] = ['role' => 'user', 'content' => $request->input('message')];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type'  => 'application/json',
            ])
            ->withOptions(['curl' => [CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]])
            ->timeout(20)
            ->post('https://api.groq.com/openai/v1/chat/completions', [
                'model'       => 'llama-3.3-70b-versatile',
                'messages'    => $apiMessages,
                'temperature' => 0.4,
                'max_tokens'  => 700,
            ]);

            if (!$response->successful()) {
                return response()->json(['reply' => 'Maaf, koneksi ke AI bermasalah. Coba lagi sebentar.'], 500);
            }

            $reply = $response->json('choices.0.message.content', 'Maaf, tidak bisa menjawab saat ini.');

            return response()->json([
                'reply'   => $reply,
                'context' => $context,
            ]);
        } catch (\Exception $e) {
            return response()->json(['reply' => 'Koneksi ke AI terputus. Coba lagi nanti.'], 500);
        }
    }

    /**
     * Kumpulkan seluruh angka fakta dari database. Ini yang akan disuapkan
     * ke AI sebagai konteks - AI TIDAK menghitung apa-apa sendiri.
     */
    private function gatherContext(): array
    {
        $today       = now()->toDateString();
        $sevenDays   = now()->subDays(7);
        $thirtyDays  = now()->subDays(30);

        $omzetHariIni     = (float) Order::where('status', 'selesai')->whereDate('created_at', $today)->sum('total');
        $omzet7Hari       = (float) Order::where('status', 'selesai')->where('created_at', '>=', $sevenDays)->sum('total');
        $omzet30Hari      = (float) Order::where('status', 'selesai')->where('created_at', '>=', $thirtyDays)->sum('total');

        $pesananHariIni   = Order::whereDate('created_at', $today)->count();
        $pesananDiproses  = Order::where('status', 'diproses')->count();
        $pesananMenunggu  = Order::where('status', 'menunggu_pembayaran')->count();

        // Stok kritis: di bawah atau sama dengan min_stock_alert (aktif saja)
        $stokKritis = Product::where('is_active', true)
            ->whereColumn('stock', '<=', 'min_stock_alert')
            ->where('stock', '>', 0)
            ->orderBy('stock')
            ->limit(15)
            ->get(['id', 'name', 'stock', 'min_stock_alert', 'unit', 'sold_count'])
            ->map(fn ($p) => [
                'nama'      => $p->name,
                'stok'      => $p->stock,
                'min_alert' => $p->min_stock_alert,
                'unit'      => $p->unit,
                'terjual'   => (int) $p->sold_count,
            ])->toArray();

        $stokHabis = Product::where('is_active', true)->where('stock', 0)
            ->orderByDesc('sold_count')
            ->limit(15)
            ->get(['id', 'name', 'unit', 'sold_count'])
            ->map(fn ($p) => [
                'nama'    => $p->name,
                'unit'    => $p->unit,
                'terjual' => (int) $p->sold_count,
            ])->toArray();

        // Hitung kesegaran per produk. Kita TIDAK pakai accessor freshness_remaining
        // yang membatasi ke max(0, ...) karena admin perlu tahu SEBERAPA LAMA sudah
        // lewat tanggal segar - "sudah lewat 5 hari" ≠ "sudah lewat 1 hari".
        $withFreshness = Product::where('is_active', true)
            ->whereNotNull('stock_in_date')->whereNotNull('freshness_days')
            ->where('stock', '>', 0)
            ->get(['id', 'name', 'stock', 'unit', 'freshness_days', 'stock_in_date'])
            ->map(function ($p) {
                $sudahHari = (int) round(abs(now()->diffInDays($p->stock_in_date)));
                $sisa      = $p->freshness_days - $sudahHari; // NEGATIF = kadaluarsa
                return [
                    'nama'       => $p->name,
                    'stok'       => $p->stock,
                    'unit'       => $p->unit,
                    'masuk_stok' => $p->stock_in_date->format('Y-m-d'),
                    'daya_tahan' => $p->freshness_days,
                    'sudah_hari' => $sudahHari,
                    'sisa_hari'  => $sisa,
                ];
            });

        $kesegaranSegar = $withFreshness->filter(fn ($p) => $p['sisa_hari'] >= 3)
            ->sortBy('sisa_hari')->values()->toArray();

        $kesegaranMenipis = $withFreshness->filter(fn ($p) => $p['sisa_hari'] >= 1 && $p['sisa_hari'] <= 2)
            ->sortBy('sisa_hari')->values()->toArray();

        $kesegaranKadaluarsa = $withFreshness->filter(fn ($p) => $p['sisa_hari'] <= 0)
            ->sortBy('sisa_hari')->values()->toArray();

        // Produk aktif dengan stok tapi TIDAK punya data kesegaran - admin perlu diingatkan
        $tanpaDataKesegaran = Product::where('is_active', true)
            ->where('stock', '>', 0)
            ->where(fn ($q) => $q->whereNull('stock_in_date')->orWhereNull('freshness_days'))
            ->pluck('name')->toArray();

        // Produk terlaris - kandidat kuat untuk restock kalau stoknya menipis
        $terlaris = Product::where('is_active', true)
            ->orderByDesc('sold_count')
            ->limit(10)
            ->get(['id', 'name', 'stock', 'sold_count', 'unit'])
            ->map(fn ($p) => [
                'nama'    => $p->name,
                'stok'    => $p->stock,
                'unit'    => $p->unit,
                'terjual' => (int) $p->sold_count,
            ])->toArray();

        return [
            'tanggal'               => $today,
            'omzet_hari_ini'        => $omzetHariIni,
            'omzet_7_hari'          => $omzet7Hari,
            'omzet_30_hari'         => $omzet30Hari,
            'pesanan_hari_ini'      => $pesananHariIni,
            'pesanan_diproses'      => $pesananDiproses,
            'pesanan_menunggu'      => $pesananMenunggu,
            'stok_kritis'           => $stokKritis,
            'stok_habis'            => $stokHabis,
            'kesegaran_segar'       => $kesegaranSegar,
            'kesegaran_menipis'     => $kesegaranMenipis,
            'kesegaran_kadaluarsa'  => $kesegaranKadaluarsa,
            'tanpa_data_kesegaran'  => $tanpaDataKesegaran,
            'produk_terlaris'       => $terlaris,
        ];
    }

    private function buildSystemPrompt(array $ctx): string
    {
        $fmt = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');

        $stokKritisText = empty($ctx['stok_kritis']) ? '(tidak ada produk stok kritis)'
            : collect($ctx['stok_kritis'])->map(fn ($p) =>
                "- {$p['nama']}: stok {$p['stok']} {$p['unit']} (min alert {$p['min_alert']}), sudah terjual {$p['terjual']}x"
            )->implode("\n");

        $stokHabisText = empty($ctx['stok_habis']) ? '(tidak ada produk habis)'
            : collect($ctx['stok_habis'])->map(fn ($p) =>
                "- {$p['nama']} ({$p['unit']}), sudah terjual {$p['terjual']}x"
            )->implode("\n");

        $segarText = empty($ctx['kesegaran_segar']) ? '(tidak ada produk dengan kesegaran > 2 hari)'
            : collect($ctx['kesegaran_segar'])->map(fn ($p) =>
                "- {$p['nama']}: sisa {$p['sisa_hari']} hari lagi, stok {$p['stok']} {$p['unit']} (masuk {$p['masuk_stok']}, daya tahan {$p['daya_tahan']}d)"
            )->implode("\n");

        $menipisText = empty($ctx['kesegaran_menipis']) ? '(tidak ada produk yang kesegarannya menipis)'
            : collect($ctx['kesegaran_menipis'])->map(fn ($p) =>
                "- {$p['nama']}: sisa {$p['sisa_hari']} hari, stok {$p['stok']} {$p['unit']}"
            )->implode("\n");

        $kadaluarsaText = empty($ctx['kesegaran_kadaluarsa']) ? '(tidak ada produk yang sudah lewat masa segar)'
            : collect($ctx['kesegaran_kadaluarsa'])->map(function ($p) {
                $lewat = abs($p['sisa_hari']);
                $ket = $p['sisa_hari'] == 0 ? 'HABIS masa segarnya hari ini' : "sudah lewat {$lewat} hari";
                return "- {$p['nama']}: {$ket}, stok {$p['stok']} {$p['unit']} (masuk {$p['masuk_stok']}, daya tahan cuma {$p['daya_tahan']}d)";
            })->implode("\n");

        $tanpaDataText = empty($ctx['tanpa_data_kesegaran']) ? '(semua produk aktif sudah punya data kesegaran)'
            : '- ' . implode("\n- ", $ctx['tanpa_data_kesegaran']);

        $terlarisText = collect($ctx['produk_terlaris'])->map(fn ($p) =>
            "- {$p['nama']}: terjual {$p['terjual']}x, stok saat ini {$p['stok']} {$p['unit']}"
        )->implode("\n");

        return <<<PROMPT
Kamu adalah "Asisten Admin IPUL BUAH" - AI yang membantu admin toko buah IPUL BUAH di Palu.

=== ATURAN MUTLAK ===
1. JANGAN PERNAH mengarang angka. Semua angka omzet, stok, jumlah pesanan HANYA boleh berasal dari data di bawah.
2. Kalau admin bertanya sesuatu yang datanya tidak ada di bawah, katakan jujur "data itu belum saya punya, silakan cek menu Laporan".
3. JANGAN bahas soal harga modal (cost_price), margin, atau keuntungan - data itu sengaja tidak diberikan kepadamu.
4. Kalau admin minta MENGUBAH data (ubah stok, hapus produk, dll), jawab bahwa kamu tidak bisa mengubah data dan arahkan ke menu yang sesuai (Produk, Pesanan, dsb).
5. Untuk musim buah: kamu boleh pakai pengetahuan umum, tapi WAJIB beri disclaimer "(perkiraan umum, bukan dari data IPUL BUAH)".
6. Jawab dalam Bahasa Indonesia yang ramah, ringkas, dan langsung ke poin. Maksimal 4 paragraf pendek.
7. Gunakan bullet point untuk daftar. Format angka pakai titik pemisah (Rp 1.500.000, bukan Rp 1500000).
8. Tugas utamamu: (a) menjawab pertanyaan omzet/stok, (b) memberi SARAN buah apa yang perlu di-restock, (c) memperingatkan produk yang perlu didiskon karena kesegarannya menipis.

=== DATA FAKTUAL HARI INI ({$ctx['tanggal']}) ===

PENJUALAN:
- Omzet hari ini: {$fmt($ctx['omzet_hari_ini'])}
- Omzet 7 hari terakhir: {$fmt($ctx['omzet_7_hari'])}
- Omzet 30 hari terakhir: {$fmt($ctx['omzet_30_hari'])}
- Jumlah pesanan hari ini: {$ctx['pesanan_hari_ini']}
- Pesanan sedang diproses: {$ctx['pesanan_diproses']}
- Pesanan menunggu pembayaran: {$ctx['pesanan_menunggu']}

STOK KRITIS (di bawah minimum, tapi belum habis):
{$stokKritisText}

STOK HABIS (perlu restock segera, terutama yang penjualannya tinggi):
{$stokHabisText}

=== DATA KESEGARAN BUAH ===
Setiap buah punya "daya_tahan" (berapa hari maksimal setelah masuk stok) dan tanggal "masuk_stok".
Sisa hari = daya_tahan - jumlah hari sejak masuk stok. Bisa negatif kalau sudah lewat masa segar.

🟢 BUAH MASIH SEGAR (sisa ≥ 3 hari, aman dijual normal):
{$segarText}

🟡 KESEGARAN MENIPIS (sisa 1-2 hari, sarankan didiskon / prioritaskan jual hari ini):
{$menipisText}

🔴 SUDAH LEWAT MASA SEGAR (WAJIB DITARIK DARI ETALASE - jangan dijual):
{$kadaluarsaText}

⚠️ PRODUK AKTIF TANPA DATA KESEGARAN (admin lupa isi tanggal masuk / daya tahan):
{$tanpaDataText}

10 PRODUK PALING LARIS SEPANJANG WAKTU:
{$terlarisText}

=== CARA MENJAWAB PERTANYAAN KESEGARAN ===
- "Buah apa yang masih segar?" → Sebutkan dari daftar 🟢 BUAH MASIH SEGAR, urutkan dari sisa terlama.
- "Buah apa yang harus ditarik / sudah tidak layak jual?" → Sebutkan SEMUA dari daftar 🔴 SUDAH LEWAT MASA SEGAR, tegas bilang JANGAN DIJUAL, sarankan segera hapus dari etalase atau ganti stok baru.
- "Buah apa yang perlu didiskon?" → Sebutkan dari daftar 🟡 KESEGARAN MENIPIS.
- Kalau admin tanya kesegaran buah spesifik yang tidak ada di daftar, katakan "produk itu belum ada data tanggal masuk / daya tahannya" dan sebutkan dia ada di daftar tanpa data.
- JANGAN pernah katakan buah masih segar kalau dia ada di daftar 🔴, dan sebaliknya.

=== SARAN RESTOCK ===
Kalau admin tanya "buah apa yang perlu di-restock", prioritaskan:
1. Produk di daftar STOK HABIS yang jumlah terjualnya tinggi (paling mendesak).
2. Produk di daftar STOK KRITIS yang juga masuk daftar produk terlaris.
3. Produk di daftar 🔴 SUDAH LEWAT MASA SEGAR - stok yang ada perlu dibuang dan diganti stok baru.

Jangan asal rekomendasi produk yang tidak masuk daftar di atas.
PROMPT;
    }
}
