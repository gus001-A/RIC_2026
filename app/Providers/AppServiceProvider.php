<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Usuario;
use Illuminate\Support\Facades\Schema; // ← ¡AGREGA ESTA LÍNEA!

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // ✅ ¡ESTA LÍNEA ES LA QUE SOLUCIONA EL ERROR!
        Schema::defaultStringLength(191);

        // ============================================================
        // PERMISOS POR ROL (según especificación del negocio)
        //  LECTOR       -> sólo ve la pantalla de Movimientos (nada más)
        //  CAPTURISTA   -> captura pólizas y personas; ve sólo lo suyo;
        //                  NO edita/revisa/autoriza pólizas; NO cuentas/usuarios/empresas
        //  ADMINISTRADOR-> crea usuarios (lector/capturista/admin), personas;
        //                  REVISA y EDITA pólizas (NO autoriza); ve todo; NO empresas
        //  AUDITOR      -> crea usuarios (todos menos super); AUTORIZA pólizas;
        //                  crea cuentas; NO empresas
        //  SUPERUSUARIO -> todo, incluye empresas
        // ============================================================
        $lector       = 'LECTOR';
        $capturista   = 'CAPTURISTA';
        $administrador = 'ADMINISTRADOR';
        $auditor      = 'AUDITOR';
        $super        = 'SUPERUSUARIO';

        // ✅ MOVIMIENTOS / PÓLIZAS
        Gate::define('ver-movimientos', fn (Usuario $user) => in_array($user->tipo_usuario, [$lector, $capturista, $administrador, $auditor, $super]));
        Gate::define('crear-movimientos', fn (Usuario $user) => in_array($user->tipo_usuario, [$capturista, $administrador, $auditor, $super]));
        // Editar una póliza: NO el capturista, NO el lector.
        Gate::define('editar-movimientos', fn (Usuario $user) => in_array($user->tipo_usuario, [$administrador, $auditor, $super]));
        Gate::define('eliminar-movimientos', fn (Usuario $user) => in_array($user->tipo_usuario, [$administrador, $auditor, $super]));
        // Revisar: administrador (y super). El auditor autoriza, no revisa.
        Gate::define('revisar-polizas', fn (Usuario $user) => in_array($user->tipo_usuario, [$administrador, $super]));
        // Autorizar: SÓLO auditor y super (el administrador YA NO autoriza).
        Gate::define('autorizar-polizas', fn (Usuario $user) => in_array($user->tipo_usuario, [$auditor, $super]));
        // Ver los movimientos de todos los usuarios (el capturista sólo ve los suyos).
        Gate::define('ver-todos-movimientos', fn (Usuario $user) => in_array($user->tipo_usuario, [$lector, $administrador, $auditor, $super]));

        // ✅ PERSONAS  (el LECTOR no ve personas)
        Gate::define('ver-personas', fn (Usuario $user) => in_array($user->tipo_usuario, [$capturista, $administrador, $auditor, $super]));
        Gate::define('crear-personas', fn (Usuario $user) => in_array($user->tipo_usuario, [$capturista, $administrador, $auditor, $super]));
        Gate::define('editar-personas', fn (Usuario $user) => in_array($user->tipo_usuario, [$capturista, $administrador, $auditor, $super]));
        Gate::define('eliminar-personas', fn (Usuario $user) => in_array($user->tipo_usuario, [$administrador, $auditor, $super]));

        // ✅ CUENTAS  (ni LECTOR ni CAPTURISTA)
        Gate::define('ver-cuentas', fn (Usuario $user) => in_array($user->tipo_usuario, [$administrador, $auditor, $super]));
        Gate::define('crear-cuentas', fn (Usuario $user) => in_array($user->tipo_usuario, [$administrador, $auditor, $super]));
        Gate::define('editar-cuentas', fn (Usuario $user) => in_array($user->tipo_usuario, [$administrador, $auditor, $super]));
        Gate::define('eliminar-cuentas', fn (Usuario $user) => in_array($user->tipo_usuario, [$administrador, $auditor, $super]));

        // ✅ USUARIOS  (qué TIPOS puede crear cada rol se valida en UsuarioController)
        Gate::define('ver-usuarios', fn (Usuario $user) => in_array($user->tipo_usuario, [$administrador, $auditor, $super]));
        Gate::define('crear-usuarios', fn (Usuario $user) => in_array($user->tipo_usuario, [$administrador, $auditor, $super]));
        Gate::define('editar-usuarios', fn (Usuario $user) => in_array($user->tipo_usuario, [$administrador, $auditor, $super]));
        Gate::define('eliminar-usuarios', fn (Usuario $user) => in_array($user->tipo_usuario, [$administrador, $auditor, $super]));

        // ✅ EMPRESAS  (sólo SUPERUSUARIO)
        Gate::define('ver-empresas', fn (Usuario $user) => $user->tipo_usuario === $super);
        Gate::define('crear-empresas', fn (Usuario $user) => $user->tipo_usuario === $super);
        Gate::define('editar-empresas', fn (Usuario $user) => $user->tipo_usuario === $super);
        Gate::define('eliminar-empresas', fn (Usuario $user) => $user->tipo_usuario === $super);

        // ✅ REPORTES  (ni LECTOR ni CAPTURISTA)
        Gate::define('ver-reportes', fn (Usuario $user) => in_array($user->tipo_usuario, [$administrador, $auditor, $super]));
    }

    /**
     * Tipos de usuario que un rol puede CREAR (usado por UsuarioController).
     */
    public static function tiposUsuarioQuePuedeCrear(string $tipoUsuario): array
    {
        return match ($tipoUsuario) {
            'SUPERUSUARIO'  => ['LECTOR', 'CAPTURISTA', 'ADMINISTRADOR', 'AUDITOR', 'SUPERUSUARIO'],
            'AUDITOR'       => ['LECTOR', 'CAPTURISTA', 'ADMINISTRADOR', 'AUDITOR'],
            'ADMINISTRADOR' => ['LECTOR', 'CAPTURISTA', 'ADMINISTRADOR'],
            default         => [],
        };
    }
}