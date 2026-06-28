<template>
    <Head title="Iniciar Sesión" />

    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 via-blue-100/50 to-blue-200/30 p-4">
        <div class="w-full max-w-5xl bg-white/80 backdrop-blur-sm rounded-3xl shadow-2xl overflow-hidden flex flex-col md:flex-row border border-white/20 relative">
            
            <!-- Efecto de brillo en el borde -->
            <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-400 via-purple-400 to-blue-400 rounded-3xl blur-xl opacity-20 animate-pulse-slow"></div>
            
            <!-- Columna Izquierda - Branding -->
            <div class="md:w-1/2 bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 p-10 md:p-14 flex flex-col justify-center items-center text-white relative overflow-hidden">
                <!-- Elementos decorativos -->
                <div class="absolute -top-20 -right-20 w-72 h-72 bg-gradient-to-br from-blue-400/30 to-purple-400/30 rounded-full blur-3xl animate-float"></div>
                <div class="absolute -bottom-20 -left-20 w-72 h-72 bg-gradient-to-tr from-indigo-400/30 to-blue-400/30 rounded-full blur-3xl animate-float" style="animation-delay: 2s;"></div>
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-gradient-to-r from-blue-500/10 to-purple-500/10 rounded-full blur-3xl animate-pulse-slow"></div>
                
                <!-- Partículas decorativas -->
                <div v-for="i in 25" :key="i" class="particle"
                     :style="{
                         left: Math.random() * 100 + '%',
                         top: Math.random() * 100 + '%',
                         width: (Math.random() * 4 + 1) + 'px',
                         height: (Math.random() * 4 + 1) + 'px',
                         animationDelay: (Math.random() * 10) + 's',
                         animationDuration: (Math.random() * 15 + 10) + 's',
                         opacity: Math.random() * 0.3 + 0.1
                     }">
                </div>
                
                <!-- Líneas decorativas -->
                <div class="absolute top-0 left-0 w-full h-0.5 bg-gradient-to-r from-transparent via-white/20 to-transparent"></div>
                <div class="absolute bottom-0 left-0 w-full h-0.5 bg-gradient-to-r from-transparent via-white/20 to-transparent"></div>
                <div class="absolute left-0 top-0 w-0.5 h-full bg-gradient-to-b from-transparent via-white/10 to-transparent"></div>
                <div class="absolute right-0 top-0 w-0.5 h-full bg-gradient-to-b from-transparent via-white/10 to-transparent"></div>
                
                <!-- Esquinas decorativas -->
                <div class="absolute top-4 left-4 w-8 h-8 border-t-2 border-l-2 border-white/10 rounded-tl-xl"></div>
                <div class="absolute top-4 right-4 w-8 h-8 border-t-2 border-r-2 border-white/10 rounded-tr-xl"></div>
                <div class="absolute bottom-4 left-4 w-8 h-8 border-b-2 border-l-2 border-white/10 rounded-bl-xl"></div>
                <div class="absolute bottom-4 right-4 w-8 h-8 border-b-2 border-r-2 border-white/10 rounded-br-xl"></div>
                
                <div class="relative z-10 text-center">
                    <!-- Logo -->
                    <div class="mb-8 flex justify-center group">
                        <div class="relative">
                            <div class="absolute inset-0 bg-gradient-to-r from-blue-400 via-purple-400 to-blue-400 rounded-3xl blur-2xl group-hover:blur-3xl transition-all duration-700 opacity-60 group-hover:opacity-100"></div>
                            <div class="relative bg-white/10 backdrop-blur-2xl rounded-3xl p-6 border border-white/20 shadow-2xl transition-all duration-500 group-hover:scale-105 group-hover:border-white/40 group-hover:shadow-[0_0_60px_rgba(59,130,246,0.3)]">
                                <img 
                                    :src="logoImage" 
                                    alt="RIC Logo" 
                                    class="h-28 w-auto object-contain drop-shadow-2xl"
                                    @error="handleLogoError"
                                    v-if="logoExists"
                                />
                                <div v-else class="w-28 h-28 bg-blue-500/30 rounded-2xl flex items-center justify-center backdrop-blur-sm">
                                    <span class="text-4xl font-bold text-white">RIC</span>
                                </div>
                            </div>
                            <div class="absolute -bottom-2 left-1/2 -translate-x-1/2 w-16 h-1 bg-gradient-to-r from-transparent via-white/50 to-transparent rounded-full"></div>
                        </div>
                    </div>
                    
                    <h1 class="text-5xl md:text-6xl font-bold mb-3 tracking-tight">
                        <span class="bg-gradient-to-r from-white via-blue-200 to-purple-200 bg-clip-text text-transparent">
                            RIC {{ currentYear }}
                        </span>
                    </h1>
                    
                    <div class="h-1 w-20 bg-gradient-to-r from-blue-300/50 to-purple-300/50 mx-auto rounded-full mb-6"></div>
                    
                    <p class="text-blue-100/90 text-lg font-light leading-relaxed">
                        Red <br />
                        <span class="font-medium text-white">Informática Contable</span>
                    </p>
                    
                    <div class="flex items-center justify-center gap-4 mt-10 mb-6">
                        <div class="w-12 h-px bg-gradient-to-r from-transparent via-blue-300/20 to-transparent"></div>
                        <div class="w-1.5 h-1.5 rounded-full bg-blue-300/30"></div>
                        <div class="w-12 h-px bg-gradient-to-r from-transparent via-blue-300/20 to-transparent"></div>
                    </div>
                    
                    <div class="mt-6 text-blue-200/20 text-xs tracking-wider">
                        <p>&copy; RIC {{ currentYear }}</p>
                    </div>
                </div>
            </div>

            <!-- Columna Derecha - Formulario -->
            <div class="md:w-1/2 p-8 md:p-12 flex flex-col justify-center bg-white/90 backdrop-blur-sm relative">
                <div class="absolute -top-20 -right-20 w-48 h-48 bg-gradient-to-br from-blue-200/10 to-purple-200/10 rounded-full blur-2xl"></div>
                <div class="absolute -bottom-20 -left-20 w-48 h-48 bg-gradient-to-tr from-indigo-200/10 to-blue-200/10 rounded-full blur-2xl"></div>
                
                <div class="absolute top-4 left-4 w-6 h-6 border-t-2 border-l-2 border-blue-100 rounded-tl-lg"></div>
                <div class="absolute top-4 right-4 w-6 h-6 border-t-2 border-r-2 border-blue-100 rounded-tr-lg"></div>
                <div class="absolute bottom-4 left-4 w-6 h-6 border-b-2 border-l-2 border-blue-100 rounded-bl-lg"></div>
                <div class="absolute bottom-4 right-4 w-6 h-6 border-b-2 border-r-2 border-blue-100 rounded-br-lg"></div>
                
                <div class="max-w-sm mx-auto w-full relative z-10">
                    <!-- Header -->
                    <div class="mb-10">
                        <div class="flex items-center gap-4 mb-2">
                            <div class="w-1 h-14 bg-gradient-to-b from-blue-500 to-purple-600 rounded-full"></div>
                            <div>
                                <h2 class="text-3xl font-bold text-gray-800 tracking-tight">Bienvenido</h2>
                                <p class="text-sm text-gray-500 mt-0.5 font-light">Accede con tu usuario y contraseña</p>
                            </div>
                        </div>
                        <div class="w-16 h-0.5 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full ml-[22px]"></div>
                    </div>

                    <AForm 
                        @submit="submit" 
                        layout="vertical" 
                        class="space-y-5"
                        :model="form"
                    >
                        <!-- Usuario -->
                        <AFormItem class="mb-1">
                            <template #label>
                                <span class="text-sm font-semibold text-gray-700">
                                    👤 Nombre de Usuario
                                </span>
                            </template>
                            <AInput 
                                v-model:value="form.username"
                                size="large"
                                placeholder="Ingresa tu usuario"
                                class="!rounded-2xl !border-2 !border-gray-200 hover:!border-blue-500 focus-within:!border-blue-500 !bg-gray-50/50 hover:!bg-white !transition-all !duration-200 !px-4 !py-3.5"
                            >
                                <template #prefix>
                                    <UserOutlined class="text-gray-400" />
                                </template>
                            </AInput>
                        </AFormItem>

                        <!-- Contraseña -->
                        <AFormItem class="mb-1">
                            <template #label>
                                <span class="text-sm font-semibold text-gray-700">
                                    🔒 Contraseña
                                </span>
                            </template>
                            <AInput.Password 
                                v-model:value="form.password"
                                size="large"
                                placeholder="Ingresa tu contraseña"
                                class="!rounded-2xl !border-2 !border-gray-200 hover:!border-blue-500 focus-within:!border-blue-500 !bg-gray-50/50 hover:!bg-white !transition-all !duration-200 !px-4 !py-3.5"
                            >
                                <template #prefix>
                                    <LockOutlined class="text-gray-400" />
                                </template>
                            </AInput.Password>
                        </AFormItem>

                        <!-- Recordarme y olvidé contraseña -->
                        <div class="flex items-center justify-between py-1">
                            <ACheckbox v-model:checked="form.remember" class="text-gray-600 hover:text-gray-800">
                                Recordarme
                            </ACheckbox>
                            <Link :href="route('password.request')" class="text-sm text-blue-600 hover:text-blue-800 font-medium transition-colors duration-200 flex items-center gap-1">
                                <QuestionCircleOutlined />
                                ¿Olvidaste tu contraseña?
                            </Link>
                        </div>

                        <!-- Botón Iniciar Sesión -->
                        <AButton 
                            type="primary"
                            html-type="submit"
                            size="large"
                            block
                            :loading="form.processing"
                            class="!h-12 !text-base !font-semibold !rounded-2xl !shadow-lg !shadow-blue-500/30 hover:!shadow-xl hover:!shadow-blue-500/40 !bg-gradient-to-r !from-blue-600 !via-blue-700 !to-indigo-700 hover:!from-blue-700 hover:!via-blue-800 hover:!to-indigo-800 !border-0 !transition-all !duration-300"
                        >
                            <template v-if="!form.processing">
                                <LoginOutlined class="mr-2" />
                                Iniciar Sesión
                            </template>
                            <template v-else>
                                <LoadingOutlined class="animate-spin mr-2" />
                                Ingresando...
                            </template>
                        </AButton>

                        <!-- Acceso restringido -->
                        <div class="text-center pt-3">
                            <div class="inline-flex items-center gap-2.5 px-5 py-2.5 bg-gray-50/80 backdrop-blur-sm rounded-full border border-gray-200/50 shadow-sm">
                                <SafetyOutlined class="text-gray-400" />
                                <span class="text-xs text-gray-500 font-light">🔐 Sistema de acceso restringido</span>
                            </div>
                        </div>
                    </AForm>
                </div>
            </div>
        </div>
    </div>

    <!-- ✅ ALERTA PERSONALIZADA -->
    <AlertModal ref="alertModal" @close="onAlertClose" />
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

