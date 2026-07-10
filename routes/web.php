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

// ✅ TODAS LAS RUTAS DE MOVIMIENTOS JUNTAS Y EN ORDEN
Route::middleware(['auth'])->group(function () {
    // ============================================
    // 📝 RUTAS DE NÓMINA (MUST GO BEFORE RESOURCE)
    // ============================================
    Route::get('/movimientos/nomina/create', [MovimientoController::class, 'createNomina'])->name('movimientos.nomina.create');
    Route::post('/movimientos/nomina/store', [MovimientoController::class, 'storeNomina'])->name('movimientos.nomina.store');
    Route::get('/movimientos/nomina/empleados', [MovimientoController::class, 'getEmpleadosNomina'])->name('movimientos.nomina.empleados');
    Route::get('/movimientos/nomina/calcular-fecha', [MovimientoController::class, 'calcularFechaPago'])->name('movimientos.nomina.calcular-fecha');

    // ============================================
    // 📄 RUTAS PERSONALIZADAS DE MOVIMIENTOS
    // ============================================
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
    Route::get('/movimientos/fiscal-doble-iva/create', [MovimientoController::class, 'createFiscalDobleIva'])->name('movimientos.fiscal.doble.iva.create');
    Route::post('/movimientos/fiscal-doble-iva/store', [MovimientoController::class, 'storeFiscalDobleIva'])->name('movimientos.fiscal.doble.iva.store');
    
    // 🔹 Movimientos - Obtener última póliza de una persona
    Route::get('/movimientos/ultima-poliza', [MovimientoController::class, 'obtenerUltimaPoliza'])->name('movimientos.ultima.poliza');
    
    // 🔹 Movimientos - Exportar PDF de póliza
    Route::get('/movimientos/export-pdf/{id}', [MovimientoController::class, 'exportPdfPoliza'])->name('movimientos.export.pdf.poliza');

    // ============================================
    // ✅ ACCIONES DE PÓLIZA (FLUJO DE APROBACIÓN)
    // ============================================
    Route::post('/movimientos/{id}/revisar', [MovimientoController::class, 'revisarPoliza'])->name('movimientos.revisar');
    Route::post('/movimientos/{id}/autorizar', [MovimientoController::class, 'autorizarPoliza'])->name('movimientos.autorizar');
    Route::post('/movimientos/{id}/rechazar', [MovimientoController::class, 'rechazarPoliza'])->name('movimientos.rechazar');
    Route::post('/movimientos/{id}/cerrar', [MovimientoController::class, 'cerrarPoliza'])->name('movimientos.cerrar');
    Route::post('/movimientos/{id}/reabrir', [MovimientoController::class, 'reabrirPoliza'])->name('movimientos.reabrir');
    Route::post('/movimientos/{id}/revertir-revision', [MovimientoController::class, 'revertirRevision'])->name('movimientos.revertir.revision');

    // ============================================
    // 📊 INFORMACIÓN ADICIONAL DE PÓLIZA (API)
    // ============================================
    Route::get('/movimientos/{id}/detalle', [MovimientoController::class, 'getDetallePoliza'])->name('movimientos.detalle');
    Route::get('/movimientos/{id}/estadisticas', [MovimientoController::class, 'getEstadisticasPoliza'])->name('movimientos.estadisticas');
    Route::get('/movimientos/{id}/historial', [MovimientoController::class, 'getHistorialPoliza'])->name('movimientos.historial');
    Route::get('/movimientos/{id}/imprimir', [MovimientoController::class, 'imprimirPoliza'])
    ->name('movimientos.imprimir');
    Route::post('/movimientos/{id}/archivos', [MovimientoController::class, 'subirArchivo'])
    ->name('movimientos.archivos.subir');

    Route::get('/movimientos/archivos/{id}', [MovimientoController::class, 'verArchivo'])
        ->name('movimientos.archivos.ver');
    Route::delete('/movimientos/archivos/{id}', [MovimientoController::class, 'eliminarArchivo'])
        ->name('movimientos.archivos.eliminar');
    // ============================================
    // 🟢 RESOURCE - DEBE IR AL FINAL
    // ============================================
    Route::resource('movimientos', MovimientoController::class);
});

