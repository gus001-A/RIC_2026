<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\CuentaController;
use App\Http\Controllers\PersonaController;
use App\Http\Controllers\MovimientoController;
use App\Http\Controllers\ReporteController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Redirigir a login
Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');

// ============================================
// 📌 RUTAS EXPLÍCITAS PARA VISTAS DE INERTIA
// ============================================
Route::middleware(['auth'])->group(function () {
    // 👤 PERSONAS
    Route::get('/personas', [PersonaController::class, 'index'])->name('personas.index');
    Route::get('/personas/create', [PersonaController::class, 'create'])->name('personas.create');
    Route::get('/personas/{id}', [PersonaController::class, 'show'])->name('personas.show');
    Route::get('/personas/{id}/edit', [PersonaController::class, 'edit'])->name('personas.edit');

    // 💰 CUENTAS
    Route::get('/cuentas', [CuentaController::class, 'index'])->name('cuentas.index');
    Route::get('/cuentas/create', [CuentaController::class, 'create'])->name('cuentas.create');
    Route::get('/cuentas/{id}', [CuentaController::class, 'show'])->name('cuentas.show');
    Route::get('/cuentas/{id}/edit', [CuentaController::class, 'edit'])->name('cuentas.edit');

    // 🏢 EMPRESAS
    Route::get('/empresas', [EmpresaController::class, 'index'])->name('empresas.index');
    Route::get('/empresas/create', [EmpresaController::class, 'create'])->name('empresas.create');
    Route::get('/empresas/{id}', [EmpresaController::class, 'show'])->name('empresas.show');
    Route::get('/empresas/{id}/edit', [EmpresaController::class, 'edit'])->name('empresas.edit');

    // 📝 MOVIMIENTOS
    Route::get('/movimientos', [MovimientoController::class, 'index'])->name('movimientos.index');
    Route::get('/movimientos/create', [MovimientoController::class, 'create'])->name('movimientos.create');
    Route::get('/movimientos/{id}', [MovimientoController::class, 'show'])->name('movimientos.show');
    Route::get('/movimientos/{id}/edit', [MovimientoController::class, 'edit'])->name('movimientos.edit');

    // 👥 USUARIOS
    Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
    Route::get('/usuarios/create', [UsuarioController::class, 'create'])->name('usuarios.create');
    Route::get('/usuarios/{id}', [UsuarioController::class, 'show'])->name('usuarios.show');
    Route::get('/usuarios/{id}/edit', [UsuarioController::class, 'edit'])->name('usuarios.edit');

    // 📊 REPORTES
    Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
});

// ============================================
// 📝 MOVIMIENTOS - ACCIONES (POST, PUT, DELETE)
// ============================================
Route::middleware(['auth'])->group(function () {
    Route::get('/movimientos/nomina/create', [MovimientoController::class, 'createNomina'])->name('movimientos.nomina.create');
    Route::post('/movimientos/nomina/store', [MovimientoController::class, 'storeNomina'])->name('movimientos.nomina.store');
    Route::get('/movimientos/nomina/empleados', [MovimientoController::class, 'getEmpleadosNomina'])->name('movimientos.nomina.empleados');
    Route::get('/movimientos/nomina/calcular-fecha', [MovimientoController::class, 'calcularFechaPago'])->name('movimientos.nomina.calcular-fecha');
    Route::get('/movimientos/{id}/abono', [MovimientoController::class, 'showAbono'])->name('movimientos.abono');
    Route::post('/movimientos/abono', [MovimientoController::class, 'storeAbono'])->name('movimientos.abono.store');
    Route::delete('/movimientos/abono/{id}', [MovimientoController::class, 'destroyAbono'])->name('movimientos.abono.destroy');
    Route::post('movimientos/liquidar-ingreso', [MovimientoController::class, 'liquidarIngreso'])->name('movimientos.liquidar.ingreso');
    Route::get('/movimientos/documento-fiscal/{id}/{tipo}', [MovimientoController::class, 'getDocumentoFiscal'])->name('movimientos.documento.fiscal');
    Route::get('movimientos/buscar/personas', [MovimientoController::class, 'buscarPersonas'])->name('movimientos.buscar.personas');
    Route::get('movimientos/buscar/cuentas', [MovimientoController::class, 'buscarCuentas'])->name('movimientos.buscar.cuentas');
    Route::get('movimientos/saldo/cuenta', [MovimientoController::class, 'obtenerSaldoCuenta'])->name('movimientos.saldo.cuenta');
    Route::post('movimientos/calcular-desglose', [MovimientoController::class, 'calcularDesglose'])->name('movimientos.calcular-desglose');
    Route::post('movimientos/marcadores/store', [MovimientoController::class, 'storeMarcador'])->name('movimientos.marcadores.store');
    Route::post('movimientos/traspaso/store', [MovimientoController::class, 'storeTraspaso'])->name('movimientos.traspaso.store');
    Route::get('movimientos/export/excel', [MovimientoController::class, 'exportExcel'])->name('movimientos.export.excel');
    Route::get('movimientos/export/pdf', [MovimientoController::class, 'exportPdf'])->name('movimientos.export.pdf');
    Route::resource('movimientos', MovimientoController::class)->except(['index', 'create', 'show', 'edit']);
});

