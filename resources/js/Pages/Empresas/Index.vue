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
                            <h2 class="header-title-premium">Gestión de Empresas</h2>
                            <p class="header-subtitle-premium">
                                <span class="subtitle-line"></span>
                                Administra las empresas del sistema
                            </p>
                        </div>
                    </div>
                    <Link v-if="permisos?.puede_crear_empresas" :href="route('empresas.create')">
                        <button type="button" class="btn-nueva-empresa-premium">
                            <i class="pi pi-plus"></i>
                            Nueva Empresa
                        </button>
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-full px-4 sm:px-6 lg:px-8">
                <!-- Stats Cards -->
                <div class="stats-grid-empresas mb-6">
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

                    <div class="stats-card-enhanced">
                        <div class="stats-card-enhanced-content">
                            <div class="stats-card-enhanced-left">
                                <span class="stats-card-enhanced-label">Activas</span>
                                <span class="stats-card-enhanced-value" style="color: #2e7d32;">{{ stats?.activas || 0 }}</span>
                            </div>
                            <div class="stats-card-enhanced-icon" style="background: linear-gradient(135deg, rgba(46, 125, 50, 0.1), rgba(46, 125, 50, 0.05));">
                                <i class="pi pi-check-circle" style="color: #2e7d32; font-size: 24px;"></i>
                            </div>
                        </div>
                        <div class="stats-card-enhanced-progress">
                            <div class="stats-card-enhanced-progress-bar" :style="{ width: stats?.total ? Math.round((stats.activas / stats.total) * 100) + '%' : '0%', background: 'linear-gradient(90deg, #2e7d32, #43a047)' }"></div>
                        </div>
                    </div>

                    <div class="stats-card-enhanced">
                        <div class="stats-card-enhanced-content">
                            <div class="stats-card-enhanced-left">
                                <span class="stats-card-enhanced-label">Inactivas</span>
                                <span class="stats-card-enhanced-value" style="color: #c62828;">{{ stats?.inactivas || 0 }}</span>
                            </div>
                            <div class="stats-card-enhanced-icon" style="background: linear-gradient(135deg, rgba(198, 40, 40, 0.1), rgba(198, 40, 40, 0.05));">
                                <i class="pi pi-times-circle" style="color: #c62828; font-size: 24px;"></i>
                            </div>
                        </div>
                        <div class="stats-card-enhanced-progress">
                            <div class="stats-card-enhanced-progress-bar" :style="{ width: stats?.total ? Math.round((stats.inactivas / stats.total) * 100) + '%' : '0%', background: 'linear-gradient(90deg, #c62828, #e53935)' }"></div>
                        </div>
                    </div>

                    <div class="stats-card-enhanced">
                        <div class="stats-card-enhanced-content">
                            <div class="stats-card-enhanced-left">
                                <span class="stats-card-enhanced-label">Tipo Persona</span>
                                <span class="stats-card-enhanced-value" style="color: #1a3a5c; font-size: 22px;">{{ stats?.fisicas || 0 }}F / {{ stats?.morales || 0 }}M</span>
                            </div>
                            <div class="stats-card-enhanced-icon" style="background: linear-gradient(135deg, rgba(26, 58, 92, 0.1), rgba(26, 58, 92, 0.05));">
                                <i class="pi pi-users" style="color: #1a3a5c; font-size: 24px;"></i>
                            </div>
                        </div>
                        <div class="stats-card-enhanced-progress">
                            <div class="stats-card-enhanced-progress-bar" :style="{ width: stats?.total ? Math.round(((stats.fisicas || 0) / stats.total) * 100) + '%' : '0%', background: 'linear-gradient(90deg, #1a3a5c, #3d6ea5)' }"></div>
                        </div>
                    </div>
                </div>

                <!-- Tabla Premium -->
                <div class="table-wrapper-premium">
                    <div class="table-header-ultra">
                        <div class="table-header-left-ultra">
                            <span class="table-title-ultra">Listado de Empresas</span>
                            <span v-if="filtrosActivos" class="filter-tag-ultra">
                                <span class="filter-dot-active"></span>
                                Filtros activos
                            </span>
                        </div>
                        <div class="table-header-right-ultra">
                            <button v-if="filtrosActivos" type="button" @click="limpiarFiltros" class="btn-limpiar-ultra">
                                <i class="pi pi-times"></i>
                                Limpiar filtros
                            </button>
                        </div>
                    </div>

                    <div class="table-scroll-container-full">
                        <DataTable
                            :value="empresas.data"
                            :loading="loading"
                            data-key="id"
                            scrollable
                            scroll-height="400px"
                            row-hover
                            table-style="min-width: 60rem"
                            class="empresa-table-ultra"
                        >
                            <template #empty>
                                <div class="tabla-vacia">No se encontraron empresas.</div>
                            </template>

                            <Column header="Empresa" style="width: 20%" frozen>
                                <template #body="{ data }">
                                    <div class="empresa-cell-ultra">
                                        <div class="avatar-container-ultra">
                                            <div
                                                class="avatar-ultra"
                                                :style="{ background: data.activo ? 'linear-gradient(135deg, #1a3a5c, #2c5282)' : 'linear-gradient(135deg, #94a3b8, #64748b)' }"
                                            >
                                                {{ data.nombre_empresa?.charAt(0) || 'E' }}
                                            </div>
                                            <div class="avatar-ring" :class="data.activo ? 'active' : 'inactive'"></div>
                                        </div>
                                        <div class="empresa-info-ultra">
                                            <p class="empresa-nombre-ultra">{{ data.nombre_empresa }}</p>
                                            <p class="empresa-clave-ultra">Clave: {{ data.clave || 'N/A' }}</p>
                                        </div>
                                    </div>
                                </template>
                            </Column>

                            <Column header="RFC" field="rfc" style="width: 14%" />

                            <Column header="Tipo" style="width: 10%">
                                <template #body="{ data }">
                                    <span class="tipo-badge" :class="data.tipo_persona === 'MORAL' ? 'moral' : 'fisica'">
                                        {{ data.tipo_persona === 'MORAL' ? 'Moral' : 'Física' }}
                                    </span>
                                </template>
                            </Column>

                            <Column header="Ubicación" style="width: 16%">
                                <template #body="{ data }">
                                    <span class="ubicacion-text-ultra">
                                        {{ data.ciudad }}{{ data.ciudad && data.estado ? ', ' : '' }}{{ data.estado || 'Sin ubicación' }}
                                    </span>
                                </template>
                            </Column>

                            <Column header="Contacto" style="width: 18%">
                                <template #body="{ data }">
                                    <div class="contacto-cell-ultra">
                                        <a v-if="data.correo" :href="`mailto:${data.correo}`" class="contacto-email-ultra">
                                            <i class="pi pi-envelope contacto-icon-ultra"></i>
                                            <span>{{ data.correo }}</span>
                                        </a>
                                        <span v-if="data.telefono_personal" class="contacto-telefono-ultra">
                                            <i class="pi pi-phone contacto-icon-ultra"></i>
                                            <span>{{ data.telefono_personal }}</span>
                                        </span>
                                        <span v-if="!data.correo && !data.telefono_personal" class="contacto-vacio-ultra">
                                            Sin contacto
                                        </span>
                                    </div>
                                </template>
                            </Column>

                            <Column header="Estado" style="width: 10%">
                                <template #body="{ data }">
                                    <div class="estado-badge-ultra" :class="data.activo ? 'activo' : 'inactivo'">
                                        <span class="estado-dot-ultra"></span>
                                        <span>{{ data.activo ? 'Activa' : 'Inactiva' }}</span>
                                    </div>
                                </template>
                            </Column>

                            <Column header="Acciones" style="width: 12%" align-frozen="right" frozen>
                                <template #body="{ data }">
                                    <div class="acciones-ultra">
                                        <Link v-if="permisos?.puede_editar_empresas" :href="route('empresas.edit', data.id)">
                                            <button v-tooltip.top="'Editar'" class="btn-action-ultra btn-edit-ultra">
                                                <i class="pi pi-pencil"></i>
                                            </button>
                                        </Link>

                                        <button
                                            v-if="data.activo && permisos?.puede_editar_empresas"
                                            v-tooltip.top="'Desactivar empresa'"
                                            class="btn-action-ultra btn-delete-ultra"
                                            @click="confirmarDesactivar(data)"
                                        >
                                            <i class="pi pi-trash"></i>
                                        </button>

                                        <button
                                            v-if="!data.activo && permisos?.puede_editar_empresas"
                                            v-tooltip.top="'Activar empresa'"
                                            class="btn-action-ultra btn-activate-ultra"
                                            @click="confirmarActivar(data)"
                                        >
                                            <i class="pi pi-check-circle"></i>
                                        </button>
                                    </div>
                                </template>
                            </Column>
                        </DataTable>
                    </div>

                    <!-- FILTROS INFERIOR -->
                    <div class="filtros-ultra-full">
                        <div class="filtros-grid-ultra-full">
                            <div class="filtro-item-ultra">
                                <InputLabel>Nombre</InputLabel>
                                <TextInput v-model="filtros.search" @input="aplicarFiltros" placeholder="Buscar..." square />
                            </div>

                            <div class="filtro-item-ultra">
                                <InputLabel>RFC</InputLabel>
                                <TextInput v-model="filtros.rfc" @input="aplicarFiltros" placeholder="Buscar..." square />
                            </div>

                            <div class="filtro-item-ultra">
                                <InputLabel>Tipo</InputLabel>
                                <Select
                                    v-model="filtros.tipo_persona"
                                    :options="tipoOptions"
                                    option-label="label"
                                    option-value="value"
                                    placeholder="Todos"
                                    show-clear
                                    class="filtro-select-ultra"
                                    @change="aplicarFiltros"
                                />
                            </div>

                            <div class="filtro-item-ultra">
                                <InputLabel>Ubicación</InputLabel>
                                <Select
                                    v-model="filtros.estado"
                                    :options="estados"
                                    placeholder="Todos"
                                    show-clear
                                    filter
                                    class="filtro-select-ultra"
                                    @change="aplicarFiltros"
                                />
                            </div>

                            <div class="filtro-item-ultra">
                                <InputLabel>Contacto</InputLabel>
                                <TextInput v-model="filtros.contacto" @input="aplicarFiltros" placeholder="Email/Teléfono..." square />
                            </div>

                            <div class="filtro-item-ultra">
                                <InputLabel>Estado</InputLabel>
                                <Select
                                    v-model="filtros.activo"
                                    :options="activoOptions"
                                    option-label="label"
                                    option-value="value"
                                    placeholder="Todos"
                                    show-clear
                                    class="filtro-select-ultra"
                                    @change="aplicarFiltros"
                                />
                            </div>

                            <div class="filtro-item-ultra filtro-actions">
                                <InputLabel>Acciones</InputLabel>
                                <button v-if="filtrosActivos" type="button" @click="limpiarFiltros" class="btn-clear-ultra">
                                    <i class="pi pi-times"></i>
                                    Limpiar filtros
                                </button>
                                <button v-else type="button" disabled class="btn-no-filtros-ultra">
                                    <span class="no-filtros-text-ultra">Sin filtros</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="pagination-ultra">
                        <span class="pagination-info-ultra">Mostrando <span class="pagination-highlight-ultra">{{ empresas.from || 0 }}</span> a
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
import { ref, computed } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Select from 'primevue/select';
import Pagination from '@/Components/Pagination.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { useNotify } from '@/composables/useNotify';
import axios from 'axios';

