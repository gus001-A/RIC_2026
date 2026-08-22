<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition-all duration-300 ease-[cubic-bezier(0.16,1,0.3,1)]"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition-all duration-200 ease-in"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div v-if="visible" class="fixed inset-0 z-[9999] flex items-center justify-center p-4">
                <!-- Overlay con blur -->
                <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="close"></div>
                
                <!-- Modal -->
                <div class="relative bg-white rounded-3xl shadow-2xl max-w-md w-full mx-4 overflow-hidden animate-slide-up">
                    <!-- Icono animado -->
                    <div class="flex justify-center pt-8 pb-4">
                        <div class="w-20 h-20 rounded-full flex items-center justify-center text-4xl animate-bounce-in"
                             :class="{
                                 'bg-red-100 text-red-500': currentType === 'error',
                                 'bg-green-100 text-green-500': currentType === 'success',
                                 'bg-yellow-100 text-yellow-500': currentType === 'warning',
                                 'bg-blue-100 text-blue-500': currentType === 'info'
                             }">
                            {{ currentIcon }}
                        </div>
                    </div>

                    <!-- Título -->
                    <h3 class="text-xl font-bold text-center text-gray-800 px-6">
                        {{ currentTitle }}
                    </h3>

                    <!-- ✅ Mensaje con v-html (sin contenido entre etiquetas) -->
                    <p class="text-sm text-gray-600 text-center px-6 mt-2 leading-relaxed" v-html="currentMessage"></p>

                    <!-- Botón -->
                    <div class="p-6 pt-4 flex justify-center">
                        <button 
                            @click="close"
                            class="px-8 py-2.5 rounded-xl text-white font-semibold transition-all duration-300 transform hover:scale-105 active:scale-95"
                            :class="{
                                'bg-gradient-to-r from-red-500 to-red-600 hover:shadow-lg hover:shadow-red-500/30': currentType === 'error',
                                'bg-gradient-to-r from-green-500 to-green-600 hover:shadow-lg hover:shadow-green-500/30': currentType === 'success',
                                'bg-gradient-to-r from-yellow-500 to-yellow-600 hover:shadow-lg hover:shadow-yellow-500/30': currentType === 'warning',
                                'bg-gradient-to-r from-blue-500 to-blue-600 hover:shadow-lg hover:shadow-blue-500/30': currentType === 'info'
                            }"
                        >
                            {{ currentButtonText }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    type: {
        type: String,
        default: 'info'
    },
    title: {
        type: String,
        default: ''
    },
    message: {
        type: String,
        default: ''
    },
    buttonText: {
        type: String,
        default: 'Entendido'
    },
    duration: {
        type: Number,
        default: 4000
    }
});

const emit = defineEmits(['close']);

// Estado interno
const visible = ref(false);
const currentType = ref(props.type);
const currentTitle = ref(props.title);
const currentMessage = ref(props.message);
const currentButtonText = ref(props.buttonText);
const currentDuration = ref(props.duration);

let timer = null;

// Iconos por tipo
const iconMap = {
    error: '❌',
    success: '✅',
    warning: '⚠️',
    info: 'ℹ️'
};

const currentIcon = computed(() => iconMap[currentType.value] || 'ℹ️');

// ✅ Método para mostrar la alerta
const show = (options = {}) => {
    // Actualizar valores
    currentType.value = options.type || props.type || 'info';
    currentTitle.value = options.title || props.title || '';
    currentMessage.value = options.message || props.message || '';
    currentButtonText.value = options.buttonText || props.buttonText || 'Entendido';
    currentDuration.value = options.duration || props.duration || 4000;
    
    // Mostrar
    visible.value = true;
    
    // Auto-cerrar
    if (currentDuration.value > 0) {
        timer = setTimeout(() => {
            close();
        }, currentDuration.value);
    }
};

// ✅ Método para cerrar
const close = () => {
    visible.value = false;
    if (timer) {
        clearTimeout(timer);
        timer = null;
    }
    emit('close');
};

// ✅ Exponer métodos
defineExpose({ show, close });
</script>

<style scoped>
@keyframes slide-up {
    from {
        opacity: 0;
        transform: translateY(30px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

@keyframes bounce-in {
    0% {
        transform: scale(0);
        opacity: 0;
    }
    50% {
        transform: scale(1.2);
    }
    70% {
        transform: scale(0.9);
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}

.animate-slide-up {
    animation: slide-up 0.4s ease-out forwards;
}

.animate-bounce-in {
    animation: bounce-in 0.6s ease forwards;
}
</style>