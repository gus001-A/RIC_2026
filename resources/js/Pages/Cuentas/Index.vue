<template>
    <AppLayout title="RIC - Cuentas">
        <div class="py-6">
            <div class="max-w-full px-4 sm:px-6 lg:px-8">
                <!-- Selector de Empresa + Botones -->
                <div v-if="empresas.length > 0" class="empresa-selector-premium">
                    <div class="empresa-selector-content">
                        <div class="empresa-selector-label">
                            <i class="pi pi-building" style="font-size: 16px; color: #1a3a5c;"></i>
                            <span>Empresa</span>
                        </div>
                        <div class="empresa-selector-field">
                            <select v-model="empresaSeleccionada" @change="cambiarEmpresa" class="empresa-select-native">
                                <option v-for="empresa in empresas" :key="empresa.id" :value="empresa.id">
                                    {{ empresa.nombre_empresa }}
                                </option>
                            </select>
                        </div>
                        <div class="empresa-selector-actions">
                            <button
                                v-if="permisos?.puede_editar_cuentas"
                                type="button"
                                class="btn-ver-inactivas"
                                @click="abrirModalInactivas"
                            >
                                <i class="pi pi-eye"></i>
                                Ver Inactivas
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tabla Premium -->
                <div v-if="empresas.length > 0" class="table-wrapper-premium">
                    <div class="table-header-ultra">
                        <div class="table-header-left-ultra">
                            <span class="table-title-ultra">Listado de Cuentas</span>
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
                            :value="cuentas.data"
                            :loading="loading"
                            data-key="id_cuenta"
                            scrollable
                            scroll-height="420px"
                            row-hover
                            table-style="min-width: 60rem"
                            class="cuenta-table-ultra"
                        >
                            <template #empty>
                                <div class="tabla-vacia">No se encontraron cuentas.</div>
                            </template>

                            <Column header="Código" style="width: 14%" frozen>
                                <template #body="{ data }">
                                    <span class="clave-text-ultra">{{ data.codigo_cuenta }}</span>
                                </template>
                            </Column>

                            <Column header="Cuenta" style="width: 24%">
                                <template #body="{ data }">
                                    <Link
                                        v-if="permisos?.puede_editar_cuentas"
                                        :href="route('cuentas.edit', data.id_cuenta)"
                                        class="nombre-link-ultra"
                                    >
                                        <span class="nombre-link-icon"></span>
                                        {{ data.nombre_cuenta }}
                                    </Link>
                                    <span v-else class="nombre-text-ultra">{{ data.nombre_cuenta }}</span>
                                </template>
                            </Column>

                            <Column header="Índice" style="width: 12%">
                                <template #body="{ data }">
                                    <span class="indice-text-ultra">{{ data.indice }}</span>
                                </template>
                            </Column>

                            <Column header="Cuenta Madre" style="width: 18%">
                                <template #body="{ data }">
                                    <span class="origen-text-ultra">{{ data.cuenta_madre }}</span>
                                </template>
                            </Column>

                            <Column header="Naturaleza" style="width: 12%">
                                <template #body="{ data }">
                                    <span class="naturaleza-badge" :class="data.Naturaleza === 'DEUDORA' ? 'deudora' : 'acreedora'">
                                        {{ data.Naturaleza === 'DEUDORA' ? 'Deudora' : data.Naturaleza === 'ACREEDORA' ? 'Acreedora' : '—' }}
                                    </span>
                                </template>
                            </Column>

                            <Column header="Fondeadora" style="width: 12%">
                                <template #body="{ data }">
                                    <span class="fondeadora-badge" :class="data.fondeo_c == 1 ? 'fondeadora-si' : 'fondeadora-no'">
                                        {{ data.fondeo_c == 1 ? 'Si' : 'No' }}
                                    </span>
                                </template>
                            </Column>
                        </DataTable>
                    </div>

                    <!-- FILTROS INFERIOR -->
                    <div class="filtros-ultra-full filtros-inferior">
                        <div class="filtros-grid-ultra-full">
                            <div class="filtro-item-ultra">
                                <InputLabel>Código</InputLabel>
                                <TextInput v-model="filtros.codigo_cuenta" @input="aplicarFiltros" placeholder="Buscar..." square />
                            </div>
                            <div class="filtro-item-ultra">
                                <InputLabel>Cuenta</InputLabel>
                                <TextInput v-model="filtros.nombre_cuenta" @input="aplicarFiltros" placeholder="Buscar..." square />
                            </div>
                            <div class="filtro-item-ultra">
                                <InputLabel>Índice</InputLabel>
                                <TextInput v-model="filtros.indice" @input="aplicarFiltros" placeholder="Buscar..." square />
                            </div>
                            <div class="filtro-item-ultra">
                                <InputLabel>Cuenta Madre</InputLabel>
                                <TextInput v-model="filtros.cuenta_madre" @input="aplicarFiltros" placeholder="Buscar..." square />
                            </div>
                            <div class="filtro-item-ultra">
                                <InputLabel>Naturaleza</InputLabel>
                                <Select
                                    v-model="filtros.naturaleza"
                                    :options="naturalezaOptions"
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
                        <span class="pagination-info-ultra">Mostrando <span class="pagination-highlight-ultra">{{ cuentas.from || 0 }}</span> a
                            <span class="pagination-highlight-ultra">{{ cuentas.to || 0 }}</span> de
                            <span class="pagination-highlight-ultra">{{ cuentas.total || 0 }}</span> resultados
                        </span>
                        <Pagination :links="cuentas.links" />
                    </div>
                </div>

                <div v-else class="table-wrapper-premium">
                    <div class="text-center py-12">
                        <i class="pi pi-building empty-state-icon"></i>
                        <h3 class="text-xl font-semibold text-gray-700 mb-2">No tienes empresas asignadas</h3>
                        <p class="text-gray-500">Contacta al administrador para que te asigne una empresa.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL CUENTAS INACTIVAS -->
        <Dialog
            v-model:visible="modalInactivasVisible"
            modal
            header="Cuentas Inactivas"
            :style="{ width: '90vw', maxWidth: '1100px' }"
            class="inactivas-modal-premium"
            @hide="cerrarModalInactivas"
        >
            <div class="modal-inactivas-content">
                <div class="modal-inactivas-header">
                    <span class="modal-inactivas-title">Listado de Cuentas Inactivas</span>
                    <span class="modal-inactivas-count">{{ cuentasInactivasFiltradas.length }} cuentas</span>
                </div>

                <div class="table-scroll-container-modal">
                    <DataTable
                        :value="cuentasInactivasFiltradas"
                        :loading="loadingInactivas"
                        data-key="id_cuenta"
                        scrollable
                        scroll-height="280px"
                        row-hover
                        table-style="min-width: 45rem"
                        class="cuenta-table-ultra"
                    >
                        <template #empty>
                            <div class="tabla-vacia">Sin cuentas inactivas.</div>
                        </template>

                        <Column header="Código" style="width: 18%">
                            <template #body="{ data }">
                                <span class="clave-text-ultra inactivo-text">{{ data.codigo_cuenta }}</span>
                            </template>
                        </Column>
                        <Column header="Cuenta" style="width: 32%">
                            <template #body="{ data }">
                                <span class="nombre-text-ultra inactivo-text">{{ data.nombre_cuenta }}</span>
                            </template>
                        </Column>
                        <Column header="Cuenta Madre" style="width: 25%">
                            <template #body="{ data }">
                                <span class="origen-text-ultra inactivo-text">{{ data.cuenta_madre }}</span>
                            </template>
                        </Column>
                        <Column header="Restaurar" style="width: 13%">
                            <template #body="{ data }">
                                <button
                                    v-if="permisos?.puede_editar_cuentas"
                                    v-tooltip.top="'Restaurar cuenta'"
                                    class="btn-restaurar-ultra"
                                    @click="confirmarRestaurar(data)"
                                >
                                    <i class="pi pi-refresh"></i>
                                </button>
                                <span v-else class="text-gray-400 text-sm">—</span>
                            </template>
                        </Column>
                    </DataTable>
                </div>

                <div class="filtros-ultra-full-modal">
                    <div class="filtros-grid-ultra-full-modal">
                        <div class="filtro-item-ultra-modal">
                            <InputLabel>Código</InputLabel>
                            <TextInput v-model="filtrosInactivos.codigo_cuenta" placeholder="Buscar..." square />
                        </div>
                        <div class="filtro-item-ultra-modal">
                            <InputLabel>Cuenta</InputLabel>
                            <TextInput v-model="filtrosInactivos.nombre_cuenta" placeholder="Buscar..." square />
                        </div>
                        <div class="filtro-item-ultra-modal">
                            <InputLabel>Cuenta Madre</InputLabel>
                            <TextInput v-model="filtrosInactivos.cuenta_madre" placeholder="Buscar..." square />
                        </div>
                        <div class="filtro-item-ultra-modal filtro-actions-modal">
                            <InputLabel>Acciones</InputLabel>
                            <button v-if="filtrosInactivosActivos" type="button" @click="limpiarFiltrosInactivos" class="btn-clear-ultra">
                                <i class="pi pi-times"></i>
                                Limpiar
                            </button>
                            <button v-else type="button" disabled class="btn-no-filtros-ultra">
                                <span class="no-filtros-text-ultra">Sin filtros</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="modal-inactivas-footer">
                    <span class="pagination-info-ultra">Mostrando <span class="pagination-highlight-ultra">{{ cuentasInactivasFiltradas.length }}</span> cuentas inactivas
                    </span>
                    <button type="button" @click="cerrarModalInactivas" class="btn-cerrar-modal">
                        <i class="pi pi-times"></i>
                        Cerrar
                    </button>
                </div>
            </div>
        </Dialog>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Select from 'primevue/select';
