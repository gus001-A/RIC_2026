<template>
    <AppLayout title="Detalle Empresa">
        <template #header>
            <div class="flex items-center justify-between w-full">
                <div class="flex items-center gap-4">
                    <Link :href="route('empresas.index')" 
                          class="p-2.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-xl transition-all duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                    </Link>
                    <div>
                        <h2 class="font-bold text-2xl text-gray-900 leading-tight tracking-tight">
                            Detalle de Empresa
                        </h2>
                        <p class="text-sm text-gray-500 mt-0.5">Información completa de la empresa</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-medium">
                        ID: {{ empresa.id }}
                    </span>
                    <span :class="empresa.tipo_persona === 'MORAL' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700'"
                          class="px-3 py-1 rounded-full text-xs font-medium">
                        {{ empresa.tipo_persona === 'MORAL' ? '🏢 Moral' : '👤 Física' }}
                    </span>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Tarjeta de empresa -->
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <!-- Banner superior -->
                    <div class="relative h-32 bg-gradient-to-r from-blue-600 via-blue-500 to-indigo-600">
                        <div class="absolute inset-0 bg-black/10"></div>
                        <div class="absolute -bottom-12 left-8 flex items-end gap-6">
                            <!-- Avatar grande -->
                            <div class="relative">
                                <div class="w-28 h-28 rounded-2xl flex items-center justify-center text-white text-5xl font-bold border-4 border-white shadow-xl"
                                     :class="empresa.activo ? 'bg-gradient-to-br from-blue-500 to-blue-700' : 'bg-gradient-to-br from-gray-500 to-gray-700'">
                                    {{ empresa.nombre_empresa?.charAt(0) || 'E' }}
                                </div>
                                <div v-if="empresa.activo" 
                                     class="absolute -bottom-1 -right-1 w-6 h-6 bg-green-400 border-2 border-white rounded-full shadow-md animate-pulse"></div>
                                <div v-else 
                                     class="absolute -bottom-1 -right-1 w-6 h-6 bg-red-400 border-2 border-white rounded-full shadow-md"></div>
                            </div>
                            <!-- Nombre y datos -->
                            <div class="text-white pb-4">
                                <h3 class="text-3xl font-bold drop-shadow-lg">{{ empresa.nombre_empresa }}</h3>
                                <div class="flex items-center gap-3 mt-1">
                                    <span class="text-blue-100 text-sm font-mono">{{ empresa.rfc }}</span>
                                    <span class="text-white/30">|</span>
                                    <span class="text-blue-100 text-sm">Clave: {{ empresa.clave || 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                        <!-- Badge de estado -->
                        <div class="absolute top-4 right-4">
                            <span :class="empresa.activo ? 'bg-green-500/90 text-white' : 'bg-red-500/90 text-white'"
                                  class="px-4 py-2 rounded-xl text-sm font-medium backdrop-blur-sm shadow-lg flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full" :class="empresa.activo ? 'bg-green-200 animate-pulse' : 'bg-red-200'"></span>
                                {{ empresa.activo ? 'Activa' : 'Inactiva' }}
                            </span>
                        </div>
                    </div>

                    <!-- Contenido -->
                    <div class="pt-16 px-8 pb-8">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                            <!-- Columna izquierda: Información General -->
                            <div class="lg:col-span-2">
                                <div class="flex items-center gap-2 mb-6">
                                    <div class="w-1 h-8 bg-blue-500 rounded-full"></div>
                                    <h4 class="text-lg font-semibold text-gray-800">Información General</h4>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="bg-gray-50 rounded-xl p-4 hover:bg-gray-100 transition">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                                <span class="text-lg">🏷️</span>
                                            </div>
                                            <div>
                                                <p class="text-xs text-gray-500 font-medium">Razón Social</p>
                                                <p class="text-sm font-semibold text-gray-800">{{ empresa.razon_social || 'No registrada' }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="bg-gray-50 rounded-xl p-4 hover:bg-gray-100 transition">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                                <span class="text-lg">📋</span>
                                            </div>
                                            <div>
                                                <p class="text-xs text-gray-500 font-medium">Régimen</p>
                                                <p class="text-sm font-semibold text-gray-800">{{ empresa.regimen || 'No registrado' }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="bg-gray-50 rounded-xl p-4 hover:bg-gray-100 transition">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                                <span class="text-lg">📧</span>
                                            </div>
                                            <div>
                                                <p class="text-xs text-gray-500 font-medium">Correo Electrónico</p>
                                                <a :href="`mailto:${empresa.correo}`" 
                                                   class="text-sm font-semibold text-blue-600 hover:text-blue-800 hover:underline">
                                                    {{ empresa.correo || 'No registrado' }}
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="bg-gray-50 rounded-xl p-4 hover:bg-gray-100 transition">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-yellow-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                                <span class="text-lg">📱</span>
                                            </div>
                                            <div>
                                                <p class="text-xs text-gray-500 font-medium">Teléfono Personal</p>
                                                <p class="text-sm font-semibold text-gray-800">{{ empresa.telefono_personal || 'No registrado' }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="bg-gray-50 rounded-xl p-4 hover:bg-gray-100 transition">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                                <span class="text-lg">🏢</span>
                                            </div>
                                            <div>
                                                <p class="text-xs text-gray-500 font-medium">Teléfono Trabajo</p>
                                                <p class="text-sm font-semibold text-gray-800">{{ empresa.telefono_trabajo || 'No registrado' }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="bg-gray-50 rounded-xl p-4 hover:bg-gray-100 transition">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-rose-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                                <span class="text-lg">🔢</span>
                                            </div>
                                            <div>
                                                <p class="text-xs text-gray-500 font-medium">Extensión</p>
                                                <p class="text-sm font-semibold text-gray-800">{{ empresa.extension || 'No registrada' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Datos del Representante (solo para MORAL) -->
                                <div v-if="empresa.tipo_persona === 'MORAL'" class="mt-8">
                                    <div class="flex items-center gap-2 mb-6">
                                        <div class="w-1 h-8 bg-purple-500 rounded-full"></div>
                                        <h4 class="text-lg font-semibold text-gray-800">👤 Representante Legal</h4>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="bg-purple-50 rounded-xl p-4 hover:bg-purple-100 transition border border-purple-100">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 bg-purple-200 rounded-xl flex items-center justify-center flex-shrink-0">
                                                    <span class="text-lg">👤</span>
                                                </div>
                                                <div>
                                                    <p class="text-xs text-gray-500 font-medium">Nombre Completo</p>
                                                    <p class="text-sm font-semibold text-gray-800">
                                                        {{ empresa.representante_nombre || 'N/A' }}
                                                        {{ empresa.representante_apellido_paterno || '' }}
                                                        {{ empresa.representante_apellido_materno || '' }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="bg-purple-50 rounded-xl p-4 hover:bg-purple-100 transition border border-purple-100">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 bg-purple-200 rounded-xl flex items-center justify-center flex-shrink-0">
                                                    <span class="text-lg">📋</span>
                                                </div>
                                                <div>
                                                    <p class="text-xs text-gray-500 font-medium">RFC del Representante</p>
                                                    <p class="text-sm font-mono font-semibold text-gray-800">{{ empresa.representante_rfc || 'No registrado' }}</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="md:col-span-2 bg-purple-50 rounded-xl p-4 hover:bg-purple-100 transition border border-purple-100">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 bg-purple-200 rounded-xl flex items-center justify-center flex-shrink-0">
                                                    <span class="text-lg">🆔</span>
                                                </div>
                                                <div>
                                                    <p class="text-xs text-gray-500 font-medium">CURP del Representante</p>
                                                    <p class="text-sm font-mono font-semibold text-gray-800">{{ empresa.representante_curp || 'No registrado' }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Columna derecha: Resumen y acciones -->
                            <div class="lg:col-span-1">
                                <div class="bg-gradient-to-br from-gray-50 to-gray-100/50 rounded-2xl p-6 border border-gray-200 sticky top-6">
                                    <h5 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
                                        <span class="text-lg">📊</span>
                                        Estadísticas
                                    </h5>
                                    
                                    <div class="space-y-3">
                                        <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                            <span class="text-sm text-gray-600 flex items-center gap-2">
                                                <span class="text-lg">👥</span> Usuarios
                                            </span>
                                            <span class="font-bold text-blue-600 text-lg">{{ stats?.total_usuarios || 0 }}</span>
                                        </div>
                                        <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                            <span class="text-sm text-gray-600 flex items-center gap-2">
                                                <span class="text-lg">💰</span> Cuentas
                                            </span>
                                            <span class="font-bold text-green-600 text-lg">{{ stats?.total_cuentas || 0 }}</span>
                                        </div>
                                        <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                            <span class="text-sm text-gray-600 flex items-center gap-2">
                                                <span class="text-lg">📋</span> Pólizas
                                            </span>
                                            <span class="font-bold text-purple-600 text-lg">{{ stats?.total_polizas || 0 }}</span>
                                        </div>
                                        <div class="flex justify-between items-center py-2">
                                            <span class="text-sm text-gray-600 flex items-center gap-2">
                                                <span class="text-lg">🏷️</span> Tipo
                                            </span>
                                            <span :class="empresa.tipo_persona === 'MORAL' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700'"
                                                  class="px-2 py-1 rounded text-xs font-medium">
                                                {{ empresa.tipo_persona === 'MORAL' ? 'Moral' : 'Física' }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="mt-6 pt-6 border-t border-gray-200">
                                        <h5 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                                            <span class="text-lg">⚡</span>
                                            Acciones Rápidas
                                        </h5>
                                        
                                        <div class="space-y-2">
                                            <Link :href="route('empresas.edit', empresa.id)"
                                                  class="w-full px-4 py-2.5 bg-gradient-to-r from-yellow-500 to-yellow-600 text-white rounded-xl hover:from-yellow-600 hover:to-yellow-700 transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2 text-sm font-medium">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                                Editar Empresa
                                            </Link>

                                            <button @click="cambiarEstado"
                                                    :class="empresa.activo ? 'from-red-500 to-red-600 hover:from-red-600 hover:to-red-700' : 'from-green-500 to-green-600 hover:from-green-600 hover:to-green-700'"
                                                    class="w-full px-4 py-2.5 bg-gradient-to-r text-white rounded-xl transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2 text-sm font-medium">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                                </svg>
                                                {{ empresa.activo ? 'Desactivar' : 'Activar' }}
                                            </button>

                                            <button @click="eliminarEmpresa"
                                                    class="w-full px-4 py-2.5 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-xl hover:from-red-700 hover:to-red-800 transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2 text-sm font-medium">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                                Eliminar Empresa
                                            </button>

                                            <div class="pt-2 mt-2 border-t border-gray-200">
                                                <Link :href="route('empresas.index')"
                                                      class="w-full px-4 py-2 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 transition flex items-center justify-center gap-2 text-sm font-medium">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                                    </svg>
                                                    Volver al listado
                                                </Link>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Información adicional -->
                                    <div class="mt-6 pt-4 border-t border-gray-200">
                                        <p class="text-xs text-gray-400">
                                            Creada: {{ formatDate(empresa.created_at) }}
                                        </p>
                                        <p class="text-xs text-gray-400">
                                            Actualizada: {{ formatDate(empresa.updated_at) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

const props = defineProps({
    empresa: Object,
    stats: Object
});

const cambiarEstado = () => {
    const action = props.empresa.activo ? 'desactivar' : 'activar';
    
    Swal.fire({
        title: `¿${action === 'desactivar' ? 'Desactivar' : 'Activar'} empresa?`,
        text: `La empresa ${props.empresa.nombre_empresa} quedará ${action === 'desactivar' ? 'inactiva' : 'activa'}`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: props.empresa.activo ? '#f43f5e' : '#10b981',
        cancelButtonColor: '#6b7280',
        confirmButtonText: `Sí, ${action === 'desactivar' ? 'desactivar' : 'activar'}`,
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
        customClass: {
            confirmButton: 'px-5 py-2.5 rounded-xl font-medium',
            cancelButton: 'px-5 py-2.5 rounded-xl font-medium'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            router.post(route('empresas.cambiar-estado', props.empresa.id), {}, {
                preserveScroll: true,
                onSuccess: () => {
                    Swal.fire({
                        title: '¡Hecho!',
                        text: `Empresa ${action === 'desactivar' ? 'desactivada' : 'activada'} correctamente`,
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false,
                        timerProgressBar: true
                    });
                }
            });
        }
    });
};

const eliminarEmpresa = () => {
    Swal.fire({
        title: '¿Eliminar empresa?',
        html: `
            <p class="text-gray-600">Esta acción no se puede deshacer</p>
            <p class="text-sm text-rose-600 mt-2 font-medium">⚠️ Se eliminarán todos los datos asociados</p>
        `,
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#f43f5e',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
        customClass: {
            confirmButton: 'px-5 py-2.5 rounded-xl font-medium',
            cancelButton: 'px-5 py-2.5 rounded-xl font-medium'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('empresas.destroy', props.empresa.id), {
                onSuccess: () => {
                    Swal.fire({
                        title: '¡Eliminada!',
                        text: 'La empresa ha sido eliminada correctamente',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false,
                        timerProgressBar: true
                    });
                }
            });
        }
    });
};

const formatDate = (date) => {
    if (!date) return 'Nunca';
    return new Date(date).toLocaleDateString('es-MX', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
    });
};
</script>

<style scoped>
/* Animaciones suaves */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.rounded-2xl {
    animation: fadeInUp 0.4s ease-out;
}

/* Efectos hover mejorados */
.group:hover {
    transform: translateY(-1px);
}

/* Transiciones suaves */
* {
    transition-property: background-color, border-color, color, fill, stroke, opacity, box-shadow, transform;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
}

/* Sombras mejoradas */
.shadow-sm {
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}
</style>