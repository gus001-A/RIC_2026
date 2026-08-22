<template>
    <AppLayout title="RIC - Personas">
        <template #header>
            <div class="header-premium">
                <div class="header-content-premium">
                    <div class="header-left-premium">
                        <div class="header-icon-wrapper">
                            <svg class="header-icon-svg" fill="none" stroke="white" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="header-title-premium">
                                Gestión de Personas
                            </h2>
                            <p class="header-subtitle-premium">
                                <span class="subtitle-line"></span>
                                Administra las personas físicas y morales
                            </p>
                        </div>
                    </div>
                    <Link v-if="permisos?.puede_crear_personas" :href="route('personas.create')">
                        <a-button type="primary" size="large" class="btn-nueva-persona-premium">
                            <template #icon>
                                <PlusOutlined />
                            </template>
                            Nueva Persona
                        </a-button>
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-full px-4 sm:px-6 lg:px-8">
                <!-- Stats Cards -->
                <a-row :gutter="[20, 20]" class="mb-6">
                    <a-col :xs="24" :sm="12" :md="6">
                        <div class="stats-card-enhanced">
                            <div class="stats-card-enhanced-content">
                                <div class="stats-card-enhanced-left">
                                    <span class="stats-card-enhanced-label">Total Personas</span>
                                    <span class="stats-card-enhanced-value">{{ stats?.total || 0 }}</span>
                                </div>
                                <div class="stats-card-enhanced-icon" style="background: linear-gradient(135deg, rgba(26, 58, 92, 0.1), rgba(26, 58, 92, 0.05));">
                                    <svg class="stats-card-enhanced-svg" style="color: #1a3a5c;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
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
                                    <span class="stats-card-enhanced-label">Físicas</span>
                                    <span class="stats-card-enhanced-value" style="color: #2563eb;">{{ stats?.fisicas || 0 }}</span>
                                </div>
                                <div class="stats-card-enhanced-icon" style="background: linear-gradient(135deg, rgba(37, 99, 235, 0.1), rgba(37, 99, 235, 0.05));">
                                    <svg class="stats-card-enhanced-svg" style="color: #2563eb;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="stats-card-enhanced-progress">
                                <div class="stats-card-enhanced-progress-bar" :style="{ width: stats?.total ? Math.round((stats.fisicas / stats.total) * 100) + '%' : '0%', background: 'linear-gradient(90deg, #2563eb, #3b82f6)' }"></div>
                            </div>
                        </div>
                    </a-col>

                    <a-col :xs="24" :sm="12" :md="6">
                        <div class="stats-card-enhanced">
                            <div class="stats-card-enhanced-content">
                                <div class="stats-card-enhanced-left">
                                    <span class="stats-card-enhanced-label">Morales</span>
                                    <span class="stats-card-enhanced-value" style="color: #7c3aed;">{{ stats?.morales || 0 }}</span>
                                </div>
                                <div class="stats-card-enhanced-icon" style="background: linear-gradient(135deg, rgba(124, 58, 237, 0.1), rgba(124, 58, 237, 0.05));">
                                    <svg class="stats-card-enhanced-svg" style="color: #7c3aed;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="stats-card-enhanced-progress">
                                <div class="stats-card-enhanced-progress-bar" :style="{ width: stats?.total ? Math.round((stats.morales / stats.total) * 100) + '%' : '0%', background: 'linear-gradient(90deg, #7c3aed, #8b5cf6)' }"></div>
                            </div>
                        </div>
                    </a-col>

                    <a-col :xs="24" :sm="12" :md="6">
                        <div class="stats-card-enhanced">
                            <div class="stats-card-enhanced-content">
                                <div class="stats-card-enhanced-left">
                                    <span class="stats-card-enhanced-label">Activas / Inactivas</span>
                                    <span class="stats-card-enhanced-value" style="color: #0f172a; font-size: 22px;">{{ stats?.activas || 0 }} / {{ stats?.inactivas || 0 }}</span>
                                </div>
                                <div class="stats-card-enhanced-icon" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(239, 68, 68, 0.05));">
                                    <svg class="stats-card-enhanced-svg" style="color: #10b981;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="stats-card-enhanced-progress">
                                <div class="stats-card-enhanced-progress-bar" :style="{ width: stats?.total ? Math.round((stats.activas / stats.total) * 100) + '%' : '0%', background: 'linear-gradient(90deg, #10b981, #34d399)' }"></div>
                            </div>
                        </div>
                    </a-col>
                </a-row>

                <!-- Tabla Premium -->
                <div class="table-wrapper-premium">
                    <!-- Header de la tabla -->
                    <div class="table-header-ultra">
                        <div class="table-header-left-ultra">
                            <span class="table-title-ultra">Listado de Personas</span>
                            <a-tag v-if="filtrosActivos" color="blue" class="filter-tag-ultra">
                                <span class="filter-dot-active"></span>
                                Filtros activos
                            </a-tag>
                            <span v-if="personas.total > 0" class="resultados-count-ultra">
                                {{ personas.total }} resultado{{ personas.total !== 1 ? 's' : '' }}
                            </span>
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

                    <!-- Contenedor de tabla con scroll -->
                    <div class="table-scroll-container-full">
                        <a-table
                            :columns="columns"
                            :data-source="personas.data"
                            :pagination="false"
                            :loading="loading"
                            row-key="id_persona"
                            class="persona-table-ultra"
                            size="middle"
                            :scroll="{ x: 'max-content' }"
                        >
                            <template #bodyCell="{ column, record }">
                                <template v-if="column.key === 'persona'">
                                    <div class="persona-cell-ultra">
                                        <div class="persona-info-ultra">
                                            <span class="persona-nombre-ultra">{{ record.nombre_completo || 'Sin nombre' }}</span>
                                        </div>
                                    </div>
                                </template>

                                <template v-if="column.key === 'tipo_persona'">
                                    <div class="tipo-cell-ultra">
                                        <span class="tipo-badge" :class="record.tipo_persona === 'MORAL' ? 'moral' : 'fisica'">
                                            {{ record.tipo_persona === 'MORAL' ? 'Moral' : 'Física' }}
                                        </span>
                                    </div>
                                </template>

                                <template v-if="column.key === 'rfc'">
                                    <div class="rfc-cell-ultra">
                                        <span class="rfc-text-ultra">{{ record.rfc || '-' }}</span>
                                    </div>
                                </template>

                                <template v-if="column.key === 'ubicacion'">
                                    <div class="ubicacion-cell-ultra">
                                        <span class="ubicacion-text-ultra">
                                            {{ record.ciudad || '' }}{{ record.ciudad && record.estado ? ', ' : '' }}{{ record.estado || 'Sin ubicación' }}
                                        </span>
                                    </div>
                                </template>

                                <template v-if="column.key === 'contacto'">
                                    <div class="contacto-cell-ultra">
                                        <a v-if="record.email" :href="`mailto:${record.email}`" class="contacto-email-ultra">
                                            <MailOutlined class="contacto-icon-ultra" /> 
                                            <span>{{ record.email }}</span>
                                        </a>
                                        <span v-if="record.telefono_particular || record.telefono_trabajo" class="contacto-telefono-ultra">
                                            <PhoneOutlined class="contacto-icon-ultra" /> 
                                            <span>{{ record.telefono_particular || record.telefono_trabajo }}</span>
                                        </span>
                                        <span v-if="!record.email && !record.telefono_particular && !record.telefono_trabajo" class="contacto-vacio-ultra">
                                            Sin contacto
                                        </span>
                                    </div>
                                </template>

                                <template v-if="column.key === 'representante'">
                                    <div class="representante-cell-ultra">
                                        <span v-if="record.representante_nombre_completo" class="representante-nombre-ultra">
                                            {{ record.representante_nombre_completo }}
                                        </span>
                                        <span v-else class="representante-vacio-ultra">-</span>
                                    </div>
                                </template>

                                <template v-if="column.key === 'estado'">
                                    <div class="estado-cell-ultra">
                                        <div class="estado-badge-ultra" :class="record.activo ? 'activo' : 'inactivo'">
                                            <span class="estado-dot-ultra"></span>
                                            <span>{{ record.activo ? 'Activo' : 'Inactivo' }}</span>
                                        </div>
                                    </div>
                                </template>

                                <template v-if="column.key === 'acciones'">
                                    <div class="acciones-ultra">
                                        <a-tooltip title="Ver detalles" placement="top" color="#1a3a5c">
                                            <Link :href="route('personas.show', record.id_persona)">
                                                <button class="btn-action-ultra btn-view-ultra">
                                                    <EyeOutlined />
                                                </button>
                                            </Link>
                                        </a-tooltip>

                                        <a-tooltip v-if="permisos?.puede_editar_personas" title="Editar" placement="top" color="#1a3a5c">
                                            <Link :href="route('personas.edit', record.id_persona)">
                                                <button class="btn-action-ultra btn-edit-ultra">
                                                    <EditOutlined />
                                                </button>
                                            </Link>
                                        </a-tooltip>

                                        <a-tooltip 
                                            v-if="!record.activo && permisos?.puede_editar_personas"
                                            title="Activar persona" 
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

                                        <a-tooltip 
                                            v-if="record.activo && permisos?.puede_editar_personas"
                                            title="Desactivar" 
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
                                    </div>
                                </template>
                            </template>
                        </a-table>
                    </div>

                    <!-- FILTROS INFERIOR - ALINEADOS PERFECTAMENTE -->
                    <div class="filtros-ultra-full">
                        <div class="filtros-grid-ultra-full">
                            <!-- Filtro Persona (COLUMNA 1) -->
                            <div class="filtro-item-ultra">
                                <InputLabel>Persona</InputLabel>
                                <TextInput 
                                    v-model="filtros.search"
                                    @input="aplicarFiltros"
                                    placeholder="Buscar..."
                                    square
                                />
                            </div>

                            <!-- Filtro Tipo (COLUMNA 2) -->
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
                                    <ASelectOption value="">Todos</ASelectOption>
                                    <ASelectOption value="FISICA">Física</ASelectOption>
                                    <ASelectOption value="MORAL">Moral</ASelectOption>
                                </a-select>
                            </div>

                            <!-- Filtro RFC (COLUMNA 3) -->
                            <div class="filtro-item-ultra">
                                <InputLabel>RFC</InputLabel>
                                <TextInput 
                                    v-model="filtros.rfc"
                                    @input="aplicarFiltros"
                                    placeholder="Buscar..."
                                    square
                                />
                            </div>

                            <!-- Filtro Ciudad (COLUMNA 4) -->
                            <div class="filtro-item-ultra">
                                <InputLabel>Ciudad</InputLabel>
                                <a-select
                                    v-model:value="filtros.ciudad"
                                    @change="aplicarFiltros"
                                    placeholder="Todas"
                                    allow-clear
                                    size="small"
                                    class="filtro-select-ultra"
                                    show-search
                                    :filter-option="filterOption"
                                >
                                    <ASelectOption value="">Todas</ASelectOption>
                                    <ASelectOption v-for="ciudad in ciudadesUnicas" :key="ciudad" :value="ciudad">
                                        {{ ciudad }}
                                    </ASelectOption>
                                </a-select>
                            </div>

                            <!-- Filtro Contacto (COLUMNA 5) -->
                            <div class="filtro-item-ultra">
                                <InputLabel>Contacto</InputLabel>
                                <TextInput 
                                    v-model="filtros.contacto"
                                    @input="aplicarFiltros"
                                    placeholder="Buscar..."
                                    square
                                />
                            </div>

                            <!-- Filtro Representante (COLUMNA 6) -->
                            <div class="filtro-item-ultra">
                                <InputLabel>Representante</InputLabel>
                                <TextInput 
                                    v-model="filtros.representante"
                                    @input="aplicarFiltros"
                                    placeholder="Buscar..."
                                    square
                                />
                            </div>

                            <!-- Filtro Estado (COLUMNA 7) -->
                            <div class="filtro-item-ultra">
                                <InputLabel>Estado</InputLabel>
                                <a-select
                                    v-model:value="filtros.estado"
                                    @change="aplicarFiltros"
                                    placeholder="Todos"
                                    allow-clear
                                    size="small"
                                    class="filtro-select-ultra"
                                >
                                    <ASelectOption :value="null">Todos</ASelectOption>
                                    <ASelectOption :value="true">Activo</ASelectOption>
                                    <ASelectOption :value="false">Inactivo</ASelectOption>
                                </a-select>
                            </div>

                            <!-- Botón Limpiar (COLUMNA 8 - Acciones) -->
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

                    <!-- Paginación -->
                    <div class="pagination-ultra">
                        <span class="pagination-info-ultra">
                            Mostrando <span class="pagination-highlight-ultra">{{ personas.from || 0 }}</span> a 
                            <span class="pagination-highlight-ultra">{{ personas.to || 0 }}</span> de 
                            <span class="pagination-highlight-ultra">{{ personas.total || 0 }}</span> resultados
                        </span>
                        <Pagination :links="personas.links" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Documentos -->
        <a-modal
            v-model:open="modalDocumentosVisible"
            :title="'Documentos de ' + (personaSeleccionada?.razon_social_display || personaSeleccionada?.nombre_completo || '')"
            width="680px"
            class="documentos-modal-premium"
            :footer="null"
            :closable="true"
            @cancel="cerrarModalDocumentos"
        >
            <!-- ... (contenido del modal igual que antes) ... -->
        </a-modal>
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
    FileOutlined,
    UploadOutlined,
    CloudUploadOutlined,
    DownloadOutlined,
    CheckOutlined,
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
    Modal as AModal,
} from 'ant-design-vue';

