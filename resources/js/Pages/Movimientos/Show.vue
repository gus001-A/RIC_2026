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
                            <span class="tipo-badge" :class="getTipoClass(movimiento.tipo_poliza)">
                                {{ getTipoTexto(movimiento.tipo_poliza) }}
                            </span>
                            <span v-if="movimiento.es_fiscal" class="fiscal-badge">FISCAL</span>
                            <span v-if="movimiento.tiene_doble_iva" class="doble-iva-badge-header">2 IVAs</span>
                            <!-- 🔥 ESTADO DE LA PÓLIZA -->
                            <span class="estatus-badge" :class="getEstatusClass(movimiento.estatus)">
                                {{ getEstatusTexto(movimiento.estatus) }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </template>

        <div class="page-content" id="print-area">
            <div class="container-custom">
                <div class="detail-card">
                    <!-- ============================================================ -->
                    <!-- SECCIÓN 1: INFORMACIÓN GENERAL -->
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
                            <div class="info-item">
                                <span class="info-label">Tipo de Póliza</span>
                                <span class="info-value">{{ getTipoTexto(movimiento.tipo_poliza) }}</span>
                            </div>
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
                                <span class="info-label">Fecha Póliza</span>
                                <span class="info-value">{{ formatFecha(movimiento.fecha_poliza) }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Marcador</span>
                                <span class="info-value">{{ movimiento.marcador || '—' }}</span>
                            </div>

                            <div class="info-item" v-if="movimiento.es_traspaso">
                                <span class="info-label">Cuenta Origen</span>
                                <span class="info-value">{{ movimiento.cuenta_origen || '—' }}</span>
                            </div>
                            <div class="info-item" v-if="movimiento.es_traspaso">
                                <span class="info-label">Cuenta Destino</span>
                                <span class="info-value">{{ movimiento.cuenta_destino || '—' }}</span>
                            </div>

                            <div class="info-item" v-if="movimiento.categoria">
                                <span class="info-label">Categoría</span>
                                <span class="info-value">{{ movimiento.categoria || '—' }}</span>
                            </div>

                            <!-- 🔥 FOLIO DE LA PÓLIZA -->
                            <div class="info-item">
                                <span class="info-label">Folio</span>
                                <span class="info-value">{{ movimiento.folio || '—' }}</span>
                            </div>
                            
                            <!-- 🔥 REFERENCIA -->
                            <div class="info-item" v-if="movimiento.referencia">
                                <span class="info-label">Referencia</span>
                                <span class="info-value">{{ movimiento.referencia }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================================ -->
                    <!-- SECCIÓN 2: MONTOS Y DESGLOSE DE IVA -->
                    <!-- ============================================================ -->
                    <div class="detail-section">
                        <div class="section-header">
                            <div class="section-icon green">
                                <svg class="icon-svg" fill="none" stroke="white" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="section-title">Montos y Desglose de IVA</h3>
                                <p class="section-subtitle">Información financiera detallada</p>
                            </div>
                            <span v-if="movimiento.tiene_doble_iva" class="doble-iva-tag">Doble IVA</span>
                        </div>

                        <div class="montos-grid">
                            <div class="monto-card total-card">
                                <span class="monto-label">Monto Total</span>
                                <span class="monto-value" :class="getMontoClass(movimiento.monto)">
                                    ${{ formatNumber(Math.abs(movimiento.monto)) }}
                                </span>
                            </div>

                            <div class="monto-card" v-if="movimiento.es_ingreso_egreso">
                                <span class="monto-label">Base Gravable</span>
                                <span class="monto-value">${{ formatNumber(Math.abs(movimiento.monto_base)) }}</span>
                            </div>
                            <div class="monto-card" v-if="movimiento.es_ingreso_egreso && !movimiento.tiene_doble_iva">
                                <span class="monto-label">IVA</span>
                                <span class="monto-value">${{ formatNumber(Math.abs(movimiento.monto_iva)) }}</span>
                            </div>

                            <div class="monto-card" v-if="movimiento.es_traspaso">
                                <span class="monto-label">Monto Traspaso</span>
                                <span class="monto-value" style="color: #8b5cf6;">${{ formatNumber(Math.abs(movimiento.monto_traspaso)) }}</span>
                            </div>
                        </div>

                        <!-- DOBLE IVA -->
                        <div v-if="movimiento.tiene_doble_iva" class="doble-iva-box">
                            <div class="doble-iva-header">
                                <span class="doble-iva-title">Desglose de Doble IVA</span>
                                <span class="doble-iva-badge">Fiscal</span>
                            </div>
                            <div class="doble-iva-grid">
                                <div class="doble-iva-item">
                                    <span class="doble-iva-label">IVA 0% (Exento)</span>
                                    <span class="doble-iva-value" style="color: #2563eb;">
                                        ${{ formatNumber(Math.abs(movimiento.monto_iva_cero)) }}
                                    </span>
                                </div>
                                <div class="doble-iva-item">
                                    <span class="doble-iva-label">IVA 16% (Base)</span>
                                    <span class="doble-iva-value" style="color: #f59e0b;">
                                        ${{ formatNumber(Math.abs(movimiento.monto_iva_dieciseis)) }}
                                    </span>
                                </div>
                                <div class="doble-iva-item">
                                    <span class="doble-iva-label">IVA 16% (Impuesto)</span>
                                    <span class="doble-iva-value" style="color: #ef4444;">
                                        ${{ formatNumber(Math.abs(movimiento.iva_dieciseis)) }}
                                    </span>
                                </div>
                                <div class="doble-iva-item highlight">
                                    <span class="doble-iva-label font-bold">Total Factura</span>
                                    <span class="doble-iva-value font-bold" style="color: #10b981;">
                                        ${{ formatNumber(Math.abs(calcularTotalDobleIva)) }}
                                    </span>
                                </div>
                            </div>
                            <div class="doble-iva-footer">
                                <span class="doble-iva-note">
                                    <strong>Nota:</strong> La póliza incluye dos tipos de IVA (0% y 16%). 
                                    El <strong>Total Factura</strong> es la suma de las bases sin IVA.
                                    El IVA 16% (Impuesto) es el cálculo del impuesto sobre la base.
                                </span>
                            </div>
                        </div>

                        <!-- IVA SIMPLE -->
                        <div v-else-if="movimiento.es_ingreso_egreso && movimiento.monto_iva != 0" class="iva-simple-box">
                            <div class="iva-simple-grid">
                                <div class="iva-simple-item">
                                    <span class="iva-simple-label">Tipo de IVA</span>
                                    <span class="iva-simple-value">{{ getNombreIva(movimiento.id_tipo_iva) || 'IVA General' }}</span>
                                </div>
                                <div class="iva-simple-item">
                                    <span class="iva-simple-label">Porcentaje</span>
                                    <span class="iva-simple-value">{{ getPorcentajeIva(movimiento.id_tipo_iva) }}%</span>
                                </div>
                                <div class="iva-simple-item highlight">
                                    <span class="iva-simple-label">Monto IVA</span>
                                    <span class="iva-simple-value" style="color: #f59e0b;">${{ formatNumber(Math.abs(movimiento.monto_iva)) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- SIN IVA -->
                        <div v-else-if="movimiento.es_ingreso_egreso && movimiento.monto_iva == 0 && movimiento.monto_base != 0" class="iva-simple-box iva-cero-box">
                            <div class="iva-simple-grid">
                                <div class="iva-simple-item">
                                    <span class="iva-simple-label">Tipo de IVA</span>
                                    <span class="iva-simple-value">Sin IVA</span>
                                </div>
                                <div class="iva-simple-item">
                                    <span class="iva-simple-label">Porcentaje</span>
                                    <span class="iva-simple-value">0%</span>
                                </div>
                                <div class="iva-simple-item highlight">
                                    <span class="iva-simple-label">Monto IVA</span>
                                    <span class="iva-simple-value" style="color: #94a3b8;">$0.00</span>
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
                            <div class="info-item" v-if="movimiento.uuid_factura">
                                <span class="info-label">UUID</span>
                                <span class="info-value uuid-value">{{ movimiento.uuid_factura }}</span>
                            </div>
                            
                            <!-- PDF - Botón "Ver Factura" estilo profesional -->
                            <div class="info-item" v-if="movimiento.tiene_pdf_fiscal">
                                <span class="info-label">Factura</span>
                                <span class="info-value">
                                    <button class="btn-ver-documento btn-ver-pdf" @click="abrirModalPreview(movimiento.pdf_url, 'pdf')">
                                        <svg class="btn-ver-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        Ver Factura
                                    </button>
                                </span>
                            </div>
                            
                            <!-- XML - Botón "Ver XML" estilo profesional -->
                            <div class="info-item" v-if="movimiento.tiene_xml_fiscal">
                                <span class="info-label">XML</span>
                                <span class="info-value">
                                    <button class="btn-ver-documento btn-ver-xml" @click="abrirModalPreview(movimiento.xml_url, 'xml')">
                                        <svg class="btn-ver-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        Ver XML
                                    </button>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================================ -->
                    <!-- SECCIÓN 4: NOTA (SI EXISTE) -->
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
                            <div class="audit-timeline">
                                <!-- Creado por -->
                                <div class="audit-item created">
                                    <div class="audit-icon created-icon">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <div class="audit-content">
                                        <span class="audit-label">Creado por</span>
                                        <span class="audit-name">{{ movimiento.usuario_nombre || movimiento.usuario || '—' }}</span>
                                        <span class="audit-date">{{ formatFechaHora(movimiento.fecha_creacion) }}</span>
                                    </div>
                                </div>

                                <div v-if="movimiento.usuario_revisor || movimiento.usuario_autorizador" class="audit-connector">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>

                                <div v-if="movimiento.usuario_revisor" class="audit-item reviewed">
                                    <div class="audit-icon reviewed-icon">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <div class="audit-content">
                                        <span class="audit-label">Revisó</span>
                                        <span class="audit-name">{{ movimiento.usuario_revisor }}</span>
                                        <span class="audit-date">{{ formatFechaHora(movimiento.fecha_revision) }}</span>
                                    </div>
                                </div>

                                <div v-if="movimiento.usuario_revisor && movimiento.usuario_autorizador" class="audit-connector">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>

                                <div v-if="movimiento.usuario_autorizador" class="audit-item authorized">
                                    <div class="audit-icon authorized-icon">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                        </svg>
                                    </div>
                                    <div class="audit-content">
                                        <span class="audit-label">Autorizó</span>
                                        <span class="audit-name">{{ movimiento.usuario_autorizador }}</span>
                                        <span class="audit-date">{{ formatFechaHora(movimiento.fecha_autorizacion) }}</span>
                                    </div>
                                </div>

                                <div v-if="movimiento.motivo_rechazo" class="audit-item rejected">
                                    <div class="audit-icon rejected-icon">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <div class="audit-content">
                                        <span class="audit-label">Rechazado</span>
                                        <span class="audit-name rejected">{{ movimiento.motivo_rechazo }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="action-right">
                            <button 
                                class="btn-action btn-pdf"
                                @click="accionImprimir"
                            >
                                <svg class="btn-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                                Imprimir
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
                                v-if="permisos?.puede_cerrar && puedeCerrar()"
                                class="btn-action btn-cerrar"
                                @click="accionCerrar"
                            >
                                <svg class="btn-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Cerrar
                            </button>

                            <button 
                                v-if="permisos?.puede_reabrir && movimiento.estatus === 'CERRADO'"
                                class="btn-action btn-reabrir"
                                @click="accionReabrir"
                            >
                                <svg class="btn-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Reabrir
                            </button>

                            <!-- 🔥 BOTÓN EDITAR - Solo visible si se puede editar -->
                            <Link 
                                v-if="permisos?.puede_editar && puedeEditar()" 
                                :href="route('movimientos.edit', movimiento.id)" 
                                class="btn-action btn-editar"
                            >
                                <svg class="btn-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Editar
                            </Link>

                            <button 
                                v-if="permisos?.puede_eliminar && puedeEliminar()" 
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

        <!-- ============================================================ -->
        <!-- MODAL PARA PREVISUALIZAR PDF/XML -->
        <!-- ============================================================ -->
        <a-modal
            v-model:open="modalPreviewVisible"
            :title="modalPreviewTitle"
            width="90%"
            :footer="null"
            class="modal-preview-premium"
            :style="{ maxWidth: '1200px' }"
        >
            <div class="preview-content">
                <div v-if="modalPreviewTipo === 'pdf'" class="preview-pdf-wrapper">
                    <iframe :src="modalPreviewUrl" class="preview-pdf" frameborder="0"></iframe>
                </div>
                <div v-else-if="modalPreviewTipo === 'xml'" class="preview-xml-wrapper">
                    <div class="preview-xml-content">
                        <div class="preview-xml-header">
                            <svg class="preview-xml-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span>Vista previa no disponible para XML</span>
                        </div>
                        <p class="preview-xml-text">El archivo XML se abrirá en una nueva pestaña para su descarga.</p>
                        <div class="preview-xml-actions">
                            <button class="btn-modal-cancel" @click="cerrarPreview">Cerrar</button>
                            <a :href="modalPreviewUrl" target="_blank" class="btn-modal-submit" download>
                                <svg class="btn-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                Descargar XML
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="preview-footer">
                <button class="btn-modal-cancel" @click="cerrarPreview">Cerrar</button>
                <a :href="modalPreviewUrl" target="_blank" class="btn-modal-submit" download>
                    <svg class="btn-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Descargar
                </a>
            </div>
        </a-modal>

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
import { Modal as AModal } from 'ant-design-vue';

// ============================================
// PERMISOS
// ============================================
const page = usePage();
const permisos = computed(() => page.props.permisos || {});

// ============================================
// PROPS
// ============================================
const props = defineProps({
    movimiento: {
        type: Object,
        required: true
    }
});

const alertRef = ref(null);

const tituloPagina = computed(() => {
    return `Detalle de Póliza`;
});

// ============================================
// 🔥 FUNCIONES PARA ESTADOS - NUEVAS
// ============================================
const getEstatusTexto = (estatus) => {
    const map = {
        'CAPTURADO': 'Capturado',
        'REVISADO': 'Revisado',
        'AUTORIZADO': 'Autorizado',
        'ABONADO': 'Abonado',
        'LIQUIDADO': 'Liquidado',
        'PENDIENTE': 'Pendiente',
        'CERRADO': 'Cerrado'
    };
    return map[estatus] || estatus || '—';
};

const getEstatusClass = (estatus) => {
    const map = {
        'CAPTURADO': 'estatus-capturado',
        'REVISADO': 'estatus-revisado',
        'AUTORIZADO': 'estatus-autorizado',
        'ABONADO': 'estatus-abonado',
        'LIQUIDADO': 'estatus-liquidado',
        'PENDIENTE': 'estatus-pendiente',
        'CERRADO': 'estatus-cerrado'
    };
    return map[estatus] || 'estatus-default';
};

// ============================================
// 🔥 FUNCIONES PARA DETERMINAR SI PUEDE EDITAR/ELIMINAR
// ============================================
const puedeEditar = () => {
    // Si es super usuario, siempre puede editar
    if (permisos.value?.es_super_usuario) {
        return true;
    }
    
    // Si es auditor, no puede editar
    if (permisos.value?.es_auditor) {
        return false;
    }
    
    const estatus = props.movimiento.estatus;
    
    // 🔥 Estados NO EDITABLES
    const estatusNoEditables = ['REVISADO', 'AUTORIZADO', 'LIQUIDADO', 'CERRADO', 'ABONADO'];
    
    // Si el estado está en la lista de no editables, no se puede editar
    if (estatusNoEditables.includes(estatus)) {
        return false;
    }
    
    // CAPTURADO y PENDIENTE son editables
    return true;
};

const puedeEliminar = () => {
    // Si es super usuario, siempre puede eliminar
    if (permisos.value?.es_super_usuario) {
        return true;
    }
    
    // Si es auditor, no puede eliminar
    if (permisos.value?.es_auditor) {
        return false;
    }
    
    const estatus = props.movimiento.estatus;
    
    // 🔥 Estados NO ELIMINABLES
    const estatusNoEliminables = ['REVISADO', 'AUTORIZADO', 'LIQUIDADO', 'CERRADO', 'ABONADO'];
    
    if (estatusNoEliminables.includes(estatus)) {
        return false;
    }
    
    return true;
};

const puedeCerrar = () => {
    const estatus = props.movimiento.estatus;
    
    // No se puede cerrar si ya está liquidado, autorizado o cerrado
    const estatusNoCerrar = ['LIQUIDADO', 'AUTORIZADO', 'CERRADO'];
    
    if (estatusNoCerrar.includes(estatus)) {
        return false;
    }
    
    return true;
};

// ============================================
// COMPUTED - Total factura = SUMA DE BASES (sin IVA)
// ============================================
const calcularTotalDobleIva = computed(() => {
    const cero = Number(props.movimiento.monto_iva_cero) || 0;
    const dieciseisBase = Number(props.movimiento.monto_iva_dieciseis) || 0;
    return cero + dieciseisBase;
});

// ============================================
// MODAL DE PREVISUALIZACIÓN
// ============================================
const modalPreviewVisible = ref(false);
const modalPreviewUrl = ref('');
const modalPreviewTipo = ref('pdf');
const modalPreviewTitle = ref('');

const abrirModalPreview = (url, tipo) => {
    if (!url) {
        mostrarModal('info', 'Documento no disponible', 'Este documento no está disponible para visualización.');
        return;
    }
    
    modalPreviewUrl.value = url;
    modalPreviewTipo.value = tipo;
    modalPreviewTitle.value = tipo === 'pdf' ? 'Factura PDF' : 'Documento XML';
    modalPreviewVisible.value = true;
};

const cerrarPreview = () => {
    modalPreviewVisible.value = false;
    modalPreviewUrl.value = '';
    modalPreviewTipo.value = 'pdf';
    modalPreviewTitle.value = '';
};

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

const getCategoriaClass = (categoria) => {
    return categoria === 'FISCAL' ? 'fiscal' : 'no-fiscal';
};

const getMontoClass = (monto) => {
    if (monto > 0) return 'ingreso';
    if (monto < 0) return 'egreso';
    return 'neutro';
};

const getNombreIva = (id) => {
    if (!id) return 'IVA General';
    const tiposIva = window.tiposIva || [];
    const iva = tiposIva.find(i => i.id === id);
    return iva ? iva.nombre : 'IVA General';
};

const getPorcentajeIva = (id) => {
    if (!id) return 0;
    const tiposIva = window.tiposIva || [];
    const iva = tiposIva.find(i => i.id === id);
    return iva ? iva.porcentaje : 0;
};

// ============================================
// MODAL / ALERTAS
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

// ============================================
// ACCIONES
// ============================================
const accionImprimir = () => {
    if (!props.movimiento.id) {
        mostrarModal('error', 'Error', 'No se puede generar el PDF, falta el ID de la póliza.');
        return;
    }
    
    const url = route('movimientos.imprimir', props.movimiento.id);
    window.open(url, '_blank');
};

// ============================================
// REVISAR
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
// AUTORIZAR
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
// CERRAR
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
// REABRIR
// ============================================
const accionReabrir = () => {
    Swal.fire({
        title: '¿Reabrir póliza?',
        text: 'Esta acción reabrirá la póliza y la dejará en estado CAPTURADO para poder editarla. ¿Estás seguro?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3b82f6',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, reabrir',
        cancelButtonText: 'Cancelar',
        input: 'textarea',
        inputPlaceholder: 'Motivo de la reapertura (opcional)...',
        inputAttributes: {
            'aria-label': 'Motivo de la reapertura'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            axios.post(route('movimientos.reabrir', props.movimiento.id_poliza), {
                motivo: result.value || 'Reapertura manual de póliza'
            }).then(() => {
                mostrarModal('success', 'Póliza reabierta', 'La póliza ha sido reabierta exitosamente.');
                setTimeout(() => {
                    router.visit(route('movimientos.show', props.movimiento.id));
                }, 1500);
            }).catch((error) => {
                mostrarModal('error', 'Error', error.response?.data?.message || 'Error al reabrir la póliza.');
            });
        }
    });
};

// ============================================
// ELIMINAR
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

/* ========== BADGES ========== */
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

.doble-iva-badge-header {
    display: inline-block;
    padding: 2px 12px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border-radius: 4px;
    font-weight: 700;
    font-size: 0.7rem;
    text-transform: uppercase;
}

/* 🔥 ESTADOS BADGE */
.estatus-badge {
    display: inline-block;
    padding: 2px 14px;
    border-radius: 50px;
    font-weight: 700;
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    border: 1.5px solid transparent;
}

.estatus-capturado {
    background: #f3f4f6;
    color: #374151;
    border-color: #d1d5db;
}

.estatus-revisado {
    background: #dbeafe;
    color: #1e40af;
    border-color: #93c5fd;
}

.estatus-autorizado {
    background: #d1fae5;
    color: #065f46;
    border-color: #6ee7b7;
}

.estatus-abonado {
    background: #fef3c7;
    color: #92400e;
    border-color: #fcd34d;
}

.estatus-liquidado {
    background: #e0e7ff;
    color: #3730a3;
    border-color: #818cf8;
}

.estatus-pendiente {
    background: #fed7aa;
    color: #9a3412;
    border-color: #fdba74;
}

.estatus-cerrado {
    background: #e5e7eb;
    color: #4b5563;
    border-color: #9ca3af;
}

.estatus-default {
    background: #f3f4f6;
    color: #6b7280;
    border-color: #d1d5db;
}

/* ========== PAGE CONTENT ========== */
.page-content { padding: 1.5rem 0; }
.container-custom { max-width: 80rem; margin: 0 auto; padding: 0 1.5rem; }

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

.doble-iva-tag {
    padding: 4px 14px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border-radius: 20px;
    font-size: 0.7rem;
    font-weight: 700;
    margin-left: auto;
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

.btn-icon-sm { width: 16px; height: 16px; }
.w-4 { width: 16px; height: 16px; }
.h-4 { width: 16px; height: 16px; }
.w-3 { width: 12px; height: 12px; }
.h-3 { width: 12px; height: 12px; }

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

.info-value.uuid-value {
    font-family: 'Courier New', monospace;
    font-size: 0.75rem;
    color: #475569;
    background: #f1f5f9;
    padding: 4px 10px;
    border-radius: 4px;
}

/* ============================================================ */
/* BOTONES VER DOCUMENTO */
/* ============================================================ */
.btn-ver-documento {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 18px 6px 14px;
    border: 1.5px solid #e2e8f0;
    border-radius: 8px;
    font-weight: 500;
    font-size: 0.8rem;
    cursor: pointer;
    transition: all 0.3s ease;
    font-family: inherit;
    background: #f8fafc;
    color: #1e293b;
    text-decoration: none;
}

.btn-ver-documento:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
}

.btn-ver-documento:active {
    transform: translateY(0px) scale(0.98);
}

.btn-ver-pdf {
    background: #f8fafc;
    border-color: #e2e8f0;
    color: #1e293b;
}

.btn-ver-pdf:hover {
    background: #ffffff;
    border-color: #3b82f6;
    color: #1e40af;
    box-shadow: 0 4px 16px rgba(59, 130, 246, 0.15);
}

.btn-ver-pdf .btn-ver-icon {
    color: #3b82f6;
}

.btn-ver-xml {
    background: #f8fafc;
    border-color: #e2e8f0;
    color: #1e293b;
}

.btn-ver-xml:hover {
    background: #ffffff;
    border-color: #8b5cf6;
    color: #5b21b6;
    box-shadow: 0 4px 16px rgba(139, 92, 246, 0.15);
}

.btn-ver-xml .btn-ver-icon {
    color: #8b5cf6;
}

.btn-ver-icon {
    width: 16px;
    height: 16px;
    flex-shrink: 0;
}

/* ============================================================ */
/* MODAL PREVIEW */
/* ============================================================ */
.modal-preview-premium :deep(.ant-modal-header) {
    background: linear-gradient(135deg, #1a3a5c, #2c5282);
    border-radius: 8px 8px 0 0;
    padding: 16px 24px;
}

.modal-preview-premium :deep(.ant-modal-title) {
    color: white;
    font-weight: 700;
    font-size: 1.1rem;
}

.modal-preview-premium :deep(.ant-modal-close) {
    color: white;
}

.modal-preview-premium :deep(.ant-modal-close:hover) {
    color: #fca5a5;
}

.modal-preview-premium :deep(.ant-modal-body) {
    padding: 0;
}

.preview-content {
    min-height: 400px;
    max-height: 80vh;
    overflow: auto;
    background: #f8fafc;
}

.preview-pdf-wrapper {
    width: 100%;
    height: 75vh;
    min-height: 500px;
}

.preview-pdf {
    width: 100%;
    height: 100%;
    border: none;
}

/* XML Preview */
.preview-xml-wrapper {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 400px;
    padding: 40px;
}

.preview-xml-content {
    text-align: center;
    max-width: 500px;
}

.preview-xml-header {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    margin-bottom: 16px;
}

.preview-xml-icon {
    width: 48px;
    height: 48px;
    color: #7c3aed;
}

.preview-xml-header span {
    font-size: 1.1rem;
    font-weight: 600;
    color: #1f2937;
}

.preview-xml-text {
    color: #6b7280;
    font-size: 0.95rem;
    margin-bottom: 24px;
}

.preview-xml-actions {
    display: flex;
    gap: 12px;
    justify-content: center;
}

.preview-footer {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding: 16px 24px;
    background: white;
    border-top: 1px solid #f3f4f6;
}

.btn-modal-cancel {
    padding: 8px 24px;
    background: #f1f5f9;
    color: #64748b;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.85rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-modal-cancel:hover {
    background: #e2e8f0;
}

.btn-modal-submit {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 24px;
    background: linear-gradient(135deg, #1a3a5c, #2c5282);
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.85rem;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
}

.btn-modal-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(26, 58, 92, 0.3);
    color: white;
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
    transition: all 0.3s ease;
}

.doble-iva-box:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(252, 211, 77, 0.2);
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
    text-align: center;
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

.doble-iva-footer {
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid #fde68a;
}

.doble-iva-note {
    font-size: 0.8rem;
    color: #78716c;
}

.font-bold { font-weight: 700; }

/* ========== IVA SIMPLE ========== */
.iva-simple-box {
    margin-top: 16px;
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
    border: 2px solid #86efac;
    border-radius: 14px;
    padding: 16px 20px;
    transition: all 0.3s ease;
}

.iva-simple-box:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(134, 239, 172, 0.2);
}

.iva-simple-box.iva-cero-box {
    background: linear-gradient(135deg, #f8fafc, #f1f5f9);
    border-color: #cbd5e1;
}

.iva-simple-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 12px;
}

.iva-simple-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

.iva-simple-item.highlight {
    background: rgba(255, 255, 255, 0.5);
    border-radius: 8px;
    padding: 4px 8px;
}

.iva-simple-label {
    font-size: 0.6rem;
    color: #78716c;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.iva-simple-value {
    font-size: 0.95rem;
    font-weight: 600;
    margin-top: 2px;
}

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

@media (min-width: 768px) {
    .action-footer {
        flex-direction: row;
        align-items: stretch;
    }
}

/* ===== AUDIT TIMELINE ===== */
.action-left {
    display: flex;
    align-items: center;
    flex: 1;
    min-width: 0;
}

.audit-timeline {
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
    background: #f8fafc;
    padding: 8px 16px 8px 12px;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    width: 100%;
}

.audit-item {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px 4px 6px;
    border-radius: 8px;
    transition: all 0.2s ease;
}

.audit-item:hover {
    transform: translateY(-1px);
}

.audit-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    flex-shrink: 0;
}

.created-icon {
    background: #dbeafe;
    color: #1e40af;
}

.reviewed-icon {
    background: #d1fae5;
    color: #065f46;
}

.authorized-icon {
    background: #e0e7ff;
    color: #3730a3;
}

.rejected-icon {
    background: #fee2e2;
    color: #991b1b;
}

.audit-content {
    display: flex;
    align-items: center;
    gap: 4px;
    flex-wrap: wrap;
}

.audit-label {
    font-size: 0.6rem;
    font-weight: 600;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.audit-name {
    font-size: 0.8rem;
    font-weight: 700;
    color: #0f172a;
}

.audit-name.rejected {
    color: #991b1b;
}

.audit-date {
    font-size: 0.65rem;
    color: #94a3b8;
    font-weight: 500;
}

.audit-connector {
    display: flex;
    align-items: center;
    color: #cbd5e1;
    flex-shrink: 0;
}

/* ===== BOTONES ===== */
.action-right {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
    flex-shrink: 0;
}

.btn-action {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 18px;
    font-weight: 600;
    border: none;
    border-radius: 10px;
    font-size: 0.8rem;
    transition: all 0.3s ease;
    cursor: pointer;
    text-decoration: none;
    white-space: nowrap;
}

.btn-action:hover {
    transform: translateY(-2px);
}

.btn-action:active {
    transform: translateY(0px) scale(0.97);
}

.btn-pdf {
    background: #dc2626;
    color: white;
}
.btn-pdf:hover {
    background: #b91c1c;
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
    color: white;
}

.btn-revisar {
    background: #dbeafe;
    color: #1e40af;
}
.btn-revisar:hover {
    background: #bfdbfe;
    box-shadow: 0 4px 12px rgba(30, 64, 175, 0.2);
}

.btn-autorizar {
    background: #d1fae5;
    color: #065f46;
}
.btn-autorizar:hover {
    background: #a7f3d0;
    box-shadow: 0 4px 12px rgba(6, 95, 70, 0.2);
}

.btn-cerrar {
    background: #ef4444;
    color: white;
}
.btn-cerrar:hover {
    background: #dc2626;
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
    color: white;
}

.btn-reabrir {
    background: #dbeafe;
    color: #1e40af;
}
.btn-reabrir:hover {
    background: #bfdbfe;
    box-shadow: 0 4px 12px rgba(30, 64, 175, 0.2);
}

.btn-editar {
    background: #fef3c7;
    color: #92400e;
    border: 1.5px solid #fcd34d;
}
.btn-editar:hover {
    background: #fde68a;
    box-shadow: 0 4px 12px rgba(146, 64, 14, 0.15);
}

.btn-eliminar {
    background: #fee2e2;
    color: #991b1b;
}
.btn-eliminar:hover {
    background: #fecaca;
    box-shadow: 0 4px 12px rgba(153, 27, 27, 0.2);
}

.btn-regresar {
    background: #f1f5f9;
    color: #475569;
}
.btn-regresar:hover {
    background: #e2e8f0;
    box-shadow: 0 4px 12px rgba(71, 85, 105, 0.15);
}

/* ============================================================ */
/* RESPONSIVE */
/* ============================================================ */
@media (max-width: 768px) {
    .detail-card { padding: 1.25rem; }
    .info-grid { grid-template-columns: 1fr; }
    .montos-grid { grid-template-columns: 1fr 1fr; }
    .doble-iva-grid { grid-template-columns: 1fr 1fr; }
    .header-wrapper { flex-direction: column; align-items: flex-start; }
    .badge-categoria { margin-left: 0; width: 100%; text-align: center; }
    .section-header { flex-wrap: wrap; }
    .header-subtitle { flex-wrap: wrap; }
    .doble-iva-tag { margin-left: 0; width: 100%; text-align: center; }
    .action-footer { flex-direction: column; align-items: stretch; gap: 12px; }
    .action-left { width: 100%; }
    .audit-timeline { padding: 6px 12px; gap: 4px; justify-content: center; }
    .audit-item { padding: 3px 6px; }
    .audit-label { font-size: 0.5rem; }
    .audit-name { font-size: 0.7rem; }
    .audit-date { font-size: 0.55rem; }
    .audit-connector { display: none; }
    .action-right { justify-content: center; width: 100%; }
    .btn-action { flex: 1; justify-content: center; min-width: 80px; padding: 6px 12px; font-size: 0.7rem; }
    .btn-ver-documento { font-size: 0.7rem; padding: 4px 12px; }
    .preview-pdf-wrapper { height: 60vh; min-height: 300px; }
}

@media (max-width: 480px) {
    .info-grid { grid-template-columns: 1fr; }
    .montos-grid { grid-template-columns: 1fr; }
    .doble-iva-grid { grid-template-columns: 1fr; }
    .iva-simple-grid { grid-template-columns: 1fr; }
    .header-title { font-size: 1.1rem; }
    .header-subtitle { font-size: 0.75rem; }
    .section-title { font-size: 0.95rem; }
    .detail-card { padding: 1rem; }
    .info-item { padding: 8px 12px; }
    .doble-iva-box { padding: 12px 16px; }
    .iva-simple-box { padding: 12px 16px; }
    .audit-timeline { flex-wrap: wrap; justify-content: center; gap: 2px; }
    .audit-item { padding: 2px 4px; }
    .audit-icon { width: 18px; height: 18px; }
    .audit-icon .w-4 { width: 12px; height: 12px; }
    .audit-icon .h-4 { width: 12px; height: 12px; }
    .audit-label { font-size: 0.45rem; }
    .audit-name { font-size: 0.65rem; }
    .audit-date { font-size: 0.5rem; }
    .action-right { flex-direction: column; width: 100%; }
    .btn-action { width: 100%; justify-content: center; }
    .preview-pdf-wrapper { height: 50vh; min-height: 250px; }
}

/* ============================================================ */
/* PRINT */
/* ============================================================ */
@media print {
    .no-print, .btn-back, .btn-action, .btn-ver-documento {
        display: none !important;
    }
    body { background: white !important; }
    .page-content { padding: 0 !important; }
    .container-custom { padding: 0 !important; max-width: 100% !important; }
    .detail-card { box-shadow: none !important; border: 1px solid #e5e7eb !important; border-radius: 0 !important; padding: 1rem !important; }
    .section-icon, .badge-categoria, .tipo-badge, .fiscal-badge, .doble-iva-badge-header, .doble-iva-tag, .estatus-badge {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    .section-icon.blue { background: #3b82f6 !important; }
    .section-icon.green { background: #10b981 !important; }
    .section-icon.orange { background: #f59e0b !important; }
    .section-icon.teal { background: #14b8a6 !important; }
    .badge-categoria.fiscal { background: #d1fae5 !important; color: #065f46 !important; }
    .badge-categoria.no-fiscal { background: #f3f4f6 !important; color: #374151 !important; }
    .tipo-badge.tipo-ingreso { background: #d1fae5 !important; color: #065f46 !important; }
    .tipo-badge.tipo-egreso { background: #fee2e2 !important; color: #991b1b !important; }
    .tipo-badge.tipo-traspaso { background: #e0e7ff !important; color: #3730a3 !important; }
    .estatus-capturado { background: #f3f4f6 !important; color: #374151 !important; border-color: #d1d5db !important; }
    .estatus-revisado { background: #dbeafe !important; color: #1e40af !important; border-color: #93c5fd !important; }
    .estatus-autorizado { background: #d1fae5 !important; color: #065f46 !important; border-color: #6ee7b7 !important; }
    .estatus-abonado { background: #fef3c7 !important; color: #92400e !important; border-color: #fcd34d !important; }
    .estatus-liquidado { background: #e0e7ff !important; color: #3730a3 !important; border-color: #818cf8 !important; }
    .estatus-pendiente { background: #fed7aa !important; color: #9a3412 !important; border-color: #fdba74 !important; }
    .estatus-cerrado { background: #e5e7eb !important; color: #4b5563 !important; border-color: #9ca3af !important; }
    .monto-card.total-card { background: linear-gradient(135deg, #eff6ff, #dbeafe) !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    .doble-iva-box { background: #fefce8 !important; border-color: #fcd34d !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    .doble-iva-badge { background: #92400e !important; color: white !important; }
    .iva-simple-box { background: #f0fdf4 !important; border-color: #86efac !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    .action-footer { border-top: 2px solid #e5e7eb !important; }
    .detail-section { page-break-inside: avoid; break-inside: avoid; }
    .audit-timeline { background: #f8fafc !important; border: 1px solid #e5e7eb !important; }
    .created-icon { background: #dbeafe !important; }
    .reviewed-icon { background: #d1fae5 !important; }
    .authorized-icon { background: #e0e7ff !important; }
    .rejected-icon { background: #fee2e2 !important; }
}
</style>