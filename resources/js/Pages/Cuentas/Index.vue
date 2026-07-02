<template>
    <AppLayout title="RIC - Cuentas">
        <div class="py-6">
            <div class="max-w-full px-4 sm:px-6 lg:px-8">
                <!-- Selector de Empresa + Botones -->
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
                            
                            <!-- ✅ VER INACTIVAS - solo admin, auditor, super -->
                            <a-button 
                                v-if="permisos?.puede_editar_cuentas"
                                size="middle" 
                                class="btn-ver-inactivas"
                                @click="abrirModalInactivas"
                            >
                                <template #icon>
                                    <EyeOutlined />
                                </template>
                                Ver Inactivas
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
                        </div>
                        <div class="table-header-right-ultra">
                            <a-button v-if="filtrosActivos" @click="limpiarFiltros" size="small" class="btn-limpiar-ultra">
                                <template #icon>
                                    <CloseOutlined />
                                </template>
                                Limpiar filtros
                            </a-button>
                        </div>
                    </div>

                    <!-- Tabla con scroll fijo -->
                    <div class="table-scroll-container">
                        <a-table
                            :columns="columns"
                            :data-source="cuentas.data"
                            :pagination="false"
                            :loading="loading"
                            row-key="id_cuenta"
                            class="cuenta-table-ultra"
                            size="middle"
                            :scroll="{ x: 'max-content', y: 500 }"
                            sticky
                        >
                            <template #bodyCell="{ column, record }">
                                <!-- # - Mostrar ID real de la BD -->
                                <template v-if="column.key === '#'">
                                    <span class="text-gray-400 font-mono text-sm">{{ record.id_cuenta }}</span>
                                </template>

                                <!-- CÓDIGO -->
                                <template v-if="column.key === 'codigo_cuenta'">
                                    <div class="clave-cell-ultra">
                                        <span class="clave-text-ultra">{{ record.codigo_cuenta }}</span>
                                    </div>
                                </template>

                                <!-- CUENTA - Click para editar (solo admin, auditor, super) -->
                                <template v-if="column.key === 'nombre_cuenta'">
                                    <div class="nombre-cell-ultra">
                                        <!-- ✅ Si puede editar, muestra enlace para editar -->
                                        <Link 
                                            v-if="permisos?.puede_editar_cuentas"
                                            :href="route('cuentas.edit', record.id_cuenta)"
                                            class="nombre-link-ultra"
                                        >
                                            <span class="nombre-link-icon">✎</span>
                                            {{ record.nombre_cuenta }}
                                        </Link>
                                        <!-- Si no puede editar, solo muestra el nombre -->
                                        <span v-else class="nombre-text-ultra">
                                            {{ record.nombre_cuenta }}
                                        </span>
                                    </div>
                                </template>

                                <!-- NIVEL (JERARQUÍA COMPLETA) -->
                                <template v-if="column.key === 'nivel'">
                                    <div class="nivel-cell-ultra">
                                        <span class="nivel-badge" :class="getNivelClass(record.nivel_texto)">
                                            {{ record.nivel_jerarquico || record.codigo_cuenta }}
                                        </span>
                                    </div>
                                </template>

                                <!-- ÍNDICE -->
                                <template v-if="column.key === 'indice'">
                                    <div class="indice-cell-ultra">
                                        <span class="indice-text-ultra">{{ record.indice }}</span>
                                    </div>
                                </template>

                                <!-- CUENTA MADRE -->
                                <template v-if="column.key === 'cuenta_madre'">
                                    <div class="origen-cell-ultra">
                                        <span class="origen-text-ultra">{{ record.cuenta_madre }}</span>
                                    </div>
                                </template>

                                <!-- TIPO -->
                                <template v-if="column.key === 'tipo_cuenta'">
                                    <div class="fondo-cell-ultra">
                                        <span class="fondo-badge" :class="getFondoClass(record.tipo_cuenta)">
                                            {{ record.tipo_cuenta || '—' }}
                                        </span>
                                    </div>
                                </template>

                                <!-- NATURALEZA -->
                                <template v-if="column.key === 'naturaleza'">
                                    <div class="naturaleza-cell-ultra">
                                        <span class="naturaleza-badge" :class="record.Naturaleza === 'DEUDORA' ? 'deudora' : 'acreedora'">
                                            {{ record.Naturaleza === 'DEUDORA' ? 'Deudora' : record.Naturaleza === 'ACREEDORA' ? 'Acreedora' : '—' }}
                                        </span>
                                    </div>
                                </template>

                                <!-- 🟢 FONDEADORA -->
                                <template v-if="column.key === 'fondeadora'">
                                    <div class="fondeadora-cell-ultra">
                                        <span class="fondeadora-badge" :class="record.fondeo_c == 1 ? 'fondeadora-si' : 'fondeadora-no'">
                                            {{ record.fondeo_c == 1 ? ' Sí' : ' No' }}
                                        </span>
                                    </div>
                                </template>
                            </template>

                            <!-- Footer con filtros -->
                            <template #footer>
                                <div class="filtros-ultra">
                                    <div class="filtros-grid-ultra">
                                        <!-- Filtro Código -->
                                        <div class="filtro-item-ultra">
                                            <InputLabel>Código</InputLabel>
                                            <TextInput 
                                                v-model="filtros.codigo_cuenta"
                                                @input="aplicarFiltros"
                                                placeholder="Código..."
                                                square
                                            />
                                        </div>

                                        <!-- Filtro Cuenta -->
                                        <div class="filtro-item-ultra">
                                            <InputLabel>Cuenta</InputLabel>
                                            <TextInput 
                                                v-model="filtros.nombre_cuenta"
                                                @input="aplicarFiltros"
                                                placeholder="Nombre..."
                                                square
                                            />
                                        </div>

                                        <!-- Filtro Nivel -->
                                        <div class="filtro-item-ultra">
                                            <InputLabel>Nivel</InputLabel>
                                            <a-select
                                                v-model:value="filtros.nivel"
                                                @change="aplicarFiltros"
                                                placeholder="Todos"
                                                allow-clear
                                                size="small"
                                                class="filtro-select-ultra"
                                            >
                                                <a-select-option value="">Todos</a-select-option>
                                                <a-select-option value="1">1er Nivel</a-select-option>
                                                <a-select-option value="2">2do Nivel</a-select-option>
                                                <a-select-option value="3">3er Nivel</a-select-option>
                                                <a-select-option value="4">4to Nivel</a-select-option>
                                                <a-select-option value="5">5to Nivel</a-select-option>
                                            </a-select>
                                        </div>

                                        <!-- Filtro Naturaleza -->
                                        <div class="filtro-item-ultra">
                                            <InputLabel>Naturaleza</InputLabel>
                                            <a-select
                                                v-model:value="filtros.naturaleza"
                                                @change="aplicarFiltros"
                                                placeholder="Todos"
                                                allow-clear
                                                size="small"
                                                class="filtro-select-ultra"
                                            >
                                                <a-select-option value="">Todos</a-select-option>
                                                <a-select-option value="DEUDORA">Deudora</a-select-option>
                                                <a-select-option value="ACREEDORA">Acreedora</a-select-option>
                                            </a-select>
                                        </div>

                                        <!-- Filtro Tipo -->
                                        <div class="filtro-item-ultra">
                                            <InputLabel>Tipo</InputLabel>
                                            <a-select
                                                v-model:value="filtros.tipo_cuenta"
                                                @change="aplicarFiltros"
                                                placeholder="Todos"
                                                allow-clear
                                                size="small"
                                                class="filtro-select-ultra"
                                            >
                                                <a-select-option value="">Todos</a-select-option>
                                                <a-select-option value="FONDEADORA">Fondeadora</a-select-option>
                                                <a-select-option value="RESULTADO">Resultado</a-select-option>
                                                <a-select-option value="ORDEN">Orden</a-select-option>
                                            </a-select>
                                        </div>

                                        <!-- Botón Limpiar -->
                                        <div class="filtro-item-ultra filtro-actions">
                                            <InputLabel>Acciones</InputLabel>
                                            <a-button 
                                                v-if="filtrosActivos"
                                                @click="limpiarFiltros" 
                                                size="small"
                                                class="btn-clear-ultra"
                                                block
                                            >
                                                <template #icon>
                                                    <CloseOutlined />
                                                </template>
                                                Limpiar filtros
                                            </a-button>
                                            <a-button 
                                                v-else
                                                disabled
                                                size="small"
                                                block
                                                class="btn-no-filtros-ultra"
                                            >
                                                <span class="no-filtros-text-ultra">Sin filtros</span>
                                            </a-button>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </a-table>
                    </div>

                    <!-- Paginación -->
                    <div class="pagination-ultra">
                        <span class="pagination-info-ultra">
                            Mostrando <span class="pagination-highlight-ultra">{{ cuentas.from || 0 }}</span> a 
                            <span class="pagination-highlight-ultra">{{ cuentas.to || 0 }}</span> de 
                            <span class="pagination-highlight-ultra">{{ cuentas.total || 0 }}</span> resultados
                        </span>
                        <Pagination :links="cuentas.links" />
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

        <!-- MODAL CUENTAS INACTIVAS -->
        <a-modal
            v-model:open="modalInactivasVisible"
            title="Cuentas Inactivas"
            width="90%"
            class="inactivas-modal-premium"
            :footer="null"
            :closable="true"
            @cancel="cerrarModalInactivas"
        >
            <div class="modal-inactivas-content">
                <div class="modal-inactivas-header">
                    <span class="modal-inactivas-title">📋 Listado de Cuentas Inactivas</span>
                    <span class="modal-inactivas-count">{{ cuentasInactivas.length }} cuentas</span>
                </div>

                <div class="table-scroll-container">
                    <a-table
                        :columns="columnsInactivas"
                        :data-source="cuentasInactivas"
                        :pagination="false"
                        :loading="loadingInactivas"
                        row-key="id_cuenta"
                        class="cuenta-table-ultra"
                        size="middle"
                        :scroll="{ x: 'max-content', y: 400 }"
                        sticky
                    >
                        <template #bodyCell="{ column, record }">
                            <!-- # -->
                            <template v-if="column.key === '#'">
                                <span class="text-gray-400 font-mono text-sm">{{ record.id_cuenta }}</span>
                            </template>

                            <!-- CÓDIGO -->
                            <template v-if="column.key === 'codigo_cuenta'">
                                <div class="clave-cell-ultra">
                                    <span class="clave-text-ultra inactivo-text">{{ record.codigo_cuenta }}</span>
                                </div>
                            </template>

                            <!-- CUENTA -->
                            <template v-if="column.key === 'nombre_cuenta'">
                                <div class="nombre-cell-ultra">
                                    <span class="nombre-text-ultra inactivo-text">{{ record.nombre_cuenta }}</span>
                                </div>
                            </template>

                            <!-- NIVEL -->
                            <template v-if="column.key === 'nivel'">
                                <div class="nivel-cell-ultra">
                                    <span class="nivel-badge" :class="getNivelClass(record.nivel_texto)">
                                        {{ record.nivel_jerarquico || record.codigo_cuenta }}
                                    </span>
                                </div>
                            </template>

                            <!-- ÍNDICE -->
                            <template v-if="column.key === 'indice'">
                                <div class="indice-cell-ultra">
                                    <span class="indice-text-ultra inactivo-text">{{ record.indice }}</span>
                                </div>
                            </template>

                            <!-- CUENTA MADRE -->
                            <template v-if="column.key === 'cuenta_madre'">
                                <div class="origen-cell-ultra">
                                    <span class="origen-text-ultra inactivo-text">{{ record.cuenta_madre }}</span>
                                </div>
                            </template>

                            <!-- TIPO -->
                            <template v-if="column.key === 'tipo_cuenta'">
                                <div class="fondo-cell-ultra">
                                    <span class="fondo-badge" :class="getFondoClass(record.tipo_cuenta)">
                                        {{ record.tipo_cuenta || '—' }}
                                    </span>
                                </div>
                            </template>

                            <!-- NATURALEZA -->
                            <template v-if="column.key === 'naturaleza'">
                                <div class="naturaleza-cell-ultra">
                                    <span class="naturaleza-badge" :class="record.Naturaleza === 'DEUDORA' ? 'deudora' : 'acreedora'">
                                        {{ record.Naturaleza === 'DEUDORA' ? 'Deudora' : record.Naturaleza === 'ACREEDORA' ? 'Acreedora' : '—' }}
                                    </span>
                                </div>
                            </template>

                            <!-- 🟢 FONDEADORA en inactivas -->
                            <template v-if="column.key === 'fondeadora'">
                                <div class="fondeadora-cell-ultra">
                                    <span class="fondeadora-badge" :class="record.fondeo_c == 1 ? 'fondeadora-si' : 'fondeadora-no'">
                                        {{ record.fondeo_c == 1 ? '✅ Sí' : '❌ No' }}
                                    </span>
                                </div>
                            </template>

                            <!-- RESTAURAR - solo admin, auditor, super -->
                            <template v-if="column.key === 'restaurar'">
                                <div class="restaurar-cell-ultra">
                                    <a-tooltip v-if="permisos?.puede_editar_cuentas" title="Restaurar cuenta" placement="top" color="#10b981">
                                        <button 
                                            class="btn-restaurar-ultra"
                                            @click="confirmarRestaurar(record)"
                                        >
                                            <ReloadOutlined />
                                        </button>
                                    </a-tooltip>
                                    <span v-else class="text-gray-400 text-sm">—</span>
                                </div>
                            </template>
                        </template>
                    </a-table>
                </div>

                <div class="modal-inactivas-footer">
                    <span class="pagination-info-ultra">
                        Mostrando <span class="pagination-highlight-ultra">{{ cuentasInactivas.length }}</span> cuentas inactivas
                    </span>
                    <a-button @click="cerrarModalInactivas" class="btn-cerrar-modal">
                        <template #icon>
                            <CloseOutlined />
                        </template>
                        Cerrar
                    </a-button>
                </div>
            </div>
        </a-modal>

        <ModalAlert ref="modalAlert" />
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted, nextTick } from 'vue';
import Pagination from '@/Components/Pagination.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import ModalAlert from '@/Components/AlertModal.vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import {
    PlusOutlined,
    CloseOutlined,
    EyeOutlined,
    ReloadOutlined,
    ShopOutlined
} from '@ant-design/icons-vue';

