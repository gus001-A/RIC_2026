<template>
    <AppLayout :title="tituloPagina">
        <!-- FLASH MESSAGES -->
        <div v-if="$page.props.flash" class="flash-container">
            <div v-if="$page.props.flash.success" class="flash-message flash-success">
                <span class="flash-icon">
                    <svg class="icon-svg-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </span>
                <span class="flash-text">{{ $page.props.flash.success }}</span>
                <button @click="$page.props.flash.success = null" class="flash-close">✕</button>
            </div>
            <div v-if="$page.props.flash.error" class="flash-message flash-error">
                <span class="flash-icon">
                    <svg class="icon-svg-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </span>
                <span class="flash-text">{{ $page.props.flash.error }}</span>
                <button @click="$page.props.flash.error = null" class="flash-close">✕</button>
            </div>
            <div v-if="$page.props.flash.info" class="flash-message flash-info">
                <span class="flash-icon">
                    <svg class="icon-svg-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
                <span class="flash-text">{{ $page.props.flash.info }}</span>
                <button @click="$page.props.flash.info = null" class="flash-close">✕</button>
            </div>
        </div>

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
                        <span v-if="hasErrors">! {{ errorCount }} errores</span>
                        <span v-else-if="isComplete">✓ Completado</span>
                        <span v-else>{{ Math.round(progressPercentage) }}%</span>
                    </div>
                </div>
            </div>
        </template>

        <div class="page-content">
            <div class="container-custom">
                <div class="form-card">
                    <!-- LEYENDA INFORMATIVA DEL ABONO -->
                    <div class="info-leyenda-premium">
                        <div class="info-leyenda-icon">
                            <svg width="24" height="24" fill="none" stroke="#92400e" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="info-leyenda-content">
                            <span class="info-leyenda-texto">
                                Abono <strong>#{{ numeroAbono }}</strong> para la poliza <strong>{{ movimiento.referencia || 'S/N' }}</strong>. 
                                Ingrese el monto del abono a registrar.
                            </span>
                        </div>
                    </div>

                    <!-- PANEL DE IVAS DISPONIBLES -->
                    <div v-if="tieneIva" class="info-iva-disponible">
                        <div class="info-iva-disponible-icon">
                            <svg width="20" height="20" fill="none" stroke="#1e40af" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                            </svg>
                        </div>
                        <div class="info-iva-disponible-content">
                            <span class="info-iva-disponible-title">Montos disponibles para abonar:</span>
                            <div class="info-iva-disponible-items">
                                <span v-for="iva in ivasDisponibles" :key="iva.id" class="info-iva-disponible-item">
                                    <span class="info-iva-disponible-badge" :class="iva.porcentaje === 0 ? 'badge-cero' : 'badge-dieciseis'">
                                        {{ iva.porcentaje }}%
                                    </span>
                                    <span class="info-iva-disponible-monto">${{ formatNumber(iva.monto_disponible) }}</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <form @submit.prevent="submitAbono" id="abonoForm" novalidate>
                        <!-- ============================================ -->
                        <!-- FILA 1: PERSONA, CUENTA, CUENTA FONDEADORA -->
                        <!-- ============================================ -->
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Persona</label>
                                <div class="readonly-value">
                                    <span class="readonly-icon">
                                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </span>
                                    <span>{{ movimiento.persona || 'N/A' }}</span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Cuenta</label>
                                <div class="readonly-value">
                                    <span class="readonly-icon">
                                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                        </svg>
                                    </span>
                                    <span>{{ movimiento.cuenta || 'N/A' }}</span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Cuenta Fondeadora</label>
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
                        </div>

                        <!-- ============================================ -->
                        <!-- FILA 2: MARCADOR, FECHA VENCIMIENTO, FECHA PAGO, NUMERO ABONO -->
                        <!-- ============================================ -->
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Marcador</label>
                                <div class="readonly-value">
                                    <span class="readonly-icon">
                                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                        </svg>
                                    </span>
                                    <span>{{ movimiento.marcador || 'Sin marcador' }}</span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Fecha Vencimiento</label>
                                <div class="readonly-value" :class="diasRestantesClass">
                                    <span class="readonly-icon">
                                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </span>
                                    <span v-if="movimiento.fecha_vencimiento">
                                        {{ movimiento.fecha_vencimiento }} 
                                        <span class="dias-restantes">
                                            ({{ diasRestantes > 0 ? diasRestantes + ' dias restantes' : diasRestantes === 0 ? 'Hoy vence' : 'Vencido' }})
                                        </span>
                                    </span>
                                    <span v-else>Sin fecha de vencimiento</span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Fecha de Pago</label>
                                <div class="readonly-value">
                                    <span class="readonly-icon">
                                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </span>
                                    <span>{{ fechaActualFormateada }}</span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Numero de Abono</label>
                                <div class="readonly-value">
                                    <span class="readonly-icon">#</span>
                                    <span>{{ numeroAbono }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- ============================================ -->
                        <!-- MONTO DEL ABONO - SOLO SI NO TIENE IVA -->
                        <!-- ============================================ -->
                        <div class="form-row" v-if="!tieneIva">
                            <div class="form-group full-width">
                                <label class="form-label">
                                    Monto del Abono <span class="required-star">*</span>
                                </label>
                                <div class="input-wrapper input-with-prefix">
                                    <span class="input-prefix">$</span>
                                    <input type="number" step="0.01" v-model.number="abonoForm.monto_abonado"
                                           @input="calcularNuevoSaldo; clearError('monto_abonado')"
                                           class="form-input"
                                           :class="{ error: abonoForm.errors.monto_abonado }"
                                           placeholder="0.00"
                                           min="0.01"
                                           :max="movimiento.saldo_pendiente">
                                    <button type="button" 
                                            @click="abonoForm.monto_abonado = Math.round(movimiento.saldo_pendiente * 100) / 100; calcularNuevoSaldo()"
                                            class="btn-max"
                                            title="Completar saldo pendiente">
                                        Max
                                    </button>
                                </div>
                                <div v-if="abonoForm.errors.monto_abonado" class="error-text">{{ abonoForm.errors.monto_abonado }}</div>
                                <div class="hint-text">Monto maximo: ${{ formatNumber(movimiento.saldo_pendiente) }}</div>
                            </div>
                        </div>

                        <!-- Campo oculto para cuando tiene IVA -->
                        <input v-if="tieneIva" type="hidden" v-model="abonoForm.monto_abonado" />

                        <!-- ============================================ -->
                        <!-- DESGLOSE DE IVA - SOLO SI LA POLIZA TIENE IVA -->
                        <!-- ============================================ -->
                        <div v-if="tieneIva" class="sub-section">
                            <div class="sub-section-header">
                                <span class="sub-section-title">Asignacion de IVA</span>
                                <span class="sub-section-subtitle">Ingresa los montos que deseas abonar a cada tipo de IVA</span>
                            </div>

                            <div class="iva-detail">
                                <div class="iva-detail-grid">
                                    <div v-for="ivaId in ivasSeleccionados" :key="ivaId" class="iva-detail-item">
                                        <span class="iva-detail-badge" :class="getIvaPorcentaje(ivaId) === 0 ? 'badge-cero' : 'badge-dieciseis'">
                                            {{ getIvaPorcentaje(ivaId) }}%
                                        </span>
                                        <div class="input-wrapper input-with-prefix iva-input-wrap">
                                            <span class="input-prefix">$</span>
                                            <input 
                                                type="number" 
                                                step="0.01" 
                                                v-model.number="abonoForm.ivas[ivaId].monto"
                                                @input="calcularTotalAbono; clearError('ivas')"
                                                class="form-input iva-input"
                                                placeholder="0.00"
                                                min="0"
                                                :max="obtenerMontoDisponibleIva(ivaId)"
                                            >
                                        </div>
                                        <span class="iva-detail-disponible">Disponible: ${{ formatNumber(obtenerMontoDisponibleIva(ivaId)) }}</span>
                                        <span class="iva-detail-result">IVA: ${{ formatNumber(calcularIvaMontoAbono(ivaId)) }}</span>
                                        <button type="button" @click="quitarIvaAbono(ivaId)" class="iva-remove-btn">✕</button>
                                    </div>
                                    <div class="iva-total">
                                        <span>Total abono: <strong>${{ formatNumber(totalConIvaAbonoCalculado) }}</strong></span>
                                    </div>
                                </div>
                            </div>

                            <!-- VALIDACION DE IVA VS MONTO -->
                            <div v-if="abonoForm.monto_abonado > 0 && ivasSeleccionados.length > 0 && !validarMontoConIva" 
                                 class="validacion-iva iva-invalido">
                                <span class="validacion-icon">!</span>
                                <span class="validacion-texto">
                                    La suma del desglose (${{ formatNumber(totalConIvaAbonoCalculado) }}) excede el monto (${{ formatNumber(abonoForm.monto_abonado) }})
                                </span>
                            </div>
                        </div>

                        <!-- ============================================ -->
                        <!-- NUEVO SALDO PENDIENTE -->
                        <!-- ============================================ -->
                        <div class="nuevo-saldo-box" v-if="montoAbonoMostrar > 0">
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
                        <!-- RESUMEN FINANCIERO -->
                        <!-- ============================================ -->
                        <div class="resumen-financiero">
                            <div class="resumen-header">
                                <span class="resumen-title">Resumen Financiero</span>
                            </div>
                            <div class="resumen-grid">
                                <div class="resumen-item">
                                    <span class="resumen-label">Total Factura</span>
                                    <span class="resumen-value total">${{ formatNumber(movimiento.monto_abs) }}</span>
                                </div>
                                <div class="resumen-item">
                                    <span class="resumen-label">Total Abonado</span>
                                    <span class="resumen-value abonado">${{ formatNumber(movimiento.total_abonado) }}</span>
                                </div>
                                <div class="resumen-item">
                                    <span class="resumen-label">Saldo Pendiente</span>
                                    <span class="resumen-value pendiente">${{ formatNumber(movimiento.saldo_pendiente) }}</span>
                                </div>
                                <div class="resumen-item">
                                    <span class="resumen-label">Nuevo Saldo</span>
                                    <span class="resumen-value nuevo">${{ formatNumber(nuevoSaldoPendiente) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- ============================================ -->
                        <!-- OBSERVACIONES -->
                        <!-- ============================================ -->
                        <div class="form-row observaciones-row">
                            <div class="form-group full-width">
                                <label class="form-label">Observaciones</label>
                                <div class="input-wrapper">
                                    <textarea v-model="abonoForm.nota" rows="2"
                                              class="form-textarea"
                                              placeholder="Agrega notas o comentarios..."></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- ============================================ -->
                        <!-- BOTONES -->
                        <!-- ============================================ -->
                        <div class="info-box">
                            <span>Los campos con <strong class="text-danger">*</strong> son obligatorios</span>
                        </div>

                        <div class="form-actions">
                            <div class="actions-left">
                                <div class="total-card">
                                    <span class="total-label">Total Abono</span>
                                    <span class="total-value">${{ formatNumber(totalAbonoFinal) }}</span>
                                </div>
                            </div>
                            <div class="actions-right">
                                <Link :href="route('movimientos.index', { vista: 'diferidas' })" class="btn btn-cancel">
                                    <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    Cancelar
                                </Link>
                                <button type="submit" 
                                        :disabled="processing || !isFormValid"
                                        class="btn btn-submit">
                                    <span v-if="processing" class="spinner-border"></span>
                                    <svg v-else class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

        <AlertModal ref="alertRef" />
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, useForm, router } from '@inertiajs/vue3';
import AlertModal from '@/Components/AlertModal.vue';

// ============================================
// PROPS
// ============================================
const props = defineProps({
    movimiento: {
        type: Object,
        required: true
    },
    tipos_iva: {
        type: Array,
        default: () => []
    }
});

// ============================================
// REFS
// ============================================
const alertRef = ref(null);
const processing = ref(false);
const tiposIva = ref(props.tipos_iva || []);

// IVAs seleccionados
const ivasSeleccionados = ref([]);

// ============================================
// FORMULARIO
// ============================================
const abonoForm = useForm({
    id_poliza: props.movimiento.id_poliza,
    monto_abonado: 0,
    fecha_abono: new Date().toISOString().split('T')[0],
    referencia: '',
    nota: '',
    id_cuenta_fondeadora: props.movimiento.cuenta_fondeadora_id || '',
    ivas: {}
});

// ============================================
// COMPUTED
// ============================================
const tieneIva = computed(() => {
    const ivasHeredados = props.movimiento?.ivas_heredados || [];
    return ivasHeredados.length > 0;
});

const montoAbonoMostrar = computed(() => {
    if (tieneIva.value) {
        return totalConIvaAbonoCalculado.value;
    }
    return abonoForm.monto_abonado || 0;
});

const totalAbonoFinal = computed(() => {
    if (tieneIva.value) {
        return totalConIvaAbonoCalculado.value;
    }
    return abonoForm.monto_abonado || 0;
});

const numeroAbono = computed(() => {
    return (props.movimiento.numero_abonos || 0) + 1;
});

const fechaActualFormateada = computed(() => {
    const fecha = new Date();
    return fecha.toLocaleDateString('es-MX', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
});

const nuevoSaldoPendiente = computed(() => {
    const saldoActual = props.movimiento.saldo_pendiente || 0;
    const montoAbono = totalAbonoFinal.value || 0;
    const nuevoSaldo = saldoActual - montoAbono;
    return nuevoSaldo < 0 ? 0 : Math.round(nuevoSaldo * 100) / 100;
});

const totalConIvaAbonoCalculado = computed(() => {
    let total = 0;
    ivasSeleccionados.value.forEach(ivaId => {
        total += abonoForm.ivas[ivaId]?.monto || 0;
    });
    return Math.round(total * 100) / 100;
});

const totalBaseAbonoCalculado = computed(() => {
    let totalBase = 0;
    ivasSeleccionados.value.forEach(ivaId => {
        const montoConIva = abonoForm.ivas[ivaId]?.monto || 0;
        if (montoConIva <= 0) return;
        
        const iva = tiposIva.value.find(i => i.id === ivaId);
        if (!iva) return;
        
        if (iva.porcentaje > 0) {
            const base = montoConIva / (1 + (iva.porcentaje / 100));
            totalBase += base;
        } else {
            totalBase += montoConIva;
        }
    });
    return Math.round(totalBase * 100) / 100;
});

const totalIvaAbonoCalculado = computed(() => {
    let totalIva = 0;
    ivasSeleccionados.value.forEach(ivaId => {
        const montoConIva = abonoForm.ivas[ivaId]?.monto || 0;
        if (montoConIva <= 0) return;
        
        const iva = tiposIva.value.find(i => i.id === ivaId);
        if (!iva || iva.porcentaje <= 0) return;
        
        const base = montoConIva / (1 + (iva.porcentaje / 100));
        totalIva += (montoConIva - base);
    });
    return Math.round(totalIva * 100) / 100;
});

const calcularTotalAbono = () => {
    if (tieneIva.value) {
        abonoForm.monto_abonado = totalConIvaAbonoCalculado.value;
    }
};

// VALIDACION DE IVA VS MONTO
const validarMontoConIva = computed(() => {
    if (!abonoForm.monto_abonado || abonoForm.monto_abonado <= 0) return true;
    if (ivasSeleccionados.value.length === 0) return true;
    
    const totalIva = totalConIvaAbonoCalculado.value;
    const montoAbono = abonoForm.monto_abonado;
    
    return totalIva <= montoAbono;
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

// VALIDACION DEL FORMULARIO
const isFormValid = computed(() => {
    const montoAbono = totalAbonoFinal.value;
    if (!montoAbono || montoAbono <= 0) return false;
    if (montoAbono > props.movimiento.saldo_pendiente) return false;
    
    if (tieneIva.value) {
        if (ivasSeleccionados.value.length === 0) return false;
        const tieneMontoIva = ivasSeleccionados.value.some(ivaId => {
            const monto = abonoForm.ivas[ivaId]?.monto || 0;
            return monto > 0;
        });
        if (!tieneMontoIva) return false;
        if (!validarMontoConIva.value) return false;
    }
    
    if (hasErrors.value) return false;
    return true;
});

const progressPercentage = computed(() => {
    let filled = 0;
    const total = tieneIva.value ? 2 : 1;
    if (totalAbonoFinal.value > 0) filled++;
    if (tieneIva.value) {
        const tieneMonto = ivasSeleccionados.value.some(ivaId => {
            return (abonoForm.ivas[ivaId]?.monto || 0) > 0;
        });
        if (tieneMonto) filled++;
    }
    return total > 0 ? (filled / total) * 100 : 0;
});

const isComplete = computed(() => progressPercentage.value === 100 && !hasErrors.value);

const statusClass = computed(() => {
    if (hasErrors.value) return 'status-error';
    if (isComplete.value) return 'status-success';
    return 'status-progress';
});

const tituloPagina = computed(() => {
    return `Abono - Poliza ${props.movimiento.referencia || 'S/N'}`;
});

const subtituloPagina = computed(() => {
    return `Registro del abono #${numeroAbono.value} para la poliza ${props.movimiento.referencia || 'S/N'}`;
});

// ============================================
// IVAS DISPONIBLES PARA MOSTRAR EN EL PANEL
// ============================================
const ivasDisponibles = computed(() => {
    const ivasHeredados = props.movimiento?.ivas_heredados || [];
    return ivasHeredados.map(iva => {
        const montoDisponible = iva.monto || 0;
        return {
            id: iva.id,
            porcentaje: iva.porcentaje,
            monto_disponible: montoDisponible
        };
    });
});

const obtenerMontoDisponibleIva = (ivaId) => {
    const iva = ivasDisponibles.value.find(i => i.id === ivaId);
    return iva?.monto_disponible || 0;
};

// ============================================
// METODOS PARA IVA
// ============================================
const getIvaPorcentaje = (ivaId) => {
    const iva = tiposIva.value.find(i => i.id === ivaId);
    return iva ? iva.porcentaje : 0;
};

const calcularIvaMontoAbono = (ivaId) => {
    const montoConIva = abonoForm.ivas[ivaId]?.monto || 0;
    const iva = tiposIva.value.find(i => i.id === ivaId);
    if (!iva || montoConIva === 0) return 0;
    const base = montoConIva / (1 + (iva.porcentaje / 100));
    return Math.round((montoConIva - base) * 100) / 100;
};

const toggleIvaAbono = (ivaId) => {
    const index = ivasSeleccionados.value.indexOf(ivaId);
    if (index > -1) {
        ivasSeleccionados.value.splice(index, 1);
        delete abonoForm.ivas[ivaId];
    } else {
        if (ivasSeleccionados.value.length >= 2) {
            alertRef.value?.show({
                type: 'warning',
                title: 'Limite alcanzado',
                message: 'Solo puedes seleccionar hasta 2 tipos de IVA',
                buttonText: 'Entendido'
            });
            return;
        }
        ivasSeleccionados.value.push(ivaId);
        if (!abonoForm.ivas[ivaId]) {
            abonoForm.ivas[ivaId] = { monto: 0 };
        }
    }
    calcularTotalAbono();
};

const quitarIvaAbono = (ivaId) => {
    const index = ivasSeleccionados.value.indexOf(ivaId);
    if (index > -1) {
        ivasSeleccionados.value.splice(index, 1);
        delete abonoForm.ivas[ivaId];
    }
    calcularTotalAbono();
};

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
    
    const folio = props.movimiento.referencia || '0000';
    const folioLimpio = folio.replace(/[^0-9]/g, '').padStart(4, '0');
    
    const fechaStr = `${año}${mes}${dia}`;
    const referencia = `ABO-${folioLimpio}-${fechaStr}`;
    abonoForm.referencia = referencia;
};

// ============================================
// SUBMIT ABONO
// ============================================
const submitAbono = () => {
    processing.value = true;

    if (tieneIva.value) {
        abonoForm.monto_abonado = totalConIvaAbonoCalculado.value;
    }
    
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

    if (tieneIva.value) {
        if (ivasSeleccionados.value.length === 0) {
            alertRef.value?.show({ 
                type: 'error', 
                title: 'Error', 
                message: 'Debes seleccionar al menos un tipo de IVA', 
                buttonText: 'Entendido' 
            });
            processing.value = false;
            return;
        }

        const tieneMontoIva = ivasSeleccionados.value.some(ivaId => {
            return (abonoForm.ivas[ivaId]?.monto || 0) > 0;
        });
        if (!tieneMontoIva) {
            alertRef.value?.show({ 
                type: 'error', 
                title: 'Error', 
                message: 'Debes asignar un monto a al menos un tipo de IVA', 
                buttonText: 'Entendido' 
            });
            processing.value = false;
            return;
        }

        if (!validarMontoConIva.value) {
            alertRef.value?.show({ 
                type: 'warning', 
                title: 'Atencion', 
                message: `El total del desglose ($${formatNumber(totalConIvaAbonoCalculado.value)}) excede el monto del abono ($${formatNumber(abonoForm.monto_abonado)}). Verifica los montos de IVA.`, 
                buttonText: 'Entendido' 
            });
            processing.value = false;
            return;
        }
    }

    // Generar referencia automatica si no tiene
    if (!abonoForm.referencia || abonoForm.referencia.trim() === '') {
        generarReferenciaAutomatica();
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

    // 🔥 CONSTRUIR EL ARRAY DE IVAS CON EL FORMATO CORRECTO
    const ivasArray = ivasSeleccionados.value.map(ivaId => ({
        id: ivaId,
        monto: abonoForm.ivas[ivaId]?.monto || 0
    }));

    const formData = new FormData();
    const formDataObj = abonoForm.data();
    Object.keys(formDataObj).forEach(key => {
        if (key === 'ivas') return;
        let value = formDataObj[key];
        if (typeof value === 'boolean') value = value ? 'true' : 'false';
        if (value === null || value === undefined) value = '';
        if (value !== '' && value !== null && value !== undefined) {
            formData.append(key, value);
        }
    });

    // 🔥 ENVIAR IVAS EN EL FORMATO ESPERADO POR EL BACKEND
    ivasArray.forEach((iva, index) => {
        formData.append(`ivas[${index}][id]`, iva.id);
        formData.append(`ivas[${index}][monto]`, iva.monto);
    });

    // 🔥 ENVIAR LOS TOTALES CALCULADOS
    const totalBase = totalBaseAbonoCalculado.value;
    const totalIva = totalIvaAbonoCalculado.value;
    const totalConIva = totalConIvaAbonoCalculado.value;

    console.log('📊 Enviando al backend:', {
        totalBase,
        totalIva,
        totalConIva,
        ivas: ivasArray
    });

    formData.append('total_base', totalBase);
    formData.append('total_iva', totalIva);
    formData.append('total_con_iva', totalConIva);

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
    console.log('📊 Tipos de IVA disponibles:', tiposIva.value);
    console.log('📊 Movimiento recibido:', props.movimiento);
    
    if (!abonoForm.fecha_abono) {
        abonoForm.fecha_abono = new Date().toLocaleDateString('en-CA', { timeZone: 'America/Mexico_City' });
    }

    if (abonoForm.monto_abonado) {
        abonoForm.monto_abonado = Math.round(abonoForm.monto_abonado * 100) / 100;
    }
    
    generarReferenciaAutomatica();

    const ivasHeredados = props.movimiento?.ivas_heredados || [];
    const saldoPendiente = props.movimiento.saldo_pendiente || 0;
    
    console.log('📊 IVAs heredados:', ivasHeredados);
    
    if (ivasHeredados.length > 0) {
        ivasSeleccionados.value = [];
        abonoForm.ivas = {};
        
        ivasHeredados.forEach((iva) => {
            ivasSeleccionados.value.push(iva.id);
            if (!abonoForm.ivas[iva.id]) {
                abonoForm.ivas[iva.id] = { 
                    monto: 0
                };
            }
        });
        
        calcularTotalAbono();
        console.log('📊 IVAs seleccionados:', ivasSeleccionados.value);
        console.log('📊 Montos iniciales:', abonoForm.ivas);
        
    } else {
        if (saldoPendiente > 0) {
            abonoForm.monto_abonado = Math.round(saldoPendiente * 100) / 100;
        }
    }
});
</script>

<style scoped>
/* ========== ESTILOS COMPLETOS ========== */

/* --- HEADER --- */
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
    gap: 12px;
}

.btn-back {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 38px;
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.9);
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: #6b7280;
    transition: all 0.3s ease;
}

.btn-back:hover {
    background: white;
    color: #1f2937;
    transform: translateX(-2px) scale(1.05);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.header-content { display: flex; flex-direction: column; }
.header-title { font-size: 1.2rem; font-weight: 700; color: #0f172a; margin: 0; line-height: 1.3; }
.header-subtitle { font-size: 0.8rem; color: #64748b; margin: 0; }

.header-right { display: flex; align-items: center; gap: 10px; }

.status-badge {
    padding: 4px 14px;
    border-radius: 18px;
    font-size: 0.7rem;
    font-weight: 700;
    transition: all 0.3s ease;
}
.status-success { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
.status-error { background: #fecaca; color: #991b1b; border: 1px solid #f87171; animation: shake 0.4s ease; }
.status-progress { background: #e0e7ff; color: #4338ca; border: 1px solid #818cf8; }

/* --- FLASH --- */
.flash-container { margin: 0 0 8px 0; padding: 0 1.5rem; }
.flash-message {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 14px;
    border-radius: 8px;
    animation: slideDown 0.3s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    margin-bottom: 4px;
    font-size: 0.85rem;
}
.flash-success { background: #ecfdf5; border-left: 3px solid #10b981; }
.flash-error { background: #fef2f2; border-left: 3px solid #dc2626; }
.flash-info { background: #eff6ff; border-left: 3px solid #3b82f6; }
.flash-text { font-weight: 500; color: #1f2937; flex: 1; }
.flash-close {
    background: none;
    border: none;
    color: #6b7280;
    font-size: 1rem;
    cursor: pointer;
    padding: 0 4px;
    border-radius: 4px;
    transition: all 0.3s ease;
}
.flash-close:hover { background: rgba(0, 0, 0, 0.05); color: #dc2626; }

/* --- PAGE --- */
.page-content { padding: 0.5rem 0; }
.container-custom { max-width: 80rem; margin: 0 auto; padding: 0 1.5rem; }

/* --- FORM CARD --- */
.form-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 2px 16px rgba(0, 0, 0, 0.05);
    border: 1px solid #f1f5f9;
    padding: 1.25rem 1.5rem;
}

/* --- INFO LEYENDA --- */
.info-leyenda-premium {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    padding: 16px 20px;
    background: linear-gradient(135deg, #fef3c7, #fde68a);
    border-radius: 14px;
    border-left: 6px solid #f59e0b;
    margin-bottom: 16px;
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

/* --- INFO IVA DISPONIBLE --- */
.info-iva-disponible {
    display: flex;
    gap: 12px;
    padding: 12px 16px;
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
    border-radius: 10px;
    border-left: 4px solid #22c55e;
    margin-bottom: 16px;
    align-items: center;
}

.info-iva-disponible-icon {
    flex-shrink: 0;
    color: #16a34a;
}

.info-iva-disponible-content {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}

.info-iva-disponible-title {
    font-size: 0.85rem;
    font-weight: 600;
    color: #166534;
}

.info-iva-disponible-items {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.info-iva-disponible-item {
    display: flex;
    align-items: center;
    gap: 6px;
    background: white;
    padding: 2px 10px 2px 6px;
    border-radius: 6px;
    border: 1px solid #bbf7d0;
}

.info-iva-disponible-badge {
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 0.6rem;
    font-weight: 700;
    color: white;
}

.info-iva-disponible-badge.badge-cero { background: #64748b; }
.info-iva-disponible-badge.badge-dieciseis { background: #3b82f6; }

.info-iva-disponible-monto {
    font-size: 0.85rem;
    font-weight: 700;
    color: #065f46;
}

/* --- FORM ROWS --- */
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr 1fr;
    gap: 14px;
    margin-bottom: 12px;
}

.form-row.observaciones-row {
    grid-template-columns: 1fr;
    margin-bottom: 8px;
}

/* --- FORM GROUP --- */
.form-group {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-label {
    font-size: 0.8rem;
    font-weight: 600;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 4px;
}

.required-star { color: #ef4444; font-weight: 700; }

/* --- READONLY VALUE --- */
.readonly-value {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 8px;
    font-size: 0.9rem;
    font-weight: 600;
    color: #0f172a;
    background: #f8fafc;
    border-radius: 6px;
    border: 1px solid #e5e7eb;
    min-height: 36px;
}

.readonly-icon {
    display: flex;
    align-items: center;
    color: #94a3b8;
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

.dias-restantes {
    font-weight: 400;
    font-size: 0.8rem;
    color: #64748b;
}

/* --- INPUTS --- */
.input-wrapper {
    position: relative;
}

.form-input {
    width: 100%;
    padding: 8px 12px 8px 14px;
    font-size: 0.85rem;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    background: white;
    color: #1f2937;
    transition: all 0.3s ease;
    outline: none;
    height: 40px;
    appearance: none;
    -webkit-appearance: none;
}

.form-input:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.form-input.error {
    border-color: #ef4444;
    animation: shake 0.3s ease;
}

.form-input.error:focus {
    box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
}

.form-textarea {
    width: 100%;
    padding: 8px 12px;
    font-size: 0.85rem;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    background: white;
    color: #1f2937;
    transition: all 0.3s ease;
    outline: none;
    resize: vertical;
    min-height: 50px;
    max-height: 100px;
    font-family: inherit;
}

.form-textarea:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.form-textarea.error { border-color: #ef4444; }

.input-icon {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    width: 18px;
    height: 18px;
    color: #94a3b8;
    pointer-events: none;
}

.input-with-prefix .form-input {
    padding-left: 32px;
}

.input-prefix {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    font-weight: 700;
    color: #64748b;
    font-size: 0.9rem;
    z-index: 1;
}

/* --- BTN MAX --- */
.btn-max {
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    padding: 2px 12px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border: none;
    border-radius: 4px;
    font-size: 0.7rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    z-index: 2;
}

.btn-max:hover {
    transform: translateY(-50%) scale(1.05);
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
}

.icon-svg-sm {
    width: 18px;
    height: 18px;
}

/* --- NUEVO SALDO --- */
.nuevo-saldo-box {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    background: linear-gradient(135deg, #ecfdf5, #d1fae5);
    border: 2px solid #6ee7b7;
    border-radius: 8px;
    margin-bottom: 12px;
    flex-wrap: wrap;
}

.nuevo-saldo-icon {
    display: flex;
    align-items: center;
}

.nuevo-saldo-label {
    font-size: 0.85rem;
    color: #065f46;
    font-weight: 600;
}

.nuevo-saldo-value {
    font-size: 1.1rem;
    font-weight: 700;
    color: #047857;
    margin-left: auto;
}

.nuevo-saldo-liquidado {
    font-size: 0.8rem;
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

/* --- SUB-SECCION IVA --- */
.sub-section {
    margin-top: 16px;
    padding-top: 16px;
    border-top: 2px dashed #e5e7eb;
}

.sub-section-header {
    margin-bottom: 12px;
}

.sub-section-title {
    font-size: 0.9rem;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 8px;
}

.sub-section-subtitle {
    font-size: 0.75rem;
    color: #94a3b8;
    display: block;
    margin-top: 2px;
}

/* --- IVA DETAIL --- */
.iva-detail {
    background: #f8fafc;
    border-radius: 8px;
    padding: 8px 12px;
    margin-bottom: 8px;
    border: 1px solid #e5e7eb;
}

.iva-detail-grid {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    align-items: center;
}

.iva-detail-item {
    display: flex;
    align-items: center;
    gap: 6px;
    background: white;
    padding: 4px 10px;
    border-radius: 6px;
    border: 1px solid #e5e7eb;
}

.iva-detail-badge {
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 0.6rem;
    font-weight: 700;
    color: white;
}

.iva-detail-badge.badge-cero { background: #64748b; }
.iva-detail-badge.badge-dieciseis { background: #3b82f6; }

.iva-input-wrap {
    width: 100px;
}

.iva-input {
    height: 32px !important;
    padding: 2px 8px 2px 24px !important;
    font-size: 0.8rem !important;
}

.iva-detail-disponible {
    font-size: 0.65rem;
    color: #6b7280;
    white-space: nowrap;
}

.iva-detail-result {
    font-size: 0.7rem;
    font-weight: 600;
    color: #059669;
    white-space: nowrap;
}

.iva-remove-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 22px;
    height: 22px;
    border: none;
    background: #fef2f2;
    border-radius: 50%;
    color: #dc2626;
    cursor: pointer;
    font-size: 0.6rem;
    font-weight: 700;
    transition: all 0.3s ease;
}

.iva-remove-btn:hover {
    background: #fecaca;
    transform: rotate(90deg) scale(1.1);
}

.iva-total {
    display: flex;
    gap: 12px;
    font-size: 0.8rem;
    color: #1f2937;
    padding-left: 6px;
}

.iva-total strong {
    color: #059669;
    font-size: 0.9rem;
}

/* --- VALIDACION IVA --- */
.validacion-iva {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 14px;
    border-radius: 8px;
    font-size: 0.8rem;
    margin-bottom: 8px;
}

.validacion-iva.iva-valido {
    background: #ecfdf5;
    border: 1px solid #6ee7b7;
    color: #065f46;
}

.validacion-iva.iva-invalido {
    background: #fef2f2;
    border: 1px solid #fca5a5;
    color: #991b1b;
    animation: shake 0.3s ease;
}

.validacion-icon { font-size: 1rem; flex-shrink: 0; }
.validacion-texto { font-weight: 500; flex: 1; }

/* --- RESUMEN FINANCIERO --- */
.resumen-financiero {
    background: linear-gradient(135deg, #f8fafc, #f1f5f9);
    border-radius: 8px;
    padding: 16px 20px;
    border: 1px solid #e5e7eb;
    margin: 12px 0;
}

.resumen-header {
    margin-bottom: 12px;
}

.resumen-title {
    font-size: 0.85rem;
    font-weight: 700;
    color: #0f172a;
}

.resumen-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
}

.resumen-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 10px;
    background: white;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
}

.resumen-label {
    font-size: 0.65rem;
    color: #64748b;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.resumen-value {
    font-size: 1.1rem;
    font-weight: 700;
    margin-top: 2px;
}

.resumen-value.total { color: #0f172a; }
.resumen-value.abonado { color: #10b981; }
.resumen-value.pendiente { color: #f59e0b; }
.resumen-value.nuevo { color: #2563eb; }

/* --- INFO BOX --- */
.info-box {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 14px;
    background: #f8fafc;
    border-radius: 8px;
    border-left: 4px solid #667eea;
    font-size: 0.8rem;
    color: #4b5563;
    margin-top: 8px;
    flex-wrap: wrap;
}

.text-danger { color: #ef4444; }

/* --- FORM ACTIONS --- */
.form-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    margin-top: 12px;
    padding-top: 12px;
    border-top: 2px solid #f1f5f9;
}

.actions-left,
.actions-right {
    display: flex;
    gap: 10px;
    align-items: center;
}

.total-card {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 6px 18px;
    background: linear-gradient(135deg, #f8fafc, #eef2ff);
    border-radius: 8px;
    border: 1px solid #e5e7eb;
}

.total-label {
    font-size: 0.65rem;
    color: #64748b;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.total-value {
    font-size: 1rem;
    font-weight: 700;
    color: #0f172a;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 24px;
    font-weight: 700;
    border: none;
    border-radius: 8px;
    font-size: 0.85rem;
    transition: all 0.3s ease;
    cursor: pointer;
    text-decoration: none;
    height: 40px;
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none !important;
}

.btn-icon {
    width: 18px;
    height: 18px;
}

.btn-cancel {
    background: #f1f5f9;
    color: #64748b;
    border: 1.5px solid #e5e7eb;
}

.btn-cancel:hover:not(:disabled) {
    background: #fecaca;
    color: #dc2626;
    border-color: #fca5a5;
}

.btn-submit {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    box-shadow: 0 4px 16px rgba(102, 126, 234, 0.25);
}

.btn-submit:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(102, 126, 234, 0.35);
}

.spinner-border {
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

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-4px); }
    75% { transform: translateX(4px); }
}

@keyframes slideDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.fade-slide {
    animation: fadeSlide 0.3s ease;
}

@keyframes fadeSlide {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
}

/* --- RESPONSIVE --- */
@media (max-width: 992px) {
    .form-row {
        grid-template-columns: 1fr 1fr;
    }
    .resumen-grid {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 640px) {
    .form-row {
        grid-template-columns: 1fr;
        gap: 10px;
        margin-bottom: 10px;
    }
    .form-card { padding: 1rem; }
    .container-custom { padding: 0 0.75rem; }
    .header-wrapper {
        flex-direction: column;
        align-items: flex-start;
        gap: 6px;
    }
    .header-right { width: 100%; justify-content: flex-start; }
    .form-actions {
        flex-direction: column-reverse;
        align-items: stretch;
        gap: 8px;
    }
    .actions-left,
    .actions-right {
        width: 100%;
        justify-content: center;
        flex-wrap: wrap;
    }
    .btn { flex: 1; justify-content: center; padding: 6px 16px; height: 36px; font-size: 0.8rem; }
    .iva-detail-item { flex-wrap: wrap; justify-content: center; }
    .iva-input-wrap { width: 100%; }
    .resumen-grid { grid-template-columns: 1fr; }
    .info-leyenda-premium {
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: 14px;
    }
    .info-iva-disponible {
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: 14px;
    }
    .info-iva-disponible-content {
        justify-content: center;
    }
    .total-card { padding: 4px 12px; }
    .total-value { font-size: 0.9rem; }
    .nuevo-saldo-box {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    .nuevo-saldo-value { margin-left: 0; }
}
</style>