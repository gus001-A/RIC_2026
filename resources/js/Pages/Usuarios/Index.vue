<template>
    <AppLayout title="RIC - Usuarios">
        <template #header>
            <div class="header-premium">
                <div class="header-content-premium">
                    <div class="header-left-premium">
                        <div class="header-icon-wrapper">
                            <svg class="header-icon-svg" fill="none" stroke="white" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="header-title-premium">Gestión de Usuarios</h2>
                            <p class="header-subtitle-premium">
                                <span class="subtitle-line"></span>
                                Administra los usuarios del sistema
                            </p>
                        </div>
                    </div>
                    <Link v-if="permisos?.puede_crear_usuarios" :href="route('usuarios.create')">
                        <button type="button" class="btn-nuevo-usuario-premium">
                            <i class="pi pi-plus"></i>
                            Nuevo Usuario
                        </button>
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Stats Cards -->
                <div class="stats-grid-usuarios mb-6">
                    <div class="stats-card-enhanced">
                        <div class="stats-card-enhanced-content">
                            <div class="stats-card-enhanced-left">
                                <span class="stats-card-enhanced-label">Total Usuarios</span>
                                <span class="stats-card-enhanced-value">{{ stats?.total || 0 }}</span>
                            </div>
                            <div class="stats-card-enhanced-icon" style="background: linear-gradient(135deg, rgba(26, 58, 92, 0.1), rgba(26, 58, 92, 0.05));">
                                <i class="pi pi-user" style="font-size: 24px; color: #1a3a5c;"></i>
                            </div>
                        </div>
                        <div class="stats-card-enhanced-progress">
                            <div class="stats-card-enhanced-progress-bar" style="width: 100%; background: linear-gradient(90deg, #1a3a5c, #2c5282);"></div>
                        </div>
                    </div>

                    <div class="stats-card-enhanced">
                        <div class="stats-card-enhanced-content">
                            <div class="stats-card-enhanced-left">
                                <span class="stats-card-enhanced-label">Activos</span>
                                <span class="stats-card-enhanced-value" style="color: #2e7d32;">{{ stats?.activos || 0 }}</span>
                            </div>
                            <div class="stats-card-enhanced-icon" style="background: linear-gradient(135deg, rgba(46, 125, 50, 0.1), rgba(46, 125, 50, 0.05));">
                                <i class="pi pi-check-circle" style="font-size: 24px; color: #2e7d32;"></i>
                            </div>
                        </div>
                        <div class="stats-card-enhanced-progress">
                            <div class="stats-card-enhanced-progress-bar" :style="{ width: stats?.total ? Math.round((stats.activos / stats.total) * 100) + '%' : '0%', background: 'linear-gradient(90deg, #2e7d32, #43a047)' }"></div>
                        </div>
                    </div>

                    <div class="stats-card-enhanced">
                        <div class="stats-card-enhanced-content">
                            <div class="stats-card-enhanced-left">
                                <span class="stats-card-enhanced-label">Inactivos</span>
                                <span class="stats-card-enhanced-value" style="color: #c62828;">{{ stats?.inactivos || 0 }}</span>
                            </div>
                            <div class="stats-card-enhanced-icon" style="background: linear-gradient(135deg, rgba(198, 40, 40, 0.1), rgba(198, 40, 40, 0.05));">
                                <i class="pi pi-times-circle" style="font-size: 24px; color: #c62828;"></i>
                            </div>
                        </div>
                        <div class="stats-card-enhanced-progress">
                            <div class="stats-card-enhanced-progress-bar" :style="{ width: stats?.total ? Math.round((stats.inactivos / stats.total) * 100) + '%' : '0%', background: 'linear-gradient(90deg, #c62828, #e53935)' }"></div>
                        </div>
                    </div>

                    <div class="stats-card-enhanced">
                        <div class="stats-card-enhanced-content">
                            <div class="stats-card-enhanced-left">
                                <span class="stats-card-enhanced-label">Con Empresas</span>
                                <span class="stats-card-enhanced-value" style="color: #1a3a5c;">{{ usuariosConEmpresas }}</span>
                            </div>
                            <div class="stats-card-enhanced-icon" style="background: linear-gradient(135deg, rgba(26, 58, 92, 0.1), rgba(26, 58, 92, 0.05));">
                                <i class="pi pi-building" style="font-size: 24px; color: #1a3a5c;"></i>
                            </div>
                        </div>
                        <div class="stats-card-enhanced-progress">
                            <div class="stats-card-enhanced-progress-bar" :style="{ width: stats?.total ? Math.round((usuariosConEmpresas / stats.total) * 100) + '%' : '0%', background: 'linear-gradient(90deg, #1a3a5c, #3d6ea5)' }"></div>
                        </div>
                    </div>
                </div>

                <!-- Tabla Premium -->
                <div class="table-wrapper-premium">
                    <div class="table-header-ultra">
                        <div class="table-header-left-ultra">
                            <span v-if="filtrosActivos" class="filter-tag-ultra">
                                <span class="filter-dot-active"></span>
                                Filtros activos
                            </span>
                            <span v-if="mostrarInactivos" class="filter-tag-ultra filter-tag-orange">
                                <span class="filter-dot-inactive"></span>
                                Mostrando inactivos
                            </span>
                        </div>
                        <div class="table-header-right-ultra">
                            <button v-if="filtrosActivos" type="button" @click="limpiarFiltros" class="btn-limpiar-ultra">
                                <i class="pi pi-times"></i>
                                Limpiar filtros
                            </button>
                        </div>
                    </div>

                    <DataTable
                        :value="usuarios.data"
                        :loading="loading"
                        data-key="id_usuario"
                        row-hover
                        table-style="min-width: 60rem"
                        class="usuario-table-ultra"
                    >
                        <template #empty>
                            <div class="tabla-vacia">No se encontraron usuarios.</div>
                        </template>

                        <Column header="Nombre" style="width: 16%">
                            <template #body="{ data }">
                                <div class="nombre-cell-ultra">
                                    <span class="nombre-text-ultra" :class="{ 'text-gray-400': !data.activo }">
                                        {{ data.nombre_completo }}
                                    </span>
                                    <span v-if="!data.activo" class="badge-inactivo-ultra">Inactivo</span>
                                </div>
                            </template>
                        </Column>

                        <Column header="Usuario" style="width: 10%">
                            <template #body="{ data }">
                                <span class="usuario-nombre-ultra" :class="{ 'text-gray-400': !data.activo }">
                                    {{ data.nombre_usuario }}
                                </span>
                            </template>
                        </Column>

                        <Column header="Tipo" style="width: 10%">
                            <template #body="{ data }">
                                <span class="rol-badge" :class="getRolClass(data.tipo_usuario)">
                                    <span class="rol-dot" :class="getRolDotClass(data.tipo_usuario)"></span>
                                    {{ data.tipo_usuario_texto || data.tipo_usuario }}
                                </span>
                            </template>
                        </Column>

                        <Column header="Teléfono" style="width: 8%">
                            <template #body="{ data }">
                                <span :class="{ 'text-gray-400': !data.activo }">{{ data.telefono || '—' }}</span>
                            </template>
                        </Column>

                        <Column header="Reset" style="width: 5%">
                            <template #body="{ data }">
                                <button
                                    v-if="data.activo && permisos?.puede_editar_usuarios"
                                    v-tooltip.top="'Cambiar contraseña'"
                                    class="btn-reset-ultra"
                                    @click="abrirModalReset(data)"
                                >
                                    <i class="pi pi-refresh"></i>
                                </button>
                                <span v-else class="text-gray-400 text-xs">—</span>
                            </template>
                        </Column>

                        <Column header="Empresas" style="width: 18%">
                            <template #body="{ data }">
                                <div v-if="data.empresas && data.empresas.length > 0" class="empresas-hover-wrap">
                                    <div class="empresas-tags">
                                        <span
                                            v-for="empresa in data.empresas.slice(0, 2)"
                                            :key="empresa.id_empresa"
                                            class="empresa-tag"
                                            :class="{ 'opacity-50': !data.activo }"
                                        >
                                            {{ empresa.nombre_empresa }}
                                        </span>
                                        <span
                                            v-if="data.empresas.length > 2"
                                            class="empresa-tag-more"
                                            :class="{ 'opacity-50': !data.activo }"
                                        >
                                            +{{ data.empresas.length - 2 }}
                                        </span>
                                    </div>
                                    <div v-if="data.empresas.length > 2" class="empresas-hover-panel">
                                        <div class="empresas-popover-header">
                                            <i class="pi pi-building"></i>
                                            <span>Empresas asignadas</span>
                                        </div>
                                        <div class="empresas-popover-list">
                                            <div v-for="empresa in data.empresas" :key="empresa.id_empresa" class="empresa-popover-item">
                                                <i class="pi pi-building empresa-popover-icon"></i>
                                                <span>{{ empresa.nombre_empresa }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <span v-else class="contacto-vacio-ultra">Sin empresas</span>
                            </template>
                        </Column>

                        <Column header="Estado" style="width: 8%">
                            <template #body="{ data }">
                                <div class="estado-badge-ultra" :class="data.activo ? 'activo' : 'inactivo'">
                                    <span class="estado-dot-ultra"></span>
                                    <span>{{ data.activo ? 'Activo' : 'Inactivo' }}</span>
                                </div>
                            </template>
                        </Column>

                        <Column header="Acciones" style="width: 12%">
                            <template #body="{ data }">
                                <div class="acciones-ultra">
                                    <button
                                        v-if="!data.activo && data.id_usuario !== $page.props.auth.user?.id_usuario && permisos?.puede_editar_usuarios"
                                        v-tooltip.top="'Restaurar usuario'"
                                        class="btn-action-ultra btn-restore-ultra"
                                        @click="confirmarRestaurar(data)"
                                    >
                                        <i class="pi pi-refresh"></i>
                                    </button>

                                    <Link v-if="permisos?.puede_editar_usuarios" :href="route('usuarios.edit', data.id_usuario)">
                                        <button v-tooltip.top="'Editar'" class="btn-action-ultra btn-edit-ultra">
                                            <i class="pi pi-pencil"></i>
                                        </button>
                                    </Link>

                                    <button
                                        v-if="data.id_usuario !== $page.props.auth.user?.id_usuario && data.activo && permisos?.puede_eliminar_usuarios"
                                        v-tooltip.top="'Eliminar usuario'"
                                        class="btn-action-ultra btn-delete-ultra"
                                        @click="confirmarEliminar(data)"
                                    >
                                        <i class="pi pi-trash"></i>
                                    </button>
                                </div>
                            </template>
                        </Column>

                        <template #footer>
                            <div class="filtros-ultra">
                                <div class="filtros-grid-ultra">
                                    <div class="filtro-item-ultra">
                                        <InputLabel>Buscar</InputLabel>
                                        <TextInput v-model="filtros.search" @input="aplicarFiltros" placeholder="Nombre, usuario..." square />
                                    </div>

                                    <div class="filtro-item-ultra">
                                        <InputLabel>Tipo</InputLabel>
                                        <Select
                                            v-model="filtros.tipo"
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
                                        <InputLabel>Estado</InputLabel>
                                        <Select
                                            v-model="filtros.estado"
                                            :options="estadoOptions"
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
                        </template>
                    </DataTable>

                    <div class="pagination-ultra">
                        <span class="pagination-info-ultra">Mostrando <span class="pagination-highlight-ultra">{{ usuarios.from || 0 }}</span> a
                            <span class="pagination-highlight-ultra">{{ usuarios.to || 0 }}</span> de
                            <span class="pagination-highlight-ultra">{{ usuarios.total || 0 }}</span> resultados
                        </span>
                        <Pagination :links="usuarios.links" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para cambiar contraseña -->
        <Dialog
            v-model:visible="modalResetVisible"
            modal
            :style="{ width: '560px' }"
            class="reset-modal-premium"
            :show-header="false"
            @hide="cerrarModalReset"
        >
            <div class="reset-modal-content">
                <div class="reset-modal-header">
                    <div class="reset-modal-icon">
                        <svg class="modal-icon-svg" fill="none" stroke="white" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <h3 class="reset-modal-title">Cambiar Contraseña</h3>
                    <p class="reset-modal-subtitle">Establece una nueva contraseña para <strong>{{ usuarioSeleccionado?.nombre_completo }}</strong>
                    </p>
                    <p class="reset-modal-username">@{{ usuarioSeleccionado?.nombre_usuario }}</p>
                </div>

                <div class="reset-password-container">
                    <div class="form-group-modal">
                        <label class="form-label-modal">
                            <span class="label-icon-wrapper"><i class="pi pi-lock"></i></span>
                            Nueva Contraseña <span class="required-star">*</span>
                        </label>
                        <div class="input-wrapper-modal">
                            <input
                                :type="showPassword ? 'text' : 'password'"
                                v-model="nuevaPassword"
                                @input="validarPassword"
                                class="form-input-modal"
                                :class="{ 'error': passwordError || (nuevaPassword && !passwordValid) }"
                                placeholder="Ingresa tu nueva contraseña"
                            />
                            <button type="button" class="btn-toggle-password" @click="showPassword = !showPassword">
                                <i class="pi" :class="showPassword ? 'pi-eye-slash' : 'pi-eye'"></i>
                            </button>
                        </div>
                        <div v-if="passwordError" class="error-message-modal">
                            <i class="pi pi-times-circle"></i>
                            {{ passwordError }}
                        </div>
                    </div>

                    <div class="form-group-modal">
                        <label class="form-label-modal">
                            <span class="label-icon-wrapper"><i class="pi pi-lock"></i></span>
                            Confirmar Contraseña <span class="required-star">*</span>
                        </label>
                        <div class="input-wrapper-modal">
                            <input
                                :type="showConfirmPassword ? 'text' : 'password'"
                                v-model="confirmarPassword"
                                @input="validarPassword"
                                class="form-input-modal"
                                :class="{ 'error': passwordMatchError }"
                                placeholder="Confirma tu nueva contraseña"
                            />
                            <button type="button" class="btn-toggle-password" @click="showConfirmPassword = !showConfirmPassword">
                                <i class="pi" :class="showConfirmPassword ? 'pi-eye-slash' : 'pi-eye'"></i>
                            </button>
                        </div>
                        <div v-if="passwordMatchError" class="error-message-modal">
                            <i class="pi pi-times-circle"></i>
                            {{ passwordMatchError }}
                        </div>
                        <div v-else-if="confirmarPassword && nuevaPassword && !passwordMatchError" class="success-message-modal">
                            <i class="pi pi-check-circle"></i>
                            <span>Las contraseñas coinciden</span>
                            <span class="success-check"><i class="pi pi-check"></i></span>
                        </div>
                    </div>

                    <div class="password-strength-section">
                        <p class="strength-title">Requisitos de seguridad</p>
                        <div class="reset-password-requirements">
                            <div class="requirement-item" :class="{ met: passwordLength }">
                                <span class="req-icon">
                                    <i v-if="passwordLength" class="pi pi-check" style="color: #10b981;"></i>
                                    <i v-else class="pi pi-circle req-circle"></i>
                                </span>
                                <span>Mínimo 8 caracteres</span>
                            </div>
                            <div class="requirement-item" :class="{ met: passwordHasUpperCase }">
                                <span class="req-icon">
                                    <i v-if="passwordHasUpperCase" class="pi pi-check" style="color: #10b981;"></i>
                                    <i v-else class="pi pi-circle req-circle"></i>
                                </span>
                                <span>Mayúsculas y minúsculas</span>
                            </div>
                            <div class="requirement-item" :class="{ met: passwordHasNumber }">
                                <span class="req-icon">
                                    <i v-if="passwordHasNumber" class="pi pi-check" style="color: #10b981;"></i>
                                    <i v-else class="pi pi-circle req-circle"></i>
                                </span>
                                <span>Al menos un número</span>
                            </div>
                            <div class="requirement-item" :class="{ met: passwordHasSpecial }">
                                <span class="req-icon">
                                    <i v-if="passwordHasSpecial" class="pi pi-check" style="color: #10b981;"></i>
                                    <i v-else class="pi pi-circle req-circle"></i>
                                </span>
                                <span>Carácter especial</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="reset-modal-actions">
                    <button type="button" @click="cerrarModalReset" class="btn-cancel-reset">Cancelar</button>
                    <button
                        type="button"
                        :disabled="!isFormValid || resetLoading"
                        @click="confirmarReset"
                        class="btn-confirm-reset"
                    >
                        <i class="pi" :class="resetLoading ? 'pi-spin pi-spinner' : 'pi-check'"></i>
                        {{ resetLoading ? 'Guardando...' : 'Actualizar Contraseña' }}
                    </button>
                </div>
            </div>
        </Dialog>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Select from 'primevue/select';
import Dialog from 'primevue/dialog';
import Pagination from '@/Components/Pagination.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { useNotify } from '@/composables/useNotify';

const page = usePage();
const permisos = computed(() => page.props.permisos || {});
const notify = useNotify();

const props = defineProps({
    usuarios: Object,
    stats: Object,
    filtros: Object,
    tipos: Object,
    flash: Object,
});

const loading = ref(false);

// Estado para el modal de reset
const modalResetVisible = ref(false);
const resetLoading = ref(false);
const usuarioSeleccionado = ref(null);
const nuevaPassword = ref('');
const confirmarPassword = ref('');
const passwordError = ref('');
const passwordMatchError = ref('');
const showPassword = ref(false);
const showConfirmPassword = ref(false);

const usuariosConEmpresas = computed(() => {
    if (!props.usuarios?.data) return 0;
    return props.usuarios.data.filter(u => u.empresas && u.empresas.length > 0).length;
});

const passwordLength = computed(() => nuevaPassword.value.length >= 8);
const passwordHasUpperCase = computed(() => /[A-Z]/.test(nuevaPassword.value) && /[a-z]/.test(nuevaPassword.value));
const passwordHasNumber = computed(() => /\d/.test(nuevaPassword.value));
const passwordHasSpecial = computed(() => /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(nuevaPassword.value));
const passwordValid = computed(() => passwordLength.value && passwordHasUpperCase.value && passwordHasNumber.value && passwordHasSpecial.value);

const isFormValid = computed(() => {
    if (!nuevaPassword.value || !confirmarPassword.value) return false;
    if (!passwordValid.value) return false;
    if (passwordMatchError.value) return false;
    if (passwordError.value) return false;
    return true;
});

const tipoOptions = computed(() =>
    Object.entries(props.tipos || {}).map(([value, label]) => ({ value, label })),
);

const estadoOptions = [
    { label: 'Activos', value: 'activo' },
    { label: 'Inactivos', value: 'inactivo' },
];

const filtros = ref({
    search: props.filtros?.search || '',
    tipo: props.filtros?.tipo || null,
    estado: props.filtros?.estado || null,
});

const filtrosActivos = computed(() =>
    Object.values(filtros.value).some(value => value !== '' && value !== null && value !== undefined),
);

const mostrarInactivos = computed(() => filtros.value.estado === 'inactivo');

const getRolClass = (rol) => {
    const classes = {
        SUPERUSUARIO: 'superusuario',
        ADMINISTRADOR: 'administrador',
        AUDITOR: 'auditor',
        CAPTURISTA: 'capturista',
        LECTOR: 'lector',
    };
    return classes[rol] || 'lector';
};

const getRolDotClass = (rol) => {
    const classes = {
        SUPERUSUARIO: 'dot-superusuario',
        ADMINISTRADOR: 'dot-administrador',
        AUDITOR: 'dot-auditor',
        CAPTURISTA: 'dot-capturista',
        LECTOR: 'dot-lector',
    };
    return classes[rol] || 'dot-lector';
};

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

        router.get(route('usuarios.index'), params, {
            preserveState: true,
            replace: true,
            onFinish: () => { loading.value = false; },
        });
    }, 300);
};