// ============================================
// 👤 PERSONAS - ACCIONES
// ============================================
Route::middleware(['auth'])->group(function () {
    Route::post('/personas/{id}/toggle-active', [PersonaController::class, 'toggleActive'])->name('personas.toggle-active');
    Route::post('/personas/generar-rfc', [PersonaController::class, 'generarRFC'])->name('personas.generar-rfc');
    Route::get('/personas/{id}/documentos', [PersonaController::class, 'getDocumentos'])->name('personas.documentos');
    Route::post('/personas/{id}/subir-documento', [PersonaController::class, 'subirDocumento'])->name('personas.subir-documento');
    Route::get('/personas/documentos/{id}/descargar', [PersonaController::class, 'descargarDocumento'])->name('personas.descargar-documento');
    Route::delete('/personas/documentos/{id}/eliminar', [PersonaController::class, 'eliminarDocumento'])->name('personas.eliminar-documento');
    Route::patch('/personas/documentos/{id}/toggle-finalizado', [PersonaController::class, 'toggleFinalizado'])->name('personas.toggle-finalizado');
    Route::resource('personas', PersonaController::class)->except(['index', 'create', 'show', 'edit']);
});

// ============================================
// 💰 CUENTAS - ACCIONES
// ============================================
Route::middleware(['auth'])->group(function () {
    Route::get('/cuentas/get-cuentas-madre', [CuentaController::class, 'getCuentasMadre'])->name('cuentas.get-cuentas-madre');
    Route::get('/cuentas/datatable', [CuentaController::class, 'getCuentas'])->name('cuentas.datatable');
    Route::post('/cuentas/{cuenta}/cambiar-estado', [CuentaController::class, 'cambiarEstado'])->name('cuentas.cambiar-estado');
    Route::get('/cuentas/inactivas', [CuentaController::class, 'inactivas'])->name('cuentas.inactivas');
    Route::post('/cuentas/{id}/restaurar', [CuentaController::class, 'restaurar'])->name('cuentas.restaurar');
    Route::resource('cuentas', CuentaController::class)->except(['index', 'create', 'show', 'edit']);
});

// ============================================
// 🏢 EMPRESAS - ACCIONES
// ============================================
Route::middleware(['auth'])->group(function () {
    Route::post('/empresas/{id}/toggle-active', [EmpresaController::class, 'toggleActive'])->name('empresas.toggle-active');
    Route::post('/empresas/{empresa}/cambiar-estado', [EmpresaController::class, 'cambiarEstado'])->name('empresas.cambiar-estado');
    Route::get('/empresas/select', [EmpresaController::class, 'getEmpresasSelect'])->name('empresas.select');
    Route::resource('empresas', EmpresaController::class)->except(['index', 'create', 'show', 'edit']);
});

// ============================================
// 👥 USUARIOS - ACCIONES
// ============================================
Route::middleware(['auth'])->group(function () {
    Route::post('/usuarios/{id}/restore', [UsuarioController::class, 'restore'])->name('usuarios.restore');
    Route::post('/usuarios/{id}/reset-password', [UsuarioController::class, 'resetPassword'])->name('usuarios.reset-password');
    Route::post('/usuarios/{id}/toggle-activo', [UsuarioController::class, 'toggleActivo'])->name('usuarios.toggle-activo');
    Route::resource('usuarios', UsuarioController::class)->except(['index', 'create', 'show', 'edit']);
});

// ============================================
// 📊 REPORTES - ACCIONES
// ============================================
Route::middleware(['auth'])->group(function () {
    Route::get('/reportes/movimientos', [ReporteController::class, 'getMovimientos'])->name('reportes.movimientos');
    Route::get('/reportes/movimientos-cuenta', [ReporteController::class, 'getMovimientosCuenta'])->name('reportes.movimientos.cuenta');
    Route::get('/reportes/export/excel', [ReporteController::class, 'exportExcel'])->name('reportes.export.excel');
    Route::get('/reportes/export/pdf', [ReporteController::class, 'exportPdf'])->name('reportes.export.pdf');
});

// ============================================
// 👤 PERFIL
// ============================================
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Deshabilitar registro
Route::get('/register', function () {
    abort(404);
})->name('register');

// ============================================
// 🔐 RUTAS DE AUTENTICACIÓN
// ============================================
require __DIR__.'/auth.php';

// ============================================
// 🔥🔥🔥 RUTA CATCH-ALL - DEBE IR AL FINAL
// ============================================
Route::get('/{any}', function () {
    if (auth()->check()) {
        return Inertia::render('Dashboard');
    }
    return redirect()->route('login');
})->where('any', '.*');