<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Convierte a MAYÚSCULAS todo el texto que llega en el request (body y query),
 * excepto contraseñas y correos electrónicos.
 *
 * - Contraseñas: nunca se tocan (login, cambio de contraseña, creación de
 *   usuario, etc.) porque son sensibles a mayúsculas/minúsculas.
 * - Correos: se dejan tal cual el usuario los escribió; convertirlos a
 *   mayúsculas no aporta nada y puede causar confusión visual o problemas
 *   con integraciones externas que sí distinguen mayúsculas/minúsculas.
 * - Rutas de autenticación (login, registro, recuperación de contraseña):
 *   se excluyen por completo, no hay beneficio en tocarlas y evita
 *   cualquier sorpresa en un flujo crítico.
 */
class ConvertirEntradaAMayusculas
{
    /**
     * Patrones (sobre el NOMBRE del campo) que nunca se convierten.
     */
    protected array $patronesExcluidos = [
        '/password/i',
        '/contrasena/i',
        '/contraseña/i',
        '/^email$/i',
        '/correo/i',
        // Campos internos de Laravel: NUNCA tocar. `_token` es el CSRF token,
        // sensible a mayúsculas/minúsculas — convertirlo tumbaría todos los
        // formularios con "Page Expired" / 419.
        '/^_token$/i',
        '/^_method$/i',
    ];

    /**
     * Rutas (por URI) donde no se aplica ninguna conversión.
     */
    protected array $rutasExcluidas = [
        'login',
        'register',
        'forgot-password',
        'reset-password',
        'reset-password/*',
        'confirm-password',
        'password',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is($this->rutasExcluidas)) {
            return $next($request);
        }

        $datos = $request->all();

        if (!empty($datos)) {
            $request->merge($this->convertirRecursivo($datos));
        }

        return $next($request);
    }

    private function convertirRecursivo(array $datos, ?string $claveContenedora = null): array
    {
        foreach ($datos as $clave => $valor) {
            if (is_array($valor)) {
                // La clave del arreglo (p.ej. "empleados") no debe excluir a
                // sus hijos salvo que el propio hijo coincida con un patrón.
                $datos[$clave] = $this->convertirRecursivo($valor, is_string($clave) ? $clave : $claveContenedora);
                continue;
            }

            if (!is_string($valor) || $valor === '') {
                continue;
            }

            if ($this->campoExcluido((string) $clave)) {
                continue;
            }

            $datos[$clave] = mb_strtoupper($valor, 'UTF-8');
        }

        return $datos;
    }

    private function campoExcluido(string $clave): bool
    {
        foreach ($this->patronesExcluidos as $patron) {
            if (preg_match($patron, $clave)) {
                return true;
            }
        }

        return false;
    }
}
