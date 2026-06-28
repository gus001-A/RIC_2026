<template>
    <AppLayout title="RIC - Reportes">
        <div class="py-6">
            <div class="max-w-full px-4 sm:px-6 lg:px-8">
                <!-- Selector de Empresa -->
                <div v-if="empresas.length > 0" class="empresa-selector-premium">
                    <div class="empresa-selector-content">
                        <div class="empresa-selector-label">
                            <ShopOutlined style="font-size: 16px; color: #667eea;" />
                            <span>Empresa</span>
                        </div>
                        <div class="empresa-selector-field">
                            <select 
                                v-model="empresaSeleccionada"
                                @change="cambiarEmpresa"
                                class="empresa-select-native"
                            >
                                <option value="">Seleccione...</option>
                                <option 
                                    v-for="empresa in empresas" 
                                    :key="empresa.id" 
                                    :value="empresa.id"
                                >
                                    {{ empresa.nombre_empresa }}
                                </option>
                            </select>
                        </div>
                        <div class="empresa-selector-actions">
                            <div class="view-toggle-group">
                                <button 
                                    class="view-toggle-btn" 
                                    :class="{ active: vistaActual === 'por_cuenta' }"
                                    @click="cambiarVista('por_cuenta')"
                                >
                                    <svg class="view-toggle-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                                    </svg>
                                    Por Cuenta
                                </button>
                                <button 
                                    class="view-toggle-btn" 
                                    :class="{ active: vistaActual === 'por_persona' }"
                                    @click="cambiarVista('por_persona')"
                                >
                                    <svg class="view-toggle-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    Por Persona
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BARRA SUPERIOR: FECHAS + BOTONES -->
                <div v-if="empresas.length > 0" class="filtros-superior-premium">
                    <div class="filtros-superior-content">
                        <!-- Fechas -->
                        <div class="fecha-item">
                            <InputLabel>Desde</InputLabel>
                            <input 
                                type="date" 
                                v-model="filtros.fecha_desde"
                                @change="cargarReporte"
                                :max="fechaActual"
                                class="fecha-input-premium"
                            />
                        </div>
                        <div class="fecha-item">
                            <InputLabel>Hasta</InputLabel>
                            <input 
                                type="date" 
                                v-model="filtros.fecha_hasta"
                                @change="cargarReporte"
                                :max="fechaActual"
                                class="fecha-input-premium"
                            />
                        </div>
                        <div class="fecha-item fecha-actions">
                            <a-button 
                                size="small" 
                                class="btn-hoy-premium"
                                @click="setFechaHoy"
                            >
                                <template #icon>
                                    <CalendarOutlined />
                                </template>
                                Hoy
                            </a-button>
                            <a-button 
                                v-if="filtros.fecha_desde || filtros.fecha_hasta" 
                                size="small" 
                                class="btn-limpiar-fechas"
                                @click="limpiarFechas"
                            >
                                <template #icon>
                                    <CloseOutlined />
                                </template>
                                Limpiar
                            </a-button>
                        </div>

                        <!-- Separador -->
                        <div class="filtros-separator"></div>

                        <!-- Botones Exportación -->
                        <div class="fecha-item fecha-actions-export">
                            <a-button 
                                size="small" 
                                class="btn-export-excel-mini"
                                @click="exportarExcel"
                            >
                                <template #icon>
                                    <FileExcelOutlined />
                                </template>
                                Excel
                            </a-button>
                            <a-button 
                                size="small" 
                                class="btn-resultados-mini"
                                @click="abrirModalCuentasResultados"
                            >
                                <template #icon>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                    </svg>
                                </template>
                                Resultados
                            </a-button>
                        </div>
                    </div>
                </div>

                <!-- CONTENIDO DEL REPORTE -->
                <div v-if="empresas.length > 0 && cargado" class="reporte-wrapper-premium">
                    <!-- TABLA PRINCIPAL -->
                    <div class="tabla-principal-premium">
                        <div class="table-header-ultra">
                            <div class="table-header-left-ultra">
                                <span class="table-title-premium">
                                    Movimientos por {{ vistaActual === 'por_cuenta' ? 'Cuenta' : 'Persona' }}
                                </span>
                                <a-tag v-if="filtrosActivos" color="blue" class="filter-tag-ultra">
                                    <span class="filter-dot-active"></span>
                                    Filtros activos
                                </a-tag>
                            </div>
                            <div class="table-header-right-ultra">
                                <span class="total-registros-premium">
                                    Total: <strong>{{ reporteData.length }}</strong> registros
                                </span>
                            </div>
                        </div>

                        <div class="table-scroll-container">
                            <a-table
                                :columns="columnasPrincipales"
                                :data-source="datosFiltrados"
                                :pagination="false"
                                :loading="loading"
                                row-key="id"
                                class="reporte-table-ultra"
                                size="middle"
                                :scroll="{ x: 'max-content', y: 400 }"
                                sticky
                                @row-click="onRowClick"
                            >
                                <template #bodyCell="{ column, record }">
                                    <template v-if="column.key === 'nombre'">
                                        <span class="nombre-text-ultra clickable" @click="onRowClick(record)">
                                            {{ record.nombre || '---' }}
                                        </span>
                                    </template>
                                    <template v-if="column.key === 'codigo'">
                                        <span class="codigo-text-ultra">{{ record.codigo || '---' }}</span>
                                    </template>
                                    <template v-if="column.key === 'persona'">
                                        <span class="persona-text-ultra">{{ record.persona || '---' }}</span>
                                    </template>
                                    <template v-if="column.key === 'fondeo'">
                                        <span class="fondeo-text-ultra">{{ record.fondeo || '---' }}</span>
                                    </template>
                                    <template v-if="column.key === 'ingreso'">
                                        <span class="monto-text-ultra ingreso">
                                            ${{ formatNumber(record.ingreso || 0) }}
                                        </span>
                                    </template>
                                    <template v-if="column.key === 'egreso'">
                                        <span class="monto-text-ultra egreso">
                                            ${{ formatNumber(record.egreso || 0) }}
                                        </span>
                                    </template>
                                </template>
                            </a-table>
                        </div>

                        <!-- FILTROS INFERIORES -->
                        <div class="filtros-inferior-tabla">
                            <div class="filtros-inferior-grid">
                                <div class="filtro-inferior-item">
                                    <label class="filtro-inferior-label">
                                        {{ vistaActual === 'por_cuenta' ? 'Cuenta' : 'Persona' }}
                                    </label>
                                    <input 
                                        v-model="filtrosTabla.nombre"
                                        @input="aplicarFiltrosTabla"
                                        placeholder="Buscar..."
                                        class="filtro-inferior-input"
                                    />
                                </div>
                                <div class="filtro-inferior-item" v-if="vistaActual === 'por_cuenta'">
                                    <label class="filtro-inferior-label">Fondero</label>
                                    <input 
                                        v-model="filtrosTabla.fondeo"
                                        @input="aplicarFiltrosTabla"
                                        placeholder="Buscar..."
                                        class="filtro-inferior-input"
                                    />
                                </div>
                                <div class="filtro-inferior-item" v-if="vistaActual === 'por_cuenta'">
                                    <label class="filtro-inferior-label">Persona</label>
                                    <input 
                                        v-model="filtrosTabla.persona"
                                        @input="aplicarFiltrosTabla"
                                        placeholder="Buscar..."
                                        class="filtro-inferior-input"
                                    />
                                </div>
                                <div class="filtro-inferior-item filtro-inferior-actions">
                                    <button 
                                        v-if="filtrosTablaActivos" 
                                        class="btn-limpiar-filtros-inferior"
                                        @click="limpiarFiltrosTabla"
                                    >
                                        <CloseOutlined />
                                        Limpiar filtros
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- TOTALES DE LA TABLA PRINCIPAL -->
                        <div class="totales-principal-wrapper">
                            <div class="totales-principal-container">
                                <div class="total-item-principal">
                                    <span class="total-label-principal">Total Ingresos</span>
                                    <span class="total-value-principal ingreso">${{ formatNumber(totalIngresos) }}</span>
                                </div>
                                <div class="total-item-principal">
                                    <span class="total-label-principal">Total Egresos</span>
                                    <span class="total-value-principal egreso">${{ formatNumber(totalEgresos) }}</span>
                                </div>
                                <div class="total-item-principal total-balance">
                                    <span class="total-label-principal">Balance</span>
                                    <span class="total-value-principal" :class="balanceClass">${{ formatNumber(balance) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TABLA DE CUENTAS FONDEADORAS -->
                    <div class="tabla-fondeadoras-premium">
                        <div class="table-header-ultra">
                            <div class="table-header-left-ultra">
                                <span class="table-title-premium">
                                    Cuentas Fondeadoras - Saldos
                                </span>
                            </div>
                            <div class="table-header-right-ultra">
                                <span class="total-registros-premium">
                                    Total disponible: <strong class="total-disponible">${{ formatNumber(totalDisponible) }}</strong>
                                </span>
                            </div>
                        </div>

                        <div class="table-scroll-container">
                            <a-table
                                :columns="columnasFondeadoras"
                                :data-source="reporteFondeadoras"
                                :pagination="false"
                                :loading="loading"
                                row-key="id_cuenta"
                                class="reporte-table-ultra"
                                size="middle"
                                :scroll="{ x: 'max-content', y: 200 }"
                                sticky
                            >
                                <template #bodyCell="{ column, record }">
                                    <template v-if="column.key === 'codigo'">
                                        <span class="codigo-text-ultra">{{ record.codigo_cuenta || '---' }}</span>
                                    </template>
                                    <template v-if="column.key === 'nombre'">
                                        <span class="nombre-text-ultra">{{ record.nombre_cuenta || '---' }}</span>
                                    </template>
                                    <template v-if="column.key === 'saldo'">
                                        <span class="monto-text-ultra" :class="record.saldo >= 0 ? 'ingreso' : 'egreso'">
                                            ${{ formatNumber(record.saldo || 0) }}
                                        </span>
                                    </template>
                                </template>
                            </a-table>
                        </div>

                        <div class="totales-fondeadoras-wrapper">
                            <div class="totales-fondeadoras-container">
                                <div class="total-item-fondeadora">
                                    <span class="total-label-fondeadora">Total disponible</span>
                                    <span class="total-value-fondeadora ingreso">${{ formatNumber(totalDisponible) }}</span>
                                </div>
                                <div class="total-item-fondeadora">
                                    <span class="total-label-fondeadora">Cuentas activas</span>
                                    <span class="total-value-fondeadora">{{ reporteFondeadoras.length }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mensaje si no hay empresas -->
                <div v-else class="reporte-wrapper-premium">
                    <div class="text-center py-12">
                        <div class="text-6xl mb-4">🏢</div>
                        <h3 class="text-xl font-semibold text-gray-700 mb-2">No tienes empresas asignadas</h3>
                        <p class="text-gray-500">Contacta al administrador para que te asigne una empresa.</p>
                    </div>
                </div>

                <!-- Mensaje de carga -->
                <div v-if="loading && empresas.length > 0" class="reporte-wrapper-premium">
                    <div class="text-center py-12">
                        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="text-gray-500 mt-4">Cargando reporte...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================ -->
        <!-- MODAL: CUENTAS DE RESULTADOS (CON CLICK HABILITADO) -->
        <!-- ============================================ -->
        <a-modal
            v-model:open="modalResultadosVisible"
            title="Cuentas de Resultados"
            width="700px"
            :footer="null"
            class="modal-resultados-premium"
        >
            <div v-if="loadingResultados" class="text-center py-8">
                <div class="spinner-border text-primary" role="status" style="width: 2rem; height: 2rem;">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <p class="text-gray-500 mt-3">Cargando cuentas de resultados...</p>
            </div>
            <div v-else-if="cuentasResultados.length === 0" class="text-center py-8">
                <p class="text-gray-500">No hay cuentas de resultados registradas.</p>
            </div>
            <div v-else>
                <div class="table-scroll-container" style="max-height: 450px;">
                    <a-table
                        :columns="columnasResultados"
                        :data-source="cuentasResultados"
                        :pagination="false"
                        row-key="id_cuenta"
                        class="reporte-table-ultra"
                        size="middle"
                        :scroll="{ x: 'max-content', y: 400 }"
                        @row-click="onRowClickResultados"
                    >
                        <template #bodyCell="{ column, record }">
                            <template v-if="column.key === 'nombre'">
                                <span class="nombre-text-ultra clickable" @click="onRowClickResultados(record)">
                                    {{ record.nombre_cuenta || '---' }}
                                </span>
                            </template>
                            <template v-if="column.key === 'saldo'">
                                <span class="monto-text-ultra" :class="(record.saldo || 0) >= 0 ? 'ingreso' : 'egreso'">
                                    ${{ formatNumber(record.saldo || 0) }}
                                </span>
                            </template>
                        </template>
                    </a-table>
                </div>
                <div class="totales-resultados-wrapper">
                    <div class="totales-resultados-container">
                        <div class="total-item-resultados">
                            <span class="total-label-resultados">Total de cuentas</span>
                            <span class="total-value-resultados">{{ cuentasResultados.length }}</span>
                        </div>
                        <div class="total-item-resultados">
                            <span class="total-label-resultados">Saldo total</span>
                            <span class="total-value-resultados" :class="saldoResultadosTotal >= 0 ? 'ingreso' : 'egreso'">
                                ${{ formatNumber(saldoResultadosTotal) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </a-modal>

        <!-- ============================================ -->
        <!-- MODAL: DETALLE DE CUENTA -->
        <!-- ============================================ -->
        <a-modal
            v-model:open="modalDetalleVisible"
            :title="'Detalle de: ' + (cuentaSeleccionada?.nombre || '')"
            width="1000px"
            :footer="null"
            class="modal-detalle-premium"
        >
            <div v-if="loadingDetalle" class="text-center py-8">
                <div class="spinner-border text-primary" role="status" style="width: 2rem; height: 2rem;">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <p class="text-gray-500 mt-3">Cargando movimientos...</p>
            </div>
            <div v-else-if="movimientosCuenta.length === 0" class="text-center py-8">
                <p class="text-gray-500">No hay movimientos para esta cuenta.</p>
            </div>
            <div v-else>
                <div class="totales-modal-detalle">
                    <div class="total-modal-item">
                        <span class="total-modal-label">Total Ingresos</span>
                        <span class="total-modal-value ingreso">${{ formatNumber(totalIngresosCuenta) }}</span>
                    </div>
                    <div class="total-modal-item">
                        <span class="total-modal-label">Total Egresos</span>
                        <span class="total-modal-value egreso">${{ formatNumber(totalEgresosCuenta) }}</span>
                    </div>
                    <div class="total-modal-item total-modal-balance">
                        <span class="total-modal-label">Balance</span>
                        <span class="total-modal-value" :class="balanceCuentaClass">${{ formatNumber(balanceCuenta) }}</span>
                    </div>
                    <div class="total-modal-item">
                        <span class="total-modal-label">Movimientos</span>
                        <span class="total-modal-value">{{ movimientosCuenta.length }}</span>
                    </div>
                </div>

                <div class="table-scroll-container" style="max-height: 400px;">
                    <a-table
                        :columns="columnasDetalle"
                        :data-source="movimientosCuenta"
                        :pagination="false"
                        row-key="id_movimiento"
                        class="reporte-table-ultra"
                        size="middle"
                        :scroll="{ x: 'max-content', y: 350 }"
                    >
                        <template #bodyCell="{ column, record }">
                            <template v-if="column.key === 'fecha'">
                                <span class="fecha-text-ultra">{{ record.fecha_poliza || '---' }}</span>
                            </template>
                            <template v-if="column.key === 'folio'">
                                <span class="folio-text-ultra clickable" @click="verPoliza(record.id_poliza)">
                                    {{ record.folio || '---' }}
                                </span>
                            </template>
                            <template v-if="column.key === 'persona'">
                                <span class="persona-text-ultra">{{ record.persona || '---' }}</span>
                            </template>
                            <template v-if="column.key === 'cuenta'">
                                <span class="cuenta-text-ultra">{{ record.cuenta || '---' }}</span>
                            </template>
                            <template v-if="column.key === 'cuenta_fondeadora'">
                                <span class="fondeo-text-ultra">{{ record.cuenta_fondeadora || '---' }}</span>
                            </template>
                            <template v-if="column.key === 'monto'">
                                <span class="monto-text-ultra" :class="record.tipo === 'INGRESO' ? 'ingreso' : 'egreso'">
                                    ${{ formatNumber(record.monto) }}
                                </span>
                            </template>
                            <template v-if="column.key === 'acciones'">
                                <a-button size="small" type="primary" @click="verPoliza(record.id_poliza)">
                                    Ver Poliza
                                </a-button>
                            </template>
                        </template>
                    </a-table>
                </div>
            </div>
        </a-modal>

        <ModalAlert ref="modalAlert" />
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted, nextTick } from 'vue';
import ModalAlert from '@/Components/AlertModal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Swal from 'sweetalert2';
import axios from 'axios';
import {
    CloseOutlined,
    ShopOutlined,
    FileExcelOutlined,
    CalendarOutlined,
} from '@ant-design/icons-vue';
import {
    Button as AButton,
    Table as ATable,
    Tag as ATag,
    Modal as AModal,
} from 'ant-design-vue';

const props = defineProps({
    empresas: {
        type: Array,
        default: () => []
    },
    reporte: {
        type: Object,
        default: () => ({ data: [], fondeadoras: [] })
    },
    filtros: {
        type: Object,
        default: () => ({})
    },
    empresa_seleccionada: {
        type: [Number, String],
        default: null
    },
    vista: {
        type: String,
        default: 'por_cuenta'
    }
});

// ============================================
// REFS Y VARIABLES
// ============================================
const loading = ref(false);
const cargado = ref(false);
const modalAlert = ref(null);
const empresaSeleccionada = ref(props.empresa_seleccionada || null);
const fechaActual = ref(new Date().toISOString().split('T')[0]);
const vistaActual = ref(props.vista || 'por_cuenta');
let timeoutId = null;
let timeoutTabla = null;

// ============================================
// MODAL DE RESULTADOS
// ============================================
const modalResultadosVisible = ref(false);
const loadingResultados = ref(false);
const cuentasResultados = ref([]);

// ============================================
// MODAL DE DETALLE
// ============================================
const modalDetalleVisible = ref(false);
const loadingDetalle = ref(false);
const cuentaSeleccionada = ref(null);
const movimientosCuenta = ref([]);
const totalIngresosCuenta = ref(0);
const totalEgresosCuenta = ref(0);

// ============================================
// FILTROS PRINCIPALES
// ============================================
const filtros = ref({
    fecha_desde: props.filtros?.fecha_desde || '',
    fecha_hasta: props.filtros?.fecha_hasta || '',
});

// ============================================
// FILTROS DE TABLA
// ============================================
const filtrosTabla = ref({
    nombre: '',
    persona: '',
    fondeo: ''
});

const filtrosTablaActivos = computed(() => {
    return Object.values(filtrosTabla.value).some(v => v !== '' && v !== null && v !== undefined);
});

// ============================================
// DATOS DEL REPORTE
// ============================================
const reporteData = ref([]);
const reporteFondeadoras = ref([]);

// ============================================
// COLUMNAS PRINCIPALES
// ============================================
const columnasPrincipales = computed(() => {
    if (vistaActual.value === 'por_cuenta') {
        return [
            {
                title: 'Cuenta de Orden',
                key: 'nombre',
                width: '200px',
                fixed: 'left'
            },
            {
                title: 'Codigo',
                key: 'codigo',
                width: '120px'
            },
            {
                title: 'Persona',
                key: 'persona',
                width: '180px'
            },
            {
                title: 'Fondero',
                key: 'fondeo',
                width: '180px'
            },
            {
                title: 'Ingreso',
                key: 'ingreso',
                width: '150px',
                align: 'right'
            },
            {
                title: 'Egreso',
                key: 'egreso',
                width: '150px',
                align: 'right'
            }
        ];
    } else {
        return [
            {
                title: 'Persona',
                key: 'nombre',
                width: '250px',
                fixed: 'left'
            },
            {
                title: 'Fondero',
                key: 'fondeo',
                width: '200px'
            },
            {
                title: 'Ingreso',
                key: 'ingreso',
                width: '150px',
                align: 'right'
            },
            {
                title: 'Egreso',
                key: 'egreso',
                width: '150px',
                align: 'right'
            }
        ];
    }
});

// ============================================
// COLUMNAS FONDEADORAS
// ============================================
const columnasFondeadoras = [
    {
        title: 'Codigo',
        key: 'codigo',
        width: '120px'
    },
    {
        title: 'Nombre de la Cuenta',
        key: 'nombre',
        width: '300px'
    },
    {
        title: 'Saldo Disponible',
        key: 'saldo',
        width: '180px',
        align: 'right'
    }
];

// ============================================
// COLUMNAS RESULTADOS (SOLO NOMBRE Y SALDO)
// ============================================
const columnasResultados = [
    {
        title: 'Nombre de la Cuenta',
        key: 'nombre',
        width: '100%'
    },
    {
        title: 'Saldo',
        key: 'saldo',
        width: '200px',
        align: 'right'
    }
];

// ============================================
// COLUMNAS DETALLE
// ============================================
const columnasDetalle = [
    {
        title: 'Fecha',
        key: 'fecha',
        width: '120px'
    },
    {
        title: 'Folio',
        key: 'folio',
        width: '140px'
    },
    {
        title: 'Persona',
        key: 'persona',
        width: '180px'
    },
    {
        title: 'Cuenta',
        key: 'cuenta',
        width: '180px'
    },
    {
        title: 'Cuenta Fondero',
        key: 'cuenta_fondeadora',
        width: '180px'
    },
    {
        title: 'Monto',
        key: 'monto',
        width: '140px',
        align: 'right'
    },
    {
        title: 'Acciones',
        key: 'acciones',
        width: '120px',
        align: 'center'
    }
];

// ============================================
// COMPUTED - FILTROS ACTIVOS
// ============================================
const filtrosActivos = computed(() => {
    const { fecha_desde, fecha_hasta } = filtros.value;
    return !!(fecha_desde || fecha_hasta);
});

// ============================================
// COMPUTED - DATOS FILTRADOS
// ============================================
const datosFiltrados = computed(() => {
    let data = reporteData.value;
    
    if (filtrosTabla.value.nombre) {
        const search = filtrosTabla.value.nombre.toLowerCase().trim();
        data = data.filter(item => 
            item.nombre && item.nombre.toLowerCase().includes(search)
        );
    }
    
    if (vistaActual.value === 'por_cuenta' && filtrosTabla.value.persona) {
        const search = filtrosTabla.value.persona.toLowerCase().trim();
        data = data.filter(item => 
            item.persona && item.persona.toLowerCase().includes(search)
        );
    }
    
    if (filtrosTabla.value.fondeo) {
        const search = filtrosTabla.value.fondeo.toLowerCase().trim();
        data = data.filter(item => 
            item.fondeo && item.fondeo.toLowerCase().includes(search)
        );
    }
    
    return data;
});

// ============================================
// COMPUTED - TOTALES
// ============================================
const totalIngresos = computed(() => {
    return datosFiltrados.value.reduce((sum, item) => sum + (item.ingreso || 0), 0);
});

const totalEgresos = computed(() => {
    return datosFiltrados.value.reduce((sum, item) => sum + (item.egreso || 0), 0);
});

const balance = computed(() => {
    return totalIngresos.value - totalEgresos.value;
});

const balanceClass = computed(() => {
    if (balance.value > 0) return 'ingreso';
    if (balance.value < 0) return 'egreso';
    return 'neutro';
});

const totalDisponible = computed(() => {
    return reporteFondeadoras.value.reduce((sum, item) => sum + (item.saldo || 0), 0);
});

const saldoResultadosTotal = computed(() => {
    if (!cuentasResultados.value || cuentasResultados.value.length === 0) {
        return 0;
    }
    return cuentasResultados.value.reduce((sum, item) => {
        const saldo = parseFloat(item.saldo) || 0;
        return sum + saldo;
    }, 0);
});

const balanceCuenta = computed(() => {
    return totalIngresosCuenta.value - totalEgresosCuenta.value;
});

const balanceCuentaClass = computed(() => {
    if (balanceCuenta.value > 0) return 'ingreso';
    if (balanceCuenta.value < 0) return 'egreso';
    return 'neutro';
});

// ============================================
// FUNCIONES DE FORMATO
// ============================================
const formatNumber = (value) => {
    if (value === null || value === undefined || isNaN(value)) {
        return '0.00';
    }
    return Number(value).toLocaleString('es-MX', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
};

// ============================================
// FILTROS DE FECHAS
// ============================================
const limpiarFechas = () => {
    filtros.value.fecha_desde = '';
    filtros.value.fecha_hasta = '';
    cargarReporte();
};

const setFechaHoy = () => {
    const hoy = new Date().toISOString().split('T')[0];
    filtros.value.fecha_desde = hoy;
    filtros.value.fecha_hasta = hoy;
    cargarReporte();
};

// ============================================
// FILTROS DE TABLA
// ============================================
const aplicarFiltrosTabla = () => {
    clearTimeout(timeoutTabla);
    timeoutTabla = setTimeout(() => {}, 300);
};

const limpiarFiltrosTabla = () => {
    filtrosTabla.value = {
        nombre: '',
        persona: '',
        fondeo: ''
    };
};

// ============================================
// CAMBIAR VISTA
// ============================================
const cambiarVista = (vista) => {
    if (vistaActual.value === vista) return;
    vistaActual.value = vista;
    limpiarFiltrosTabla();
    cargarReporte();
};

// ============================================
// CAMBIAR EMPRESA
// ============================================
const cambiarEmpresa = () => {
    if (empresaSeleccionada.value) {
        localStorage.setItem('empresa_reportes', String(empresaSeleccionada.value));
        cargarReporte();
    }
};

// ============================================
// CARGAR REPORTE
// ============================================
const cargarReporte = async () => {
    if (!empresaSeleccionada.value) {
        Swal.fire({
            icon: 'warning',
            title: 'Sin empresa',
            text: 'Selecciona una empresa primero',
            confirmButtonColor: '#667eea'
        });
        return;
    }

    loading.value = true;
    cargado.value = false;

    try {
        const params = {
            empresa_id: empresaSeleccionada.value,
            vista: vistaActual.value,
            fecha_desde: filtros.value.fecha_desde,
            fecha_hasta: filtros.value.fecha_hasta
        };

        const response = await axios.get(route('reportes.movimientos'), { params });

        if (response.data.success) {
            reporteData.value = response.data.data || [];
            reporteFondeadoras.value = response.data.fondeadoras || [];
            cuentasResultados.value = response.data.cuentas_resultados || [];
            cargado.value = true;
        } else {
            throw new Error(response.data.message || 'Error al cargar el reporte');
        }

    } catch (error) {
        console.error('Error al cargar reporte:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error al cargar',
            text: error.response?.data?.message || error.message || 'Ocurrio un error inesperado',
            confirmButtonColor: '#dc2626'
        });
        reporteData.value = [];
        reporteFondeadoras.value = [];
        cargado.value = true;
    } finally {
        loading.value = false;
    }
};

// ============================================
// CLICK EN FILA - ABRIR DETALLE
// ============================================
const onRowClick = (record) => {
    if (vistaActual.value === 'por_cuenta' && record.id_cuenta) {
        abrirDetalleCuenta(record);
    }
};

// ============================================
// ABRIR DETALLE DE CUENTA
// ============================================
const abrirDetalleCuenta = async (record) => {
    if (!record.id_cuenta) return;
    
    cuentaSeleccionada.value = record;
    modalDetalleVisible.value = true;
    loadingDetalle.value = true;
    movimientosCuenta.value = [];
    totalIngresosCuenta.value = 0;
    totalEgresosCuenta.value = 0;

    try {
        const params = {
            empresa_id: empresaSeleccionada.value,
            id_cuenta: record.id_cuenta,
            fecha_desde: filtros.value.fecha_desde,
            fecha_hasta: filtros.value.fecha_hasta
        };

        const response = await axios.get(route('reportes.movimientos.cuenta'), { params });

        if (response.data.success) {
            movimientosCuenta.value = response.data.data || [];
            totalIngresosCuenta.value = response.data.total_ingresos || 0;
            totalEgresosCuenta.value = response.data.total_egresos || 0;
        } else {
            throw new Error(response.data.message || 'Error al cargar los movimientos');
        }
    } catch (error) {
        console.error('Error al cargar movimientos de cuenta:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.response?.data?.message || error.message || 'Error al cargar los movimientos',
            confirmButtonColor: '#dc2626'
        });
    } finally {
        loadingDetalle.value = false;
    }
};

// ============================================
// CLICK EN FILA DE RESULTADOS - ABRE DETALLE DE LA CUENTA
// ============================================
const onRowClickResultados = (record) => {
    // Buscar la cuenta en reporteData para obtener más datos
    const recordData = reporteData.value.find(r => r.id_cuenta === record.id_cuenta);
    if (recordData) {
        abrirDetalleCuenta(recordData);
    } else {
        // Si no está en reporteData, crear uno básico
        abrirDetalleCuenta({
            id_cuenta: record.id_cuenta,
            nombre: record.nombre_cuenta,
            codigo: record.codigo_cuenta || '---',
            persona: 'N/A',
            fondeo: 'N/A'
        });
    }
};

// ============================================
// ABRIR MODAL DE RESULTADOS
// ============================================
const abrirModalCuentasResultados = async () => {
    if (!empresaSeleccionada.value) {
        Swal.fire({
            icon: 'warning',
            title: 'Sin empresa',
            text: 'Selecciona una empresa primero',
            confirmButtonColor: '#667eea'
        });
        return;
    }

    // Si ya tenemos datos cargados, solo mostramos el modal
    if (cuentasResultados.value.length > 0) {
        modalResultadosVisible.value = true;
        return;
    }

    modalResultadosVisible.value = true;
    loadingResultados.value = true;

    try {
        const params = {
            empresa_id: empresaSeleccionada.value,
            vista: vistaActual.value,
            fecha_desde: filtros.value.fecha_desde,
            fecha_hasta: filtros.value.fecha_hasta
        };

        const response = await axios.get(route('reportes.movimientos'), { params });

        if (response.data.success) {
            cuentasResultados.value = response.data.cuentas_resultados || [];
        } else {
            throw new Error(response.data.message || 'Error al cargar las cuentas de resultados');
        }
    } catch (error) {
        console.error('Error al cargar cuentas de resultados:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.response?.data?.message || error.message || 'Error al cargar las cuentas de resultados',
            confirmButtonColor: '#dc2626'
        });
        cuentasResultados.value = [];
    } finally {
        loadingResultados.value = false;
    }
};

// ============================================
// VER POLIZA
// ============================================
const verPoliza = (idPoliza) => {
    if (idPoliza) {
        router.visit(route('movimientos.index', { poliza_id: idPoliza }));
    }
};

// ============================================
// EXPORTAR EXCEL
// ============================================
const exportarExcel = () => {
    if (!empresaSeleccionada.value) {
        Swal.fire({ icon: 'warning', title: 'Sin empresa', text: 'Selecciona una empresa primero' });
        return;
    }
    if (!reporteData.value.length) {
        Swal.fire({ icon: 'info', title: 'Sin datos', text: 'No hay datos para exportar' });
        return;
    }
    
    const params = new URLSearchParams();
    params.append('empresa_id', empresaSeleccionada.value);
    params.append('vista', vistaActual.value);
    params.append('fecha_desde', filtros.value.fecha_desde || '');
    params.append('fecha_hasta', filtros.value.fecha_hasta || '');
    
    window.open(route('reportes.export.excel') + '?' + params.toString(), '_blank');
};

// ============================================
// LIFECYCLE
// ============================================
onMounted(() => {
    if (!empresaSeleccionada.value) {
        const empresaGuardada = localStorage.getItem('empresa_reportes');
        if (empresaGuardada && props.empresas?.some(e => e.id == parseInt(empresaGuardada))) {
            empresaSeleccionada.value = parseInt(empresaGuardada);
        } else if (props.empresas && props.empresas.length > 0) {
            empresaSeleccionada.value = props.empresas[0].id;
        }
    }
    
    if (empresaSeleccionada.value) {
        cargarReporte();
    }
});
</script>

<style scoped>
/* ===== TODOS LOS ESTILOS PREMIUM ===== */
.empresa-selector-premium {
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #f0f2f5;
    padding: 10px 20px;
    margin-bottom: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.empresa-selector-content {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

@media (min-width: 640px) {
    .empresa-selector-content {
        flex-direction: row;
        align-items: center;
        gap: 12px;
    }
}

.empresa-selector-label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    font-weight: 600;
    color: #0f172a;
    white-space: nowrap;
}

.empresa-selector-field {
    flex: 1;
    min-width: 150px;
}

.empresa-select-native {
    width: 100%;
    padding: 6px 12px;
    border: 2px solid #e2e8f0;
    border-radius: 6px;
    font-size: 14px;
    background: #fafbfc;
    color: #0f172a;
    transition: all 0.3s ease;
    outline: none;
    height: 36px;
    appearance: auto;
    cursor: pointer;
}

.empresa-select-native:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    background: #ffffff;
}

.view-toggle-group {
    display: flex;
    align-items: center;
    gap: 0;
    border-radius: 8px;
    overflow: hidden;
    border: 2px solid #e2e8f0;
    background: #f8fafc;
}

.view-toggle-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 6px 16px;
    border: none;
    background: transparent;
    font-size: 12px;
    font-weight: 600;
    color: #64748b;
    cursor: pointer;
    transition: all 0.3s ease;
}

.view-toggle-btn:hover {
    color: #1a3a5c;
    background: #f1f5f9;
}

.view-toggle-btn.active {
    background: #1a3a5c;
    color: white;
}

.view-toggle-icon {
    width: 16px;
    height: 16px;
}

.view-toggle-btn:first-child {
    border-right: 1px solid #e2e8f0;
}

/* ===== BARRA SUPERIOR DE FILTROS ===== */
.filtros-superior-premium {
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #f0f2f5;
    padding: 12px 20px;
    margin-bottom: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.filtros-superior-content {
    display: flex;
    flex-direction: row;
    align-items: flex-end;
    gap: 12px;
    flex-wrap: wrap;
}

.fecha-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 120px;
}

.fecha-item :deep(.input-label) {
    font-size: 11px !important;
    font-weight: 600 !important;
    color: #475569 !important;
    margin-bottom: 0 !important;
}

.fecha-input-premium {
    padding: 6px 10px;
    border: 2px solid #d1d5db;
    border-radius: 6px;
    font-size: 12px;
    height: 36px;
    background: #ffffff;
    color: #0f172a;
    transition: all 0.3s ease;
    outline: none;
    width: 100%;
    min-width: 120px;
}

.fecha-input-premium:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.filtros-separator {
    width: 1px;
    height: 36px;
    background: #e2e8f0;
    flex-shrink: 0;
}

.fecha-actions {
    display: flex;
    flex-direction: row;
    align-items: flex-end;
    gap: 8px;
    min-width: auto;
}

.fecha-actions-export {
    display: flex;
    flex-direction: row;
    align-items: flex-end;
    gap: 8px;
    min-width: auto;
    margin-left: auto;
}

@media (max-width: 768px) {
    .fecha-actions-export {
        margin-left: 0;
        width: 100%;
        flex-wrap: wrap;
    }
}

.btn-hoy-premium {
    border-radius: 6px !important;
    background: linear-gradient(135deg, #1a3a5c, #2c5282) !important;
    border: none !important;
    color: white !important;
    height: 36px !important;
    font-weight: 600 !important;
    padding: 0 16px !important;
    transition: all 0.3s ease !important;
}

.btn-hoy-premium:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(26, 58, 92, 0.3) !important;
}

.btn-limpiar-fechas {
    border-radius: 6px !important;
    color: #64748b !important;
    border: 2px solid #d1d5db !important;
    height: 36px !important;
    font-weight: 600 !important;
    background: #ffffff !important;
    transition: all 0.3s ease !important;
}

.btn-limpiar-fechas:hover {
    color: #1a3a5c !important;
    border-color: #1a3a5c !important;
    transform: translateY(-2px);
}

.btn-export-excel-mini {
    border-radius: 6px !important;
    background: linear-gradient(135deg, #217346, #2e7d32) !important;
    border: none !important;
    color: white !important;
    height: 36px !important;
    font-weight: 600 !important;
    padding: 0 16px !important;
    transition: all 0.3s ease !important;
}

.btn-export-excel-mini:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(33, 115, 70, 0.3) !important;
}

.btn-resultados-mini {
    border-radius: 6px !important;
    background: linear-gradient(135deg, #7c3aed, #6d28d9) !important;
    border: none !important;
    color: white !important;
    height: 36px !important;
    font-weight: 600 !important;
    padding: 0 16px !important;
    transition: all 0.3s ease !important;
}

.btn-resultados-mini:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(124, 58, 237, 0.3) !important;
}

/* ===== REPORTE WRAPPER ===== */
.reporte-wrapper-premium {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid #f0f2f5;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    padding: 20px;
}

/* ===== TABLA PRINCIPAL ===== */
.tabla-principal-premium {
    margin-bottom: 32px;
}

.tabla-fondeadoras-premium {
    margin-top: 32px;
    padding-top: 32px;
    border-top: 2px solid #f1f5f9;
}

.table-scroll-container {
    overflow: hidden;
    border-radius: 8px;
    border: 1px solid #f1f5f9;
}

.table-scroll-container :deep(.ant-table-body) {
    max-height: 400px !important;
    overflow-y: auto !important;
}

.table-scroll-container :deep(.ant-table-body)::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}

