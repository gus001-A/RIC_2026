<template>
    <AppLayout title="RIC - Detalles de Persona">
        <template #header>
            <div class="header-premium">
                <div class="header-content-premium">
                    <div class="header-left-premium">
                        <Link :href="route('personas.index')" class="btn-back-premium">
                            <ArrowLeftOutlined />
                        </Link>
                        <div class="header-icon-wrapper">
                            <svg class="header-icon-svg" fill="none" stroke="white" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="header-title-premium">
                                {{ persona.razon_social_display || persona.nombre_completo }}
                            </h2>
                            <p class="header-subtitle-premium">
                                <span class="subtitle-line"></span>
                                <span class="badge-tipo-persona" :class="getTipoPersonaClass(persona.tipo_persona)">
                                    {{ persona.tipo_persona_texto }}
                                </span>
                                <span v-if="persona.rfc" class="badge-rfc">RFC: {{ persona.rfc }}</span>
                                <span v-if="!persona.activo" class="badge-inactivo">Inactivo</span>
                            </p>
                        </div>
                    </div>
                    <div class="header-right-premium">
                        <Link :href="route('personas.edit', persona.id_persona)">
                            <a-button type="primary" size="large" class="btn-editar-premium">
                                <template #icon>
                                    <EditOutlined />
                                </template>
                                Editar Persona
                            </a-button>
                        </Link>
                    </div>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Stats Cards -->
                <a-row :gutter="[20, 20]" class="mb-6">
                    <a-col :xs="24" :sm="8" :md="8">
                        <div class="stats-card-enhanced">
                            <div class="stats-card-enhanced-content">
                                <div class="stats-card-enhanced-left">
                                    <span class="stats-card-enhanced-label">ID Persona</span>
                                    <span class="stats-card-enhanced-value" style="font-size: 22px;">#{{ persona.id_persona }}</span>
                                </div>
                                <div class="stats-card-enhanced-icon" style="background: linear-gradient(135deg, rgba(26, 58, 92, 0.1), rgba(26, 58, 92, 0.05));">
                                    <UserOutlined style="font-size: 28px; color: #1a3a5c;" />
                                </div>
                            </div>
                        </div>
                    </a-col>

                    <a-col :xs="24" :sm="8" :md="8">
                        <div class="stats-card-enhanced">
                            <div class="stats-card-enhanced-content">
                                <div class="stats-card-enhanced-left">
                                    <span class="stats-card-enhanced-label">Documentos</span>
                                    <span class="stats-card-enhanced-value" style="color: #805ad5;">{{ persona.total_documentos || 0 }}</span>
                                </div>
                                <div class="stats-card-enhanced-icon" style="background: linear-gradient(135deg, rgba(128, 90, 213, 0.1), rgba(128, 90, 213, 0.05));">
                                    <FileOutlined style="font-size: 28px; color: #805ad5;" />
                                </div>
                            </div>
                        </div>
                    </a-col>

                    <a-col :xs="24" :sm="8" :md="8">
                        <div class="stats-card-enhanced">
                            <div class="stats-card-enhanced-content">
                                <div class="stats-card-enhanced-left">
                                    <span class="stats-card-enhanced-label">Estado</span>
                                    <span class="stats-card-enhanced-value" :style="{ color: persona.activo ? '#10b981' : '#ef4444' }">
                                        {{ persona.activo ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </div>
                                <div class="stats-card-enhanced-icon" :style="{ background: persona.activo ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)' }">
                                    <CheckCircleOutlined v-if="persona.activo" style="font-size: 28px; color: #10b981;" />
                                    <CloseCircleOutlined v-else style="font-size: 28px; color: #ef4444;" />
                                </div>
                            </div>
                            <div class="stats-card-enhanced-progress">
                                <div class="stats-card-enhanced-progress-bar" :style="{ 
                                    width: '100%', 
                                    background: persona.activo ? 'linear-gradient(90deg, #10b981, #34d399)' : 'linear-gradient(90deg, #ef4444, #f87171)' 
                                }"></div>
                            </div>
                        </div>
                    </a-col>
                </a-row>

                <!-- Tabs -->
                <div class="tabs-wrapper-premium">
                    <a-tabs v-model:activeKey="activeTab" class="tabs-premium">
                        <!-- TAB 1: INFORMACIÓN -->
                        <a-tab-pane key="info" tab="Información General">
                            <div class="tab-content-premium">
                                <div class="info-grid-premium">
                                    <!-- Tipo de persona -->
                                    <div class="info-item-premium">
                                        <span class="info-label-premium">Tipo de Persona</span>
                                        <span class="info-value-premium">
                                            <span class="badge-tipo-persona" :class="getTipoPersonaClass(persona.tipo_persona)">
                                                {{ persona.tipo_persona_texto }}
                                            </span>
                                        </span>
                                    </div>

                                    <!-- RFC -->
                                    <div class="info-item-premium">
                                        <span class="info-label-premium">RFC</span>
                                        <span class="info-value-premium font-mono">{{ persona.rfc || '—' }}</span>
                                    </div>

                                    <!-- CURP -->
                                    <div class="info-item-premium">
                                        <span class="info-label-premium">CURP</span>
                                        <span class="info-value-premium font-mono">{{ persona.curp || '—' }}</span>
                                    </div>

                                    <!-- Email -->
                                    <div class="info-item-premium">
                                        <span class="info-label-premium">Correo Electrónico</span>
                                        <span class="info-value-premium">
                                            <a v-if="persona.email" :href="'mailto:' + persona.email" class="email-link-ultra">
                                                {{ persona.email }}
                                            </a>
                                            <span v-else>—</span>
                                        </span>
                                    </div>

                                    <!-- Teléfonos -->
                                    <div class="info-item-premium">
                                        <span class="info-label-premium">Teléfonos</span>
                                        <span class="info-value-premium">
                                            <div v-if="persona.telefono_particular" class="telefono-item">
                                                <PhoneOutlined class="telefono-icon" />
                                                {{ persona.telefono_particular }}
                                            </div>
                                            <div v-if="persona.telefono_trabajo" class="telefono-item">
                                                <BuildOutlined class="telefono-icon" />
                                                {{ persona.telefono_trabajo }}
                                                <span v-if="persona.extension_trabajo" class="extension-text">ext. {{ persona.extension_trabajo }}</span>
                                            </div>
                                            <span v-if="!persona.telefono_particular && !persona.telefono_trabajo">—</span>
                                        </span>
                                    </div>

                                    <!-- Ubicación -->
                                    <div class="info-item-premium">
                                        <span class="info-label-premium">Ubicación</span>
                                        <span class="info-value-premium">
                                            <div v-if="persona.calle || persona.colonia || persona.ciudad || persona.estado" class="ubicacion-content">
                                                <span v-if="persona.calle">{{ persona.calle }} {{ persona.numero_exterior || '' }} {{ persona.numero_interior ? 'Int. ' + persona.numero_interior : '' }}</span>
                                                <span v-if="persona.colonia">{{ persona.colonia }}</span>
                                                <span v-if="persona.ciudad || persona.estado">
                                                    {{ persona.ciudad || '' }}{{ persona.ciudad && persona.estado ? ', ' : '' }}{{ persona.estado || '' }}
                                                </span>
                                                <span v-if="persona.codigo_postal" class="ubicacion-cp">CP {{ persona.codigo_postal }}</span>
                                            </div>
                                            <span v-else>—</span>
                                        </span>
                                    </div>

                                    <!-- Notas -->
                                    <div class="info-item-premium full-width">
                                        <span class="info-label-premium">Notas</span>
                                        <span class="info-value-premium">{{ persona.notas || 'Sin notas' }}</span>
                                    </div>
                                </div>

                                <!-- Separador condicional para representante -->
                                <div v-if="persona.tipo_persona === 'MORAL' && persona.representante_nombre" class="representante-section-premium">
                                    <div class="representante-header-premium">
                                        <UserOutlined style="font-size: 18px; color: #667eea;" />
                                        <span class="representante-title-premium">Representante Legal</span>
                                    </div>
                                    <div class="info-grid-premium">
                                        <div class="info-item-premium">
                                            <span class="info-label-premium">Nombre</span>
                                            <span class="info-value-premium">{{ persona.representante_nombre_completo }}</span>
                                        </div>
                                        <div class="info-item-premium">
                                            <span class="info-label-premium">Fecha de Nacimiento</span>
                                            <span class="info-value-premium">{{ formatFecha(persona.representante_fecha_nacimiento) }}</span>
                                        </div>
                                        <div class="info-item-premium">
                                            <span class="info-label-premium">Sexo</span>
                                            <span class="info-value-premium">{{ persona.representante_sexo_texto || '—' }}</span>
                                        </div>
                                        <div class="info-item-premium">
                                            <span class="info-label-premium">Correo</span>
                                            <span class="info-value-premium">
                                                <a v-if="persona.representante_email" :href="'mailto:' + persona.representante_email" class="email-link-ultra">
                                                    {{ persona.representante_email }}
                                                </a>
                                                <span v-else>—</span>
                                            </span>
                                        </div>
                                        <div class="info-item-premium">
                                            <span class="info-label-premium">Teléfono Particular</span>
                                            <span class="info-value-premium">
                                                <span v-if="persona.representante_telefono_particular">
                                                    <PhoneOutlined class="telefono-icon" />
                                                    {{ persona.representante_telefono_particular }}
                                                </span>
                                                <span v-else>—</span>
                                            </span>
                                        </div>
                                        <div class="info-item-premium">
                                            <span class="info-label-premium">Teléfono Trabajo</span>
                                            <span class="info-value-premium">
                                                <span v-if="persona.representante_telefono_trabajo">
                                                    <BuildOutlined class="telefono-icon" />
                                                    {{ persona.representante_telefono_trabajo }}
                                                    <span v-if="persona.representante_extension_trabajo" class="extension-text">ext. {{ persona.representante_extension_trabajo }}</span>
                                                </span>
                                                <span v-else>—</span>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Fechas de creación y actualización -->
                                <div class="fechas-section-premium">
                                    <div class="fecha-item-premium">
                                        <span class="fecha-label-premium">Creado</span>
                                        <span class="fecha-value-premium">{{ formatFecha(persona.created_at) }}</span>
                                    </div>
                                    <div class="fecha-item-premium">
                                        <span class="fecha-label-premium">Actualizado</span>
                                        <span class="fecha-value-premium">{{ formatFecha(persona.updated_at) }}</span>
                                    </div>
                                </div>
                            </div>
                        </a-tab-pane>

                        <!-- TAB 2: DOCUMENTOS -->
                        <a-tab-pane key="documentos" tab="Documentos">
                            <div class="tab-content-premium">
                                <div class="documentos-tab-container">
                                    <!-- Subir documento -->
                                    <div class="upload-section-premium">
                                        <div class="upload-header-premium">
                                            <CloudUploadOutlined style="font-size: 18px; color: #667eea;" />
                                            <span class="upload-title-premium">Subir nuevo documento</span>
                                        </div>
                                        <form @submit.prevent="subirDocumento" class="upload-form-premium">
                                            <div class="upload-grid-premium">
                                                <div class="upload-field-premium">
                                                    <label class="upload-label-premium">
                                                        <FileOutlined style="font-size: 14px; color: #667eea;" />
                                                        Tipo de documento <span class="required-star">*</span>
                                                    </label>
                                                    <a-select
                                                        v-model:value="nuevoDocumento.tipo"
                                                        placeholder="Seleccionar tipo"
                                                        class="upload-select-premium"
                                                        size="large"
                                                    >
                                                        <a-select-option value="INE">INE</a-select-option>
                                                        <a-select-option value="RFC">RFC</a-select-option>
                                                        <a-select-option value="CURP">CURP</a-select-option>
                                                        <a-select-option value="COMPROBANTE">Comprobante Domicilio</a-select-option>
                                                        <a-select-option value="OTRO">Otro</a-select-option>
                                                    </a-select>
                                                </div>

                                                <div class="upload-field-premium">
                                                    <label class="upload-label-premium">
                                                        <FileOutlined style="font-size: 14px; color: #667eea;" />
                                                        Título (opcional)
                                                    </label>
                                                    <input 
                                                        type="text" 
                                                        v-model="nuevoDocumento.titulo"
                                                        class="form-input-premium"
                                                        placeholder="Ej: INE de Juan Pérez"
                                                    />
                                                </div>

                                                <div class="upload-field-premium">
                                                    <label class="upload-label-premium">
                                                        <UploadOutlined style="font-size: 14px; color: #667eea;" />
                                                        Archivo <span class="required-star">*</span>
                                                    </label>
                                                    <div class="file-upload-wrapper-premium">
                                                        <input 
                                                            type="file" 
                                                            ref="fileInput"
                                                            @change="handleFileSelect"
                                                            accept=".pdf,.jpg,.jpeg,.png"
                                                            class="file-input-hidden-premium"
                                                        >
                                                        <div 
                                                            class="file-drop-zone-premium"
                                                            :class="{ 'has-file': archivoSeleccionado }"
                                                            @click="$refs.fileInput.click()"
                                                        >
                                                            <div v-if="!archivoSeleccionado" class="file-placeholder-premium">
                                                                <UploadOutlined style="font-size: 28px; color: #94a3b8;" />
                                                                <span>Haz clic para seleccionar</span>
                                                                <span class="file-types-premium">PDF, JPG, PNG (max. 5MB)</span>
                                                            </div>
                                                            <div v-else class="file-selected-premium">
                                                                <FileOutlined style="font-size: 24px; color: #667eea;" />
                                                                <span class="file-name-premium">{{ archivoSeleccionado.name }}</span>
                                                                <span class="file-size-premium">({{ (archivoSeleccionado.size / 1024).toFixed(1) }} KB)</span>
                                                                <button type="button" class="btn-remove-file" @click.stop="removerArchivo">
                                                                    <CloseOutlined />
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="upload-field-premium">
                                                    <label class="upload-label-premium" style="cursor: pointer; padding-top: 24px;">
                                                        <input type="checkbox" v-model="nuevoDocumento.finalizado" />
                                                        <span style="margin-left: 8px; font-weight: 500;">Marcar como finalizado</span>
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="upload-actions-premium">
                                                <a-button 
                                                    type="primary"
                                                    html-type="submit"
                                                    :loading="subiendoDocumento"
                                                    class="btn-upload-premium"
                                                    :disabled="!nuevoDocumento.tipo || !archivoSeleccionado"
                                                >
                                                    <template #icon>
                                                        <CloudUploadOutlined />
                                                    </template>
                                                    Subir documento
                                                </a-button>
                                            </div>
                                        </form>
                                    </div>

                                    <!-- Lista de documentos -->
                                    <div class="documentos-list-section-premium">
                                        <div class="documentos-list-header-premium">
                                            <FileOutlined style="font-size: 18px; color: #667eea;" />
                                            <span class="documentos-list-title-premium">Documentos existentes</span>
                                            <span class="documentos-count-premium">{{ persona.total_documentos || 0 }}</span>
                                        </div>
                                        
                                        <div v-if="persona.documentos && persona.documentos.length > 0" class="documentos-list-premium">
                                            <div v-for="doc in persona.documentos" :key="doc.id" class="documento-item-premium">
                                                <div class="documento-info-premium">
                                                    <div class="documento-icon-premium" :style="{ background: getColorDocumento(doc.tipo_documento) }">
                                                        <FileOutlined style="color: white; font-size: 16px;" />
                                                    </div>
                                                    <div>
                                                        <div class="documento-nombre-premium">
                                                            {{ doc.tipo_documento_texto || doc.tipo_documento }}
                                                            <span v-if="doc.finalizado" class="doc-finalizado-badge">Finalizado</span>
                                                            <span v-else class="doc-pendiente-badge">Pendiente</span>
                                                        </div>
                                                        <div class="documento-fecha-premium">
                                                            <span>{{ formatFecha(doc.fecha_subida) }}</span>
                                                            <span v-if="doc.titulo" class="doc-titulo-premium">• {{ doc.titulo }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="documento-actions-premium">
                                                    <a-tooltip title="Descargar" placement="top" color="#1a3a5c">
                                                        <a-button size="small" type="link" class="doc-action-btn-premium download" @click="descargarDocumento(doc)">
                                                            <DownloadOutlined />
                                                        </a-button>
                                                    </a-tooltip>
                                                    <a-tooltip title="Marcar finalizado" placement="top" color="#1a3a5c">
                                                        <a-button size="small" type="link" class="doc-action-btn-premium toggle" @click="toggleFinalizado(doc)">
                                                            <CheckOutlined v-if="!doc.finalizado" />
                                                            <CloseOutlined v-else />
                                                        </a-button>
                                                    </a-tooltip>
                                                    <a-tooltip title="Eliminar" placement="top" color="#ef4444">
                                                        <a-button size="small" type="link" danger class="doc-action-btn-premium delete" @click="eliminarDocumento(doc)">
                                                            <DeleteOutlined />
                                                        </a-button>
                                                    </a-tooltip>
                                                </div>
                                            </div>
                                        </div>
                                        <div v-else class="documentos-empty-premium">
                                            <FileOutlined style="font-size: 48px; color: #d1d5db;" />
                                            <p>No hay documentos asociados</p>
                                            <span class="empty-subtext-premium">Sube el primer documento usando el formulario superior</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a-tab-pane>
                    </a-tabs>
                </div>
            </div>
        </div>

        <!-- Modal de validaciones personalizado -->
        <AlertModal ref="alertModal" />
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, nextTick } from 'vue';
import AlertModal from '@/Components/AlertModal.vue';
import axios from 'axios';

// Importar iconos
import {
    PlusOutlined,
    CloseOutlined,
    EditOutlined,
    DeleteOutlined,
    EyeOutlined,
    UserOutlined,
    TeamOutlined,
    FileOutlined,
    UploadOutlined,
    CloudUploadOutlined,
    DownloadOutlined,
    CheckOutlined,
    PhoneOutlined,
    BuildOutlined,
    PoweroffOutlined,
    CheckCircleOutlined,
    CloseCircleOutlined,
    FileProtectOutlined,
    ArrowLeftOutlined,
} from '@ant-design/icons-vue';

// Importar componentes de Ant Design
import {
    Button as AButton,
    Row as ARow,
    Col as ACol,
    Tabs as ATabs,
    TabPane as ATabPane,
    Tag as ATag,
    Select as ASelect,
    Tooltip as ATooltip,
} from 'ant-design-vue';

const props = defineProps({
    persona: Object,
    flash: Object,
});

const activeTab = ref('info');
const alertModal = ref(null);

// Estado para documentos
const subiendoDocumento = ref(false);
const archivoSeleccionado = ref(null);
const fileInput = ref(null);

const nuevoDocumento = ref({
    tipo: '',
    titulo: '',
    finalizado: false,
});

// ============================================
// FUNCIONES DE UTILIDAD
// ============================================

const getTipoPersonaClass = (tipo) => {
    const classes = {
        'FISICA': 'fisica',
        'MORAL': 'moral'
    };
    return classes[tipo] || 'fisica';
};

const getColorDocumento = (tipo) => {
    const colores = {
        'INE': '#3b82f6',
        'RFC': '#8b5cf6',
        'CURP': '#10b981',
        'COMPROBANTE': '#f59e0b',
        'OTRO': '#6b7280'
    };
    return colores[tipo] || '#6b7280';
};

const formatFecha = (fecha) => {
    if (!fecha) return '';
    const d = new Date(fecha);
    return d.toLocaleDateString('es-MX', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

// ============================================
// FUNCIONES PARA DOCUMENTOS
// ============================================

const handleFileSelect = (event) => {
    const file = event.target.files[0];
    if (file) {
        if (file.size > 5 * 1024 * 1024) {
            alertModal.value?.show({
                type: 'error',
                title: 'Archivo muy grande',
                message: 'El archivo no debe superar los 5MB.',
                buttonText: 'Entendido'
            });
            event.target.value = '';
            return;
        }
        archivoSeleccionado.value = file;
    }
};

const removerArchivo = () => {
    archivoSeleccionado.value = null;
    if (fileInput.value) fileInput.value.value = '';
};

const subirDocumento = async () => {
    if (!archivoSeleccionado.value || !nuevoDocumento.value.tipo) {
        return;
    }

    subiendoDocumento.value = true;

    const formData = new FormData();
    formData.append('documento', archivoSeleccionado.value);
    formData.append('tipo_documento', nuevoDocumento.value.tipo);
    formData.append('titulo', nuevoDocumento.value.titulo || `${nuevoDocumento.value.tipo} - ${props.persona.nombre_completo}`);
    formData.append('finalizado', nuevoDocumento.value.finalizado ? '1' : '0');

    try {
        await router.post(
            route('personas.subir-documento', props.persona.id_persona),
            formData,
            {
                preserveScroll: true,
                onSuccess: () => {
                    subiendoDocumento.value = false;
                    archivoSeleccionado.value = null;
                    nuevoDocumento.value = { tipo: '', titulo: '', finalizado: false };
                    if (fileInput.value) fileInput.value.value = '';
                    
                    // Recargar la página para mostrar el nuevo documento
                    router.reload({ preserveScroll: true });
                    
                    alertModal.value?.show({
                        type: 'success',
                        title: 'Documento subido',
                        message: 'El documento se ha subido exitosamente.',
                        buttonText: 'Entendido'
                    });
                },
                onError: (errors) => {
                    subiendoDocumento.value = false;
                    alertModal.value?.show({
                        type: 'error',
                        title: 'Error',
                        message: Object.values(errors).flat()[0] || 'Error al subir el documento',
                        buttonText: 'Entendido'
                    });
                }
            }
        );
    } catch (error) {
        subiendoDocumento.value = false;
        alertModal.value?.show({
            type: 'error',
            title: 'Error',
            message: error.message || 'Error al subir el documento',
            buttonText: 'Entendido'
        });
    }
};

const descargarDocumento = (doc) => {
    window.open(route('personas.descargar-documento', doc.id), '_blank');
};

const toggleFinalizado = (doc) => {
    router.patch(route('personas.toggle-finalizado', doc.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            router.reload({ preserveScroll: true });
            alertModal.value?.show({
                type: 'success',
                title: 'Estado actualizado',
                message: 'El estado del documento ha sido actualizado.',
                buttonText: 'Entendido'
            });
        },
        onError: (errors) => {
            alertModal.value?.show({
                type: 'error',
                title: 'Error',
                message: errors?.error || 'Error al actualizar el estado',
                buttonText: 'Entendido'
            });
        }
    });
};

const eliminarDocumento = (doc) => {
    alertModal.value?.show({
        type: 'error',
        title: 'Eliminar documento',
        message: `
            <div class="text-center">
                <p class="font-medium text-gray-700">${doc.tipo_documento_texto || doc.tipo_documento}</p>
                <p class="text-sm text-red-600 mt-2 font-medium">Esta acción no se puede deshacer</p>
                <p class="text-xs text-gray-500 mt-1">El archivo se eliminará permanentemente</p>
            </div>
        `,
        buttonText: 'Sí, eliminar',
        duration: 0
    }, () => {
        router.delete(route('personas.eliminar-documento', doc.id), {
            preserveScroll: true,
            onSuccess: () => {
                router.reload({ preserveScroll: true });
                alertModal.value?.show({
                    type: 'success',
                    title: 'Documento eliminado',
                    message: 'El documento se ha eliminado correctamente.',
                    buttonText: 'Entendido'
                });
            },
            onError: (errors) => {
                alertModal.value?.show({
                    type: 'error',
                    title: 'Error',
                    message: errors?.error || 'Error al eliminar el documento',
                    buttonText: 'Entendido'
                });
            }
        });
    });
};

// ============================================
// PROCESAR FLASH
// ============================================

const mostrarModal = (type, title, message, duration = 4000, onConfirm = null) => {
    if (alertModal.value && alertModal.value.show) {
        alertModal.value.show({
            type,
            title,
            message,
            duration,
            buttonText: type === 'error' ? 'Entendido' : 'Aceptar'
        }, onConfirm);
    }
};

const procesarFlash = () => {
    if (!props.flash) return;
    
    const tipoMap = {
        success: { type: 'success', title: 'Éxito' },
        error: { type: 'error', title: 'Error' },
        updated: { type: 'success', title: 'Actualizado' },
        created: { type: 'success', title: 'Creado' },
        deleted: { type: 'success', title: 'Eliminado' },
        info: { type: 'info', title: 'Información' },
        warning: { type: 'warning', title: 'Advertencia' }
    };

    for (const [key, message] of Object.entries(props.flash)) {
        if (message && tipoMap[key]) {
            mostrarModal(
                tipoMap[key].type,
                tipoMap[key].title,
                message,
                4000
            );
            break;
        }
    }
};

onMounted(() => {
    if (props.flash && Object.keys(props.flash).length > 0) {
        nextTick(() => {
            procesarFlash();
        });
    }
});
</script>

<style scoped>
/* ===== HEADER ===== */
.header-premium {
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    border-radius: 20px;
    padding: 24px 28px;
    margin-bottom: 8px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
    border: 1px solid #f0f2f5;
}

.header-content-premium {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

@media (min-width: 640px) {
    .header-content-premium {
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
    }
}

.header-left-premium {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}

.btn-back-premium {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: white;
    border: 2px solid #e2e8f0;
    color: #64748b;
    transition: all 0.3s ease;
    font-size: 18px;
    flex-shrink: 0;
}

.btn-back-premium:hover {
    border-color: #1a3a5c;
    color: #1a3a5c;
    transform: translateX(-4px);
    box-shadow: 0 4px 12px rgba(26, 58, 92, 0.1);
}

.header-icon-wrapper {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, #1a3a5c, #2c5282);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(26, 58, 92, 0.2);
    flex-shrink: 0;
}

.header-icon-svg {
    width: 28px;
    height: 28px;
    stroke: white;
}

.header-title-premium {
    font-size: 24px;
    font-weight: 700;
    color: #0f172a;
    margin: 0;
    letter-spacing: -0.5px;
}

.header-subtitle-premium {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #64748b;
    font-size: 14px;
    margin: 4px 0 0 0;
    flex-wrap: wrap;
}

.subtitle-line {
    width: 24px;
    height: 2px;
    background: linear-gradient(90deg, #1a3a5c, transparent);
    border-radius: 2px;
}

.header-right-premium {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

/* ===== BADGES ===== */
.badge-tipo-persona {
    display: inline-flex;
    align-items: center;
    padding: 4px 14px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.badge-tipo-persona.fisica {
    background: #dbeafe;
    color: #1d4ed8;
}

.badge-tipo-persona.moral {
    background: #ede9fe;
    color: #5b21b6;
}

.badge-rfc {
    font-size: 12px;
    font-weight: 500;
    color: #475569;
    background: #f1f5f9;
    padding: 2px 10px;
    border-radius: 4px;
    font-family: monospace;
}

.badge-inactivo {
    font-size: 12px;
    font-weight: 600;
    color: #ef4444;
    background: #fef2f2;
    padding: 2px 10px;
    border-radius: 4px;
    border: 1px solid #fecaca;
}

/* ===== STATS CARDS ===== */
.stats-card-enhanced {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid #f0f2f5;
    overflow: hidden;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.stats-card-enhanced:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
    border-color: #d0d7de;
}

.stats-card-enhanced-content {
    padding: 20px 20px 16px;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
}

.stats-card-enhanced-left {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.stats-card-enhanced-label {
    font-size: 13px;
    font-weight: 500;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stats-card-enhanced-value {
    font-size: 28px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.2;
}

.stats-card-enhanced-icon {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: all 0.3s ease;
}

.stats-card-enhanced:hover .stats-card-enhanced-icon {
    transform: scale(1.1) rotate(-5deg);
}

.stats-card-enhanced-progress {
    height: 4px;
    background: #f1f5f9;
}

.stats-card-enhanced-progress-bar {
    height: 100%;
    transition: width 1.2s cubic-bezier(0.4, 0, 0.2, 1);
}

/* ===== TABS ===== */
.tabs-wrapper-premium {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid #f0f2f5;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    padding: 20px;
}

.tabs-premium :deep(.ant-tabs-nav) {
    margin-bottom: 24px !important;
}

.tabs-premium :deep(.ant-tabs-nav::before) {
    border-bottom: 2px solid #f1f5f9 !important;
}

.tabs-premium :deep(.ant-tabs-tab) {
    padding: 12px 20px !important;
    font-weight: 600 !important;
    font-size: 14px !important;
    color: #64748b !important;
    transition: all 0.3s ease !important;
}

.tabs-premium :deep(.ant-tabs-tab:hover) {
    color: #1a3a5c !important;
}

.tabs-premium :deep(.ant-tabs-tab-active) {
    color: #1a3a5c !important;
}

.tabs-premium :deep(.ant-tabs-tab-active .ant-tabs-tab-btn) {
    color: #1a3a5c !important;
}

.tabs-premium :deep(.ant-tabs-ink-bar) {
    background: linear-gradient(90deg, #1a3a5c, #2c5282) !important;
    height: 3px !important;
}

.tab-content-premium {
    padding: 8px 0;
}

/* ===== INFO GRID ===== */
.info-grid-premium {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px 40px;
}

@media (max-width: 640px) {
    .info-grid-premium {
        grid-template-columns: 1fr;
        gap: 16px;
    }
}

.info-item-premium {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.info-item-premium.full-width {
    grid-column: 1 / -1;
}

.info-label-premium {
    font-size: 12px;
    font-weight: 600;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.info-value-premium {
    font-size: 15px;
    color: #0f172a;
    font-weight: 500;
    word-break: break-word;
}

/* ===== UBICACIÓN ===== */
.ubicacion-content {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.ubicacion-cp {
    font-size: 13px;
    color: #94a3b8;
    font-weight: 500;
}

/* ===== REPRESENTANTE ===== */
.representante-section-premium {
    margin-top: 24px;
    padding-top: 24px;
    border-top: 2px solid #f1f5f9;
}

.representante-header-premium {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 16px;
}

.representante-title-premium {
    font-size: 16px;
    font-weight: 700;
    color: #0f172a;
}

/* ===== FECHAS ===== */
.fechas-section-premium {
    margin-top: 24px;
    padding-top: 20px;
    border-top: 2px solid #f1f5f9;
    display: flex;
    gap: 32px;
    flex-wrap: wrap;
}

.fecha-item-premium {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.fecha-label-premium {
    font-size: 12px;
    font-weight: 600;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.fecha-value-premium {
    font-size: 14px;
    color: #475569;
}

/* ===== BOTONES ===== */
.btn-editar-premium {
    background: linear-gradient(135deg, #1a3a5c 0%, #2c5282 100%) !important;
    border: none !important;
    border-radius: 0px !important;
    font-weight: 700 !important;
    padding: 0 32px !important;
    height: 50px !important;
    box-shadow: 0 4px 16px rgba(26, 58, 92, 0.3);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
    font-size: 15px !important;
    letter-spacing: 0.3px;
}

.btn-editar-premium:hover {
    transform: translateY(-3px) scale(1.02);
    box-shadow: 0 8px 30px rgba(26, 58, 92, 0.4) !important;
    background: linear-gradient(135deg, #2c5282 0%, #1a3a5c 100%) !important;
}

/* ============================================ */
/* DOCUMENTOS                                   */
/* ============================================ */
.documentos-tab-container {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.upload-section-premium {
    background: #f8fafc;
    border-radius: 12px;
    padding: 20px;
    border: 1px solid #e2e8f0;
}

.upload-header-premium {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 16px;
}

.upload-title-premium {
    font-size: 15px;
    font-weight: 600;
    color: #0f172a;
}

.upload-grid-premium {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

@media (max-width: 640px) {
    .upload-grid-premium {
        grid-template-columns: 1fr;
    }
}

.upload-field-premium {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.upload-label-premium {
    font-size: 13px;
    font-weight: 500;
    color: #475569;
    display: flex;
    align-items: center;
    gap: 6px;
}

.required-star {
    color: #ef4444;
    font-weight: 700;
}

.upload-select-premium :deep(.ant-select-selector) {
    border-radius: 8px !important;
    height: 44px !important;
    border: 2px solid #e2e8f0 !important;
    transition: all 0.3s ease !important;
}

.upload-select-premium :deep(.ant-select-selector:hover) {
    border-color: #667eea !important;
}

.upload-select-premium :deep(.ant-select-focused .ant-select-selector) {
    border-color: #667eea !important;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1) !important;
}

/* ===== FILE UPLOAD ===== */
.file-upload-wrapper-premium {
    position: relative;
}

.file-input-hidden-premium {
    display: none;
}

.file-drop-zone-premium {
    border: 2px dashed #e2e8f0;
    border-radius: 8px;
    padding: 16px;
    cursor: pointer;
    transition: all 0.3s ease;
    background: white;
    min-height: 54px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.file-drop-zone-premium:hover {
    border-color: #667eea;
    background: #f8f7ff;
}

.file-drop-zone-premium.has-file {
    border-color: #667eea;
    background: #f8f7ff;
}

.file-placeholder-premium {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
}

.file-placeholder-premium span {
    font-size: 13px;
    color: #94a3b8;
}

.file-types-premium {
    font-size: 11px !important;
    color: #cbd5e1 !important;
}

.file-selected-premium {
    display: flex;
    align-items: center;
    gap: 8px;
    width: 100%;
}

.file-name-premium {
    font-size: 13px;
    font-weight: 500;
    color: #0f172a;
    flex: 1;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.file-size-premium {
    font-size: 12px;
    color: #94a3b8;
}

.btn-remove-file {
    background: transparent;
    border: none;
    color: #ef4444;
    cursor: pointer;
    padding: 4px;
    border-radius: 4px;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-remove-file:hover {
    background: #fef2f2;
    transform: scale(1.1);
}

/* ===== FORM INPUT ===== */
.form-input-premium {
    width: 100%;
    padding: 10px 14px;
    font-size: 0.9rem;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    background: white;
    color: #1f2937;
    transition: all 0.3s ease;
    outline: none;
    height: 44px;
}

.form-input-premium:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

/* ===== UPLOAD BUTTON ===== */
.upload-actions-premium {
    margin-top: 16px;
    display: flex;
    justify-content: flex-end;
}

.btn-upload-premium {
    border-radius: 8px !important;
    height: 44px !important;
    padding: 0 32px !important;
    background: linear-gradient(135deg, #667eea, #764ba2) !important;
    border: none !important;
    font-weight: 600 !important;
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3) !important;
    transition: all 0.3s ease !important;
}

.btn-upload-premium:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(102, 126, 234, 0.4) !important;
}

.btn-upload-premium:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* ===== LISTA DE DOCUMENTOS ===== */
.documentos-list-section-premium {
    margin-top: 4px;
}

.documentos-list-header-premium {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 12px;
}

.documentos-list-title-premium {
    font-size: 15px;
    font-weight: 600;
    color: #0f172a;
}

.documentos-count-premium {
    background: #e5e7eb;
    color: #6b7280;
    font-size: 12px;
    font-weight: 600;
    padding: 2px 10px;
    border-radius: 20px;
    margin-left: auto;
}

.documentos-list-premium {
    display: flex;
    flex-direction: column;
    gap: 8px;
    max-height: 280px;
    overflow-y: auto;
    padding-right: 4px;
}

.documentos-list-premium::-webkit-scrollbar {
    width: 4px;
}

.documentos-list-premium::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.documentos-list-premium::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}

.documentos-list-premium::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

.documento-item-premium {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    transition: all 0.2s ease;
}

.documento-item-premium:hover {
    border-color: #cbd5e1;
    background: #f8fafc;
    transform: translateX(4px);
}

.documento-info-premium {
    display: flex;
    align-items: center;
    gap: 12px;
}

.documento-icon-premium {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.documento-nombre-premium {
    font-size: 14px;
    font-weight: 500;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.doc-finalizado-badge {
    font-size: 11px;
    font-weight: 600;
    color: #10b981;
    background: #dcfce7;
    padding: 2px 8px;
    border-radius: 4px;
}

.doc-pendiente-badge {
    font-size: 11px;
    font-weight: 600;
    color: #f59e0b;
    background: #fef3c7;
    padding: 2px 8px;
    border-radius: 4px;
}

.documento-fecha-premium {
    font-size: 12px;
    color: #94a3b8;
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}

.doc-titulo-premium {
    color: #6b7280;
}

.documento-actions-premium {
    display: flex;
    gap: 4px;
}

.doc-action-btn-premium {
    font-size: 16px !important;
    padding: 4px 8px !important;
    transition: all 0.3s ease !important;
}

.doc-action-btn-premium.download:hover {
    color: #667eea !important;
    transform: scale(1.1);
}

.doc-action-btn-premium.toggle:hover {
    color: #f59e0b !important;
    transform: scale(1.1);
}

.doc-action-btn-premium.delete:hover {
    transform: scale(1.1);
}

/* ===== EMPTY STATE ===== */
.documentos-empty-premium {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
    background: #f8fafc;
    border-radius: 8px;
    border: 2px dashed #e2e8f0;
}

.documentos-empty-premium p {
    color: #94a3b8;
    margin-top: 12px;
    font-size: 14px;
    font-weight: 500;
}

.empty-subtext-premium {
    color: #cbd5e1;
    font-size: 12px;
    margin-top: 4px;
}

/* ===== TELEFONO ===== */
.telefono-item {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 14px;
}

.telefono-icon {
    font-size: 14px;
    color: #94a3b8;
}

.extension-text {
    font-size: 12px;
    color: #94a3b8;
    font-weight: 500;
}

.email-link-ultra {
    color: #2563eb;
    text-decoration: none;
    transition: all 0.2s ease;
}

.email-link-ultra:hover {
    color: #1d4ed8;
    text-decoration: underline;
}

/* ===== FONT MONO ===== */
.font-mono {
    font-family: 'Courier New', monospace;
    letter-spacing: 0.5px;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    .header-premium {
        padding: 16px 20px;
    }
    
    .header-title-premium {
        font-size: 20px;
    }
    
    .header-icon-wrapper {
        width: 44px;
        height: 44px;
    }
    
    .header-icon-svg {
        width: 22px;
        height: 22px;
    }
    
    .stats-card-enhanced-value {
        font-size: 22px;
    }
    
    .stats-card-enhanced-icon {
        width: 44px;
        height: 44px;
    }
    
    .tabs-wrapper-premium {
        padding: 16px;
    }
    
    .btn-editar-premium {
        padding: 0 20px !important;
        height: 40px !important;
        font-size: 14px !important;
    }
    
    .documento-item-premium {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
    
    .documento-actions-premium {
        width: 100%;
        justify-content: flex-end;
    }
    
    .btn-upload-premium {
        width: 100% !important;
        justify-content: center !important;
    }
    
    .fechas-section-premium {
        flex-direction: column;
        gap: 12px;
    }
}

@media (max-width: 480px) {
    .header-subtitle-premium {
        font-size: 12px;
    }
    
    .btn-back-premium {
        width: 36px;
        height: 36px;
        font-size: 14px;
    }
    
    .info-value-premium {
        font-size: 14px;
    }
    
    .documento-nombre-premium {
        font-size: 13px;
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
    }
    
    .file-drop-zone-premium {
        padding: 12px;
        min-height: 44px;
    }
    
    .file-selected-premium {
        flex-wrap: wrap;
    }
    
    .file-name-premium {
        font-size: 12px;
    }
}
</style>