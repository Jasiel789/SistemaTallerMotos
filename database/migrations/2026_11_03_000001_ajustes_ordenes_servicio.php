<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Agrega el estado "cancelada" (MySQL / MariaDB, que es lo que trae XAMPP)
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement(
                "ALTER TABLE ordenes_servicio MODIFY estado
                 ENUM('recibida','en_proceso','lista','entregada','cancelada') NOT NULL DEFAULT 'recibida'"
            );
        }

        // Cuando se cobra una orden, la mano de obra queda guardada en la venta
        Schema::table('ventas', function (Blueprint $table) {
            $table->decimal('mano_de_obra', 10, 2)->default(0)->after('total');
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropColumn('mano_de_obra');
        });

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement(
                "ALTER TABLE ordenes_servicio MODIFY estado
                 ENUM('recibida','en_proceso','lista','entregada') NOT NULL DEFAULT 'recibida'"
            );
        }
    }
};