.table-scroll-container :deep(.ant-table-body)::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 4px;
}

.table-scroll-container :deep(.ant-table-body)::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}

.table-scroll-container :deep(.ant-table-body)::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

.table-header-ultra {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-bottom: 16px;
}

@media (min-width: 640px) {
    .table-header-ultra {
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
    }
}

.table-header-left-ultra {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.table-header-right-ultra {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.table-title-premium {
    font-size: 16px;
    font-weight: 700;
    color: #0f172a;
}

.total-registros-premium {
    font-size: 13px;
    color: #64748b;
}

.total-registros-premium strong {
    color: #0f172a;
}

.total-disponible {
    color: #10b981 !important;
    font-size: 16px;
}

.filter-tag-ultra {
    border-radius: 30px !important;
    background: linear-gradient(135deg, #eff6ff, #dbeafe) !important;
    border: none !important;
    color: #1a3a5c !important;
    font-weight: 600 !important;
    padding: 4px 16px !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
}

.filter-dot-active {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #1a3a5c;
    display: inline-block;
    animation: pulse 2s infinite;
}

/* ===== ESTILOS DE LA TABLA ===== */
.reporte-table-ultra {
    width: 100%;
}

.reporte-table-ultra :deep(.ant-table-thead > tr > th) {
    background: linear-gradient(135deg, #1a3a5c 0%, #2c5282 100%) !important;
    font-weight: 700;
    color: #ffffff !important;
    border-bottom: none !important;
    padding: 12px 14px !important;
    font-size: 11px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    position: sticky;
    top: 0;
    z-index: 10;
    box-shadow: 0 2px 8px rgba(26, 58, 92, 0.15);
}

.reporte-table-ultra :deep(.ant-table-tbody > tr:hover) {
    background: linear-gradient(90deg, #f8faff, #f0f7ff) !important;
    box-shadow: inset 0 0 0 1px #667eea;
}

.reporte-table-ultra :deep(.ant-table-tbody > tr:nth-child(even)) {
    background: #fafbfc;
}

.reporte-table-ultra :deep(.ant-table-tbody > tr:nth-child(even):hover) {
    background: linear-gradient(90deg, #f8faff, #f0f7ff) !important;
}

.reporte-table-ultra :deep(.ant-table-cell) {
    padding: 10px 12px !important;
    border-bottom: 1px solid #f1f5f9;
    font-size: 13px;
}

.reporte-table-ultra :deep(.ant-table-cell-fix-left) {
    background: #ffffff;
    z-index: 5;
    box-shadow: 0 0 8px rgba(0, 0, 0, 0.04);
}

.clickable {
    cursor: pointer;
}

.clickable:hover {
    color: #667eea;
    text-decoration: underline;
}

/* ===== CELDAS ===== */
.nombre-text-ultra {
    font-size: 13px;
    font-weight: 600;
    color: #0f172a;
}

.codigo-text-ultra {
    font-size: 13px;
    font-weight: 500;
    color: #64748b;
    font-family: 'Courier New', monospace;
}

.persona-text-ultra {
    font-size: 13px;
    color: #475569;
}

.fondeo-text-ultra {
    font-size: 13px;
    color: #475569;
}

.fecha-text-ultra {
    font-size: 13px;
    color: #475569;
}

.folio-text-ultra {
    font-size: 13px;
    font-weight: 600;
    color: #1a3a5c;
}

.cuenta-text-ultra {
    font-size: 13px;
    color: #475569;
}

.monto-text-ultra {
    font-size: 14px;
    font-weight: 700;
    font-family: 'Courier New', monospace;
}

.monto-text-ultra.ingreso {
    color: #2563eb;
}

.monto-text-ultra.egreso {
    color: #dc2626;
}

.monto-text-ultra.neutro {
    color: #94a3b8;
}

/* ===== FILTROS INFERIOR ===== */
.filtros-inferior-tabla {
    margin-top: 16px;
    padding: 16px 20px;
    background: #f8fafc;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
}

.filtros-inferior-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 12px;
    align-items: end;
}

.filtro-inferior-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.filtro-inferior-label {
    font-size: 10px;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.filtro-inferior-input {
    padding: 6px 10px;
    border: 2px solid #d1d5db;
    border-radius: 6px;
    font-size: 12px;
    height: 32px;
    background: #ffffff;
    color: #0f172a;
    transition: all 0.3s ease;
    outline: none;
    width: 100%;
}

.filtro-inferior-input:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.filtro-inferior-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    min-width: 150px;
}

.btn-limpiar-filtros-inferior {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 16px;
    border: 2px solid #d1d5db;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    color: #64748b;
    background: #ffffff;
    cursor: pointer;
    transition: all 0.3s ease;
    height: 32px;
    white-space: nowrap;
}

.btn-limpiar-filtros-inferior:hover {
    color: #dc2626;
    border-color: #dc2626;
    background: #fef2f2;
    transform: translateY(-1px);
}

/* ===== TOTALES PRINCIPAL ===== */
.totales-principal-wrapper {
    margin-top: 16px;
}

.totales-principal-container {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
    background: linear-gradient(135deg, #f8fafc, #f1f5f9);
    border-radius: 12px;
    padding: 16px 24px;
    border: 1px solid #e2e8f0;
}

.total-item-principal {
    display: flex;
    flex-direction: column;
    gap: 2px;
    padding: 8px 16px;
    background: white;
    border-radius: 8px;
    border: 1px solid #f1f5f9;
    min-width: 140px;
    flex: 1;
}

.total-item-principal.total-balance {
    background: linear-gradient(135deg, #1a3a5c, #2c5282);
    border-color: #1a3a5c;
}

.total-item-principal.total-balance .total-label-principal {
    color: rgba(255, 255, 255, 0.7);
}

.total-item-principal.total-balance .total-value-principal {
    color: white;
}

.total-label-principal {
    font-size: 11px;
    font-weight: 600;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.total-value-principal {
    font-size: 20px;
    font-weight: 700;
    font-family: 'Courier New', monospace;
}

.total-value-principal.ingreso {
    color: #2563eb;
}

.total-value-principal.egreso {
    color: #dc2626;
}

.total-value-principal.neutro {
    color: #94a3b8;
}

/* ===== TOTALES FONDEADORAS ===== */
.totales-fondeadoras-wrapper {
    margin-top: 16px;
}

.totales-fondeadoras-container {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
    background: linear-gradient(135deg, #ecfdf5, #d1fae5);
    border-radius: 12px;
    padding: 16px 24px;
    border: 1px solid #a7f3d0;
}

.total-item-fondeadora {
    display: flex;
    flex-direction: column;
    gap: 2px;
    padding: 8px 16px;
    background: white;
    border-radius: 8px;
    border: 1px solid #d1fae5;
    min-width: 140px;
    flex: 1;
}

.total-label-fondeadora {
    font-size: 11px;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.total-value-fondeadora {
    font-size: 20px;
    font-weight: 700;
    font-family: 'Courier New', monospace;
}

.total-value-fondeadora.ingreso {
    color: #10b981;
}

/* ===== TOTALES RESULTADOS ===== */
.totales-resultados-wrapper {
    margin-top: 16px;
}

.totales-resultados-container {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
    background: linear-gradient(135deg, #ede9fe, #ddd6fe);
    border-radius: 12px;
    padding: 16px 24px;
    border: 1px solid #c4b5fd;
}

.total-item-resultados {
    display: flex;
    flex-direction: column;
    gap: 2px;
    padding: 8px 16px;
    background: white;
    border-radius: 8px;
    border: 1px solid #ddd6fe;
    min-width: 140px;
    flex: 1;
}

.total-label-resultados {
    font-size: 11px;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.total-value-resultados {
    font-size: 20px;
    font-weight: 700;
    font-family: 'Courier New', monospace;
}

.total-value-resultados.ingreso {
    color: #7c3aed;
}

.total-value-resultados.egreso {
    color: #dc2626;
}

/* ===== MODAL DETALLE ===== */
.modal-detalle-premium :deep(.ant-modal-header) {
    background: linear-gradient(135deg, #1a3a5c, #2c5282);
    border-radius: 8px 8px 0 0;
    padding: 16px 24px;
}

.modal-detalle-premium :deep(.ant-modal-title) {
    color: white;
    font-weight: 700;
}

.modal-detalle-premium :deep(.ant-modal-close) {
    color: white;
}

.modal-detalle-premium :deep(.ant-modal-close:hover) {
    color: #fca5a5;
}

.modal-resultados-premium :deep(.ant-modal-header) {
    background: linear-gradient(135deg, #7c3aed, #6d28d9);
    border-radius: 8px 8px 0 0;
    padding: 16px 24px;
}

.modal-resultados-premium :deep(.ant-modal-title) {
    color: white;
    font-weight: 700;
}

.modal-resultados-premium :deep(.ant-modal-close) {
    color: white;
}

.modal-resultados-premium :deep(.ant-modal-close:hover) {
    color: #fca5a5;
}

/* ===== TOTALES MODAL DETALLE ===== */
.totales-modal-detalle {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 16px;
    padding: 12px 16px;
    background: #f8fafc;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
}

.total-modal-item {
    display: flex;
    flex-direction: column;
    gap: 2px;
    padding: 4px 12px;
    background: white;
    border-radius: 6px;
    border: 1px solid #f1f5f9;
    min-width: 100px;
    flex: 1;
}

.total-modal-item.total-modal-balance {
    background: linear-gradient(135deg, #1a3a5c, #2c5282);
    border-color: #1a3a5c;
}

.total-modal-item.total-modal-balance .total-modal-label {
    color: rgba(255, 255, 255, 0.7);
}

.total-modal-item.total-modal-balance .total-modal-value {
    color: white;
}

.total-modal-label {
    font-size: 10px;
    font-weight: 600;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.total-modal-value {
    font-size: 18px;
    font-weight: 700;
    font-family: 'Courier New', monospace;
}

.total-modal-value.ingreso {
    color: #2563eb;
}

.total-modal-value.egreso {
    color: #dc2626;
}

.total-modal-value.neutro {
    color: #94a3b8;
}

/* ===== ANIMACIONES ===== */
@keyframes pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(1.2); }
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    .filtros-superior-content {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filtros-separator {
        display: none;
    }
    
    .fecha-actions-export {
        margin-left: 0;
        width: 100%;
        flex-wrap: wrap;
    }

    .btn-export-excel-mini,
    .btn-resultados-mini {
        flex: 1;
        justify-content: center;
    }

    .filtros-inferior-grid {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    }

    .reporte-wrapper-premium {
        padding: 12px;
    }

    .view-toggle-group {
        width: 100%;
    }

    .view-toggle-btn {
        flex: 1;
        justify-content: center;
    }

    .totales-principal-container,
    .totales-fondeadoras-container,
    .totales-resultados-container {
        flex-direction: column;
    }

    .total-item-principal,
    .total-item-fondeadora,
    .total-item-resultados {
        min-width: 100%;
    }

    .totales-modal-detalle {
        flex-direction: column;
    }

    .total-modal-item {
        min-width: 100%;
    }
}

@media (max-width: 480px) {
    .empresa-selector-actions {
        flex-direction: column;
        width: 100%;
    }

    .view-toggle-group {
        width: 100%;
    }

    .fecha-actions-export {
        flex-direction: column;
    }

    .btn-export-excel-mini,
    .btn-resultados-mini {
        width: 100%;
        justify-content: center;
    }

    .filtros-inferior-grid {
        grid-template-columns: 1fr;
    }

    .filtro-inferior-actions {
        justify-content: center;
    }
}
</style>