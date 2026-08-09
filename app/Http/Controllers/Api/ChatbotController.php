<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ChatbotController extends Controller
{
    public function chat(Request $request)
    {
        $request->validate(['message' => 'required|string|max:500']);

        $apiKey = config('services.groq.api_key');
        if (!$apiKey) {
            return response()->json(['reply' => 'Chatbot AI belum dikonfigurasi.'], 503);
        }

        // Catatan: JANGAN pakai ->limit(1) di dalam with() - pada eager loading
        // limit berlaku untuk satu query gabungan (WHERE product_id IN ...),
        // sehingga hanya 1 produk saja yang kebagian gambar. Urutkan is_primary
        // dulu lalu ambil first() per produk saat memetakan hasilnya.
        $products = Product::where('is_active', true)
            ->with(['images' => fn ($q) => $q->orderByDesc('is_primary')->orderBy('sort_order')])
            ->get(['id', 'name', 'slug', 'unit', 'price_unit', 'labels', 'freshness_days', 'stock_in_date', 'storage_tips']);

        $catalog = $products->map(fn ($p) => [
            'nama'      => $p->name,
            'slug'      => $p->slug,
            'harga'     => 'Rp ' . number_format($p->price_unit, 0, ',', '.') . '/' . $p->unit,
            'label'     => implode(', ', $p->labels ?? []),
            'segar'     => $p->freshness_remaining ? round($p->freshness_remaining) . ' hari' : '-',
            'tips'      => $p->storage_tips ?? '-',
        ])->toArray();

        $catalogText = collect($catalog)->map(fn ($c) =>
            "- {$c['nama']} | {$c['harga']} | Label: {$c['label']} | Segar: {$c['segar']} | Penyimpanan: {$c['tips']}"
        )->implode("\n");

        $systemPrompt = <<<PROMPT
Kamu adalah "Asisten Buah IPUL BUAH" — chatbot pintar untuk toko buah IPUL BUAH di Palu, Sulawesi Tengah.

ATURAN UTAMA:
1. Jawab dalam bahasa Indonesia yang ramah, santai, dan informatif.
2. Gunakan emoji secukupnya untuk membuat percakapan hangat.
3. Selalu rekomendasikan produk dari katalog IPUL BUAH jika relevan.
4. Kamu ahli soal buah: jenis, manfaat, olahan (jus, rujak, smoothie, salad, kolak, selai, dll), cara simpan, dan nutrisi.
5. Kalau ditanya di luar topik buah, tetap jawab senatural mungkin tapi arahkan kembali ke buah/toko.
6. Jangan pernah bilang kamu AI/robot — kamu adalah "Asisten Buah IPUL BUAH".
7. Jika merekomendasikan produk, WAJIB sertakan format [PRODUK:slug] untuk setiap produk agar sistem bisa menampilkan card produk.
   Contoh: "Saya rekomendasikan [PRODUK:mangga-harum-manis] untuk jus yang manis!"
8. Jawab ringkas, maksimal 3 paragraf pendek. Jangan terlalu panjang.

INFO TOKO:
- Nama: IPUL BUAH
- Alamat: Jl. Kemiri No. 47, Siranindi, Palu Barat, Kota Palu
- Jam: 08:00 - 17:00 WITA
- WhatsApp: +62 852-4418-9949
- Fitur: Bisa pesan online, QRIS, parsel kustom, pengiriman area Palu-Sigi-Donggala

KATALOG PRODUK SAAT INI:
{$catalogText}

PENGETAHUAN OLAHAN BUAH:
- Jus: mangga, jeruk, semangka, melon, alpukat, pepaya, nanas, apel
- Rujak: mangga manalagi (muda), nanas, pepaya muda, kedondong
- Smoothie: alpukat+coklat, mangga+yogurt, pisang+strawberry, kiwi+pisang
- Kolak: pisang kepok, nangka, kolang-kaling
- Goreng: pisang kepok (goreng crispy, nugget pisang)
- Salad buah: apel, anggur, kiwi, melon, semangka + mayones/yogurt
- Es buah/sop buah: melon, semangka, pepaya, nanas, alpukat
- Selai: mangga, nanas, strawberry
- Infused water: jeruk, kiwi, lemon, mentimun
- Parsel premium: apel fuji, anggur, kiwi zespri, jeruk sunkist
PROMPT;

        $history = $request->input('history', []);

        $apiMessages = [['role' => 'system', 'content' => $systemPrompt]];

        foreach (array_slice($history, -6) as $h) {
            $apiMessages[] = [
                'role'    => $h['from'] === 'user' ? 'user' : 'assistant',
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
            ->timeout(15)
            ->post('https://api.groq.com/openai/v1/chat/completions', [
                'model'       => 'llama-3.3-70b-versatile',
                'messages'    => $apiMessages,
                'temperature' => 0.7,
                'max_tokens'  => 512,
            ]);

            if (!$response->successful()) {
                return response()->json(['reply' => 'Maaf, saya sedang sibuk. Coba lagi ya! 😅'], 500);
            }

            $reply = $response->json('choices.0.message.content', 'Maaf, saya tidak bisa menjawab saat ini.');

            $mentionedProducts = [];
            preg_match_all('/\[PRODUK:([a-z0-9\-]+)\]/', $reply, $matches);
            if (!empty($matches[1])) {
                $slugs = array_unique($matches[1]);
                $mentionedProducts = $products->whereIn('slug', $slugs)->map(fn ($p) => [
                    'name'  => $p->name,
                    'slug'  => $p->slug,
                    'price' => (float) $p->price_unit,
                    'unit'  => $p->unit,
                    'image' => $p->images->first()?->image_path,
                ])->values()->toArray();
            }

            $cleanReply = preg_replace('/\[PRODUK:[a-z0-9\-]+\]/', '', $reply);
            $cleanReply = trim(preg_replace('/\s{2,}/', ' ', $cleanReply));

            return response()->json([
                'reply'    => $cleanReply,
                'products' => $mentionedProducts,
            ]);
        } catch (\Exception $e) {
            return response()->json(['reply' => 'Koneksi ke AI terputus. Coba lagi nanti ya! 😅'], 500);
        }
    }
}
