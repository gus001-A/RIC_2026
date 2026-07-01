<template>
    <AppLayout :title="tituloPagina">
        <template #header>
            <div class="header-wrapper">
                <div class="header-left">
                    <Link :href="route('movimientos.index')" class="btn-back">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                    </Link>
                    <div class="header-content">
                        <h2 class="header-title">Detalle de Póliza</h2>
                        <p class="header-subtitle">
                            <span class="folio-badge">{{ movimiento.folio || '—' }}</span>
                            <span class="header-divider">|</span>
                            <span class="tipo-badge" :class="getTipoClass(movimiento.tipo_poliza)">
                                {{ getTipoTexto(movimiento.tipo_poliza) }}
                            </span>
                            <span v-if="movimiento.es_fiscal" class="fiscal-badge">FISCAL</span>
                        </p>
                    </div>
                </div>
                <div class="header-right">
                    <div class="header-actions">
                        <span class="status-badge" :class="getEstatusClass(movimiento.estatus)">
                            {{ movimiento.estatus_texto || movimiento.estatus || '—' }}
                        </span>
                    </div>
                </div>
            </div>
        </template>

        <div class="page-content" id="print-area">
            <div class="container-custom">
                <!-- ===== TARJETA DE RESUMEN ===== -->
                <div class="summary-card">
                    <div class="summary-grid">
                        <div class="summary-item highlight">
                            <span class="summary-label">Folio</span>
                            <span class="summary-value folio-principal">{{ movimiento.folio || '—' }}</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Tipo</span>
                            <span class="summary-value">{{ getTipoTexto(movimiento.tipo_poliza) }}</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Fecha</span>
                            <span class="summary-value">{{ formatFecha(movimiento.fecha_poliza) }}</span>
                        </div>
                        <div class="summary-item highlight">
                            <span class="summary-label">Monto</span>
                            <span class="summary-value monto-principal" :class="getMontoClass(movimiento.monto)">
                                ${{ formatNumber(movimiento.monto) }}
                            </span>
                        </div>

                        <!-- 🔹 TRASPASO: Mostrar monto traspaso -->
                        <div class="summary-item" v-if="movimiento.es_traspaso">
                            <span class="summary-label">Monto Traspaso</span>
                            <span class="summary-value">${{ formatNumber(movimiento.monto_traspaso) }}</span>
                        </div>

                        <!-- 🔹 POR PAGAR: Mostrar saldo pendiente -->
                        <div class="summary-item" v-if="movimiento.es_por_pagar">
                            <span class="summary-label">Saldo Pendiente</span>
                            <span class="summary-value" :class="getSaldoPendienteClass(movimiento.saldo_pendiente)">
                                ${{ formatNumber(movimiento.saldo_pendiente) }}
                            </span>
                        </div>
                        <div class="summary-item" v-if="movimiento.es_por_pagar">
                            <span class="summary-label">Total Abonado</span>
                            <span class="summary-value abonado-value">${{ formatNumber(movimiento.total_abonado) }}</span>
                        </div>

                        <!-- 🔹 Referencia -->
                        <div class="summary-item" v-if="movimiento.referencia">
                            <span class="summary-label">Referencia</span>
                            <span class="summary-value">{{ movimiento.referencia }}</span>
                        </div>
                    </div>
                </div>

                <div class="detail-card">
                    <!-- ============================================================ -->
                    <!-- SECCIÓN 1: INFORMACIÓN GENERAL (SIEMPRE VISIBLE) -->
                    <!-- ============================================================ -->
                    <div class="detail-section">
                        <div class="section-header">
                            <div class="section-icon blue">
                                <svg class="icon-svg" fill="none" stroke="white" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="section-title">Información General</h3>
                                <p class="section-subtitle">Datos principales de la póliza</p>
                            </div>
                            <span class="badge-categoria" :class="getCategoriaClass(movimiento.categoria)">
                                {{ movimiento.categoria || '—' }}
                            </span>
                        </div>

                        <div class="info-grid">
                            <div class="info-item highlight">
                                <span class="info-label">Folio</span>
                                <span class="info-value folio-value">{{ movimiento.folio || '—' }}</span>
                            </div>
                            <div class="info-item" v-if="movimiento.referencia">
                                <span class="info-label">Referencia</span>
                                <span class="info-value">{{ movimiento.referencia }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Tipo de Póliza</span>
                                <span class="info-value">{{ getTipoTexto(movimiento.tipo_poliza) }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Fecha Póliza</span>
                                <span class="info-value">{{ formatFecha(movimiento.fecha_poliza) }}</span>
                            </div>
                            <div class="info-item" v-if="movimiento.es_por_pagar">
                                <span class="info-label">Fecha Vencimiento</span>
                                <span class="info-value" :class="getVencimientoClass(movimiento.fecha_vencimiento)">
                                    {{ formatFecha(movimiento.fecha_vencimiento) || '—' }}
                                    <span v-if="isVencido(movimiento.fecha_vencimiento)" class="vencido-badge">VENCIDO</span>
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Categoría</span>
                                <span class="info-value">{{ movimiento.categoria || '—' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Estatus</span>
                                <span class="info-value">
                                    <span class="estatus-badge" :class="getEstatusClass(movimiento.estatus)">
                                        {{ movimiento.estatus_texto || movimiento.estatus || '—' }}
                                    </span>
                                </span>
                            </div>
                            <div class="info-item" v-if="movimiento.es_ingreso_egreso">
                                <span class="info-label">Por Pagar</span>
                                <span class="info-value">{{ movimiento.es_por_pagar ? 'Sí' : 'No' }}</span>
                            </div>

                            <!-- 🔹 TRASPASO: Cuenta Origen y Destino -->
                            <div class="info-item" v-if="movimiento.es_traspaso">
                                <span class="info-label">Cuenta Origen</span>
                                <span class="info-value">{{ movimiento.cuenta_origen || '—' }}</span>
                            </div>
                            <div class="info-item" v-if="movimiento.es_traspaso">
                                <span class="info-label">Cuenta Destino</span>
                                <span class="info-value">{{ movimiento.cuenta_destino || '—' }}</span>
                            </div>

                            <!-- 🔹 INGRESO/EGRESO: Persona y Cuentas -->
                            <div class="info-item" v-if="movimiento.es_ingreso_egreso">
                                <span class="info-label">Persona</span>
                                <span class="info-value">{{ movimiento.persona || '—' }}</span>
                            </div>
                            <div class="info-item" v-if="movimiento.es_ingreso_egreso">
                                <span class="info-label">Cuenta</span>
                                <span class="info-value">{{ movimiento.cuenta || '—' }}</span>
                            </div>
                            <div class="info-item" v-if="movimiento.es_ingreso_egreso">
                                <span class="info-label">Cuenta Fondeadora</span>
                                <span class="info-value">{{ movimiento.cuenta_fondeadora || '—' }}</span>
                            </div>

                            <div class="info-item">
                                <span class="info-label">Marcador</span>
                                <span class="info-value">{{ movimiento.marcador || '—' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================================ -->
                    <!-- SECCIÓN 2: MONTOS Y DESGLOSE -->
                    <!-- ============================================================ -->
                    <div class="detail-section">
                        <div class="section-header">
                            <div class="section-icon green">
                                <svg class="icon-svg" fill="none" stroke="white" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="section-title">Montos y Desglose</h3>
                                <p class="section-subtitle">Información financiera detallada</p>
                            </div>
                        </div>

                        <!-- 🔹 MONTO TOTAL -->
                        <div class="montos-grid">
                            <div class="monto-card total-card">
                                <span class="monto-label">Monto Total</span>
                                <span class="monto-value" :class="getMontoClass(movimiento.monto)">
                                    ${{ formatNumber(movimiento.monto) }}
                                </span>
                            </div>

                            <!-- 🔹 INGRESO/EGRESO: Base e IVA -->
                            <div class="monto-card" v-if="movimiento.es_ingreso_egreso">
                                <span class="monto-label">Base Gravable</span>
                                <span class="monto-value">${{ formatNumber(movimiento.monto_base) }}</span>
                            </div>
                            <div class="monto-card" v-if="movimiento.es_ingreso_egreso">
                                <span class="monto-label">IVA</span>
                                <span class="monto-value">${{ formatNumber(movimiento.monto_iva) }}</span>
                            </div>

                            <!-- 🔹 TRASPASO: Monto Traspaso -->
                            <div class="monto-card" v-if="movimiento.es_traspaso">
                                <span class="monto-label">Monto Traspaso</span>
                                <span class="monto-value" style="color: #8b5cf6;">${{ formatNumber(movimiento.monto_traspaso) }}</span>
                            </div>
                        </div>

                        <!-- 🔹 DOBLE IVA (si existe) -->
                        <div v-if="movimiento.tiene_doble_iva" class="doble-iva-box">
                            <div class="doble-iva-header">
                                <span class="doble-iva-title">📋 Desglose de Doble IVA</span>
                                <span class="doble-iva-badge">Fiscal</span>
                            </div>
                            <div class="doble-iva-grid">
                                <div class="doble-iva-item">
                                    <span class="doble-iva-label">IVA 0% (Exento)</span>
                                    <span class="doble-iva-value" style="color: #2563eb;">
                                        ${{ formatNumber(movimiento.monto_iva_cero) }}
                                    </span>
                                </div>
                                <div class="doble-iva-item">
                                    <span class="doble-iva-label">IVA 16% (Base)</span>
                                    <span class="doble-iva-value" style="color: #f59e0b;">
                                        ${{ formatNumber(movimiento.monto_iva_dieciseis) }}
                                    </span>
                                </div>
                                <div class="doble-iva-item">
                                    <span class="doble-iva-label">IVA 16% (Calculado)</span>
                                    <span class="doble-iva-value" style="color: #ef4444;">
                                        ${{ formatNumber(movimiento.iva_dieciseis) }}
                                    </span>
                                </div>
                                <div class="doble-iva-item highlight">
                                    <span class="doble-iva-label font-bold">Total Factura</span>
                                    <span class="doble-iva-value font-bold" style="color: #10b981;">
                                        ${{ formatNumber(Number(movimiento.monto_iva_cero) + Number(movimiento.monto_iva_dieciseis) + Number(movimiento.iva_dieciseis)) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================================ -->
                    <!-- SECCIÓN 3: FACTURACIÓN (SOLO SI ES FISCAL) -->
                    <!-- ============================================================ -->
                    <div v-if="movimiento.es_fiscal" class="detail-section">
                        <div class="section-header">
                            <div class="section-icon orange">
                                <svg class="icon-svg" fill="none" stroke="white" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="section-title">Facturación</h3>
                                <p class="section-subtitle">Datos de la factura</p>
                            </div>
                            <div class="docs-actions">
                                <button 
                                    v-if="movimiento.tiene_pdf_fiscal" 
                                    class="btn-doc"
                                    @click="verPdf"
                                >
                                    <svg class="btn-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                    </svg>
                                    PDF
                                </button>
                                <button 
                                    v-if="movimiento.tiene_xml_fiscal" 
                                    class="btn-doc xml"
                                    @click="verXml"
                                >
                                    <svg class="btn-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    XML
                                </button>
                            </div>
                        </div>

                        <div class="info-grid">
                            <div class="info-item">
                                <span class="info-label">Fecha Factura</span>
                                <span class="info-value">{{ formatFecha(movimiento.fecha_factura) || '—' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Número Factura</span>
                                <span class="info-value">{{ movimiento.numero_factura || '—' }}</span>
                            </div>
                            <div class="info-item" v-if="movimiento.serie_factura">
                                <span class="info-label">Serie</span>
                                <span class="info-value">{{ movimiento.serie_factura }}</span>
                            </div>
                            <div class="info-item" v-if="movimiento.folio_factura">
                                <span class="info-label">Folio Factura</span>
                                <span class="info-value">{{ movimiento.folio_factura }}</span>
                            </div>
                            <div class="info-item full-width" v-if="movimiento.uuid_factura">
                                <span class="info-label">UUID</span>
                                <span class="info-value uuid-value">{{ movimiento.uuid_factura }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================================ -->
                    <!-- SECCIÓN 4: ABONOS (SOLO SI ES POR PAGAR) -->
                    <!-- ============================================================ -->
                    <div v-if="movimiento.es_por_pagar && movimiento.abonos && movimiento.abonos.length > 0" class="detail-section">
                        <div class="section-header">
                            <div class="section-icon purple">
                                <svg class="icon-svg" fill="none" stroke="white" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="section-title">Historial de Abonos</h3>
                                <p class="section-subtitle">Registro de pagos realizados</p>
                            </div>
                            <span class="badge-abonos">
                                Total: ${{ formatNumber(movimiento.total_abonado) }}
                            </span>
                        </div>

                        <div class="table-wrapper">
                            <table class="abonos-table">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Monto</th>
                                        <th>Referencia</th>
                                        <th>Método Pago</th>
                                        <th>Nota</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="abono in movimiento.abonos" :key="abono.id">
                                        <td>{{ formatFecha(abono.fecha_abono) }}</td>
                                        <td class="monto-abonado">${{ formatNumber(abono.monto_abonado) }}</td>
                                        <td>{{ abono.referencia || '—' }}</td>
                                        <td><span class="metodo-pago">{{ abono.metodo_pago || '—' }}</span></td>
                                        <td>{{ abono.nota || '—' }}</td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="4" class="total-label">Saldo Pendiente</td>
                                        <td class="total-value" :class="getSaldoPendienteClass(movimiento.saldo_pendiente)">
                                            ${{ formatNumber(movimiento.saldo_pendiente) }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- ============================================================ -->
                    <!-- SECCIÓN 5: USUARIOS Y REVISIONES -->
                    <!-- ============================================================ -->
                    <div class="detail-section">
                        <div class="section-header">
                            <div class="section-icon teal">
                                <svg class="icon-svg" fill="none" stroke="white" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="section-title">Usuarios y Revisiones</h3>
                                <p class="section-subtitle">Flujo de aprobación de la póliza</p>
                            </div>
                        </div>

                        <div class="info-grid">
                            <div class="info-item">
                                <span class="info-label">Creado por</span>
                                <span class="info-value">{{ movimiento.usuario_nombre || movimiento.usuario || '—' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Fecha creación</span>
                                <span class="info-value">{{ formatFechaHora(movimiento.fecha_creacion) }}</span>
                            </div>
                            <div class="info-item" v-if="movimiento.usuario_revisor">
                                <span class="info-label">Revisado por</span>
                                <span class="info-value">{{ movimiento.usuario_revisor }}</span>
                            </div>
                            <div class="info-item" v-if="movimiento.fecha_revision">
                                <span class="info-label">Fecha revisión</span>
                                <span class="info-value">{{ formatFechaHora(movimiento.fecha_revision) }}</span>
                            </div>
                            <div class="info-item" v-if="movimiento.usuario_autorizador">
                                <span class="info-label">Autorizado por</span>
                                <span class="info-value">{{ movimiento.usuario_autorizador }}</span>
                            </div>
                            <div class="info-item" v-if="movimiento.fecha_autorizacion">
                                <span class="info-label">Fecha autorización</span>
                                <span class="info-value">{{ formatFechaHora(movimiento.fecha_autorizacion) }}</span>
                            </div>
                            <div class="info-item full-width" v-if="movimiento.comentario_revision">
                                <span class="info-label">Comentario revisión</span>
                                <span class="info-value comentario">{{ movimiento.comentario_revision }}</span>
                            </div>
                            <div class="info-item full-width" v-if="movimiento.comentario_autorizacion">
                                <span class="info-label">Comentario autorización</span>
                                <span class="info-value comentario">{{ movimiento.comentario_autorizacion }}</span>
                            </div>
                            <div class="info-item full-width" v-if="movimiento.motivo_rechazo">
                                <span class="info-label">Motivo rechazo</span>
                                <span class="info-value comentario rechazo">{{ movimiento.motivo_rechazo }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================================ -->
                    <!-- SECCIÓN 6: NOTA (SI EXISTE) -->
                    <!-- ============================================================ -->
                    <div v-if="movimiento.nota" class="detail-section">
                        <div class="section-header">
                            <div class="section-icon teal">
                                <svg class="icon-svg" fill="none" stroke="white" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="section-title">Observaciones</h3>
                                <p class="section-subtitle">Notas adicionales</p>
                            </div>
                        </div>
                        <div class="nota-content">
                            {{ movimiento.nota }}
                        </div>
                    </div>

                    <!-- ============================================================ -->
                    <!-- BOTONES DE ACCIÓN -->
                    <!-- ============================================================ -->
                    <div class="action-footer no-print">
                        <div class="action-left">
                            <span class="footer-info">
                                Creado: {{ formatFechaHora(movimiento.fecha_creacion) }}
                                <span v-if="movimiento.fecha_actualizacion">
                                    | Actualizado: {{ formatFechaHora(movimiento.fecha_actualizacion) }}
                                </span>
                            </span>
                        </div>
                        <div class="action-right">
                            <button 
                                class="btn-action btn-pdf"
                                @click="accionImprimir"
                            >
                                <svg class="btn-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                                PDF
                            </button>

                            <button 
                                v-if="permisos?.puede_revisar && movimiento.estatus === 'CAPTURADO'"
                                class="btn-action btn-revisar"
                                @click="accionRevisar"
                            >
                                <svg class="btn-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                Revisar
                            </button>

                            <button 
                                v-if="permisos?.puede_autorizar && movimiento.estatus === 'REVISADO'"
                                class="btn-action btn-autorizar"
                                @click="accionAutorizar"
                            >
                                <svg class="btn-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Autorizar
                            </button>

                            <button 
                                v-if="permisos?.puede_editar && movimiento.estatus !== 'LIQUIDADO' && movimiento.estatus !== 'AUTORIZADO'"
                                class="btn-action btn-cerrar"
                                @click="accionCerrar"
                            >
                                <svg class="btn-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Cerrar
                            </button>

                            <Link 
                                v-if="permisos?.puede_editar && movimiento.estatus !== 'LIQUIDADO' && movimiento.estatus !== 'AUTORIZADO'" 
                                :href="route('movimientos.edit', movimiento.id)" 
                                class="btn-action btn-editar"
                            >
                                <svg class="btn-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Editar
                            </Link>

                            <button 
                                v-if="permisos?.puede_eliminar && movimiento.estatus !== 'LIQUIDADO' && movimiento.estatus !== 'AUTORIZADO'" 
                                class="btn-action btn-eliminar"
                                @click="accionEliminar"
                            >
                                <svg class="btn-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Eliminar
                            </button>

                            <Link 
                                :href="route('movimientos.index')" 
                                class="btn-action btn-regresar"
                            >
                                <svg class="btn-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                </svg>
                                Regresar
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <ModalAlert ref="alertRef" />
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import ModalAlert from '@/Components/AlertModal.vue';
import Swal from 'sweetalert2';
import axios from 'axios';

// ============================================
// PERMISOS
// ============================================
const page = usePage();
const permisos = computed(() => page.props.permisos || {});

const props = defineProps({
    movimiento: {
        type: Object,
        required: true
    }
});

const alertRef = ref(null);

const tituloPagina = computed(() => {
    return `Póliza ${props.movimiento.folio || ''}`;
});

// ============================================
// FORMATOS
// ============================================
const formatNumber = (value) => {
    if (value === null || value === undefined || isNaN(value)) return '0.00';
    return Number(value).toLocaleString('es-MX', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
};

const formatFecha = (fecha) => {
    if (!fecha) return null;
    const d = new Date(fecha);
    return d.toLocaleDateString('es-MX', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
};

const formatFechaHora = (fecha) => {
    if (!fecha) return '';
    const d = new Date(fecha);
    return d.toLocaleDateString('es-MX', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    }) + ' | ' + d.toLocaleTimeString('es-MX', {
        hour: '2-digit',
        minute: '2-digit'
    });
};

// ============================================
// CLASES Y ESTILOS
// ============================================
const getTipoTexto = (tipo) => {
    const map = {
        'INGRESO': 'Ingreso',
        'EGRESO': 'Egreso',
        'TRASPASO': 'Traspaso'
    };
    return map[tipo] || tipo || '—';
};

const getTipoClass = (tipo) => {
    const map = {
        'INGRESO': 'tipo-ingreso',
        'EGRESO': 'tipo-egreso',
        'TRASPASO': 'tipo-traspaso'
    };
    return map[tipo] || '';
};

const getEstatusClass = (estatus) => {
    const classes = {
        'CAPTURADO': 'capturado',
        'REVISADO': 'revisado',
        'AUTORIZADO': 'autorizado',
        'ABONADO': 'abonado',
        'LIQUIDADO': 'liquidado',
        'PENDIENTE': 'pendiente'
    };
    return classes[estatus] || 'pendiente';
};

const getCategoriaClass = (categoria) => {
    return categoria === 'FISCAL' ? 'fiscal' : 'no-fiscal';
};

const getMontoClass = (monto) => {
    if (monto > 0) return 'ingreso';
    if (monto < 0) return 'egreso';
    return 'neutro';
};

const getSaldoPendienteClass = (saldo) => {
    if (saldo > 0.01) return 'saldo-positivo';
    if (saldo < -0.01) return 'saldo-negativo';
    return 'saldo-cero';
};

const getVencimientoClass = (fecha) => {
    if (!fecha) return '';
    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0);
    const fechaVenc = new Date(fecha);
    fechaVenc.setHours(0, 0, 0, 0);
    if (fechaVenc < hoy) return 'vencido';
    return 'vigente';
};

const isVencido = (fecha) => {
    if (!fecha) return false;
    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0);
    const fechaVenc = new Date(fecha);
    fechaVenc.setHours(0, 0, 0, 0);
    return fechaVenc < hoy;
};

// ============================================
// ACCIONES
// ============================================
const mostrarModal = (type, title, message) => {
    if (alertRef.value && alertRef.value.show) {
        alertRef.value.show({ type, title, message, buttonText: type === 'error' ? 'Entendido' : 'Aceptar' });
    } else {
        const iconMap = { success: 'success', error: 'error', info: 'info', warning: 'warning' };
        Swal.fire({
            icon: iconMap[type] || 'info',
            title: title || 'Información',
            text: message,
            confirmButtonColor: '#1a3a5c',
            confirmButtonText: 'Aceptar'
        });
    }
};

const verPdf = () => {
    if (props.movimiento.tiene_pdf_fiscal && props.movimiento.pdf_url) {
        window.open(props.movimiento.pdf_url, '_blank');
    } else {
        mostrarModal('info', 'PDF no disponible', 'Esta póliza no tiene un PDF asociado.');
    }
};

const verXml = () => {
    if (props.movimiento.tiene_xml_fiscal && props.movimiento.xml_url) {
        window.open(props.movimiento.xml_url, '_blank');
    } else {
        mostrarModal('info', 'XML no disponible', 'Esta póliza no tiene un XML asociado.');
    }
};

const accionImprimir = () => {
    if (!props.movimiento.id) {
        mostrarModal('error', 'Error', 'No se puede generar el PDF, falta el ID de la póliza.');
        return;
    }
    
    Swal.fire({
        title: 'Generando PDF...',
        text: 'Por favor espera un momento',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    const url = route('movimientos.export.pdf.poliza', props.movimiento.id);
    window.open(url, '_blank');
    
    setTimeout(() => {
        Swal.close();
    }, 1500);
};

// ============================================
// ✅ REVISAR
// ============================================
const accionRevisar = () => {
    Swal.fire({
        title: '¿Revisar póliza?',
        text: 'Confirma que la póliza está correcta para pasarla a REVISADO.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3b82f6',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, revisar',
        cancelButtonText: 'Cancelar',
        input: 'textarea',
        inputPlaceholder: 'Comentario (opcional)...',
        inputAttributes: {
            'aria-label': 'Comentario'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            axios.post(route('movimientos.revisar', props.movimiento.id_poliza), {
                comentario: result.value || null
            }).then(() => {
                mostrarModal('success', 'Póliza revisada', 'La póliza ha sido revisada exitosamente.');
                setTimeout(() => {
                    router.visit(route('movimientos.show', props.movimiento.id));
                }, 1500);
            }).catch((error) => {
                mostrarModal('error', 'Error', error.response?.data?.message || 'Error al revisar la póliza.');
            });
        }
    });
};

// ============================================
// ✅ AUTORIZAR
// ============================================
const accionAutorizar = () => {
    Swal.fire({
        title: '¿Autorizar póliza?',
        text: 'Confirma que la póliza está correcta para pasarla a AUTORIZADO.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, autorizar',
        cancelButtonText: 'Cancelar',
        input: 'textarea',
        inputPlaceholder: 'Comentario (opcional)...',
        inputAttributes: {
            'aria-label': 'Comentario'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            axios.post(route('movimientos.autorizar', props.movimiento.id_poliza), {
                comentario: result.value || null
            }).then(() => {
                mostrarModal('success', 'Póliza autorizada', 'La póliza ha sido autorizada exitosamente.');
                setTimeout(() => {
                    router.visit(route('movimientos.show', props.movimiento.id));
                }, 1500);
            }).catch((error) => {
                mostrarModal('error', 'Error', error.response?.data?.message || 'Error al autorizar la póliza.');
            });
        }
    });
};

// ============================================
// ❌ CERRAR
// ============================================
const accionCerrar = () => {
    Swal.fire({
        title: '¿Cerrar póliza?',
        text: 'Esta acción cerrará la póliza y no podrá ser editada. ¿Estás seguro?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, cerrar',
        cancelButtonText: 'Cancelar',
        input: 'textarea',
        inputPlaceholder: 'Motivo del cierre (opcional)...',
        inputAttributes: {
            'aria-label': 'Motivo del cierre'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            axios.post(route('movimientos.cerrar', props.movimiento.id_poliza), {
                motivo: result.value || 'Cierre manual de póliza'
            }).then(() => {
                mostrarModal('success', 'Póliza cerrada', 'La póliza ha sido cerrada exitosamente.');
                setTimeout(() => {
                    router.visit(route('movimientos.show', props.movimiento.id));
                }, 1500);
            }).catch((error) => {
                mostrarModal('error', 'Error', error.response?.data?.message || 'Error al cerrar la póliza.');
            });
        }
    });
};

// ============================================
// 🗑️ ELIMINAR
// ============================================
const accionEliminar = () => {
    Swal.fire({
        title: '¿Eliminar póliza?',
        text: 'Esta acción no se puede deshacer. ¿Estás seguro?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            axios.delete(route('movimientos.destroy', props.movimiento.id))
                .then(() => {
                    mostrarModal('success', 'Eliminada', 'La póliza ha sido eliminada exitosamente.');
                    setTimeout(() => {
                        router.visit(route('movimientos.index'));
                    }, 1500);
                })
                .catch((error) => {
                    mostrarModal('error', 'Error', error.response?.data?.message || 'Error al eliminar la póliza.');
                });
        }
    });
};
</script>

<style scoped>
/* ========== HEADER ========== */
.header-wrapper {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    padding: 4px 0;
    flex-wrap: wrap;
    gap: 12px;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 14px;
}

.btn-back {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.9);
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: #6b7280;
    transition: all 0.3s ease;
    backdrop-filter: blur(4px);
}

.btn-back:hover {
    background: white;
    color: #1f2937;
    transform: translateX(-3px) scale(1.05);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.header-content {
    display: flex;
    flex-direction: column;
}

.header-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #111827;
    margin: 0;
    line-height: 1.3;
}

.header-subtitle {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.85rem;
    color: #6b7280;
    margin: 0;
    flex-wrap: wrap;
}

.folio-badge {
    display: inline-block;
    padding: 2px 14px;
    background: linear-gradient(135deg, #1a3a5c, #2c5282);
    color: white;
    border-radius: 4px;
    font-weight: 700;
    font-size: 0.85rem;
}

.tipo-badge {
    display: inline-block;
    padding: 2px 14px;
    border-radius: 4px;
    font-weight: 600;
    font-size: 0.8rem;
}

.tipo-badge.tipo-ingreso {
    background: #d1fae5;
    color: #065f46;
}

.tipo-badge.tipo-egreso {
    background: #fee2e2;
    color: #991b1b;
}

.tipo-badge.tipo-traspaso {
    background: #e0e7ff;
    color: #3730a3;
}

.fiscal-badge {
    display: inline-block;
    padding: 2px 12px;
    background: #fef3c7;
    color: #92400e;
    border-radius: 4px;
    font-weight: 700;
    font-size: 0.7rem;
    text-transform: uppercase;
}

.header-divider {
    color: #d1d5db;
}

.header-right {
    display: flex;
    align-items: center;
    gap: 12px;
}

.header-actions {
    display: flex;
    align-items: center;
    gap: 12px;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 24px;
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
}

.status-badge.capturado { background: #f3f4f6; color: #374151; }
.status-badge.revisado { background: #dbeafe; color: #1e40af; }
.status-badge.autorizado { background: #d1fae5; color: #065f46; }
.status-badge.abonado { background: #fef3c7; color: #92400e; }
.status-badge.liquidado { background: #e0e7ff; color: #3730a3; }
.status-badge.pendiente { background: #fef3c7; color: #92400e; }

/* ========== PAGE CONTENT ========== */
.page-content { padding: 1.5rem 0; }
.container-custom { max-width: 80rem; margin: 0 auto; padding: 0 1.5rem; }

/* ========== SUMMARY CARD ========== */
.summary-card {
    background: linear-gradient(135deg, #1a3a5c 0%, #2c5282 100%);
    border-radius: 16px;
    padding: 20px 28px;
    margin-bottom: 24px;
    box-shadow: 0 8px 32px rgba(26, 58, 92, 0.25);
    position: relative;
    overflow: hidden;
}

.summary-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 16px;
    position: relative;
    z-index: 1;
}

.summary-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
    padding: 10px 14px;
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.06);
    transition: all 0.3s ease;
}

.summary-item:hover {
    background: rgba(255, 255, 255, 0.12);
    transform: translateY(-2px);
}

.summary-item.highlight {
    background: rgba(255, 255, 255, 0.15);
    border-color: rgba(255, 255, 255, 0.15);
}

.summary-label {
    font-size: 0.65rem;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.6);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.summary-value {
    font-size: 1rem;
    font-weight: 600;
    color: #ffffff;
    word-break: break-word;
}

.summary-value.monto-principal {
    font-size: 1.3rem;
    font-weight: 700;
}

.summary-value.monto-principal.ingreso { color: #6ee7b7; }
.summary-value.monto-principal.egreso { color: #fca5a5; }
.summary-value.abonado-value { color: #93c5fd; }

.folio-principal {
    font-weight: 700 !important;
    font-size: 1.2rem !important;
    color: #ffffff !important;
}

/* ========== DETAIL CARD ========== */
.detail-card {
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
    border: 1px solid #f1f5f9;
    padding: 2rem;
    transition: all 0.3s ease;
}

/* ========== SECTIONS ========== */
.detail-section {
    margin-bottom: 2rem;
    padding-bottom: 2rem;
    border-bottom: 2px solid #f1f5f9;
}

.detail-section:last-of-type {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.section-header {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.section-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 12px;
    color: white;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.section-icon.blue { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
.section-icon.green { background: linear-gradient(135deg, #10b981, #059669); }
.section-icon.orange { background: linear-gradient(135deg, #f59e0b, #d97706); }
.section-icon.purple { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
.section-icon.teal { background: linear-gradient(135deg, #14b8a6, #0d9488); }

.icon-svg { width: 20px; height: 20px; }

.section-title {
    font-size: 1rem;
    font-weight: 600;
    color: #0f172a;
    margin: 0;
}

.section-subtitle {
    font-size: 0.8rem;
    color: #94a3b8;
    margin: 0;
}

/* ========== BADGES ========== */
.badge-categoria {
    padding: 4px 16px;
    border-radius: 50px;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    margin-left: auto;
}

.badge-categoria.fiscal { background: #d1fae5; color: #065f46; }
.badge-categoria.no-fiscal { background: #f3f4f6; color: #374151; }

.badge-abonos {
    padding: 4px 16px;
    background: #dbeafe;
    color: #1e40af;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 700;
    margin-left: auto;
}

.docs-actions {
    display: flex;
    gap: 8px;
    margin-left: auto;
}

.btn-doc {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 14px;
    background: #ef4444;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-doc:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

.btn-doc.xml { background: #3b82f6; }
.btn-doc.xml:hover { box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3); }

.btn-icon-sm { width: 16px; height: 16px; }

/* ========== GRIDS ========== */
.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 12px 24px;
}

.montos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 16px;
}

/* ========== INFO ITEMS ========== */
.info-item {
    display: flex;
    flex-direction: column;
    gap: 2px;
    padding: 10px 14px;
    background: #f8fafc;
    border-radius: 10px;
    border: 1px solid #f1f5f9;
    transition: all 0.3s ease;
}

.info-item:hover {
    background: #f1f5f9;
    border-color: #e2e8f0;
    transform: translateY(-1px);
}

.info-item.highlight {
    background: #eff6ff;
    border-color: #bfdbfe;
}

.info-item.full-width { grid-column: 1 / -1; }

.info-label {
    font-size: 0.65rem;
    font-weight: 600;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.info-value {
    font-size: 0.95rem;
    font-weight: 500;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.info-value.vencido { color: #dc2626; font-weight: 700; }
.info-value.vigente { color: #10b981; }

.info-value.uuid-value {
    font-family: 'Courier New', monospace;
    font-size: 0.75rem;
    color: #475569;
    background: #f1f5f9;
    padding: 4px 10px;
    border-radius: 4px;
}

.info-value.comentario {
    font-style: italic;
    color: #475569;
    background: #f1f5f9;
    padding: 8px 12px;
    border-radius: 6px;
    border-left: 3px solid #3b82f6;
    width: 100%;
}

.info-value.comentario.rechazo {
    border-left-color: #dc2626;
    background: #fef2f2;
    color: #991b1b;
}

.folio-value {
    font-weight: 700 !important;
    color: #1a3a5c !important;
    font-size: 1.05rem !important;
}

.vencido-badge {
    display: inline-block;
    padding: 2px 10px;
    background: #fee2e2;
    color: #991b1b;
    border-radius: 4px;
    font-size: 0.6rem;
    font-weight: 700;
    text-transform: uppercase;
}

/* ========== MONTOS ========== */
.monto-card {
    display: flex;
    flex-direction: column;
    gap: 4px;
    padding: 14px 18px;
    background: #f8fafc;
    border-radius: 10px;
    border: 1px solid #f1f5f9;
    text-align: center;
    transition: all 0.3s ease;
}

.monto-card:hover {
    background: #f1f5f9;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
}

.monto-card.total-card {
    background: linear-gradient(135deg, #eff6ff, #dbeafe);
    border-color: #93c5fd;
}

.monto-label {
    font-size: 0.7rem;
    font-weight: 600;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.monto-value {
    font-size: 1.1rem;
    font-weight: 700;
    color: #0f172a;
}

.monto-value.ingreso { color: #2563eb; }
.monto-value.egreso { color: #dc2626; }
.monto-value.neutro { color: #94a3b8; }

/* ========== DOBLE IVA ========== */
.doble-iva-box {
    margin-top: 16px;
    background: linear-gradient(135deg, #fefce8, #fef3c7);
    border: 2px solid #fcd34d;
    border-radius: 14px;
    padding: 18px 24px;
}

.doble-iva-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 14px;
}

.doble-iva-title {
    font-size: 0.95rem;
    font-weight: 700;
    color: #92400e;
}

.doble-iva-badge {
    padding: 2px 14px;
    background: #92400e;
    color: white;
    border-radius: 20px;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
}

.doble-iva-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 12px;
    background: white;
    padding: 14px 16px;
    border-radius: 10px;
    border: 1px solid #fde68a;
}

.doble-iva-item {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.doble-iva-item.highlight {
    background: #ecfdf5;
    border-radius: 8px;
    padding: 4px 8px;
}

.doble-iva-label {
    font-size: 0.6rem;
    color: #78716c;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.doble-iva-value {
    font-size: 0.95rem;
    font-weight: 600;
    margin-top: 2px;
}

.font-bold { font-weight: 700; }

/* ========== ESTATUS BADGE ========== */
.estatus-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 14px;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.estatus-badge.capturado { background: #f3f4f6; color: #374151; }
.estatus-badge.revisado { background: #dbeafe; color: #1e40af; }
.estatus-badge.autorizado { background: #d1fae5; color: #065f46; }
.estatus-badge.abonado { background: #fef3c7; color: #92400e; }
.estatus-badge.liquidado { background: #e0e7ff; color: #3730a3; }
.estatus-badge.pendiente { background: #fef3c7; color: #92400e; }

/* ========== NOTA ========== */
.nota-content {
    padding: 16px 20px;
    background: #f8fafc;
    border-radius: 10px;
    border-left: 4px solid #14b8a6;
    color: #1f2937;
    line-height: 1.7;
    white-space: pre-wrap;
}

/* ========== TABLA DE ABONOS ========== */
.table-wrapper {
    overflow-x: auto;
    border-radius: 10px;
    border: 1px solid #f1f5f9;
}

.abonos-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.85rem;
}

.abonos-table thead {
    background: linear-gradient(135deg, #f8fafc, #f1f5f9);
}

.abonos-table th {
    padding: 10px 14px;
    text-align: left;
    font-weight: 700;
    color: #1e293b;
    text-transform: uppercase;
    font-size: 0.7rem;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #e2e8f0;
}

.abonos-table td {
    padding: 10px 14px;
    border-bottom: 1px solid #f1f5f9;
    color: #334155;
}

.abonos-table tbody tr:hover { background: #f8fafc; }
.abonos-table tfoot { background: #f8fafc; font-weight: 700; }
.abonos-table tfoot td { padding: 12px 14px; border-top: 2px solid #e2e8f0; }

.monto-abonado { color: #2563eb; font-weight: 600; }

.metodo-pago {
    display: inline-block;
    padding: 2px 10px;
    background: #f1f5f9;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
    color: #475569;
}

.total-label { text-align: right; color: #475569; }
.total-value { font-size: 1.1rem; font-weight: 700; }
.total-value.saldo-positivo { color: #dc2626; }
.total-value.saldo-negativo { color: #2563eb; }
.total-value.saldo-cero { color: #94a3b8; }

/* ========== ACTION FOOTER ========== */
.action-footer {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    padding-top: 24px;
    margin-top: 8px;
    border-top: 2px solid #f1f5f9;
}

@media (min-width: 640px) {
    .action-footer { flex-direction: row; }
}

.action-left { display: flex; align-items: center; }
.footer-info { font-size: 0.8rem; color: #94a3b8; }

.action-right {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    align-items: center;
}

.btn-action {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 20px;
    font-weight: 600;
    border: none;
    border-radius: 10px;
    font-size: 0.85rem;
    transition: all 0.3s ease;
    cursor: pointer;
    text-decoration: none;
}

.btn-action:hover { transform: translateY(-2px); }

.btn-pdf { background: #dc2626; color: white; }
.btn-pdf:hover { background: #b91c1c; box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3); color: white; }

.btn-revisar { background: #dbeafe; color: #1e40af; }
.btn-revisar:hover { background: #bfdbfe; box-shadow: 0 4px 12px rgba(30, 64, 175, 0.2); }

.btn-autorizar { background: #d1fae5; color: #065f46; }
.btn-autorizar:hover { background: #a7f3d0; box-shadow: 0 4px 12px rgba(6, 95, 70, 0.2); }

.btn-cerrar { background: #ef4444; color: white; }
.btn-cerrar:hover { background: #dc2626; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3); color: white; }

.btn-editar { background: linear-gradient(135deg, #667eea, #764ba2); color: white; }
.btn-editar:hover { box-shadow: 0 4px 16px rgba(102, 126, 234, 0.3); color: white; }

.btn-eliminar { background: #fee2e2; color: #991b1b; }
.btn-eliminar:hover { background: #fecaca; box-shadow: 0 4px 12px rgba(153, 27, 27, 0.2); }

.btn-regresar { background: #f1f5f9; color: #475569; }
.btn-regresar:hover { background: #e2e8f0; box-shadow: 0 4px 12px rgba(71, 85, 105, 0.15); }

/* ========== RESPONSIVE ========== */
@media (max-width: 768px) {
    .detail-card { padding: 1.25rem; }
    .info-grid { grid-template-columns: 1fr; }
    .montos-grid { grid-template-columns: 1fr 1fr; }
    .summary-grid { grid-template-columns: repeat(2, 1fr); }
    .doble-iva-grid { grid-template-columns: 1fr 1fr; }
    .header-wrapper { flex-direction: column; align-items: flex-start; }
    .header-right { width: 100%; }
    .header-actions { width: 100%; flex-wrap: wrap; }
    .status-badge { flex: 1; text-align: center; justify-content: center; }
    .badge-categoria, .badge-abonos { margin-left: 0; width: 100%; text-align: center; }
    .docs-actions { margin-left: 0; width: 100%; justify-content: flex-start; }
    .action-footer { flex-direction: column; align-items: stretch; }
    .action-left { justify-content: center; }
    .action-right { justify-content: center; flex-wrap: wrap; }
    .btn-action { flex: 1; justify-content: center; min-width: 120px; }
    .section-header { flex-wrap: wrap; }
    .header-subtitle { flex-wrap: wrap; }
}

@media (max-width: 480px) {
    .summary-grid { grid-template-columns: 1fr; }
    .montos-grid { grid-template-columns: 1fr; }
    .doble-iva-grid { grid-template-columns: 1fr; }
    .header-title { font-size: 1.1rem; }
    .header-subtitle { font-size: 0.75rem; }
    .section-title { font-size: 0.95rem; }
    .action-right { flex-direction: column; width: 100%; }
    .btn-action { width: 100%; justify-content: center; }
    .abonos-table { font-size: 0.75rem; }
    .abonos-table th, .abonos-table td { padding: 6px 10px; }
    .summary-card { padding: 14px 16px; }
    .detail-card { padding: 1rem; }
    .info-item { padding: 8px 12px; }
}

/* ========== PRINT ========== */
@media print {
    .no-print, .btn-back, .btn-doc, .btn-action, .header-actions .status-badge {
        display: none !important;
    }
    body { background: white !important; }
    .page-content { padding: 0 !important; }
    .container-custom { padding: 0 !important; max-width: 100% !important; }
    .detail-card { box-shadow: none !important; border: 1px solid #e5e7eb !important; border-radius: 0 !important; padding: 1rem !important; }
    .summary-card { background: #1a3a5c !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; border-radius: 0 !important; padding: 16px !important; margin-bottom: 16px !important; }
    .summary-item { background: rgba(255,255,255,0.08) !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    .summary-item.highlight { background: rgba(255,255,255,0.15) !important; }
    .section-icon, .badge-categoria, .badge-abonos, .estatus-badge, .tipo-badge, .fiscal-badge {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    .section-icon.blue { background: #3b82f6 !important; }
    .section-icon.green { background: #10b981 !important; }
    .section-icon.orange { background: #f59e0b !important; }
    .section-icon.purple { background: #8b5cf6 !important; }
    .section-icon.teal { background: #14b8a6 !important; }
    .badge-categoria.fiscal { background: #d1fae5 !important; color: #065f46 !important; }
    .badge-categoria.no-fiscal { background: #f3f4f6 !important; color: #374151 !important; }
    .tipo-badge.tipo-ingreso { background: #d1fae5 !important; color: #065f46 !important; }
    .tipo-badge.tipo-egreso { background: #fee2e2 !important; color: #991b1b !important; }
    .tipo-badge.tipo-traspaso { background: #e0e7ff !important; color: #3730a3 !important; }
    .status-badge.capturado { background: #f3f4f6 !important; color: #374151 !important; }
    .status-badge.revisado { background: #dbeafe !important; color: #1e40af !important; }
    .status-badge.autorizado { background: #d1fae5 !important; color: #065f46 !important; }
    .status-badge.abonado { background: #fef3c7 !important; color: #92400e !important; }
    .status-badge.liquidado { background: #e0e7ff !important; color: #3730a3 !important; }
    .monto-card.total-card { background: linear-gradient(135deg, #eff6ff, #dbeafe) !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    .abonos-table thead { background: #f1f5f9 !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    .abonos-table tfoot { background: #f8fafc !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    .action-footer { border-top: 2px solid #e5e7eb !important; }
    .detail-section { page-break-inside: avoid; break-inside: avoid; }
    .summary-card { page-break-after: avoid; break-after: avoid; }
}
</style>