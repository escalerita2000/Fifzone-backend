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
  .ok  { color: #16a34a; font-weight: bold; }
  .low { color: #d97706; font-weight: bold; }
  .out { color: #dc2626; font-weight: bold; }
</style>
</head>
<body>
<div class="header">
  <h1>FitZone — Reporte de Inventario</h1>
  <p>Generado: {{ $generated_at }} | Productos: {{ $products->count() }} | Sin stock: {{ $out_of_stock }} | Stock bajo: {{ $low_stock }}</p>
</div>
<table>
  <thead><tr><th>ID</th><th>Producto</th><th>Categoría</th><th>Stock</th><th>Costo</th><th>P.Venta</th><th>Valor Stock</th><th>Estado</th></tr></thead>
  <tbody>
    @foreach($products as $p)
    <tr>
      <td>{{ $p->id }}</td>
      <td>{{ $p->name }}</td>
      <td>{{ $p->category }}</td>
      <td>{{ $p->stock }}</td>
      <td>${{ number_format($p->cost_price,0,',','.') }}</td>
      <td>${{ number_format($p->sale_price,0,',','.') }}</td>
      <td>${{ number_format($p->cost_price*$p->stock,0,',','.') }}</td>
      <td class="{{ $p->stock===0?'out':($p->stock<=5?'low':'ok') }}">
        {{ $p->stock===0?'AGOTADO':($p->stock<=5?'BAJO':'OK') }}
      </td>
    </tr>
    @endforeach
  </tbody>
</table>
</body>
</html>