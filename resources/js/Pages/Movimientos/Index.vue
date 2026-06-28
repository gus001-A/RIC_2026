<template>
    <AppLayout title="RIC - Movimientos">
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
                                    :class="{ active: vistaActual === 'normal' }"
                                    @click="cambiarVista('normal')"
                                >
                                    <svg class="view-toggle-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                                    </svg>
                                    Polizas
                                </button>
                                <button 
                                    class="view-toggle-btn" 
                                    :class="{ active: vistaActual === 'diferidas' }"
                                    @click="cambiarVista('diferidas')"
                                >
                                    <svg class="view-toggle-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Diferidas
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BARRA SUPERIOR: FECHAS + EXCEL + PDF + CHECKBOX TODOS -->
                <div v-if="empresas.length > 0" class="filtros-superior-premium">
                    <div class="filtros-superior-content">
                        <!-- Fechas -->
                        <div class="fecha-item">
                            <InputLabel>Desde</InputLabel>
                            <input 
                                type="date" 
                                v-model="filtros.fecha_desde"
                                @change="aplicarFiltros"
                                :max="fechaActual"
                                class="fecha-input-premium"
                            />
                        </div>
                        <div class="fecha-item">
                            <InputLabel>Hasta</InputLabel>
                            <input 
                                type="date" 
                                v-model="filtros.fecha_hasta"
                                @change="aplicarFiltros"
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

                        <!-- Checkbox TODOS -->
                        <div class="fecha-item checkbox-todos-item">
                            <label class="checkbox-todos-label">
                                <input 
                                    type="checkbox" 
                                    v-model="mostrarTodos"
                                    @change="aplicarFiltros"
                                    class="checkbox-todos-input"
                                />
                                <span class="checkbox-todos-text">Mostrar todos</span>
                            </label>
                        </div>

                        <!-- Separador -->
                        <div class="filtros-separator"></div>

                        <!-- Botones Exportacion -->
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
                                class="btn-export-pdf-mini"
                                @click="exportarPdf"
                            >
                                <template #icon>
                                    <FilePdfOutlined />
                                </template>
                                PDF
                            </a-button>
                        </div>
                    </div>
                </div>

                <!-- Tabla Premium -->
                <div v-if="empresas.length > 0" class="table-wrapper-premium">
                    <!-- Header de la tabla -->
                    <div class="table-header-ultra">
                        <div class="table-header-left-ultra">
                            <a-tag v-if="filtrosActivos" color="blue" class="filter-tag-ultra">
                                <span class="filter-dot-active"></span>
                                Filtros activos
                            </a-tag>
                            <span v-if="vistaActual === 'diferidas' && movimientos.data.length > 0" class="total-pendiente-badge">
                                <span class="total-pendiente-label">Total pendiente:</span>
                                <span class="total-pendiente-value">${{ formatNumber(totalPendiente) }}</span>
                            </span>
                        </div>
                        <div class="table-header-right-ultra">
                            <div class="btn-group-actions">
                                <!-- ✅ NUEVA PÓLIZA - solo capturista, admin, auditor, super -->
                                <Link 
                                    v-if="permisos?.puede_crear" 
                                    :href="route('movimientos.create')" 
                                    class="btn-nueva-poliza"
                                >
                                    <PlusOutlined />
                                    Nueva Poliza
                                </Link>
                                
                                <!-- ✅ NÓMINA - solo capturista, admin, auditor, super -->
                                <Link 
                                    v-if="permisos?.puede_crear" 
                                    :href="route('movimientos.nomina.create')" 
                                    class="btn-nomina-poliza"
                                >
                                    <svg class="btn-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Nomina
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla con scroll fijo -->
                    <div class="table-scroll-container">
                        <a-table
                            :columns="columnasActuales"
                            :data-source="movimientos.data"
                            :pagination="false"
                            :loading="loading"
                            row-key="id_movimiento"
                            class="movimiento-table-ultra"
                            size="middle"
                            :scroll="{ x: 'max-content', y: 380 }"
                            sticky
                            @change="handleTableChange"
                            :row-class-name="getRowClassName"
                        >
                            <template #bodyCell="{ column, record }">
                                <!-- REFERENCIA (SOLO PARA VISTA NORMAL) -->
                                <template v-if="column.key === 'referencia'">
                                    <Link 
                                        v-if="permisos?.puede_editar" 
                                        :href="route('movimientos.edit', record.id_movimiento)" 
                                        class="referencia-link"
                                    >
                                        <span class="referencia-text-ultra">{{ record.referencia || '—' }}</span>
                                        <span v-if="record.referencia_adicional" class="referencia-extra-ultra">
                                            {{ record.referencia_adicional }}
                                        </span>
                                    </Link>
                                    <span v-else class="referencia-text-ultra">{{ record.referencia || '—' }}</span>
                                </template>

                                <!-- FECHA POLIZA (SOLO PARA VISTA NORMAL) -->
                                <template v-if="column.key === 'fecha_poliza'">
                                    <div class="fecha-usuario-cell">
                                        <span class="fecha-text-ultra">{{ formatFecha(record.fecha_poliza) }}</span>
                                        <span v-if="record.usuario" class="usuario-fecha-creacion">
                                            {{ record.usuario }} | {{ formatFechaHora(record.created_at) }}
                                        </span>
                                    </div>
                                </template>

                                <!-- ESTATUS (SOLO PARA VISTA NORMAL) -->
                                <template v-if="column.key === 'estatus'">
                                    <span class="estatus-badge" :class="getEstatusClass(record.estatus)">
                                        {{ record.estatus || '—' }}
                                    </span>
                                </template>

                                <!-- PERSONA (PARA AMBAS VISTAS) -->
                                <template v-if="column.key === 'persona'">
                                    <span class="persona-text-ultra">{{ record.persona || '—' }}</span>
                                </template>

                                <!-- CUENTA (SOLO PARA VISTA NORMAL) -->
                                <template v-if="column.key === 'cuenta'">
                                    <span class="cuenta-text-ultra">{{ record.cuenta || '—' }}</span>
                                </template>

                                <!-- CUENTA FONDEADORA (SOLO PARA VISTA NORMAL) -->
                                <template v-if="column.key === 'cuenta_fondeadora'">
                                    <span class="cuenta-text-ultra">{{ record.cuenta_fondeadora || '—' }}</span>
                                </template>

                                <!-- NOTA (PARA AMBAS VISTAS) -->
                                <template v-if="column.key === 'nota'">
                                    <span class="nota-text-ultra">{{ record.nota || '—' }}</span>
                                </template>

                                <!-- MONTO (PARA AMBAS VISTAS) -->
                                <template v-if="column.key === 'monto'">
                                    <span class="monto-text-ultra" :class="getMontoClass(record.monto)">
                                        {{ record.monto < 0 ? '-' : '' }}${{ formatNumber(Math.abs(record.monto)) }}
                                    </span>
                                </template>

                                <!-- ABONADO (SOLO PARA VISTA DIFERIDAS) -->
                                <template v-if="column.key === 'abonado'">
                                    <span class="monto-text-ultra abonado-text">
                                        ${{ formatNumber(record.abonado || 0) }}
                                    </span>
                                </template>

                                <!-- SALDO PENDIENTE (SOLO PARA VISTA DIFERIDAS) -->
                                <template v-if="column.key === 'saldo_pendiente'">
                                    <span class="monto-text-ultra" :class="getSaldoPendienteClass(record.saldo_pendiente)">
                                        ${{ formatNumber(record.saldo_pendiente || 0) }}
                                    </span>
                                </template>

                                <!-- VENCIMIENTO (SOLO PARA VISTA DIFERIDAS) -->
                                <template v-if="column.key === 'vencimiento'">
                                    <span class="fecha-text-ultra" :class="getVencimientoClass(record.fecha_vencimiento)">
                                        {{ formatFecha(record.fecha_vencimiento) }}
                                    </span>
                                    <span v-if="isVencido(record.fecha_vencimiento)" class="vencido-icon">!</span>
                                </template>

                                <!-- USUARIO (PARA AMBAS VISTAS) -->
                                <template v-if="column.key === 'usuario'">
                                    <div class="usuario-cell">
                                        <span class="usuario-text-ultra">{{ record.usuario || '—' }}</span>
                                        <span v-if="record.created_at" class="usuario-fecha-ultra">
                                            {{ formatFechaHora(record.created_at) }}
                                        </span>
                                    </div>
                                </template>

                                <!-- PDF FISCAL (SOLO PARA VISTA NORMAL) -->
                                <template v-if="column.key === 'pdf'">
                                    <div class="pdf-cell">
                                        <a-button 
                                            size="small" 
                                            type="link" 
                                            @click="verPdf(record)"
                                            :disabled="!record.tiene_pdf_fiscal"
                                            class="btn-pdf"
                                            :title="record.tiene_pdf_fiscal ? 'Abrir Comprobante Fiscal PDF' : 'Sin PDF Fiscal'"
                                        >
                                            <FilePdfOutlined :style="{ 
                                                color: getPdfColor(record),
                                                fontSize: '18px',
                                                transition: 'color 0.3s ease'
                                            }" />
                                        </a-button>
                                    </div>
                                </template>

                                <!-- ACCIONES (SOLO PARA VISTA DIFERIDAS) -->
                                <template v-if="column.key === 'acciones'">
                                    <div class="acciones-cell">
                                        <!-- ✅ LIQUIDAR - solo admin, auditor, super -->
                                        <button 
                                            v-if="permisos?.puede_autorizar && record.es_por_pagar && record.tipo_poliza === 'EGRESO'"
                                            class="btn-accion liquidar" 
                                            @click="accionLiquidar(record)"
                                            :disabled="record.estatus === 'LIQUIDADO' || record.saldo_pendiente === 0"
                                            title="Liquidar"
                                        >
                                            <svg class="btn-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            Liquidar
                                        </button>
                                        
                                        <!-- ✅ ABONAR - solo admin, auditor, super -->
                                        <button 
                                            v-if="permisos?.puede_autorizar"
                                            class="btn-accion abonar" 
                                            @click="accionAbonar(record)"
                                            :disabled="record.estatus === 'LIQUIDADO' || record.saldo_pendiente === 0"
                                            title="Abonar"
                                        >
                                            <svg class="btn-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                            </svg>
                                            Abonar
                                        </button>
                                        
                                        <!-- ✅ EDITAR - solo admin, auditor, super -->
                                        <button 
                                            v-if="permisos?.puede_editar"
                                            class="btn-accion editar" 
                                            @click="accionEditar(record)"
                                            title="Editar"
                                        >
                                            <svg class="btn-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                            Editar
                                        </button>
                                        
                                        <!-- ✅ VER (solo lectura) - LECTOR y CAPTURISTA pueden ver -->
                                        <button 
                                            v-if="!permisos?.puede_editar"
                                            class="btn-accion ver" 
                                            @click="accionVer(record)"
                                            title="Ver"
                                        >
                                            <svg class="btn-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            Ver
                                        </button>
                                    </div>
                                </template>
                            </template>
                        </a-table>
                    </div>

                    <!-- FILTROS EN LA PARTE INFERIOR DE LA TABLA -->
                    <div class="filtros-inferior-tabla">
                        <div class="filtros-inferior-grid">
                            <!-- Referencia (solo normal) -->
                            <div class="filtro-inferior-item" v-if="vistaActual === 'normal'">
                                <label class="filtro-inferior-label">Referencia</label>
                                <input 
                                    v-model="filtros.referencia"
                                    @input="aplicarFiltros"
                                    placeholder="Buscar..."
                                    class="filtro-inferior-input"
                                />
                            </div>

                            <!-- Estatus (solo normal) -->
                            <div class="filtro-inferior-item" v-if="vistaActual === 'normal'">
                                <label class="filtro-inferior-label">Estatus</label>
                                <select 
                                    v-model="filtros.estatus"
                                    @change="aplicarFiltros"
                                    class="filtro-inferior-select"
                                >
                                    <option value="">Todos</option>
                                    <option value="PENDIENTE">Pendiente</option>
                                    <option value="ABONADO">Abonado</option>
                                    <option value="LIQUIDADO">Liquidado</option>
                                </select>
                            </div>

                            <!-- Persona (ambas) -->
                            <div class="filtro-inferior-item">
                                <label class="filtro-inferior-label">Persona</label>
                                <input 
                                    v-model="filtros.persona"
                                    @input="aplicarFiltros"
                                    placeholder="Buscar..."
                                    class="filtro-inferior-input"
                                />
                            </div>

                            <!-- Cuenta (solo normal) -->
                            <div class="filtro-inferior-item" v-if="vistaActual === 'normal'">
                                <label class="filtro-inferior-label">Cuenta</label>
                                <input 
                                    v-model="filtros.cuenta"
                                    @input="aplicarFiltros"
                                    placeholder="Buscar..."
                                    class="filtro-inferior-input"
                                />
                            </div>

                            <!-- Cuenta Fondeadora (solo normal) -->
                            <div class="filtro-inferior-item" v-if="vistaActual === 'normal'">
                                <label class="filtro-inferior-label">Cta. Fondeo</label>
                                <input 
                                    v-model="filtros.cuenta_fondeadora"
                                    @input="aplicarFiltros"
                                    placeholder="Buscar..."
                                    class="filtro-inferior-input"
                                />
                            </div>

                            <!-- Nota (ambas) -->
                            <div class="filtro-inferior-item">
                                <label class="filtro-inferior-label">Nota</label>
                                <input 
                                    v-model="filtros.nota"
                                    @input="aplicarFiltros"
                                    placeholder="Buscar..."
                                    class="filtro-inferior-input"
                                />
                            </div>

                            <!-- Usuario (solo diferidas) -->
                            <div class="filtro-inferior-item" v-if="vistaActual === 'diferidas'">
                                <label class="filtro-inferior-label">Usuario</label>
                                <input 
                                    v-model="filtros.usuario"
                                    @input="aplicarFiltros"
                                    placeholder="Buscar..."
                                    class="filtro-inferior-input"
                                />
                            </div>

                            <!-- Boton limpiar filtros -->
                            <div class="filtro-inferior-item filtro-inferior-actions">
                                <button 
                                    v-if="filtrosActivos" 
                                    class="btn-limpiar-filtros-inferior"
                                    @click="limpiarFiltros"
                                >
                                    <CloseOutlined />
                                    Limpiar filtros
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- RESUMEN DE TOTALES - SOLO PARA VISTA NORMAL -->
                    <div v-if="vistaActual === 'normal' && movimientos.data && movimientos.data.length > 0" class="resumen-totales-wrapper">
                        <div class="resumen-totales-container-grande">
                            <div class="resumen-totales-header-grande">
                                <span class="resumen-totales-title-grande">Resumen de Totales</span>
                            </div>
                            <div class="resumen-totales-items-grande">
                                <div class="total-item-grande total-ingresos">
                                    <div class="total-icon-grande">
                                        <svg class="total-icon-svg-grande" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                        </svg>
                                    </div>
                                    <div class="total-info-grande">
                                        <span class="total-label-grande">Ingresos</span>
                                        <span class="total-value-grande ingresos-value">${{ formatNumber(totalIngresos) }}</span>
                                    </div>
                                </div>

                                <div class="total-item-grande total-egresos">
                                    <div class="total-icon-grande">
                                        <svg class="total-icon-svg-grande" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"/>
                                        </svg>
                                    </div>
                                    <div class="total-info-grande">
                                        <span class="total-label-grande">Egresos</span>
                                        <span class="total-value-grande egresos-value">-${{ formatNumber(Math.abs(totalEgresos)) }}</span>
                                    </div>
                                </div>

                                <div class="total-item-grande total-saldo-neto">
                                    <div class="total-icon-grande saldo-icon">
                                        <svg class="total-icon-svg-grande" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                                        </svg>
                                    </div>
                                    <div class="total-info-grande">
                                        <span class="total-label-grande">Saldo Neto</span>
                                        <span class="total-value-grande saldo-net-value">${{ formatNumber(saldoNeto) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Paginacion -->
                    <div class="pagination-ultra">
                        <span class="pagination-info-ultra">
                            Mostrando <span class="pagination-highlight-ultra">{{ movimientos.from || 0 }}</span> a 
                            <span class="pagination-highlight-ultra">{{ movimientos.to || 0 }}</span> de 
                            <span class="pagination-highlight-ultra">{{ movimientos.total || 0 }}</span> resultados
                        </span>
                        <Pagination :links="movimientos.links" />
                    </div>
                </div>

                <!-- Mensaje si no hay empresas -->
                <div v-else class="table-wrapper-premium">
                    <div class="text-center py-12">
                        <div class="text-6xl mb-4">🏢</div>
                        <h3 class="text-xl font-semibold text-gray-700 mb-2">No tienes empresas asignadas</h3>
                        <p class="text-gray-500">Contacta al administrador para que te asigne una empresa.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ✅ MODAL DE ALERTAS - Usando el componente que me diste -->
        <ModalAlert ref="modalAlert" />
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted, nextTick } from 'vue';
import Pagination from '@/Components/Pagination.vue';
import InputLabel from '@/Components/InputLabel.vue';
import ModalAlert from '@/Components/AlertModal.vue';
import Swal from 'sweetalert2';
import axios from 'axios';
import {
    PlusOutlined,
    CloseOutlined,
    ShopOutlined,
    FileExcelOutlined,
    FilePdfOutlined,
    CalendarOutlined,
} from '@ant-design/icons-vue';
import {
    Button as AButton,
    Table as ATable,
    Tag as ATag,
} from 'ant-design-vue';

// ============================================
// ✅ PERMISOS DESDE EL BACKEND
// ============================================
const page = usePage();
const permisos = computed(() => page.props.permisos || {});

const props = defineProps({
    movimientos: {
        type: Object,
        default: () => ({ data: [], from: 0, to: 0, total: 0, links: [] })
    },
    empresas: {
        type: Array,
        default: () => []
    },
    filtros: {
        type: Object,
        default: () => ({})
    },
    flash: {
        type: Object,
        default: () => ({})
    },
    empresa_seleccionada: {
        type: [Number, String],
        default: null
    },
    saldo_total: {
        type: [Number, null],
        default: null
    },
    vista: {
        type: String,
        default: 'normal'
    }
});

// ============================================
// REFS Y VARIABLES
// ============================================
const loading = ref(false);
const modalAlert = ref(null); // ✅ Referencia al componente ModalAlert
const empresaSeleccionada = ref(props.empresa_seleccionada || null);
const fechaActual = ref(new Date().toISOString().split('T')[0]);
const vistaActual = ref(props.vista || 'normal');
const mostrarTodos = ref(false);
let timeoutId = null;

// ============================================
// TOTALES
// ============================================
const totalPendiente = computed(() => {
    if (vistaActual.value !== 'diferidas' || !props.movimientos.data) return 0;
    return props.movimientos.data.reduce((sum, item) => {
        return sum + (item.saldo_pendiente || 0);
    }, 0);
});

const totalIngresos = computed(() => {
    if (vistaActual.value !== 'normal' || !props.movimientos.data || props.movimientos.data.length === 0) return 0;
    return props.movimientos.data
        .filter(item => item.monto > 0)
        .reduce((sum, item) => sum + Math.abs(item.monto || 0), 0);
});

const totalEgresos = computed(() => {
    if (vistaActual.value !== 'normal' || !props.movimientos.data || props.movimientos.data.length === 0) return 0;
    return props.movimientos.data
        .filter(item => item.monto < 0)
        .reduce((sum, item) => sum + Math.abs(item.monto || 0), 0);
});

const saldoNeto = computed(() => {
    return totalIngresos.value - totalEgresos.value;
});

// ============================================
// COLUMNAS - NORMAL
// ============================================
const columnasNormal = [
    {
        title: 'Referencia',
        key: 'referencia',
        width: '160px',
        fixed: 'left'
    },
    {
        title: 'Fecha Poliza',
        key: 'fecha_poliza',
        width: '160px',
        align: 'center',
        sorter: true
    },
    {
        title: 'Estatus',
        key: 'estatus',
        width: '120px',
        align: 'center'
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
        title: 'Cta. Fondeo',
        key: 'cuenta_fondeadora',
        width: '180px'
    },
    {
        title: 'Nota',
        key: 'nota',
        width: '200px'
    },
    {
        title: 'Monto',
        key: 'monto',
        width: '150px',
        align: 'right'
    },
    {
        title: 'PDF',
        key: 'pdf',
        width: '85px',
        align: 'center',
        fixed: 'right'
    }
];

// ============================================
// COLUMNAS - DIFERIDAS
// ============================================
const columnasDiferidas = [
    {
        title: 'Vencimiento',
        key: 'vencimiento',
        width: '130px',
        align: 'center',
        sorter: true
    },
    {
        title: 'Persona',
        key: 'persona',
        width: '180px'
    },
    {
        title: 'Nota',
        key: 'nota',
        width: '200px'
    },
    {
        title: 'Monto',
        key: 'monto',
        width: '130px',
        align: 'right'
    },
    {
        title: 'Abonado',
        key: 'abonado',
        width: '130px',
        align: 'right'
    },
    {
        title: 'Saldo Pendiente',
        key: 'saldo_pendiente',
        width: '150px',
        align: 'right'
    },
    {
        title: 'Usuario',
        key: 'usuario',
        width: '180px'
    },
    {
        title: 'Acciones',
        key: 'acciones',
        width: '280px',
        align: 'center',
        fixed: 'right'
    }
];

const columnasActuales = computed(() => {
    return vistaActual.value === 'diferidas' ? columnasDiferidas : columnasNormal;
});

// ============================================
// CLASE PARA LA FILA COMPLETA (ROJO SI VENCIDA)
// ============================================
const getRowClassName = (record) => {
    if (vistaActual.value !== 'diferidas') return '';
    
    if (record.fecha_vencimiento) {
        const hoy = new Date();
        hoy.setHours(0, 0, 0, 0);
        const fechaVenc = new Date(record.fecha_vencimiento);
        fechaVenc.setHours(0, 0, 0, 0);
        
        if (fechaVenc < hoy) {
            return 'row-vencida';
        }
    }
    
    return '';
};

// ============================================
// FUNCION PARA OBTENER COLOR DEL PDF
// ============================================
const getPdfColor = (record) => {
    if (record.es_fiscal && record.tiene_pdf_fiscal) {
        return '#10b981';
    } else if (record.es_fiscal && !record.tiene_pdf_fiscal) {
        return '#f59e0b';
    } else {
        return '#d1d5db';
    }
};

// ============================================
// FILTROS
// ============================================
const filtros = ref({
    fecha_desde: props.filtros?.fecha_desde || '',
    fecha_hasta: props.filtros?.fecha_hasta || '',
    referencia: props.filtros?.referencia || '',
    estatus: props.filtros?.estatus || '',
    persona: props.filtros?.persona || '',
    cuenta: props.filtros?.cuenta || '',
    cuenta_fondeadora: props.filtros?.cuenta_fondeadora || '',
    nota: props.filtros?.nota || '',
    usuario: props.filtros?.usuario || '',
    sort_by: props.filtros?.sort_by || 'fecha_poliza',
    sort_order: props.filtros?.sort_order || 'desc',
    vista: vistaActual.value,
    mostrar_todos: props.filtros?.mostrar_todos || false
});

// Sincronizar mostrarTodos con filtros
watch(mostrarTodos, (val) => {
    filtros.value.mostrar_todos = val;
});

const filtrosActivos = computed(() => {
    const { fecha_desde, fecha_hasta, vista, sort_by, sort_order, mostrar_todos, ...otros } = filtros.value;
    return Object.values(otros).some(v => v !== '' && v !== null && v !== undefined) ||
           fecha_desde || fecha_hasta || mostrar_todos;
});

const aplicarFiltros = () => {
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => {
        if (!empresaSeleccionada.value) return;
        loading.value = true;
        const params = { 
            empresa_id: empresaSeleccionada.value, 
            vista: vistaActual.value,
            mostrar_todos: mostrarTodos.value
        };
        for (const [key, value] of Object.entries(filtros.value)) {
            if (value !== '' && value !== null && value !== undefined && 
                key !== 'vista' && key !== 'mostrar_todos') {
                params[key] = value;
            }
        }
        router.get(route('movimientos.index'), params, {
            preserveState: true,
            replace: true,
            onFinish: () => { loading.value = false; }
        });
    }, 300);
};

