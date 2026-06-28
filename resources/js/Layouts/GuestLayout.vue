<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gradient-to-br from-blue-50 to-indigo-100">
        <!-- Logo -->
        <div class="mb-6">
            <Link href="/" class="flex items-center gap-3">
                <span class="text-5xl">📊</span>
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">RIC 2026</h1>
                    <p class="text-sm text-gray-500">Sistema de Registro de Información Contable</p>
                </div>
            </Link>
        </div>

        <!-- Card -->
        <div class="w-full sm:max-w-md px-6 py-8 bg-white shadow-lg overflow-hidden sm:rounded-lg">
            <slot />
        </div>

        <!-- Footer -->
        <div class="mt-8 text-sm text-gray-500">
            <p>© {{ new Date().getFullYear() }} RIC 2026 - Todos los derechos reservados</p>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
</script>