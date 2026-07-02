<template>
    <AppLayout :title="'Generar Pólizas de Nómina'">
        <!-- FLASH MESSAGES -->
        <div v-if="$page.props.flash" class="flash-container">
            <div v-if="$page.props.flash.success" class="flash-message flash-success">
                <span class="flash-icon">✔</span>
                <span class="flash-text">{{ $page.props.flash.success }}</span>
                <button @click="$page.props.flash.success = null" class="flash-close">×</button>
            </div>
            <div v-if="$page.props.flash.error" class="flash-message flash-error">
                <span class="flash-icon">✖</span>
                <span class="flash-text">{{ $page.props.flash.error }}</span>
                <button @click="$page.props.flash.error = null" class="flash-close">×</button>
            </div>
            <div v-if="$page.props.flash.info" class="flash-message flash-info">
                <span class="flash-icon">ℹ</span>
                <span class="flash-text">{{ $page.props.flash.info }}</span>
                <button @click="$page.props.flash.info = null" class="flash-close">×</button>
            </div>
        </div>

        <template #header>
            <div class="header-wrapper">
                <div class="header-left">
                    <Link :href="route('movimientos.index')" class="btn-back">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                    </Link>
                    <div class="header-content">
                        <h2 class="header-title">Generar Pólizas de Nómina</h2>
                        <p class="header-subtitle">Genera de manera masiva las pólizas de pago de empleados</p>
                    </div>
                </div>
            </div>
        </template>

        <div class="page-content">
            <div class="container-custom">
                <div class="form-card">
                    <!-- Mostrar mensaje si no hay empleados -->
                    <div v-if="empleados.length === 0" class="empty-state-premium" style="padding: 40px; text-align: center;">
                        <span class="empty-icon">👤</span>
                        <h3 style="margin-top: 16px;">No hay empleados disponibles</h3>
                        <p style="color: #6b7280; margin-top: 8px;">Registra personas en el módulo de personas para poder generar nóminas.</p>
                        <Link :href="route('personas.index')" class="btn-premium btn-submit-premium" style="margin-top: 16px;">
                            Ir a Personas
                        </Link>
                    </div>

                    <form v-else @submit.prevent="submit" id="nominaForm" novalidate>
                        <!-- ============================================ -->
                        <!-- SECCION 1: CONFIGURACION DE LA NOMINA -->
                        <!-- ============================================ -->
                        <div class="section-block-premium">
                            <div class="section-header-premium">
                                <div class="section-icon-premium blue">
                                    <svg class="icon-svg-premium" fill="none" stroke="white" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="section-title-text">Configuración de la Nómina</h3>
                                    <p class="section-title-sub">Configura los datos generales de la nómina</p>
                                </div>
                            </div>

                            <div class="form-grid-premium">
                                <!-- Fecha de Pago (automatica - fecha del sistema) -->
                                <div class="form-group-premium">
                                    <label class="form-label-premium">
                                        Fecha de Pago <span class="required-star">*</span>
                                    </label>
                                    <div class="input-wrapper-premium">
                                        <input type="date" v-model="formData.fecha_pago"
                                               class="form-input-premium"
                                               disabled
                                               style="background: #f3f4f6; cursor: not-allowed;">
                                        <div class="input-icon-premium">
                                            <svg class="icon-svg-sm-premium" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="field-hint-premium">La fecha se autocompleta con la fecha del sistema</div>
                                    <div v-if="errors.fecha_pago" class="error-message-premium">{{ errors.fecha_pago }}</div>
                                </div>


                                <!-- Caja Fondo -->
                                <div class="form-group-premium">
                                    <label class="form-label-premium">
                                        Caja Fondo <span class="required-star">*</span>
                                    </label>
                                    <div class="input-wrapper-premium">
                                        <select v-model="formData.id_cuenta_fondeadora"
                                                @change="clearError('id_cuenta_fondeadora')"
                                                class="form-input-premium form-select-premium"
                                                :class="{ 'error': errors.id_cuenta_fondeadora }">
                                            <option value="">Selecciona una caja fondo</option>
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
                                    <div v-if="errors.id_cuenta_fondeadora" class="error-message-premium">{{ errors.id_cuenta_fondeadora }}</div>
                                </div>

                                <!-- Tipo de Poliza (Egreso por defecto) -->
                                <div class="form-group-premium">
                                    <label class="form-label-premium">
                                        Tipo de Póliza <span class="required-star">*</span>
                                    </label>
                                    <div class="input-wrapper-premium">
                                        <select v-model="formData.tipo_poliza"
                                                class="form-input-premium form-select-premium"
                                                disabled
                                                style="background: #f3f4f6; cursor: not-allowed;">
                                            <option value="EGRESO">Egreso</option>
                                        </select>
                                        <div class="input-icon-premium">
                                            <svg class="icon-svg-sm-premium" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </div>
                                    </div>
                                </div>

                                <!-- Marcador -->
                                <div class="form-group-premium">
                                    <label class="form-label-premium">Marcador</label>
                                    <div class="input-wrapper-premium" style="display: flex; gap: 8px;">
                                        <select v-model="formData.id_marcador"
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

                                <!-- Observación General (se replica en todos los empleados) -->
                                <div class="form-group-premium full-width-premium">
                                    <label class="form-label-premium">
                                        Observación General <span class="required-star">*</span>
                                    </label>
                                    <div class="input-wrapper-premium">
                                        <input type="text" v-model="formData.observacion_general"
                                               class="form-input-premium"
                                               placeholder="Ej: Pago de nómina quincenal"
                                               @input="replicarObservacion">
                                    </div>
                                    <div class="field-hint-premium">Esta observación se replicará en todos los empleados seleccionados</div>
                                    <div v-if="errors.observacion_general" class="error-message-premium">{{ errors.observacion_general }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- ============================================ -->
                        <!-- SECCION 2: LISTA DE EMPLEADOS -->
                        <!-- ============================================ -->
                        <div class="section-block-premium">
                            <div class="section-header-premium">
                                <div class="section-icon-premium green">
                                    <svg class="icon-svg-premium" fill="none" stroke="white" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="section-title-text">Empleados</h3>
                                    <p class="section-title-sub">Selecciona los empleados a pagar y sus montos</p>
                                </div>
                            </div>

                            <!-- Barra de acciones -->
                            <div class="table-actions-premium">
                                <div class="table-actions-left">
                                    <button type="button" @click="seleccionarTodos" class="btn-action-premium btn-select-all">
                                        <svg class="icon-svg-sm-premium" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                        </svg>
                                        Seleccionar Todos
                                    </button>
                                    <button type="button" @click="deseleccionarTodos" class="btn-action-premium btn-deselect-all">
                                        <svg class="icon-svg-sm-premium" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                        Deseleccionar Todos
                                    </button>
                                </div>
                                <div class="table-actions-right">
                                    <span class="selected-count-premium">{{ empleadosSeleccionados }} seleccionados</span>
                                    <span class="total-payroll-premium">Total: ${{ formatNumber(totalNomina) }}</span>
                                </div>
                            </div>

                            <!-- Tabla de empleados -->
                            <div class="table-container-premium">
                                <table class="table-premium">
                                    <thead>
                                        <tr>
                                            <th style="width: 50px;">
                                                <input type="checkbox" 
                                                       :checked="todosSeleccionados"
                                                       @change="toggleSeleccionarTodos"
                                                       class="checkbox-input-premium">
                                            </th>
                                            <th>Empleado</th>
                                            <th style="width: 180px;">Monto a Pagar</th>
                                            <th style="width: 200px;">Cuenta de Fondo</th>
                                            <th>Observación</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(empleado, index) in empleados" :key="empleado.id_persona">
                                            <td>
                                                <input type="checkbox" 
                                                       v-model="empleado.seleccionado"
                                                       class="checkbox-input-premium">
                                            </td>
                                            <td>
                                                <span class="empleado-nombre">{{ empleado.nombre_completo }}</span>
                                            </td>
                                            <td>
                                                <div class="input-wrapper-premium" style="width: 100%;">
                                                    <span class="input-prefix-premium">$</span>
                                                    <input type="number" 
                                                           step="0.01" 
                                                           v-model.number="empleado.monto"
                                                           class="form-input-premium monto-input"
                                                           placeholder="0.00"
                                                           min="0">
                                                </div>
                                            </td>
                                            <td>
                                                <div class="input-wrapper-premium" style="width: 100%;">
                                                    <select v-model="empleado.id_cuenta_fondeadora"
                                                            class="form-input-premium form-select-premium"
                                                            style="width: 100%;">
                                                        <option value="">Seleccionar</option>
                                                        <option v-for="c in cuentasFondeadoras" :key="c.id_cuenta" :value="c.id_cuenta">
                                                            {{ c.nombre_cuenta }}
                                                        </option>
                                                    </select>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="input-wrapper-premium" style="width: 100%;">
                                                    <input type="text" 
                                                           v-model="empleado.observacion"
                                                           class="form-input-premium"
                                                           placeholder="Observación">
                                                </div>
                                            </td>
                                        </tr>
                                        <tr v-if="empleados.length === 0">
                                            <td colspan="5" class="empty-state-premium">
                                                <span class="empty-icon">📭</span>
                                                <span>No hay empleados disponibles</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
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
                            <span style="margin-left: 16px;">Total a pagar: <strong>${{ formatNumber(totalNomina) }}</strong></span>
                            <span style="margin-left: 16px;">Empleados: <strong>{{ empleadosSeleccionados }}</strong></span>
                        </div>

                        <div class="form-actions-premium">
                            <div class="actions-left-premium">
                                <Link :href="route('movimientos.index')" class="btn-premium btn-cancel-premium">
                                    <svg class="btn-icon-premium" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    Cancelar
                                </Link>
                            </div>
                            <div class="actions-right-premium">
                                <button type="submit" 
                                        :disabled="processing || !isFormValid"
                                        class="btn-premium btn-submit-premium">
                                    <span v-if="processing" class="spinner-border-premium"></span>
                                    <svg v-else class="btn-icon-premium" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                                    </svg>
                                    {{ processing ? 'Generando...' : 'Generar Pólizas' }}
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
                            <label class="form-label-premium">Descripción</label>
                            <div class="input-wrapper-premium">
                                <textarea v-model="nuevoMarcador.descripcion"
                                          class="form-textarea-premium"
                                          rows="2"
                                          placeholder="Breve descripción..."></textarea>
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
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import AlertModal from '@/Components/AlertModal.vue';
import axios from 'axios';
import { ref, computed, onMounted, watch, reactive } from 'vue';

// ============================================
// PROPS
// ============================================
const props = defineProps({
    empresa_id: { type: Number, default: null },
    empleados: { type: Array, default: () => [] },
    cuentas_fondeadoras: { type: Array, default: () => [] },
    cuentas_nomina: { type: Array, default: () => [] },
    marcadores: { type: Array, default: () => [] }
});

// ============================================
// REFS
// ============================================
const alertRef = ref(null);
const modalMarcadorVisible = ref(false);
const guardandoMarcador = ref(false);
const nuevoMarcador = ref({ nombre: '', descripcion: '' });
const processing = ref(false);

// ============================================
// DATOS REACTIVOS
// ============================================
const cuentasFondeadoras = ref(props.cuentas_fondeadoras || []);
const cuentasNomina = ref(props.cuentas_nomina || []);
const marcadores = ref(props.marcadores || []);

// Inicializar empleados con datos de props
const empleados = ref([]);

// ============================================
// FORMULARIO
// ============================================
const formData = reactive({
    fecha_pago: '', // Se autocompleta con la fecha del sistema
    id_cuenta: props.cuentas_nomina?.length > 0 ? props.cuentas_nomina[0].id_cuenta : null,
    id_cuenta_fondeadora: props.cuentas_fondeadoras?.length > 0 ? props.cuentas_fondeadoras[0].id_cuenta : null,
    tipo_poliza: 'EGRESO',
    id_marcador: null,
    observacion_general: '', // Nueva: observación que se replica
});

const errors = reactive({});

// ============================================
// COMPUTED
// ============================================
const empleadosSeleccionados = computed(() => {
    return empleados.value.filter(e => e.seleccionado).length;
});

const totalNomina = computed(() => {
    return empleados.value
        .filter(e => e.seleccionado)
        .reduce((sum, e) => sum + (parseFloat(e.monto) || 0), 0);
});

const todosSeleccionados = computed({
    get: () => {
        const disponibles = empleados.value.length;
        if (disponibles === 0) return false;
        return empleados.value.every(e => e.seleccionado);
    },
    set: (value) => {
        empleados.value.forEach(e => e.seleccionado = value);
    }
});

const isFormValid = computed(() => {
    // Validar fecha de pago
    if (!formData.fecha_pago) return false;
    
    // Validar cuenta
    if (!formData.id_cuenta) return false;
    
    // Validar cuenta fondeadora
    if (!formData.id_cuenta_fondeadora) return false;
    
    // Validar observación general
    if (!formData.observacion_general || formData.observacion_general.trim() === '') return false;
    
    // Validar que haya al menos un empleado seleccionado
    if (empleadosSeleccionados.value === 0) return false;
    
    // Validar que todos los empleados seleccionados tengan monto > 0
    const empleadosConMonto = empleados.value
        .filter(e => e.seleccionado)
        .every(e => e.monto > 0);
    
    if (!empleadosConMonto) return false;
    
    // Validar que todos los empleados seleccionados tengan cuenta de fondo
    const empleadosConCuenta = empleados.value
        .filter(e => e.seleccionado)
        .every(e => e.id_cuenta_fondeadora);
    
    if (!empleadosConCuenta) return false;
    
    return true;
});

// ============================================
// METODOS
// ============================================
const clearError = (field) => {
    if (errors[field]) delete errors[field];
};

const formatNumber = (value) => {
    if (!value && value !== 0) return '0.00';
    return Number(value).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const seleccionarTodos = () => {
    empleados.value.forEach(e => e.seleccionado = true);
};

const deseleccionarTodos = () => {
    empleados.value.forEach(e => e.seleccionado = false);
};

const toggleSeleccionarTodos = (event) => {
    const checked = event.target.checked;
    empleados.value.forEach(e => e.seleccionado = checked);
};

// ============================================
// REPLICAR OBSERVACIÓN
// ============================================
const replicarObservacion = () => {
    const observacion = formData.observacion_general;
    empleados.value.forEach(e => {
        if (e.seleccionado) {
            e.observacion = observacion;
        }
    });
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
            formData.id_marcador = res.data.data.id;
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
    } finally { 
        guardandoMarcador.value = false; 
    }
};

// ============================================
// SUBMIT
// ============================================
const submit = () => {
    processing.value = true;
    
    // Limpiar errores
    Object.keys(errors).forEach(key => delete errors[key]);

    // Validar fecha de pago
    if (!formData.fecha_pago) {
        errors.fecha_pago = 'La fecha de pago no se ha calculado correctamente';
        processing.value = false;
        return;
    }

    // Validar cuenta
    if (!formData.id_cuenta) {
        errors.id_cuenta = 'Selecciona una cuenta para la nómina';
        processing.value = false;
        return;
    }

    // Validar cuenta fondeadora
    if (!formData.id_cuenta_fondeadora) {
        errors.id_cuenta_fondeadora = 'Selecciona una caja fondo';
        processing.value = false;
        return;
    }

    // Validar observación general
    if (!formData.observacion_general || formData.observacion_general.trim() === '') {
        errors.observacion_general = 'Ingresa una observación general para la nómina';
        processing.value = false;
        return;
    }

    // Validar empleados seleccionados
    const empleadosSeleccionados = empleados.value.filter(e => e.seleccionado);
    if (empleadosSeleccionados.length === 0) {
        alertRef.value?.show({
            type: 'error',
            title: 'Error',
            message: 'Selecciona al menos un empleado para generar la póliza',
            buttonText: 'Entendido'
        });
        processing.value = false;
        return;
    }

    // Validar montos
    const empleadosSinMonto = empleadosSeleccionados.filter(e => e.monto <= 0);
    if (empleadosSinMonto.length > 0) {
        const nombres = empleadosSinMonto.map(e => e.nombre_completo).join(', ');
        alertRef.value?.show({
            type: 'error',
            title: 'Error',
            message: `Los siguientes empleados no tienen un monto válido: ${nombres}`,
            buttonText: 'Entendido'
        });
        processing.value = false;
        return;
    }

    // Validar cuentas de fondo
    const empleadosSinCuenta = empleadosSeleccionados.filter(e => !e.id_cuenta_fondeadora);
    if (empleadosSinCuenta.length > 0) {
        const nombres = empleadosSinCuenta.map(e => e.nombre_completo).join(', ');
        alertRef.value?.show({
            type: 'error',
            title: 'Error',
            message: `Los siguientes empleados no tienen una cuenta de fondo seleccionada: ${nombres}`,
            buttonText: 'Entendido'
        });
        processing.value = false;
        return;
    }

    // Preparar datos para enviar
    const data = {
        fecha_pago: formData.fecha_pago,
        id_cuenta: formData.id_cuenta,
        id_cuenta_fondeadora: formData.id_cuenta_fondeadora,
        tipo_poliza: formData.tipo_poliza,
        id_marcador: formData.id_marcador,
        observacion_general: formData.observacion_general,
        empleados: empleadosSeleccionados.map(e => ({
            id_persona: e.id_persona,
            monto: e.monto,
            id_cuenta_fondeadora: e.id_cuenta_fondeadora,
            observacion: e.observacion || formData.observacion_general
        }))
    };

    // Enviar al servidor
    axios.post(route('movimientos.nomina.store'), data)
        .then(() => {
            processing.value = false;
            alertRef.value?.show({ 
                type: 'success', 
                title: 'Exito', 
                message: 'Las pólizas de nómina se han generado correctamente.',
                buttonText: 'Ir al listado'
            });
            setTimeout(() => {
                router.visit(route('movimientos.index'), { 
                    method: 'get', 
                    replace: true 
                });
            }, 1500);
        })
        .catch(error => {
            processing.value = false;
            console.error('Error:', error);
            
            if (error.response?.data?.errors) {
                const err = error.response.data.errors;
                Object.keys(err).forEach(key => {
                    errors[key] = Array.isArray(err[key]) ? err[key][0] : err[key];
                });
                const firstError = Object.values(errors)[0];
                alertRef.value?.show({ 
                    type: 'error', 
                    title: 'Error de validación', 
                    message: firstError,
                    buttonText: 'Entendido' 
                });
            } else {
                alertRef.value?.show({ 
                    type: 'error', 
                    title: 'Error', 
                    message: error.response?.data?.message || 'Error al generar las pólizas. Intenta nuevamente.',
                    buttonText: 'Entendido' 
                });
            }
        });
};

// ============================================
// WATCHERS
// ============================================
// Cuando cambia la selección de empleados, replicar observación a los seleccionados
watch(
    () => empleados.value.map(e => e.seleccionado),
    () => {
        const observacion = formData.observacion_general;
        empleados.value.forEach(e => {
            if (e.seleccionado) {
                e.observacion = observacion;
            }
        });
    },
    { deep: true }
);

// ============================================
// MOUNTED
// ============================================
onMounted(() => {
    console.log('Props recibidas:', props);
    
    // 🔥 Fecha del sistema autocompletada
    const hoy = new Date();
    const year = hoy.getFullYear();
    const month = String(hoy.getMonth() + 1).padStart(2, '0');
    const day = String(hoy.getDate()).padStart(2, '0');
    formData.fecha_pago = `${year}-${month}-${day}`;
    
    // Inicializar cuentas de nómina
    if (props.cuentas_nomina && props.cuentas_nomina.length > 0) {
        cuentasNomina.value = props.cuentas_nomina;
        formData.id_cuenta = props.cuentas_nomina[0].id_cuenta;
    }

    // Inicializar cuentas fondeadoras
    if (props.cuentas_fondeadoras && props.cuentas_fondeadoras.length > 0) {
        cuentasFondeadoras.value = props.cuentas_fondeadoras;
        formData.id_cuenta_fondeadora = props.cuentas_fondeadoras[0].id_cuenta;
    }

    // Inicializar marcadores
    if (props.marcadores && props.marcadores.length > 0) {
        marcadores.value = props.marcadores;
    }

    // Inicializar empleados
    if (props.empleados && props.empleados.length > 0) {
        empleados.value = props.empleados.map(e => ({
            ...e,
            seleccionado: false,
            monto: 0,
            id_cuenta_fondeadora: props.cuentas_fondeadoras?.length > 0 ? props.cuentas_fondeadoras[0].id_cuenta : null,
            observacion: ''
        }));
        console.log('Empleados inicializados:', empleados.value.length);
    } else {
        console.warn('No hay empleados en props');
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

/* ========== FLASH MESSAGES ========== */
.flash-container {
    margin: 0 0 16px 0;
    padding: 0 1.5rem;
}

.flash-message {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 20px;
    border-radius: 12px;
    animation: slideDown 0.5s ease;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    margin-bottom: 8px;
}

.flash-success {
    background: linear-gradient(135deg, #ecfdf5, #d1fae5);
    border-left: 4px solid #10b981;
}

.flash-error {
    background: linear-gradient(135deg, #fef2f2, #fecaca);
    border-left: 4px solid #dc2626;
}

.flash-info {
    background: linear-gradient(135deg, #eff6ff, #dbeafe);
    border-left: 4px solid #3b82f6;
}

.flash-icon {
    font-size: 1.2rem;
    margin-right: 12px;
}

.flash-text {
    font-size: 0.95rem;
    font-weight: 500;
    color: #1f2937;
    flex: 1;
}

.flash-close {
    background: none;
    border: none;
    color: #6b7280;
    font-size: 1.2rem;
    cursor: pointer;
    padding: 4px 8px;
    border-radius: 6px;
    transition: all 0.3s ease;
}

.flash-close:hover {
    background: rgba(0, 0, 0, 0.05);
    color: #dc2626;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
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
.section-icon-premium.green { background: linear-gradient(135deg, #10b981, #059669); }

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

.form-input-premium:disabled {
    background: #f3f4f6;
    cursor: not-allowed;
    opacity: 0.7;
}

.form-select-premium {
    appearance: none;
    cursor: pointer;
    padding-right: 40px;
}

.form-select-premium:disabled {
    background: #f3f4f6;
    cursor: not-allowed;
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

.field-hint-premium {
    font-size: 0.7rem;
    color: #94a3b8;
    margin-top: 2px;
}

.error-message-premium {
    font-size: 0.75rem;
    color: #ef4444;
    display: flex;
    align-items: center;
    gap: 4px;
    margin-top: 4px;
    animation: slideDown 0.3s ease;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}

/* ========== TABLE ========== */
.table-actions-premium {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 16px;
    padding: 12px 16px;
    background: #f8fafc;
    border-radius: 10px;
    border: 1px solid #e5e7eb;
}

.table-actions-left {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.btn-action-premium {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 16px;
    border: none;
    border-radius: 8px;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    background: white;
    color: #64748b;
    border: 1px solid #e5e7eb;
}

.btn-action-premium:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.btn-select-all {
    color: #2563eb;
}

.btn-select-all:hover {
    background: #eff6ff;
    border-color: #2563eb;
}

.btn-deselect-all {
    color: #dc2626;
}

.btn-deselect-all:hover {
    background: #fef2f2;
    border-color: #dc2626;
}

.table-actions-right {
    display: flex;
    gap: 16px;
    align-items: center;
    flex-wrap: wrap;
}

.selected-count-premium {
    font-size: 0.85rem;
    font-weight: 600;
    color: #475569;
}

.total-payroll-premium {
    font-size: 0.95rem;
    font-weight: 700;
    color: #0f172a;
    padding: 4px 14px;
    background: linear-gradient(135deg, #ecfdf5, #d1fae5);
    border-radius: 8px;
    border: 1px solid #a7f3d0;
}

.table-container-premium {
    overflow-x: auto;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
}

.table-premium {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.9rem;
}

.table-premium thead {
    background: linear-gradient(135deg, #f8fafc, #eef2ff);
    border-bottom: 2px solid #e5e7eb;
}

.table-premium th {
    padding: 12px 14px;
    text-align: left;
    font-weight: 700;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #475569;
    white-space: nowrap;
}

.table-premium td {
    padding: 10px 14px;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}

.table-premium tbody tr:hover {
    background: #f8fafc;
}

.table-premium tbody tr:last-child td {
    border-bottom: none;
}

.empleado-nombre {
    font-weight: 600;
    color: #0f172a;
}

.monto-input {
    height: 38px;
    font-size: 0.85rem;
    padding: 6px 10px 6px 28px;
}

.checkbox-input-premium {
    width: 18px;
    height: 18px;
    accent-color: #667eea;
    cursor: pointer;
    border-radius: 4px;
}

.empty-state-premium {
    text-align: center;
    padding: 40px 20px;
    color: #94a3b8;
    font-weight: 500;
}

.empty-icon {
    font-size: 3rem;
    display: block;
    margin-bottom: 8px;
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

.btn-add-marcador-premium {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    border: 2px dashed #d1d5db;
    border-radius: 10px;
    background: white;
    color: #667eea;
    cursor: pointer;
    transition: all 0.3s ease;
    flex-shrink: 0;
}

.btn-add-marcador-premium:hover {
    border-color: #667eea;
    background: linear-gradient(135deg, #f8f7ff, #eef2ff);
    transform: scale(1.05) rotate(90deg);
}

/* ========== FORM ACTIONS ========== */
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
}

/* ========== INFO BOX ========== */
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
    flex-wrap: wrap;
}

.info-icon-premium {
    width: 20px;
    height: 20px;
    flex-shrink: 0;
}

.text-danger-premium {
    color: #ef4444;
}

/* ========== SPINNER ========== */
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

/* ========== MODAL ========== */
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

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
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

/* ========== RESPONSIVE ========== */
@media (max-width: 992px) {
    .form-grid-premium {
        grid-template-columns: 1fr 1fr;
    }
}

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

    .table-actions-premium {
        flex-direction: column;
        align-items: stretch;
    }

    .table-actions-left {
        justify-content: center;
    }

    .table-actions-right {
        justify-content: center;
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

    .table-premium {
        font-size: 0.8rem;
    }

    .table-premium th,
    .table-premium td {
        padding: 8px 10px;
    }

    .info-box-premium {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }

    .modal-container-premium {
        margin: 16px;
        max-height: 80vh;
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

    .table-premium th,
    .table-premium td {
        padding: 6px 8px;
        font-size: 0.75rem;
    }

    .table-actions-premium {
        padding: 10px 12px;
    }

    .btn-action-premium {
        font-size: 0.7rem;
        padding: 4px 12px;
    }

    .total-payroll-premium {
        font-size: 0.8rem;
        padding: 4px 10px;
    }

    .selected-count-premium {
        font-size: 0.75rem;
    }
}
</style>