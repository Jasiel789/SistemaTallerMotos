@php
    $clasesEstado = [
        'recibida' => 'bg-gray-100 text-gray-700',
        'en_proceso' => 'bg-blue-100 text-blue-800',
        'lista' => 'bg-green-100 text-green-800',
        'entregada' => 'bg-indigo-100 text-indigo-800',
        'cancelada' => 'bg-red-100 text-red-800',
    ][$estado] ?? 'bg-gray-100 text-gray-700';
@endphp
<span class="inline-flex whitespace-nowrap rounded-full px-2 py-0.5 text-xs font-medium {{ $clasesEstado }}">{{ \App\Models\OrdenServicio::ETIQUETAS[$estado] ?? $estado }}</span>
