<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Qué cuentas fondeadoras puede ver/usar cada usuario (la asigna el
 * SUPERUSUARIO, igual que la asignación de empresas).
 *
 * Regla: si un usuario NO tiene ninguna fondeadora asignada en una empresa,
 * ve todas las de esa empresa (así los usuarios existentes no pierden acceso);
 * en cuanto tiene al menos una asignada en esa empresa, sólo ve esas.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('cuentas_fondeadoras_usuarios')) {
            return;
        }

        Schema::create('cuentas_fondeadoras_usuarios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_usuario');
            $table->unsignedBigInteger('id_cuenta');
            $table->timestamps();

            $table->unique(['id_usuario', 'id_cuenta'], 'cfu_usuario_cuenta_unique');
            $table->index('id_cuenta', 'cfu_cuenta_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuentas_fondeadoras_usuarios');
    }
};
