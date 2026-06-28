<template>
    <Head title="Registro - RIC 2026" />

    <div class="min-h-screen flex">
        <!-- Columna Izquierda - Imagen/Branding -->
        <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden bg-gradient-to-br from-blue-600 to-blue-800">
            <div class="absolute inset-0 opacity-10">
                <svg class="absolute inset-0 w-full h-full" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <pattern id="pattern2" x="0" y="0" width="40" height="40" patternUnits="userSpaceOnUse">
                            <path d="M0 20 L20 0 L40 20 L20 40 Z" fill="none" stroke="white" stroke-width="0.5"/>
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#pattern2)" />
                </svg>
            </div>

            <div class="relative z-10 flex flex-col justify-center items-center w-full px-12 text-center text-white">
                <div class="mb-8">
                    <div class="w-32 h-32 mx-auto bg-white/10 backdrop-blur-sm rounded-2xl flex items-center justify-center border border-white/20">
                        <span class="text-7xl">📊</span>
                    </div>
                </div>

                <h1 class="text-4xl font-bold mb-4">Únete a RIC 2026</h1>
                <p class="text-xl font-light text-blue-100 mb-6">
                    Crea tu cuenta y comienza a gestionar tu contabilidad
                </p>

                <div class="grid grid-cols-1 gap-4 w-full max-w-sm">
                    <div class="flex items-center gap-3 p-3 bg-white/10 backdrop-blur-sm rounded-lg border border-white/10">
                        <span class="text-2xl">📋</span>
                        <span class="text-sm text-blue-100">Registro de movimientos contables</span>
                    </div>
                    <div class="flex items-center gap-3 p-3 bg-white/10 backdrop-blur-sm rounded-lg border border-white/10">
                        <span class="text-2xl">👥</span>
                        <span class="text-sm text-blue-100">Gestión de usuarios y empresas</span>
                    </div>
                    <div class="flex items-center gap-3 p-3 bg-white/10 backdrop-blur-sm rounded-lg border border-white/10">
                        <span class="text-2xl">📈</span>
                        <span class="text-sm text-blue-100">Reportes y análisis financiero</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna Derecha - Formulario de Registro -->
        <div class="flex-1 flex items-center justify-center px-4 sm:px-6 lg:px-8 bg-gradient-to-br from-blue-50 to-white py-8">
            <div class="w-full max-w-md">
                <div class="lg:hidden flex justify-center mb-8">
                    <div class="w-20 h-20 bg-blue-600 rounded-2xl flex items-center justify-center shadow-lg">
                        <span class="text-4xl">📊</span>
                    </div>
                </div>

                <div class="text-center lg:text-left mb-8">
                    <h2 class="text-3xl font-bold text-gray-800">Crear Cuenta</h2>
                    <p class="text-gray-500 mt-2">Completa el formulario para registrarte</p>
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre Completo</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-400">👤</span>
                            </div>
                            <input
                                type="text"
                                class="pl-10 w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                :class="{ 'border-red-500': form.errors.name }"
                                v-model="form.name"
                                required
                                placeholder="Tu nombre completo"
                            />
                        </div>
                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Correo Electrónico</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-400">📧</span>
                            </div>
                            <input
                                type="email"
                                class="pl-10 w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                :class="{ 'border-red-500': form.errors.email }"
                                v-model="form.email"
                                required
                                placeholder="ejemplo@correo.com"
                            />
                        </div>
                        <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-400">🔒</span>
                            </div>
                            <input
                                :type="showPassword ? 'text' : 'password'"
                                class="pl-10 pr-12 w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                :class="{ 'border-red-500': form.errors.password }"
                                v-model="form.password"
                                required
                                placeholder="Mínimo 8 caracteres"
                            />
                            <button type="button" @click="showPassword = !showPassword" 
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                {{ showPassword ? '👁️' : '👁️‍🗨️' }}
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</p>
                        <div class="mt-2 flex flex-wrap gap-2 text-xs">
                            <span :class="form.password.length >= 8 ? 'text-green-500' : 'text-gray-400'">
                                {{ form.password.length >= 8 ? '✅' : '⬜' }} 8+ caracteres
                            </span>
                            <span :class="/[A-Z]/.test(form.password) ? 'text-green-500' : 'text-gray-400'">
                                {{ /[A-Z]/.test(form.password) ? '✅' : '⬜' }} Mayúscula
                            </span>
                            <span :class="/[a-z]/.test(form.password) ? 'text-green-500' : 'text-gray-400'">
                                {{ /[a-z]/.test(form.password) ? '✅' : '⬜' }} Minúscula
                            </span>
                            <span :class="/[0-9]/.test(form.password) ? 'text-green-500' : 'text-gray-400'">
                                {{ /[0-9]/.test(form.password) ? '✅' : '⬜' }} Número
                            </span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar Contraseña</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-400">🔐</span>
                            </div>
                            <input
                                type="password"
                                class="pl-10 w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                :class="{ 'border-red-500': form.errors.password_confirmation }"
                                v-model="form.password_confirmation"
                                required
                                placeholder="Repite tu contraseña"
                            />
                        </div>
                        <p v-if="form.errors.password_confirmation" class="mt-1 text-sm text-red-600">{{ form.errors.password_confirmation }}</p>
                    </div>

                    <div class="flex items-start">
                        <input type="checkbox" v-model="acceptTerms"
                               class="mt-1 rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-600">
                            Acepto los <a href="#" class="text-blue-600 hover:underline">términos y condiciones</a>
                        </span>
                    </div>

                    <button type="submit" 
                            :disabled="form.processing || !acceptTerms"
                            class="w-full flex justify-center items-center gap-2 px-4 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition disabled:opacity-50">
                        <span v-if="form.processing" class="inline-block animate-spin">⟳</span>
                        {{ form.processing ? 'Registrando...' : '📝 Crear Cuenta' }}
                    </button>

                    <div class="text-center">
                        <p class="text-sm text-gray-600">
                            ¿Ya tienes cuenta?
                            <Link :href="route('login')" class="font-medium text-blue-600 hover:text-blue-800 hover:underline">
                                Inicia sesión aquí
                            </Link>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const acceptTerms = ref(false);
const showPassword = ref(false);

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>