const page = usePage();
const permisos = computed(() => page.props.permisos || {});
const notify = useNotify();

const props = defineProps({
    empresas: Object,
    stats: Object,
    estados: Array,
    filtros: Object,
    flash: Object,
});

const loading = ref(false);

const tipoOptions = [
    { label: 'Física', value: 'FISICA' },
    { label: 'Moral', value: 'MORAL' },
];

const activoOptions = [
    { label: 'Activas', value: 'true' },
    { label: 'Inactivas', value: 'false' },
];

const filtros = ref({
    search: props.filtros?.search || '',
    rfc: props.filtros?.rfc || '',
    tipo_persona: props.filtros?.tipo_persona || null,
    estado: props.filtros?.estado || null,
    contacto: props.filtros?.contacto || '',
    activo: props.filtros?.activo || null,
});

const filtrosActivos = computed(() =>
    Object.values(filtros.value).some(value => value !== '' && value !== null && value !== undefined),
);

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
            onFinish: () => { loading.value = false; },
        });
    }, 300);
};

const limpiarFiltros = () => {
    filtros.value = {
        search: '',
        rfc: '',
        tipo_persona: null,
        estado: null,
        contacto: '',
        activo: null,
    };
    aplicarFiltros();
};

const confirmarDesactivar = (empresa) => {
    if (!permisos.value?.puede_editar_empresas) {
        notify.error('No tienes permisos para desactivar empresas. Contacta al administrador.', 'Sin permisos');
        return;
    }

    const nombre = empresa.nombre_empresa;
    let extra = '';
    if (empresa.usuarios_count > 0 || empresa.polizas_count > 0) {
        const partes = [];
        if (empresa.usuarios_count > 0) partes.push(`${empresa.usuarios_count} usuario(s)`);
        if (empresa.polizas_count > 0) partes.push(`${empresa.polizas_count} póliza(s)`);
        extra = ` Tiene ${partes.join(' y ')} asociados.`;
    }

    notify.confirmDelete({
        header: 'Desactivar empresa',
        message: `La empresa "${nombre}" será desactivada. Podrás reactivarla en cualquier momento.${extra}`,
        acceptLabel: 'Sí, desactivar',
        accept: () => procesarCambioEstado(empresa, false),
    });
};