import {
    Button as AButton,
    Table as ATable,
    Tag as ATag,
    Select as ASelect,
    Tooltip as ATooltip,
    Modal as AModal,
} from 'ant-design-vue';

// ============================================
// ✅ PERMISOS DESDE EL BACKEND
// ============================================
const page = usePage();
const permisos = computed(() => page.props.permisos || {});

const props = defineProps({
    cuentas: {
        type: Object,
        default: () => ({ data: [], from: 0, to: 0, total: 0, links: [] })
    },
    cuentas_inactivas: {
        type: Array,
        default: () => []
    },
    stats: {
        type: Object,
        default: null
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
    }
});

const loading = ref(false);
const loadingInactivas = ref(false);
const modalAlert = ref(null);
const empresaSeleccionada = ref(props.empresa_seleccionada || null);
const modalInactivasVisible = ref(false);
const cuentasInactivas = ref(props.cuentas_inactivas || []);

// ============================================
// 🟢 COLUMNAS DE LA TABLA PRINCIPAL (CON FONDEADORA)
// ============================================
const columns = [
    {
        title: 'ID',
        key: '#',
        width: '70px',
        align: 'center',
        fixed: 'left'
    },
    {
        title: 'Código',
        key: 'codigo_cuenta',
        width: '130px',
        fixed: 'left'
    },
    {
        title: 'Cuenta',
        key: 'nombre_cuenta',
        width: '320px'
    },
    {
        title: 'Nivel Jerárquico',
        key: 'nivel',
        width: '150px',
        align: 'center'
    },
    {
        title: 'Índice',
        key: 'indice',
        width: '180px'
    },
    {
        title: 'Cuenta Madre',
        key: 'cuenta_madre',
        width: '180px'
    },
    {
        title: 'Tipo',
        key: 'tipo_cuenta',
        width: '100px',
        align: 'center'
    },
    {
        title: 'Naturaleza',
        key: 'naturaleza',
        width: '100px',
        align: 'center'
    },
    {
        title: 'Fondeadora',
        key: 'fondeadora',
        width: '100px',
        align: 'center'
    }
];

