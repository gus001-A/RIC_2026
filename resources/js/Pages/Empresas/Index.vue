<template>
    <AppLayout title="RIC - Empresas">
        <template #header>
            <div class="header-premium">
                <div class="header-content-premium">
                    <div class="header-left-premium">
                        <div class="header-icon-wrapper">
                            <svg class="header-icon-svg" fill="none" stroke="white" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="header-title-premium">
                                Gestión de Empresas
                            </h2>
                            <p class="header-subtitle-premium">
                                <span class="subtitle-line"></span>
                                Administra las empresas del sistema
                            </p>
                        </div>
                    </div>
                    <!-- ✅ NUEVA EMPRESA - solo SUPERUSUARIO -->
                    <Link v-if="permisos?.puede_crear_empresas" :href="route('empresas.create')">
                        <a-button type="primary" size="large" class="btn-nueva-empresa-premium">
                            <template #icon>
                                <PlusOutlined />
                            </template>
                            Nueva Empresa
                        </a-button>
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Stats Cards -->
                <a-row :gutter="[20, 20]" class="mb-6">
                    <a-col :xs="24" :sm="12" :md="6">
                        <div class="stats-card-enhanced">
                            <div class="stats-card-enhanced-content">
                                <div class="stats-card-enhanced-left">
                                    <span class="stats-card-enhanced-label">Total Empresas</span>
                                    <span class="stats-card-enhanced-value">{{ stats?.total || 0 }}</span>
                                </div>
                                <div class="stats-card-enhanced-icon" style="background: linear-gradient(135deg, rgba(26, 58, 92, 0.1), rgba(26, 58, 92, 0.05));">
                                    <svg class="stats-card-enhanced-svg" style="color: #1a3a5c;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="stats-card-enhanced-progress">
                                <div class="stats-card-enhanced-progress-bar" style="width: 100%; background: linear-gradient(90deg, #1a3a5c, #2c5282);"></div>
                            </div>
                        </div>
                    </a-col>

                    <a-col :xs="24" :sm="12" :md="6">
                        <div class="stats-card-enhanced">
                            <div class="stats-card-enhanced-content">
                                <div class="stats-card-enhanced-left">
                                    <span class="stats-card-enhanced-label">Activas</span>
                                    <span class="stats-card-enhanced-value" style="color: #2e7d32;">{{ stats?.activas || 0 }}</span>
                                </div>
                                <div class="stats-card-enhanced-icon" style="background: linear-gradient(135deg, rgba(46, 125, 50, 0.1), rgba(46, 125, 50, 0.05));">
                                    <svg class="stats-card-enhanced-svg" style="color: #2e7d32;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="stats-card-enhanced-progress">
                                <div class="stats-card-enhanced-progress-bar" :style="{ width: stats?.total ? Math.round((stats.activas / stats.total) * 100) + '%' : '0%', background: 'linear-gradient(90deg, #2e7d32, #43a047)' }"></div>
                            </div>
                        </div>
                    </a-col>

                    <a-col :xs="24" :sm="12" :md="6">
                        <div class="stats-card-enhanced">
                            <div class="stats-card-enhanced-content">
                                <div class="stats-card-enhanced-left">
                                    <span class="stats-card-enhanced-label">Inactivas</span>
                                    <span class="stats-card-enhanced-value" style="color: #c62828;">{{ stats?.inactivas || 0 }}</span>
                                </div>
                                <div class="stats-card-enhanced-icon" style="background: linear-gradient(135deg, rgba(198, 40, 40, 0.1), rgba(198, 40, 40, 0.05));">
                                    <svg class="stats-card-enhanced-svg" style="color: #c62828;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="stats-card-enhanced-progress">
                                <div class="stats-card-enhanced-progress-bar" :style="{ width: stats?.total ? Math.round((stats.inactivas / stats.total) * 100) + '%' : '0%', background: 'linear-gradient(90deg, #c62828, #e53935)' }"></div>
                            </div>
                        </div>
                    </a-col>

                    <a-col :xs="24" :sm="12" :md="6">
                        <div class="stats-card-enhanced">
                            <div class="stats-card-enhanced-content">
                                <div class="stats-card-enhanced-left">
                                    <span class="stats-card-enhanced-label">Tipo Persona</span>
                                    <span class="stats-card-enhanced-value" style="color: #4a148c; font-size: 22px;">{{ stats?.fisicas || 0 }}F / {{ stats?.morales || 0 }}M</span>
                                </div>
                                <div class="stats-card-enhanced-icon" style="background: linear-gradient(135deg, rgba(74, 20, 140, 0.1), rgba(74, 20, 140, 0.05));">
                                    <svg class="stats-card-enhanced-svg" style="color: #4a148c;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="stats-card-enhanced-progress">
                                <div class="stats-card-enhanced-progress-bar" :style="{ width: stats?.total ? Math.round(((stats.fisicas || 0) / stats.total) * 100) + '%' : '0%', background: 'linear-gradient(90deg, #4a148c, #6a1b9a)' }"></div>
                            </div>
                        </div>
                    </a-col>
                </a-row>

                <!-- Tabla Premium -->
                <div class="table-wrapper-premium">
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

                    <!-- Tabla -->
                    <a-table
                        :columns="columns"
                        :data-source="empresas.data"
                        :pagination="false"
                        :loading="loading"
                        row-key="id"
                        class="empresa-table-ultra"
                        size="middle"
                    >
                        <template #bodyCell="{ column, record }">
                            <template v-if="column.key === 'empresa'">
                                <div class="empresa-cell-ultra">
                                    <div class="avatar-container-ultra">
                                        <a-avatar
                                            :style="{ 
                                                background: record.activo 
                                                    ? `linear-gradient(135deg, #1a3a5c, #2c5282)` 
                                                    : `linear-gradient(135deg, #94a3b8, #64748b)`
                                            }"
                                            size="default"
                                            shape="square"
                                            class="avatar-ultra"
                                        >
                                            {{ record.nombre_empresa?.charAt(0) || 'E' }}
                                        </a-avatar>
                                        <div class="avatar-ring" :class="record.activo ? 'active' : 'inactive'"></div>
                                    </div>
                                    <div class="empresa-info-ultra">
                                        <p class="empresa-nombre-ultra">{{ record.nombre_empresa }}</p>
                                        <p class="empresa-clave-ultra">Clave: {{ record.clave || 'N/A' }}</p>
                                    </div>
                                </div>
                            </template>

                            <template v-if="column.key === 'tipo'">
                                <div class="tipo-cell-ultra">
                                    <span class="tipo-badge" :class="record.tipo_persona === 'MORAL' ? 'moral' : 'fisica'">
                                        {{ record.tipo_persona === 'MORAL' ? 'Moral' : 'Física' }}
                                    </span>
                                </div>
                            </template>

                            <template v-if="column.key === 'ubicacion'">
                                <div class="ubicacion-cell-ultra">
                                    <span class="ubicacion-text-ultra">
                                        {{ record.ciudad }}{{ record.ciudad && record.estado ? ', ' : '' }}{{ record.estado || 'Sin ubicación' }}
                                    </span>
                                </div>
                            </template>

                            <template v-if="column.key === 'contacto'">
                                <div class="contacto-cell-ultra">
                                    <a v-if="record.correo" :href="`mailto:${record.correo}`" class="contacto-email-ultra">
                                        <MailOutlined class="contacto-icon-ultra" /> 
                                        <span>{{ record.correo }}</span>
                                    </a>
                                    <span v-if="record.telefono_personal" class="contacto-telefono-ultra">
                                        <PhoneOutlined class="contacto-icon-ultra" /> 
                                        <span>{{ record.telefono_personal }}</span>
                                    </span>
                                    <span v-if="!record.correo && !record.telefono_personal" class="contacto-vacio-ultra">
                                        Sin contacto
                                    </span>
                                </div>
                            </template>

                            <template v-if="column.key === 'estado'">
                                <div class="estado-cell-ultra">
                                    <div class="estado-badge-ultra" :class="record.activo ? 'activo' : 'inactivo'">
                                        <span class="estado-dot-ultra"></span>
                                        <span>{{ record.activo ? 'Activa' : 'Inactiva' }}</span>
                                    </div>
                                </div>
                            </template>

                            <template v-if="column.key === 'acciones'">
                                <div class="acciones-ultra">
                                    <!-- ✅ VER - solo superusuario -->
                                    <a-tooltip v-if="permisos?.puede_ver_empresas" title="Ver detalles" placement="top" color="#1a3a5c">
                                        <Link :href="route('empresas.show', record.id)">
                                            <button class="btn-action-ultra btn-view-ultra">
                                                <EyeOutlined />
                                            </button>
                                        </Link>
                                    </a-tooltip>

                                    <!-- ✅ EDITAR - solo superusuario -->
                                    <a-tooltip v-if="permisos?.puede_editar_empresas" title="Editar" placement="top" color="#1a3a5c">
                                        <Link :href="route('empresas.edit', record.id)">
                                            <button class="btn-action-ultra btn-edit-ultra">
                                                <EditOutlined />
                                            </button>
                                        </Link>
                                    </a-tooltip>

                                    <!-- ✅ DESACTIVAR - solo superusuario (solo si está activa) -->
                                    <a-tooltip 
                                        v-if="record.activo && permisos?.puede_editar_empresas"
                                        title="Desactivar empresa" 
                                        placement="top" 
                                        color="#ef4444"
                                    >
                                        <button 
                                            class="btn-action-ultra btn-delete-ultra"
                                            @click="confirmarDesactivar(record)"
                                        >
                                            <DeleteOutlined />
                                        </button>
                                    </a-tooltip>

                                    <!-- ✅ ACTIVAR - solo superusuario (solo si está inactiva) -->
                                    <a-tooltip 
                                        v-if="!record.activo && permisos?.puede_editar_empresas"
                                        title="Activar empresa" 
                                        placement="top" 
                                        color="#10b981"
                                    >
                                        <button 
                                            class="btn-action-ultra btn-activate-ultra"
                                            @click="confirmarActivar(record)"
                                        >
                                            <CheckCircleOutlined />
                                        </button>
                                    </a-tooltip>
                                </div>
                            </template>
                        </template>

                        <!-- Footer con filtros -->
                        <template #footer>
                            <div class="filtros-ultra">
                                <div class="filtros-grid-ultra">
                                    <div class="filtro-item-ultra">
                                        <InputLabel>Buscar</InputLabel>
                                        <TextInput 
                                            v-model="filtros.search"
                                            @input="aplicarFiltros"
                                            placeholder="Nombre empresa..."
                                            square
                                        />
                                    </div>

                                    <div class="filtro-item-ultra">
                                        <InputLabel>RFC</InputLabel>
                                        <TextInput 
                                            v-model="filtros.rfc"
                                            @input="aplicarFiltros"
                                            placeholder="RFC..."
                                            square
                                        />
                                    </div>

                                    <div class="filtro-item-ultra">
                                        <InputLabel>Tipo</InputLabel>
                                        <a-select
                                            v-model:value="filtros.tipo_persona"
                                            @change="aplicarFiltros"
                                            placeholder="Todos"
                                            allow-clear
                                            size="small"
                                            class="filtro-select-ultra"
                                        >
                                            <a-select-option value="">Todos</a-select-option>
                                            <a-select-option value="FISICA">Física</a-select-option>
                                            <a-select-option value="MORAL">Moral</a-select-option>
                                        </a-select>
                                    </div>

                                    <div class="filtro-item-ultra">
                                        <InputLabel>Ubicación</InputLabel>
                                        <a-select
                                            v-model:value="filtros.estado"
                                            @change="aplicarFiltros"
                                            placeholder="Todos"
                                            allow-clear
                                            size="small"
                                            class="filtro-select-ultra"
                                            show-search
                                        >
                                            <a-select-option value="">Todos</a-select-option>
                                            <a-select-option v-for="estado in estados" :key="estado" :value="estado">
                                                {{ estado }}
                                            </a-select-option>
                                        </a-select>
                                    </div>

                                    <div class="filtro-item-ultra">
                                        <InputLabel>Contacto</InputLabel>
                                        <TextInput 
                                            v-model="filtros.contacto"
                                            @input="aplicarFiltros"
                                            placeholder="Email/Teléfono..."
                                            square
                                        />
                                    </div>

                                    <div class="filtro-item-ultra">
                                        <InputLabel>Estado</InputLabel>
                                        <a-select
                                            v-model:value="filtros.activo"
                                            @change="aplicarFiltros"
                                            placeholder="Todos"
                                            allow-clear
                                            size="small"
                                            class="filtro-select-ultra"
                                        >
                                            <a-select-option value="">Todos</a-select-option>
                                            <a-select-option value="true">Activas</a-select-option>
                                            <a-select-option value="false">Inactivas</a-select-option>
                                        </a-select>
                                    </div>

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

                    <!-- Paginación -->
                    <div class="pagination-ultra">
                        <span class="pagination-info-ultra">
                            Mostrando <span class="pagination-highlight-ultra">{{ empresas.from || 0 }}</span> a 
                            <span class="pagination-highlight-ultra">{{ empresas.to || 0 }}</span> de 
                            <span class="pagination-highlight-ultra">{{ empresas.total || 0 }}</span> resultados
                        </span>
                        <Pagination :links="empresas.links" />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted, nextTick } from 'vue';