import Dialog from 'primevue/dialog';
import Pagination from '@/Components/Pagination.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import axios from 'axios';
import { useNotify } from '@/composables/useNotify';
import { useEmpresa } from '@/composables/useEmpresa';

const page = usePage();
const permisos = computed(() => page.props.permisos || {});
const notify = useNotify();

const props = defineProps({
    cuentas: {
        type: Object,
        default: () => ({ data: [], from: 0, to: 0, total: 0, links: [] }),
    },
    cuentas_inactivas: {
        type: Array,
        default: () => [],
    },
    stats: {
        type: Object,
        default: null,
    },
    empresas: {
        type: Array,
        default: () => [],
    },
    filtros: {
        type: Object,
        default: () => ({}),
    },
    flash: {
        type: Object,
        default: () => ({}),
    },
    empresa_seleccionada: {
        type: [Number, String],
        default: null,
    },
});

const { empresaSeleccionada, cargarEmpresaGuardada, guardarEmpresa } = useEmpresa();

const loading = ref(false);
const loadingInactivas = ref(false);
const modalInactivasVisible = ref(false);
const cuentasInactivas = ref(props.cuentas_inactivas || []);

const naturalezaOptions = [
    { label: 'Deudora', value: 'DEUDORA' },
    { label: 'Acreedora', value: 'ACREEDORA' },
];