const confirmarActivar = (empresa) => {
    if (!permisos.value?.puede_editar_empresas) {
        notify.error('No tienes permisos para activar empresas. Contacta al administrador.', 'Sin permisos');
        return;
    }

    notify.confirmAction({
        header: 'Activar empresa',
        message: `La empresa "${empresa.nombre_empresa}" será activada. Podrás desactivarla en cualquier momento.`,
        acceptLabel: 'Sí, activar',
        acceptSeverity: 'success',
        icon: 'pi pi-check-circle',
        accept: () => procesarCambioEstado(empresa, true),
    });
};

const procesarCambioEstado = (empresa, nuevoEstado) => {
    const nombre = empresa.nombre_empresa;
    const accionTexto = nuevoEstado ? 'activada' : 'desactivada';

    loading.value = true;
    axios.post(route('empresas.toggle-active', empresa.id))
        .then(() => {
            notify.success(`Empresa "${nombre}" ${accionTexto} exitosamente`);
            router.reload({ onFinish: () => { loading.value = false; } });
        })
        .catch(error => {
            loading.value = false;
            const errorMsg = error.response?.data?.flash?.error
                || error.response?.data?.message
                || error.message
                || 'Error al cambiar el estado';
            notify.error(errorMsg);
        });
};
</script>

