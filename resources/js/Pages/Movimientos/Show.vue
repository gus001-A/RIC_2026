<template>
    <AppLayout :title="tituloPagina">
        <template #header>
            <div class="header-wrapper">
                <div class="header-left">
                    <Link :href="route('movimientos.index')" class="btn-back">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                    </Link>
                    <div class="header-content">
                        <h2 class="header-title">📄 Detalle de Póliza</h2>
                        <p class="header-subtitle">Información completa de la póliza {{ movimiento.referencia || '' }}</p>
                    </div>
                </div>
                <div class="header-right">
                    <div class="header-actions">
                        <Link :href="route('movimientos.edit', movimiento.id)" class="btn-edit-header">
                            <svg class="btn-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Editar
                        </Link>
                        <div class="status-badge" :class="getEstatusClass(movimiento.estatus)">
                            {{ movimiento.estatus || '—' }}
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <div class="page-content">
            <div class="container-custom">
                <div class="detail-card">
                    <!-- Información General -->
                    <div class="detail-section">
                        <div class="section-header">
                            <div class="section-icon blue">
                                <svg class="icon-svg" fill="none" stroke="white" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="section-title">Información General</h3>
                                <p class="section-subtitle">Datos principales de la póliza</p>
                            </div>
                            <Link :href="route('movimientos.edit', movimiento.id)" class="btn-edit-section">
                                <svg class="btn-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Editar
                            </Link>
                        </div>

                        <div class="detail-grid-2">
                            <div class="detail-item">
                                <span class="detail-label">Folio</span>
                                <span class="detail-value">{{ movimiento.referencia || '—' }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Referencia</span>
                                <span class="detail-value">{{ movimiento.referencia_adicional || '—' }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Tipo de Póliza</span>
                                <span class="detail-value">{{ movimiento.tipo_poliza || '—' }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Fecha Póliza</span>
                                <span class="detail-value">{{ formatFecha(movimiento.fecha_poliza) }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Fecha Vencimiento</span>
                                <span class="detail-value" :class="getVencimientoClass(movimiento.fecha_vencimiento)">
                                    {{ formatFecha(movimiento.fecha_vencimiento) || '—' }}
                                </span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Categoría</span>
                                <span class="detail-value">{{ movimiento.categoria || '—' }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Estatus</span>
                                <span class="detail-value">
                                    <span class="estatus-badge" :class="getEstatusClass(movimiento.estatus)">
                                        {{ movimiento.estatus || '—' }}
                                    </span>
                                </span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Por Pagar</span>
                                <span class="detail-value">{{ movimiento.es_por_pagar ? 'Sí' : 'No' }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Persona</span>
                                <span class="detail-value">{{ movimiento.persona || '—' }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Cuenta</span>
                                <span class="detail-value">{{ movimiento.cuenta || '—' }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Marcador</span>
                                <span class="detail-value">{{ movimiento.marcador || '—' }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Usuario Creador</span>
                                <span class="detail-value">{{ movimiento.usuario_creador || '—' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Montos y Desglose -->
                    <div class="detail-section">
                        <div class="section-header">
                            <div class="section-icon green">
                                <svg class="icon-svg" fill="none" stroke="white" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="section-title">Montos y Desglose</h3>
                                <p class="section-subtitle">Información financiera detallada</p>
                            </div>
                            <Link :href="route('movimientos.edit', movimiento.id)" class="btn-edit-section">
                                <svg class="btn-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Editar
                            </Link>
                        </div>

                        <div class="detail-grid-3">
                            <div class="detail-item highlight">
                                <span class="detail-label">Monto Total</span>
                                <span class="detail-value monto-value" :class="getMontoClass(movimiento.monto)">
                                    ${{ formatNumber(movimiento.monto) }}
                                </span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Base Gravable</span>
                                <span class="detail-value">${{ formatNumber(movimiento.monto_base) }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">IVA</span>
                                <span class="detail-value">${{ formatNumber(movimiento.monto_iva) }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Tipo de IVA</span>
                                <span class="detail-value">{{ movimiento.tipo_iva || 'Sin IVA' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Facturación (si es fiscal) -->
                    <div v-if="movimiento.categoria === 'FISCAL'" class="detail-section">
                        <div class="section-header">
                            <div class="section-icon orange">
                                <svg class="icon-svg" fill="none" stroke="white" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="section-title">Facturación</h3>
                                <p class="section-subtitle">Datos de la factura</p>
                            </div>
                            <Link :href="route('movimientos.edit', movimiento.id)" class="btn-edit-section">
                                <svg class="btn-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Editar
                            </Link>
                        </div>

                        <div class="detail-grid-2">
                            <div class="detail-item">
                                <span class="detail-label">Fecha Factura</span>
                                <span class="detail-value">{{ formatFecha(movimiento.fecha_factura) || '—' }}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Número Factura</span>
                                <span class="detail-value">{{ movimiento.numero_factura || '—' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Abonos (si es por pagar) -->
                    <div v-if="movimiento.es_por_pagar && movimiento.abonos && movimiento.abonos.length > 0" class="detail-section">
                        <div class="section-header">
                            <div class="section-icon purple">
                                <svg class="icon-svg" fill="none" stroke="white" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="section-title">Abonos</h3>
                                <p class="section-subtitle">Historial de abonos realizados</p>
                            </div>
                            <span class="badge-info">Total: ${{ formatNumber(movimiento.total_abonado) }}</span>
                            <Link :href="route('movimientos.edit', movimiento.id)" class="btn-edit-section">
                                <svg class="btn-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Editar
                            </Link>
                        </div>

                        <div class="table-wrapper">
                            <table class="abonos-table">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Monto</th>
                                        <th>Referencia</th>
                                        <th>Método Pago</th>
                                        <th>Nota</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="abono in movimiento.abonos" :key="abono.id">
                                        <td>{{ formatFecha(abono.fecha_abono) }}</td>
                                        <td class="monto-abonado">${{ formatNumber(abono.monto_abonado) }}</td>
                                        <td>{{ abono.referencia || '—' }}</td>
                                        <td>{{ abono.metodo_pago || '—' }}</td>
                                        <td>{{ abono.nota || '—' }}</td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="4" class="total-label">Saldo Pendiente</td>
                                        <td class="total-value" :class="getSaldoPendienteClass(movimiento.saldo_pendiente)">
                                            ${{ formatNumber(movimiento.saldo_pendiente) }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- Nota -->
                    <div v-if="movimiento.nota" class="detail-section">
                        <div class="section-header">
                            <div class="section-icon teal">
                                <svg class="icon-svg" fill="none" stroke="white" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="section-title">Observaciones</h3>
                                <p class="section-subtitle">Notas adicionales</p>
                            </div>
                            <Link :href="route('movimientos.edit', movimiento.id)" class="btn-edit-section">
                                <svg class="btn-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Editar
                            </Link>
                        </div>
                        <div class="nota-content">
                            {{ movimiento.nota }}
                        </div>
                    </div>

                    <!-- Fechas de creación -->
                    <div class="detail-footer">
                        <div class="footer-info">
                            <span>Creado: {{ formatFechaHora(movimiento.fecha_creacion) }}</span>
                            <span v-if="movimiento.fecha_actualizacion">Actualizado: {{ formatFechaHora(movimiento.fecha_actualizacion) }}</span>
                        </div>
                        <div class="footer-actions">
                            <Link :href="route('movimientos.edit', movimiento.id)" class="btn btn-edit">
                                <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Editar Póliza
                            </Link>
                            <Link :href="route('movimientos.index')" class="btn btn-back-list">
                                <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                </svg>
                                Volver al listado
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <ModalAlert ref="alertRef" />
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import ModalAlert from '@/Components/AlertModal.vue';

const props = defineProps({
    movimiento: {
        type: Object,
        required: true
    }
});

const alertRef = ref(null);

const tituloPagina = computed(() => {
    return `Póliza ${props.movimiento.referencia || ''}`;
});

const formatNumber = (value) => {
    if (value === null || value === undefined) return '0.00';
    return Number(value).toLocaleString('es-MX', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
};

const formatFecha = (fecha) => {
    if (!fecha) return null;
    const d = new Date(fecha);
    return d.toLocaleDateString('es-MX', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
};

const formatFechaHora = (fecha) => {
    if (!fecha) return '';
    const d = new Date(fecha);
    return d.toLocaleDateString('es-MX', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    }) + ' | ' + d.toLocaleTimeString('es-MX', {
        hour: '2-digit',
        minute: '2-digit'
    });
};

const getEstatusClass = (estatus) => {
    const classes = {
        'PENDIENTE': 'pendiente',
        'ABONADO': 'abonado',
        'LIQUIDADO': 'liquidado'
    };
    return classes[estatus] || 'pendiente';
};

const getMontoClass = (monto) => {
    if (monto > 0) return 'ingreso';
    if (monto < 0) return 'egreso';
    return 'neutro';
};

const getSaldoPendienteClass = (saldo) => {
    if (saldo > 0) return 'saldo-positivo';
    if (saldo < 0) return 'saldo-negativo';
    return 'saldo-cero';
};

const getVencimientoClass = (fecha) => {
    if (!fecha) return '';
    const hoy = new Date();
    const fechaVenc = new Date(fecha);
    if (fechaVenc < hoy) return 'vencido';
    return 'vigente';
};
</script>

<style scoped>
/* ========== HEADER ========== */
.header-wrapper {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    padding: 4px 0;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 14px;
}

.btn-back {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.8);
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: #6b7280;
    transition: all 0.3s ease;
}

.btn-back:hover {
    background: white;
    color: #1f2937;
    transform: translateX(-3px) scale(1.05);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.header-content {
    display: flex;
    flex-direction: column;
}

.header-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #111827;
    margin: 0;
    line-height: 1.3;
}

.header-subtitle {
    font-size: 0.85rem;
    color: #6b7280;
    margin: 0;
}

.header-right {
    display: flex;
    align-items: center;
    gap: 12px;
}

.header-actions {
    display: flex;
    align-items: center;
    gap: 12px;
}

.btn-edit-header {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 18px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    border: none;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    cursor: pointer;
}

.btn-edit-header:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(102, 126, 234, 0.3);
    color: white;
}

.btn-icon-sm {
    width: 16px;
    height: 16px;
}

.status-badge {
    padding: 6px 20px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-badge.pendiente {
    background: #fef3c7;
    color: #92400e;
}

.status-badge.abonado {
    background: #dbeafe;
    color: #1e40af;
}

.status-badge.liquidado {
    background: #dcfce7;
    color: #166534;
}

/* ========== PAGE CONTENT ========== */
.page-content {
    padding: 1.5rem 0;
}

.container-custom {
    max-width: 72rem;
    margin: 0 auto;
    padding: 0 1.5rem;
}

/* ========== DETAIL CARD ========== */
.detail-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
    border: 1px solid #f3f4f6;
    padding: 2rem;
    transition: all 0.3s ease;
}

.detail-card:hover {
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
}

/* ========== SECTIONS ========== */
.detail-section {
    margin-bottom: 2rem;
    padding-bottom: 2rem;
    border-bottom: 2px solid #f3f4f6;
}

.detail-section:last-of-type {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.section-header {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.section-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 12px;
    color: white;
    flex-shrink: 0;
}

.section-icon.blue { background: linear-gradient(135deg, #667eea, #764ba2); }
.section-icon.green { background: linear-gradient(135deg, #10b981, #059669); }
.section-icon.orange { background: linear-gradient(135deg, #f59e0b, #d97706); }
.section-icon.purple { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
.section-icon.teal { background: linear-gradient(135deg, #14b8a6, #0d9488); }

.icon-svg {
    width: 22px;
    height: 22px;
}

.section-title {
    font-size: 1.05rem;
    font-weight: 600;
    color: #111827;
    margin: 0;
}

.section-subtitle {
    font-size: 0.8rem;
    color: #6b7280;
    margin: 0;
}

.badge-info {
    margin-left: auto;
    padding: 4px 14px;
    background: #e0e7ff;
    color: #4338ca;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}

.btn-edit-section {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 14px;
    background: #f1f5f9;
    color: #64748b;
    border: none;
    border-radius: 16px;
    font-size: 0.75rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    cursor: pointer;
    margin-left: auto;
}

.btn-edit-section:hover {
    background: #e2e8f0;
    color: #1a3a5c;
    transform: translateY(-1px);
}

/* ========== DETAIL GRID ========== */
.detail-grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px 24px;
}

.detail-grid-3 {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 16px 24px;
}

.detail-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
    padding: 8px 12px;
    background: #f8fafc;
    border-radius: 8px;
    border: 1px solid #f1f5f9;
    transition: all 0.3s ease;
}

.detail-item:hover {
    background: #f1f5f9;
    border-color: #e2e8f0;
}

.detail-item.highlight {
    background: #eff6ff;
    border-color: #bfdbfe;
}

.detail-label {
    font-size: 0.7rem;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.detail-value {
    font-size: 0.95rem;
    font-weight: 500;
    color: #111827;
}

.detail-value.vencido {
    color: #dc2626;
    font-weight: 700;
}

.detail-value.vigente {
    color: #10b981;
}

.monto-value {
    font-size: 1.2rem;
    font-weight: 700;
}

.monto-value.ingreso {
    color: #0f172a;
}

.monto-value.egreso {
    color: #dc2626;
}

.monto-value.neutro {
    color: #94a3b8;
}

/* ===== ESTATUS BADGE ===== */
.estatus-badge {
    display: inline-block;
    padding: 3px 14px;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.estatus-badge.pendiente {
    background: #fef3c7;
    color: #92400e;
}

.estatus-badge.abonado {
    background: #dbeafe;
    color: #1e40af;
}

.estatus-badge.liquidado {
    background: #dcfce7;
    color: #166534;
}

/* ===== NOTA ===== */
.nota-content {
    padding: 16px 20px;
    background: #f8fafc;
    border-radius: 8px;
    border-left: 4px solid #14b8a6;
    color: #1f2937;
    line-height: 1.6;
    white-space: pre-wrap;
}

/* ===== TABLA DE ABONOS ===== */
.table-wrapper {
    overflow-x: auto;
    border-radius: 8px;
    border: 1px solid #f1f5f9;
}

.abonos-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.85rem;
}

.abonos-table thead {
    background: #f1f5f9;
}

.abonos-table th {
    padding: 10px 14px;
    text-align: left;
    font-weight: 700;
    color: #1e293b;
    text-transform: uppercase;
    font-size: 0.7rem;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #e2e8f0;
}

.abonos-table td {
    padding: 10px 14px;
    border-bottom: 1px solid #f1f5f9;
    color: #334155;
}

.abonos-table tbody tr:hover {
    background: #f8fafc;
}

.abonos-table tfoot {
    background: #f8fafc;
    font-weight: 700;
}

.abonos-table tfoot td {
    padding: 12px 14px;
    border-top: 2px solid #e2e8f0;
}

.monto-abonado {
    color: #2563eb;
    font-weight: 600;
}

.total-label {
    text-align: right;
    color: #475569;
}

.total-value {
    font-size: 1.1rem;
    font-weight: 700;
}

.total-value.saldo-positivo {
    color: #2563eb;
}

.total-value.saldo-negativo {
    color: #dc2626;
}

.total-value.saldo-cero {
    color: #94a3b8;
}

/* ===== FOOTER ===== */
.detail-footer {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    padding-top: 20px;
    border-top: 2px solid #f3f4f6;
    margin-top: 8px;
}

@media (min-width: 640px) {
    .detail-footer {
        flex-direction: row;
    }
}

.footer-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
    font-size: 0.8rem;
    color: #94a3b8;
}

.footer-actions {
    display: flex;
    gap: 12px;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 20px;
    font-weight: 600;
    border: none;
    border-radius: 50px;
    font-size: 0.85rem;
    transition: all 0.3s ease;
    cursor: pointer;
    text-decoration: none;
}

.btn-icon {
    width: 18px;
    height: 18px;
}

.btn-edit {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
}

.btn-edit:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(102, 126, 234, 0.3);
    color: white;
}

.btn-back-list {
    background: #f1f5f9;
    color: #64748b;
}

.btn-back-list:hover {
    background: #e2e8f0;
    color: #0f172a;
    transform: translateY(-2px);
}

/* ========== RESPONSIVE ========== */
@media (max-width: 768px) {
    .detail-card {
        padding: 1.25rem;
    }

    .detail-grid-2,
    .detail-grid-3 {
        grid-template-columns: 1fr;
        gap: 12px;
    }

    .header-wrapper {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }

    .header-right {
        width: 100%;
        justify-content: flex-start;
    }

    .header-actions {
        width: 100%;
        flex-wrap: wrap;
    }

    .btn-edit-header {
        flex: 1;
        justify-content: center;
    }

    .footer-actions {
        width: 100%;
        flex-direction: column;
    }

    .btn {
        width: 100%;
        justify-content: center;
    }

    .section-header {
        flex-wrap: wrap;
    }

    .badge-info {
        margin-left: 0;
        width: 100%;
        text-align: center;
    }

    .btn-edit-section {
        margin-left: 0;
        width: 100%;
        justify-content: center;
    }

    .abonos-table {
        font-size: 0.75rem;
    }

    .abonos-table th,
    .abonos-table td {
        padding: 6px 10px;
    }
}

@media (max-width: 480px) {
    .header-title {
        font-size: 1.1rem;
    }

    .header-subtitle {
        font-size: 0.75rem;
    }

    .section-title {
        font-size: 0.95rem;
    }
}
</style>