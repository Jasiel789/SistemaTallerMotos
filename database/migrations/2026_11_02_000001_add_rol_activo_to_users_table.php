<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('rol')->default('empleado')->after('password'); // dueno | empleado
            $table->boolean('activo')->default(true)->after('rol');
        });

        // Las cuentas que ya existen son tuyas: quedan como dueño
        DB::table('users')->update(['rol' => 'dueno']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['rol', 'activo']);
        });
    }
};