const limpiarFiltros = () => {
    filtros.value = {
        fecha_desde: '',
        fecha_hasta: '',
        referencia: '',
        estatus: '',
        persona: '',
        cuenta: '',
        cuenta_fondeadora: '',
        nota: '',
        usuario: '',
        sort_by: vistaActual.value === 'diferidas' ? 'fecha_vencimiento' : 'fecha_poliza',
        sort_order: vistaActual.value === 'diferidas' ? 'asc' : 'desc',
        vista: vistaActual.value,
        mostrar_todos: false
    };
    mostrarTodos.value = false;
    aplicarFiltros();
};

const limpiarFechas = () => {
    filtros.value.fecha_desde = '';
    filtros.value.fecha_hasta = '';
    aplicarFiltros();
};

const setFechaHoy = () => {
    const hoy = new Date().toISOString().split('T')[0];
    filtros.value.fecha_desde = hoy;
    filtros.value.fecha_hasta = hoy;
    aplicarFiltros();
};

const cambiarVista = (vista) => {
    if (vistaActual.value === vista) return;
    vistaActual.value = vista;
    filtros.value.vista = vista;
    filtros.value.sort_by = vista === 'diferidas' ? 'fecha_vencimiento' : 'fecha_poliza';
    filtros.value.sort_order = vista === 'diferidas' ? 'asc' : 'desc';
    aplicarFiltros();
};