// ✅ PERSONAS - PRIMERO LAS RUTAS PERSONALIZADAS, LUEGO EL RESOURCE
Route::middleware(['auth'])->group(function () {
    Route::post('/personas/{id}/toggle-active', [PersonaController::class, 'toggleActive'])->name('personas.toggle-active');
    Route::post('/personas/generar-rfc', [PersonaController::class, 'generarRFC'])->name('personas.generar-rfc');
    Route::get('/personas/{id}/documentos', [PersonaController::class, 'getDocumentos'])->name('personas.documentos');
    Route::post('/personas/{id}/subir-documento', [PersonaController::class, 'subirDocumento'])->name('personas.subir-documento');
    Route::get('/personas/documentos/{id}/descargar', [PersonaController::class, 'descargarDocumento'])->name('personas.descargar-documento');
    Route::delete('/personas/documentos/{id}/eliminar', [PersonaController::class, 'eliminarDocumento'])->name('personas.eliminar-documento');
    Route::patch('/personas/documentos/{id}/toggle-finalizado', [PersonaController::class, 'toggleFinalizado'])->name('personas.toggle-finalizado');
    Route::resource('personas', PersonaController::class);
});

// ✅ CUENTAS - PRIMERO LAS RUTAS PERSONALIZADAS, LUEGO EL RESOURCE
Route::middleware(['auth'])->group(function () {
    Route::get('/cuentas/get-cuentas-madre', [CuentaController::class, 'getCuentasMadre'])->name('cuentas.get-cuentas-madre');
    Route::get('/cuentas/datatable', [CuentaController::class, 'getCuentas'])->name('cuentas.datatable');
    Route::post('/cuentas/{cuenta}/cambiar-estado', [CuentaController::class, 'cambiarEstado'])->name('cuentas.cambiar-estado');
    Route::get('/cuentas/inactivas', [CuentaController::class, 'inactivas'])->name('cuentas.inactivas');
    Route::post('/cuentas/{id}/restaurar', [CuentaController::class, 'restaurar'])->name('cuentas.restaurar');
    // En routes/web.php agregar:
    Route::get('/cuentas/get-cuentas-resultados', [CuentaController::class, 'getCuentasResultados'])
        ->name('cuentas.get-cuentas-resultados');
    Route::resource('cuentas', CuentaController::class);
    
});

// ✅ EMPRESAS - PRIMERO LAS RUTAS PERSONALIZADAS, LUEGO EL RESOURCE
Route::middleware(['auth'])->group(function () {
    Route::post('/empresas/{id}/toggle-active', [EmpresaController::class, 'toggleActive'])->name('empresas.toggle-active');
    Route::post('/empresas/{empresa}/cambiar-estado', [EmpresaController::class, 'cambiarEstado'])->name('empresas.cambiar-estado');
    Route::get('/empresas/select', [EmpresaController::class, 'getEmpresasSelect'])->name('empresas.select');
    Route::resource('empresas', EmpresaController::class);
});

// ✅ USUARIOS
Route::middleware(['auth'])->group(function () {
    Route::resource('usuarios', UsuarioController::class);
    Route::post('/usuarios/{id}/restore', [UsuarioController::class, 'restore'])->name('usuarios.restore');
    Route::post('/usuarios/{id}/reset-password', [UsuarioController::class, 'resetPassword'])->name('usuarios.reset-password');
    Route::post('/usuarios/{id}/toggle-activo', [UsuarioController::class, 'toggleActivo'])->name('usuarios.toggle-activo');
});

// ✅ REPORTES
Route::middleware(['auth'])->group(function () {
    Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
    Route::get('/reportes/movimientos', [ReporteController::class, 'getMovimientos'])->name('reportes.movimientos');
    Route::get('/reportes/movimientos-cuenta', [ReporteController::class, 'getMovimientosCuenta'])->name('reportes.movimientos.cuenta');
    Route::get('/reportes/export/excel', [ReporteController::class, 'exportExcel'])->name('reportes.export.excel');
    Route::get('/reportes/export/pdf', [ReporteController::class, 'exportPdf'])->name('reportes.export.pdf');
});

// Perfil
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Deshabilitar registro
Route::get('/register', function () {
    abort(404);
})->name('register');

require __DIR__.'/auth.php';