// ============================================
// 🟢 COLUMNAS DEL MODAL DE INACTIVAS (CON FONDEADORA)
// ============================================
const columnsInactivas = [
    {
        title: 'ID',
        key: '#',
        width: '70px',
        align: 'center',
        fixed: 'left'
    },
    {
        title: 'Código',
        key: 'codigo_cuenta',
        width: '130px',
        fixed: 'left'
    },
    {
        title: 'Cuenta',
        key: 'nombre_cuenta',
        width: '320px'
    },
    {
        title: 'Nivel Jerárquico',
        key: 'nivel',
        width: '150px',
        align: 'center'
    },
    {
        title: 'Índice',
        key: 'indice',
        width: '180px'
    },
    {
        title: 'Cuenta Madre',
        key: 'cuenta_madre',
        width: '180px'
    },
    {
        title: 'Tipo',
        key: 'tipo_cuenta',
        width: '100px',
        align: 'center'
    },
    {
        title: 'Naturaleza',
        key: 'naturaleza',
        width: '100px',
        align: 'center'
    },
    {
        title: 'Fondeadora',
        key: 'fondeadora',
        width: '100px',
        align: 'center'
    },
    {
        title: 'Restaurar',
        key: 'restaurar',
        width: '100px',
        align: 'center',
        fixed: 'right'
    }
];