// ✅ Ant Design Componentes
import {
    Form as AForm,
    FormItem as AFormItem,
    Input as AInput,
    Button as AButton,
    Checkbox as ACheckbox,
} from 'ant-design-vue';

// ✅ Ant Design Iconos
import {
    UserOutlined,
    LockOutlined,
    QuestionCircleOutlined,
    LoginOutlined,
    LoadingOutlined,
    SafetyOutlined,
} from '@ant-design/icons-vue';

// ✅ IMPORTAR EL LOGO
import logoImage from '@/images/logo.png';

// ✅ IMPORTAR COMPONENTE DE ALERTA
import AlertModal from '@/Components/AlertModal.vue';

const props = defineProps({
    status: {
        type: String,
        default: null
    },
    canResetPassword: {
        type: Boolean,
        default: true
    }
});

// ✅ Referencia a la alerta
const alertModal = ref(null);
let alertShown = false;

const form = useForm({
    username: '',
    password: '',
    remember: false,
});

const logoExists = ref(true);
const currentYear = new Date().getFullYear();

const handleLogoError = () => {
    logoExists.value = false;
};

// ✅ Función para mostrar alerta
const showAlert = (options) => {
    if (alertModal.value) {
        alertModal.value.show(options);
    }
};

const submit = () => {
    form.post('/login', {
        onError: (errors) => {
            if (errors.username || errors.password || errors.email) {
                showAlert({
                    type: 'error',
                    title: 'Acceso Denegado',
                     message: 'Usuario o contraseña incorrectos.<br>Por favor, verifica tus credenciales.',
                    buttonText: 'Intentar de nuevo',
                    duration: 5000
                });
            } else {
                const firstError = Object.values(errors)[0];
                if (firstError) {
                    showAlert({
                        type: 'warning',
                        title: 'Advertencia',
                        message: firstError,
                        buttonText: 'Entendido',
                        duration: 4000
                    });
                }
            }
        },
        onFinish: () => form.reset('password'),
    });
};

