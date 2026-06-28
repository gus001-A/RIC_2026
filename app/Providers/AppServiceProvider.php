<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Usuario;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // ✅ MOVIMIENTOS
        Gate::define('ver-movimientos', function (Usuario $user) {
            return in_array($user->tipo_usuario, ['LECTOR', 'CAPTURISTA', 'ADMINISTRADOR', 'AUDITOR', 'SUPERUSUARIO']);
        });

        Gate::define('crear-movimientos', function (Usuario $user) {
            return in_array($user->tipo_usuario, ['CAPTURISTA', 'ADMINISTRADOR', 'AUDITOR', 'SUPERUSUARIO']);
        });

        Gate::define('editar-movimientos', function (Usuario $user) {
            return in_array($user->tipo_usuario, ['ADMINISTRADOR', 'AUDITOR', 'SUPERUSUARIO']);
        });

        Gate::define('eliminar-movimientos', function (Usuario $user) {
            return in_array($user->tipo_usuario, ['ADMINISTRADOR', 'AUDITOR', 'SUPERUSUARIO']);
        });

        Gate::define('autorizar-polizas', function (Usuario $user) {
            return in_array($user->tipo_usuario, ['ADMINISTRADOR', 'AUDITOR', 'SUPERUSUARIO']);
        });

        Gate::define('ver-todos-movimientos', function (Usuario $user) {
            return in_array($user->tipo_usuario, ['ADMINISTRADOR', 'AUDITOR', 'SUPERUSUARIO']);
        });

        // ✅ PERSONAS
        Gate::define('ver-personas', function (Usuario $user) {
            return in_array($user->tipo_usuario, ['LECTOR', 'CAPTURISTA', 'ADMINISTRADOR', 'AUDITOR', 'SUPERUSUARIO']);
        });

        Gate::define('crear-personas', function (Usuario $user) {
            return in_array($user->tipo_usuario, ['CAPTURISTA', 'ADMINISTRADOR', 'AUDITOR', 'SUPERUSUARIO']);
        });

        Gate::define('editar-personas', function (Usuario $user) {
            return in_array($user->tipo_usuario, ['ADMINISTRADOR', 'AUDITOR', 'SUPERUSUARIO']);
        });

        Gate::define('eliminar-personas', function (Usuario $user) {
            return in_array($user->tipo_usuario, ['ADMINISTRADOR', 'AUDITOR', 'SUPERUSUARIO']);
        });

        // ✅ CUENTAS
        Gate::define('ver-cuentas', function (Usuario $user) {
            return in_array($user->tipo_usuario, ['LECTOR', 'CAPTURISTA', 'ADMINISTRADOR', 'AUDITOR', 'SUPERUSUARIO']);
        });

        Gate::define('crear-cuentas', function (Usuario $user) {
            return in_array($user->tipo_usuario, ['ADMINISTRADOR', 'AUDITOR', 'SUPERUSUARIO']);
        });

        Gate::define('editar-cuentas', function (Usuario $user) {
            return in_array($user->tipo_usuario, ['ADMINISTRADOR', 'AUDITOR', 'SUPERUSUARIO']);
        });

        Gate::define('eliminar-cuentas', function (Usuario $user) {
            return in_array($user->tipo_usuario, ['ADMINISTRADOR', 'AUDITOR', 'SUPERUSUARIO']);
        });

        // ✅ USUARIOS ⭐ ESTOS SON LOS QUE FALTABAN
        Gate::define('ver-usuarios', function (Usuario $user) {
            return in_array($user->tipo_usuario, ['ADMINISTRADOR', 'AUDITOR', 'SUPERUSUARIO']);
        });

        Gate::define('crear-usuarios', function (Usuario $user) {
            return in_array($user->tipo_usuario, ['ADMINISTRADOR', 'AUDITOR', 'SUPERUSUARIO']);
        });

        Gate::define('editar-usuarios', function (Usuario $user) {
            return in_array($user->tipo_usuario, ['ADMINISTRADOR', 'AUDITOR', 'SUPERUSUARIO']);
        });

        Gate::define('eliminar-usuarios', function (Usuario $user) {
            return in_array($user->tipo_usuario, ['ADMINISTRADOR', 'AUDITOR', 'SUPERUSUARIO']);
        });

        // ✅ EMPRESAS
        Gate::define('ver-empresas', function (Usuario $user) {
            return $user->tipo_usuario === 'SUPERUSUARIO';
        });

        Gate::define('crear-empresas', function (Usuario $user) {
            return $user->tipo_usuario === 'SUPERUSUARIO';
        });

        Gate::define('editar-empresas', function (Usuario $user) {
            return $user->tipo_usuario === 'SUPERUSUARIO';
        });

        Gate::define('eliminar-empresas', function (Usuario $user) {
            return $user->tipo_usuario === 'SUPERUSUARIO';
        });

        // ✅ REPORTES
        Gate::define('ver-reportes', function (Usuario $user) {
            return in_array($user->tipo_usuario, ['LECTOR', 'CAPTURISTA', 'ADMINISTRADOR', 'AUDITOR', 'SUPERUSUARIO']);
        });
    }
}