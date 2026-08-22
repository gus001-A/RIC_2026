<template>
    <a-modal
        v-model:visible="visible"
        title="Confirmar Liquidación"
        :confirm-loading="loading"
        :footer="null"
        width="500px"
        class="modal-liquidacion"
        @cancel="cerrar"
    >
        <div class="modal-content">
            <div class="modal-icon">
                <svg class="w-16 h-16 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>

            <h3 class="modal-title">¿Liquidar esta póliza?</h3>
            
            <div class="modal-info">
                <div class="info-row">
                    <span class="info-label">Referencia:</span>
                    <span class="info-value">{{ movimiento?.referencia || '—' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Persona:</span>
                    <span class="info-value">{{ movimiento?.persona || '—' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Nota:</span>
                    <span class="info-value">{{ movimiento?.nota || '—' }}</span>
                </div>
                <div class="info-row info-row-highlight">
                    <span class="info-label">Saldo pendiente:</span>
                    <span class="info-value info-value-amount">${{ formatNumber(movimiento?.saldo_pendiente || 0) }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Cuenta fondeadora:</span>
                    <span class="info-value info-value-fondeadora">{{ cuentaFondeadoraNombre }}</span>
                </div>
            </div>

            <div class="modal-actions">
                <a-button 
                    class="btn-cancelar"
                    @click="cerrar"
                    :disabled="loading"
                >
                    Cancelar
                </a-button>
                <a-button 
                    class="btn-confirmar"
                    :loading="loading"
                    @click="confirmarLiquidacion"
                >
                    <template #icon>
                        <CheckOutlined />
                    </template>
                    Confirmar Liquidación
                </a-button>
            </div>
        </div>
    </a-modal>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { CheckOutlined } from '@ant-design/icons-vue';
import Swal from 'sweetalert2';

const props = defineProps({
    movimiento: {
        type: Object,
        default: null
    },
    cuentasFondeadoras: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['liquidado', 'close']);

const visible = ref(false);
const loading = ref(false);

// Computed para obtener el nombre de la cuenta fondeadora
const cuentaFondeadoraNombre = computed(() => {
    if (!props.movimiento) return '—';
    
    // Si el movimiento ya tiene una cuenta fondeadora asignada
    if (props.movimiento.cuenta_fondeadora) {
        return props.movimiento.cuenta_fondeadora;
    }
    
    // Si tiene id_cuenta_fondeadora, buscar en el listado
    if (props.movimiento.id_cuenta_fondeadora && props.cuentasFondeadoras.length > 0) {
        const cuenta = props.cuentasFondeadoras.find(c => c.id === props.movimiento.id_cuenta_fondeadora);
        if (cuenta) {
            return cuenta.nombre_cuenta || cuenta.cuenta || 'Cuenta fondeadora';
        }
    }
    
    // Si es un traspaso, mostrar la cuenta origen
    if (props.movimiento.es_traspaso && props.movimiento.cuenta) {
        return props.movimiento.cuenta;
    }
    
    return 'Sin cuenta fondeadora';
});

const formatNumber = (value) => {
    if (value === null || value === undefined || isNaN(value)) return '0.00';
    return Number(value).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const open = () => {
    visible.value = true;
};

const cerrar = () => {
    visible.value = false;
    loading.value = false;
    emit('close');
};

const confirmarLiquidacion = async () => {
    if (!props.movimiento) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No hay movimiento seleccionado',
            confirmButtonColor: '#1a3a5c'
        });
        return;
    }

    loading.value = true;

    try {
        const response = await axios.post(
            route('movimientos.liquidar', props.movimiento.id_movimiento),
            {
                _method: 'PUT',
                // Solo enviamos el ID del movimiento, la cuenta fondeadora ya está asociada
                id_movimiento: props.movimiento.id_movimiento
            },
            {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            }
        );

        if (response.data.success) {
            Swal.fire({
                icon: 'success',
                title: '¡Liquidación exitosa!',
                text: response.data.message || 'La póliza ha sido liquidada correctamente.',
                confirmButtonColor: '#1a3a5c',
                timer: 3000,
                timerProgressBar: true
            });

            emit('liquidado', response.data);
            cerrar();
        } else {
            throw new Error(response.data.message || 'Error al liquidar la póliza');
        }
    } catch (error) {
        console.error('Error en liquidación:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.response?.data?.message || 'Ocurrió un error al liquidar la póliza',
            confirmButtonColor: '#1a3a5c'
        });
    } finally {
        loading.value = false;
    }
};

defineExpose({
    open,
    cerrar
});
</script>

<style scoped>
.modal-liquidacion :deep(.ant-modal-content) {
    border-radius: 16px;
    padding: 0;
    overflow: hidden;
}

.modal-liquidacion :deep(.ant-modal-header) {
    padding: 16px 24px;
    border-bottom: 2px solid #f1f5f9;
    background: linear-gradient(135deg, #f8fafc, #ffffff);
    margin: 0;
}

.modal-liquidacion :deep(.ant-modal-title) {
    font-size: 18px;
    font-weight: 700;
    color: #0f172a;
}

.modal-liquidacion :deep(.ant-modal-body) {
    padding: 0;
}

.modal-content {
    padding: 24px 28px 28px 28px;
}

.modal-icon {
    display: flex;
    justify-content: center;
    margin-bottom: 16px;
}

.modal-icon svg {
    width: 64px;
    height: 64px;
    color: #10b981;
}

.modal-title {
    text-align: center;
    font-size: 20px;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 20px;
}

.modal-info {
    background: #f8fafc;
    border-radius: 12px;
    padding: 16px 20px;
    margin-bottom: 24px;
    border: 1px solid #e2e8f0;
}

.info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 6px 0;
    border-bottom: 1px solid #f1f5f9;
}

.info-row:last-child {
    border-bottom: none;
}

.info-label {
    font-size: 13px;
    font-weight: 600;
    color: #64748b;
}

.info-value {
    font-size: 14px;
    font-weight: 500;
    color: #0f172a;
    text-align: right;
}

.info-row-highlight {
    background: #eff6ff;
    margin: 4px -10px;
    padding: 8px 10px;
    border-radius: 6px;
    border-bottom: none;
}

.info-value-amount {
    font-size: 18px;
    font-weight: 700;
    color: #2563eb;
}

.info-value-fondeadora {
    font-size: 14px;
    font-weight: 600;
    color: #7c3aed;
    background: #ede9fe;
    padding: 2px 12px;
    border-radius: 4px;
}

.modal-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    margin-top: 8px;
}

.btn-cancelar {
    padding: 0 24px !important;
    height: 40px !important;
    border-radius: 8px !important;
    border: 2px solid #d1d5db !important;
    color: #64748b !important;
    font-weight: 600 !important;
    transition: all 0.3s ease !important;
}

.btn-cancelar:hover {
    border-color: #1a3a5c !important;
    color: #1a3a5c !important;
    transform: translateY(-2px);
}

.btn-confirmar {
    padding: 0 24px !important;
    height: 40px !important;
    border-radius: 8px !important;
    background: linear-gradient(135deg, #10b981, #059669) !important;
    border: none !important;
    color: white !important;
    font-weight: 600 !important;
    transition: all 0.3s ease !important;
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.25) !important;
}

.btn-confirmar:hover:not(:disabled) {
    transform: translateY(-2px) !important;
    box-shadow: 0 4px 16px rgba(16, 185, 129, 0.35) !important;
}

.btn-confirmar:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Responsive */
@media (max-width: 480px) {
    .modal-content {
        padding: 16px;
    }
    
    .info-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 2px;
    }
    
    .info-value {
        text-align: left;
        width: 100%;
    }
    
    .modal-actions {
        flex-direction: column;
    }
    
    .btn-cancelar,
    .btn-confirmar {
        width: 100%;
        justify-content: center;
    }
}
</style>