// ============================================
// FILTROS PRINCIPALES
// ============================================
const filtros = ref({
    codigo_cuenta: props.filtros?.codigo_cuenta || '',
    nombre_cuenta: props.filtros?.nombre_cuenta || '',
    indice: props.filtros?.indice || '',
    cuenta_madre: props.filtros?.cuenta_madre || '',
    naturaleza: props.filtros?.naturaleza || null,
});

const filtrosActivos = computed(() =>
    Object.values(filtros.value).some(value => value !== '' && value !== null && value !== undefined),
);

let timeoutId = null;
const aplicarFiltros = () => {
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => {
        if (!empresaSeleccionada.value) return;
        loading.value = true;
        const params = { empresa_id: empresaSeleccionada.value };
        for (const [key, value] of Object.entries(filtros.value)) {
            if (value !== '' && value !== null && value !== undefined) {
                params[key] = value;
            }
        }

        router.get(route('cuentas.index'), params, {
            preserveState: true,
            replace: true,
            onFinish: () => { loading.value = false; },
        });
    }, 300);
};

const limpiarFiltros = () => {
    filtros.value = {
        codigo_cuenta: '',
        nombre_cuenta: '',
        indice: '',
        cuenta_madre: '',
        naturaleza: null,
    };
    aplicarFiltros();
};

// ============================================
// FILTROS PARA INACTIVAS
// ============================================
const filtrosInactivos = ref({
    codigo_cuenta: '',
    nombre_cuenta: '',
    cuenta_madre: '',
});

const filtrosInactivosActivos = computed(() =>
    Object.values(filtrosInactivos.value).some(val => val !== '' && val !== null && val !== undefined),
);