const ASelectOption = ASelect.Option;

// ============================================
// PERMISOS
// ============================================
const page = usePage();
const permisos = computed(() => page.props.permisos || {});

const props = defineProps({
    personas: Object,
    stats: Object,
    filtros: Object,
    flash: Object
});

const loading = ref(false);

// Estado para el modal de documentos
const modalDocumentosVisible = ref(false);
const personaSeleccionada = ref(null);
const subiendoDocumento = ref(false);
const archivoSeleccionado = ref(null);
const fileInput = ref(null);
const cargandoDocumentos = ref(false);

const nuevoDocumento = ref({
    tipo: '',
    titulo: '',
    finalizado: false,
});

// ============================================
// COLUMNAS DE LA TABLA - CON PORCENTAJES EXACTOS
// ============================================
const columns = [
    {
        title: 'Persona',
        key: 'persona',
        width: '18%',
        fixed: 'left'
    },
    {
        title: 'Tipo',
        key: 'tipo_persona',
        width: '8%'
    },
    {
        title: 'RFC',
        key: 'rfc',
        width: '10%'
    },
    {
        title: 'Ubicación',
        key: 'ubicacion',
        width: '12%'
    },
    {
        title: 'Contacto',
        key: 'contacto',
        width: '18%'
    },
    {
        title: 'Representante',
        key: 'representante',
        width: '12%'
    },
    {
        title: 'Estado',
        key: 'estado',
        width: '8%'
    },
    {
        title: 'Acciones',
        key: 'acciones',
        width: '10%',
        align: 'center',
        fixed: 'right'
    }
];

