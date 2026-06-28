<template>
    <AppLayout :title="pageTitle">
        <template #header>
            <div class="header-premium">
                <div class="header-content-premium">
                    <div class="header-left-premium">
                        <div class="header-icon-wrapper">
                            <svg class="header-icon-svg" fill="none" stroke="white" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="header-title-premium">
                                Dashboard
                            </h2>
                            <p class="header-subtitle-premium">
                                <span class="subtitle-line"></span>
                                <span>Bienvenido, {{ usuario?.nombre_completo || 'Usuario' }}</span>
                            </p>
                        </div>
                    </div>
                    <div class="header-actions-premium">
                        <span class="fecha-actual">{{ fechaActual }}</span>
                    </div>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Stats Cards -->
                <a-row :gutter="[20, 20]" class="mb-6">
                    <!-- Usuarios - Solo Admin, Auditor, Super -->
                    <a-col v-if="permisos?.puede_ver_usuarios" :xs="24" :sm="12" :md="6">
                        <div class="stats-card-enhanced">
                            <div class="stats-card-enhanced-content">
                                <div class="stats-card-enhanced-left">
                                    <span class="stats-card-enhanced-label">Usuarios</span>
                                    <span class="stats-card-enhanced-value">{{ stats.total_usuarios || 0 }}</span>
                                </div>
                                <div class="stats-card-enhanced-icon" style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(59, 130, 246, 0.05));">
                                    <svg class="w-7 h-7" fill="none" stroke="#3b82f6" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="stats-card-enhanced-progress">
                                <div class="stats-card-enhanced-progress-bar" :style="{ 
                                    width: stats.total_usuarios ? Math.round((stats.usuarios_activos / stats.total_usuarios) * 100) + '%' : '0%',
                                    background: 'linear-gradient(90deg, #3b82f6, #2563eb)'
                                }"></div>
                            </div>
                            <div class="stats-card-enhanced-footer">
                                <span class="text-xs text-green-600">{{ stats.usuarios_activos || 0 }} activos</span>
                                <span class="text-xs text-gray-400">{{ stats.usuarios_inactivos || 0 }} inactivos</span>
                            </div>
                        </div>
                    </a-col>

                    <!-- Empresas - Solo Super Usuario -->
                    <a-col v-if="permisos?.puede_ver_empresas" :xs="24" :sm="12" :md="6">
                        <div class="stats-card-enhanced">
                            <div class="stats-card-enhanced-content">
                                <div class="stats-card-enhanced-left">
                                    <span class="stats-card-enhanced-label">Empresas</span>
                                    <span class="stats-card-enhanced-value">{{ stats.total_empresas || 0 }}</span>
                                </div>
                                <div class="stats-card-enhanced-icon" style="background: linear-gradient(135deg, rgba(139, 92, 246, 0.1), rgba(139, 92, 246, 0.05));">
                                    <svg class="w-7 h-7" fill="none" stroke="#8b5cf6" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="stats-card-enhanced-progress">
                                <div class="stats-card-enhanced-progress-bar" :style="{ 
                                    width: stats.total_empresas ? Math.round((stats.empresas_activas / stats.total_empresas) * 100) + '%' : '0%',
                                    background: 'linear-gradient(90deg, #8b5cf6, #7c3aed)'
                                }"></div>
                            </div>
                            <div class="stats-card-enhanced-footer">
                                <span class="text-xs text-green-600">{{ stats.empresas_activas || 0 }} activas</span>
                                <span class="text-xs text-gray-400">{{ stats.empresas_inactivas || 0 }} inactivas</span>
                            </div>
                        </div>
                    </a-col>

                    <!-- Personas - Todos pueden ver -->
                    <a-col :xs="24" :sm="12" :md="6">
                        <div class="stats-card-enhanced">
                            <div class="stats-card-enhanced-content">
                                <div class="stats-card-enhanced-left">
                                    <span class="stats-card-enhanced-label">Personas</span>
                                    <span class="stats-card-enhanced-value">{{ stats.total_personas || 0 }}</span>
                                </div>
                                <div class="stats-card-enhanced-icon" style="background: linear-gradient(135deg, rgba(34, 197, 94, 0.1), rgba(34, 197, 94, 0.05));">
                                    <svg class="w-7 h-7" fill="none" stroke="#22c55e" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="stats-card-enhanced-progress">
                                <div class="stats-card-enhanced-progress-bar" :style="{ 
                                    width: stats.total_personas ? Math.round((stats.personas_activas / stats.total_personas) * 100) + '%' : '0%',
                                    background: 'linear-gradient(90deg, #22c55e, #16a34a)'
                                }"></div>
                            </div>
                            <div class="stats-card-enhanced-footer">
                                <span class="text-xs text-green-600">{{ stats.personas_activas || 0 }} activas</span>
                                <span class="text-xs text-gray-400">{{ stats.personas_fisicas || 0 }} físicas · {{ stats.personas_morales || 0 }} morales</span>
                            </div>
                        </div>
                    </a-col>

                    <!-- Pólizas - Todos pueden ver -->
                    <a-col :xs="24" :sm="12" :md="6">
                        <div class="stats-card-enhanced">
                            <div class="stats-card-enhanced-content">
                                <div class="stats-card-enhanced-left">
                                    <span class="stats-card-enhanced-label">Pólizas</span>
                                    <span class="stats-card-enhanced-value">{{ stats.total_polizas || 0 }}</span>
                                </div>
                                <div class="stats-card-enhanced-icon" style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(245, 158, 11, 0.05));">
                                    <svg class="w-7 h-7" fill="none" stroke="#f59e0b" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="stats-card-enhanced-progress">
                                <div class="stats-card-enhanced-progress-bar multi">
                                    <div class="progress-segment pending" :style="{ width: stats.total_polizas ? Math.round((stats.polizas_pendientes / stats.total_polizas) * 100) + '%' : '0%' }"></div>
                                    <div class="progress-segment abonado" :style="{ width: stats.total_polizas ? Math.round((stats.polizas_abonadas / stats.total_polizas) * 100) + '%' : '0%' }"></div>
                                    <div class="progress-segment liquidado" :style="{ width: stats.total_polizas ? Math.round((stats.polizas_liquidadas / stats.total_polizas) * 100) + '%' : '0%' }"></div>
                                </div>
                            </div>
                            <div class="stats-card-enhanced-footer">
                                <div class="flex gap-2">
                                    <span class="text-xs text-yellow-600">🟡 {{ stats.polizas_pendientes || 0 }}</span>
                                    <span class="text-xs text-blue-600">🔵 {{ stats.polizas_abonadas || 0 }}</span>
                                    <span class="text-xs text-green-600">🟢 {{ stats.polizas_liquidadas || 0 }}</span>
                                </div>
                                <span class="text-xs text-gray-400">{{ stats.polizas_ingresos || 0 }} ingresos · {{ stats.polizas_egresos || 0 }} egresos</span>
                            </div>
                        </div>
                    </a-col>
                </a-row>

                <!-- Segunda fila de estadísticas -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                    <!-- Movimientos del día - Todos pueden ver -->
                    <div class="stats-card-enhanced">
                        <div class="stats-card-enhanced-content">
                            <div class="stats-card-enhanced-left">
                                <span class="stats-card-enhanced-label">📅 Movimientos Hoy</span>
                                <span class="stats-card-enhanced-value text-2xl">{{ stats.polizas_hoy || 0 }}</span>
                            </div>
                            <div class="stats-card-enhanced-icon" style="background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(99, 102, 241, 0.05));">
                                <svg class="w-7 h-7" fill="none" stroke="#6366f1" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="stats-card-enhanced-footer">
                            <div class="flex gap-4">
                                <span class="text-sm text-green-600">↑ Ingresos: {{ stats.ingresos_hoy || 0 }}</span>
                                <span class="text-sm text-red-600">↓ Egresos: {{ stats.egresos_hoy || 0 }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Resumen Financiero - Todos pueden ver -->
                    <div class="stats-card-enhanced">
                        <div class="stats-card-enhanced-content">
                            <div class="stats-card-enhanced-left">
                                <span class="stats-card-enhanced-label">💰 Resumen Financiero</span>
                                <span class="stats-card-enhanced-value text-2xl" :class="stats.balance >= 0 ? 'text-green-600' : 'text-red-600'">
                                    ${{ formatNumber(stats.balance) }}
                                </span>
                            </div>
                            <div class="stats-card-enhanced-icon" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(16, 185, 129, 0.05));">
                                <svg class="w-7 h-7" fill="none" stroke="#10b981" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="stats-card-enhanced-footer">
                            <div class="flex gap-4">
                                <span class="text-sm text-green-600">Ingresos: ${{ formatNumber(stats.total_ingresos) }}</span>
                                <span class="text-sm text-red-600">Egresos: ${{ formatNumber(stats.total_egresos) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Cuentas y Cajas - Todos pueden ver -->
                    <div class="stats-card-enhanced">
                        <div class="stats-card-enhanced-content">
                            <div class="stats-card-enhanced-left">
                                <span class="stats-card-enhanced-label">🏦 Cuentas y Cajas</span>
                                <span class="stats-card-enhanced-value text-2xl">{{ stats.total_cuentas || 0 }}</span>
                            </div>
                            <div class="stats-card-enhanced-icon" style="background: linear-gradient(135deg, rgba(139, 92, 246, 0.1), rgba(139, 92, 246, 0.05));">
                                <svg class="w-7 h-7" fill="none" stroke="#8b5cf6" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="stats-card-enhanced-footer">
                            <div class="flex gap-4 flex-wrap">
                                <span class="text-xs text-blue-600">Cuentas: {{ stats.cuentas_en_uso || 0 }} en uso</span>
                                <span class="text-xs text-green-600">Cajas: {{ stats.cajas_activas || 0 }} activas</span>
                                <span class="text-xs text-purple-600">Marcadores: {{ stats.marcadores_activos || 0 }} activos</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Últimos Movimientos -->
                <div class="table-wrapper-premium">
                    <div class="table-header-ultra">
                        <div class="table-header-left-ultra">
                            <h3 class="text-lg font-semibold text-[#0A1628] flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="#1a3a5c" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                Últimos Movimientos
                            </h3>
                        </div>
                        <div class="table-header-right-ultra">
                            <span class="text-xs text-gray-400">{{ ultimasPolizas.length }} movimientos</span>
                        </div>
                    </div>

                    <div class="table-scroll-container">
                        <a-table
                            :columns="columnsMovimientos"
                            :data-source="ultimasPolizas"
                            :pagination="false"
                            :loading="loading"
                            row-key="id"
                            class="movimientos-table"
                            size="middle"
                            :scroll="{ x: 800 }"
                        >
                            <template #bodyCell="{ column, record }">
                                <template v-if="column.key === 'folio'">
                                    <span class="font-medium text-[#0A1628]">{{ record.folio }}</span>
                                </template>

                                <template v-if="column.key === 'tipo'">
                                    <span :class="record.tipo === 'INGRESO' ? 'text-green-600' : 'text-red-600'" class="font-medium">
                                        {{ record.tipo_texto || record.tipo }}
                                    </span>
                                </template>

                                <template v-if="column.key === 'persona'">
                                    <span class="text-gray-600">{{ record.persona || 'Sin persona' }}</span>
                                </template>

                                <template v-if="column.key === 'fecha'">
                                    <span class="text-gray-500">{{ record.fecha }}</span>
                                </template>

                                <template v-if="column.key === 'total'">
                                    <span class="font-bold" :class="record.tipo === 'INGRESO' ? 'text-green-600' : 'text-red-600'">
                                        ${{ record.total }}
                                    </span>
                                </template>

                                <template v-if="column.key === 'estatus'">
                                    <span :class="{
                                        'bg-yellow-100 text-yellow-700': record.estatus === 'PENDIENTE',
                                        'bg-blue-100 text-blue-700': record.estatus === 'ABONADO',
                                        'bg-green-100 text-green-700': record.estatus === 'LIQUIDADO'
                                    }" class="px-3 py-1 rounded-full text-xs font-medium">
                                        {{ record.estatus_texto || record.estatus }}
                                    </span>
                                </template>
                            </template>
                        </a-table>
                    </div>

                    <div v-if="ultimasPolizas.length === 0" class="text-center py-8">
                        <div class="text-6xl mb-4 opacity-50">📭</div>
                        <p class="text-gray-400 font-medium">No hay movimientos recientes</p>
                        <p class="text-gray-300 text-sm mt-1">Los nuevos movimientos aparecerán aquí</p>
                    </div>
                </div>

                <!-- Mis Empresas - Todos pueden ver (filtrado por sus empresas) -->
                <div v-if="empresasUsuario.length > 0" class="table-wrapper-premium mt-6">
                    <div class="table-header-ultra">
                        <div class="table-header-left-ultra">
                            <h3 class="text-lg font-semibold text-[#0A1628] flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="#1a3a5c" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                Mis Empresas
                            </h3>
                        </div>
                        <div class="table-header-right-ultra">
                            <span class="text-xs text-gray-400">{{ empresasUsuario.length }} empresas</span>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <span v-for="empresa in empresasUsuario" :key="empresa.id"
                              class="px-4 py-2 bg-gradient-to-r from-blue-50 to-blue-100/50 text-[#0A1628] rounded-full text-sm font-medium border border-blue-200/50 hover:shadow-md transition-all duration-300 hover:scale-105 cursor-default flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-[#1a3a5c] text-white flex items-center justify-center text-xs font-bold">
                                {{ empresa.nombre?.charAt(0) || 'E' }}
                            </span>
                            {{ empresa.nombre }}
                            <span v-if="empresa.rfc" class="text-xs text-gray-400 font-mono">({{ empresa.rfc }})</span>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de validaciones personalizado -->
        <AlertModal ref="alertModal" />
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted, nextTick, watch } from 'vue';
import AlertModal from '@/Components/AlertModal.vue';