import Pagination from '@/Components/Pagination.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import axios from 'axios';
import Swal from 'sweetalert2';

// Importar iconos
import {
    PlusOutlined,
    CloseOutlined,
    MailOutlined,
    PhoneOutlined,
    EyeOutlined,
    EditOutlined,
    DeleteOutlined,
    CheckCircleOutlined,
} from '@ant-design/icons-vue';

// Importar componentes de Ant Design
import {
    Button as AButton,
    Row as ARow,
    Col as ACol,
    Table as ATable,
    Tag as ATag,
    Select as ASelect,
    Tooltip as ATooltip,
    Avatar as AAvatar,
} from 'ant-design-vue';

// ============================================
// ✅ PERMISOS DESDE EL BACKEND
// ============================================
const page = usePage();
const permisos = computed(() => page.props.permisos || {});

const props = defineProps({
    empresas: Object,
    stats: Object,
    estados: Array,
    filtros: Object,
    flash: Object
});

const loading = ref(false);

// Columnas de la tabla
const columns = [
    {
        title: 'Empresa',
        key: 'empresa',
        width: '22%'
    },
    {
        title: 'RFC',
        dataIndex: 'rfc',
        key: 'rfc',
        width: '12%'
    },
    {
        title: 'Tipo',
        key: 'tipo',
        width: '10%',
        filters: [
            { text: 'Física', value: 'FISICA' },
            { text: 'Moral', value: 'MORAL' }
        ],
        onFilter: (value, record) => record.tipo_persona === value
    },
    {
        title: 'Ubicación',
        key: 'ubicacion',
        width: '15%'
    },
    {
        title: 'Contacto',
        key: 'contacto',
        width: '18%'
    },
    {
        title: 'Estado',
        key: 'estado',
        width: '10%',
        filters: [
            { text: 'Activa', value: true },
            { text: 'Inactiva', value: false }
        ],
        onFilter: (value, record) => record.activo === value
    },
    {
        title: 'Acciones',
        key: 'acciones',
        width: '13%',
        align: 'center'
    }
];