// ============================================
// FILTROS
// ============================================
const filtros = ref({
    codigo_cuenta: props.filtros?.codigo_cuenta || '',
    nombre_cuenta: props.filtros?.nombre_cuenta || '',
    nivel: props.filtros?.nivel || '',
    naturaleza: props.filtros?.naturaleza || '',
    tipo_cuenta: props.filtros?.tipo_cuenta || '',
});

const filtrosActivos = computed(() => {
    return Object.values(filtros.value).some(value => value !== '' && value !== null && value !== undefined);
});

let timeoutId = null;
const aplicarFiltros = () => {
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => {
        if (!empresaSeleccionada.value) {
            return;
        }
        loading.value = true;
        const params = {
            empresa_id: empresaSeleccionada.value
        };
        for (const [key, value] of Object.entries(filtros.value)) {
            if (value !== '' && value !== null && value !== undefined) {
                params[key] = value;
            }
        }
        
        router.get(route('cuentas.index'), params, {
            preserveState: true,
            replace: true,
            onFinish: () => {
                loading.value = false;
            }
        });
    }, 300);
};

const limpiarFiltros = () => {
    filtros.value = {
        codigo_cuenta: '',
        nombre_cuenta: '',
        nivel: '',
        naturaleza: '',
        tipo_cuenta: '',
    };
    aplicarFiltros();
};

// ============================================
// EMPRESA
// ============================================
const cambiarEmpresa = () => {
    if (empresaSeleccionada.value) {
        localStorage.setItem('empresa_cuentas', String(empresaSeleccionada.value));
        loading.value = true;
        const params = {
            empresa_id: empresaSeleccionada.value
        };
        for (const [key, value] of Object.entries(filtros.value)) {
            if (value !== '' && value !== null && value !== undefined) {
                params[key] = value;
            }
        }
        router.get(route('cuentas.index'), params, {
            preserveState: true,
            replace: true,
            onFinish: () => {
                loading.value = false;
            }
        });
    } else {
        router.get(route('cuentas.index'), {}, {
            preserveState: true,
            replace: true
        });
    }
};

// ============================================
// MODAL DE CUENTAS INACTIVAS
// ============================================
const abrirModalInactivas = async () => {
    if (!empresaSeleccionada.value) {
        mostrarModal('warning', 'Sin empresa', 'Selecciona una empresa primero');
        return;
    }
    
    modalInactivasVisible.value = true;
    loadingInactivas.value = true;
    
    try {
        const response = await axios.get(route('cuentas.inactivas'), {
            params: {
                empresa_id: empresaSeleccionada.value
            }
        });
        cuentasInactivas.value = response.data.data || [];
    } catch (error) {
        console.error('Error al cargar cuentas inactivas:', error);
        mostrarModal('error', 'Error', 'No se pudieron cargar las cuentas inactivas');
    } finally {
        loadingInactivas.value = false;
    }
};

const cerrarModalInactivas = () => {
    modalInactivasVisible.value = false;
    cuentasInactivas.value = [];
};