// Importar componentes de Ant Design
import {
    Row as ARow,
    Col as ACol,
    Table as ATable,
} from 'ant-design-vue';

// ============================================
// ✅ PERMISOS DESDE EL BACKEND
// ============================================
const page = usePage();
const permisos = computed(() => page.props.permisos || {});

const pageTitle = 'RIC - Dashboard';

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({})
    },
    ultimasPolizas: {
        type: Array,
        default: () => []
    },
    empresasUsuario: {
        type: Array,
        default: () => []
    },
    usuario: {
        type: Object,
        default: () => ({})
    },
    flash: {
        type: Object,
        default: () => ({})
    }
});

const loading = ref(false);
const fechaActual = ref('');
const alertModal = ref(null);

// ============================================
// SISTEMA DE ALERTAS CON SWEETALERT2
// ============================================

// Función para mostrar el modal
const mostrarModal = (type, title, message, duration = 4000, onConfirm = null) => {
    if (alertModal.value && alertModal.value.show) {
        alertModal.value.show({
            type,
            title,
            message,
            duration,
            buttonText: type === 'error' ? 'Entendido' : 'Aceptar'
        }, onConfirm);
    } else {
        // Fallback si AlertModal no está disponible
        console.warn('AlertModal no disponible');
        alert(`${title}: ${message}`);
        if (onConfirm) onConfirm();
    }
};