// Filtros
const filtros = ref({
    search: props.filtros?.search || '',
    rfc: props.filtros?.rfc || '',
    tipo_persona: props.filtros?.tipo_persona || '',
    estado: props.filtros?.estado || '',
    contacto: props.filtros?.contacto || '',
    activo: props.filtros?.activo || ''
});

const filtrosActivos = computed(() => {
    return Object.values(filtros.value).some(value => value !== '' && value !== null && value !== undefined);
});

let timeoutId = null;
const aplicarFiltros = () => {
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => {
        loading.value = true;
        const params = {};
        for (const [key, value] of Object.entries(filtros.value)) {
            if (value !== '' && value !== null && value !== undefined) {
                params[key] = value;
            }
        }
        
        router.get(route('empresas.index'), params, {
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
        search: '',
        rfc: '',
        tipo_persona: '',
        estado: '',
        contacto: '',
        activo: ''
    };
    aplicarFiltros();
};

// ============================================
// PROCESAR FLASH CON SWEETALERT2
// ============================================

const procesarFlash = () => {
    if (!props.flash) return;
    
    const tipoMap = {
        success: { icon: 'success', title: 'Éxito' },
        error: { icon: 'error', title: 'Error' },
        updated: { icon: 'success', title: 'Actualizado' },
        created: { icon: 'success', title: 'Creado' },
        deleted: { icon: 'success', title: 'Eliminado' },
        info: { icon: 'info', title: 'Información' },
        warning: { icon: 'warning', title: 'Advertencia' }
    };

    for (const [key, message] of Object.entries(props.flash)) {
        if (message && tipoMap[key]) {
            Swal.fire({
                title: tipoMap[key].title,
                text: message,
                icon: tipoMap[key].icon,
                confirmButtonColor: tipoMap[key].icon === 'success' ? '#1a3a5c' : '#ef4444',
                timer: 3000,
                timerProgressBar: true
            });
            break;
        }
    }
};

watch(() => props.flash, (newFlash) => {
    if (newFlash && Object.keys(newFlash).length > 0) {
        nextTick(() => {
            procesarFlash();
        });
    }
}, { deep: true, immediate: true });

onMounted(() => {
    if (props.flash && Object.keys(props.flash).length > 0) {
        nextTick(() => {
            procesarFlash();
        });
    }
});

// ============================================
// FUNCIONES PARA ACTIVAR/DESACTIVAR
// ============================================

const confirmarDesactivar = (empresa) => {
    // ✅ Verificar permiso antes de desactivar
    if (!permisos.value?.puede_editar_empresas) {
        Swal.fire({
            icon: 'error',
            title: 'Sin permisos',
            text: 'No tienes permisos para desactivar empresas. Contacta al administrador.',
            confirmButtonColor: '#dc2626'
        });
        return;
    }

    const nombre = empresa.nombre_empresa;
    
    Swal.fire({
        title: 'Desactivar empresa',
        html: `
            <div class="swal-custom-content">
                <div class="swal-persona-info">
                    <div class="swal-avatar" style="background: linear-gradient(135deg, #ef4444, #dc2626);">
                        <span>${nombre.charAt(0).toUpperCase()}</span>
                    </div>
                    <p class="swal-persona-nombre">${nombre}</p>
                    <span class="swal-persona-estado activo">🟢 Activa</span>
                </div>
                <div class="swal-mensaje">
                    <p class="swal-texto-principal">⚠️ Esta empresa será <strong>desactivada</strong></p>
                    <p class="swal-texto-secundario">Podrás reactivarla en cualquier momento</p>
                </div>
                ${empresa.usuarios_count > 0 || empresa.polizas_count > 0 ? `
                    <div class="swal-advertencia">
                        <span class="swal-icono-advertencia">⚠️</span>
                        <span>Tiene ${empresa.usuarios_count > 0 ? empresa.usuarios_count + ' usuario(s)' : ''} 
                        ${empresa.usuarios_count > 0 && empresa.polizas_count > 0 ? ' y ' : ''} 
                        ${empresa.polizas_count > 0 ? empresa.polizas_count + ' póliza(s)' : ''} asociados</span>
                    </div>
                ` : ''}
            </div>
        `,
        icon: 'warning',
        iconColor: '#ef4444',
        showCancelButton: true,
        confirmButtonText: 'Sí, desactivar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#ef4444',
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
            procesarCambioEstado(empresa, false);
        }
    });
};

const confirmarActivar = (empresa) => {
    // ✅ Verificar permiso antes de activar
    if (!permisos.value?.puede_editar_empresas) {
        Swal.fire({
            icon: 'error',
            title: 'Sin permisos',
            text: 'No tienes permisos para activar empresas. Contacta al administrador.',
            confirmButtonColor: '#dc2626'
        });
        return;
    }

    const nombre = empresa.nombre_empresa;
    
    Swal.fire({
        title: 'Activar empresa',
        html: `
            <div class="swal-custom-content">
                <div class="swal-persona-info">
                    <div class="swal-avatar" style="background: linear-gradient(135deg, #10b981, #059669);">
                        <span>${nombre.charAt(0).toUpperCase()}</span>
                    </div>
                    <p class="swal-persona-nombre">${nombre}</p>
                    <span class="swal-persona-estado inactivo">🔴 Inactiva</span>
                </div>
                <div class="swal-mensaje">
                    <p class="swal-texto-principal">✅ Esta empresa será <strong>activada</strong></p>
                    <p class="swal-texto-secundario">Podrás desactivarla en cualquier momento</p>
                </div>
            </div>
        `,
        icon: 'success',
        iconColor: '#10b981',
        showCancelButton: true,
        confirmButtonText: 'Sí, activar',
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
            procesarCambioEstado(empresa, true);
        }
    });
};

