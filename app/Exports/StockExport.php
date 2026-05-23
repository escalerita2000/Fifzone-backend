<?php
namespace App\Exports;

use App\Models\Producto;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class StockExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    public function collection()
    {
        return Producto::with('categoria')->where('activo', true)->get()->map(fn($p) => [
            'ID'       => $p->id_producto,
            'Producto' => $p->nombre,
            'Categoría'=> $p->categoria?->nombre ?? 'Sin Categoría',
            'Stock'    => $p->stock_actual,
            'Costo'    => (float) $p->precio_costo,
            'Interno'  => (float) ($p->precio_venta * 0.9), // precio interno estimado
            'Venta'    => (float) $p->precio_venta,
            'Estado'   => $p->stock_actual === 0 ? 'AGOTADO' : ($p->stock_actual <= 5 ? 'BAJO' : 'OK'),
            'Valor'    => (float) (($p->precio_costo ?? 0) * $p->stock_actual),
        ]);
    }

    public function headings(): array
    {
        return ['ID', 'Producto', 'Categoría', 'Stock', 'Costo', 'P.Interno', 'P.Venta', 'Estado', 'Valor Stock'];
    }

    public function title(): string { return 'Inventario'; }

    public function columnWidths(): array
    {
        return ['A' => 6, 'B' => 28, 'C' => 14, 'D' => 8, 'E' => 14, 'F' => 14, 'G' => 14, 'H' => 12, 'I' => 16];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:I1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FF4B63']],
        ]);
        $last = $sheet->getHighestRow();
        if ($last > 1) {
            $sheet->getStyle("E2:G{$last}")->getNumberFormat()->setFormatCode('$#,##0');
            $sheet->getStyle("I2:I{$last}")->getNumberFormat()->setFormatCode('$#,##0');
        }
        return [];
    }
}