// Procesar mensajes flash automáticamente
const procesarFlash = () => {
    if (!props.flash) return;
    
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
            mostrarModal(
                tipoMap[key].type,
                tipoMap[key].title,
                message
            );
            break; // Mostrar solo el primer mensaje encontrado
        }
    }
};

// Watch para detectar cambios en flash
watch(() => props.flash, (newFlash) => {
    if (newFlash && Object.keys(newFlash).length > 0) {
        nextTick(() => {
            procesarFlash();
        });
    }
}, { deep: true, immediate: true });

// También escuchar el flash de la página
watch(() => page.props.flash, (newFlash) => {
    if (newFlash && Object.keys(newFlash).length > 0) {
        nextTick(() => {
            procesarFlash();
        });
    }
}, { deep: true, immediate: true });

// ============================================
// FIN DEL SISTEMA DE ALERTAS
// ============================================

// Columnas de la tabla de movimientos
const columnsMovimientos = [
    {
        title: 'Folio',
        key: 'folio',
        width: 120,
    },
    {
        title: 'Tipo',
        key: 'tipo',
        width: 100,
        align: 'center'
    },
    {
        title: 'Persona',
        key: 'persona',
        width: 200,
    },
    {
        title: 'Fecha',
        key: 'fecha',
        width: 150,
    },
    {
        title: 'Total',
        key: 'total',
        width: 120,
        align: 'right'
    },
    {
        title: 'Estatus',
        key: 'estatus',
        width: 120,
        align: 'center'
    }
];