const procesarCambioEstado = (empresa, nuevoEstado) => {
    const nombre = empresa.nombre_empresa;
    const accionTexto = nuevoEstado ? 'activada' : 'desactivada';
    
    Swal.fire({
        title: 'Procesando...',
        html: `
            <div class="swal-loading-content">
                <div class="swal-spinner"></div>
                <p class="swal-loading-text">${nuevoEstado ? 'Activando' : 'Desactivando'} empresa</p>
                <p class="swal-loading-subtext">Por favor espera un momento</p>
            </div>
        `,
        allowOutsideClick: false,
        showConfirmButton: false,
        customClass: {
            popup: 'swal-loading-popup'
        },
        didOpen: () => {
            agregarEstilosLoading();
        }
    });
    
    const url = route('empresas.toggle-active', empresa.id);
    
    axios.post(url)
        .then(response => {
            Swal.fire({
                title: '¡Completado!',
                html: `
                    <div class="swal-success-content">
                        <div class="swal-success-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                                <path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <p class="swal-success-text">Empresa <strong>${nombre}</strong></p>
                        <p class="swal-success-subtext">${accionTexto} exitosamente</p>
                    </div>
                `,
                icon: 'success',
                iconColor: '#10b981',
                confirmButtonText: 'Aceptar',
                confirmButtonColor: '#1a3a5c',
                timer: 3000,
                timerProgressBar: true,
                customClass: {
                    popup: 'swal-success-popup',
                    confirmButton: 'swal-success-confirm'
                },
                didOpen: () => {
                    agregarEstilosSuccess();
                }
            }).then(() => {
                router.reload();
            });
        })
        .catch(error => {
            console.error('Error detallado:', error);
            const errorMsg = error.response?.data?.flash?.error || 
                           error.response?.data?.message || 
                           error.message || 
                           'Error al cambiar el estado';
            
            Swal.fire({
                title: 'Error',
                html: `
                    <div class="swal-error-content">
                        <div class="swal-error-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                                <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <p class="swal-error-text">${errorMsg}</p>
                        <p class="swal-error-subtext">Intenta nuevamente o contacta al administrador</p>
                    </div>
                `,
                icon: 'error',
                iconColor: '#ef4444',
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#1a3a5c',
                customClass: {
                    popup: 'swal-error-popup',
                    confirmButton: 'swal-error-confirm'
                },
                didOpen: () => {
                    agregarEstilosError();
                }
            });
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
        .swal-custom-content {
            padding: 0;
        }
        .swal-persona-info {
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            padding: 24px 32px 20px;
            text-align: center;
            border-bottom: 2px solid #e2e8f0;
        }
        .swal-avatar {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            font-weight: 700;
            color: white;
            margin-bottom: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
        }
        .swal-persona-nombre {
            font-size: 18px;
            font-weight: 600;
            color: #0f172a;
            margin: 0 0 4px 0;
        }
        .swal-persona-estado {
            font-size: 13px;
            font-weight: 500;
            padding: 4px 16px;
            border-radius: 20px;
            display: inline-block;
        }
        .swal-persona-estado.activo {
            background: #dcfce7;
            color: #16a34a;
        }
        .swal-persona-estado.inactivo {
            background: #fee2e2;
            color: #dc2626;
        }
        .swal-mensaje {
            padding: 20px 32px 12px;
            text-align: center;
        }
        .swal-texto-principal {
            font-size: 16px;
            color: #0f172a;
            margin: 0 0 6px 0;
        }
        .swal-texto-principal strong {
            color: #1a3a5c;
        }
        .swal-texto-secundario {
            font-size: 14px;
            color: #94a3b8;
            margin: 0;
        }
        .swal-advertencia {
            margin: 12px 32px 20px;
            padding: 12px 16px;
            background: #fef2f2;
            border-radius: 10px;
            border-left: 4px solid #ef4444;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            color: #991b1b;
        }
        .swal-icono-advertencia {
            font-size: 18px;
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
    `;
    document.head.appendChild(style);
};

const agregarEstilosLoading = () => {
    const style = document.createElement('style');
    style.textContent = `
        .swal-loading-popup {
            border-radius: 20px !important;
            padding: 2rem !important;
        }
        .swal-loading-content {
            text-align: center;
        }
        .swal-spinner {
            width: 48px;
            height: 48px;
            border: 4px solid #e2e8f0;
            border-top-color: #1a3a5c;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin: 0 auto 16px;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .swal-loading-text {
            font-size: 16px;
            font-weight: 600;
            color: #0f172a;
            margin: 0 0 4px 0;
        }
        .swal-loading-subtext {
            font-size: 14px;
            color: #94a3b8;
            margin: 0;
        }
    `;
    document.head.appendChild(style);
};

const agregarEstilosSuccess = () => {
    const style = document.createElement('style');
    style.textContent = `
        .swal-success-popup {
            border-radius: 20px !important;
        }
        .swal-success-content {
            text-align: center;
            padding: 8px 0;
        }
        .swal-success-icon {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: linear-gradient(135deg, #10b981, #059669);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
        }
        .swal-success-icon svg {
            width: 28px;
            height: 28px;
        }
        .swal-success-text {
            font-size: 16px;
            color: #0f172a;
            margin: 0 0 4px 0;
        }
        .swal-success-text strong {
            color: #1a3a5c;
        }
        .swal-success-subtext {
            font-size: 14px;
            color: #94a3b8;
            margin: 0;
        }
        .swal-success-confirm {
            padding: 10px 32px !important;
            border-radius: 10px !important;
            font-weight: 600 !important;
        }
    `;
    document.head.appendChild(style);
};

const agregarEstilosError = () => {
    const style = document.createElement('style');
    style.textContent = `
        .swal-error-popup {
            border-radius: 20px !important;
        }
        .swal-error-content {
            text-align: center;
            padding: 8px 0;
        }
        .swal-error-icon {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
        }
        .swal-error-icon svg {
            width: 28px;
            height: 28px;
        }
        .swal-error-text {
            font-size: 16px;
            color: #0f172a;
            margin: 0 0 4px 0;
        }
        .swal-error-subtext {
            font-size: 14px;
            color: #94a3b8;
            margin: 0;
        }
        .swal-error-confirm {
            padding: 10px 32px !important;
            border-radius: 10px !important;
            font-weight: 600 !important;
        }
    `;
    document.head.appendChild(style);
};
</script>

<style scoped>
/* ===== TODOS LOS ESTILOS IGUALES AL ORIGINAL ===== */
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

/* Stats Cards */
.stats-card-enhanced {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid #f0f2f5;
    overflow: hidden;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.stats-card-enhanced:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
    border-color: #d0d7de;
}

.stats-card-enhanced-content {
    padding: 20px 20px 16px;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
}

.stats-card-enhanced-left {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.stats-card-enhanced-label {
    font-size: 13px;
    font-weight: 500;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stats-card-enhanced-value {
    font-size: 28px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.2;
}

.stats-card-enhanced-icon {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: all 0.3s ease;
}

.stats-card-enhanced:hover .stats-card-enhanced-icon {
    transform: scale(1.1) rotate(-5deg);
}

.stats-card-enhanced-svg {
    width: 26px;
    height: 26px;
}

.stats-card-enhanced-progress {
    height: 4px;
    background: #f1f5f9;
}

.stats-card-enhanced-progress-bar {
    height: 100%;
    transition: width 1.2s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Tabla */
.table-wrapper-premium {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid #f0f2f5;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    padding: 20px;
}

.table-header-ultra {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-bottom: 20px;
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

/* Tabla Ant Design */
.empresa-table-ultra {
    border-radius: 8px;
    overflow: hidden;
}

.empresa-table-ultra :deep(.ant-table) {
    border-radius: 8px;
}

.empresa-table-ultra :deep(.ant-table-thead > tr > th) {
    background: linear-gradient(135deg, #f1f5f9, #e8edf2) !important;
    font-weight: 700;
    color: #1e293b;
    border-bottom: 2px solid #d1d5db;
    padding: 14px 18px;
    font-size: 12px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    transition: background 0.3s ease;
}

.empresa-table-ultra :deep(.ant-table-tbody > tr) {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.empresa-table-ultra :deep(.ant-table-tbody > tr:hover) {
    background: linear-gradient(90deg, #f8faff, #f0f7ff) !important;
    box-shadow: inset 0 0 0 1px #dbeafe;
}

.empresa-table-ultra :deep(.ant-table-cell) {
    padding: 14px 18px;
    border-bottom: 1px solid #f1f5f9;
}

/* Celdas */
.empresa-cell-ultra {
    display: flex;
    align-items: center;
    gap: 14px;
}

.avatar-container-ultra {
    position: relative;
}

.avatar-ultra {
    border-radius: 8px !important;
    font-weight: 700 !important;
    font-size: 15px !important;
    flex-shrink: 0;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
}

.avatar-ring {
    position: absolute;
    top: -2px;
    left: -2px;
    right: -2px;
    bottom: -2px;
    border-radius: 10px;
    border: 2px solid transparent;
    transition: all 0.3s ease;
}

.avatar-ring.active {
    border-color: #22c55e;
    animation: pulse-ring 2s infinite;
}

.avatar-ring.inactive {
    border-color: #94a3b8;
}

.empresa-info-ultra {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.empresa-nombre-ultra {
    font-size: 14px;
    font-weight: 600;
    color: #0f172a;
    margin: 0;
}

.empresa-clave-ultra {
    font-size: 12px;
    color: #94a3b8;
    margin: 0;
}

.tipo-badge {
    display: inline-block;
    padding: 4px 14px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.tipo-badge.fisica {
    background: #dbeafe;
    color: #1e40af;
}

.tipo-badge.moral {
    background: #fce7f3;
    color: #9d174d;
}

.ubicacion-text-ultra {
    font-size: 13px;
    color: #475569;
}

.contacto-cell-ultra {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.contacto-email-ultra {
    color: #3b82f6;
    font-size: 13px;
    text-decoration: none;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.contacto-email-ultra:hover {
    color: #2563eb;
    text-decoration: underline;
}

.contacto-telefono-ultra {
    color: #475569;
    font-size: 13px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.contacto-icon-ultra {
    font-size: 14px !important;
}

.contacto-vacio-ultra {
    color: #cbd5e1;
    font-size: 13px;
    font-style: italic;
}

.estado-badge-ultra {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 4px 14px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.estado-badge-ultra.activo {
    background: #dcfce7;
    color: #166534;
}

.estado-badge-ultra.inactivo {
    background: #fee2e2;
    color: #991b1b;
}

.estado-dot-ultra {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
}

.estado-badge-ultra.activo .estado-dot-ultra {
    background: #22c55e;
    animation: pulse 2s infinite;
}

.estado-badge-ultra.inactivo .estado-dot-ultra {
    background: #ef4444;
}

/* Acciones */
.acciones-ultra {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}

.btn-action-ultra {
    width: 34px;
    height: 34px;
    border-radius: 6px;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    background: transparent;
}

.btn-action-ultra:hover {
    transform: translateY(-2px) scale(1.05);
}

.btn-view-ultra {
    color: #3b82f6;
}

.btn-view-ultra:hover {
    background: #eff6ff;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
}

.btn-edit-ultra {
    color: #eab308;
}

.btn-edit-ultra:hover {
    background: #fefce8;
    box-shadow: 0 4px 12px rgba(234, 179, 8, 0.2);
}

.btn-delete-ultra {
    color: #ef4444;
}

.btn-delete-ultra:hover {
    background: #fef2f2;
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);
}

.btn-activate-ultra {
    color: #10b981;
}

.btn-activate-ultra:hover {
    background: #dcfce7;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
}

/* Filtros */
.filtros-ultra {
    background: #ffffff;
    padding: 24px 0 0 0;
    border-top: 2px solid #f1f5f9;
}

.filtros-grid-ultra {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr 1fr 1fr 1fr 0.8fr;
    gap: 16px;
    align-items: end;
}

.filtro-item-ultra {
    display: flex;
    flex-direction: column;
    gap: 6px;
    min-width: 0;
}

.filtro-actions {
    min-width: 120px;
}

.filtro-select-ultra :deep(.ant-select-selector) {
    border-radius: 0px !important;
    border: 2px solid #d1d5db !important;
    transition: all 0.3s ease !important;
    background: #ffffff !important;
    height: 40px !important;
    font-size: 13px !important;
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

/* Botones */
.btn-nueva-empresa-premium {
    background: linear-gradient(135deg, #1a3a5c 0%, #2c5282 100%) !important;
    border: none !important;
    border-radius: 0px !important;
    font-weight: 700 !important;
    padding: 0 32px !important;
    height: 50px !important;
    box-shadow: 0 4px 16px rgba(26, 58, 92, 0.3);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
    font-size: 15px !important;
    letter-spacing: 0.3px;
}

.btn-nueva-empresa-premium:hover {
    transform: translateY(-3px) scale(1.02);
    box-shadow: 0 8px 30px rgba(26, 58, 92, 0.4) !important;
    background: linear-gradient(135deg, #2c5282 0%, #1a3a5c 100%) !important;
}

.btn-limpiar-ultra {
    border-radius: 0px !important;
    color: #64748b !important;
    border: 2px solid #d1d5db !important;
    transition: all 0.3s ease !important;
    height: 40px !important;
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

.btn-clear-ultra {
    border-radius: 0px !important;
    background: linear-gradient(135deg, #1a3a5c, #2c5282) !important;
    border: none !important;
    color: white !important;
    height: 40px !important;
    font-size: 13px !important;
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
    height: 40px !important;
    font-size: 13px !important;
}

.no-filtros-text-ultra {
    color: #94a3b8;
    font-weight: 600;
}

/* Paginación */
.pagination-ultra {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-top: 20px;
    padding-top: 16px;
    border-top: 2px solid #f1f5f9;
}

@media (min-width: 640px) {
    .pagination-ultra {
        flex-direction: row;
    }
}

.pagination-info-ultra {
    font-size: 14px;
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

/* Animaciones */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(1.2); }
}

@keyframes pulse-ring {
    0% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.1); opacity: 0.7; }
    100% { transform: scale(1); opacity: 1; }
}

/* Responsive */
@media (max-width: 1200px) {
    .filtros-grid-ultra {
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 14px;
    }
}

@media (max-width: 768px) {
    .filtros-grid-ultra {
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }
    
    .filtro-actions {
        grid-column: 1 / -1;
    }
    
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
        padding: 16px;
    }
    
    .stats-card-enhanced-value {
        font-size: 22px;
    }
    
    .stats-card-enhanced-icon {
        width: 44px;
        height: 44px;
    }
    
    .stats-card-enhanced-svg {
        width: 22px;
        height: 22px;
    }
}

@media (max-width: 480px) {
    .filtros-grid-ultra {
        grid-template-columns: 1fr;
        gap: 10px;
    }
    
    .filtro-actions {
        grid-column: 1;
    }
    
    .btn-limpiar-ultra {
        width: 100%;
        justify-content: center;
    }
}
</style>