// ============================================
// RESTAURAR CUENTA (solo admin, auditor, super)
// ============================================
const confirmarRestaurar = (cuenta) => {
    // ✅ Verificar permiso antes de restaurar
    if (!permisos.value?.puede_editar_cuentas) {
        Swal.fire({
            icon: 'error',
            title: 'Sin permisos',
            text: 'No tienes permisos para restaurar cuentas. Contacta al administrador.',
            confirmButtonColor: '#dc2626'
        });
        return;
    }

    Swal.fire({
        title: '¿Restaurar cuenta?',
        html: `
            <div class="text-center">
                <div class="flex justify-center mb-3">
                    <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </div>
                </div>
                <p class="font-medium text-gray-800 text-lg">${cuenta.nombre_cuenta}</p>
                <p class="text-sm text-gray-500">Código: <strong>${cuenta.codigo_cuenta}</strong></p>
                <div class="mt-4 p-3 bg-green-50 rounded-lg border border-green-200">
                    <p class="text-sm text-green-700 font-medium">✅ La cuenta volverá a estar <strong>activa</strong></p>
                    <p class="text-xs text-green-600 mt-1">Aparecerá nuevamente en el listado principal</p>
                </div>
            </div>
        `,
        icon: 'success',
        iconColor: '#10b981',
        showCancelButton: true,
        confirmButtonText: 'Sí, restaurar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#64748b',
        reverseButtons: true,
        customClass: {
            popup: 'swal-premium-popup',
            confirmButton: 'swal-premium-confirm',
            cancelButton: 'swal-premium-cancel'
        },
        didOpen: () => {
            agregarEstilosSwal();
        }
    }).then((result) => {
        if (result.isConfirmed) {
            router.post(route('cuentas.restaurar', cuenta.id_cuenta), {}, {
                preserveScroll: true,
                onSuccess: () => {
                    Swal.fire({
                        title: '¡Restaurada!',
                        html: `
                            <div class="text-center">
                                <div class="flex justify-center mb-3">
                                    <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center">
                                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                </div>
                                <p class="text-gray-700">Cuenta <strong>${cuenta.nombre_cuenta}</strong></p>
                                <p class="text-sm text-green-600 mt-1">Restaurada correctamente</p>
                            </div>
                        `,
                        icon: 'success',
                        confirmButtonText: 'Aceptar',
                        confirmButtonColor: '#1a3a5c',
                        timer: 3000,
                        timerProgressBar: true
                    }).then(() => {
                        cuentasInactivas.value = cuentasInactivas.value.filter(c => c.id_cuenta !== cuenta.id_cuenta);
                        router.reload();
                    });
                },
                onError: (errors) => {
                    const errorMsg = errors?.error || 'Ocurrió un error al restaurar la cuenta';
                    Swal.fire({
                        title: 'Error',
                        text: errorMsg,
                        icon: 'error',
                        confirmButtonText: 'Entendido',
                        confirmButtonColor: '#ef4444'
                    });
                }
            });
        }
    });
};

// ============================================
// ESTILOS PARA SWEETALERT2
// ============================================
const agregarEstilosSwal = () => {
    const style = document.createElement('style');
    style.textContent = `
        .swal-premium-popup {
            border-radius: 20px !important;
            box-shadow: 0 25px 80px rgba(0, 0, 0, 0.3) !important;
            padding: 0 !important;
            overflow: hidden !important;
        }
        .swal-premium-confirm {
            padding: 10px 28px !important;
            font-weight: 600 !important;
            border-radius: 10px !important;
            transition: all 0.3s ease !important;
        }
        .swal-premium-confirm:hover {
            transform: translateY(-2px) scale(1.02);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2) !important;
        }
        .swal-premium-cancel {
            padding: 10px 28px !important;
            font-weight: 600 !important;
            border-radius: 10px !important;
            transition: all 0.3s ease !important;
        }
        .swal-premium-cancel:hover {
            transform: translateY(-2px);
            background: #f1f5f9 !important;
        }
        .swal2-actions {
            padding: 0 32px 24px !important;
            gap: 12px !important;
        }
        .swal2-timer-progress-bar {
            height: 4px !important;
            background: linear-gradient(90deg, #1a3a5c, #2c5282) !important;
        }
        .swal2-popup {
            font-family: 'Inter', system-ui, -apple-system, sans-serif !important;
        }
    `;
    document.head.appendChild(style);
};

// ============================================
// CLASES Y ESTILOS
// ============================================
const getNivelClass = (nivel) => {
    const classes = {
        'Primer Nivel': 'nivel-1',
        'Segundo Nivel': 'nivel-2',
        'Tercer Nivel': 'nivel-3',
        'Cuarto Nivel': 'nivel-4',
        'Quinto Nivel': 'nivel-5'
    };
    return classes[nivel] || 'nivel-1';
};

const getFondoClass = (tipo) => {
    const classes = {
        'FONDEADORA': 'fondeadora',
        'RESULTADO': 'resultado',
        'ORDEN': 'orden'
    };
    return classes[tipo] || '';
};

// ============================================
// FUNCIONES DE ACCIÓN
// ============================================
const mostrarModal = (type, title, message, duration = 4000, onConfirm = null) => {
    if (modalAlert.value && modalAlert.value.show) {
        modalAlert.value.show({
            type,
            title,
            message,
            duration,
            buttonText: type === 'error' ? 'Entendido' : 'Aceptar'
        }, onConfirm);
    }
};