const limpiarFiltros = () => {
    filtros.value = { search: '', tipo: null, estado: null };
    aplicarFiltros();
};

// ============================================
// CAMBIAR CONTRASEÑA
// ============================================
const abrirModalReset = (usuario) => {
    usuarioSeleccionado.value = usuario;
    nuevaPassword.value = '';
    confirmarPassword.value = '';
    passwordError.value = '';
    passwordMatchError.value = '';
    showPassword.value = false;
    showConfirmPassword.value = false;
    modalResetVisible.value = true;
};

const cerrarModalReset = () => {
    modalResetVisible.value = false;
    usuarioSeleccionado.value = null;
    nuevaPassword.value = '';
    confirmarPassword.value = '';
    passwordError.value = '';
    passwordMatchError.value = '';
    showPassword.value = false;
    showConfirmPassword.value = false;
    resetLoading.value = false;
};

const validarPassword = () => {
    if (nuevaPassword.value) {
        if (nuevaPassword.value.length < 8) {
            passwordError.value = 'La contraseña debe tener al menos 8 caracteres';
        } else if (!/[A-Z]/.test(nuevaPassword.value) || !/[a-z]/.test(nuevaPassword.value)) {
            passwordError.value = 'La contraseña debe tener mayúsculas y minúsculas';
        } else if (!/[0-9]/.test(nuevaPassword.value)) {
            passwordError.value = 'La contraseña debe tener al menos un número';
        } else if (!/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(nuevaPassword.value)) {
            passwordError.value = 'La contraseña debe tener al menos un carácter especial';
        } else {
            passwordError.value = '';
        }
    } else {
        passwordError.value = '';
    }

    if (confirmarPassword.value && nuevaPassword.value) {
        passwordMatchError.value = nuevaPassword.value !== confirmarPassword.value ? 'Las contraseñas no coinciden' : '';
    } else {
        passwordMatchError.value = '';
    }
};

