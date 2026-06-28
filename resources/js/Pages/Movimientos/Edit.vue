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
                        <h2 class="header-title">{{ tituloPagina }}</h2>
                        <p class="header-subtitle">{{ subtituloPagina }}</p>
                    </div>
                </div>
                <div class="header-right">
                    <div class="status-badge" :class="statusClass">
                        <span v-if="hasErrors">⚠ {{ errorCount }} errores</span>
                        <span v-else-if="isComplete">✓ Completado</span>
                        <span v-else>📝 {{ Math.round(progressPercentage) }}%</span>
                    </div>
                    <!-- ✅ BOTÓN DE AUTORIZAR - solo admin, auditor, super -->
                    <button 
                        v-if="permisos?.puede_autorizar && puedeAutorizar"
                        @click="confirmarAutorizar"
                        class="btn-autorizar-premium"
                        :disabled="processingAutorizar"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        {{ processingAutorizar ? 'Autorizando...' : 'Autorizar Póliza' }}
                    </button>
                </div>
            </div>
        </template>

        <div class="page-content">
            <div class="container-custom">
                <div class="form-card">
                    <!-- Selector de Tipo de Poliza -->
                    <div class="tipo-selector-premium">
                        <button 
                            type="button"
                            @click="seleccionarTipo('INGRESO_EGRESO')"
                            class="tipo-btn-premium"
                            :class="{ active: tipoPolizaSeleccionado === 'INGRESO_EGRESO' }"
                        >
                            <div class="tipo-btn-content">
                                <span class="tipo-btn-icon">
                                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                                    </svg>
                                </span>
                                <span class="tipo-btn-label">Ingreso / Egreso</span>
                                <span class="tipo-btn-badge" v-if="tipoPolizaSeleccionado === 'INGRESO_EGRESO'">Activo</span>
                            </div>
                            <div class="tipo-btn-glow"></div>
                        </button>
                        <button 
                            type="button"
                            @click="seleccionarTipo('TRASPASO')"
                            class="tipo-btn-premium"
                            :class="{ active: tipoPolizaSeleccionado === 'TRASPASO' }"
                        >
                            <div class="tipo-btn-content">
                                <span class="tipo-btn-icon">
                                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                    </svg>
                                </span>
                                <span class="tipo-btn-label">Traspaso</span>
                                <span class="tipo-btn-badge" v-if="tipoPolizaSeleccionado === 'TRASPASO'">Activo</span>
                            </div>
                            <div class="tipo-btn-glow"></div>
                        </button>
                    </div>

                    <form @submit.prevent="submit" id="polizaForm" novalidate>
                        <!-- INGRESO / EGRESO -->
                        <div v-if="tipoPolizaSeleccionado === 'INGRESO_EGRESO'" class="fade-slide">
                            <!-- Seccion 1: Datos Generales -->
                            <div class="section-block-premium">
                                <div class="section-header-premium">
                                    <div class="section-icon-premium blue">
                                        <svg class="icon-svg-premium" fill="none" stroke="white" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="section-title-text">Informacion General</h3>
                                        <p class="section-title-sub">Configura la poliza con los datos principales</p>
                                    </div>
                                </div>

                                <div class="form-grid-premium">
                                    <div class="form-group-premium">
                                        <label class="form-label-premium">
                                            Tipo de Poliza <span class="required-star">*</span>
                                        </label>
                                        <div class="input-wrapper-premium">
                                            <select v-model="form.tipo_poliza" 
                                                    @change="clearError('tipo_poliza')"
                                                    class="form-input-premium form-select-premium"
                                                    :class="{ 'error': form.errors.tipo_poliza }">
                                                <option value="">Selecciona un tipo</option>
                                                <option value="INGRESO">Ingreso</option>
                                                <option value="EGRESO">Egreso</option>
                                            </select>
                                            <div class="input-icon-premium">
                                                <svg class="icon-svg-sm-premium" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                </svg>
                                            </div>
                                        </div>
                                        <div v-if="form.errors.tipo_poliza" class="error-message-premium">{{ form.errors.tipo_poliza }}</div>
                                    </div>

                                    <div class="form-group-premium">
                                        <label class="form-label-premium">Persona</label>
                                        <div class="input-wrapper-premium">
                                            <select v-model="form.id_persona"
                                                    class="form-input-premium form-select-premium"
                                                    :class="{ 'error': form.errors.id_persona }">
                                                <option value="">Selecciona una persona</option>
                                                <option v-for="p in personas" :key="p.id_persona" :value="p.id_persona">
                                                    {{ p.nombre_completo }}
                                                </option>
                                            </select>
                                            <div class="input-icon-premium">
                                                <svg class="icon-svg-sm-premium" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="field-hint-premium">Escribe para buscar personas</div>
                                    </div>

                                    <div class="form-group-premium">
                                        <label class="form-label-premium">Cuenta</label>
                                        <div class="input-wrapper-premium">
                                            <select v-model="form.id_cuenta"
                                                    class="form-input-premium form-select-premium"
                                                    :class="{ 'error': form.errors.id_cuenta }">
                                                <option value="">Selecciona una cuenta</option>
                                                <option v-for="c in cuentas" :key="c.id_cuenta" :value="c.id_cuenta">
                                                    {{ c.nombre_cuenta }}
                                                </option>
                                            </select>
                                            <div class="input-icon-premium">
                                                <svg class="icon-svg-sm-premium" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group-premium" v-if="form.tipo_poliza === 'EGRESO'">
                                        <label class="form-label-premium">Cuenta Fondeadora <span class="required-star">*</span></label>
                                        <div class="input-wrapper-premium">
                                            <select v-model="form.id_cuenta_fondeadora"
                                                    @change="cambiarCuentaFondeadora"
                                                    class="form-input-premium form-select-premium"
                                                    :class="{ 'error': form.errors.id_cuenta_fondeadora }">
                                                <option value="">Selecciona una fondeadora</option>
                                                <option v-for="c in cuentasFondeadoras" :key="c.id_cuenta" :value="c.id_cuenta">
                                                    {{ c.nombre_cuenta }}
                                                </option>
                                            </select>
                                            <div class="input-icon-premium">
                                                <svg class="icon-svg-sm-premium" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                </svg>
                                            </div>
                                        </div>
                                        <div v-if="form.errors.id_cuenta_fondeadora" class="error-message-premium">{{ form.errors.id_cuenta_fondeadora }}</div>
                                    </div>

                                    <div class="form-group-premium" v-if="form.tipo_poliza === 'INGRESO'">
                                        <div class="info-message-ingreso">
                                            <span class="info-icon-ingreso">
                                                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </span>
                                            <span>En un ingreso no se requiere cuenta fondeadora, el dinero se recibe en la cuenta seleccionada.</span>
                                        </div>
                                    </div>

                                    <div class="form-group-premium checkbox-group-premium">
                                        <label class="form-label-premium">Opciones</label>
                                        <div class="checkbox-grid-premium">
                                            <label class="checkbox-premium">
                                                <input type="checkbox" v-model="form.es_por_pagar" class="checkbox-input-premium">
                                                <span class="checkbox-custom-premium"></span>
                                                <span class="checkbox-text-premium">Por Pagar</span>
                                            </label>
                                            <label class="checkbox-premium">
                                                <input type="checkbox" v-model="form.es_fiscal" @change="toggleFiscal" class="checkbox-input-premium">
                                                <span class="checkbox-custom-premium"></span>
                                                <span class="checkbox-text-premium">Fiscal</span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="form-group-premium" v-if="form.es_por_pagar">
                                        <label class="form-label-premium">Fecha Vencimiento <span class="required-star">*</span></label>
                                        <div class="input-wrapper-premium">
                                            <input type="date" v-model="form.fecha_vencimiento"
                                                   @input="clearError('fecha_vencimiento')"
                                                   class="form-input-premium"
                                                   :class="{ 'error': form.errors.fecha_vencimiento }"
                                                   :min="fechaActual">
                                        </div>
                                        <div v-if="form.errors.fecha_vencimiento" class="error-message-premium">{{ form.errors.fecha_vencimiento }}</div>
                                    </div>

                                    <div class="form-group-premium">
                                        <label class="form-label-premium">Marcador</label>
                                        <div class="input-wrapper-premium" style="display: flex; gap: 8px;">
                                            <select v-model="form.id_marcador"
                                                    class="form-input-premium form-select-premium"
                                                    style="flex: 1;">
                                                <option value="">Selecciona un marcador</option>
                                                <option v-for="m in marcadores" :key="m.id" :value="m.id">
                                                    {{ m.nombre_marcador }}
                                                </option>
                                            </select>
                                            <button type="button" @click="abrirModalMarcador" class="btn-add-marcador-premium" title="Nuevo marcador">
                                                <svg class="icon-svg-sm-premium" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Seccion 2: Monto y Desglose -->
                            <div class="section-block-premium">
                                <div class="section-header-premium">
                                    <div class="section-icon-premium green">
                                        <svg class="icon-svg-premium" fill="none" stroke="white" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="section-title-text">Monto y Desglose</h3>
                                        <p class="section-title-sub">Configuracion financiera y fiscal detallada</p>
                                    </div>
                                </div>

                                <div class="form-grid-premium">
                                    <div class="form-group-premium">
                                        <label class="form-label-premium">Total Factura <span class="required-star">*</span></label>
                                        <div class="input-wrapper-premium">
                                            <span class="input-prefix-premium">$</span>
                                            <input type="number" step="0.01" v-model.number="form.total_factura"
                                                   @input="calcularDesglose; clearError('total_factura')"
                                                   class="form-input-premium"
                                                   :class="{ 'error': form.errors.total_factura }"
                                                   placeholder="0.00"
                                                   min="0.01">
                                        </div>
                                        <div v-if="form.errors.total_factura" class="error-message-premium">{{ form.errors.total_factura }}</div>
                                    </div>

                                    <div class="form-group-premium">
                                        <label class="form-label-premium">IVA</label>
                                        <div class="input-wrapper-premium">
                                            <select v-model="form.id_tipo_iva"
                                                    @change="calcularDesglose"
                                                    class="form-input-premium form-select-premium"
                                                    :class="{ 'error': form.errors.id_tipo_iva }">
                                                <option value="">Sin IVA</option>
                                                <option v-for="iva in tiposIva" :key="iva.id" :value="iva.id">
                                                    {{ iva.nombre }} ({{ iva.porcentaje_formateado }})
                                                </option>
                                            </select>
                                            <div class="input-icon-premium">
                                                <svg class="icon-svg-sm-premium" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                </svg>
                                            </div>
                                        </div>
                                        <div v-if="form.errors.id_tipo_iva" class="error-message-premium">{{ form.errors.id_tipo_iva }}</div>
                                    </div>
                                </div>

                                <div v-if="form.total_factura > 0 && form.id_tipo_iva" class="desglose-box-premium">
                                    <div class="desglose-header-premium">
                                        <span class="desglose-title-premium">Desglose Automatico</span>
                                        <span class="desglose-subtitle-premium">Calculo del sistema</span>
                                    </div>
                                    <div class="desglose-grid-premium">
                                        <div class="desglose-item-premium">
                                            <span class="desglose-label-premium">Base Gravable</span>
                                            <span class="desglose-value-premium" style="color: #2563eb;">${{ formatNumber(form.monto_base) }}</span>
                                        </div>
                                        <div class="desglose-item-premium">
                                            <span class="desglose-label-premium">IVA en Pesos</span>
                                            <span class="desglose-value-premium" style="color: #f59e0b;">${{ formatNumber(form.monto_iva) }}</span>
                                        </div>
                                        <div class="desglose-item-premium">
                                            <span class="desglose-label-premium font-bold">Total Factura</span>
                                            <span class="desglose-value-premium font-bold" style="color: #10b981;">${{ formatNumber(calcularSumaTotal) }}</span>
                                        </div>
                                    </div>
                                    <div class="desglose-verificacion" v-if="mostrarAvisoRedondeo">
                                        <span class="desglose-verificacion-text">Ajuste por redondeo aplicado</span>
                                    </div>
                                </div>

                                <div v-if="form.es_fiscal && form.total_factura > 0 && form.id_tipo_iva" class="desglose-fiscal-box-premium">
                                    <div class="desglose-fiscal-header-premium">
                                        <span class="desglose-fiscal-title-premium">Desglose Fiscal</span>
                                        <span class="desglose-fiscal-subtitle-premium">Este desglose se usara para efectos fiscales y contables</span>
                                    </div>
                                    <div class="desglose-fiscal-grid-premium">
                                        <div class="desglose-fiscal-item-premium">
                                            <span class="desglose-fiscal-label-premium">Base Gravable</span>
                                            <span class="desglose-fiscal-value-premium" style="color: #2563eb;">${{ formatNumber(form.monto_base) }}</span>
                                        </div>
                                        <div class="desglose-fiscal-item-premium">
                                            <span class="desglose-fiscal-label-premium">IVA</span>
                                            <span class="desglose-fiscal-value-premium" style="color: #f59e0b;">${{ formatNumber(form.monto_iva) }}</span>
                                        </div>
                                        <div class="desglose-fiscal-item-premium">
                                            <span class="desglose-fiscal-label-premium">Total</span>
                                            <span class="desglose-fiscal-value-premium" style="color: #10b981;">${{ formatNumber(calcularSumaTotal) }}</span>
                                        </div>
                                    </div>
                                    <div class="desglose-fiscal-footer-premium">
                                        <span class="desglose-fiscal-leyenda-premium">Este desglose se usara para efectos fiscales y contables</span>
                                    </div>
                                </div>

                                <div v-if="form.tipo_poliza === 'EGRESO' && cuentaFondeadoraSeleccionada" class="saldo-box-premium" :class="saldoSuficiente ? 'saldo-ok-premium' : 'saldo-error-premium'">
                                    <span class="saldo-icon-premium">
                                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </span>
                                    <span class="saldo-label-premium">Saldo disponible:</span>
                                    <span class="saldo-value-premium">${{ formatNumber(cuentaFondeadoraSeleccionada.saldo) }}</span>
                                    <span v-if="!saldoSuficiente" class="saldo-warning-premium">Fondos insuficientes</span>
                                </div>
                            </div>

                            <!-- Seccion 3: Factura y Archivos -->
                            <div v-if="form.es_fiscal" class="section-block-premium fade-slide">
                                <div class="section-header-premium">
                                    <div class="section-icon-premium orange">
                                        <svg class="icon-svg-premium" fill="none" stroke="white" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="section-title-text">Facturacion</h3>
                                        <p class="section-title-sub">Datos de la factura y archivos adjuntos</p>
                                    </div>
                                </div>

                                <div class="form-grid-premium">
                                    <div class="form-group-premium">
                                        <label class="form-label-premium">Fecha Factura</label>
                                        <div class="input-wrapper-premium">
                                            <input type="date" v-model="form.fecha_factura"
                                                   class="form-input-premium"
                                                   :max="fechaActual">
                                        </div>
                                    </div>

                                    <div class="form-group-premium">
                                        <label class="form-label-premium">Numero Factura</label>
                                        <div class="input-wrapper-premium">
                                            <input type="text" v-model="form.numero_factura"
                                                   class="form-input-premium"
                                                   placeholder="Ej: A-1258">
                                        </div>
                                    </div>

                                    <div class="form-group-premium">
                                        <label class="form-label-premium">PDF</label>
                                        <div class="file-upload-premium">
                                            <input type="file" @change="handleFileUpload('pdf', $event)" 
                                                   accept=".pdf" class="file-input-premium">
                                            <div class="file-upload-area-premium">
                                                <span class="file-upload-icon-premium">
                                                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 17h10M7 13h10M7 9h6"/>
                                                    </svg>
                                                </span>
                                                <span class="file-upload-text-premium">{{ archivos.pdf ? archivos.pdf.name : 'Seleccionar PDF' }}</span>
                                            </div>
                                            <button v-if="archivos.pdf" type="button" @click="archivos.pdf = null" class="file-remove-premium">×</button>
                                        </div>
                                    </div>

                                    <div class="form-group-premium">
                                        <label class="form-label-premium">XML</label>
                                        <div class="file-upload-premium">
                                            <input type="file" @change="handleFileUpload('xml', $event)" 
                                                   accept=".xml" class="file-input-premium">
                                            <div class="file-upload-area-premium">
                                                <span class="file-upload-icon-premium">
                                                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                                    </svg>
                                                </span>
                                                <span class="file-upload-text-premium">{{ archivos.xml ? archivos.xml.name : 'Seleccionar XML' }}</span>
                                            </div>
                                            <button v-if="archivos.xml" type="button" @click="archivos.xml = null" class="file-remove-premium">×</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Seccion 4: Observaciones -->
                            <div class="section-block-premium">
                                <div class="section-header-premium">
                                    <div class="section-icon-premium teal">
                                        <svg class="icon-svg-premium" fill="none" stroke="white" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="section-title-text">Observaciones</h3>
                                        <p class="section-title-sub">Notas adicionales y comentarios</p>
                                    </div>
                                </div>

                                <div class="form-grid-premium">
                                    <div class="form-group-premium full-width-premium">
                                        <label class="form-label-premium">Observacion</label>
                                        <div class="input-wrapper-premium">
                                            <textarea v-model="form.nota" rows="3"
                                                      class="form-textarea-premium"
                                                      placeholder="Agrega notas o comentarios sobre esta poliza..."
                                                      @input="clearError('nota')"></textarea>
                                        </div>
                                        <div v-if="form.nota" class="char-counter-premium">{{ form.nota.length }} caracteres</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TRASPASO -->
                        <div v-if="tipoPolizaSeleccionado === 'TRASPASO'" class="fade-slide">
                            <!-- Seccion 1: Informacion General Traspaso -->
                            <div class="section-block-premium">
                                <div class="section-header-premium">
                                    <div class="section-icon-premium purple">
                                        <svg class="icon-svg-premium" fill="none" stroke="white" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="section-title-text">Informacion del Traspaso</h3>
                                        <p class="section-title-sub">Configuracion de la transferencia entre cuentas</p>
                                    </div>
                                </div>

                                <div class="form-grid-premium">
                                    <div class="form-group-premium">
                                        <label class="form-label-premium">Tipo de Poliza <span class="required-star">*</span></label>
                                        <div class="input-wrapper-premium">
                                            <select v-model="formTraspaso.tipo_poliza"
                                                    class="form-input-premium form-select-premium"
                                                    :class="{ 'error': formTraspaso.errors.tipo_poliza }">
                                                <option value="TRASPASO">Traspaso</option>
                                            </select>
                                            <div class="input-icon-premium">
                                                <svg class="icon-svg-sm-premium" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group-premium">
                                        <label class="form-label-premium">Referencia / Folio</label>
                                        <div class="input-wrapper-premium">
                                            <input type="text" v-model="formTraspaso.referencia"
                                                   class="form-input-premium"
                                                   placeholder="Ej: TR-2024-001">
                                        </div>
                                    </div>

                                    <div class="form-group-premium checkbox-group-premium">
                                        <label class="form-label-premium">Opciones</label>
                                        <div class="checkbox-grid-premium">
                                            <label class="checkbox-premium">
                                                <input type="checkbox" v-model="formTraspaso.es_fiscal" class="checkbox-input-premium">
                                                <span class="checkbox-custom-premium"></span>
                                                <span class="checkbox-text-premium">Fiscal</span>
                                            </label>
                                            <label class="checkbox-premium">
                                                <input type="checkbox" v-model="formTraspaso.es_por_pagar" class="checkbox-input-premium">
                                                <span class="checkbox-custom-premium"></span>
                                                <span class="checkbox-text-premium">Por Pagar</span>
                                            </label>
                                        </div>
                                    </div>

                                    <div class="form-group-premium" v-if="formTraspaso.es_por_pagar">
                                        <label class="form-label-premium">Fecha Vencimiento <span class="required-star">*</span></label>
                                        <div class="input-wrapper-premium">
                                            <input type="date" v-model="formTraspaso.fecha_vencimiento"
                                                   class="form-input-premium"
                                                   :min="fechaActual">
                                        </div>
                                    </div>

                                    <div class="form-group-premium">
                                        <label class="form-label-premium">Marcador</label>
                                        <div class="input-wrapper-premium" style="display: flex; gap: 8px;">
                                            <select v-model="formTraspaso.id_marcador"
                                                    class="form-input-premium form-select-premium"
                                                    style="flex: 1;">
                                                <option value="">Selecciona un marcador</option>
                                                <option v-for="m in marcadores" :key="m.id" :value="m.id">
                                                    {{ m.nombre_marcador }}
                                                </option>
                                            </select>
                                            <button type="button" @click="abrirModalMarcador" class="btn-add-marcador-premium" title="Nuevo marcador">
                                                <svg class="icon-svg-sm-premium" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Seccion 2: Cuentas Traspaso -->
                            <div class="section-block-premium">
                                <div class="section-header-premium">
                                    <div class="section-icon-premium blue">
                                        <svg class="icon-svg-premium" fill="none" stroke="white" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="section-title-text">Cuentas de Traspaso</h3>
                                        <p class="section-title-sub">Origen y destino de la transferencia</p>
                                    </div>
                                </div>

                                <div class="traspaso-layout-premium">
                                    <!-- Cuenta Origen -->
                                    <div class="cuenta-origen-premium">
                                        <div class="cuenta-card-premium">
                                            <div class="cuenta-card-header-premium">
                                                <span class="cuenta-card-icon">
                                                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                    </svg>
                                                </span>
                                                <span class="cuenta-card-title">Cuenta Origen</span>
                                                <span class="cuenta-card-badge sale">Sale</span>
                                            </div>
                                            <div class="cuenta-card-body-premium">
                                                <div class="input-wrapper-premium">
                                                    <select v-model="formTraspaso.id_cuenta_origen"
                                                            class="form-input-premium form-select-premium"
                                                            :class="{ 'error': formTraspaso.errors.id_cuenta_origen }">
                                                        <option value="">Selecciona una cuenta</option>
                                                        <option v-for="c in cuentas" :key="c.id_cuenta" :value="c.id_cuenta">
                                                            {{ c.nombre_cuenta }}
                                                        </option>
                                                    </select>
                                                    <div class="input-icon-premium">
                                                        <svg class="icon-svg-sm-premium" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                        </svg>
                                                    </div>
                                                </div>
                                                <div v-if="formTraspaso.errors.id_cuenta_origen" class="error-message-premium">{{ formTraspaso.errors.id_cuenta_origen }}</div>
                                                <div class="cuenta-card-info">
                                                    <span class="info-label">Saldo actual</span>
                                                    <span class="info-value">${{ formatNumber(obtenerSaldoCuenta(formTraspaso.id_cuenta_origen)) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Flecha Central -->
                                    <div class="transfer-center-premium">
                                        <div class="transfer-arrows-premium">
                                            <div class="arrow-container">
                                                <svg class="arrow-icon-premium" fill="none" stroke="#667eea" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                                </svg>
                                            </div>
                                            <div class="transfer-text-premium">
                                                <span class="transfer-label">Transferir</span>
                                                <div class="transfer-amount">
                                                    ${{ formatNumber(formTraspaso.monto) }}
                                                </div>
                                            </div>
                                            <div class="transfer-particles">
                                                <span class="particle-dot" style="animation-delay: 0s"></span>
                                                <span class="particle-dot" style="animation-delay: 0.3s"></span>
                                                <span class="particle-dot" style="animation-delay: 0.6s"></span>
                                                <span class="particle-dot" style="animation-delay: 0.9s"></span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Cuenta Destino -->
                                    <div class="cuenta-destino-premium">
                                        <div class="cuenta-card-premium">
                                            <div class="cuenta-card-header-premium">
                                                <span class="cuenta-card-icon">
                                                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                    </svg>
                                                </span>
                                                <span class="cuenta-card-title">Cuenta Destino</span>
                                                <span class="cuenta-card-badge receive">Recibe</span>
                                            </div>
                                            <div class="cuenta-card-body-premium">
                                                <div class="input-wrapper-premium">
                                                    <select v-model="formTraspaso.id_cuenta_destino"
                                                            class="form-input-premium form-select-premium"
                                                            :class="{ 'error': formTraspaso.errors.id_cuenta_destino }">
                                                        <option value="">Selecciona una cuenta</option>
                                                        <option v-for="c in cuentas" :key="c.id_cuenta" :value="c.id_cuenta">
                                                            {{ c.nombre_cuenta }}
                                                        </option>
                                                    </select>
                                                    <div class="input-icon-premium">
                                                        <svg class="icon-svg-sm-premium" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                        </svg>
                                                    </div>
                                                </div>
                                                <div v-if="formTraspaso.errors.id_cuenta_destino" class="error-message-premium">{{ formTraspaso.errors.id_cuenta_destino }}</div>
                                                <div class="cuenta-card-info">
                                                    <span class="info-label">Saldo actual</span>
                                                    <span class="info-value">${{ formatNumber(obtenerSaldoCuenta(formTraspaso.id_cuenta_destino)) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Monto a Transferir -->
                                <div class="monto-transferir-premium">
                                    <label class="form-label-premium">Monto a Transferir <span class="required-star">*</span></label>
                                    <div class="input-wrapper-premium">
                                        <span class="input-prefix-premium">$</span>
                                        <input type="number" step="0.01" v-model.number="formTraspaso.monto"
                                               @input="clearErrorTraspaso('monto')"
                                               class="form-input-premium"
                                               :class="{ 'error': formTraspaso.errors.monto }"
                                               placeholder="0.00"
                                               min="0.01">
                                    </div>
                                    <div v-if="formTraspaso.errors.monto" class="error-message-premium">{{ formTraspaso.errors.monto }}</div>
                                </div>

                                <div v-if="formTraspaso.es_fiscal && formTraspaso.monto > 0" class="desglose-fiscal-box-premium fade-slide">
                                    <div class="desglose-fiscal-header-premium">
                                        <span class="desglose-fiscal-title-premium">Desglose Fiscal del Traspaso</span>
                                        <span class="desglose-fiscal-subtitle-premium">Este desglose se usara para efectos fiscales y contables</span>
                                    </div>
                                    <div class="desglose-fiscal-grid-premium">
                                        <div class="desglose-fiscal-item-premium">
                                            <span class="desglose-fiscal-label-premium">Monto Transferido</span>
                                            <span class="desglose-fiscal-value-premium" style="color: #2563eb;">${{ formatNumber(formTraspaso.monto) }}</span>
                                        </div>
                                        <div class="desglose-fiscal-item-premium">
                                            <span class="desglose-fiscal-label-premium">Cuenta Origen</span>
                                            <span class="desglose-fiscal-value-premium">{{ cuentaOrigenNombre }}</span>
                                        </div>
                                        <div class="desglose-fiscal-item-premium">
                                            <span class="desglose-fiscal-label-premium">Cuenta Destino</span>
                                            <span class="desglose-fiscal-value-premium">{{ cuentaDestinoNombre }}</span>
                                        </div>
                                    </div>
                                    <div class="desglose-fiscal-footer-premium">
                                        <span class="desglose-fiscal-leyenda-premium">Este desglose se usara para efectos fiscales y contables</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Seccion 3: Observaciones Traspaso -->
                            <div class="section-block-premium">
                                <div class="section-header-premium">
                                    <div class="section-icon-premium teal">
                                        <svg class="icon-svg-premium" fill="none" stroke="white" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="section-title-text">Observaciones</h3>
                                        <p class="section-title-sub">Notas adicionales del traspaso</p>
                                    </div>
                                </div>

                                <div class="form-grid-premium">
                                    <div class="form-group-premium full-width-premium">
                                        <label class="form-label-premium">Observacion</label>
                                        <div class="input-wrapper-premium">
                                            <textarea v-model="formTraspaso.nota" rows="3"
                                                      class="form-textarea-premium"
                                                      placeholder="Agrega notas o comentarios sobre este traspaso..."
                                                      @input="clearErrorTraspaso('nota')"></textarea>
                                        </div>
                                        <div v-if="formTraspaso.nota" class="char-counter-premium">{{ formTraspaso.nota.length }} caracteres</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="info-box-premium">
                            <svg class="info-icon-premium" fill="none" stroke="#667eea" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Los campos con <strong class="text-danger-premium">*</strong> son obligatorios</span>
                        </div>

                        <div class="form-actions-premium">
                            <div class="actions-left-premium">
                                <div class="total-card-premium">
                                    <span class="total-label-premium">Total</span>
                                    <span class="total-value-premium">${{ formatNumber(tipoPolizaSeleccionado === 'INGRESO_EGRESO' ? calcularSumaTotal : formTraspaso.monto) }}</span>
                                </div>
                            </div>
                            <div class="actions-right-premium">
                                <!-- ✅ ELIMINAR - solo admin, auditor, super -->
                                <button 
                                    v-if="permisos?.puede_eliminar"
                                    type="button" 
                                    @click="confirmarEliminar"
                                    class="btn-premium btn-delete-premium">
                                    <svg class="btn-icon-premium" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Eliminar
                                </button>
                                <Link :href="route('movimientos.index')" class="btn-premium btn-cancel-premium">
                                    <svg class="btn-icon-premium" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    Cancelar
                                </Link>
                                <!-- ✅ ACTUALIZAR - solo admin, auditor, super -->
                                <button 
                                    v-if="permisos?.puede_editar"
                                    type="submit" 
                                    :disabled="processing || !isFormValidGeneral"
                                    class="btn-premium btn-submit-premium">
                                    <span v-if="processing" class="spinner-border-premium"></span>
                                    <svg v-else class="btn-icon-premium" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                                    </svg>
                                    {{ processing ? 'Guardando...' : 'Actualizar Poliza' }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Marcador -->
        <div v-if="modalMarcadorVisible" class="modal-overlay-premium" @click.self="cerrarModalMarcador">
            <div class="modal-container-premium">
                <div class="modal-header-premium">
                    <h3 class="modal-title-premium">Nuevo Marcador</h3>
                    <button type="button" @click="cerrarModalMarcador" class="modal-close-premium">
                        <svg class="icon-svg-sm-premium" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="modal-body-premium">
                    <form @submit.prevent="guardarMarcador">
                        <div class="form-group-premium">
                            <label class="form-label-premium">Nombre <span class="required-star">*</span></label>
                            <div class="input-wrapper-premium">
                                <input type="text" v-model="nuevoMarcador.nombre"
                                       class="form-input-premium"
                                       placeholder="Ej: Urgente, Importante">
                            </div>
                        </div>
                        <div class="form-group-premium" style="margin-top: 16px;">
                            <label class="form-label-premium">Descripcion</label>
                            <div class="input-wrapper-premium">
                                <textarea v-model="nuevoMarcador.descripcion"
                                          class="form-textarea-premium"
                                          rows="2"
                                          placeholder="Breve descripcion..."></textarea>
                            </div>
                        </div>
                        <div class="modal-actions-premium">
                            <button type="button" @click="cerrarModalMarcador" class="btn-premium btn-cancel-premium">Cancelar</button>
                            <button type="submit" :disabled="guardandoMarcador" class="btn-premium btn-submit-premium">
                                {{ guardandoMarcador ? 'Guardando...' : 'Crear Marcador' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <AlertModal ref="alertRef" />
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, useForm, router, usePage } from '@inertiajs/vue3';
import AlertModal from '@/Components/AlertModal.vue';
import axios from 'axios';
import Swal from 'sweetalert2';

// ============================================
// ✅ PERMISOS DESDE EL BACKEND
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
    },
    empresa_id: { type: Number, default: null },
    cuentas_fondeadoras: { type: Array, default: () => [] },
    cuentas: { type: Array, default: () => [] },
    tipos_iva: { type: Array, default: () => [] },
    marcadores: { type: Array, default: () => [] },
    es_traspaso: { type: Boolean, default: false }
});

// ============================================
// REFS
// ============================================
const alertRef = ref(null);
const personas = ref([]);
const cuentaFondeadoraSeleccionada = ref(null);
const fechaActual = ref(new Date().toISOString().split('T')[0]);
const modalMarcadorVisible = ref(false);
const guardandoMarcador = ref(false);
const nuevoMarcador = ref({ nombre: '', descripcion: '' });
const processing = ref(false);
const processingAutorizar = ref(false);
const archivos = ref({ pdf: null, xml: null });
const mostrarAvisoRedondeo = ref(false);

const tiposIva = ref(props.tipos_iva || []);
const cuentasFondeadoras = ref(props.cuentas_fondeadoras || []);
const cuentas = ref(props.cuentas || []);
const marcadores = ref(props.marcadores || []);
const tipoPolizaSeleccionado = ref(props.es_traspaso ? 'TRASPASO' : 'INGRESO_EGRESO');

// ============================================
// COMPUTED
// ============================================
const puedeAutorizar = computed(() => {
    const estatus = props.movimiento.estatus || '';
    return estatus === 'CAPTURADO' || estatus === 'REVISADO' || estatus === 'PENDIENTE';
});

const saldoSuficiente = computed(() => {
    if (!cuentaFondeadoraSeleccionada.value) return true;
    const saldo = parseFloat(cuentaFondeadoraSeleccionada.value.saldo) || 0;
    const total = parseFloat(form.total_factura) || 0;
    return saldo >= total;
});

const hasErrors = computed(() => Object.keys(form.errors).length > 0);
const errorCount = computed(() => Object.keys(form.errors).length);

const requiredFields = computed(() => {
    const fields = ['tipo_poliza', 'total_factura'];
    if (form.tipo_poliza === 'EGRESO') fields.push('id_cuenta_fondeadora');
    if (form.es_por_pagar) fields.push('fecha_vencimiento');
    return fields;
});

const progressPercentage = computed(() => {
    const total = requiredFields.value.length;
    const filled = requiredFields.value.filter(f => {
        const val = form[f];
        return val !== null && val !== undefined && val !== '' && val !== 0;
    }).length;
    return total > 0 ? (filled / total) * 100 : 0;
});

const isComplete = computed(() => progressPercentage.value === 100 && !hasErrors.value);

const statusClass = computed(() => {
    if (hasErrors.value) return 'status-error';
    if (isComplete.value) return 'status-success';
    return 'status-progress';
});

const calcularSumaTotal = computed(() => {
    if (!form.total_factura || form.total_factura <= 0) return 0;
    if (!form.id_tipo_iva) return form.total_factura;
    const base = parseFloat(form.monto_base) || 0;
    const iva = parseFloat(form.monto_iva) || 0;
    return Math.round((base + iva) * 100) / 100;
});

const cuentaOrigenNombre = computed(() => {
    if (!formTraspaso.id_cuenta_origen) return 'Seleccionar';
    const cuenta = cuentas.value.find(c => c.id_cuenta === formTraspaso.id_cuenta_origen);
    return cuenta ? cuenta.nombre_cuenta : 'Seleccionar';
});

const cuentaDestinoNombre = computed(() => {
    if (!formTraspaso.id_cuenta_destino) return 'Seleccionar';
    const cuenta = cuentas.value.find(c => c.id_cuenta === formTraspaso.id_cuenta_destino);
    return cuenta ? cuenta.nombre_cuenta : 'Seleccionar';
});

const isFormValidGeneral = computed(() => {
    if (tipoPolizaSeleccionado.value === 'INGRESO_EGRESO') {
        if (!form.tipo_poliza) return false;
        if (form.tipo_poliza === 'EGRESO' && !form.id_cuenta_fondeadora) return false;
        if (form.total_factura <= 0) return false;
        if (form.es_por_pagar && !form.fecha_vencimiento) return false;
        if (hasErrors.value) return false;
        return true;
    } else {
        if (!formTraspaso.id_cuenta_origen) return false;
        if (!formTraspaso.id_cuenta_destino) return false;
        if (formTraspaso.monto <= 0) return false;
        if (formTraspaso.id_cuenta_origen === formTraspaso.id_cuenta_destino) return false;
        if (formTraspaso.es_por_pagar && !formTraspaso.fecha_vencimiento) return false;
        return true;
    }
});

const tituloPagina = computed(() => {
    const tipo = tipoPolizaSeleccionado.value === 'INGRESO_EGRESO' 
        ? 'Ingreso/Egreso'
        : 'Traspaso';
    return `Editar Póliza de ${tipo}`;
});

const subtituloPagina = computed(() => {
    const tipo = tipoPolizaSeleccionado.value === 'INGRESO_EGRESO'
        ? 'Modifica los datos de la póliza de ingreso o egreso'
        : 'Modifica los datos del traspaso entre cuentas';
    return tipo;
});

// ============================================
// FORMULARIOS
// ============================================
const form = useForm({
    tipo_poliza: props.movimiento.tipo_poliza || 'INGRESO',
    id_persona: props.movimiento.id_persona || null,
    id_cuenta: props.movimiento.id_cuenta || null,
    es_por_pagar: props.movimiento.es_por_pagar || false,
    fecha_vencimiento: props.movimiento.fecha_vencimiento || null,
    es_fiscal: props.movimiento.es_fiscal || false,
    id_cuenta_fondeadora: props.movimiento.id_cuenta_fondeadora || null,
    id_marcador: props.movimiento.id_marcador || null,
    total_factura: props.movimiento.total_factura || 0,
    id_tipo_iva: props.movimiento.id_tipo_iva || null,
    monto_base: props.movimiento.monto_base || 0,
    monto_iva: props.movimiento.monto_iva || 0,
    fecha_factura: props.movimiento.fecha_factura || null,
    numero_factura: props.movimiento.numero_factura || null,
    nota: props.movimiento.nota || '',
    referencia: props.movimiento.referencia || null,
    folio: props.movimiento.folio || null
});

const formTraspaso = useForm({
    tipo_poliza: 'TRASPASO',
    id_cuenta_origen: props.movimiento.id_cuenta_origen || null,
    id_cuenta_destino: props.movimiento.id_cuenta_destino || null,
    monto: props.movimiento.monto || 0,
    es_fiscal: props.movimiento.es_fiscal || false,
    es_por_pagar: props.movimiento.es_por_pagar || false,
    fecha_vencimiento: props.movimiento.fecha_vencimiento || null,
    id_marcador: props.movimiento.id_marcador || null,
    referencia: props.movimiento.referencia || null,
    nota: props.movimiento.nota || null
});

// ============================================
// AUTORIZAR PÓLIZA
// ============================================
const confirmarAutorizar = () => {
    Swal.fire({
        title: 'Autorizar Póliza',
        html: `
            <div style="text-align: left; padding: 10px 0;">
                <p style="font-size: 14px; color: #374151; margin-bottom: 16px;">
                    Estas a punto de <strong style="color: #10b981;">autorizar</strong> esta póliza:
                </p>
                <div style="background: #f8fafc; border-radius: 10px; padding: 14px; border: 1px solid #e2e8f0; margin-bottom: 16px;">
                    <div style="display: flex; justify-content: space-between; padding: 4px 0; border-bottom: 1px dashed #e2e8f0;">
                        <span style="font-weight: 600; color: #64748b;">Folio:</span>
                        <span style="font-weight: 700; color: #0f172a;">${props.movimiento.folio || 'S/N'}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 4px 0; border-bottom: 1px dashed #e2e8f0;">
                        <span style="font-weight: 600; color: #64748b;">Tipo:</span>
                        <span style="font-weight: 700; color: #0f172a;">${props.movimiento.tipo_poliza || 'N/A'}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 4px 0;">
                        <span style="font-weight: 600; color: #64748b;">Monto:</span>
                        <span style="font-weight: 700; color: #10b981; font-size: 16px;">$${formatNumber(props.movimiento.total_factura || props.movimiento.monto_abs || 0)}</span>
                    </div>
                </div>
                <div style="background: #ecfdf5; border-radius: 8px; padding: 12px 16px; border-left: 4px solid #10b981;">
                    <span style="color: #065f46; font-size: 13px;">
                        Al autorizar, la póliza cambiará a estado <strong>"AUTORIZADO"</strong> y quedará registrada oficialmente.
                    </span>
                </div>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Autorizar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#64748b',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            autorizarPoliza();
        }
    });
};

const autorizarPoliza = () => {
    processingAutorizar.value = true;
    
    const id = props.movimiento.id_movimiento || props.movimiento.id;
    
    axios.post(route('movimientos.autorizar', id))
        .then(() => {
            processingAutorizar.value = false;
            Swal.fire({
                title: '¡Póliza Autorizada!',
                html: `
                    <div style="padding: 10px 0;">
                        <div style="background: #dcfce7; border-radius: 8px; padding: 12px; border: 1px solid #86efac;">
                            <p style="font-size: 14px; color: #166534; font-weight: 600;">
                                ✅ Póliza ${props.movimiento.folio || 'S/N'} autorizada exitosamente
                            </p>
                            <p style="font-size: 12px; color: #065f46; margin-top: 4px;">
                                Estado: <strong>AUTORIZADO</strong>
                            </p>
                        </div>
                    </div>
                `,
                icon: 'success',
                confirmButtonColor: '#10b981',
                confirmButtonText: 'Aceptar',
                timer: 4000,
                timerProgressBar: true
            }).then(() => {
                router.visit(route('movimientos.index'), { method: 'get', replace: true });
            });
        })
        .catch((error) => {
            processingAutorizar.value = false;
            const message = error.response?.data?.message || 'Error al autorizar la póliza';
            Swal.fire({
                title: 'Error',
                text: message,
                icon: 'error',
                confirmButtonColor: '#dc2626',
                confirmButtonText: 'Entendido'
            });
        });
};

// ============================================
// ELIMINAR PÓLIZA
// ============================================
const confirmarEliminar = () => {
    // ✅ Verificar permiso
    if (!permisos.value?.puede_eliminar) {
        Swal.fire({
            icon: 'error',
            title: 'Sin permisos',
            text: 'No tienes permisos para eliminar pólizas. Contacta al administrador.',
            confirmButtonColor: '#dc2626'
        });
        return;
    }

    const referencia = props.movimiento.folio || 
                       form.referencia || 
                       props.movimiento.referencia || 
                       'S/N';
    
    const tipo = form.tipo_poliza || props.movimiento.tipo_poliza || 'N/A';
    const monto = form.total_factura || props.movimiento.total_factura || props.movimiento.monto_abs || 0;
    const idPoliza = props.movimiento.id_poliza || props.movimiento.id || null;
    
    Swal.fire({
        title: 'Eliminar esta póliza?',
        html: `
            <div style="text-align: left; padding: 10px 0;">
                <p style="font-size: 15px; color: #374151; margin-bottom: 16px;">
                    Estas a punto de eliminar la siguiente póliza:
                </p>
                <div style="background: #f8fafc; border-radius: 12px; padding: 16px; border: 1px solid #e2e8f0; margin-bottom: 16px;">
                    <div style="display: flex; justify-content: space-between; padding: 4px 0; border-bottom: 1px dashed #e2e8f0;">
                        <span style="font-weight: 600; color: #64748b;">Referencia:</span>
                        <span style="font-weight: 700; color: #0f172a;">${referencia}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 4px 0; border-bottom: 1px dashed #e2e8f0;">
                        <span style="font-weight: 600; color: #64748b;">Tipo:</span>
                        <span style="font-weight: 700; color: #0f172a;">${tipo}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 4px 0;">
                        <span style="font-weight: 600; color: #64748b;">Monto:</span>
                        <span style="font-weight: 700; color: #dc2626; font-size: 16px;">$${formatNumber(monto)}</span>
                    </div>
                </div>
                <div style="background: #fef2f2; border-radius: 8px; padding: 12px 16px; border-left: 4px solid #dc2626;">
                    <span style="color: #991b1b; font-size: 13px;">
                        Esta acción no se puede deshacer. Se eliminarán todos los datos relacionados con esta póliza.
                    </span>
                </div>
            </div>
        `,
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Sí, eliminar definitivamente',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            eliminarPoliza();
        }
    });
};

const eliminarPoliza = () => {
    processing.value = true;
    
    const id = props.movimiento.id_movimiento || props.movimiento.id;
    
    if (!id) {
        Swal.fire({
            title: 'Error',
            text: 'No se pudo identificar la póliza a eliminar.',
            icon: 'error',
            confirmButtonColor: '#dc2626',
            confirmButtonText: 'Entendido'
        });
        processing.value = false;
        return;
    }
    
    const referencia = props.movimiento.folio || 
                       form.referencia || 
                       props.movimiento.referencia || 
                       'S/N';
    
    axios.delete(route('movimientos.destroy', id))
        .then(() => {
            processing.value = false;
            Swal.fire({
                title: 'Póliza eliminada',
                html: `
                    <div style="padding: 10px 0;">
                        <p style="font-size: 14px; color: #374151;">
                            La póliza <strong>${referencia}</strong> ha sido eliminada exitosamente.
                        </p>
                    </div>
                `,
                icon: 'success',
                confirmButtonColor: '#10b981',
                confirmButtonText: 'Aceptar',
                timer: 3000,
                timerProgressBar: true
            }).then(() => {
                router.visit(route('movimientos.index'), { method: 'get', replace: true });
            });
        })
        .catch((error) => {
            processing.value = false;
            console.error('Error al eliminar:', error);
            const message = error.response?.data?.message || 'No se pudo eliminar la póliza. Verifica que no tenga dependencias.';
            Swal.fire({
                title: 'Error al eliminar',
                text: message,
                icon: 'error',
                confirmButtonColor: '#dc2626',
                confirmButtonText: 'Entendido'
            });
        });
};

// ============================================
// SUBMIT
// ============================================
const submit = () => {
    // ✅ Verificar permiso para editar
    if (!permisos.value?.puede_editar) {
        Swal.fire({
            icon: 'error',
            title: 'Sin permisos',
            text: 'No tienes permisos para editar pólizas. Contacta al administrador.',
            confirmButtonColor: '#dc2626'
        });
        return;
    }

    processing.value = true;

    if (tipoPolizaSeleccionado.value === 'INGRESO_EGRESO') {
        if (form.id_tipo_iva && form.total_factura > 0) {
            const suma = form.monto_base + form.monto_iva;
            const total = parseFloat(form.total_factura);
            if (Math.abs(suma - total) > 0.01) {
                const ajuste = total - suma;
                form.monto_iva = Math.round((form.monto_iva + ajuste) * 100) / 100;
            }
        }
        
        if (form.tipo_poliza === 'EGRESO' && !form.id_cuenta_fondeadora) {
            alertRef.value?.show({ type: 'error', title: 'Error', message: 'Selecciona una cuenta fondeadora para el egreso', buttonText: 'Entendido' });
            processing.value = false;
            return;
        }
        if (form.es_por_pagar && !form.fecha_vencimiento) {
            alertRef.value?.show({ type: 'error', title: 'Error', message: 'Ingresa una fecha de vencimiento', buttonText: 'Entendido' });
            processing.value = false;
            return;
        }
        if (form.total_factura <= 0) {
            alertRef.value?.show({ type: 'error', title: 'Error', message: 'El total debe ser mayor a 0', buttonText: 'Entendido' });
            processing.value = false;
            return;
        }

        form.put(route('movimientos.update', props.movimiento.id), {
            preserveState: false,
            preserveScroll: false,
            onSuccess: () => {
                processing.value = false;
                router.visit(route('movimientos.index'), { method: 'get', replace: true });
            },
            onError: (errors) => {
                processing.value = false;
                const firstError = Object.values(errors)[0];
                alertRef.value?.show({ 
                    type: 'error', 
                    title: 'Error', 
                    message: firstError || 'Error al actualizar la póliza.', 
                    buttonText: 'Entendido' 
                });
            }
        });
    } else {
        if (!formTraspaso.id_cuenta_origen) {
            alertRef.value?.show({ type: 'error', title: 'Error', message: 'Selecciona una cuenta origen', buttonText: 'Entendido' });
            processing.value = false;
            return;
        }
        if (!formTraspaso.id_cuenta_destino) {
            alertRef.value?.show({ type: 'error', title: 'Error', message: 'Selecciona una cuenta destino', buttonText: 'Entendido' });
            processing.value = false;
            return;
        }
        if (formTraspaso.id_cuenta_origen === formTraspaso.id_cuenta_destino) {
            alertRef.value?.show({ type: 'error', title: 'Error', message: 'La cuenta origen y destino no pueden ser la misma', buttonText: 'Entendido' });
            processing.value = false;
            return;
        }
        if (formTraspaso.monto <= 0) {
            alertRef.value?.show({ type: 'error', title: 'Error', message: 'El monto debe ser mayor a 0', buttonText: 'Entendido' });
            processing.value = false;
            return;
        }
        if (formTraspaso.es_por_pagar && !formTraspaso.fecha_vencimiento) {
            alertRef.value?.show({ type: 'error', title: 'Error', message: 'Ingresa una fecha de vencimiento', buttonText: 'Entendido' });
            processing.value = false;
            return;
        }

        formTraspaso.put(route('movimientos.update', props.movimiento.id), {
            preserveState: false,
            preserveScroll: false,
            onSuccess: () => {
                processing.value = false;
                router.visit(route('movimientos.index'), { method: 'get', replace: true });
            },
            onError: (errors) => {
                processing.value = false;
                const firstError = Object.values(errors)[0];
                alertRef.value?.show({ 
                    type: 'error', 
                    title: 'Error', 
                    message: firstError || 'Error al actualizar el traspaso.', 
                    buttonText: 'Entendido' 
                });
            }
        });
    }
};

// ============================================
// RESTAURAR FUNCIONES
// ============================================
const buscarPersonas = async (search) => {
    if (!search || search.length < 2) {
        if (personas.value.length === 0) {
            try {
                const res = await axios.get(route('movimientos.buscar.personas'), { params: { q: '' } });
                personas.value = res.data;
            } catch (error) { console.error('Error al cargar personas:', error); }
        }
        return;
    }
    try {
        const res = await axios.get(route('movimientos.buscar.personas'), { params: { q: search } });
        personas.value = res.data;
    } catch (error) { console.error('Error al buscar personas:', error); }
};

const cambiarCuentaFondeadora = async () => {
    if (!form.id_cuenta_fondeadora) { 
        cuentaFondeadoraSeleccionada.value = null; 
        return; 
    }
    try {
        const res = await axios.get(route('movimientos.saldo.cuenta'), { 
            params: { id: form.id_cuenta_fondeadora } 
        });
        if (res.data) {
            cuentaFondeadoraSeleccionada.value = {
                id_cuenta: res.data.id_cuenta,
                nombre_cuenta: res.data.cuenta,
                saldo: res.data.saldo || 0
            };
        }
        clearError('id_cuenta_fondeadora');
    } catch (error) { 
        console.error('Error al obtener saldo:', error); 
        cuentaFondeadoraSeleccionada.value = null;
    }
};

const obtenerSaldoCuenta = (idCuenta) => {
    if (!idCuenta) return 0;
    const cuenta = cuentas.value.find(c => c.id_cuenta === idCuenta);
    return cuenta ? cuenta.saldo_inicial || 0 : 0;
};

const calcularDesglose = async () => {
    if (!form.total_factura || form.total_factura <= 0 || !form.id_tipo_iva) {
        form.monto_base = 0;
        form.monto_iva = 0;
        mostrarAvisoRedondeo.value = false;
        return;
    }
    
    try {
        const ivaSeleccionado = tiposIva.value.find(iva => iva.id === form.id_tipo_iva);
        if (!ivaSeleccionado) {
            form.monto_base = 0;
            form.monto_iva = 0;
            mostrarAvisoRedondeo.value = false;
            return;
        }
        
        const porcentaje = ivaSeleccionado.porcentaje || 0;
        const total = parseFloat(form.total_factura);
        const factor = 1 + (porcentaje / 100);
        
        const base = total / factor;
        const iva = total - base;
        
        form.monto_base = Math.round(base * 100) / 100;
        form.monto_iva = Math.round(iva * 100) / 100;
        
        const suma = form.monto_base + form.monto_iva;
        const diferencia = Math.abs(suma - total);
        
        if (diferencia > 0.01) {
            const ajuste = total - suma;
            form.monto_iva = Math.round((form.monto_iva + ajuste) * 100) / 100;
            mostrarAvisoRedondeo.value = true;
        } else {
            mostrarAvisoRedondeo.value = false;
        }
    } catch (error) { 
        console.error('Error al calcular desglose:', error); 
        form.monto_base = 0;
        form.monto_iva = 0;
        mostrarAvisoRedondeo.value = false;
    }
};

watch(
    () => [form.total_factura, form.id_tipo_iva],
    () => {
        calcularDesglose();
    },
    { deep: true }
);

watch(
    () => form.tipo_poliza,
    (nuevoValor) => {
        if (nuevoValor === 'INGRESO') {
            form.id_cuenta_fondeadora = null;
            cuentaFondeadoraSeleccionada.value = null;
        }
    }
);

const toggleFiscal = () => {
    if (!form.es_fiscal) {
        form.fecha_factura = null;
        form.numero_factura = null;
        archivos.value.pdf = null;
        archivos.value.xml = null;
    }
};

const handleFileUpload = (tipo, event) => {
    const file = event.target.files[0];
    if (file) {
        archivos.value[tipo] = file;
    }
};

const clearError = (field) => {
    if (form.errors[field]) delete form.errors[field];
};

const clearErrorTraspaso = (field) => {
    if (formTraspaso.errors[field]) delete formTraspaso.errors[field];
};

const formatNumber = (value) => {
    if (value === null || value === undefined || value === '') return '0.00';
    const num = parseFloat(value);
    if (isNaN(num)) return '0.00';
    return num.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const seleccionarTipo = (tipo) => {
    tipoPolizaSeleccionado.value = tipo;
    form.clearErrors();
    formTraspaso.clearErrors();
};

// ============================================
// MODAL MARCADOR
// ============================================
const abrirModalMarcador = () => {
    nuevoMarcador.value = { nombre: '', descripcion: '' };
    modalMarcadorVisible.value = true;
};

const cerrarModalMarcador = () => {
    modalMarcadorVisible.value = false;
    nuevoMarcador.value = { nombre: '', descripcion: '' };
};

const guardarMarcador = async () => {
    guardandoMarcador.value = true;
    try {
        const res = await axios.post(route('movimientos.marcadores.store'), {
            nombre_marcador: nuevoMarcador.value.nombre,
            descripcion: nuevoMarcador.value.descripcion
        });
        
        if (res.data.success) {
            marcadores.value.push(res.data.data);
            if (tipoPolizaSeleccionado.value === 'INGRESO_EGRESO') {
                form.id_marcador = res.data.data.id;
            } else {
                formTraspaso.id_marcador = res.data.data.id;
            }
            cerrarModalMarcador();
            alertRef.value?.show({ 
                type: 'success', 
                title: 'Marcador creado', 
                message: 'El marcador se ha creado y seleccionado correctamente.', 
                buttonText: 'Aceptar' 
            });
        }
    } catch (error) {
        alertRef.value?.show({ 
            type: 'error', 
            title: 'Error', 
            message: error.response?.data?.message || 'Error al crear el marcador', 
            buttonText: 'Entendido' 
        });
    } finally { guardandoMarcador.value = false; }
};

// ============================================
// MOUNTED
// ============================================
onMounted(async () => {
    if (props.cuentas_fondeadoras && props.cuentas_fondeadoras.length > 0) {
        cuentasFondeadoras.value = props.cuentas_fondeadoras;
        
        if (form.tipo_poliza === 'EGRESO' && form.id_cuenta_fondeadora) {
            const cuentaEncontrada = cuentasFondeadoras.value.find(
                c => c.id_cuenta === form.id_cuenta_fondeadora
            );
            if (cuentaEncontrada) {
                cuentaFondeadoraSeleccionada.value = {
                    id_cuenta: cuentaEncontrada.id_cuenta,
                    nombre_cuenta: cuentaEncontrada.nombre_cuenta,
                    saldo: cuentaEncontrada.saldo_inicial || 0
                };
            } else {
                form.id_cuenta_fondeadora = cuentasFondeadoras.value[0]?.id_cuenta || null;
                if (form.id_cuenta_fondeadora) {
                    await cambiarCuentaFondeadora();
                }
            }
        } else if (form.tipo_poliza === 'INGRESO') {
            form.id_cuenta_fondeadora = null;
            cuentaFondeadoraSeleccionada.value = null;
        }
    }
    
    if (props.tipos_iva && props.tipos_iva.length > 0) {
        tiposIva.value = props.tipos_iva;
        if (props.movimiento.id_tipo_iva) {
            form.id_tipo_iva = props.movimiento.id_tipo_iva;
        }
    }

    if (props.cuentas && props.cuentas.length > 0) {
        cuentas.value = props.cuentas;
    }

    if (props.marcadores && props.marcadores.length > 0) {
        marcadores.value = props.marcadores;
    }

    buscarPersonas('');
    
    if (props.movimiento.tipo_poliza === 'TRASPASO' || props.es_traspaso) {
        tipoPolizaSeleccionado.value = 'TRASPASO';
    } else {
        tipoPolizaSeleccionado.value = 'INGRESO_EGRESO';
    }
});
</script>

<style scoped>
/* ========== HEADER ========== */
.header-wrapper {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    padding: 4px 0;
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
    background: rgba(255, 255, 255, 0.8);
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: #6b7280;
    transition: all 0.3s ease;
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
    font-size: 0.85rem;
    color: #6b7280;
    margin: 0;
}

.header-right {
    display: flex;
    align-items: center;
    gap: 12px;
}

.status-badge {
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    transition: all 0.3s ease;
}

.status-success {
    background: linear-gradient(135deg, #d1fae5, #a7f3d0);
    color: #065f46;
}

.status-error {
    background: linear-gradient(135deg, #fecaca, #fca5a5);
    color: #991b1b;
    animation: shake 0.5s ease;
}

.status-progress {
    background: linear-gradient(135deg, #e0e7ff, #c7d2fe);
    color: #4338ca;
}

/* ========== BOTÓN AUTORIZAR ========== */
.btn-autorizar-premium {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 20px;
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    border: none;
    border-radius: 12px;
    font-weight: 700;
    font-size: 0.85rem;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    height: 44px;
    box-shadow: 0 4px 16px rgba(16, 185, 129, 0.3);
}

.btn-autorizar-premium:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(16, 185, 129, 0.4);
}

.btn-autorizar-premium:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.btn-autorizar-premium svg {
    width: 20px;
    height: 20px;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}

/* ========== PAGE CONTENT ========== */
.page-content {
    padding: 1.5rem 0;
}

.container-custom {
    max-width: 72rem;
    margin: 0 auto;
    padding: 0 1.5rem;
}

/* ========== FORM CARD ========== */
.form-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
    border: 1px solid #f3f4f6;
    padding: 2rem;
    transition: all 0.3s ease;
}

.form-card:hover {
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
}

/* ========== TIPO SELECTOR PREMIUM ========== */
.tipo-selector-premium {
    display: flex;
    gap: 12px;
    margin-bottom: 28px;
    padding: 6px;
    background: linear-gradient(135deg, #f8fafc, #eef2ff);
    border-radius: 14px;
    border: 2px solid rgba(102, 126, 234, 0.15);
}

.tipo-btn-premium {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 14px 24px;
    border: none;
    border-radius: 10px;
    background: transparent;
    color: #64748b;
    font-weight: 600;
    font-size: 0.95rem;
    cursor: pointer;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.tipo-btn-content {
    display: flex;
    align-items: center;
    gap: 10px;
    position: relative;
    z-index: 2;
}

.tipo-btn-premium .tipo-btn-glow {
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, #667eea, #764ba2);
    opacity: 0;
    border-radius: 10px;
    transition: all 0.4s ease;
}

.tipo-btn-premium.active {
    color: white;
    box-shadow: 0 8px 32px rgba(102, 126, 234, 0.3);
}

.tipo-btn-premium.active .tipo-btn-glow {
    opacity: 1;
}

.tipo-btn-icon {
    font-size: 1.4rem;
}

.tipo-btn-label {
    font-weight: 700;
}

.tipo-btn-badge {
    font-size: 0.6rem;
    padding: 2px 12px;
    border-radius: 20px;
    background: rgba(255, 255, 255, 0.2);
    color: rgba(255, 255, 255, 0.9);
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.tipo-btn-premium:not(.active) .tipo-btn-badge {
    background: #e2e8f0;
    color: #64748b;
}

/* ========== SECTIONS ========== */
.section-block-premium {
    margin-bottom: 2.5rem;
    padding-bottom: 2.5rem;
    border-bottom: 2px solid #f1f5f9;
    transition: all 0.3s ease;
}

.section-block-premium:last-of-type {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.section-header-premium {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 20px;
}

.section-icon-premium {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    border-radius: 14px;
    color: white;
    flex-shrink: 0;
    transition: all 0.3s ease;
    box-shadow: 0 4px 16px rgba(102, 126, 234, 0.25);
}

.section-icon-premium.blue { background: linear-gradient(135deg, #667eea, #764ba2); }
.section-icon-premium.purple { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
.section-icon-premium.green { background: linear-gradient(135deg, #10b981, #059669); }
.section-icon-premium.orange { background: linear-gradient(135deg, #f59e0b, #d97706); }
.section-icon-premium.teal { background: linear-gradient(135deg, #14b8a6, #0d9488); }

.icon-svg-premium {
    width: 22px;
    height: 22px;
}

.section-title-text {
    font-size: 1.05rem;
    font-weight: 700;
    color: #0f172a;
    margin: 0;
}

.section-title-sub {
    font-size: 0.8rem;
    color: #94a3b8;
    margin: 0;
}

/* ========== FORM GRID ========== */
.form-grid-premium {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
}

.full-width-premium {
    grid-column: 1 / -1;
}

/* ========== FORM GROUP ========== */
.form-group-premium {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.form-label-premium {
    font-size: 0.85rem;
    font-weight: 600;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 6px;
}

.required-star {
    color: #ef4444;
    font-weight: 700;
}

/* ========== INPUTS ========== */
.input-wrapper-premium {
    position: relative;
}

.form-input-premium {
    width: 100%;
    padding: 10px 14px;
    font-size: 0.9rem;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    background: white;
    color: #1f2937;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    outline: none;
    height: 44px;
}

.form-input-premium:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    transform: translateY(-1px);
}

.form-input-premium.error {
    border-color: #ef4444;
    animation: shake 0.5s ease;
}

.form-select-premium {
    appearance: none;
    cursor: pointer;
    padding-right: 40px;
}

.form-textarea-premium {
    width: 100%;
    padding: 10px 14px;
    font-size: 0.9rem;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    background: white;
    color: #1f2937;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    outline: none;
    resize: vertical;
    min-height: 80px;
    font-family: inherit;
}

.form-textarea-premium:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.input-prefix-premium {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    font-weight: 700;
    color: #64748b;
    font-size: 0.9rem;
    z-index: 1;
}

.input-prefix-premium + .form-input-premium {
    padding-left: 28px;
}

.input-icon-premium {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    pointer-events: none;
}

.icon-svg-sm-premium {
    width: 18px;
    height: 18px;
}

/* ========== CHECKBOX ========== */
.checkbox-group-premium {
    grid-column: span 1;
}

.checkbox-grid-premium {
    display: flex;
    gap: 20px;
    padding: 6px 0;
}

.checkbox-premium {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    font-size: 0.9rem;
    color: #374151;
    padding: 4px 0;
    position: relative;
}

.checkbox-input-premium {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}

.checkbox-custom-premium {
    width: 20px;
    height: 20px;
    border: 2px solid #d1d5db;
    border-radius: 6px;
    flex-shrink: 0;
    transition: all 0.3s ease;
    position: relative;
}

.checkbox-custom-premium::after {
    content: '';
    position: absolute;
    inset: 2px;
    background: #667eea;
    border-radius: 3px;
    transform: scale(0);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.checkbox-input-premium:checked + .checkbox-custom-premium {
    border-color: #667eea;
}

.checkbox-input-premium:checked + .checkbox-custom-premium::after {
    transform: scale(1);
}

.checkbox-text-premium {
    font-weight: 500;
    color: #1e293b;
}

/* ========== ERROR MESSAGES ========== */
.error-message-premium {
    font-size: 0.75rem;
    color: #ef4444;
    display: flex;
    align-items: center;
    gap: 4px;
    margin-top: 4px;
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from { opacity: 0; transform: translateY(-8px); }
    to { opacity: 1; transform: translateY(0); }
}

.field-hint-premium {
    font-size: 0.7rem;
    color: #94a3b8;
    margin-top: 2px;
}

.char-counter-premium {
    font-size: 0.7rem;
    color: #94a3b8;
    text-align: right;
    margin-top: 2px;
}

/* ========== INFO INGRESO ========== */
.info-message-ingreso {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 16px;
    background: linear-gradient(135deg, #ecfdf5, #d1fae5);
    border-radius: 10px;
    border: 2px solid #6ee7b7;
    font-size: 0.85rem;
    color: #065f46;
}

.info-icon-ingreso {
    font-size: 1.2rem;
}

/* ========== DESGLOSE ========== */
.desglose-box-premium {
    background: linear-gradient(135deg, #f8faff, #eef2ff);
    border: 2px solid rgba(102, 126, 234, 0.2);
    border-radius: 14px;
    padding: 18px 24px;
    margin-top: 16px;
    transition: all 0.3s ease;
}

.desglose-box-premium:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(102, 126, 234, 0.1);
}

.desglose-header-premium {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 14px;
}

.desglose-title-premium {
    font-size: 0.95rem;
    font-weight: 700;
    color: #1e293b;
}

.desglose-subtitle-premium {
    font-size: 0.7rem;
    color: #94a3b8;
    background: rgba(255,255,255,0.6);
    padding: 2px 12px;
    border-radius: 20px;
}

.desglose-grid-premium {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
}

.desglose-item-premium {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 6px 0;
}

.desglose-item-premium:not(:last-child) {
    border-right: 2px solid rgba(102, 126, 234, 0.1);
}

.desglose-label-premium {
    font-size: 0.7rem;
    color: #64748b;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.desglose-value-premium {
    font-size: 1.2rem;
    font-weight: 700;
    margin-top: 4px;
}

.font-bold {
    font-weight: 700;
}

.desglose-verificacion {
    margin-top: 10px;
    padding: 6px 12px;
    background: #fffbeb;
    border: 1px solid #fcd34d;
    border-radius: 6px;
    text-align: center;
}

.desglose-verificacion-text {
    font-size: 0.7rem;
    color: #92400e;
    font-weight: 500;
}

/* ========== DESGLOSE FISCAL ========== */
.desglose-fiscal-box-premium {
    background: linear-gradient(135deg, #fefce8, #fef3c7);
    border: 2px solid #fcd34d;
    border-radius: 14px;
    padding: 18px 24px;
    margin-top: 16px;
    transition: all 0.3s ease;
}

.desglose-fiscal-box-premium:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(245, 158, 11, 0.15);
}

.desglose-fiscal-header-premium {
    margin-bottom: 12px;
}

.desglose-fiscal-title-premium {
    font-size: 0.95rem;
    font-weight: 700;
    color: #92400e;
    display: block;
}

.desglose-fiscal-subtitle-premium {
    font-size: 0.75rem;
    color: #b45309;
    font-style: italic;
}

.desglose-fiscal-grid-premium {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    background: white;
    padding: 14px 16px;
    border-radius: 10px;
    border: 1px solid #fde68a;
    margin-top: 10px;
}

.desglose-fiscal-item-premium {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.desglose-fiscal-label-premium {
    font-size: 0.6rem;
    color: #78716c;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.desglose-fiscal-value-premium {
    font-size: 0.95rem;
    font-weight: 600;
    color: #1c1917;
    margin-top: 2px;
}

.desglose-fiscal-footer-premium {
    margin-top: 14px;
    padding-top: 12px;
    border-top: 2px dashed #fcd34d;
}

.desglose-fiscal-leyenda-premium {
    font-size: 0.8rem;
    color: #92400e;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    font-weight: 600;
}

/* ========== SALDO BOX ========== */
.saldo-box-premium {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 18px;
    border-radius: 10px;
    margin-top: 16px;
    flex-wrap: wrap;
    transition: all 0.3s ease;
}

.saldo-ok-premium {
    background: linear-gradient(135deg, #ecfdf5, #d1fae5);
    border: 2px solid #6ee7b7;
}

.saldo-error-premium {
    background: linear-gradient(135deg, #fef2f2, #fecaca);
    border: 2px solid #fca5a5;
    animation: shake 0.5s ease;
}

.saldo-icon-premium { font-size: 1.2rem; }
.saldo-label-premium { font-size: 0.85rem; color: #374151; font-weight: 600; }
.saldo-value-premium { font-size: 1.1rem; font-weight: 700; color: #0f172a; }
.saldo-warning-premium { font-size: 0.8rem; font-weight: 700; color: #dc2626; }

/* ========== TRASPASO LAYOUT ========== */
.traspaso-layout-premium {
    display: grid;
    grid-template-columns: 1fr auto 1fr;
    gap: 24px;
    align-items: stretch;
    margin-bottom: 24px;
}

@media (max-width: 992px) {
    .traspaso-layout-premium {
        grid-template-columns: 1fr;
        gap: 16px;
    }
    
    .transfer-center-premium {
        padding: 12px 0;
    }
}

.cuenta-card-premium {
    background: white;
    border-radius: 14px;
    border: 2px solid #e5e7eb;
    overflow: hidden;
    transition: all 0.3s ease;
    height: 100%;
}

.cuenta-card-premium:hover {
    border-color: #667eea;
    box-shadow: 0 8px 24px rgba(102, 126, 234, 0.1);
    transform: translateY(-2px);
}

.cuenta-card-header-premium {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 18px;
    background: linear-gradient(135deg, #f8fafc, #eef2ff);
    border-bottom: 2px solid #e5e7eb;
}

.cuenta-card-icon {
    font-size: 1.2rem;
}

.cuenta-card-title {
    font-weight: 700;
    color: #0f172a;
    flex: 1;
}

.cuenta-card-badge {
    font-size: 0.6rem;
    padding: 3px 12px;
    border-radius: 20px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.cuenta-card-badge.sale {
    background: #fef2f2;
    color: #dc2626;
}

.cuenta-card-badge.receive {
    background: #ecfdf5;
    color: #10b981;
}

.cuenta-card-body-premium {
    padding: 16px 18px;
}

.cuenta-card-info {
    display: flex;
    justify-content: space-between;
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px solid #f1f5f9;
}

.info-label {
    font-size: 0.7rem;
    color: #94a3b8;
    font-weight: 600;
}

.info-value {
    font-size: 0.9rem;
    font-weight: 700;
    color: #0f172a;
}

/* ===== TRANSFER CENTER ===== */
.transfer-center-premium {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 8px;
}

.transfer-arrows-premium {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
}

.arrow-icon-premium {
    width: 48px;
    height: 48px;
    animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

.transfer-text-premium {
    text-align: center;
}

.transfer-label {
    font-size: 0.7rem;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.transfer-amount {
    font-size: 1.1rem;
    font-weight: 700;
    color: #4f46e5;
    margin-top: 2px;
}

.transfer-particles {
    display: flex;
    gap: 6px;
    margin-top: 6px;
}

.particle-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #667eea;
    animation: float 2s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0); opacity: 1; }
    50% { transform: translateY(-10px); opacity: 0.3; }
}

/* ===== FILE UPLOAD ===== */
.file-upload-premium {
    position: relative;
    display: flex;
    align-items: center;
    gap: 8px;
}

.file-input-premium {
    position: absolute;
    opacity: 0;
    width: 100%;
    height: 100%;
    cursor: pointer;
}

.file-upload-area-premium {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 16px;
    border: 2px dashed #d1d5db;
    border-radius: 10px;
    background: #f9fafb;
    transition: all 0.3s ease;
}

.file-upload-premium:hover .file-upload-area-premium {
    border-color: #667eea;
    background: linear-gradient(135deg, #f8f7ff, #eef2ff);
    transform: translateY(-2px);
}

.file-upload-icon-premium {
    font-size: 1.2rem;
}

.file-upload-text-premium {
    font-size: 0.85rem;
    color: #6b7280;
    font-weight: 500;
}

.file-remove-premium {
    padding: 2px 10px;
    background: linear-gradient(135deg, #fecaca, #fca5a5);
    border: none;
    border-radius: 6px;
    color: #991b1b;
    cursor: pointer;
    font-weight: 800;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.file-remove-premium:hover {
    transform: scale(1.1);
}

/* ===== BOTÓN AGREGAR MARCADOR ===== */
.btn-add-marcador-premium {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    border: 2px dashed #d1d5db;
    border-radius: 10px;
    background: white;
    color: #6b7280;
    cursor: pointer;
    transition: all 0.3s ease;
    flex-shrink: 0;
}

.btn-add-marcador-premium:hover {
    border-color: #667eea;
    color: #4f46e5;
    background: linear-gradient(135deg, #f8f7ff, #eef2ff);
    transform: scale(1.05) rotate(90deg);
}

/* ===== BUTTONS ===== */
.btn-premium {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 28px;
    font-weight: 700;
    border: none;
    border-radius: 12px;
    font-size: 0.9rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
    text-decoration: none;
    height: 44px;
}

.btn-icon-premium {
    width: 18px;
    height: 18px;
}

.btn-delete-premium {
    background: #fef2f2;
    color: #dc2626;
    border: 2px solid #fca5a5;
}

.btn-delete-premium:hover {
    background: #fecaca;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.2);
}

.btn-cancel-premium {
    background: #f1f5f9;
    color: #64748b;
    border: 2px solid #e5e7eb;
}

.btn-cancel-premium:hover {
    background: #fecaca;
    color: #dc2626;
    transform: translateY(-2px);
}

.btn-submit-premium {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);
}

.btn-submit-premium:hover:not(:disabled) {
    transform: translateY(-3px);
    box-shadow: 0 8px 32px rgba(102, 126, 234, 0.4);
}

.btn-submit-premium:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

/* ===== FORM ACTIONS ===== */
.form-actions-premium {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    margin-top: 24px;
    padding-top: 24px;
    border-top: 2px solid #f1f5f9;
}

.actions-left-premium,
.actions-right-premium {
    display: flex;
    gap: 12px;
    align-items: center;
    flex-wrap: wrap;
}

.total-card-premium {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 20px;
    background: linear-gradient(135deg, #f8fafc, #eef2ff);
    border-radius: 12px;
    border: 1px solid rgba(102, 126, 234, 0.15);
}

.total-label-premium {
    font-size: 0.7rem;
    color: #64748b;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.total-value-premium {
    font-size: 1.2rem;
    font-weight: 700;
    color: #0f172a;
}

/* ===== INFO BOX ===== */
.info-box-premium {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 20px;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.06), rgba(118, 75, 162, 0.06));
    border-radius: 12px;
    border-left: 4px solid #667eea;
    font-size: 0.85rem;
    color: #4b5563;
    margin-top: 4px;
}

.info-icon-premium {
    width: 20px;
    height: 20px;
    flex-shrink: 0;
}

.text-danger-premium {
    color: #ef4444;
}

/* ===== MODAL ===== */
.modal-overlay-premium {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(6px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    animation: fadeIn 0.3s ease;
}

.modal-container-premium {
    background: white;
    border-radius: 20px;
    width: 100%;
    max-width: 450px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 24px 64px rgba(0, 0, 0, 0.2);
    animation: slideUp 0.3s ease;
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px) scale(0.95); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

.modal-header-premium {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 24px;
    border-bottom: 1px solid #f3f4f6;
}

.modal-title-premium {
    font-size: 1.1rem;
    font-weight: 700;
    color: #0f172a;
    margin: 0;
}

.modal-close-premium {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border: none;
    background: #f3f4f6;
    border-radius: 50%;
    color: #6b7280;
    cursor: pointer;
    transition: all 0.3s ease;
}

.modal-close-premium:hover {
    background: #fecaca;
    color: #dc2626;
}

.modal-body-premium {
    padding: 24px;
}

.modal-actions-premium {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 20px;
    padding-top: 16px;
    border-top: 1px solid #f3f4f6;
}

/* ===== SPINNER ===== */
.spinner-border-premium {
    display: inline-block;
    width: 1rem;
    height: 1rem;
    border: 0.2em solid currentColor;
    border-right-color: transparent;
    border-radius: 50%;
    animation: spinner 0.75s linear infinite;
}

@keyframes spinner {
    to { transform: rotate(360deg); }
}

/* ===== ANIMATIONS ===== */
.fade-slide {
    animation: fadeSlide 0.4s ease;
}

@keyframes fadeSlide {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    .form-card {
        padding: 1.25rem;
    }

    .form-grid-premium {
        grid-template-columns: 1fr;
        gap: 14px;
    }

    .full-width-premium {
        grid-column: 1;
    }

    .header-wrapper {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }

    .header-right {
        width: 100%;
        justify-content: flex-start;
        flex-wrap: wrap;
    }

    .form-actions-premium {
        flex-direction: column-reverse;
        align-items: stretch;
    }

    .actions-left-premium,
    .actions-right-premium {
        width: 100%;
        justify-content: center;
        flex-direction: column;
    }

    .btn-premium {
        width: 100%;
        justify-content: center;
        padding: 10px 20px;
        height: 44px;
    }

    .container-custom {
        padding: 0 0.75rem;
    }

    .status-badge {
        font-size: 0.7rem;
        padding: 4px 12px;
    }

    .section-icon-premium {
        width: 38px;
        height: 38px;
    }

    .icon-svg-premium {
        width: 18px;
        height: 18px;
    }

    .section-title-text {
        font-size: 0.95rem;
    }

    .desglose-grid-premium {
        grid-template-columns: 1fr;
    }

    .desglose-item-premium:not(:last-child) {
        border-right: none;
        border-bottom: 2px solid rgba(102, 126, 234, 0.1);
        padding-bottom: 12px;
    }

    .desglose-fiscal-grid-premium {
        grid-template-columns: 1fr;
    }

    .tipo-selector-premium {
        flex-direction: column;
    }

    .checkbox-grid-premium {
        flex-direction: column;
        gap: 8px;
    }

    .checkbox-group-premium {
        grid-column: 1;
    }

    .modal-container-premium {
        margin: 16px;
        max-height: 80vh;
    }

    .btn-autorizar-premium {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .form-card {
        padding: 1rem;
        border-radius: 16px;
    }

    .header-title {
        font-size: 1.1rem;
    }

    .section-header-premium {
        gap: 10px;
    }

    .section-icon-premium {
        width: 34px;
        height: 34px;
    }

    .icon-svg-premium {
        width: 16px;
        height: 16px;
    }
}
</style>