const cuentasInactivasFiltradas = computed(() => {
    let list = cuentasInactivas.value;
    const f = filtrosInactivos.value;

    if (f.codigo_cuenta) {
        const q = f.codigo_cuenta.toLowerCase().trim();
        list = list.filter(item => item.codigo_cuenta && item.codigo_cuenta.toLowerCase().includes(q));
    }
    if (f.nombre_cuenta) {
        const q = f.nombre_cuenta.toLowerCase().trim();
        list = list.filter(item => item.nombre_cuenta && item.nombre_cuenta.toLowerCase().includes(q));
    }
    if (f.cuenta_madre) {
        const q = f.cuenta_madre.toLowerCase().trim();
        list = list.filter(item => item.cuenta_madre && item.cuenta_madre.toLowerCase().includes(q));
    }

    return list;
});

const limpiarFiltrosInactivos = () => {
    filtrosInactivos.value = {
        codigo_cuenta: '',
        nombre_cuenta: '',
        cuenta_madre: '',
    };
};

// ============================================
// CAMBIAR EMPRESA
// ============================================
const cambiarEmpresa = () => {
    if (empresaSeleccionada.value) {
        guardarEmpresa(empresaSeleccionada.value);
        loading.value = true;
        const params = { empresa_id: empresaSeleccionada.value };
        for (const [key, value] of Object.entries(filtros.value)) {
            if (value !== '' && value !== null && value !== undefined) {
                params[key] = value;
            }
        }
        router.get(route('cuentas.index'), params, {
            preserveState: true,
            replace: true,
            onFinish: () => { loading.value = false; },
        });
    } else {
        router.get(route('cuentas.index'), {}, { preserveState: true, replace: true });
    }
};

// ============================================
// MODAL CUENTAS INACTIVAS
// ============================================
const abrirModalInactivas = async () => {
    if (!empresaSeleccionada.value) {
        notify.warn('Selecciona una empresa primero', 'Sin empresa');
        return;
    }

    modalInactivasVisible.value = true;
    loadingInactivas.value = true;

    try {
        const response = await axios.get(route('cuentas.inactivas'), {
            params: { empresa_id: empresaSeleccionada.value },
        });
        cuentasInactivas.value = (response.data.data || []).sort((a, b) => a.id_cuenta - b.id_cuenta);
        limpiarFiltrosInactivos();
    } catch (error) {
        console.error('Error al cargar cuentas inactivas:', error);
        notify.error('No se pudieron cargar las cuentas inactivas');
    } finally {
        loadingInactivas.value = false;
    }
};

const cerrarModalInactivas = () => {
    modalInactivasVisible.value = false;
    cuentasInactivas.value = [];
    limpiarFiltrosInactivos();
};

// ============================================
// RESTAURAR CUENTA
// ============================================
const confirmarRestaurar = (cuenta) => {
    if (!permisos.value?.puede_editar_cuentas) {
        notify.error('No tienes permisos para restaurar cuentas. Contacta al administrador.', 'Sin permisos');
        return;
    }

    notify.confirmAction({
        header: 'Restaurar cuenta',
        message: `La cuenta "${cuenta.nombre_cuenta}" (código ${cuenta.codigo_cuenta}) volverá a estar activa y aparecerá en el listado principal.`,
        acceptLabel: 'Sí, restaurar',
        acceptSeverity: 'success',
        icon: 'pi pi-refresh',
        accept: () => {
            // El controlador redirige a cuentas.index (con flash 'error'/'warning' si falla,
            // sin flash si tiene éxito). El manejador global muestra el error; aquí sólo
            // confirmamos el éxito cuando NO vino un flash de error/aviso.
            cerrarModalInactivas();
            router.post(route('cuentas.restaurar', cuenta.id_cuenta), {}, {
                preserveScroll: true,
                onSuccess: (page) => {
                    const flash = page?.props?.flash || {};
                    if (!flash.error && !flash.warning) {
                        notify.success(`Cuenta "${cuenta.nombre_cuenta}" restaurada correctamente`);
                    }
                },
            });
        },
    });
};

