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
                            <span v-if="movimiento.archivos_adjuntos && movimiento.archivos_adjuntos.length > 0" class="archivos-badge">
                                📎 {{ movimiento.archivos_adjuntos.length }} archivo(s)
                            </span>
                        </p>
                    </div>
                </div>
                <div class="header-right">
                    <div class="header-actions">
                        <span class="capturista-info" v-if="movimiento.usuario_nombre">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Capturó: {{ movimiento.usuario_nombre }}
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

                        <div class="summary-item" v-if="movimiento.es_traspaso">
                            <span class="summary-label">Monto Traspaso</span>
                            <span class="summary-value" style="color: #8b5cf6;">${{ formatNumber(movimiento.monto_traspaso) }}</span>
                        </div>

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
                    </div>
                </div>

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

                            <div class="info-item" v-if="movimiento.es_traspaso">
                                <span class="info-label">Cuenta Origen</span>
                                <span class="info-value">{{ movimiento.cuenta_origen || '—' }}</span>
                            </div>
                            <div class="info-item" v-if="movimiento.es_traspaso">
                                <span class="info-label">Cuenta Destino</span>
                                <span class="info-value">{{ movimiento.cuenta_destino || '—' }}</span>
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
                                <span class="info-label">Marcador</span>
                                <span class="info-value">{{ movimiento.marcador || '—' }}</span>
                            </div>
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
                            <span v-if="movimiento.tiene_doble_iva" class="doble-iva-tag">📋 Doble IVA</span>
                        </div>

                        <div class="montos-grid">
                            <div class="monto-card total-card">
                                <span class="monto-label">Monto Total</span>
                                <span class="monto-value" :class="getMontoClass(movimiento.monto)">
                                    ${{ formatNumber(movimiento.monto) }}
                                </span>
                            </div>

                            <div class="monto-card" v-if="movimiento.es_ingreso_egreso">
                                <span class="monto-label">Base Gravable</span>
                                <span class="monto-value">${{ formatNumber(movimiento.monto_base) }}</span>
                            </div>
                            <div class="monto-card" v-if="movimiento.es_ingreso_egreso && !movimiento.tiene_doble_iva">
                                <span class="monto-label">IVA</span>
                                <span class="monto-value">${{ formatNumber(movimiento.monto_iva) }}</span>
                            </div>

                            <div class="monto-card" v-if="movimiento.es_traspaso">
                                <span class="monto-label">Monto Traspaso</span>
                                <span class="monto-value" style="color: #8b5cf6;">${{ formatNumber(movimiento.monto_traspaso) }}</span>
                            </div>
                        </div>

                        <!-- DOBLE IVA -->
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
                                    <span class="doble-iva-label">IVA 16% (Impuesto)</span>
                                    <span class="doble-iva-value" style="color: #ef4444;">
                                        ${{ formatNumber(movimiento.iva_dieciseis) }}
                                    </span>
                                </div>
                                <div class="doble-iva-item highlight">
                                    <span class="doble-iva-label font-bold">Total Factura</span>
                                    <span class="doble-iva-value font-bold" style="color: #10b981;">
                                        ${{ formatNumber(calcularTotalDobleIva) }}
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
                                    <span class="iva-simple-value" style="color: #f59e0b;">${{ formatNumber(movimiento.monto_iva) }}</span>
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
                            <div class="info-item full-width" v-if="movimiento.tiene_pdf_fiscal && movimiento.nombre_pdf">
                                <span class="info-label">Nombre PDF</span>
                                <span class="info-value pdf-name">{{ movimiento.nombre_pdf }}</span>
                            </div>
                            <div class="info-item full-width" v-if="movimiento.tiene_xml_fiscal && movimiento.nombre_xml">
                                <span class="info-label">Nombre XML</span>
                                <span class="info-value pdf-name">{{ movimiento.nombre_xml }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================================ -->
                    <!-- SECCIÓN 4: ARCHIVOS ADJUNTOS -->
                    <!-- ============================================================ -->
                    <div class="detail-section">
                        <div class="section-header">
                            <div class="section-icon purple">
                                <svg class="icon-svg" fill="none" stroke="white" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="section-title">Archivos Adjuntos</h3>
                                <p class="section-subtitle">Documentos relacionados con la póliza</p>
                            </div>
                            <button 
                                v-if="puedeSubirArchivos()" 
                                class="btn-adjuntar"
                                @click="abrirModalArchivos"
                            >
                                <svg class="btn-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Adjuntar archivo
                            </button>
                        </div>

                        <div v-if="movimiento.archivos_adjuntos && movimiento.archivos_adjuntos.length > 0" class="archivos-grid">
                            <div v-for="archivo in movimiento.archivos_adjuntos" :key="archivo.id" class="archivo-card">
                                <div class="archivo-icon" :class="getArchivoClase(archivo.tipo_archivo)">
                                    <svg class="archivo-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path v-if="archivo.tipo_archivo === 'pdf'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                        <path v-else-if="archivo.tipo_archivo === 'image'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <div class="archivo-info">
                                    <span class="archivo-nombre">{{ archivo.nombre_original }}</span>
                                    <span class="archivo-tamano">{{ formatFileSize(archivo.tamano) }}</span>
                                    <span class="archivo-fecha">{{ formatFecha(archivo.created_at) }}</span>
                                </div>
                                <div class="archivo-actions">
                                    <button 
                                        class="archivo-btn ver"
                                        @click="verArchivo(archivo)"
                                        title="Ver archivo"
                                    >
                                        <svg class="btn-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </button>
                                    <button 
                                        v-if="puedeSubirArchivos()" 
                                        class="archivo-btn eliminar"
                                        @click="eliminarArchivo(archivo)"
                                        title="Eliminar archivo"
                                    >
                                        <svg class="btn-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div v-else class="no-archivos">
                            <span>No hay archivos adjuntos</span>
                        </div>
                    </div>

                    <!-- ============================================================ -->
                    <!-- SECCIÓN 5: ABONOS (SOLO SI ES POR PAGAR) -->
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
                                v-if="permisos?.puede_cerrar && movimiento.estatus !== 'LIQUIDADO' && movimiento.estatus !== 'AUTORIZADO' && movimiento.estatus !== 'CERRADO'"
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
                                v-if="permisos?.puede_eliminar" 
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
        <!-- MODAL PARA SUBIR ARCHIVOS -->
        <!-- ============================================================ -->
        <a-modal
            v-model:open="modalArchivosVisible"
            title="Adjuntar archivo"
            width="550px"
            :footer="null"
            class="modal-archivos-premium"
        >
            <div class="modal-archivos-content">
                <div class="modal-archivos-info">
                    <p>Selecciona un archivo para adjuntar a esta póliza. Puedes subir <strong>PDFs</strong> o <strong>imágenes</strong> (JPG, PNG, GIF).</p>
                </div>

                <form @submit.prevent="subirArchivo">
                    <div class="drop-zone" 
                         :class="{ 'drop-zone-dragover': dragging }"
                         @dragover.prevent="dragging = true"
                         @dragleave.prevent="dragging = false"
                         @drop.prevent="onDrop"
                         @click="$refs.fileInput.click()"
                    >
                        <div class="drop-zone-content">
                            <svg class="drop-zone-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            <span v-if="!archivoSeleccionado" class="drop-zone-text">
                                Arrastra y suelta tu archivo aquí, o haz clic para seleccionarlo
                            </span>
                            <span v-else class="drop-zone-text archivo-seleccionado">
                                <svg class="archivo-seleccionado-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ archivoSeleccionado.name }} ({{ formatFileSize(archivoSeleccionado.size) }})
                            </span>
                            <span class="drop-zone-hint">Formatos permitidos: PDF, JPG, PNG, GIF</span>
                        </div>
                        <input 
                            type="file" 
                            ref="fileInput" 
                            @change="onFileSelect" 
                            accept=".pdf,.jpg,.jpeg,.png,.gif,image/*"
                            class="file-input-hidden"
                        >
                    </div>

                    <div v-if="errorArchivo" class="error-archivo">
                        <svg class="error-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ errorArchivo }}
                    </div>

                    <div class="modal-archivos-actions">
                        <button type="button" class="btn-modal-cancel" @click="cerrarModalArchivos">
                            Cancelar
                        </button>
                        <button 
                            type="submit" 
                            class="btn-modal-submit" 
                            :disabled="!archivoSeleccionado || subiendoArchivo"
                        >
                            <span v-if="subiendoArchivo" class="spinner-border-sm"></span>
                            <span v-else>Subir archivo</span>
                        </button>
                    </div>
                </form>
            </div>
        </a-modal>

        <!-- ============================================================ -->
        <!-- MODAL PARA PREVISUALIZAR ARCHIVO -->
        <!-- ============================================================ -->
        <a-modal
            v-model:open="modalPreviewVisible"
            :title="'Archivo: ' + (archivoPreview?.nombre_original || '')"
            width="800px"
            :footer="null"
            class="modal-preview-premium"
        >
            <div class="preview-content">
                <div v-if="archivoPreview?.tipo_archivo === 'image'" class="preview-image-wrapper">
                    <img :src="archivoPreview.url" alt="Archivo adjunto" class="preview-image">
                </div>
                <div v-else-if="archivoPreview?.tipo_archivo === 'pdf'" class="preview-pdf-wrapper">
                    <iframe :src="archivoPreview.url" class="preview-pdf" frameborder="0"></iframe>
                </div>
                <div v-else class="preview-other">
                    <div class="preview-other-icon">
                        <svg class="preview-other-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <p class="preview-other-text">Vista previa no disponible para este tipo de archivo</p>
                    <a :href="archivoPreview.url" target="_blank" class="preview-other-link">Descargar archivo</a>
                </div>
            </div>
            <div class="preview-footer">
                <button class="btn-modal-cancel" @click="cerrarPreview">Cerrar</button>
                <a :href="archivoPreview?.url" target="_blank" class="btn-modal-submit" download>
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
import {
    Modal as AModal,
} from 'ant-design-vue';

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
// COMPUTED - Total factura = SUMA DE BASES (sin IVA)
// ============================================
const calcularTotalDobleIva = computed(() => {
    const cero = Number(props.movimiento.monto_iva_cero) || 0;
    const dieciseisBase = Number(props.movimiento.monto_iva_dieciseis) || 0;
    return cero + dieciseisBase;
});

// ============================================
// FUNCIÓN PARA DETERMINAR SI PUEDE EDITAR
// ============================================
const puedeEditar = () => {
    const estatus = props.movimiento.estatus;
    
    if (permisos.value?.es_super_usuario) {
        return true;
    }
    
    if (permisos.value?.es_auditor) {
        return false;
    }
    
    const estatusNoEditables = ['REVISADO', 'AUTORIZADO', 'LIQUIDADO', 'CERRADO'];
    return !estatusNoEditables.includes(estatus);
};

// ============================================
// FUNCIÓN PARA SUBIR ARCHIVOS
// ============================================
const puedeSubirArchivos = () => {
    const estatus = props.movimiento.estatus;
    if (permisos.value?.es_super_usuario) return true;
    if (permisos.value?.es_auditor) return false;
    if (permisos.value?.es_lector) return false;
    const estatusNoEditables = ['REVISADO', 'AUTORIZADO', 'LIQUIDADO', 'CERRADO'];
    return !estatusNoEditables.includes(estatus);
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

const formatFileSize = (bytes) => {
    if (!bytes) return '—';
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(1024));
    const value = bytes / Math.pow(1024, i);
    return `${value.toFixed(i > 0 ? 1 : 0)} ${sizes[i]}`;
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

const getArchivoClase = (tipo) => {
    if (tipo === 'pdf') return 'archivo-pdf';
    if (tipo === 'image') return 'archivo-image';
    return 'archivo-other';
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
// ACCIONES DE ARCHIVOS
// ============================================
const modalArchivosVisible = ref(false);
const modalPreviewVisible = ref(false);
const archivoSeleccionado = ref(null);
const archivoPreview = ref(null);
const subiendoArchivo = ref(false);
const errorArchivo = ref('');
const dragging = ref(false);

const abrirModalArchivos = () => {
    errorArchivo.value = '';
    archivoSeleccionado.value = null;
    modalArchivosVisible.value = true;
};

const cerrarModalArchivos = () => {
    modalArchivosVisible.value = false;
    archivoSeleccionado.value = null;
    errorArchivo.value = '';
    dragging.value = false;
};

const onDrop = (e) => {
    dragging.value = false;
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        procesarArchivo(files[0]);
    }
};

const onFileSelect = (e) => {
    const files = e.target.files;
    if (files.length > 0) {
        procesarArchivo(files[0]);
    }
    e.target.value = '';
};

const procesarArchivo = (file) => {
    const tiposPermitidos = ['application/pdf', 'image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    const extensionesPermitidas = ['.pdf', '.jpg', '.jpeg', '.png', '.gif', '.webp'];
    
    const extension = '.' + file.name.split('.').pop().toLowerCase();
    
    if (!tiposPermitidos.includes(file.type) && !extensionesPermitidas.includes(extension)) {
        errorArchivo.value = 'Tipo de archivo no permitido. Solo PDF e imágenes (JPG, PNG, GIF).';
        archivoSeleccionado.value = null;
        return;
    }
    
    if (file.size > 10 * 1024 * 1024) {
        errorArchivo.value = 'El archivo no debe exceder 10 MB.';
        archivoSeleccionado.value = null;
        return;
    }
    
    errorArchivo.value = '';
    archivoSeleccionado.value = file;
};

const subirArchivo = async () => {
    if (!archivoSeleccionado.value) return;
    
    subiendoArchivo.value = true;
    errorArchivo.value = '';
    
    const formData = new FormData();
    formData.append('archivo', archivoSeleccionado.value);
    formData.append('id_poliza', props.movimiento.id_poliza);
    
    try {
        const response = await axios.post(
            route('movimientos.archivos.subir', props.movimiento.id_poliza),
            formData,
            { headers: { 'Content-Type': 'multipart/form-data' } }
        );
        
        if (response.data.success) {
            mostrarModal('success', 'Éxito', 'Archivo subido correctamente');
            cerrarModalArchivos();
            
            // Recargar la página para mostrar el nuevo archivo
            router.reload({ only: ['movimiento'] });
        } else {
            throw new Error(response.data.message || 'Error al subir el archivo');
        }
    } catch (error) {
        errorArchivo.value = error.response?.data?.message || error.message || 'Error al subir el archivo';
        mostrarModal('error', 'Error', errorArchivo.value);
    } finally {
        subiendoArchivo.value = false;
    }
};

const verArchivo = (archivo) => {
    archivoPreview.value = archivo;
    modalPreviewVisible.value = true;
};

const cerrarPreview = () => {
    modalPreviewVisible.value = false;
    archivoPreview.value = null;
};

const eliminarArchivo = async (archivo) => {
    if (!archivo.id) return;
    
    const result = await Swal.fire({
        title: '¿Eliminar archivo?',
        text: `¿Estás seguro de que deseas eliminar "${archivo.nombre_original}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    });
    
    if (!result.isConfirmed) return;
    
    try {
        const response = await axios.delete(route('movimientos.archivos.eliminar', archivo.id));
        
        if (response.data.success) {
            mostrarModal('success', 'Éxito', 'Archivo eliminado correctamente');
            router.reload({ only: ['movimiento'] });
        } else {
            throw new Error(response.data.message || 'Error al eliminar el archivo');
        }
    } catch (error) {
        mostrarModal('error', 'Error', error.response?.data?.message || error.message || 'Error al eliminar el archivo');
    }
};

// ============================================
// ACCIONES
// ============================================
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

.archivos-badge {
    display: inline-block;
    padding: 2px 12px;
    background: #dbeafe;
    color: #1e40af;
    border-radius: 4px;
    font-weight: 600;
    font-size: 0.7rem;
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
    flex-wrap: wrap;
}

.capturista-info {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 14px;
    background: #f1f5f9;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    color: #475569;
    white-space: nowrap;
}

.w-4 { width: 16px; height: 16px; }
.h-4 { width: 16px; height: 16px; }

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

.btn-adjuntar {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 18px;
    background: linear-gradient(135deg, #8b5cf6, #7c3aed);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 0.75rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-left: auto;
}

.btn-adjuntar:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(139, 92, 246, 0.3);
}

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

.info-value.pdf-name {
    font-size: 0.8rem;
    color: #475569;
    background: #f1f5f9;
    padding: 4px 10px;
    border-radius: 4px;
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

/* ========== ARCHIVOS ========== */
.archivos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 12px;
}

.archivo-card {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    background: #f8fafc;
    border-radius: 10px;
    border: 1px solid #f1f5f9;
    transition: all 0.3s ease;
}

.archivo-card:hover {
    background: #f1f5f9;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
}

.archivo-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 8px;
    flex-shrink: 0;
}

.archivo-icon.archivo-pdf { background: #fee2e2; color: #dc2626; }
.archivo-icon.archivo-image { background: #dbeafe; color: #2563eb; }
.archivo-icon.archivo-other { background: #f3f4f6; color: #6b7280; }

.archivo-svg {
    width: 22px;
    height: 22px;
}

.archivo-info {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.archivo-nombre {
    font-size: 0.85rem;
    font-weight: 600;
    color: #0f172a;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.archivo-tamano {
    font-size: 0.7rem;
    color: #94a3b8;
}

.archivo-fecha {
    font-size: 0.65rem;
    color: #cbd5e1;
}

.archivo-actions {
    display: flex;
    gap: 6px;
    flex-shrink: 0;
}

.archivo-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.archivo-btn.ver {
    background: #dbeafe;
    color: #2563eb;
}

.archivo-btn.ver:hover {
    background: #bfdbfe;
    transform: scale(1.1);
}

.archivo-btn.eliminar {
    background: #fee2e2;
    color: #dc2626;
}

.archivo-btn.eliminar:hover {
    background: #fecaca;
    transform: scale(1.1);
}

.no-archivos {
    padding: 24px;
    text-align: center;
    color: #94a3b8;
    font-size: 0.85rem;
    background: #f8fafc;
    border-radius: 10px;
    border: 2px dashed #e2e8f0;
}

/* ========== MODAL ARCHIVOS ========== */
.modal-archivos-premium :deep(.ant-modal-header) {
    background: linear-gradient(135deg, #8b5cf6, #7c3aed);
    border-radius: 8px 8px 0 0;
    padding: 16px 24px;
}

.modal-archivos-premium :deep(.ant-modal-title) {
    color: white;
    font-weight: 700;
}

.modal-archivos-premium :deep(.ant-modal-close) {
    color: white;
}

.modal-archivos-premium :deep(.ant-modal-close:hover) {
    color: #fca5a5;
}

.modal-archivos-content {
    padding: 8px 0;
}

.modal-archivos-info {
    margin-bottom: 16px;
    font-size: 0.85rem;
    color: #475569;
}

.modal-archivos-info strong {
    color: #0f172a;
}

.drop-zone {
    border: 2px dashed #d1d5db;
    border-radius: 12px;
    padding: 32px 24px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    background: #fafbfc;
}

.drop-zone:hover {
    border-color: #8b5cf6;
    background: #f8f7ff;
}

.drop-zone-dragover {
    border-color: #8b5cf6;
    background: #ede9fe;
    transform: scale(1.02);
}

.drop-zone-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}

.drop-zone-icon {
    width: 48px;
    height: 48px;
    color: #94a3b8;
}

.drop-zone-text {
    font-size: 0.9rem;
    color: #64748b;
}

.drop-zone-text.archivo-seleccionado {
    color: #10b981;
    font-weight: 600;
}

.archivo-seleccionado-icon {
    width: 20px;
    height: 20px;
    display: inline-block;
    vertical-align: middle;
    margin-right: 6px;
}

.drop-zone-hint {
    font-size: 0.7rem;
    color: #94a3b8;
}

.file-input-hidden {
    display: none;
}

.error-archivo {
    margin-top: 12px;
    padding: 8px 14px;
    background: #fee2e2;
    border-radius: 6px;
    color: #991b1b;
    font-size: 0.85rem;
    display: flex;
    align-items: center;
    gap: 8px;
}

.error-icon-sm {
    width: 18px;
    height: 18px;
    flex-shrink: 0;
}

.modal-archivos-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 20px;
    padding-top: 16px;
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
    padding: 8px 24px;
    background: linear-gradient(135deg, #8b5cf6, #7c3aed);
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.85rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-modal-submit:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
}

.btn-modal-submit:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.spinner-border-sm {
    display: inline-block;
    width: 1rem;
    height: 1rem;
    border: 0.15em solid currentColor;
    border-right-color: transparent;
    border-radius: 50%;
    animation: spinner 0.75s linear infinite;
}

/* ========== MODAL PREVIEW ========== */
.modal-preview-premium :deep(.ant-modal-header) {
    background: linear-gradient(135deg, #1a3a5c, #2c5282);
    border-radius: 8px 8px 0 0;
    padding: 16px 24px;
}

.modal-preview-premium :deep(.ant-modal-title) {
    color: white;
    font-weight: 700;
}

.modal-preview-premium :deep(.ant-modal-close) {
    color: white;
}

.modal-preview-premium :deep(.ant-modal-close:hover) {
    color: #fca5a5;
}

.preview-content {
    min-height: 300px;
    max-height: 70vh;
    overflow: auto;
}

.preview-image-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 300px;
}

.preview-image {
    max-width: 100%;
    max-height: 65vh;
    object-fit: contain;
    border-radius: 4px;
}

.preview-pdf-wrapper {
    width: 100%;
    height: 60vh;
}

.preview-pdf {
    width: 100%;
    height: 100%;
    border: none;
}

.preview-other {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 300px;
    gap: 12px;
}

.preview-other-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 80px;
    height: 80px;
    background: #f3f4f6;
    border-radius: 50%;
}

.preview-other-svg {
    width: 40px;
    height: 40px;
    color: #94a3b8;
}

.preview-other-text {
    color: #64748b;
    font-size: 0.95rem;
}

.preview-other-link {
    color: #2563eb;
    font-weight: 600;
    text-decoration: none;
    padding: 6px 16px;
    border: 1px solid #2563eb;
    border-radius: 6px;
    transition: all 0.3s ease;
}

.preview-other-link:hover {
    background: #eff6ff;
}

.preview-footer {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid #f3f4f6;
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

.audit-icon .w-4 { width: 14px; height: 14px; }
.audit-icon .h-4 { width: 14px; height: 14px; }

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

.audit-connector .w-3 { width: 12px; height: 12px; }
.audit-connector .h-3 { width: 12px; height: 12px; }

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
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
}
.btn-editar:hover {
    box-shadow: 0 4px 16px rgba(102, 126, 234, 0.3);
    color: white;
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
    .summary-grid { grid-template-columns: repeat(2, 1fr); }
    .doble-iva-grid { grid-template-columns: 1fr 1fr; }
    .archivos-grid { grid-template-columns: 1fr; }
    .header-wrapper { flex-direction: column; align-items: flex-start; }
    .header-right { width: 100%; }
    .header-actions { width: 100%; flex-wrap: wrap; }
    .badge-categoria, .badge-abonos { margin-left: 0; width: 100%; text-align: center; }
    .docs-actions { margin-left: 0; width: 100%; justify-content: flex-start; }
    .btn-adjuntar { margin-left: 0; width: 100%; justify-content: center; }
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
}

@media (max-width: 480px) {
    .summary-grid { grid-template-columns: 1fr; }
    .montos-grid { grid-template-columns: 1fr; }
    .doble-iva-grid { grid-template-columns: 1fr; }
    .iva-simple-grid { grid-template-columns: 1fr; }
    .header-title { font-size: 1.1rem; }
    .header-subtitle { font-size: 0.75rem; }
    .section-title { font-size: 0.95rem; }
    .abonos-table { font-size: 0.75rem; }
    .abonos-table th, .abonos-table td { padding: 6px 10px; }
    .summary-card { padding: 14px 16px; }
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
}

/* ============================================================ */
/* PRINT */
/* ============================================================ */
@media print {
    .no-print, .btn-back, .btn-doc, .btn-action, .header-actions .status-badge,
    .btn-adjuntar, .archivo-btn, .docs-actions {
        display: none !important;
    }
    body { background: white !important; }
    .page-content { padding: 0 !important; }
    .container-custom { padding: 0 !important; max-width: 100% !important; }
    .detail-card { box-shadow: none !important; border: 1px solid #e5e7eb !important; border-radius: 0 !important; padding: 1rem !important; }
    .summary-card { background: #1a3a5c !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; border-radius: 0 !important; padding: 16px !important; margin-bottom: 16px !important; }
    .summary-item { background: rgba(255,255,255,0.08) !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    .summary-item.highlight { background: rgba(255,255,255,0.15) !important; }
    .section-icon, .badge-categoria, .badge-abonos, .tipo-badge, .fiscal-badge, .doble-iva-badge-header, .doble-iva-tag {
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
    .monto-card.total-card { background: linear-gradient(135deg, #eff6ff, #dbeafe) !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    .doble-iva-box { background: #fefce8 !important; border-color: #fcd34d !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    .doble-iva-badge { background: #92400e !important; color: white !important; }
    .iva-simple-box { background: #f0fdf4 !important; border-color: #86efac !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    .abonos-table thead { background: #f1f5f9 !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    .abonos-table tfoot { background: #f8fafc !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    .action-footer { border-top: 2px solid #e5e7eb !important; }
    .detail-section { page-break-inside: avoid; break-inside: avoid; }
    .summary-card { page-break-after: avoid; break-after: avoid; }
    .audit-timeline { background: #f8fafc !important; border: 1px solid #e5e7eb !important; }
    .created-icon { background: #dbeafe !important; }
    .reviewed-icon { background: #d1fae5 !important; }
    .authorized-icon { background: #e0e7ff !important; }
    .rejected-icon { background: #fee2e2 !important; }
}

/* ============================================================ */
/* ANIMACIONES */
/* ============================================================ */
@keyframes spinner {
    to { transform: rotate(360deg); }
}
</style>