const handleTableChange = (pagination, filters, sorter) => {
    if (sorter && sorter.columnKey) {
        const map = {
            'vencimiento': 'fecha_vencimiento',
            'fecha_poliza': 'fecha_poliza'
        };
        filtros.value.sort_by = map[sorter.columnKey] || sorter.columnKey;
        filtros.value.sort_order = sorter.order === 'ascend' ? 'asc' : 'desc';
        aplicarFiltros();
    }
};

const cambiarEmpresa = () => {
    if (empresaSeleccionada.value) {
        localStorage.setItem('empresa_movimientos', String(empresaSeleccionada.value));
        loading.value = true;
        const params = { 
            empresa_id: empresaSeleccionada.value, 
            vista: vistaActual.value,
            mostrar_todos: mostrarTodos.value
        };
        for (const [key, value] of Object.entries(filtros.value)) {
            if (value !== '' && value !== null && value !== undefined && 
                key !== 'vista' && key !== 'mostrar_todos') {
                params[key] = value;
            }
        }
        router.get(route('movimientos.index'), params, {
            preserveState: true,
            replace: true,
            onFinish: () => { loading.value = false; }
        });
    } else {
        router.get(route('movimientos.index'), { vista: vistaActual.value }, {
            preserveState: true,
            replace: true
        });
    }
};

