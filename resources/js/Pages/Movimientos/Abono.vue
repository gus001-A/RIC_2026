<template>
    <AppLayout :title="tituloPagina">
        <template #header>
            <div class="header-wrapper">
                <div class="header-left">
                    <Link :href="route('movimientos.index', { vista: 'diferidas' })" class="btn-back">
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
                        <span v-if="hasErrors">Error - {{ errorCount }} campo(s) pendiente(s)</span>
                        <span v-else-if="isComplete">Completado</span>
                        <span v-else>{{ Math.round(progressPercentage) }}%</span>
                    </div>
                </div>
            </div>
        </template>

        <div class="page-content">
            <div class="container-custom">
                <div class="form-card">
                    <!-- ============================================ -->
                    <!-- LEYENDA INFORMATIVA -->
                    <!-- ============================================ -->
                    <div class="info-leyenda-premium">
                        <div class="info-leyenda-icon">
                            <svg width="24" height="24" fill="none" stroke="#92400e" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="info-leyenda-content">
                            <span class="info-leyenda-texto">
                                Abono <strong>#{{ numeroAbono }}</strong> para la factura <strong>{{ movimiento.referencia || 'S/N' }}</strong>. 
                                Ingrese el monto del abono a registrar.
                            </span>
                        </div>
                    </div>

                    <!-- ============================================ -->
                    <!-- DATOS DE LA PÓLIZA -->
                    <!-- ============================================ -->
                    <div class="section-block-premium">
                        <div class="section-header-premium">
                            <div class="section-icon-premium blue">
                                <svg class="icon-svg-premium" fill="none" stroke="white" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="section-title-text">Informacion de la Poliza</h3>
                                <p class="section-title-sub">Datos generales de la poliza (no modificables)</p>
                            </div>
                        </div>

                        <div class="form-grid-premium readonly-grid">
                            <div class="form-group-premium">
                                <label class="form-label-premium">Tipo de Poliza</label>
                                <div class="readonly-value">
                                    <span class="readonly-icon">
                                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                                        </svg>
                                    </span>
                                    <span>{{ movimiento.tipo_poliza || 'N/A' }}</span>
                                </div>
                            </div>

                            <div class="form-group-premium">
                                <label class="form-label-premium">Persona</label>
                                <div class="readonly-value">
                                    <span class="readonly-icon">
                                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </span>
                                    <span>{{ movimiento.persona || 'N/A' }}</span>
                                </div>
                            </div>

                            <div class="form-group-premium">
                                <label class="form-label-premium">Cuenta</label>
                                <div class="readonly-value">
                                    <span class="readonly-icon">
                                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                        </svg>
                                    </span>
                                    <span>{{ movimiento.cuenta || 'N/A' }}</span>
                                </div>
                            </div>

                            <div class="form-group-premium">
                                <label class="form-label-premium">Marcador</label>
                                <div class="readonly-value">
                                    <span class="readonly-icon">
                                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                        </svg>
                                    </span>
                                    <span>{{ movimiento.marcador || 'Sin marcador' }}</span>
                                </div>
                            </div>

                            <div class="form-group-premium">
                                <label class="form-label-premium">Fecha Vencimiento</label>
                                <div class="readonly-value" :class="diasRestantesClass">
                                    <span class="readonly-icon">
                                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </span>
                                    <span>{{ movimiento.fecha_vencimiento || 'Sin fecha' }}</span>
                                </div>
                            </div>

                            <div class="form-group-premium">
                                <label class="form-label-premium">Dias Restantes</label>
                                <div class="readonly-value" :class="diasRestantesClass">
                                    <span class="readonly-icon">
                                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </span>
                                    <span>{{ diasRestantes > 0 ? diasRestantes + ' dias' : diasRestantes === 0 ? 'Hoy vence' : 'Vencido' }}</span>
                                </div>
                            </div>

                            <div class="form-group-premium full-width-premium">
                                <label class="form-label-premium">Nota</label>
                                <div class="readonly-value">
                                    <span class="readonly-icon">
                                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </span>
                                    <span>{{ movimiento.nota || 'Sin nota' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================ -->
                    <!-- RESUMEN FINANCIERO -->
                    <!-- ============================================ -->
                    <div class="section-block-premium">
                        <div class="section-header-premium">
                            <div class="section-icon-premium green">
                                <svg class="icon-svg-premium" fill="none" stroke="white" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="section-title-text">Resumen Financiero</h3>
                                <p class="section-title-sub">Estado actual de la poliza</p>
                            </div>
                        </div>

                        <div class="resumen-grid-premium">
                            <div class="resumen-item-premium">
                                <span class="resumen-label-premium">Total Factura</span>
                                <span class="resumen-value-premium total">${{ formatNumber(movimiento.monto_abs) }}</span>
                            </div>
                            <div class="resumen-item-premium">
                                <span class="resumen-label-premium">Total Abonado</span>
                                <span class="resumen-value-premium abonado">${{ formatNumber(movimiento.total_abonado) }}</span>
                            </div>
                            <div class="resumen-item-premium">
                                <span class="resumen-label-premium">Saldo Pendiente</span>
                                <span class="resumen-value-premium pendiente">${{ formatNumber(movimiento.saldo_pendiente) }}</span>
                            </div>
                            <div class="resumen-item-premium">
                                <span class="resumen-label-premium">Numero de Abonos</span>
                                <span class="resumen-value-premium total">{{ movimiento.abonos?.length || 0 }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- ============================================ -->
                    <!-- NUEVO ABONO -->
                    <!-- ============================================ -->
                    <div class="section-block-premium">
                        <div class="section-header-premium">
                            <div class="section-icon-premium purple">
                                <svg class="icon-svg-premium" fill="none" stroke="white" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="section-title-text">Nuevo Abono</h3>
                                <p class="section-title-sub">Registre el abono a esta poliza</p>
                            </div>
                        </div>

                        <form @submit.prevent="submitAbono" id="abonoForm" novalidate>
                            <div class="form-grid-premium">
                                <div class="form-group-premium">
                                    <label class="form-label-premium">Monto del Abono <span class="required-star">*</span></label>
                                    <div class="input-wrapper-premium">
                                        <span class="input-prefix-premium">$</span>
                                        <input type="number" step="0.01" v-model.number="abonoForm.monto_abonado"
                                               @input="calcularNuevoSaldo; clearError('monto_abonado')"
                                               class="form-input-premium"
                                               :class="{ 'error': abonoForm.errors.monto_abonado }"
                                               placeholder="0.00"
                                               min="0.01"
                                               :max="movimiento.saldo_pendiente">
                                        <button type="button" 
                                                @click="abonoForm.monto_abonado = Math.round(movimiento.saldo_pendiente * 100) / 100"
                                                class="btn-max-premium"
                                                title="Completar saldo pendiente">
                                            Max
                                        </button>
                                    </div>
                                    <div v-if="abonoForm.errors.monto_abonado" class="error-message-premium">{{ abonoForm.errors.monto_abonado }}</div>
                                    <div class="field-hint-premium">Monto maximo: ${{ formatNumber(movimiento.saldo_pendiente) }}</div>
                                </div>

                                <div class="form-group-premium">
                                    <label class="form-label-premium">Numero de Abono</label>
                                    <div class="readonly-value">
                                        <span class="readonly-icon">#</span>
                                        <span>{{ numeroAbono }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Nuevo Saldo Pendiente -->
                            <div class="nuevo-saldo-box-premium" v-if="abonoForm.monto_abonado > 0">
                                <span class="nuevo-saldo-icon">
                                    <svg width="20" height="20" fill="none" stroke="#047857" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </span>
                                <span class="nuevo-saldo-label">Nuevo saldo pendiente:</span>
                                <span class="nuevo-saldo-value">${{ formatNumber(nuevoSaldoPendiente) }}</span>
                                <span v-if="nuevoSaldoPendiente === 0" class="nuevo-saldo-liquidado">Poliza liquidada</span>
                            </div>

                            <!-- ============================================ -->
                            <!-- DATOS GENERALES DEL ABONO -->
                            <!-- ============================================ -->
                            <div class="sub-section-premium">
                                <div class="sub-section-header-premium">
                                    <span class="sub-section-title-premium">Datos Generales del Abono</span>
                                </div>

                                <div class="form-grid-premium">
                                    <div class="form-group-premium">
                                        <label class="form-label-premium">Referencia de Factura</label>
                                        <div class="readonly-value">
                                            <span class="readonly-icon">
                                                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                                                </svg>
                                            </span>
                                            <span>{{ movimiento.referencia || 'S/N' }}</span>
                                        </div>
                                    </div>

                                    <div class="form-group-premium">
                                        <label class="form-label-premium">Referencia del Abono <span class="required-star">*</span></label>
                                        <div class="input-wrapper-premium">
                                            <input type="text" v-model="abonoForm.referencia"
                                                   @input="clearError('referencia')"
                                                   class="form-input-premium"
                                                   :class="{ 'error': abonoForm.errors.referencia }"
                                                   placeholder="AB-001-2024">
                                            <button type="button" 
                                                    @click="generarReferenciaAutomatica"
                                                    class="btn-generate-premium"
                                                    title="Generar referencia automatica">
                                                <svg class="icon-svg-sm-premium" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                                </svg>
                                            </button>
                                        </div>
                                        <div v-if="abonoForm.errors.referencia" class="error-message-premium">{{ abonoForm.errors.referencia }}</div>
                                        <div class="field-hint-premium">Referencia unica para identificar este abono</div>
                                    </div>

                                    <div class="form-group-premium">
                                        <label class="form-label-premium">Cuenta de Fondo</label>
                                        <div class="readonly-value">
                                            <span class="readonly-icon">
                                                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                </svg>
                                            </span>
                                            <span>{{ movimiento.cuenta_fondeadora || 'N/A' }}</span>
                                        </div>
                                        <input type="hidden" :value="movimiento.cuenta_fondeadora_id" />
                                        <input type="hidden" v-model="abonoForm.id_cuenta_fondeadora" />
                                    </div>

                                    <div class="form-group-premium">
                                        <label class="form-label-premium">Fecha de Pago</label>
                                        <div class="readonly-value">
                                            <span class="readonly-icon">
                                                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                            </span>
                                            <span>{{ fechaActualFormateada }}</span>
                                        </div>
                                    </div>

                                    <div class="form-group-premium full-width-premium">
                                        <label class="form-label-premium">Observaciones</label>
                                        <div class="input-wrapper-premium">
                                            <textarea v-model="abonoForm.nota" rows="2"
                                                      class="form-textarea-premium"
                                                      placeholder="Notas sobre este abono..."></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ============================================ -->
                            <!-- DESGLOSE FISCAL -->
                            <!-- ============================================ -->
                            <div v-if="movimiento.monto_base && movimiento.monto_iva" class="desglose-fiscal-box-premium">
                                <div class="desglose-fiscal-header-premium">
                                    <span class="desglose-fiscal-title-premium">Desglose Fiscal del Abono</span>
                                    <span class="desglose-fiscal-subtitle-premium">Este desglose se usara para efectos fiscales y contables</span>
                                </div>
                                <div class="desglose-fiscal-grid-premium">
                                    <div class="desglose-fiscal-item-premium">
                                        <span class="desglose-fiscal-label-premium">Base Gravable (sin IVA)</span>
                                        <span class="desglose-fiscal-value-premium" style="color: #2563eb;">${{ formatNumber(montoBaseAbono) }}</span>
                                    </div>
                                    <div class="desglose-fiscal-item-premium">
                                        <span class="desglose-fiscal-label-premium">IVA</span>
                                        <span class="desglose-fiscal-value-premium" style="color: #f59e0b;">${{ formatNumber(montoIvaAbono) }}</span>
                                    </div>
                                    <div class="desglose-fiscal-item-premium">
                                        <span class="desglose-fiscal-label-premium">Total del Abono</span>
                                        <span class="desglose-fiscal-value-premium" style="color: #10b981;">${{ formatNumber(totalAbonoConIva) }}</span>
                                    </div>
                                </div>
                                <div class="desglose-fiscal-footer-premium">
                                    <span class="desglose-fiscal-leyenda-premium">El total del abono incluye IVA</span>
                                </div>
                            </div>

                            <!-- ============================================ -->
                            <!-- BOTONES -->
                            <!-- ============================================ -->
                            <div class="info-box-premium">
                                <svg class="info-icon-premium" fill="none" stroke="#667eea" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>Los campos con <strong class="text-danger-premium">*</strong> son obligatorios</span>
                            </div>

                            <div class="form-actions-premium">
                                <div class="actions-left-premium">
                                    <div class="total-card-premium">
                                        <span class="total-label-premium">Nuevo Saldo</span>
                                        <span class="total-value-premium">${{ formatNumber(nuevoSaldoPendiente) }}</span>
                                    </div>
                                    <div v-if="abonoForm.referencia" class="referencia-card-premium">
                                        <span class="referencia-label-premium">Referencia</span>
                                        <span class="referencia-value-premium">{{ abonoForm.referencia }}</span>
                                    </div>
                                </div>
                                <div class="actions-right-premium">
                                    <Link :href="route('movimientos.index', { vista: 'diferidas' })" class="btn-premium btn-cancel-premium">
                                        <svg class="btn-icon-premium" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                        Cancelar
                                    </Link>
                                    <button type="submit" 
                                            :disabled="processing || !isFormValid"
                                            class="btn-premium btn-submit-premium">
                                        <span v-if="processing" class="spinner-border-premium"></span>
                                        <svg v-else class="btn-icon-premium" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        Registrar Abono
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <AlertModal ref="alertRef" />
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, useForm, router } from '@inertiajs/vue3';
import AlertModal from '@/Components/AlertModal.vue';
import axios from 'axios';

// ============================================
// PROPS
// ============================================
const props = defineProps({
    movimiento: {
        type: Object,
        required: true
    }
});

// ============================================
// REFS
// ============================================
const alertRef = ref(null);
const processing = ref(false);

// ============================================
// FORMULARIO
// ============================================
const abonoForm = useForm({
    id_poliza: props.movimiento.id_poliza,
    monto_abonado: 0,
    fecha_abono: new Date().toISOString().split('T')[0],
    referencia: '',
    metodo_pago: 'TRANSFERENCIA',
    nota: '',
    id_cuenta_fondeadora: props.movimiento.cuenta_fondeadora_id || ''
});

// ============================================
// COMPUTED
// ============================================
const numeroAbono = computed(() => {
    return (props.movimiento.abonos?.length || 0) + 1;
});

const fechaActualFormateada = computed(() => {
    const fecha = new Date();
    return fecha.toLocaleDateString('es-MX', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
});

const nuevoSaldoPendiente = computed(() => {
    const saldoActual = props.movimiento.saldo_pendiente || 0;
    const montoAbono = parseFloat(abonoForm.monto_abonado) || 0;
    const nuevoSaldo = saldoActual - montoAbono;
    return nuevoSaldo < 0 ? 0 : Math.round(nuevoSaldo * 100) / 100;
});

const totalAbonoConIva = computed(() => {
    return Math.round(parseFloat(abonoForm.monto_abonado || 0) * 100) / 100;
});

const montoBaseAbono = computed(() => {
    if (!abonoForm.monto_abonado || abonoForm.monto_abonado <= 0) return 0;
    if (!props.movimiento.monto_base || !props.movimiento.monto_iva) return 0;
    
    const totalFactura = props.movimiento.monto_abs || 1;
    const baseFactura = Math.abs(props.movimiento.monto_base || 0);
    const ivaFactura = Math.abs(props.movimiento.monto_iva || 0);
    
    if (totalFactura <= 0) return 0;
    
    const proporcion = abonoForm.monto_abonado / totalFactura;
    const baseAbono = baseFactura * proporcion;
    return Math.round(baseAbono * 100) / 100;
});

const montoIvaAbono = computed(() => {
    if (!abonoForm.monto_abonado || abonoForm.monto_abonado <= 0) return 0;
    if (!props.movimiento.monto_base || !props.movimiento.monto_iva) return 0;
    
    const totalFactura = props.movimiento.monto_abs || 1;
    const ivaFactura = Math.abs(props.movimiento.monto_iva || 0);
    
    if (totalFactura <= 0) return 0;
    
    const proporcion = abonoForm.monto_abonado / totalFactura;
    const ivaAbono = ivaFactura * proporcion;
    return Math.round(ivaAbono * 100) / 100;
});

const diasRestantes = computed(() => {
    if (!props.movimiento.fecha_vencimiento) return null;
    const fechaVencimiento = new Date(props.movimiento.fecha_vencimiento);
    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0);
    fechaVencimiento.setHours(0, 0, 0, 0);
    const diffTime = fechaVencimiento - hoy;
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays;
});

const diasRestantesClass = computed(() => {
    if (diasRestantes.value === null) return '';
    if (diasRestantes.value < 0) return 'vencido';
    if (diasRestantes.value === 0) return 'hoy-vence';
    if (diasRestantes.value <= 3) return 'pronto-vencer';
    return '';
});

const hasErrors = computed(() => Object.keys(abonoForm.errors).length > 0);
const errorCount = computed(() => Object.keys(abonoForm.errors).length);

const isFormValid = computed(() => {
    if (!abonoForm.monto_abonado || abonoForm.monto_abonado <= 0) return false;
    if (abonoForm.monto_abonado > props.movimiento.saldo_pendiente) return false;
    if (!abonoForm.referencia || abonoForm.referencia.trim() === '') return false;
    if (hasErrors.value) return false;
    return true;
});

const progressPercentage = computed(() => {
    let filled = 0;
    const total = 2;
    if (abonoForm.monto_abonado > 0) filled++;
    if (abonoForm.referencia && abonoForm.referencia.trim() !== '') filled++;
    return total > 0 ? (filled / total) * 100 : 0;
});

const isComplete = computed(() => progressPercentage.value === 100 && !hasErrors.value);

const statusClass = computed(() => {
    if (hasErrors.value) return 'status-error';
    if (isComplete.value) return 'status-success';
    return 'status-progress';
});

const tituloPagina = computed(() => {
    return `Polizas Diferidas - ${numeroAbono.value}° Abono`;
});

const subtituloPagina = computed(() => {
    return `Registro del abono #${numeroAbono.value} para la poliza ${props.movimiento.referencia || 'S/N'}`;
});

// ============================================
// METODOS
// ============================================
const clearError = (field) => {
    if (abonoForm.errors[field]) delete abonoForm.errors[field];
};

const formatNumber = (value) => {
    if (!value && value !== 0) return '0.00';
    return Number(value).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const calcularNuevoSaldo = () => {
    if (abonoForm.monto_abonado) {
        abonoForm.monto_abonado = Math.round(abonoForm.monto_abonado * 100) / 100;
    }
};

const generarReferenciaAutomatica = () => {
    const ahora = new Date();
    const año = ahora.getFullYear();
    const mes = String(ahora.getMonth() + 1).padStart(2, '0');
    const dia = String(ahora.getDate()).padStart(2, '0');
    
    const abonos = props.movimiento.abonos || [];
    const ultimoAbono = abonos[abonos.length - 1];
    let consecutivo = 1;
    
    if (ultimoAbono && ultimoAbono.referencia) {
        const match = ultimoAbono.referencia.match(/-(\d+)$/);
        if (match) {
            consecutivo = parseInt(match[1]) + 1;
        } else {
            consecutivo = numeroAbono.value;
        }
    } else {
        consecutivo = numeroAbono.value;
    }
    
    const fechaStr = `${año}${mes}${dia}`;
    const consecutivoStr = String(consecutivo).padStart(4, '0');
    const referencia = `AB-${fechaStr}-${consecutivoStr}`;
    
    abonoForm.referencia = referencia;
};

const generarReferenciaInicial = () => {
    if (!abonoForm.referencia || abonoForm.referencia.trim() === '') {
        generarReferenciaAutomatica();
    }
};

// ============================================
// SUBMIT ABONO
// ============================================
const submitAbono = () => {
    processing.value = true;

    abonoForm.monto_abonado = Math.round(abonoForm.monto_abonado * 100) / 100;

    if (!abonoForm.monto_abonado || abonoForm.monto_abonado <= 0) {
        alertRef.value?.show({ 
            type: 'error', 
            title: 'Error', 
            message: 'El monto del abono debe ser mayor a 0', 
            buttonText: 'Entendido' 
        });
        processing.value = false;
        return;
    }

    if (abonoForm.monto_abonado > props.movimiento.saldo_pendiente) {
        alertRef.value?.show({ 
            type: 'error', 
            title: 'Error', 
            message: `El monto del abono no puede ser mayor al saldo pendiente ($${formatNumber(props.movimiento.saldo_pendiente)})`, 
            buttonText: 'Entendido' 
        });
        processing.value = false;
        return;
    }

    if (!abonoForm.referencia || abonoForm.referencia.trim() === '') {
        alertRef.value?.show({ 
            type: 'error', 
            title: 'Error', 
            message: 'La referencia del abono es obligatoria', 
            buttonText: 'Entendido' 
        });
        processing.value = false;
        return;
    }

    if (!abonoForm.id_cuenta_fondeadora) {
        alertRef.value?.show({ 
            type: 'error', 
            title: 'Error', 
            message: 'No se encontro una cuenta de fondo asociada a esta poliza', 
            buttonText: 'Entendido' 
        });
        processing.value = false;
        return;
    }

    console.log('Enviando abono:', abonoForm.data());

    abonoForm.post(route('movimientos.abono.store'), {
        preserveState: false,
        preserveScroll: false,
        onSuccess: () => {
            processing.value = false;
            router.visit(route('movimientos.index', { vista: 'diferidas' }), { 
                method: 'get', 
                replace: true 
            });
        },
        onError: (errors) => {
            processing.value = false;
            console.error('Errores:', errors);
            const firstError = Object.values(errors)[0];
            alertRef.value?.show({ 
                type: 'error', 
                title: 'Error', 
                message: firstError || 'Error al registrar el abono.', 
                buttonText: 'Entendido' 
            });
        }
    });
};

// ============================================
// WATCH
// ============================================
watch(
    () => abonoForm.monto_abonado,
    (newVal) => {
        if (newVal) {
            abonoForm.monto_abonado = Math.round(newVal * 100) / 100;
        }
    }
);

// ============================================
// MOUNTED
// ============================================
onMounted(() => {
    if (abonoForm.monto_abonado) {
        abonoForm.monto_abonado = Math.round(abonoForm.monto_abonado * 100) / 100;
    }
    
    generarReferenciaInicial();
    
    console.log('ID Cuenta Fondeadora:', abonoForm.id_cuenta_fondeadora);
    console.log('Movimiento:', props.movimiento);
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

/* ========== INFO LEYENDA ========== */
.info-leyenda-premium {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    padding: 16px 20px;
    background: linear-gradient(135deg, #fef3c7, #fde68a);
    border-radius: 14px;
    border-left: 6px solid #f59e0b;
    margin-bottom: 28px;
    transition: all 0.3s ease;
}

.info-leyenda-premium:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(245, 158, 11, 0.15);
}

.info-leyenda-icon {
    flex-shrink: 0;
    display: flex;
    align-items: center;
}

.info-leyenda-content {
    flex: 1;
}

.info-leyenda-texto {
    font-size: 0.95rem;
    color: #78350f;
    line-height: 1.5;
}

.info-leyenda-texto strong {
    color: #92400e;
    font-weight: 700;
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

/* ========== READONLY ========== */
.readonly-grid .form-group-premium {
    background: #f8fafc;
    border-radius: 10px;
    padding: 8px 14px;
}

.readonly-value {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 0;
    font-size: 0.95rem;
    font-weight: 600;
    color: #0f172a;
}

.readonly-icon {
    display: flex;
    align-items: center;
}

.readonly-value.vencido {
    color: #dc2626;
}

.readonly-value.hoy-vence {
    color: #f59e0b;
}

.readonly-value.pronto-vencer {
    color: #f59e0b;
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
    min-height: 60px;
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

/* ========== BOTON MAX ========== */
.btn-max-premium {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    padding: 2px 12px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 0.7rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    z-index: 2;
}

.btn-max-premium:hover {
    transform: translateY(-50%) scale(1.05);
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
}

/* ========== BOTON GENERAR ========== */
.btn-generate-premium {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    padding: 4px 8px;
    background: none;
    border: none;
    color: #94a3b8;
    cursor: pointer;
    transition: all 0.3s ease;
    z-index: 2;
}

.btn-generate-premium:hover {
    color: #667eea;
    transform: translateY(-50%) scale(1.2);
}

.icon-svg-sm-premium {
    width: 18px;
    height: 18px;
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

/* ========== RESUMEN GRID ========== */
.resumen-grid-premium {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
}

.resumen-item-premium {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 16px;
    background: linear-gradient(135deg, #f8fafc, #f1f5f9);
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    transition: all 0.3s ease;
}

.resumen-item-premium:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.resumen-label-premium {
    font-size: 0.7rem;
    color: #64748b;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.resumen-value-premium {
    font-size: 1.3rem;
    font-weight: 700;
    margin-top: 4px;
}

.resumen-value-premium.total {
    color: #0f172a;
}

.resumen-value-premium.abonado {
    color: #10b981;
}

.resumen-value-premium.pendiente {
    color: #f59e0b;
}

/* ========== NUEVO SALDO BOX ========== */
.nuevo-saldo-box-premium {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 20px;
    background: linear-gradient(135deg, #ecfdf5, #d1fae5);
    border: 2px solid #6ee7b7;
    border-radius: 12px;
    margin-top: 16px;
    animation: fadeSlide 0.3s ease;
    flex-wrap: wrap;
}

.nuevo-saldo-icon {
    display: flex;
    align-items: center;
}

.nuevo-saldo-label {
    font-size: 0.9rem;
    color: #065f46;
    font-weight: 600;
}

.nuevo-saldo-value {
    font-size: 1.2rem;
    font-weight: 700;
    color: #047857;
    margin-left: auto;
}

.nuevo-saldo-liquidado {
    font-size: 0.85rem;
    font-weight: 700;
    color: #10b981;
    background: rgba(16, 185, 129, 0.1);
    padding: 2px 12px;
    border-radius: 20px;
    animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}

/* ========== SUB SECTION ========== */
.sub-section-premium {
    margin-top: 24px;
    padding-top: 24px;
    border-top: 2px dashed #e5e7eb;
}

.sub-section-header-premium {
    margin-bottom: 16px;
}

.sub-section-title-premium {
    font-size: 0.9rem;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 8px;
}

/* ========== DESGLOSE FISCAL ========== */
.desglose-fiscal-box-premium {
    background: linear-gradient(135deg, #fefce8, #fef3c7);
    border: 2px solid #fcd34d;
    border-radius: 14px;
    padding: 18px 24px;
    margin-top: 24px;
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
    grid-template-columns: repeat(3, 1fr);
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

.desglose-fiscal-item-premium:not(:last-child) {
    border-right: 2px solid rgba(245, 158, 11, 0.2);
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

/* ========== REFERENCIA CARD ========== */
.referencia-card-premium {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    background: linear-gradient(135deg, #fef3c7, #fde68a);
    border-radius: 12px;
    border: 1px solid #f59e0b;
}

.referencia-label-premium {
    font-size: 0.7rem;
    color: #92400e;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.referencia-value-premium {
    font-size: 0.95rem;
    font-weight: 700;
    color: #78350f;
}

/* ========== BUTTONS ========== */
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
    flex-wrap: wrap;
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

    .resumen-grid-premium {
        grid-template-columns: 1fr 1fr;
    }

    .desglose-fiscal-grid-premium {
        grid-template-columns: 1fr;
    }

    .desglose-fiscal-item-premium:not(:last-child) {
        border-right: none;
        border-bottom: 2px solid rgba(245, 158, 11, 0.2);
        padding-bottom: 12px;
    }

    .info-leyenda-premium {
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: 16px;
    }

    .referencia-card-premium {
        width: 100%;
        justify-content: center;
    }
    
    .btn-max-premium {
        right: 8px;
        font-size: 0.6rem;
        padding: 2px 8px;
    }

    .nuevo-saldo-box-premium {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    .nuevo-saldo-value {
        margin-left: 0;
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

    .btn-generate-premium {
        right: 8px;
    }
    
    .nuevo-saldo-liquidado {
        font-size: 0.75rem;
        width: 100%;
        text-align: center;
    }

    .resumen-grid-premium {
        grid-template-columns: 1fr;
    }
}
</style>