// Formatear números
const formatNumber = (value) => {
    if (value === null || value === undefined) return '0.00';
    return Number(value).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
};

// Fecha actual
const obtenerFechaActual = () => {
    const now = new Date();
    const options = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };
    return now.toLocaleDateString('es-MX', options);
};

// ============================================
// AGREGAR ESTILOS PARA SWEETALERT2
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
    `;
    document.head.appendChild(style);
};

onMounted(() => {
    fechaActual.value = obtenerFechaActual();
    agregarEstilosSwal();
    
    // Procesar flash al montar
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

.header-actions-premium {
    display: flex;
    align-items: center;
    gap: 12px;
}

.fecha-actual {
    font-size: 14px;
    color: #64748b;
    font-weight: 500;
}

/* ===== STATS CARDS ===== */
.stats-card-enhanced {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid #f0f2f5;
    overflow: hidden;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    height: 100%;
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
    margin: 0 20px;
}

.stats-card-enhanced-progress-bar {
    height: 100%;
    transition: width 1.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.stats-card-enhanced-progress-bar.multi {
    display: flex;
}

.progress-segment.pending {
    background: #f59e0b;
}

.progress-segment.abonado {
    background: #3b82f6;
}

.progress-segment.liquidado {
    background: #22c55e;
}

.stats-card-enhanced-footer {
    padding: 12px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 4px;
}

/* ===== TABLA DE MOVIMIENTOS ===== */
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

.table-header-right-ultra {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}

.table-scroll-container {
    overflow: hidden;
    border-radius: 8px;
    border: 1px solid #f1f5f9;
}

/* ===== TABLA ANT DESIGN ===== */
.movimientos-table {
    width: 100%;
}

.movimientos-table :deep(.ant-table) {
    border-radius: 0;
}

.movimientos-table :deep(.ant-table-thead > tr > th) {
    background: linear-gradient(135deg, #f1f5f9, #e8edf2) !important;
    font-weight: 700;
    color: #1e293b;
    border-bottom: 2px solid #d1d5db;
    padding: 12px 16px;
    font-size: 11px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}

.movimientos-table :deep(.ant-table-thead > tr > th:first-child) {
    border-radius: 0 !important;
}

.movimientos-table :deep(.ant-table-thead > tr > th:last-child) {
    border-radius: 0 !important;
}

.movimientos-table :deep(.ant-table-tbody > tr) {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.movimientos-table :deep(.ant-table-tbody > tr:hover) {
    background: linear-gradient(90deg, #f8faff, #f0f7ff) !important;
    box-shadow: inset 0 0 0 1px #dbeafe;
}

.movimientos-table :deep(.ant-table-tbody > tr:last-child td) {
    border-bottom: none;
}

.movimientos-table :deep(.ant-table-cell) {
    padding: 10px 14px;
    border-bottom: 1px solid #f1f5f9;
    font-size: 13px;
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

    .stats-card-enhanced-value {
        font-size: 22px;
    }

    .stats-card-enhanced-icon {
        width: 44px;
        height: 44px;
    }
}

@media (max-width: 480px) {
    .header-left-premium {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .stats-card-enhanced-footer {
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
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

/* ===== ESTILOS PARA SWEETALERT2 ===== */
:deep(.swal-premium-popup) {
    border-radius: 20px !important;
    box-shadow: 0 25px 80px rgba(0, 0, 0, 0.3) !important;
    padding: 0 !important;
    overflow: hidden !important;
}

:deep(.swal-premium-confirm) {
    padding: 10px 28px !important;
    font-weight: 600 !important;
    border-radius: 10px !important;
    transition: all 0.3s ease !important;
}

:deep(.swal-premium-confirm:hover) {
    transform: translateY(-2px) scale(1.02);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2) !important;
}

:deep(.swal-premium-cancel) {
    padding: 10px 28px !important;
    font-weight: 600 !important;
    border-radius: 10px !important;
    transition: all 0.3s ease !important;
}

:deep(.swal-premium-cancel:hover) {
    transform: translateY(-2px);
    background: #f1f5f9 !important;
}

:deep(.swal2-actions) {
    padding: 0 32px 24px !important;
    gap: 12px !important;
}

:deep(.swal2-timer-progress-bar) {
    height: 4px !important;
    background: linear-gradient(90deg, #1a3a5c, #2c5282) !important;
}
</style>