// ============================================
// CLASES Y ESTILOS
// ============================================
const getEstatusClass = (estatus) => {
    const classes = {
        'PENDIENTE': 'pendiente',
        'ABONADO': 'abonado',
        'LIQUIDADO': 'liquidado'
    };
    return classes[estatus] || 'pendiente';
};

const getMontoClass = (monto) => {
    if (monto > 0) return 'ingreso';
    if (monto < 0) return 'egreso';
    return 'neutro';
};

const getSaldoPendienteClass = (saldo) => {
    if (saldo > 0) return 'saldo-positivo';
    if (saldo < 0) return 'saldo-negativo';
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

const formatNumber = (value) => {
    if (value === null || value === undefined) return '0.00';
    return Number(value).toLocaleString('es-MX', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
};

const formatFecha = (fecha) => {
    if (!fecha) return '—';
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
// ✅ FUNCIÓN PARA MOSTRAR MODAL DE ALERTA
// ============================================
const mostrarModal = (type, title, message, duration = 4000) => {
    // ✅ Usar el componente ModalAlert
    if (modalAlert.value && modalAlert.value.show) {
        modalAlert.value.show({
            type: type,
            title: title,
            message: message,
            buttonText: type === 'error' ? 'Entendido' : 'Aceptar',
            duration: duration
        });
    } else {
        // ⚠️ Fallback con SweetAlert2 si el componente no está disponible
        console.warn('ModalAlert no disponible, usando SweetAlert2 fallback');
        const iconMap = {
            success: 'success',
            error: 'error',
            info: 'info',
            warning: 'warning'
        };
        Swal.fire({
            icon: iconMap[type] || 'info',
            title: title || 'Información',
            text: message,
            confirmButtonColor: '#1a3a5c',
            confirmButtonText: 'Aceptar',
            timer: duration || 4000,
            timerProgressBar: true
        });
    }
};

// ============================================
// ✅ PROCESAR FLASH MESSAGES
// ============================================
const procesarFlash = () => {
    if (!props.flash) return;
    
    console.log('📨 Procesando flash:', props.flash);
    
    const tipoMap = {
        success: { type: 'success', title: '¡Éxito!' },
        error: { type: 'error', title: 'Error' },
        updated: { type: 'success', title: '¡Actualizado!' },
        created: { type: 'success', title: '¡Creado!' },
        deleted: { type: 'success', title: '¡Eliminado!' },
        info: { type: 'info', title: 'Información' },
        warning: { type: 'warning', title: 'Advertencia' }
    };
    
    for (const [key, message] of Object.entries(props.flash)) {
        if (message && tipoMap[key]) {
            console.log(`✅ Mostrando alerta ${key}:`, message);
            mostrarModal(tipoMap[key].type, tipoMap[key].title, message);
            break; // Mostrar solo el primer mensaje
        }
    }
};

// ============================================
// ACCIONES DE NAVEGACION
// ============================================
const accionAbonar = (record) => {
    if (record.saldo_pendiente <= 0) {
        mostrarModal('warning', 'Ya liquidado', 'Esta poliza ya esta completamente liquidada.');
        return;
    }
    router.get(route('movimientos.abono', record.id_movimiento));
};

const accionEditar = (record) => {
    router.get(route('movimientos.edit', record.id_movimiento));
};

// ✅ ACCION: VER (para LECTOR y CAPTURISTA)
const accionVer = (record) => {
    router.get(route('movimientos.show', record.id_movimiento));
};

// ============================================
// ACCION: OBTENER CUENTAS FONDEADORAS
// ============================================
const obtenerCuentasFondeadoras = async () => {
    if (!empresaSeleccionada.value) return [];
    
    try {
        const response = await axios.get(route('movimientos.cuentas.fondeadoras'), {
            params: { empresa_id: empresaSeleccionada.value }
        });
        return response.data.data || [];
    } catch (error) {
        console.error('Error al obtener cuentas fondeadoras:', error);
        return [];
    }
};

// ============================================
// ACCION: VER PDF FISCAL
// ============================================
const verPdf = (record) => {
    if (record.es_fiscal && record.tiene_pdf_fiscal && record.pdf_url) {
        window.open(record.pdf_url, '_blank');
    } else if (record.es_fiscal && !record.tiene_pdf_fiscal) {
        mostrarModal('info', 'PDF no disponible', 'Esta poliza fiscal no tiene un PDF asociado. Puede agregarlo desde la edicion.');
    } else {
        mostrarModal('info', 'Sin PDF Fiscal', 'Esta poliza no es fiscal o no tiene un comprobante PDF asociado.');
    }
};

// ============================================
// ACCION: LIQUIDAR (con permisos)
// ============================================
const accionLiquidar = async (record) => {
    // ✅ Verificar permiso antes de liquidar
    if (!permisos.value?.puede_autorizar) {
        mostrarModal('error', 'Sin permisos', 'No tienes permisos para liquidar polizas. Contacta al administrador.');
        return;
    }

    if (record.saldo_pendiente <= 0) {
        mostrarModal('warning', 'Ya liquidado', 'Esta poliza ya esta completamente liquidada.');
        return;
    }

    if (record.tipo_poliza === 'INGRESO') {
        const result = await Swal.fire({
            title: 'Liquidar Poliza de INGRESO',
            html: `
                <div style="text-align: left; padding: 5px 0;">
                    <p style="font-size: 14px; color: #374151; margin-bottom: 16px;">
                        Estas a punto de <strong style="color: #10b981;">liquidar</strong> una poliza de <strong>INGRESO</strong>.
                    </p>
                    <div style="background: #f8fafc; border-radius: 10px; padding: 14px; border: 1px solid #e2e8f0; margin-bottom: 16px;">
                        <div style="display: flex; justify-content: space-between; padding: 4px 0; border-bottom: 1px dashed #e2e8f0;">
                            <span style="font-weight: 600; color: #64748b;">Referencia:</span>
                            <span style="font-weight: 700; color: #0f172a;">${record.referencia || 'S/N'}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 4px 0; border-bottom: 1px dashed #e2e8f0;">
                            <span style="font-weight: 600; color: #64748b;">Persona:</span>
                            <span style="font-weight: 700; color: #0f172a;">${record.persona || 'N/A'}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 4px 0;">
                            <span style="font-weight: 600; color: #64748b;">Saldo pendiente:</span>
                            <span style="font-weight: 700; color: #10b981; font-size: 18px;">$${formatNumber(record.saldo_pendiente)}</span>
                        </div>
                    </div>
                    <div style="background: #dbeafe; border-radius: 8px; padding: 12px 16px; border-left: 4px solid #2563eb;">
                        <span style="color: #1e40af; font-size: 13px;">
                            El saldo se abonara a la cuenta y la poliza cambiara a estatus <strong>"LIQUIDADO"</strong>.
                            ${record.cuenta ? `<br>Cuenta destino: <strong>${record.cuenta}</strong>` : ''}
                        </span>
                    </div>
                </div>
            `,
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Liquidar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        });

        if (result.isConfirmed) {
            Swal.fire({
                title: 'Procesando liquidacion...',
                text: 'Por favor espera',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            try {
                const response = await axios.post(route('movimientos.liquidar.ingreso'), {
                    id_movimiento: record.id_movimiento,
                    referencia: 'LIQUIDACION INGRESO'
                });

                if (response.data.success) {
                    await Swal.fire({
                        title: 'Poliza liquidada!',
                        html: `
                            <div style="padding: 10px 0;">
                                <p style="font-size: 14px; color: #374151;">
                                    La poliza de ingreso <strong>${record.referencia || 'S/N'}</strong> ha sido liquidada exitosamente.
                                </p>
                                <div style="background: #dcfce7; border-radius: 8px; padding: 12px; margin-top: 12px; border: 1px solid #86efac;">
                                    <span style="color: #166534; font-weight: 600;">
                                        Saldo liquidado: $${formatNumber(record.saldo_pendiente)}
                                    </span>
                                    <br>
                                    <span style="color: #166534; font-size: 12px;">
                                        Cuenta: ${response.data.data.cuenta || 'N/A'}
                                    </span>
                                </div>
                            </div>
                        `,
                        icon: 'success',
                        confirmButtonColor: '#10b981',
                        confirmButtonText: 'Aceptar',
                        timer: 5000,
                        timerProgressBar: true
                    });
                    aplicarFiltros();
                } else {
                    throw new Error(response.data.message || 'Error al liquidar');
                }
            } catch (error) {
                mostrarModal('error', 'Error al liquidar', error.response?.data?.message || error.message || 'Ocurrio un error inesperado');
            }
        }
        return;
    }

    if (record.tipo_poliza === 'EGRESO') {
        const cuentasFondeadoras = await obtenerCuentasFondeadoras();
        
        if (!cuentasFondeadoras || cuentasFondeadoras.length === 0) {
            mostrarModal('error', 'Sin cuentas fondeadoras', 'No hay cuentas fondeadoras disponibles para esta empresa.');
            return;
        }

        const optionsHtml = cuentasFondeadoras.map(cuenta => 
            `<option value="${cuenta.id_cuenta}">${cuenta.nombre_cuenta} (Saldo: $${formatNumber(cuenta.saldo || 0)})</option>`
        ).join('');

        const { value: idCuentaFondeadora } = await Swal.fire({
            title: 'Liquidar Poliza de EGRESO',
            html: `
                <div style="text-align: left; padding: 5px 0;">
                    <p style="font-size: 14px; color: #374151; margin-bottom: 16px;">
                        Estas a punto de <strong style="color: #dc2626;">liquidar</strong> una poliza de <strong>EGRESO</strong>:
                    </p>
                    <div style="background: #f8fafc; border-radius: 10px; padding: 14px; border: 1px solid #e2e8f0; margin-bottom: 16px;">
                        <div style="display: flex; justify-content: space-between; padding: 4px 0; border-bottom: 1px dashed #e2e8f0;">
                            <span style="font-weight: 600; color: #64748b;">Referencia:</span>
                            <span style="font-weight: 700; color: #0f172a;">${record.referencia || 'S/N'}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 4px 0; border-bottom: 1px dashed #e2e8f0;">
                            <span style="font-weight: 600; color: #64748b;">Persona:</span>
                            <span style="font-weight: 700; color: #0f172a;">${record.persona || 'N/A'}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 4px 0;">
                            <span style="font-weight: 600; color: #64748b;">Saldo pendiente:</span>
                            <span style="font-weight: 700; color: #dc2626; font-size: 18px;">$${formatNumber(record.saldo_pendiente)}</span>
                        </div>
                    </div>
                    <div style="margin-bottom: 16px;">
                        <label style="font-weight: 600; color: #374151; display: block; margin-bottom: 6px; font-size: 13px;">
                            Selecciona la cuenta fondeadora:
                        </label>
                        <select id="cuentaFondeadoraSelect" style="width: 100%; padding: 10px 12px; border: 2px solid #d1d5db; border-radius: 8px; font-size: 14px; background: white; cursor: pointer;">
                            <option value="">Seleccione una cuenta...</option>
                            ${optionsHtml}
                        </select>
                        <div id="saldoInfo" style="margin-top: 8px; font-size: 13px; color: #64748b; display: none;">
                            Saldo disponible: <span id="saldoDisponible" style="font-weight: 700; color: #10b981;">$0.00</span>
                        </div>
                    </div>
                    <div style="background: #fef3c7; border-radius: 8px; padding: 12px 16px; border-left: 4px solid #f59e0b;">
                        <span style="color: #92400e; font-size: 13px;">
                            Se descontara de la cuenta fondeadora y la poliza cambiara a estatus <strong>"LIQUIDADO"</strong>.
                        </span>
                    </div>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Liquidar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true,
            didOpen: () => {
                const select = document.getElementById('cuentaFondeadoraSelect');
                const saldoInfo = document.getElementById('saldoInfo');
                
                select.addEventListener('change', (e) => {
                    const idCuenta = e.target.value;
                    if (idCuenta) {
                        const cuenta = cuentasFondeadoras.find(c => c.id_cuenta == idCuenta);
                        if (cuenta) {
                            saldoInfo.style.display = 'block';
                            if ((cuenta.saldo || 0) < record.saldo_pendiente) {
                                saldoInfo.innerHTML = `
                                    Saldo insuficiente: <span style="font-weight: 700; color: #dc2626;">$${formatNumber(cuenta.saldo || 0)}</span> 
                                    (Necesario: $${formatNumber(record.saldo_pendiente)})
                                `;
                            } else {
                                saldoInfo.innerHTML = `
                                    Saldo disponible: <span style="font-weight: 700; color: #10b981;">$${formatNumber(cuenta.saldo || 0)}</span>
                                `;
                            }
                        }
                    } else {
                        saldoInfo.style.display = 'none';
                    }
                });
            },
            preConfirm: () => {
                const select = document.getElementById('cuentaFondeadoraSelect');
                const idCuenta = select.value;
                if (!idCuenta) {
                    Swal.showValidationMessage('Debes seleccionar una cuenta fondeadora');
                    return false;
                }
                const cuenta = cuentasFondeadoras.find(c => c.id_cuenta == idCuenta);
                if (!cuenta) {
                    Swal.showValidationMessage('Cuenta no valida');
                    return false;
                }
                if ((cuenta.saldo || 0) < record.saldo_pendiente) {
                    Swal.showValidationMessage(`Saldo insuficiente. Disponible: $${formatNumber(cuenta.saldo || 0)}, Necesario: $${formatNumber(record.saldo_pendiente)}`);
                    return false;
                }
                return idCuenta;
            }
        });

        if (idCuentaFondeadora) {
            Swal.fire({
                title: 'Procesando liquidacion...',
                text: 'Por favor espera',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            try {
                const response = await axios.post(route('movimientos.liquidar'), {
                    id_movimiento: record.id_movimiento,
                    id_cuenta_fondeadora: idCuentaFondeadora,
                    referencia: 'LIQUIDACION AUTOMATICA'
                });

                if (response.data.success) {
                    await Swal.fire({
                        title: 'Poliza liquidada!',
                        html: `
                            <div style="padding: 10px 0;">
                                <p style="font-size: 14px; color: #374151;">
                                    La poliza <strong>${record.referencia || 'S/N'}</strong> ha sido liquidada exitosamente.
                                </p>
                                <div style="background: #dcfce7; border-radius: 8px; padding: 12px; margin-top: 12px; border: 1px solid #86efac;">
                                    <span style="color: #166534; font-weight: 600;">
                                        Saldo liquidado: $${formatNumber(record.saldo_pendiente)}
                                    </span>
                                    <br>
                                    <span style="color: #166534; font-size: 12px;">
                                        Cuenta fondeadora: ${response.data.data.cuenta_fondeadora || 'N/A'}
                                    </span>
                                    <br>
                                    <span style="color: #166534; font-size: 12px;">
                                        Nuevo saldo de la cuenta: $${formatNumber(response.data.data.nuevo_saldo_cuenta || 0)}
                                    </span>
                                </div>
                            </div>
                        `,
                        icon: 'success',
                        confirmButtonColor: '#10b981',
                        confirmButtonText: 'Aceptar',
                        timer: 5000,
                        timerProgressBar: true
                    });
                    aplicarFiltros();
                } else {
                    throw new Error(response.data.message || 'Error al liquidar');
                }
            } catch (error) {
                mostrarModal('error', 'Error al liquidar', error.response?.data?.message || error.message || 'Ocurrio un error inesperado');
            }
        }
        return;
    }

    mostrarModal('info', 'Tipo de poliza no soportado', 'Solo se pueden liquidar polizas de INGRESO o EGRESO.');
};

// ============================================
// OTRAS ACCIONES
// ============================================
const exportarExcel = () => {
    if (!empresaSeleccionada.value) {
        mostrarModal('warning', 'Sin empresa', 'Selecciona una empresa primero');
        return;
    }
    const params = new URLSearchParams();
    params.append('empresa_id', empresaSeleccionada.value);
    params.append('vista', vistaActual.value);
    params.append('mostrar_todos', mostrarTodos.value);
    for (const [key, value] of Object.entries(filtros.value)) {
        if (value !== '' && value !== null && value !== undefined && 
            key !== 'vista' && key !== 'mostrar_todos') {
            params.append(key, value);
        }
    }
    window.open(route('movimientos.export.excel') + '?' + params.toString(), '_blank');
};

const exportarPdf = () => {
    if (!empresaSeleccionada.value) {
        mostrarModal('warning', 'Sin empresa', 'Selecciona una empresa primero');
        return;
    }
    const params = new URLSearchParams();
    params.append('empresa_id', empresaSeleccionada.value);
    params.append('vista', vistaActual.value);
    params.append('mostrar_todos', mostrarTodos.value);
    for (const [key, value] of Object.entries(filtros.value)) {
        if (value !== '' && value !== null && value !== undefined && 
            key !== 'vista' && key !== 'mostrar_todos') {
            params.append(key, value);
        }
    }
    window.open(route('movimientos.export.pdf') + '?' + params.toString(), '_blank');
};

// ============================================
// ✅ WATCH PARA FLASH MESSAGES
// ============================================
watch(() => props.flash, (newFlash) => {
    if (newFlash && Object.keys(newFlash).length > 0) {
        console.log('🔄 Detectado cambio en flash:', newFlash);
        nextTick(() => procesarFlash());
    }
}, { deep: true, immediate: true });

// ============================================
// ✅ LIFECYCLE
// ============================================
onMounted(() => {
    console.log('🚀 Componente montado');
    console.log('📨 Flash inicial:', props.flash);
    
    if (!empresaSeleccionada.value) {
        const empresaGuardada = localStorage.getItem('empresa_movimientos');
        if (empresaGuardada && props.empresas?.some(e => e.id == parseInt(empresaGuardada))) {
            empresaSeleccionada.value = parseInt(empresaGuardada);
        } else if (props.empresas && props.empresas.length > 0) {
            empresaSeleccionada.value = props.empresas[0].id;
        }
    }
    
    if (props.filtros?.mostrar_todos) {
        mostrarTodos.value = true;
    }
    
    if (empresaSeleccionada.value && (!props.movimientos?.data || props.movimientos.data.length === 0)) {
        cambiarEmpresa();
    }
    
    // ✅ PROCESAR FLASH AL MONTAR
    if (props.flash && Object.keys(props.flash).length > 0) {
        nextTick(() => procesarFlash());
    }
});
</script>

<style scoped>
/* ===== TODOS LOS ESTILOS PREMIUM ===== */
/* ... (todos los estilos que ya tenías) ... */
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

.btn-export-pdf-mini {
    border-radius: 6px !important;
    background: linear-gradient(135deg, #dc2626, #b91c1c) !important;
    border: none !important;
    color: white !important;
    height: 36px !important;
    font-weight: 600 !important;
    padding: 0 16px !important;
    transition: all 0.3s ease !important;
}

.btn-export-pdf-mini:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3) !important;
}

/* Checkbox TODOS */
.checkbox-todos-item {
    min-width: auto;
    justify-content: center;
}

.checkbox-todos-label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    padding: 4px 8px;
    border-radius: 6px;
    transition: all 0.3s ease;
    height: 36px;
}

.checkbox-todos-label:hover {
    background: #f1f5f9;
}

.checkbox-todos-input {
    width: 18px;
    height: 18px;
    cursor: pointer;
    accent-color: #1a3a5c;
}

.checkbox-todos-text {
    font-size: 13px;
    font-weight: 600;
    color: #0f172a;
    white-space: nowrap;
}

/* ===== TABLA ===== */
.table-wrapper-premium {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid #f0f2f5;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    padding: 20px;
}

.table-scroll-container {
    overflow: hidden;
    border-radius: 8px;
    border: 1px solid #f1f5f9;
}

.table-scroll-container :deep(.ant-table-body) {
    max-height: 380px !important;
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

.btn-group-actions {
    display: flex;
    gap: 10px;
    align-items: center;
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

.total-pendiente-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 4px 16px;
    background: linear-gradient(135deg, #dcfce7, #bbf7d0);
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    color: #166534;
}

.total-pendiente-value {
    font-weight: 700;
    font-size: 14px;
    color: #166534;
}

.btn-nueva-poliza {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 0 20px;
    height: 36px;
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.2);
}

.btn-nueva-poliza:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(16, 185, 129, 0.4);
    color: white;
}

.btn-nomina-poliza {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 0 20px;
    height: 36px;
    background: linear-gradient(135deg, #8b5cf6, #7c3aed);
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    box-shadow: 0 2px 8px rgba(139, 92, 246, 0.2);
}

.btn-nomina-poliza:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(139, 92, 246, 0.4);
    color: white;
}

.btn-icon-sm {
    width: 16px;
    height: 16px;
}

/* ===== ESTILOS DE LA TABLA ===== */
.movimiento-table-ultra {
    width: 100%;
}

.movimiento-table-ultra :deep(.ant-table-thead > tr > th) {
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

.movimiento-table-ultra :deep(.ant-table-tbody > tr) {
    transition: all 0.2s ease;
}

.movimiento-table-ultra :deep(.ant-table-tbody > tr.row-vencida) {
    background: linear-gradient(90deg, #fef2f2, #fecaca) !important;
    border-left: 4px solid #dc2626 !important;
}

.movimiento-table-ultra :deep(.ant-table-tbody > tr.row-vencida:hover) {
    background: linear-gradient(90deg, #fecaca, #fca5a5) !important;
    box-shadow: inset 0 0 0 1px #dc2626 !important;
}

.movimiento-table-ultra :deep(.ant-table-tbody > tr.row-vencida td) {
    color: #991b1b !important;
    font-weight: 600 !important;
}

.movimiento-table-ultra :deep(.ant-table-tbody > tr:not(.row-vencida):hover) {
    background: linear-gradient(90deg, #f8faff, #f0f7ff) !important;
    box-shadow: inset 0 0 0 1px #667eea;
}

.movimiento-table-ultra :deep(.ant-table-tbody > tr:nth-child(even)) {
    background: #fafbfc;
}

.movimiento-table-ultra :deep(.ant-table-tbody > tr:nth-child(even):not(.row-vencida):hover) {
    background: linear-gradient(90deg, #f8faff, #f0f7ff) !important;
}

.movimiento-table-ultra :deep(.ant-table-cell) {
    padding: 10px 12px !important;
    border-bottom: 1px solid #f1f5f9;
    font-size: 13px;
}

.movimiento-table-ultra :deep(.ant-table-cell-fix-left),
.movimiento-table-ultra :deep(.ant-table-cell-fix-right) {
    background: #ffffff;
    z-index: 5;
    box-shadow: 0 0 8px rgba(0, 0, 0, 0.04);
}

.movimiento-table-ultra :deep(.ant-table-tbody > tr.row-vencida .ant-table-cell-fix-left),
.movimiento-table-ultra :deep(.ant-table-tbody > tr.row-vencida .ant-table-cell-fix-right) {
    background: linear-gradient(90deg, #fef2f2, #fecaca) !important;
}

.movimiento-table-ultra :deep(.ant-table-tbody > tr.row-vencida:hover .ant-table-cell-fix-left),
.movimiento-table-ultra :deep(.ant-table-tbody > tr.row-vencida:hover .ant-table-cell-fix-right) {
    background: linear-gradient(90deg, #fecaca, #fca5a5) !important;
}

/* ===== CELDA DE FECHA CON USUARIO ===== */
.fecha-usuario-cell {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
}

.fecha-text-ultra {
    font-size: 13px;
    font-weight: 500;
    color: #475569;
}

.usuario-fecha-creacion {
    font-size: 9px;
    color: #94a3b8;
    font-weight: 400;
    background: #f1f5f9;
    padding: 1px 8px;
    border-radius: 4px;
    white-space: nowrap;
}

.fecha-text-ultra.vencido {
    color: #dc2626;
    font-weight: 700;
}

.fecha-text-ultra.vigente {
    color: #10b981;
    font-weight: 600;
}

.vencido-icon {
    font-size: 14px;
    margin-left: 4px;
}

.estatus-badge {
    display: inline-block;
    padding: 3px 12px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.estatus-badge.pendiente {
    background: #fef3c7;
    color: #92400e;
}

.estatus-badge.abonado {
    background: #dbeafe;
    color: #1e40af;
}

.estatus-badge.liquidado {
    background: #dcfce7;
    color: #166534;
}

.persona-text-ultra {
    font-size: 13px;
    color: #0f172a;
    font-weight: 500;
}

.cuenta-text-ultra {
    font-size: 13px;
    color: #475569;
}

.nota-text-ultra {
    font-size: 13px;
    color: #64748b;
    font-style: italic;
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

.monto-text-ultra.abonado-text {
    color: #2563eb;
}

.monto-text-ultra.saldo-positivo {
    color: #dc2626;
    font-weight: 700;
}

.monto-text-ultra.saldo-negativo {
    color: #2563eb;
}

.monto-text-ultra.saldo-cero {
    color: #10b981;
}

.usuario-cell {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.usuario-text-ultra {
    font-size: 13px;
    font-weight: 600;
    color: #0f172a;
}

.usuario-fecha-ultra {
    font-size: 10px;
    color: #94a3b8;
}

/* ===== PDF CELL ===== */
.pdf-cell {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
}

.btn-pdf {
    font-size: 16px !important;
    padding: 0 4px !important;
    height: 28px !important;
    width: 28px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    transition: all 0.3s ease !important;
}

.btn-pdf:hover:not(:disabled) {
    transform: scale(1.15);
    background: rgba(16, 185, 129, 0.05) !important;
}

.btn-pdf:disabled {
    cursor: not-allowed;
    transform: none !important;
}

.btn-pdf:disabled:hover {
    background: transparent !important;
    transform: none !important;
}

/* ===== ACCIONES ===== */
.acciones-cell {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    flex-wrap: nowrap;
}

.btn-accion {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    padding: 3px 10px;
    border: none;
    border-radius: 5px;
    font-size: 9px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s ease;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    white-space: nowrap;
    min-width: 38px;
    text-align: center;
    justify-content: center;
    height: 24px;
}

.btn-icon-sm {
    width: 12px;
    height: 12px;
}

.btn-accion.liquidar {
    background: #dcfce7;
    color: #166534;
}

.btn-accion.liquidar:hover:not(:disabled) {
    background: #bbf7d0;
    transform: scale(1.05);
    box-shadow: 0 2px 8px rgba(22, 101, 52, 0.15);
}

.btn-accion.abonar {
    background: #dbeafe;
    color: #1e40af;
}

.btn-accion.abonar:hover:not(:disabled) {
    background: #bfdbfe;
    transform: scale(1.05);
    box-shadow: 0 2px 8px rgba(30, 64, 175, 0.15);
}

.btn-accion.editar {
    background: #fef3c7;
    color: #92400e;
}

.btn-accion.editar:hover {
    background: #fde68a;
    transform: scale(1.05);
    box-shadow: 0 2px 8px rgba(146, 64, 14, 0.15);
}

.btn-accion.ver {
    background: #e0e7ff;
    color: #3730a3;
}

.btn-accion.ver:hover {
    background: #c7d2fe;
    transform: scale(1.05);
    box-shadow: 0 2px 8px rgba(55, 48, 163, 0.15);
}

.btn-accion:disabled {
    opacity: 0.4;
    cursor: not-allowed;
    transform: none !important;
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
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
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

.filtro-inferior-input::placeholder {
    color: #94a3b8;
}

.filtro-inferior-select {
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
    cursor: pointer;
}

.filtro-inferior-select:focus {
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

/* ===== RESUMEN DE TOTALES ===== */
.resumen-totales-wrapper {
    margin-top: 20px;
    display: flex;
    justify-content: flex-end;
}

.resumen-totales-container-grande {
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    border-radius: 16px;
    border: 2px solid #e2e8f0;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
    padding: 20px 28px;
    display: flex;
    align-items: center;
    gap: 28px;
    flex-wrap: wrap;
    min-width: 400px;
    width: 100%;
    justify-content: center;
    transition: all 0.3s ease;
}

.resumen-totales-container-grande:hover {
    box-shadow: 0 6px 24px rgba(0, 0, 0, 0.08);
    border-color: #cbd5e1;
}

.resumen-totales-header-grande {
    display: flex;
    align-items: center;
    gap: 10px;
    padding-right: 20px;
    border-right: 2px solid #e2e8f0;
}

.resumen-totales-title-grande {
    font-size: 16px;
    font-weight: 700;
    color: #0f172a;
    letter-spacing: 0.3px;
    white-space: nowrap;
}

.resumen-totales-items-grande {
    display: flex;
    align-items: center;
    gap: 20px;
    flex: 1;
    flex-wrap: wrap;
}

.total-item-grande {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 8px 16px;
    transition: all 0.3s ease;
    border-radius: 10px;
    background: #ffffff;
    border: 1px solid #f1f5f9;
    min-width: 140px;
    flex: 1;
}

.total-item-grande:hover {
    background: #f8fafc;
    border-color: #e2e8f0;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
}

.total-icon-grande {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.total-ingresos .total-icon-grande {
    background: #eff6ff;
}

.total-egresos .total-icon-grande {
    background: #fef2f2;
}

.total-saldo-neto .total-icon-grande {
    background: #f0fdf4;
}

.total-icon-svg-grande {
    width: 20px;
    height: 20px;
}

.total-ingresos .total-icon-svg-grande {
    color: #2563eb;
}

.total-egresos .total-icon-svg-grande {
    color: #dc2626;
}

.total-saldo-neto .total-icon-svg-grande {
    color: #10b981;
}

.total-info-grande {
    display: flex;
    flex-direction: column;
    gap: 2px;
    min-width: 0;
}

.total-label-grande {
    font-size: 11px;
    font-weight: 600;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.total-value-grande {
    font-size: 18px;
    font-weight: 700;
    font-family: 'Courier New', monospace;
    line-height: 1.2;
}

.ingresos-value {
    color: #2563eb;
}

.egresos-value {
    color: #dc2626;
}

.saldo-net-value {
    color: #10b981;
}

/* ===== PAGINACION ===== */
.pagination-ultra {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-top: 16px;
    padding-top: 16px;
    border-top: 2px solid #f1f5f9;
}

@media (min-width: 640px) {
    .pagination-ultra {
        flex-direction: row;
    }
}

.pagination-info-ultra {
    font-size: 13px;
    color: #64748b;
    font-weight: 500;
}

.pagination-highlight-ultra {
    font-weight: 700;
    color: #1a3a5c;
    padding: 2px 8px;
    background: #f1f4f9;
    border-radius: 4px;
}

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
    .btn-export-pdf-mini {
        flex: 1;
        justify-content: center;
    }

    .checkbox-todos-item {
        align-items: flex-start;
    }

    .filtros-inferior-grid {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    }

    .table-wrapper-premium {
        padding: 12px;
    }
    
    .btn-nueva-poliza,
    .btn-nomina-poliza {
        width: 100%;
        justify-content: center;
    }

    .btn-group-actions {
        width: 100%;
        flex-direction: column;
    }

    .table-header-right-ultra {
        width: 100%;
        flex-direction: column;
    }

    .view-toggle-group {
        width: 100%;
    }

    .view-toggle-btn {
        flex: 1;
        justify-content: center;
    }

    .acciones-cell {
        flex-wrap: wrap;
        gap: 3px;
    }

    .btn-accion {
        font-size: 8px;
        padding: 2px 6px;
        min-width: 30px;
        height: 20px;
    }

    .btn-icon-sm {
        width: 10px;
        height: 10px;
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
    .btn-export-pdf-mini {
        width: 100%;
        justify-content: center;
    }

    .filtros-inferior-grid {
        grid-template-columns: 1fr;
    }

    .filtro-inferior-actions {
        justify-content: center;
    }

    .resumen-totales-container-grande {
        min-width: 100%;
        width: 100%;
        padding: 12px 16px;
    }
    
    .total-item-grande {
        min-width: 100%;
        flex: 1 1 100%;
    }

    .acciones-cell {
        gap: 2px;
    }

    .btn-accion {
        font-size: 7px;
        padding: 2px 4px;
        min-width: 24px;
        height: 18px;
    }

    .btn-icon-sm {
        width: 8px;
        height: 8px;
    }
    
    .checkbox-todos-text {
        font-size: 12px;
    }
}
</style>