const confirmarReset = () => {
    if (!passwordValid.value) {
        notify.warn('La contraseña debe tener al menos 8 caracteres, mayúsculas, minúsculas, números y caracteres especiales.', 'Contraseña no válida');
        return;
    }

    if (passwordMatchError.value) {
        notify.warn('Verifica que ambas contraseñas sean iguales.', 'Las contraseñas no coinciden');
        return;
    }

    resetLoading.value = true;

    // El backend redirige con flash 'success' o 'error'; el toast lo dispara el
    // manejador global. onError sólo se activa en errores de validación (422).
    router.post(route('usuarios.reset-password', usuarioSeleccionado.value.id_usuario), {
        password: nuevaPassword.value,
    }, {
        preserveScroll: true,
        onSuccess: () => cerrarModalReset(),
        onError: (errors) => {
            notify.error(errors?.password || errors?.error || 'No se pudo actualizar la contraseña');
        },
        onFinish: () => { resetLoading.value = false; },
    });
};

// ============================================
// ELIMINAR / RESTAURAR
// El controlador redirige a usuarios.index: con flash 'error'/'warning' si falla,
// sin flash si tiene éxito. Por eso confirmamos el éxito sólo cuando NO vino un
// flash de error/aviso (el error lo muestra el manejador global).
// ============================================
const confirmarEliminar = (usuario) => {
    if (!permisos.value?.puede_eliminar_usuarios) {
        notify.error('No tienes permisos para eliminar usuarios. Contacta al administrador.', 'Sin permisos');
        return;
    }

    notify.confirmDelete({
        header: '¿Eliminar usuario?',
        message: `El usuario "${usuario.nombre_completo}" (@${usuario.nombre_usuario}) será eliminado.`,
        accept: () => {
            router.delete(route('usuarios.destroy', usuario.id_usuario), {
                preserveScroll: true,
                onSuccess: (page) => {
                    const flash = page?.props?.flash || {};
                    if (!flash.error && !flash.warning) {
                        notify.success(`Usuario "${usuario.nombre_completo}" eliminado correctamente`);
                    }
                },
            });
        },
    });
};