onMounted(() => {
    if (props.status && !alertShown) {
        alertShown = true;
        setTimeout(() => {
            showAlert({
                type: 'success',
                title: '✅ Sesión Cerrada',
                message: props.status,
                buttonText: 'Continuar',
                duration: 3000
            });
        }, 500);
    }
});

const onAlertClose = () => {
    // Limpiar estado si es necesario
    console.log('Alerta cerrada');
};
</script>

<style scoped>
@keyframes float {
    0%, 100% { transform: translateY(0px) rotate(0deg); }
    50% { transform: translateY(-20px) rotate(5deg); }
}

@keyframes pulse-slow {
    0%, 100% { opacity: 0.2; transform: scale(1); }
    50% { opacity: 0.6; transform: scale(1.1); }
}

@keyframes particleFloat {
    0% { transform: translateY(0px) scale(0); opacity: 0; }
    20% { opacity: 1; }
    80% { opacity: 1; }
    100% { transform: translateY(-200px) scale(1); opacity: 0; }
}

.animate-float {
    animation: float 8s ease-in-out infinite;
}

.animate-pulse-slow {
    animation: pulse-slow 6s ease-in-out infinite;
}

.particle {
    position: absolute;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.3);
    animation: particleFloat linear infinite;
    pointer-events: none;
    backdrop-filter: blur(4px);
}