<style scoped>
/* ===== HEADER PREMIUM ===== */
.header-premium {
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    border-radius: 20px;
    padding: 20px 24px;
    margin-bottom: 8px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
    border: 1px solid #f0f2f5;
}

.header-content-premium {
    display: flex;
    flex-direction: column;
    gap: 12px;
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
    gap: 14px;
}

.header-icon-wrapper {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, #1a3a5c, #2c5282);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(26, 58, 92, 0.2);
}

.header-icon-svg {
    width: 24px;
    height: 24px;
    stroke: white;
}

.header-title-premium {
    font-size: 20px;
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
    font-size: 13px;
    margin: 2px 0 0 0;
}

.subtitle-line {
    width: 20px;
    height: 2px;
    background: linear-gradient(90deg, #1a3a5c, transparent);
    border-radius: 2px;
}

.btn-nueva-empresa-premium {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: linear-gradient(135deg, #1a3a5c 0%, #2c5282 100%);
    border: none;
    color: #fff;
    border-radius: 6px;
    font-weight: 600;
    padding: 0 24px;
    height: 40px;
    cursor: pointer;
    box-shadow: 0 4px 16px rgba(26, 58, 92, 0.3);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    font-size: 14px;
    letter-spacing: 0.3px;
}

.btn-nueva-empresa-premium:hover {
    transform: translateY(-2px) scale(1.02);
    box-shadow: 0 8px 30px rgba(26, 58, 92, 0.4);
    background: linear-gradient(135deg, #2c5282 0%, #1a3a5c 100%);
}

/* ===== STATS CARDS ===== */
.stats-grid-empresas {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
}

@media (max-width: 992px) {
    .stats-grid-empresas { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 560px) {
    .stats-grid-empresas { grid-template-columns: 1fr; }
}

.stats-card-enhanced {
    background: #ffffff;
    border-radius: 14px;
    border: 1px solid #f0f2f5;
    overflow: hidden;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.stats-card-enhanced:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
    border-color: #d0d7de;
}

.stats-card-enhanced-content {
    padding: 16px 16px 12px;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
}

.stats-card-enhanced-left {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.stats-card-enhanced-label {
    font-size: 12px;
    font-weight: 500;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stats-card-enhanced-value {
    font-size: 24px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.2;
}

.stats-card-enhanced-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
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
    width: 22px;
    height: 22px;
}

.stats-card-enhanced-progress {
    height: 3px;
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
    padding: 16px;
}

.table-header-ultra {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-bottom: 14px;
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

.table-title-ultra {
    font-size: 16px;
    font-weight: 700;
    color: #0f172a;
}

.table-header-right-ultra {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.filter-tag-ultra {
    border-radius: 30px;
    background: linear-gradient(135deg, #eff6ff, #dbeafe);
    color: #1a3a5c;
    font-weight: 600;
    padding: 2px 14px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
}

.filter-dot-active {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #1a3a5c;
    display: inline-block;
    animation: pulse 2s infinite;
}

.btn-limpiar-ultra {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border-radius: 4px;
    color: #64748b;
    border: 2px solid #d1d5db;
    transition: all 0.3s ease;
    height: 34px;
    padding: 0 14px;
    font-weight: 600;
    background: #ffffff;
    font-size: 12px;
    cursor: pointer;
}

.btn-limpiar-ultra:hover {
    color: #1a3a5c;
    border-color: #1a3a5c;
    background: #f8faff;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(26, 58, 92, 0.1);
}

/* ===== CONTENEDOR DE TABLA ===== */
.table-scroll-container-full {
    border-radius: 8px;
    border: 1px solid #f1f5f9;
    overflow: hidden;
}

.tabla-vacia {
    padding: 32px 16px;
    text-align: center;
    color: #94a3b8;
    font-size: 14px;
}

/* ===== DATATABLE ===== */
.empresa-table-ultra :deep(.p-datatable-thead > tr > th) {
    background: linear-gradient(135deg, #f1f5f9, #e8edf2);
    font-weight: 700;
    color: #1e293b;
    border-bottom: 2px solid #d1d5db;
    padding: 8px 12px;
    font-size: 11px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    white-space: nowrap;
}

.empresa-table-ultra :deep(.p-datatable-tbody > tr) {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.empresa-table-ultra :deep(.p-datatable-tbody > tr:hover) {
    background: linear-gradient(90deg, #f8faff, #f0f7ff);
}

.empresa-table-ultra :deep(.p-datatable-tbody > tr:last-child > td) {
    border-bottom: none;
}

.empresa-table-ultra :deep(.p-datatable-tbody > tr > td) {
    padding: 8px 12px;
    border-bottom: 1px solid #f1f5f9;
    font-size: 13px;
}

.empresa-table-ultra :deep(.p-datatable-tbody > tr > td.p-frozen-column),
.empresa-table-ultra :deep(.p-datatable-thead > tr > th.p-frozen-column) {
    background: #ffffff;
}

/* ===== CELDAS ===== */
.empresa-cell-ultra {
    display: flex;
    align-items: center;
    gap: 12px;
}

.avatar-container-ultra {
    position: relative;
}

.avatar-ultra {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-weight: 700;
    font-size: 14px;
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
    gap: 1px;
}

.empresa-nombre-ultra {
    font-size: 13px;
    font-weight: 600;
    color: #0f172a;
    margin: 0;
}

.empresa-clave-ultra {
    font-size: 11px;
    color: #94a3b8;
    margin: 0;
}

.tipo-badge {
    display: inline-block;
    padding: 2px 12px;
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

.ubicacion-text-ultra {
    font-size: 12px;
    color: #475569;
}

.contacto-cell-ultra {
    display: flex;
    flex-direction: column;
    gap: 2px;
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
    font-size: 12px;
}

.contacto-vacio-ultra {
    color: #cbd5e1;
    font-size: 12px;
    font-style: italic;
}

.estado-badge-ultra {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 2px 12px;
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
    width: 30px;
    height: 30px;
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

/* ===== FILTROS INFERIOR ===== */
.filtros-ultra-full {
    margin-top: 14px;
    padding-top: 14px;
    border-top: 2px solid #f1f5f9;
}

.filtros-grid-ultra-full {
    display: grid;
    grid-template-columns: 20% 14% 10% 16% 18% 10% 12%;
    gap: 4px;
    align-items: end;
    width: 100%;
}

@media (max-width: 1400px) {
    .filtros-grid-ultra-full {
        grid-template-columns: 1fr 1fr 1fr 1fr;
        gap: 6px;
    }
    .filtro-actions { grid-column: 1 / -1; }
}

@media (max-width: 992px) {
    .filtros-grid-ultra-full {
        grid-template-columns: 1fr 1fr 1fr;
        gap: 6px;
    }
    .filtro-actions { grid-column: 1 / -1; }
}

@media (max-width: 768px) {
    .filtros-grid-ultra-full {
        grid-template-columns: 1fr 1fr;
        gap: 6px;
    }
    .filtro-actions { grid-column: 1 / -1; }
}

@media (max-width: 480px) {
    .filtros-grid-ultra-full {
        grid-template-columns: 1fr;
        gap: 4px;
    }
    .filtro-actions { grid-column: 1; }
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
    height: 34px !important;
    font-size: 12px !important;
    padding: 0 10px !important;
    border-radius: 4px !important;
    border: 2px solid #d1d5db !important;
    width: 100% !important;
}

.filtro-item-ultra :deep(.text-input:focus) {
    border-color: #1a3a5c !important;
    box-shadow: 0 0 0 3px rgba(26, 58, 92, 0.1) !important;
}

.filtro-actions {
    min-width: 80px;
}

/* ===== SELECT PRIMEVUE ===== */
.filtro-select-ultra {
    width: 100% !important;
}

.filtro-select-ultra.p-select {
    border-radius: 4px;
    border: 2px solid #d1d5db;
    background: #ffffff;
    height: 34px;
    box-shadow: none;
    transition: all 0.3s ease;
}

.filtro-select-ultra.p-select:hover {
    border-color: #1a3a5c;
    background: #fafbfc;
}

.filtro-select-ultra.p-select.p-focus {
    border-color: #1a3a5c;
    box-shadow: 0 0 0 3px rgba(26, 58, 92, 0.1);
    background: #fafbfc;
}

.filtro-select-ultra :deep(.p-select-label) {
    font-size: 12px;
    padding: 0 10px;
    display: flex;
    align-items: center;
}

.btn-clear-ultra {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    width: 100%;
    border-radius: 4px;
    background: linear-gradient(135deg, #1a3a5c, #2c5282);
    border: none;
    color: white;
    height: 34px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(26, 58, 92, 0.2);
}

.btn-clear-ultra:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(26, 58, 92, 0.3);
}

.btn-no-filtros-ultra {
    width: 100%;
    border-radius: 4px;
    background: #f8fafc;
    border: 2px dashed #d1d5db;
    cursor: not-allowed;
    height: 34px;
    font-size: 12px;
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
    gap: 10px;
    margin-top: 14px;
    padding-top: 14px;
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

@keyframes pulse-ring {
    0% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.1); opacity: 0.7; }
    100% { transform: scale(1); opacity: 1; }
}

@media (max-width: 768px) {
    .table-wrapper-premium { padding: 12px; }
    .header-premium { padding: 16px; }
    .header-title-premium { font-size: 18px; }
    .header-icon-wrapper { width: 40px; height: 40px; }
    .header-icon-svg { width: 20px; height: 20px; }
    .stats-card-enhanced-value { font-size: 20px; }
    .stats-card-enhanced-icon { width: 38px; height: 38px; }
    .stats-card-enhanced-svg { width: 18px; height: 18px; }
}
</style>
