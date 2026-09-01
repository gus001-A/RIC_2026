<template>
    <Head title="Iniciar Sesión" />

    <AuthShell title="Bienvenido" subtitle="Accede con tu usuario y contraseña">
        <form @submit.prevent="submit" class="space-y-5">
            <!-- Usuario -->
            <div class="space-y-1.5">
                <label class="text-sm font-semibold text-gray-700">Nombre de Usuario</label>
                <IconField>
                    <InputIcon class="pi pi-user" />
                    <InputText
                        v-model="form.username"
                        placeholder="Ingresa tu usuario"
                        class="w-full auth-input"
                        autocomplete="username"
                    />
                </IconField>
                <Message v-if="form.errors.username" severity="error" size="small" variant="simple">
                    {{ form.errors.username }}
                </Message>
            </div>

            <!-- Contraseña -->
            <div class="space-y-1.5">
                <label class="text-sm font-semibold text-gray-700">Contraseña</label>
                <Password
                    v-model="form.password"
                    placeholder="Ingresa tu contraseña"
                    :feedback="false"
                    toggle-mask
                    fluid
                    input-class="auth-input"
                    class="w-full"
                />
                <Message v-if="form.errors.password" severity="error" size="small" variant="simple">
                    {{ form.errors.password }}
                </Message>
            </div>

            <!-- Recordarme y olvidé contraseña -->
            <div class="flex items-center justify-between py-1">
                <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                    <Checkbox v-model="form.remember" binary />
                    Recordarme
                </label>
                <Link
                    :href="route('password.request')"
                    class="text-sm text-blue-600 hover:text-blue-800 font-medium transition-colors duration-200 flex items-center gap-1"
                >
                    <i class="pi pi-question-circle text-xs"></i>
                    ¿Olvidaste tu contraseña?
                </Link>
            </div>

            <!-- Botón Iniciar Sesión -->
            <Button
                type="submit"
                :loading="form.processing"
                :label="form.processing ? 'Ingresando...' : 'Iniciar Sesión'"
                :icon="form.processing ? 'pi pi-spin pi-spinner' : 'pi pi-sign-in'"
                class="w-full auth-submit-btn"
            />

            <div class="text-center pt-3">
                <div class="inline-flex items-center gap-2.5 px-5 py-2.5 bg-gray-50/80 backdrop-blur-sm rounded-full border border-gray-200/50 shadow-sm">
                    <i class="pi pi-shield text-gray-400"></i>
                    <span class="text-xs text-gray-500 font-light">Sistema de acceso restringido</span>
                </div>
            </div>
        </form>
    </AuthShell>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { onMounted } from 'vue';
import { useToast } from 'primevue/usetoast';
import AuthShell from '@/Layouts/AuthShell.vue';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Checkbox from 'primevue/checkbox';
import Button from 'primevue/button';
import Message from 'primevue/message';

const props = defineProps({
    status: { type: String, default: null },
    canResetPassword: { type: Boolean, default: true },
});

const toast = useToast();

const form = useForm({
    username: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/login', {
        onError: (errors) => {
            if (errors.username || errors.password || errors.email) {
                toast.add({
                    severity: 'error',
                    summary: 'Acceso denegado',
                    detail: 'Usuario o contraseña incorrectos. Verifica tus credenciales.',
                    life: 5000,
                });
            } else {
                const firstError = Object.values(errors)[0];
                if (firstError) {
                    toast.add({ severity: 'warn', summary: 'Advertencia', detail: firstError, life: 4000 });
                }
            }
        },
        onFinish: () => form.reset('password'),
    });
};

onMounted(() => {
    if (props.status) {
        setTimeout(() => {
            toast.add({ severity: 'success', summary: 'Sesión cerrada', detail: props.status, life: 3500 });
        }, 400);
    }
});
</script>

<style scoped>
::selection {
    background: linear-gradient(to right, #3b82f6, #1a3a5c);
    color: white;
}

:deep(.auth-input) {
    border-radius: 1rem;
    border: 2px solid #e5e7eb;
    background: rgba(249, 250, 251, 0.5);
    padding: 0.875rem 1rem;
    transition: all 0.2s ease;
    width: 100%;
}

:deep(.auth-input:hover) {
    border-color: #3b82f6;
    background: #fff;
}

:deep(.auth-input:focus) {
    border-color: #3b82f6;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.12);
}

.auth-submit-btn {
    height: 3rem;
    font-weight: 600;
    border-radius: 1rem;
    border: 0;
    background: linear-gradient(to right, #2563eb, #1d4ed8, #4338ca);
    box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.3);
    transition: all 0.3s ease;
}

.auth-submit-btn:hover {
    background: linear-gradient(to right, #1d4ed8, #1e40af, #3730a3);
    box-shadow: 0 20px 25px -5px rgba(59, 130, 246, 0.4);
}
</style>