::selection {
    background: linear-gradient(to right, #3b82f6, #8b5cf6);
    color: white;
}

img {
    filter: drop-shadow(0 8px 32px rgba(59, 130, 246, 0.3));
    transition: filter 0.5s ease;
}

.group:hover img {
    filter: drop-shadow(0 8px 48px rgba(59, 130, 246, 0.5));
}

::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-track {
    background: rgba(0,0,0,0.05);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb {
    background: linear-gradient(to bottom, #3b82f6, #8b5cf6);
    border-radius: 4px;
}

/* ✅ Estilos para Ant Design */
:deep(.ant-input-affix-wrapper) {
    @apply !rounded-2xl !border-2 !border-gray-200 hover:!border-blue-500 focus-within:!border-blue-500 !bg-gray-50/50 hover:!bg-white !transition-all !duration-200 !px-4 !py-3.5;
}

:deep(.ant-input-affix-wrapper .ant-input) {
    @apply !bg-transparent !text-base !placeholder-gray-400;
}

:deep(.ant-input-affix-wrapper .ant-input-prefix) {
    @apply !mr-3;
}

:deep(.ant-input-affix-wrapper .ant-input-suffix .ant-input-password-icon) {
    @apply !text-gray-400 hover:!text-blue-600 !transition-colors;
}

:deep(.ant-form-item) {
    @apply !mb-2;
}

:deep(.ant-checkbox-wrapper) {
    @apply !text-sm !text-gray-600 hover:!text-gray-800;
}

:deep(.ant-checkbox-wrapper .ant-checkbox) {
    @apply !rounded !border-2 !border-gray-300 hover:!border-blue-400;
}

:deep(.ant-checkbox-wrapper .ant-checkbox-checked .ant-checkbox-inner) {
    @apply !bg-blue-600 !border-blue-600;
}

:deep(.ant-btn-primary) {
    @apply !shadow-lg !shadow-blue-500/30 hover:!shadow-xl hover:!shadow-blue-500/40 !transition-all !duration-300;
}

:deep(.ant-btn-primary[disabled]) {
    @apply !opacity-50 !cursor-not-allowed;
}

:deep(.ant-btn-primary .anticon) {
    @apply !text-base;
}
</style>