// Inicializar empresa seleccionada
onMounted(() => {
    const empresaGuardada = cargarEmpresaGuardada();
    if (empresaGuardada && props.empresas.some(e => e.id === empresaGuardada)) {
        empresaSeleccionada.value = empresaGuardada;
    } else if (props.empresa_seleccionada) {
        empresaSeleccionada.value = parseInt(props.empresa_seleccionada);
        guardarEmpresa(props.empresa_seleccionada);
    } else if (props.empresas && props.empresas.length > 0) {
        empresaSeleccionada.value = props.empresas[0].id;
        guardarEmpresa(props.empresas[0].id);
    }

    if (empresaSeleccionada.value && (!props.cuentas?.data || props.cuentas.data.length === 0)) {
        cambiarEmpresa();
    }
});
</script>

<style scoped>
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
    border-color: #1a3a5c;
    box-shadow: 0 0 0 3px rgba(26, 58, 92, 0.1);
    background: #ffffff;
}

.empresa-select-native:hover {
    border-color: #1a3a5c;
    background: #ffffff;
}

.btn-ver-inactivas {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: linear-gradient(135deg, #f59e0b, #d97706);
    border: none;
    border-radius: 6px;
    font-weight: 600;
    padding: 0 20px;
    height: 36px;
    color: white;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    font-size: 13px;
    letter-spacing: 0.3px;
}

.btn-ver-inactivas:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(245, 158, 11, 0.3);
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

.table-header-right-ultra {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}

.filter-tag-ultra {
    border-radius: 30px;
    background: linear-gradient(135deg, #eff6ff, #dbeafe);
    color: #1a3a5c;
    font-weight: 600;
    font-size: 12px;
    padding: 4px 16px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
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
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #64748b;
    border: 2px solid #d1d5db;
    transition: all 0.3s ease;
    height: 40px;
    padding: 0 16px;
    font-weight: 600;
    background: #ffffff;
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
.cuenta-table-ultra :deep(.p-datatable-thead > tr > th) {
    background: linear-gradient(135deg, #f1f5f9, #e8edf2);
    font-weight: 700;
    color: #1e293b;
    border-bottom: 2px solid #d1d5db;
    padding: 10px 12px;
    font-size: 11px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    white-space: nowrap;
}

.cuenta-table-ultra :deep(.p-datatable-tbody > tr) {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.cuenta-table-ultra :deep(.p-datatable-tbody > tr:hover) {
    background: linear-gradient(90deg, #f8faff, #f0f7ff);
}

.cuenta-table-ultra :deep(.p-datatable-tbody > tr:last-child > td) {
    border-bottom: none;
}

.cuenta-table-ultra :deep(.p-datatable-tbody > tr > td) {
    padding: 8px 10px;
    border-bottom: 1px solid #f1f5f9;
    font-size: 13px;
}

.cuenta-table-ultra :deep(.p-datatable-tbody > tr > td.p-frozen-column),
.cuenta-table-ultra :deep(.p-datatable-thead > tr > th.p-frozen-column) {
    background: #ffffff;
}

/* ===== FILTROS INFERIOR ===== */
.filtros-ultra-full {
    margin-top: 16px;
    padding-top: 16px;
    border-top: 2px solid #f1f5f9;
}

.filtros-grid-ultra-full {
    display: grid;
    /* Debe tener EXACTAMENTE el mismo ancho/orden que las columnas de la tabla
       (Código 14% | Cuenta 24% | Índice 12% | Cuenta Madre 18% | Naturaleza 12%
       | Fondeadora 12%) para que cada filtro quede alineado debajo de su
       columna. Antes tenía una columna extra al inicio (8%) que corría todo un
       lugar a la derecha. */
    grid-template-columns: 14% 24% 12% 18% 12% 12%;
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

.filtro-select-ultra.p-select {
    border-radius: 0px;
    border: 2px solid #d1d5db;
    background: #ffffff;
    height: 36px;
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
    gap: 8px;
    width: 100%;
    border-radius: 0px;
    background: linear-gradient(135deg, #1a3a5c, #2c5282);
    border: none;
    color: white;
    height: 36px;
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
    border-radius: 0px;
    background: #f8fafc;
    border: 2px dashed #d1d5db;
    cursor: not-allowed;
    height: 36px;
    font-size: 12px;
}

.no-filtros-text-ultra {
    color: #94a3b8;
    font-weight: 600;
}

/* ===== FILTROS MODAL INACTIVAS ===== */
.filtros-ultra-full-modal {
    margin-top: 12px;
    padding-top: 12px;
    border-top: 2px solid #f1f5f9;
}

.filtros-grid-ultra-full-modal {
    display: grid;
    grid-template-columns: 12% 18% 32% 25% 13%;
    gap: 4px;
    align-items: end;
    width: 100%;
}

@media (max-width: 1200px) {
    .filtros-grid-ultra-full-modal {
        grid-template-columns: 1fr 1fr 1fr;
        gap: 6px;
    }
    .filtro-actions-modal { grid-column: 1 / -1; }
}

@media (max-width: 768px) {
    .filtros-grid-ultra-full-modal {
        grid-template-columns: 1fr 1fr;
        gap: 6px;
    }
    .filtro-actions-modal { grid-column: 1 / -1; }
}

@media (max-width: 480px) {
    .filtros-grid-ultra-full-modal {
        grid-template-columns: 1fr;
        gap: 4px;
    }
    .filtro-actions-modal { grid-column: 1; }
}

.filtro-item-ultra-modal {
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 0;
}

.filtro-item-ultra-modal :deep(.input-label) {
    font-size: 11px !important;
    font-weight: 600 !important;
    color: #475569 !important;
    margin-bottom: 0 !important;
}

.filtro-item-ultra-modal :deep(.text-input) {
    height: 36px !important;
    font-size: 12px !important;
    padding: 0 10px !important;
    border-radius: 0px !important;
    border: 2px solid #d1d5db !important;
    width: 100% !important;
}

.filtro-item-ultra-modal :deep(.text-input:focus) {
    border-color: #1a3a5c !important;
    box-shadow: 0 0 0 3px rgba(26, 58, 92, 0.1) !important;
}

.filtro-actions-modal {
    min-width: 90px;
}

/* ===== CELDAS ===== */
.clave-text-ultra {
    font-size: 13px;
    font-weight: 600;
    color: #1a3a5c;
    font-family: 'Courier New', monospace;
}

.inactivo-text {
    opacity: 0.6;
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
}

.nombre-link-ultra:hover {
    color: #1a3a5c;
    background: #f0f4ff;
    border-color: #dbeafe;
    text-decoration: none;
    transform: translateX(2px);
}

.nombre-link-icon {
    font-size: 12px;
    opacity: 0;
    transition: all 0.2s ease;
    color: #1a3a5c;
}

.nombre-link-ultra:hover .nombre-link-icon {
    opacity: 1;
}

.nombre-text-ultra {
    font-size: 13px;
    color: #1a3a5c;
    font-weight: 500;
}

.indice-text-ultra {
    font-size: 12px;
    color: #475569;
}

.origen-text-ultra {
    font-size: 12px;
    color: #475569;
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

/* ===== MODAL INACTIVAS ===== */
.inactivas-modal-premium :deep(.p-dialog-header) {
    border-bottom: 2px solid #f1f5f9;
    padding: 16px 24px;
    background: linear-gradient(135deg, #f8fafc, #f1f5f9);
}

.inactivas-modal-premium :deep(.p-dialog-title) {
    font-weight: 700;
    font-size: 17px;
    color: #0f172a;
}

.inactivas-modal-premium :deep(.p-dialog-content) {
    padding: 20px;
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
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border-radius: 6px;
    color: #64748b;
    border: 2px solid #d1d5db;
    transition: all 0.3s ease;
    height: 36px;
    padding: 0 16px;
    font-weight: 600;
    background: #fff;
    cursor: pointer;
}

.btn-cerrar-modal:hover {
    color: #1a3a5c;
    border-color: #1a3a5c;
}

.table-scroll-container-modal {
    overflow: hidden;
    border-radius: 8px;
    border: 1px solid #f1f5f9;
}

.table-scroll-container-modal :deep(.p-datatable-thead > tr > th) {
    padding: 8px 10px;
}

.table-scroll-container-modal :deep(.p-datatable-tbody > tr > td) {
    padding: 6px 10px;
    font-size: 12px;
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

@keyframes pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(1.2); }
}

@media (max-width: 768px) {
    .table-wrapper-premium { padding: 12px; }
    .modal-inactivas-footer {
        flex-direction: column;
        gap: 8px;
        align-items: stretch;
    }
}

.empty-state-icon {
    font-size: 3.25rem;
    color: #cbd5e1;
    display: block;
    margin: 0 auto 1rem;
}
</style>
