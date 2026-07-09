<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SalesReportExport implements FromCollection, WithHeadings
{
    public function __construct(private ?string $from = null, private ?string $to = null) {}

    public function collection()
    {
        return Order::where('status', 'selesai')
            ->when($this->from, fn ($q) => $q->whereDate('created_at', '>=', $this->from))
            ->when($this->to, fn ($q) => $q->whereDate('created_at', '<=', $this->to))
            ->get(['order_number', 'created_at', 'fulfillment_type', 'subtotal', 'discount', 'shipping_cost', 'total']);
    }

    public function headings(): array
    {
        return ['No. Invoice', 'Tanggal', 'Tipe', 'Subtotal', 'Diskon', 'Ongkir', 'Total'];
    }
}