const confirmarRestaurar = (usuario) => {
    if (!permisos.value?.puede_editar_usuarios) {
        notify.error('No tienes permisos para restaurar usuarios. Contacta al administrador.', 'Sin permisos');
        return;
    }

    notify.confirmAction({
        header: '¿Restaurar usuario?',
        message: `El usuario "${usuario.nombre_completo}" (@${usuario.nombre_usuario}) volverá a estar activo.`,
        acceptLabel: 'Sí, restaurar',
        acceptSeverity: 'success',
        icon: 'pi pi-refresh',
        accept: () => {
            router.post(route('usuarios.restore', usuario.id_usuario), {}, {
                preserveScroll: true,
                onSuccess: (page) => {
                    const flash = page?.props?.flash || {};
                    if (!flash.error && !flash.warning) {
                        notify.success(`Usuario "${usuario.nombre_completo}" restaurado correctamente`);
                    }
                },
            });
        },
    });
};
</script>

<style scoped>
/* ===== HEADER ===== */
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

.btn-nuevo-usuario-premium {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: linear-gradient(135deg, #1a3a5c 0%, #2c5282 100%);
    border: none;
    color: #fff;
    font-weight: 700;
    padding: 0 32px;
    height: 50px;
    cursor: pointer;
    box-shadow: 0 4px 16px rgba(26, 58, 92, 0.3);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    font-size: 15px;
    letter-spacing: 0.3px;
}

.btn-nuevo-usuario-premium:hover {
    transform: translateY(-3px) scale(1.02);
    box-shadow: 0 8px 30px rgba(26, 58, 92, 0.4);
    background: linear-gradient(135deg, #2c5282 0%, #1a3a5c 100%);
}

/* ===== STATS ===== */
.stats-grid-usuarios {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
}

@media (max-width: 992px) {
    .stats-grid-usuarios { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 560px) {
    .stats-grid-usuarios { grid-template-columns: 1fr; }
}

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
    border-radius: 30px;
    background: linear-gradient(135deg, #eff6ff, #dbeafe);
    color: #1a3a5c;
    font-weight: 600;
    padding: 4px 16px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
}

.filter-tag-orange {
    background: linear-gradient(135deg, #fff7ed, #ffedd5);
    color: #c2410c;
}

.filter-dot-active {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #1a3a5c;
    display: inline-block;
    animation: pulse 2s infinite;
}

.filter-dot-inactive {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #f59e0b;
    display: inline-block;
    animation: pulse 2s infinite;
}

.tabla-vacia {
    padding: 32px 16px;
    text-align: center;
    color: #94a3b8;
    font-size: 14px;
}

.usuario-table-ultra :deep(.p-datatable-thead > tr > th) {
    background: linear-gradient(135deg, #f1f5f9, #e8edf2);
    font-weight: 700;
    color: #1e293b;
    border-bottom: 2px solid #d1d5db;
    padding: 14px 18px;
    font-size: 12px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}

.usuario-table-ultra :deep(.p-datatable-tbody > tr) {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.usuario-table-ultra :deep(.p-datatable-tbody > tr:hover) {
    background: linear-gradient(90deg, #f8faff, #f0f7ff);
    box-shadow: inset 0 0 0 1px #dbeafe;
}

.usuario-table-ultra :deep(.p-datatable-tbody > tr > td) {
    padding: 14px 18px;
    border-bottom: 1px solid #f1f5f9;
}

/* ===== CELDAS ===== */
.nombre-cell-ultra {
    display: flex;
    align-items: center;
    gap: 10px;
}

.nombre-text-ultra {
    font-size: 14px;
    color: #0f172a;
    font-weight: 500;
}

.text-gray-400 {
    color: #94a3b8 !important;
}

.badge-inactivo-ultra {
    display: inline-block;
    background: #fee2e2;
    color: #991b1b;
    font-size: 10px;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 10px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.usuario-nombre-ultra {
    font-size: 14px;
    color: #334155;
    font-weight: 500;
    font-family: 'Courier New', monospace;
}

/* ===== ROL ===== */
.rol-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 4px 14px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.rol-badge.superusuario { background: #e8eef5; color: #5b21b6; }
.rol-badge.administrador { background: #dbeafe; color: #1d4ed8; }
.rol-badge.auditor { background: #d1fae5; color: #065f46; }
.rol-badge.capturista { background: #fef3c7; color: #92400e; }
.rol-badge.lector { background: #f3f4f6; color: #4b5563; }

.rol-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
}

.rol-dot.dot-superusuario { background: #132a44; }
.rol-dot.dot-administrador { background: #2563eb; }
.rol-dot.dot-auditor { background: #059669; }
.rol-dot.dot-capturista { background: #d97706; }
.rol-dot.dot-lector { background: #6b7280; }

/* ===== RESET ===== */
.btn-reset-ultra {
    width: 34px;
    height: 34px;
    border-radius: 6px;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    background: transparent;
    color: #f59e0b;
}

.btn-reset-ultra:hover {
    background: #fffbeb;
    transform: rotate(180deg) scale(1.1);
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.2);
}

/* ===== ESTADO ===== */
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

.estado-badge-ultra.activo { background: #dcfce7; color: #166534; }
.estado-badge-ultra.inactivo { background: #fee2e2; color: #991b1b; }

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

/* ===== EMPRESAS ===== */
.empresas-hover-wrap {
    position: relative;
    display: inline-block;
}

.empresas-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    align-items: center;
}

.empresa-tag {
    border-radius: 4px;
    font-size: 11px;
    padding: 2px 10px;
    max-width: 90px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    background: #eff6ff;
    color: #1e40af;
}

.empresa-tag-more {
    border-radius: 4px;
    font-size: 11px;
    padding: 2px 8px;
    border: 1px solid #d1d5db;
    background: #f9fafb;
    color: #6b7280;
}

.opacity-50 {
    opacity: 0.5;
}

.contacto-vacio-ultra {
    color: #cbd5e1;
    font-size: 13px;
    font-style: italic;
}

.empresas-hover-panel {
    position: absolute;
    top: 100%;
    left: 0;
    margin-top: 6px;
    z-index: 30;
    min-width: 220px;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.12);
    padding: 12px 16px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-4px);
    transition: all 0.2s ease;
}

.empresas-hover-wrap:hover .empresas-hover-panel {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.empresas-popover-header {
    display: flex;
    align-items: center;
    gap: 8px;
    padding-bottom: 8px;
    border-bottom: 1px solid #f1f5f9;
    font-weight: 600;
    color: #0f172a;
    font-size: 13px;
}

.empresas-popover-list {
    margin-top: 8px;
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.empresa-popover-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 6px 8px;
    border-radius: 6px;
    transition: all 0.2s ease;
    font-size: 13px;
    color: #334155;
}

.empresa-popover-item:hover {
    background: #f1f5f9;
}

.empresa-popover-icon {
    color: #6366f1;
    font-size: 14px;
}

/* ===== ACCIONES ===== */
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

.btn-edit-ultra { color: #eab308; }
.btn-edit-ultra:hover { background: #fefce8; box-shadow: 0 4px 12px rgba(234, 179, 8, 0.2); }
.btn-delete-ultra { color: #ef4444; }
.btn-delete-ultra:hover { background: #fef2f2; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2); }
.btn-restore-ultra { color: #10b981; }
.btn-restore-ultra:hover { background: #ecfdf5; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2); }

/* ===== FILTROS ===== */
.filtros-ultra {
    background: #ffffff;
    padding: 24px 0 0 0;
    border-top: 2px solid #f1f5f9;
}

.filtros-grid-ultra {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr 0.8fr;
    gap: 16px;
    align-items: end;
}

.filtro-item-ultra {
    display: flex;
    flex-direction: column;
    gap: 6px;
    min-width: 0;
}

.filtro-item-ultra :deep(.input-label) {
    font-size: 11px !important;
    font-weight: 600 !important;
    color: #475569 !important;
    margin-bottom: 0 !important;
}

.filtro-item-ultra :deep(.text-input) {
    height: 40px !important;
    font-size: 13px !important;
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
    min-width: 120px;
}

.filtro-select-ultra {
    width: 100% !important;
}

.filtro-select-ultra.p-select {
    border-radius: 0px;
    border: 2px solid #d1d5db;
    background: #ffffff;
    height: 40px;
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
    font-size: 13px;
    padding: 0 12px;
    display: flex;
    align-items: center;
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

.btn-clear-ultra {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    background: linear-gradient(135deg, #1a3a5c, #2c5282);
    border: none;
    color: white;
    height: 40px;
    font-size: 13px;
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
    background: #f8fafc;
    border: 2px dashed #d1d5db;
    cursor: not-allowed;
    height: 40px;
    font-size: 13px;
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

/* ===== MODAL CAMBIAR CONTRASEÑA ===== */
.reset-modal-premium :deep(.p-dialog-content) {
    padding: 0;
    border-radius: 20px;
    overflow: hidden;
    background: linear-gradient(180deg, #ffffff 0%, #fafbfc 100%);
}

.reset-modal-content {
    display: flex;
    flex-direction: column;
    padding: 32px 32px 28px;
}

.reset-modal-header {
    text-align: center;
    margin-bottom: 20px;
    position: relative;
}

.reset-modal-header::after {
    content: '';
    position: absolute;
    bottom: -12px;
    left: 50%;
    transform: translateX(-50%);
    width: 60px;
    height: 3px;
    background: linear-gradient(90deg, #6366f1, #1a3a5c, #ec4899);
    border-radius: 3px;
}

.reset-modal-icon {
    width: 72px;
    height: 72px;
    border-radius: 50%;
    background: linear-gradient(135deg, #fffbeb, #fef3c7);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 14px;
    border: 2px solid #fde68a;
    box-shadow: 0 8px 24px rgba(245, 158, 11, 0.15);
}

.modal-icon-svg {
    width: 32px;
    height: 32px;
    stroke: #f59e0b;
}

.reset-modal-title {
    font-size: 22px;
    font-weight: 700;
    color: #0f172a;
    margin: 0 0 6px 0;
    letter-spacing: -0.3px;
}

.reset-modal-subtitle {
    font-size: 14px;
    color: #64748b;
    margin: 0 0 2px 0;
}

.reset-modal-subtitle strong {
    color: #0f172a;
    font-weight: 600;
}

.reset-modal-username {
    font-size: 13px;
    color: #94a3b8;
    margin: 0;
    font-family: 'Courier New', monospace;
    background: #f1f5f9;
    display: inline-block;
    padding: 2px 16px;
    border-radius: 12px;
}

.reset-password-container {
    background: #ffffff;
    border-radius: 16px;
    padding: 20px 24px 16px;
    border: 1px solid #f1f5f9;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    margin: 8px 0 4px;
}

.form-group-modal {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.form-group-modal:first-child {
    margin-bottom: 6px;
}

.form-label-modal {
    font-size: 0.85rem;
    font-weight: 600;
    color: #374151;
    display: flex;
    align-items: center;
    gap: 8px;
}

.label-icon-wrapper {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border-radius: 8px;
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.12), rgba(245, 158, 11, 0.05));
    color: #f59e0b;
    font-size: 13px;
}

.required-star {
    color: #ef4444;
    font-weight: 700;
    margin-left: 2px;
}

.input-wrapper-modal {
    position: relative;
    display: flex;
    align-items: center;
}

.form-input-modal {
    width: 100%;
    padding: 10px 48px 10px 16px;
    font-size: 0.9rem;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    background: white;
    color: #1f2937;
    transition: all 0.3s ease;
    outline: none;
    height: 44px;
}

.form-input-modal:focus {
    border-color: #6366f1;
    box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.08);
}

.form-input-modal:hover:not(:focus) {
    border-color: #9ca3af;
}

.form-input-modal.error {
    border-color: #ef4444;
}

.btn-toggle-password {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: transparent;
    border: none;
    color: #9ca3af;
    cursor: pointer;
    padding: 4px 6px;
    transition: all 0.3s ease;
    z-index: 2;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    width: 32px;
    height: 32px;
}

.btn-toggle-password:hover {
    color: #6366f1;
    background: #f1f5f9;
}

.error-message-modal {
    font-size: 0.8rem;
    color: #ef4444;
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: 4px;
    padding: 4px 10px;
    background: #fef2f2;
    border-radius: 6px;
}

.success-message-modal {
    font-size: 0.8rem;
    color: #10b981;
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: 4px;
    padding: 4px 10px;
    background: #f0fdf4;
    border-radius: 6px;
}

.success-check {
    font-size: 14px;
    font-weight: 700;
    margin-left: auto;
}

.password-strength-section {
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid #f1f5f9;
}

.strength-title {
    font-size: 12px;
    font-weight: 600;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin: 0 0 8px 0;
}

.reset-password-requirements {
    display: flex;
    flex-wrap: wrap;
    gap: 6px 20px;
}

.requirement-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12.5px;
    color: #94a3b8;
    transition: all 0.3s ease;
}

.requirement-item.met {
    color: #10b981;
}

.req-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 16px;
    height: 16px;
    font-size: 11px;
}

.req-circle {
    color: #d1d5db;
}

.reset-modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding-top: 20px;
    margin-top: 4px;
    border-top: 1px solid #f1f5f9;
}

.btn-cancel-reset {
    border-radius: 10px;
    height: 44px;
    padding: 0 28px;
    font-weight: 600;
    font-size: 14px;
    border: 2px solid #e5e7eb;
    color: #64748b;
    background: transparent;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-cancel-reset:hover {
    border-color: #1a3a5c;
    color: #1a3a5c;
    background: #f8fafc;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
}

.btn-confirm-reset {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border-radius: 10px;
    height: 44px;
    padding: 0 28px;
    font-weight: 600;
    font-size: 14px;
    background: linear-gradient(135deg, #1a3a5c, #2c5282);
    border: none;
    color: #fff;
    cursor: pointer;
    box-shadow: 0 2px 12px rgba(26, 58, 92, 0.25);
    transition: all 0.3s ease;
}

.btn-confirm-reset:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 24px rgba(26, 58, 92, 0.35);
}

.btn-confirm-reset:disabled {
    background: #d1d5db;
    box-shadow: none;
    cursor: not-allowed;
    opacity: 0.6;
}

@keyframes pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(1.2); }
}

@media (max-width: 1024px) {
    .filtros-grid-ultra {
        grid-template-columns: 1fr 1fr 1fr;
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
    .header-premium { padding: 16px 20px; }
    .header-title-premium { font-size: 20px; }
    .header-icon-wrapper { width: 44px; height: 44px; }
    .header-icon-svg { width: 22px; height: 22px; }
    .table-wrapper-premium { padding: 16px; }
    .stats-card-enhanced-value { font-size: 22px; }
    .stats-card-enhanced-icon { width: 44px; height: 44px; }
    .reset-modal-content { padding: 24px 20px 20px; }
    .reset-modal-actions { flex-direction: column; }
    .reset-modal-actions button { width: 100%; justify-content: center; }
    .reset-password-requirements { flex-direction: column; gap: 4px; }
}

@media (max-width: 480px) {
    .filtros-grid-ultra {
        grid-template-columns: 1fr;
        gap: 10px;
    }
    .filtro-actions { grid-column: 1; }
}
</style>
