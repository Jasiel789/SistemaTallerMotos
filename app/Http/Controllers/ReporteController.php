<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\VentaDetalle;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReporteController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date', 'after_or_equal:desde'],
        ]);

        $desdeTxt = $request->input('desde', now()->startOfMonth()->toDateString());
        $hastaTxt = $request->input('hasta', now()->toDateString());

        $desde = Carbon::parse($desdeTxt)->startOfDay();
        $hasta = Carbon::parse($hastaTxt)->endOfDay();

        // Todas las líneas de refacciones vendidas en el periodo (cada una guarda el costo de ese día)
        $lineas = VentaDetalle::query()
            ->join('ventas', 'ventas.id', '=', 'venta_detalles.venta_id')
            ->whereBetween('ventas.fecha', [$desde, $hasta]);

        $totales = (clone $lineas)
            ->selectRaw('COALESCE(SUM(venta_detalles.cantidad * venta_detalles.precio_venta), 0) AS ingresos')
            ->selectRaw('COALESCE(SUM(venta_detalles.cantidad * venta_detalles.precio_compra), 0) AS costo')
            ->first();

        // La mano de obra de las órdenes cobradas se cuenta como ingreso (sin costo registrado)
        $manoObra = (float) Venta::whereBetween('fecha', [$desde, $hasta])->sum('mano_de_obra');

        $ingresos = (float) $totales->ingresos + $manoObra;
        $costo = (float) $totales->costo;
        $ganancia = $ingresos - $costo;
        $margen = $ingresos > 0 ? ($ganancia / $ingresos) * 100 : 0;

        $numVentas = Venta::whereBetween('fecha', [$desde, $hasta])->count();

        $topProductos = (clone $lineas)
            ->join('productos', 'productos.id', '=', 'venta_detalles.producto_id')
            ->selectRaw('productos.id, productos.sku, productos.nombre')
            ->selectRaw('SUM(venta_detalles.cantidad) AS piezas')
            ->selectRaw('SUM(venta_detalles.cantidad * venta_detalles.precio_venta) AS ingresos')
            ->selectRaw('SUM(venta_detalles.cantidad * (venta_detalles.precio_venta - venta_detalles.precio_compra)) AS ganancia')
            ->groupBy('productos.id', 'productos.sku', 'productos.nombre')
            ->orderByDesc('ganancia')
            ->limit(10)
            ->get();

        // Por día: ventas y mano de obra desde "ventas", refacciones desde sus renglones
        $ventasDia = Venta::whereBetween('fecha', [$desde, $hasta])
            ->selectRaw('DATE(fecha) AS dia, COUNT(*) AS ventas, COALESCE(SUM(mano_de_obra), 0) AS mano')
            ->groupBy('dia')
            ->orderBy('dia')
            ->get();

        $partesDia = (clone $lineas)
            ->selectRaw('DATE(ventas.fecha) AS dia')
            ->selectRaw('SUM(venta_detalles.cantidad * venta_detalles.precio_venta) AS ingresos')
            ->selectRaw('SUM(venta_detalles.cantidad * (venta_detalles.precio_venta - venta_detalles.precio_compra)) AS ganancia')
            ->groupBy('dia')
            ->get()
            ->keyBy('dia');

        $porDia = $ventasDia->map(function ($v) use ($partesDia) {
            $p = $partesDia->get($v->dia);
            $mano = (float) $v->mano;

            return (object) [
                'dia' => $v->dia,
                'ventas' => $v->ventas,
                'ingresos' => (float) ($p->ingresos ?? 0) + $mano,
                'ganancia' => (float) ($p->ganancia ?? 0) + $mano,
            ];
        });

        $porPago = Venta::whereBetween('fecha', [$desde, $hasta])
            ->selectRaw('metodo_pago, COUNT(*) AS ventas, SUM(total) AS total')
            ->groupBy('metodo_pago')
            ->orderByDesc('total')
            ->get();

        $porEmpleado = Venta::whereBetween('ventas.fecha', [$desde, $hasta])
            ->join('users', 'users.id', '=', 'ventas.user_id')
            ->selectRaw('users.name, COUNT(*) AS ventas, SUM(ventas.total) AS total')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total')
            ->get();

        return view('reportes.index', [
            'desde' => $desde->toDateString(),
            'hasta' => $hasta->toDateString(),
            'ingresos' => $ingresos,
            'manoObra' => $manoObra,
            'costo' => $costo,
            'ganancia' => $ganancia,
            'margen' => $margen,
            'numVentas' => $numVentas,
            'topProductos' => $topProductos,
            'porDia' => $porDia,
            'porPago' => $porPago,
            'porEmpleado' => $porEmpleado,
        ]);
    }
}
