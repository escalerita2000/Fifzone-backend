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
    <tr>
      <td>{{ $s->id }}</td>
      <td>{{ $s->created_at->format('d/m/Y H:i') }}</td>
      <td>{{ $s->customer }}</td>
      <td>{{ $s->type === 'external' ? 'Externo' : 'Interno' }}</td>
      <td>{{ $s->items->map(fn($i)=>"{$i->product_name} x{$i->quantity}")->implode(', ') }}</td>
      <td>${{ number_format($s->total,0,',','.') }}</td>
      <td>${{ number_format($s->profit,0,',','.') }}</td>
    </tr>
    @endforeach
  </tbody>
</table>
</body>
</html>