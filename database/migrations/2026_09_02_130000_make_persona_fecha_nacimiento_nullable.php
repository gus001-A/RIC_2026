<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A pedido del negocio, para registrar una persona basta con el NOMBRE.
 * `personas.Fecha_nacimiento` era `DATE NOT NULL` sin default, así que guardar
 * una persona sin fecha lanzaba "Field 'Fecha_nacimiento' doesn't have a default value".
 * Se hace NULL-able (igual que Paterno / Materno).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('personas', 'Fecha_nacimiento')) {
            return;
        }

        DB::statement("ALTER TABLE `personas` MODIFY `Fecha_nacimiento` DATE NULL DEFAULT NULL");
    }

    public function down(): void
    {
        if (!Schema::hasColumn('personas', 'Fecha_nacimiento')) {
            return;
        }

        DB::statement("UPDATE `personas` SET `Fecha_nacimiento` = '1900-01-01' WHERE `Fecha_nacimiento` IS NULL");
        DB::statement("ALTER TABLE `personas` MODIFY `Fecha_nacimiento` DATE NOT NULL");
    }
};