// ============================================
// FILTROS
// ============================================
const filtros = ref({
    search: props.filtros?.search || '',
    tipo_persona: props.filtros?.tipo_persona || '',
    rfc: props.filtros?.rfc || '',
    ciudad: props.filtros?.ciudad || '',
    contacto: props.filtros?.contacto || '',
    representante: props.filtros?.representante || '',
    estado: props.filtros?.estado !== undefined ? props.filtros.estado : null,
});

const filtrosActivos = computed(() => {
    return Object.values(filtros.value).some(value => value !== '' && value !== null && value !== undefined);
});

const ciudadesUnicas = computed(() => {
    if (!props.personas?.data) return [];
    const ciudades = props.personas.data
        .map(p => p.ciudad)
        .filter(c => c && c.trim() !== '');
    return [...new Set(ciudades)].sort();
});

const filterOption = (input, option) => {
    return option.value.toLowerCase().includes(input.toLowerCase());
};

let timeoutId = null;
const aplicarFiltros = () => {
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => {
        loading.value = true;
        const params = {};
        for (const [key, value] of Object.entries(filtros.value)) {
            if (value !== '' && value !== null && value !== undefined) {
                if (key === 'estado' && value !== null) {
                    params[key] = value ? 'activo' : 'inactivo';
                } else {
                    params[key] = value;
                }
            }
        }
        
        router.get(route('personas.index'), params, {
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
        tipo_persona: '',
        rfc: '',
        ciudad: '',
        contacto: '',
        representante: '',
        estado: null,
    };
    aplicarFiltros();
};

// ============================================
// FUNCIONES PARA DOCUMENTOS
// ============================================
const abrirModalDocumentos = async (record) => {
    personaSeleccionada.value = record;
    modalDocumentosVisible.value = true;
    nuevoDocumento.value = { tipo: '', titulo: '', finalizado: false };
    archivoSeleccionado.value = null;
    if (fileInput.value) fileInput.value.value = '';
    await cargarDocumentos(record.id_persona);
};

const cerrarModalDocumentos = () => {
    modalDocumentosVisible.value = false;
    personaSeleccionada.value = null;
    nuevoDocumento.value = { tipo: '', titulo: '', finalizado: false };
    archivoSeleccionado.value = null;
    subiendoDocumento.value = false;
    if (fileInput.value) fileInput.value.value = '';
};

const cargarDocumentos = async (personaId) => {
    cargandoDocumentos.value = true;
    try {
        const response = await axios.get(route('personas.documentos', personaId));
        if (response.data.success) {
            personaSeleccionada.value.documentos = response.data.data;
            personaSeleccionada.value.total_documentos = response.data.data.length;
        }
    } catch (error) {
        console.error('Error al cargar documentos:', error);
    } finally {
        cargandoDocumentos.value = false;
    }
};

const handleFileSelect = (event) => {
    const file = event.target.files[0];
    if (file) {
        if (file.size > 5 * 1024 * 1024) {
            Swal.fire({
                title: 'Archivo muy grande',
                text: 'El archivo no debe superar los 5MB.',
                icon: 'error',
                confirmButtonColor: '#ef4444'
            });
            event.target.value = '';
            return;
        }
        archivoSeleccionado.value = file;
    }
};

const removerArchivo = () => {
    archivoSeleccionado.value = null;
    if (fileInput.value) fileInput.value.value = '';
};

const getColorDocumento = (tipo) => {
    const colores = {
        'INE': '#3b82f6',
        'RFC': '#8b5cf6',
        'CURP': '#10b981',
        'COMPROBANTE': '#f59e0b',
        'OTRO': '#6b7280'
    };
    return colores[tipo] || '#6b7280';
};

const formatFecha = (fecha) => {
    if (!fecha) return '';
    const d = new Date(fecha);
    return d.toLocaleDateString('es-MX', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const subirDocumento = async () => {
    if (!archivoSeleccionado.value || !nuevoDocumento.value.tipo) {
        return;
    }

    subiendoDocumento.value = true;
    const formData = new FormData();
    formData.append('documento', archivoSeleccionado.value);
    formData.append('tipo_documento', nuevoDocumento.value.tipo);
    formData.append('titulo', nuevoDocumento.value.titulo || `${nuevoDocumento.value.tipo} - ${personaSeleccionada.value.nombre_completo}`);
    formData.append('finalizado', nuevoDocumento.value.finalizado ? '1' : '0');

    try {
        await router.post(
            route('personas.subir-documento', personaSeleccionada.value.id_persona),
            formData,
            {
                preserveScroll: true,
                onSuccess: () => {
                    subiendoDocumento.value = false;
                    archivoSeleccionado.value = null;
                    nuevoDocumento.value = { tipo: '', titulo: '', finalizado: false };
                    if (fileInput.value) fileInput.value.value = '';
                    cargarDocumentos(personaSeleccionada.value.id_persona);
                    
                    Swal.fire({
                        title: 'Documento subido',
                        text: 'El documento se ha subido exitosamente.',
                        icon: 'success',
                        confirmButtonColor: '#1a3a5c',
                        timer: 2500,
                        timerProgressBar: true
                    });
                },
                onError: (errors) => {
                    subiendoDocumento.value = false;
                    Swal.fire({
                        title: 'Error',
                        text: Object.values(errors).flat()[0] || 'Error al subir el documento',
                        icon: 'error',
                        confirmButtonColor: '#ef4444'
                    });
                }
            }
        );
    } catch (error) {
        subiendoDocumento.value = false;
        Swal.fire({
            title: 'Error',
            text: error.message || 'Error al subir el documento',
            icon: 'error',
            confirmButtonColor: '#ef4444'
        });
    }
};

const descargarDocumento = (doc) => {
    window.open(route('personas.descargar-documento', doc.id), '_blank');
};

const toggleFinalizado = (doc) => {
    router.patch(route('personas.toggle-finalizado', doc.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            cargarDocumentos(personaSeleccionada.value.id_persona);
            Swal.fire({
                title: 'Estado actualizado',
                text: 'El estado del documento ha sido actualizado.',
                icon: 'success',
                confirmButtonColor: '#1a3a5c',
                timer: 2000,
                timerProgressBar: true
            });
        },
        onError: (errors) => {
            Swal.fire({
                title: 'Error',
                text: errors?.error || 'Error al actualizar el estado',
                icon: 'error',
                confirmButtonColor: '#ef4444'
            });
        }
    });
};

const eliminarDocumento = (doc) => {
    Swal.fire({
        title: 'Eliminar documento',
        html: `
            <div class="text-center">
                <p class="font-medium text-gray-700">${doc.tipo_documento_texto || doc.tipo_documento}</p>
                <p class="text-sm text-red-600 mt-2 font-medium">Esta acción no se puede deshacer</p>
                <p class="text-xs text-gray-500 mt-1">El archivo se eliminará permanentemente</p>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('personas.eliminar-documento', doc.id), {
                preserveScroll: true,
                onSuccess: () => {
                    cargarDocumentos(personaSeleccionada.value.id_persona);
                    Swal.fire({
                        title: 'Documento eliminado',
                        text: 'El documento se ha eliminado correctamente.',
                        icon: 'success',
                        confirmButtonColor: '#1a3a5c'
                    });
                },
                onError: (errors) => {
                    Swal.fire({
                        title: 'Error',
                        text: errors?.error || 'Error al eliminar el documento',
                        icon: 'error',
                        confirmButtonColor: '#ef4444'
                    });
                }
            });
        }
    });
};

// ============================================
// FUNCIONES PARA ACTIVAR/DESACTIVAR
// ============================================
const confirmarDesactivar = (persona) => {
    const nombre = persona.razon_social_display || persona.nombre_completo || 'Persona';
    
    Swal.fire({
        title: 'Desactivar persona',
        html: `
            <div class="text-center">
                <div class="flex justify-center mb-3">
                    <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </div>
                </div>
                <p class="font-medium text-gray-800 text-lg">${nombre}</p>
                <div class="mt-4 p-3 bg-amber-50 rounded-lg border border-amber-200">
                    <p class="text-sm text-amber-700 font-medium">La persona quedará <strong>inactiva</strong></p>
                    <p class="text-xs text-amber-600 mt-1">Podrás reactivarla más tarde desde el listado</p>
                </div>
                ${persona.polizas_count > 0 || persona.total_documentos > 0 ? `
                    <div class="mt-3 p-2 bg-red-50 rounded-lg border border-red-200">
                        <p class="text-xs text-red-600">Tiene ${persona.polizas_count > 0 ? persona.polizas_count + ' póliza(s)' : ''} 
                        ${persona.polizas_count > 0 && persona.total_documentos > 0 ? ' y ' : ''} 
                        ${persona.total_documentos > 0 ? persona.total_documentos + ' documento(s)' : ''} asociados</p>
                    </div>
                ` : ''}
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, desactivar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            procesarCambioEstado(persona, false);
        }
    });
};

const confirmarActivar = (persona) => {
    const nombre = persona.razon_social_display || persona.nombre_completo || 'Persona';
    
    Swal.fire({
        title: 'Activar persona',
        html: `
            <div class="text-center">
                <div class="flex justify-center mb-3">
                    <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </div>
                </div>
                <p class="font-medium text-gray-800 text-lg">${nombre}</p>
                <div class="mt-4 p-3 bg-green-50 rounded-lg border border-green-200">
                    <p class="text-sm text-green-700 font-medium">La persona volverá a estar <strong>activa</strong></p>
                </div>
            </div>
        `,
        icon: 'success',
        showCancelButton: true,
        confirmButtonText: 'Sí, activar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#64748b',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            procesarCambioEstado(persona, true);
        }
    });
};

const procesarCambioEstado = (persona, nuevoEstado) => {
    const nombre = persona.razon_social_display || persona.nombre_completo || 'Persona';
    const accionTexto = nuevoEstado ? 'activada' : 'desactivada';
    
    Swal.fire({
        title: 'Procesando...',
        html: `
            <div class="text-center py-4">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                <p class="mt-3 font-medium text-gray-700">${nuevoEstado ? 'Activando' : 'Desactivando'} persona</p>
                <p class="text-sm text-gray-500">Por favor espera un momento</p>
            </div>
        `,
        allowOutsideClick: false,
        showConfirmButton: false
    });
    
    const url = route('personas.toggle-active', persona.id_persona);
    
    axios.post(url)
        .then(response => {
            Swal.fire({
                title: 'Completado',
                html: `
                    <div class="text-center">
                        <div class="flex justify-center mb-3">
                            <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center">
                                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                        </div>
                        <p class="text-gray-700">Persona <strong>${nombre}</strong></p>
                        <p class="text-sm text-green-600 mt-1">${accionTexto} exitosamente</p>
                    </div>
                `,
                icon: 'success',
                confirmButtonText: 'Aceptar',
                confirmButtonColor: '#1a3a5c',
                timer: 3000,
                timerProgressBar: true
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
                text: errorMsg,
                icon: 'error',
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#ef4444'
            });
        });
};

// ============================================
// PROCESAR FLASH
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
</script>

<style scoped>
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

/* ===== BOTÓN NUEVA PERSONA ===== */
.btn-nueva-persona-premium {
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

.btn-nueva-persona-premium:hover {
    transform: translateY(-3px) scale(1.02);
    box-shadow: 0 8px 30px rgba(26, 58, 92, 0.4) !important;
    background: linear-gradient(135deg, #2c5282 0%, #1a3a5c 100%) !important;
}

/* ===== STATS CARDS ===== */
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

/* ===== TABLA ===== */
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

.table-title-ultra {
    font-size: 17px;
    font-weight: 700;
    color: #0f172a;
}

.resultados-count-ultra {
    font-size: 13px;
    font-weight: 500;
    color: #94a3b8;
    background: #f1f5f9;
    padding: 2px 12px;
    border-radius: 20px;
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

/* ===== CONTENEDOR DE TABLA CON SCROLL ===== */
.table-scroll-container-full {
    flex: 1;
    overflow-y: auto;
    overflow-x: auto;
    border-radius: 8px;
    border: 1px solid #f1f5f9;
    min-height: 200px;
    max-height: 500px;
}

.table-scroll-container-full::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}

.table-scroll-container-full::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 4px;
}

.table-scroll-container-full::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}

.table-scroll-container-full::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* ===== TABLA ANT DESIGN ===== */
.persona-table-ultra {
    width: 100%;
}

.persona-table-ultra :deep(.ant-table) {
    border-radius: 0;
}

.persona-table-ultra :deep(.ant-table-thead > tr > th) {
    background: linear-gradient(135deg, #f1f5f9, #e8edf2) !important;
    font-weight: 700;
    color: #1e293b;
    border-bottom: 2px solid #d1d5db;
    padding: 10px 12px;
    font-size: 11px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    white-space: nowrap;
    position: sticky;
    top: 0;
    z-index: 10;
}

.persona-table-ultra :deep(.ant-table-thead > tr > th:first-child) {
    border-radius: 0 !important;
}

.persona-table-ultra :deep(.ant-table-thead > tr > th:last-child) {
    border-radius: 0 !important;
}

.persona-table-ultra :deep(.ant-table-tbody > tr) {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.persona-table-ultra :deep(.ant-table-tbody > tr:hover) {
    background: linear-gradient(90deg, #f8faff, #f0f7ff) !important;
}

.persona-table-ultra :deep(.ant-table-tbody > tr:last-child td) {
    border-bottom: none;
}

.persona-table-ultra :deep(.ant-table-cell) {
    padding: 8px 10px;
    border-bottom: 1px solid #f1f5f9;
    font-size: 13px;
}

/* ===== CELDAS ===== */
.persona-cell-ultra {
    display: flex;
    align-items: center;
}

.persona-info-ultra {
    display: flex;
    flex-direction: column;
    gap: 2px;
    width: 100%;
}

.persona-nombre-ultra {
    font-size: 14px;
    font-weight: 600;
    color: #0f172a;
}

.persona-rfc-ultra {
    font-size: 11px;
    color: #94a3b8;
}

.tipo-cell-ultra {
    display: flex;
    align-items: center;
}

.tipo-badge {
    display: inline-block;
    padding: 3px 12px;
    border-radius: 4px;
    font-size: 11px;
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

.rfc-cell-ultra {
    display: flex;
    align-items: center;
}

.rfc-text-ultra {
    font-size: 13px;
    font-weight: 500;
    color: #1a3a5c;
    font-family: 'Courier New', monospace;
}

.ubicacion-cell-ultra {
    display: flex;
    align-items: center;
}

.ubicacion-text-ultra {
    font-size: 12px;
    color: #475569;
}

.contacto-cell-ultra {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.contacto-email-ultra {
    color: #3b82f6;
    font-size: 12px;
    text-decoration: none;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.contacto-email-ultra:hover {
    color: #2563eb;
    text-decoration: underline;
}

.contacto-telefono-ultra {
    color: #475569;
    font-size: 12px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.contacto-icon-ultra {
    font-size: 13px !important;
}

.contacto-vacio-ultra {
    color: #cbd5e1;
    font-size: 12px;
    font-style: italic;
}

.representante-cell-ultra {
    display: flex;
    align-items: center;
}

.representante-nombre-ultra {
    font-size: 12px;
    color: #0f172a;
    font-weight: 500;
}

.representante-vacio-ultra {
    color: #cbd5e1;
    font-size: 12px;
}

.estado-cell-ultra {
    display: flex;
    align-items: center;
}

.estado-badge-ultra {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 3px 12px;
    border-radius: 4px;
    font-size: 11px;
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
    width: 6px;
    height: 6px;
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

/* ===== ACCIONES ===== */
.acciones-ultra {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
}

.btn-action-ultra {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
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

/* ===== FILTROS INFERIOR - ALINEADOS PERFECTAMENTE ===== */
.filtros-ultra-full {
    margin-top: 16px;
    padding-top: 16px;
    border-top: 2px solid #f1f5f9;
}

.filtros-grid-ultra-full {
    display: grid;
    grid-template-columns: 18% 8% 10% 12% 18% 12% 8% 10%;
    gap: 4px;
    align-items: end;
    width: 100%;
}

@media (max-width: 1400px) {
    .filtros-grid-ultra-full {
        grid-template-columns: 1fr 1fr 1fr 1fr;
        gap: 6px;
    }
    .filtro-actions {
        grid-column: 1 / -1;
    }
}

@media (max-width: 992px) {
    .filtros-grid-ultra-full {
        grid-template-columns: 1fr 1fr 1fr;
        gap: 6px;
    }
    .filtro-actions {
        grid-column: 1 / -1;
    }
}

@media (max-width: 768px) {
    .filtros-grid-ultra-full {
        grid-template-columns: 1fr 1fr;
        gap: 6px;
    }
    .filtro-actions {
        grid-column: 1 / -1;
    }
}

@media (max-width: 480px) {
    .filtros-grid-ultra-full {
        grid-template-columns: 1fr;
        gap: 4px;
    }
    .filtro-actions {
        grid-column: 1;
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
    width: 100% !important;
}

.filtro-item-ultra :deep(.text-input:focus) {
    border-color: #1a3a5c !important;
    box-shadow: 0 0 0 3px rgba(26, 58, 92, 0.1) !important;
}

.filtro-actions {
    min-width: 90px;
}

.filtro-select-ultra {
    width: 100% !important;
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

/* ===== ANIMACIONES ===== */
@keyframes pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(1.2); }
}
</style>