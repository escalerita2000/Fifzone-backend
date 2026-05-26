<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
  body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; }
  .header { background: #ff4b63; color: white; padding: 14px 20px; margin-bottom: 16px; }
  .header h1 { font-size: 18px; margin: 0; }
  .header p  { margin: 3px 0 0; font-size: 10px; opacity: .85; }
  table { width: 100%; border-collapse: collapse; }
  thead tr { background: #ff4b63; color: white; }
  th, td { padding: 6px 8px; text-align: left; font-size: 10px; }
  tbody tr:nth-child(even) { background: #fff1f2; }
  td { border-bottom: 1px solid #fecdd3; }
</style>
</head>
<body>
<div class="header">
  <h1>FitZone — Reporte de Ventas</h1>
  <p>Generado: {{ $generated_at }} | Total ventas: {{ $sales->count() }} | Ingresos: ${{ number_format($total_revenue,0,',','.') }} | Ganancia: ${{ number_format($total_profit,0,',','.') }}</p>
</div>
<table>
  <thead><tr><th>#</th><th>Fecha</th><th>Cliente</th><th>Tipo</th><th>Productos</th><th>Total</th><th>Ganancia</th></tr></thead>
  <tbody>
    @foreach($sales as $s)
    @php
      $ventaItems = $ventaItems[$s->id_venta] ?? [];
      $profit = $s->total - collect($ventaItems)->sum(fn($i) => ($i->producto?->precio_costo ?? ($i->precio_unitario * 0.65)) * $i->cantidad);
    @endphp
    <tr>
      <td>{{ $s->id_venta }}</td>
      <td>{{ $s->fecha ? $s->fecha->format('d/m/Y H:i') : '' }}</td>
      <td>{{ $s->usuario?->nombre ?? 'Cliente General' }}</td>
      <td>{{ $s->canal === 'web' ? 'Externo' : 'Interno' }}</td>
      <td>{{ collect($ventaItems)->map(fn($i) => ($i->producto?->nombre ?? 'Producto Eliminado') . " x{$i->cantidad}")->implode(', ') }}</td>
      <td>${{ number_format($s->total,0,',','.') }}</td>
      <td>${{ number_format($profit,0,',','.') }}</td>
    </tr>
    @endforeach
  </tbody>
</table>
</body>
</html>