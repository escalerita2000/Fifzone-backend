<?php
namespace App\Exports;

use App\Models\Sale;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SalesExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    public function collection()
    {
        return Sale::with('items')->latest()->get()->map(fn($s) => [
            'ID'       => $s->id,
            'Fecha'    => $s->created_at->format('d/m/Y H:i'),
            'Cliente'  => $s->customer,
            'Tipo'     => $s->type === 'external' ? 'Externo' : 'Interno',
            'Items'    => $s->items->map(fn($i) => "{$i->product_name} x{$i->quantity}")->implode(', '),
            'Total'    => (float) $s->total,
            'Ganancia' => (float) $s->profit,
        ]);
    }

    public function headings(): array
    {
        return ['ID', 'Fecha', 'Cliente', 'Tipo', 'Productos', 'Total (COP)', 'Ganancia (COP)'];
    }

    public function title(): string { return 'Ventas'; }

    public function columnWidths(): array
    {
        return ['A' => 6, 'B' => 20, 'C' => 22, 'D' => 12, 'E' => 45, 'F' => 16, 'G' => 16];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FF4B63']],
        ]);
        $last = $sheet->getHighestRow();
        $sheet->getStyle("F2:G{$last}")->getNumberFormat()->setFormatCode('$#,##0');
        return [];
    }
}