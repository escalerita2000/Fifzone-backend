<?php
namespace App\Exports;

use App\Models\Venta;
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
        return Venta::with(['items.producto', 'usuario'])->latest('fecha')->get()->map(fn($s) => [
            'ID'       => $s->id_venta,
            'Fecha'    => $s->fecha ? $s->fecha->format('d/m/Y H:i') : '',
            'Cliente'  => $s->customer ?? $s->usuario?->nombre ?? 'Cliente General',
            'Tipo'     => $s->canal === 'web' ? 'Externo' : 'Interno',
            'Items'    => $s->items->map(fn($i) => ($i->producto?->nombre ?? 'Producto Eliminado') . " x{$i->cantidad}")->implode(', '),
            'Total'    => (float) $s->total,
            'Ganancia' => (float) ($s->total - $s->items->sum(fn($i) => ($i->producto?->precio_costo ?? ($i->precio_unitario * 0.65)) * $i->cantidad)),
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
        if ($last > 1) {
            $sheet->getStyle("F2:G{$last}")->getNumberFormat()->setFormatCode('$#,##0');
        }
        return [];
    }
}