const procesarFlash = () => {
    if (!props.flash) return;
    const tipoMap = {
        success: { type: 'success', title: '¡Éxito!' },
        error: { type: 'error', title: 'Error' },
        updated: { type: 'success', title: '¡Cuenta actualizada!' },
        created: { type: 'success', title: '¡Cuenta creada!' },
        deleted: { type: 'success', title: '¡Cuenta eliminada!' },
        info: { type: 'info', title: 'Información' },
        warning: { type: 'warning', title: 'Advertencia' }
    };
    for (const [key, message] of Object.entries(props.flash)) {
        if (message && tipoMap[key]) {
            mostrarModal(tipoMap[key].type, tipoMap[key].title, message);
            break;
        }
    }
};

watch(() => props.flash, (newFlash) => {
    if (newFlash && Object.keys(newFlash).length > 0) {
        nextTick(() => procesarFlash());
    }
}, { deep: true, immediate: true });

onMounted(() => {
    agregarEstilosSwal();
    if (!empresaSeleccionada.value) {
        const empresaGuardada = localStorage.getItem('empresa_cuentas');
        if (empresaGuardada && props.empresas?.some(e => e.id == parseInt(empresaGuardada))) {
            empresaSeleccionada.value = parseInt(empresaGuardada);
        } else if (props.empresas && props.empresas.length > 0) {
            empresaSeleccionada.value = props.empresas[0].id;
        }
    }
    
    if (empresaSeleccionada.value && (!props.cuentas?.data || props.cuentas.data.length === 0)) {
        cambiarEmpresa();
    }
    
    if (props.flash && Object.keys(props.flash).length > 0) {
        nextTick(() => procesarFlash());
    }
});
</script>

<style scoped>
/* ===== TODOS LOS ESTILOS EXISTENTES SE MANTIENEN IGUAL ===== */
/* ===== HEADER PREMIUM ===== */
.header-premium {
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    border-radius: 20px;
    padding: 24px 28px;
    margin-bottom: 8px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
    border: 1px solid #f0f2f5;
}

