<?php
namespace App\Exports;

set_time_limit(120);

use App\Models\Venta;
use App\Models\VentaItem;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SalesExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    private $ventaItems = [];
    private $filters = [];

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Venta::with(['usuario']);

        if (!empty($this->filters['anio'])) {
            $query->whereYear('fecha', $this->filters['anio']);
        }

        if (!empty($this->filters['mes'])) {
            $query->whereMonth('fecha', $this->filters['mes']);
        }

        if (!empty($this->filters['dia'])) {
            $query->whereDay('fecha', $this->filters['dia']);
        }

        if (!empty($this->filters['categoria']) || !empty($this->filters['marca'])) {
            $query->whereHas('ventaItems', function ($q) {
                $q->whereHas('producto', function ($pq) {
                    if (!empty($this->filters['categoria'])) {
                        $pq->whereHas('categoria', function ($cq) {
                            $cq->where('nombre', $this->filters['categoria']);
                        });
                    }
                    if (!empty($this->filters['marca'])) {
                        $pq->whereHas('marca', function ($mq) {
                            $mq->where('nombre', $this->filters['marca']);
                        });
                    }
                });
            }, '>=', 1);
        }

        $sales = $query->latest('fecha')->get();

        $ventaIds = $sales->pluck('id_venta')->toArray();
        $allItems = VentaItem::whereIn('id_venta', $ventaIds)
            ->with('producto')
            ->get()
            ->groupBy('id_venta');

        $this->ventaItems = $allItems;

        return $sales->map(fn($s) => [
            'ID'       => $s->id_venta,
            'Fecha'    => $s->fecha ? $s->fecha->format('d/m/Y H:i') : '',
            'Cliente'  => $s->usuario?->nombre ?? 'Cliente General',
            'Tipo'     => $s->canal === 'web' ? 'Externo' : 'Interno',
            'Items'    => $this->getItemsString($s->id_venta),
            'Total'    => (float) $s->total,
            'Ganancia' => (float) ($s->total - $this->getTotalCost($s->id_venta)),
        ]);
    }

    private function getItemsString($ventaId)
    {
        $items = $this->ventaItems[$ventaId] ?? [];
        return collect($items)->map(fn($i) => ($i->producto?->nombre ?? 'Producto Eliminado') . " x{$i->cantidad}")->implode(', ');
    }

    private function getTotalCost($ventaId)
    {
        $items = $this->ventaItems[$ventaId] ?? [];
        return collect($items)->sum(fn($i) => ($i->producto?->precio_costo ?? ($i->precio_unitario * 0.65)) * $i->cantidad);
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