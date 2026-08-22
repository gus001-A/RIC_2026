<template>
    <GuestLayout>
        <Head title="Confirmar Contraseña - RIC 2026" />

        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Confirmar Contraseña</h2>
            <p class="text-sm text-gray-500">Por seguridad, confirma tu contraseña para continuar</p>
        </div>

        <div class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
            <p class="text-sm text-yellow-700">🔒 Esta es una zona segura. Confirma tu contraseña para continuar.</p>
        </div>

        <form @submit.prevent="submit">
            <div class="mb-4">
                <InputLabel for="password" value="Contraseña" />
                <div class="relative mt-1">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                        🔒
                    </span>
                    <TextInput
                        id="password"
                        type="password"
                        class="pl-10 block w-full"
                        v-model="form.password"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••"
                    />
                    <button type="button" @click="showPassword = !showPassword" 
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                        {{ showPassword ? '👁️' : '👁️‍🗨️' }}
                    </button>
                </div>
                <InputError class="mt-1" :message="form.errors.password" />
            </div>

            <button type="submit" 
                    :disabled="form.processing"
                    class="w-full flex justify-center items-center gap-2 px-4 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition disabled:opacity-50">
                <span v-if="form.processing" class="inline-block animate-spin">⟳</span>
                {{ form.processing ? 'Confirmando...' : '✅ Confirmar' }}
            </button>
        </form>
    </GuestLayout>
</template>

<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    password: '',
});

const showPassword = ref(false);

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};
</script>