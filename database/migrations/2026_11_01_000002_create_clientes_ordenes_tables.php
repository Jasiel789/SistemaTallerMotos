<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('telefono')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });

        Schema::create('motos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->string('marca');
            $table->string('modelo');
            $table->unsignedSmallInteger('anio')->nullable();
            $table->string('placa')->nullable();
            $table->unsignedInteger('kilometraje')->nullable();
            $table->timestamps();
        });

        Schema::create('ordenes_servicio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->foreignId('moto_id')->constrained('motos');
            $table->foreignId('user_id')->constrained('users'); // quién la abrió
            $table->date('fecha');
            $table->enum('estado', ['recibida', 'en_proceso', 'lista', 'entregada'])->default('recibida');
            $table->text('diagnostico')->nullable();
            $table->decimal('mano_de_obra', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0); // precio final: refacciones + mano de obra
            $table->timestamps();
        });

        Schema::create('orden_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_id')->constrained('ordenes_servicio')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos');
            $table->unsignedInteger('cantidad');
            $table->decimal('precio_venta', 10, 2);
            $table->decimal('precio_compra', 10, 2); // copia al momento, para calcular ganancia
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orden_detalles');
        Schema::dropIfExists('ordenes_servicio');
        Schema::dropIfExists('motos');
        Schema::dropIfExists('clientes');
    }
};
