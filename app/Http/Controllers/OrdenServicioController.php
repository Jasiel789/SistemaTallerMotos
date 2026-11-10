<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\OrdenDetalle;
use App\Models\OrdenServicio;
use App\Models\Producto;
use App\Services\OrdenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use RuntimeException;

class OrdenServicioController extends Controller
{
    public function __construct(private OrdenService $ordenes)
    {
    }

    public function index(Request $request)
    {
        $estado = $request->input('estado', 'abiertas');

        $lista = OrdenServicio::with(['cliente', 'moto'])
            ->when($estado === 'abiertas', fn ($q) => $q->whereIn('estado', OrdenServicio::ESTADOS_ABIERTOS))
            ->when(array_key_exists($estado, OrdenServicio::ETIQUETAS), fn ($q) => $q->where('estado', $estado))
            ->when($request->q, function ($query, $texto) {
                $texto = trim($texto, "# ");

                $query->where(function ($w) use ($texto) {
                    $w->whereHas('cliente', fn ($c) => $c->where(fn ($x) => $x
                        ->where('nombre', 'like', "%{$texto}%")
                        ->orWhere('telefono', 'like', "%{$texto}%")))
                        ->orWhereHas('moto', fn ($m) => $m->where(fn ($x) => $x
                            ->where('placa', 'like', "%{$texto}%")
                            ->orWhere('marca', 'like', "%{$texto}%")
                            ->orWhere('modelo', 'like', "%{$texto}%")));

                    if (ctype_digit($texto)) {
                        $w->orWhere('id', (int) $texto);
                    }
                });
            })
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('ordenes.index', ['ordenes' => $lista, 'estado' => $estado]);
    }

    public function create(Request $request)
    {
        return view('ordenes.create', [
            'clientes' => Cliente::with('motos:id,cliente_id,marca,modelo,anio,placa,kilometraje')
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'telefono']),
            'clienteId' => $request->input('cliente_id'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'cliente_id' => ['required', 'exists:clientes,id'],
            'moto_id' => ['required', Rule::exists('motos', 'id')->where('cliente_id', $request->input('cliente_id'))],
            'kilometraje' => ['nullable', 'integer', 'min:0'],
            'diagnostico' => ['nullable', 'string', 'max:3000'],
            'mano_de_obra' => ['nullable', 'numeric', 'min:0'],
        ], [
            'cliente_id.required' => 'Elige un cliente.',
            'moto_id.required' => 'Elige la moto.',
            'moto_id.exists' => 'Esa moto no pertenece a ese cliente.',
        ]);

        $orden = DB::transaction(function () use ($data, $request) {
            $mano = (float) ($data['mano_de_obra'] ?? 0);

            $orden = OrdenServicio::create([
                'cliente_id' => $data['cliente_id'],
                'moto_id' => $data['moto_id'],
                'user_id' => $request->user()->id,
                'fecha' => now()->toDateString(),
                'estado' => 'recibida',
                'diagnostico' => $data['diagnostico'] ?? null,
                'mano_de_obra' => $mano,
                'total' => $mano,
            ]);

            // El kilometraje al recibir la moto queda actualizado en su ficha
            if (isset($data['kilometraje'])) {
                $orden->moto->update(['kilometraje' => $data['kilometraje']]);
            }

            return $orden;
        });

        return redirect()->route('ordenes.show', $orden)->with('status', 'Orden creada. Agrega las refacciones cuando las uses.');
    }

    public function show(OrdenServicio $orden)
    {
        $orden->load(['cliente', 'moto', 'user', 'detalles.producto', 'venta']);

        // Sin precios de compra: los empleados también usan esta pantalla
        $productos = Producto::where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'sku', 'nombre', 'marca', 'compatibilidad', 'precio_venta', 'stock_actual']);

        return view('ordenes.show', compact('orden', 'productos'));
    }

    public function update(Request $request, OrdenServicio $orden)
    {
        $data = $request->validate([
            'diagnostico' => ['nullable', 'string', 'max:3000'],
            'mano_de_obra' => ['required', 'numeric', 'min:0'],
        ]);

        return $this->ejecutar($orden, 'Orden actualizada.', fn () => $this->ordenes->actualizarDatos(
            $orden->id,
            $data['diagnostico'] ?? null,
            (float) $data['mano_de_obra'],
        ));
    }

    public function agregarRefaccion(Request $request, OrdenServicio $orden)
    {
        $data = $request->validate([
            'producto_id' => ['required', 'exists:productos,id'],
            'cantidad' => ['required', 'integer', 'min:1'],
        ], [
            'producto_id.required' => 'Elige una refacción de la lista.',
        ]);

        return $this->ejecutar($orden, 'Refacción agregada.', fn () => $this->ordenes->agregarRefaccion(
            $orden->id,
            (int) $data['producto_id'],
            (int) $data['cantidad'],
            $request->user()->id,
        ));
    }

    public function quitarRefaccion(Request $request, OrdenServicio $orden, OrdenDetalle $detalle)
    {
        abort_unless($detalle->orden_id === $orden->id, 404);

        return $this->ejecutar($orden, 'Refacción quitada; regresó al stock.', fn () => $this->ordenes->quitarRefaccion(
            $orden->id,
            $detalle->id,
            $request->user()->id,
        ));
    }

    public function cambiarEstado(Request $request, OrdenServicio $orden)
    {
        $data = $request->validate([
            'estado' => ['required', Rule::in(OrdenServicio::ESTADOS_ABIERTOS)],
        ]);

        return $this->ejecutar($orden, 'Estado actualizado.', fn () => $this->ordenes->cambiarEstado($orden->id, $data['estado']));
    }

    public function entregar(Request $request, OrdenServicio $orden)
    {
        $data = $request->validate([
            'metodo_pago' => ['required', Rule::in(['efectivo', 'tarjeta', 'transferencia'])],
        ]);

        try {
            $venta = $this->ordenes->entregar($orden->id, $request->user()->id, $data['metodo_pago']);
        } catch (RuntimeException $e) {
            return redirect()->route('ordenes.show', $orden)->withErrors(['orden' => $e->getMessage()]);
        }

        return redirect()->route('ordenes.show', $orden)
            ->with('status', "Orden cobrada y entregada. Se registró la venta #{$venta->id}.");
    }

    public function cancelar(Request $request, OrdenServicio $orden)
    {
        return $this->ejecutar($orden, 'Orden cancelada. Las refacciones regresaron al stock.', fn () => $this->ordenes->cancelar(
            $orden->id,
            $request->user()->id,
        ));
    }

    private function ejecutar(OrdenServicio $orden, string $mensaje, callable $accion)
    {
        try {
            $accion();
        } catch (RuntimeException $e) {
            return redirect()->route('ordenes.show', $orden)->withErrors(['orden' => $e->getMessage()]);
        }

        return redirect()->route('ordenes.show', $orden)->with('status', $mensaje);
    }
}
