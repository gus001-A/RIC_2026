<template>
    <GuestLayout>
        <Head title="Verificar Correo - RIC 2026" />

        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Verifica tu correo</h2>
            <p class="text-sm text-gray-500">Antes de continuar, verifica tu dirección de correo</p>
        </div>

        <div class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
            <p class="text-sm text-yellow-700">
                📧 Hemos enviado un enlace de verificación a tu correo electrónico.
                Haz clic en el enlace para activar tu cuenta.
            </p>
        </div>

        <div v-if="verificationLinkSent" class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg">
            <p class="text-sm text-green-600">✅ Se ha enviado un nuevo enlace de verificación.</p>
        </div>

        <div class="space-y-3">
            <button @click="submit" 
                    :disabled="form.processing"
                    class="w-full flex justify-center items-center gap-2 px-4 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition disabled:opacity-50">
                <span v-if="form.processing" class="inline-block animate-spin">⟳</span>
                {{ form.processing ? 'Enviando...' : '📧 Reenviar correo de verificación' }}
            </button>

            <Link :href="route('logout')" method="post" as="button"
                  class="w-full flex justify-center items-center gap-2 px-4 py-3 bg-red-50 text-red-600 font-semibold rounded-lg hover:bg-red-100 transition">
                🚪 Cerrar Sesión
            </Link>
        </div>

        <div class="mt-4 p-3 bg-blue-50 rounded-lg">
            <p class="text-xs text-blue-600">
                💡 Si no recibiste el correo, revisa tu carpeta de spam o solicita un nuevo enlace.
            </p>
        </div>
    </GuestLayout>
</template>

<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
        default: null
    },
    verificationLinkSent: {
        type: Boolean,
        default: false
    }
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};
</script>