.header-content-premium {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

@media (min-width: 640px) {
    .header-content-premium {
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
    }
}

.header-left-premium {
    display: flex;
    align-items: center;
    gap: 16px;
}

.header-icon-wrapper {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, #1a3a5c, #2c5282);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(26, 58, 92, 0.2);
}

.header-icon-svg {
    width: 28px;
    height: 28px;
    stroke: white;
}

.header-title-premium {
    font-size: 24px;
    font-weight: 700;
    color: #0f172a;
    margin: 0;
    letter-spacing: -0.5px;
}

.header-subtitle-premium {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #64748b;
    font-size: 14px;
    margin: 4px 0 0 0;
}

.subtitle-line {
    width: 24px;
    height: 2px;
    background: linear-gradient(90deg, #1a3a5c, transparent);
    border-radius: 2px;
}

/* ===== EMPRESA SELECTOR ===== */
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

.empresa-selector-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-left: auto;
}

@media (max-width: 640px) {
    .empresa-selector-actions {
        margin-left: 0;
        width: 100%;
        flex-wrap: wrap;
    }
    .empresa-selector-actions .btn-nueva-cuenta-premium,
    .empresa-selector-actions .btn-ver-inactivas {
        flex: 1;
        min-width: 120px;
    }
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

.empresa-select-native:hover {
    border-color: #667eea;
    background: #ffffff;
}

.btn-nueva-cuenta-premium {
    background: linear-gradient(135deg, #1a3a5c 0%, #2c5282 100%) !important;
    border: none !important;
    border-radius: 6px !important;
    font-weight: 600 !important;
    padding: 0 20px !important;
    height: 36px !important;
    box-shadow: 0 2px 8px rgba(26, 58, 92, 0.2);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    font-size: 13px !important;
    letter-spacing: 0.3px;
}

.btn-nueva-cuenta-premium:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(26, 58, 92, 0.3) !important;
}

.btn-ver-inactivas {
    background: linear-gradient(135deg, #f59e0b, #d97706) !important;
    border: none !important;
    border-radius: 6px !important;
    font-weight: 600 !important;
    padding: 0 20px !important;
    height: 36px !important;
    color: white !important;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    font-size: 13px !important;
    letter-spacing: 0.3px;
}

.btn-ver-inactivas:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(245, 158, 11, 0.3) !important;
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
    max-height: 500px !important;
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
    gap: 16px;
    flex-wrap: wrap;
}

.table-header-right-ultra {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
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

.btn-limpiar-ultra {
    border-radius: 0px !important;
    color: #64748b !important;
    border: 2px solid #d1d5db !important;
    transition: all 0.3s ease !important;
    height: 36px !important;
    font-weight: 600 !important;
    background: #ffffff !important;
}

.btn-limpiar-ultra:hover {
    color: #1a3a5c !important;
    border-color: #1a3a5c !important;
    background: #f8faff !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(26, 58, 92, 0.1) !important;
}

/* ===== TABLA ANT DESIGN ===== */
.cuenta-table-ultra {
    width: 100%;
}

.cuenta-table-ultra :deep(.ant-table) {
    border-radius: 0;
}

.cuenta-table-ultra :deep(.ant-table-thead > tr > th) {
    background: linear-gradient(135deg, #f1f5f9, #e8edf2) !important;
    font-weight: 700;
    color: #1e293b;
    border-bottom: 2px solid #d1d5db;
    padding: 12px 16px;
    font-size: 11px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    position: sticky;
    top: 0;
    z-index: 10;
}

.cuenta-table-ultra :deep(.ant-table-thead > tr > th:first-child) {
    border-radius: 0 !important;
}

.cuenta-table-ultra :deep(.ant-table-thead > tr > th:last-child) {
    border-radius: 0 !important;
}

.cuenta-table-ultra :deep(.ant-table-tbody > tr) {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.cuenta-table-ultra :deep(.ant-table-tbody > tr:hover) {
    background: linear-gradient(90deg, #f8faff, #f0f7ff) !important;
    box-shadow: inset 0 0 0 1px #dbeafe;
}

.cuenta-table-ultra :deep(.ant-table-tbody > tr:last-child td) {
    border-bottom: none;
}

.cuenta-table-ultra :deep(.ant-table-cell) {
    padding: 10px 14px;
    border-bottom: 1px solid #f1f5f9;
    font-size: 13px;
}

/* Columnas fijas */
.cuenta-table-ultra :deep(.ant-table-cell-fix-left) {
    background: #ffffff;
    z-index: 5;
}

.cuenta-table-ultra :deep(.ant-table-tbody .ant-table-cell-fix-left:hover) {
    background: #f8faff;
}

/* ===== CELDAS ===== */
.clave-cell-ultra {
    display: flex;
    align-items: center;
}

.clave-text-ultra {
    font-size: 13px;
    font-weight: 600;
    color: #1a3a5c;
    font-family: 'Courier New', monospace;
}

.inactivo-text {
    opacity: 0.6;
}

.nombre-cell-ultra {
    display: flex;
    align-items: center;
}

.nombre-link-ultra {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: #1a3a5c;
    font-weight: 500;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s ease;
    padding: 4px 8px;
    border-radius: 6px;
    border: 1px solid transparent;
    width: 100%;
}

.nombre-link-ultra:hover {
    color: #667eea;
    background: #f0f4ff;
    border-color: #dbeafe;
    text-decoration: none;
    transform: translateX(2px);
}

.nombre-link-icon {
    font-size: 12px;
    opacity: 0;
    transition: all 0.2s ease;
    color: #667eea;
}

.nombre-link-ultra:hover .nombre-link-icon {
    opacity: 1;
}

.nombre-text-ultra {
    font-size: 13px;
    color: #1a3a5c;
    font-weight: 500;
}

.nivel-cell-ultra {
    display: flex;
    align-items: center;
    justify-content: center;
}

.nivel-badge {
    display: inline-block;
    padding: 3px 12px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
    text-align: center;
    font-family: 'Courier New', monospace;
    letter-spacing: 0.5px;
}

.nivel-badge.nivel-1 {
    background: #dbeafe;
    color: #1d4ed8;
}

.nivel-badge.nivel-2 {
    background: #d1fae5;
    color: #059669;
}

.nivel-badge.nivel-3 {
    background: #fef3c7;
    color: #d97706;
}

.nivel-badge.nivel-4 {
    background: #ede9fe;
    color: #7c3aed;
}

.nivel-badge.nivel-5 {
    background: #fce4ec;
    color: #dc2626;
}

.indice-cell-ultra {
    display: flex;
    align-items: center;
}

.indice-text-ultra {
    font-size: 12px;
    color: #475569;
    font-weight: 400;
}

.origen-cell-ultra {
    display: flex;
    align-items: center;
}

.origen-text-ultra {
    font-size: 12px;
    color: #475569;
    font-weight: 400;
}

.fondo-cell-ultra {
    display: flex;
    align-items: center;
    justify-content: center;
}

.fondo-badge {
    display: inline-block;
    padding: 2px 10px;
    border-radius: 4px;
    font-size: 10px;
    font-weight: 600;
    text-align: center;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.fondo-badge.fondeadora {
    background: #dbeafe;
    color: #1d4ed8;
}

.fondo-badge.resultado {
    background: #fef3c7;
    color: #d97706;
}

.fondo-badge.orden {
    background: #f3e8ff;
    color: #7c3aed;
}

.naturaleza-cell-ultra {
    display: flex;
    align-items: center;
    justify-content: center;
}

.naturaleza-badge {
    display: inline-block;
    padding: 2px 10px;
    border-radius: 4px;
    font-size: 10px;
    font-weight: 600;
    text-align: center;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.naturaleza-badge.deudora {
    background: #d1fae5;
    color: #065f46;
}

.naturaleza-badge.acreedora {
    background: #fee2e2;
    color: #991b1b;
}

/* ===== 🟢 FONDEADORA ===== */
.fondeadora-cell-ultra {
    display: flex;
    align-items: center;
    justify-content: center;
}

.fondeadora-badge {
    display: inline-block;
    padding: 2px 12px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
    text-align: center;
}

.fondeadora-badge.fondeadora-si {
    background: #d1fae5;
    color: #065f46;
}

.fondeadora-badge.fondeadora-no {
    background: #fef3c7;
    color: #92400e;
}

/* ===== RESTAURAR ===== */
.restaurar-cell-ultra {
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-restaurar-ultra {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    background: transparent;
    color: #10b981;
}

.btn-restaurar-ultra:hover {
    background: #ecfdf5;
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
}

/* ===== FILTROS ===== */
.filtros-ultra {
    background: #ffffff;
    padding: 20px 0 0 0;
    border-top: 2px solid #f1f5f9;
}

.filtros-grid-ultra {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr 1fr 1fr 0.8fr;
    gap: 12px;
    align-items: end;
}

@media (max-width: 1200px) {
    .filtros-grid-ultra {
        grid-template-columns: 1fr 1fr 1fr 1fr;
        gap: 10px;
    }
}

@media (max-width: 992px) {
    .filtros-grid-ultra {
        grid-template-columns: 1fr 1fr 1fr;
        gap: 10px;
    }
}

@media (max-width: 768px) {
    .filtros-grid-ultra {
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }
}

@media (max-width: 480px) {
    .filtros-grid-ultra {
        grid-template-columns: 1fr;
        gap: 8px;
    }
}

.filtro-item-ultra {
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 0;
}

.filtro-item-ultra :deep(.input-label) {
    font-size: 11px !important;
    font-weight: 600 !important;
    color: #475569 !important;
    margin-bottom: 0 !important;
}

.filtro-item-ultra :deep(.text-input) {
    height: 36px !important;
    font-size: 12px !important;
    padding: 0 10px !important;
    border-radius: 0px !important;
    border: 2px solid #d1d5db !important;
}

.filtro-item-ultra :deep(.text-input:focus) {
    border-color: #1a3a5c !important;
    box-shadow: 0 0 0 3px rgba(26, 58, 92, 0.1) !important;
}

.filtro-actions {
    min-width: 100px;
}

.filtro-select-ultra :deep(.ant-select-selector) {
    border-radius: 0px !important;
    border: 2px solid #d1d5db !important;
    transition: all 0.3s ease !important;
    background: #ffffff !important;
    height: 36px !important;
    font-size: 12px !important;
    width: 100% !important;
    box-shadow: none !important;
}

.filtro-select-ultra :deep(.ant-select-selector:hover) {
    border-color: #1a3a5c !important;
    background: #fafbfc !important;
}

.filtro-select-ultra :deep(.ant-select-focused .ant-select-selector) {
    border-color: #1a3a5c !important;
    box-shadow: 0 0 0 3px rgba(26, 58, 92, 0.1) !important;
    background: #fafbfc !important;
}

.filtro-select-ultra :deep(.ant-select-selection-item) {
    line-height: 34px !important;
}

/* ===== BOTONES ===== */
.btn-clear-ultra {
    border-radius: 0px !important;
    background: linear-gradient(135deg, #1a3a5c, #2c5282) !important;
    border: none !important;
    color: white !important;
    height: 36px !important;
    font-size: 12px !important;
    font-weight: 700 !important;
    transition: all 0.3s ease !important;
    box-shadow: 0 2px 8px rgba(26, 58, 92, 0.2);
}

.btn-clear-ultra:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(26, 58, 92, 0.3) !important;
}

.btn-no-filtros-ultra {
    border-radius: 0px !important;
    background: #f8fafc !important;
    border: 2px dashed #d1d5db !important;
    cursor: not-allowed !important;
    height: 36px !important;
    font-size: 12px !important;
}

.no-filtros-text-ultra {
    color: #94a3b8;
    font-weight: 600;
}

/* ===== PAGINACIÓN ===== */
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

/* ===== MODAL INACTIVAS ===== */
.inactivas-modal-premium :deep(.ant-modal-content) {
    border-radius: 16px !important;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
}

.inactivas-modal-premium :deep(.ant-modal-header) {
    border-bottom: 2px solid #f1f5f9 !important;
    padding: 16px 24px !important;
    background: linear-gradient(135deg, #f8fafc, #f1f5f9) !important;
}

.inactivas-modal-premium :deep(.ant-modal-title) {
    font-weight: 700 !important;
    font-size: 17px !important;
    color: #0f172a !important;
}

.inactivas-modal-premium :deep(.ant-modal-body) {
    padding: 20px !important;
}

.modal-inactivas-content {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.modal-inactivas-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 4px 8px 4px;
    border-bottom: 1px solid #f1f5f9;
}

.modal-inactivas-title {
    font-size: 14px;
    font-weight: 600;
    color: #0f172a;
}

.modal-inactivas-count {
    font-size: 12px;
    color: #64748b;
    background: #f1f5f9;
    padding: 2px 12px;
    border-radius: 12px;
}

.modal-inactivas-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 12px;
    border-top: 1px solid #f1f5f9;
}

.btn-cerrar-modal {
    border-radius: 6px !important;
    color: #64748b !important;
    border: 2px solid #d1d5db !important;
    transition: all 0.3s ease !important;
    height: 36px !important;
    font-weight: 600 !important;
}

.btn-cerrar-modal:hover {
    color: #1a3a5c !important;
    border-color: #1a3a5c !important;
}

/* ===== ANIMACIONES ===== */
@keyframes pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(1.2); }
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    .header-premium {
        padding: 16px 20px;
    }
    
    .header-title-premium {
        font-size: 20px;
    }
    
    .header-icon-wrapper {
        width: 44px;
        height: 44px;
    }
    
    .header-icon-svg {
        width: 22px;
        height: 22px;
    }
    
    .table-wrapper-premium {
        padding: 12px;
    }
    
    .table-scroll-container {
        border-radius: 6px;
    }

    .modal-inactivas-footer {
        flex-direction: column;
        gap: 8px;
        align-items: stretch;
    }
}

/* ===== SCROLLBAR PERSONALIZADA ===== */
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
</style>