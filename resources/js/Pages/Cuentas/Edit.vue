<template>
    <AppLayout :title="`RIC - Editar Cuenta: ${form.nombre_cuenta || 'Sin nombre'}`">
        <template #header>
            <div class="header-premium">
                <div class="header-content-premium">
                    <div class="header-left-premium">
                        <div class="header-icon-wrapper">
                            <svg class="header-icon-svg" fill="none" stroke="white" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="header-title-premium">Editar Cuenta</h2>
                            <p class="header-subtitle-premium">
                                <span class="subtitle-line"></span>
                                {{ form.nombre_cuenta || 'Sin nombre' }} ({{ form.codigo_cuenta || 'Sin código' }})
                            </p>
                        </div>
                    </div>
                    <div class="header-actions-premium">
                        <button type="button" class="btn-delete-premium" @click="confirmarEliminar" v-if="cuenta.en_uso">
                            <svg class="btn-icon-premium" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Desactivar
                        </button>
                        <button type="button" class="btn-delete-premium btn-reactivate" @click="confirmarReactivar" v-else>
                            <svg class="btn-icon-premium" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Reactivar
                        </button>
                        <Link :href="route('cuentas.index', { empresa_id: form.id_empresa })" class="btn-back-premium">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Volver
                        </Link>
                    </div>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Status Banner -->
                <div class="status-banner-premium" :class="statusClass">
                    <div class="status-banner-content">
                        <span class="status-banner-icon">
                            <svg v-if="hasErrors" class="status-icon-error" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <svg v-else-if="isComplete" class="status-icon-success" fill="none" stroke="#10b981" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <svg v-else class="status-icon-progress" fill="none" stroke="#667eea" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                            </svg>
                        </span>
                        <span class="status-banner-text">
                            <span v-if="hasErrors">{{ errorCount }} error(es) en el formulario</span>
                            <span v-else-if="isComplete">Formulario completado correctamente</span>
                            <span v-else>Complete todos los campos obligatorios</span>
                        </span>
                        <div class="status-banner-progress">
                            <div class="status-banner-progress-bar" :style="{ width: progressPercentage + '%' }"></div>
                        </div>
                        <span class="status-banner-percent">{{ Math.round(progressPercentage) }}%</span>
                    </div>
                </div>

                <!-- Form Card -->
                <div class="form-card-premium">
                    <form @submit.prevent="submit" id="cuentaForm" novalidate>
                        <!-- ============================================ -->
                        <!-- SECCIÓN 1: DATOS BÁSICOS -->
                        <!-- ============================================ -->
                        <div class="form-section-premium">
                            <div class="section-header-premium">
                                <div class="section-icon-premium" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                                    <svg class="icon-svg-premium" fill="none" stroke="white" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="section-title-premium">Datos Básicos</h3>
                                    <p class="section-subtitle-premium">Información principal de la cuenta contable</p>
                                </div>
                            </div>

                            <div class="form-grid-premium">
                                <!-- Código -->
                                <div class="form-group-premium">
                                    <label class="form-label-premium">
                                        <span class="label-icon-wrapper">
                                            <svg class="label-icon-premium" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                                            </svg>
                                        </span>
                                        Código <span class="required-star">*</span>
                                        <span v-if="verificandoCodigo" class="label-badge-premium" style="background: #f59e0b;">Verificando...</span>
                                        <span v-else-if="codigoExiste && form.codigo_cuenta && form.codigo_cuenta !== codigoOriginal" class="label-badge-premium" style="background: #ef4444;">Ocupado</span>
                                        <span v-else-if="!codigoExiste && form.codigo_cuenta && form.codigo_cuenta.length > 0 && form.codigo_cuenta !== codigoOriginal" class="label-badge-premium" style="background: #10b981;">Disponible</span>
                                        <span v-else-if="form.codigo_cuenta === codigoOriginal" class="label-badge-premium" style="background: #94a3b8;">Sin cambios</span>
                                    </label>
                                    <div class="input-wrapper-premium input-with-icon">
                                        <svg class="input-icon-premium" fill="none" stroke="#94a3b8" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                                        </svg>
                                        <input type="text" v-model="form.codigo_cuenta"
                                               @input="clearError('codigo_cuenta'); verificarCodigo()"
                                               class="form-input-premium"
                                               :class="{ 'error': form.errors.codigo_cuenta || codigoExiste }"
                                               placeholder="Ej: AC001, CI001, CJ001">
                                    </div>
                                    <div v-if="form.errors.codigo_cuenta" class="error-message-premium">
                                        <svg class="error-icon-premium" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ form.errors.codigo_cuenta }}
                                    </div>
                                </div>

                                <!-- Nombre -->
                                <div class="form-group-premium">
                                    <label class="form-label-premium">
                                        <span class="label-icon-wrapper">
                                            <svg class="label-icon-premium" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                            </svg>
                                        </span>
                                        Nombre <span class="required-star">*</span>
                                    </label>
                                    <div class="input-wrapper-premium input-with-icon">
                                        <svg class="input-icon-premium" fill="none" stroke="#94a3b8" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                        <input type="text" v-model="form.nombre_cuenta"
                                               @input="clearError('nombre_cuenta')"
                                               class="form-input-premium"
                                               :class="{ 'error': form.errors.nombre_cuenta }"
                                               placeholder="Ej: Bancos, Clientes, Proveedores">
                                    </div>
                                    <div v-if="form.errors.nombre_cuenta" class="error-message-premium">
                                        <svg class="error-icon-premium" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ form.errors.nombre_cuenta }}
                                    </div>
                                </div>

                                <!-- Nivel -->
                                <div class="form-group-premium">
                                    <label class="form-label-premium">
                                        <span class="label-icon-wrapper">
                                            <svg class="label-icon-premium" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                                            </svg>
                                        </span>
                                        Nivel
                                        <span v-if="form.es_cuenta_resultados" class="label-badge-premium" style="background: #8b5cf6;">Nivel 2 fijo</span>
                                    </label>
                                    <div class="input-wrapper-premium input-with-icon">
                                        <svg class="input-icon-premium" fill="none" stroke="#94a3b8" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                                        </svg>
                                        <input type="text" 
                                               :value="'Nivel ' + form.nivel"
                                               class="form-input-premium"
                                               disabled
                                               style="background: #f1f5f9; cursor: not-allowed; opacity: 0.8;">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ============================================ -->
                        <!-- SECCIÓN 2: CLASIFICACIÓN -->
                        <!-- ============================================ -->
                        <div class="form-section-premium compact">
                            <div class="section-header-premium">
                                <div class="section-icon-premium" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">
                                    <svg class="icon-svg-premium" fill="none" stroke="white" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="section-title-premium">Clasificación</h3>
                                    <p class="section-subtitle-premium">Define la naturaleza y tipo de la cuenta</p>
                                </div>
                            </div>

                            <!-- Naturaleza -->
                            <div class="naturaleza-section">
                                <div class="naturaleza-header">
                                    <span class="naturaleza-label">Naturaleza <span class="required-star">*</span></span>
                                    <span class="naturaleza-help">Selecciona el tipo de saldo de la cuenta</span>
                                </div>
                                <div class="radio-group-premium">
                                    <div class="radio-card-premium" 
                                         :class="{ 'selected': form.Naturaleza === 'DEUDORA' }"
                                         @click="form.Naturaleza = 'DEUDORA'; clearError('Naturaleza')">
                                        <div class="radio-content">
                                            <div class="radio-dot"></div>
                                            <div>
                                                <div class="radio-title-premium">Deudora</div>
                                                <div class="radio-desc-premium">Activos, Gastos</div>
                                            </div>
                                        </div>
                                        <div class="radio-check-premium" v-if="form.Naturaleza === 'DEUDORA'">
                                            <svg class="check-icon-premium" fill="none" stroke="#10b981" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </div>
                                    </div>

                                    <div class="radio-card-premium" 
                                         :class="{ 'selected': form.Naturaleza === 'ACREEDORA' }"
                                         @click="form.Naturaleza = 'ACREEDORA'; clearError('Naturaleza')">
                                        <div class="radio-content">
                                            <div class="radio-dot"></div>
                                            <div>
                                                <div class="radio-title-premium">Acreedora</div>
                                                <div class="radio-desc-premium">Pasivos, Capital, Ingresos</div>
                                            </div>
                                        </div>
                                        <div class="radio-check-premium" v-if="form.Naturaleza === 'ACREEDORA'">
                                            <svg class="check-icon-premium" fill="none" stroke="#10b981" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                                <div v-if="form.errors.Naturaleza" class="error-message-premium">
                                    {{ form.errors.Naturaleza }}
                                </div>
                            </div>

                            <!-- Configuraciones especiales -->
                            <div class="configuraciones-section">
                                <div class="configuraciones-header">
                                    <span class="configuraciones-label">Configuraciones especiales</span>
                                </div>
                                <div class="checkbox-group-horizontal">
                                    <!-- Cuenta de resultados -->
                                    <label class="checkbox-premium" 
                                           :class="{ 'checked': form.es_cuenta_resultados }"
                                           @click="toggleCuentaResultados">
                                        <div class="checkbox-custom-premium">
                                            <svg v-if="form.es_cuenta_resultados" class="checkbox-check-premium" fill="none" stroke="white" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                        <span class="checkbox-label-inline">Cuenta de resultados</span>
                                        <span class="checkbox-badge" v-if="form.es_cuenta_resultados">Nivel 2</span>
                                    </label>

                                    <!-- Cuenta fondeadora - OCULTA cuando es cuenta de resultados -->
                                    <label class="checkbox-premium" 
                                           v-if="!form.es_cuenta_resultados"
                                           :class="{ 'checked': form.fondeo_c }"
                                           @click="toggleFondeo">
                                        <div class="checkbox-custom-premium">
                                            <svg v-if="form.fondeo_c" class="checkbox-check-premium" fill="none" stroke="white" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                        <span class="checkbox-label-inline">Cuenta fondeadora</span>
                                        <span class="checkbox-badge" v-if="form.fondeo_c">Activa</span>
                                    </label>

                                    <!-- Estado - siempre visible -->
                                    <label class="checkbox-premium" 
                                           :class="{ 'checked': cuenta.en_uso }"
                                           style="cursor: default; opacity: 0.8;">
                                        <div class="checkbox-custom-premium" :style="{ background: cuenta.en_uso ? 'linear-gradient(135deg, #10b981, #059669)' : '#e5e7eb', borderColor: cuenta.en_uso ? '#10b981' : '#d1d5db' }">
                                            <svg v-if="cuenta.en_uso" class="checkbox-check-premium" fill="none" stroke="white" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                        <span class="checkbox-label-inline" :style="{ color: cuenta.en_uso ? '#10b981' : '#94a3b8' }">
                                            {{ cuenta.en_uso ? 'Activa' : 'Inactiva' }}
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <!-- Mensajes informativos -->
                            <div class="info-messages" v-if="form.fondeo_c || form.es_cuenta_resultados">
                                <div class="info-box-premium" v-if="form.fondeo_c && !form.es_cuenta_resultados" style="background: #f0fdf4; border-left-color: #059669;">
                                    <svg class="info-icon-premium" fill="none" stroke="#059669" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span style="color: #065f46;">
                                        <strong>Cuenta fondeadora</strong> — Esta cuenta proporciona fondos y no pertenece a una cuenta de resultados
                                    </span>
                                </div>

                                <div class="info-box-premium" v-if="form.es_cuenta_resultados" style="background: #f3e8ff; border-left-color: #8b5cf6;">
                                    <svg class="info-icon-premium" fill="none" stroke="#8b5cf6" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span style="color: #6d28d9;">
                                        <strong>Cuenta de resultados</strong> — Estas cuentas siempre son de nivel 2 y no tienen cuenta madre ni fondeadora
                                    </span>
                                </div>
                            </div>

                            <!-- ============================================ -->
                            <!-- JERARQUÍA Y CUENTA RESULTADOS PADRE -->
                            <!-- OCULTO CUANDO ES CUENTA DE RESULTADOS -->
                            <!-- ============================================ -->
                            <div class="jerarquia-resultados-row" v-if="!form.es_cuenta_resultados">
                                <!-- Cuenta Madre -->
                                <div class="jerarquia-section" v-if="form.nivel > 1 && form.id_empresa">
                                    <div class="jerarquia-header">
                                        <svg class="jerarquia-icon" fill="none" stroke="#667eea" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1h-2z"/>
                                        </svg>
                                        <span class="jerarquia-label">Cuenta Madre <span class="required-star">*</span></span>
                                        <span class="jerarquia-badge">Nivel {{ form.nivel - 1 }}</span>
                                    </div>
                                    <div class="input-wrapper-premium input-with-icon">
                                        <svg class="input-icon-premium" fill="none" stroke="#94a3b8" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1h-2z"/>
                                        </svg>
                                        <select v-model="form.id_cuenta_madre" 
                                                @change="clearError('id_cuenta_madre')"
                                                class="form-input-premium"
                                                :class="{ 'error': form.errors.id_cuenta_madre }"
                                                :disabled="cargandoCuentasMadre">
                                            <option value="">-- Selecciona una cuenta madre --</option>
                                            <option v-for="cuenta in cuentasMadre" :key="cuenta.id_cuenta" :value="cuenta.id_cuenta">
                                                {{ cuenta.display }}
                                            </option>
                                        </select>
                                    </div>
                                    <div v-if="form.errors.id_cuenta_madre" class="error-message-premium">
                                        {{ form.errors.id_cuenta_madre }}
                                    </div>
                                </div>

                                <!-- Cuenta de Resultados Padre - OCULTA cuando es fondeadora -->
                                <div class="resultados-padre-section" v-if="!form.fondeo_c">
                                    <div class="resultados-padre-header">
                                        <svg class="resultados-padre-icon" fill="none" stroke="#8b5cf6" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                                        </svg>
                                        <span class="resultados-padre-label">Cuenta de Resultados Padre <span class="required-star">*</span></span>
                                        <span class="resultados-padre-badge">Obligatorio</span>
                                    </div>
                                    <div class="input-wrapper-premium input-with-icon">
                                        <svg class="input-icon-premium" fill="none" stroke="#94a3b8" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                                        </svg>
                                        <select v-model="form.cuenta_resultados" 
                                                @change="clearError('cuenta_resultados')"
                                                class="form-input-premium"
                                                :class="{ 'error': form.errors.cuenta_resultados }">
                                            <option value="">-- Selecciona una cuenta de resultados --</option>
                                            <option v-for="cuenta in cuentasResultados" 
                                                    :key="cuenta.id_cuenta" 
                                                    :value="cuenta.id_cuenta">
                                                {{ cuenta.nombre_cuenta }}
                                            </option>
                                        </select>
                                    </div>
                                    <div v-if="form.errors.cuenta_resultados" class="error-message-premium">
                                        {{ form.errors.cuenta_resultados }}
                                    </div>
                                </div>

                                <!-- Mensaje para cuenta fondeadora -->
                                <div class="resultados-padre-section" v-if="form.fondeo_c">
                                    <div class="info-box-premium" style="background: #f0fdf4; border-left-color: #059669;">
                                        <svg class="info-icon-premium" fill="none" stroke="#059669" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <span style="color: #065f46;">
                                            <strong>Cuenta fondeadora</strong> — No requiere cuenta de resultados padre
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Resumen Jerarquía - OCULTO cuando es cuenta de resultados -->
                            <div class="jerarquia-resumen-premium" v-if="!form.es_cuenta_resultados">
                                <div class="jerarquia-resumen-title">
                                    <span class="jerarquia-resumen-title-text">Ruta jerárquica</span>
                                    <span class="jerarquia-resumen-status" :style="{ color: cuenta.en_uso ? '#10b981' : '#ef4444' }">
                                        ● {{ cuenta.en_uso ? 'Activa' : 'Inactiva' }}
                                    </span>
                                </div>
                                <div class="jerarquia-ruta">
                                    <div class="jerarquia-nodo nodo-empresa">
                                        <span class="nodo-badge">N0</span>
                                        <span class="nodo-nombre">{{ empresaSeleccionada?.nombre_empresa || 'Empresa' }}</span>
                                    </div>

                                    <span class="jerarquia-flecha">→</span>

                                    <div v-if="cuentaMadreSeleccionada" class="jerarquia-nodo nodo-madre">
                                        <span class="nodo-badge" style="background: #667eea;">N{{ cuentaMadreSeleccionada.nivel }}</span>
                                        <span class="nodo-codigo">{{ cuentaMadreSeleccionada.codigo_cuenta }}</span>
                                        <span class="nodo-nombre">{{ cuentaMadreSeleccionada.nombre_cuenta }}</span>
                                    </div>
                                    <span v-if="cuentaMadreSeleccionada" class="jerarquia-flecha">→</span>
                                    <div v-else-if="form.nivel > 1" class="jerarquia-nodo nodo-pendiente">
                                        <span class="nodo-badge" style="background: #f59e0b;">N{{ form.nivel - 1 }}</span>
                                        <span class="nodo-nombre" style="color: #f59e0b;">Pendiente</span>
                                    </div>

                                    <div class="jerarquia-nodo nodo-actual">
                                        <span class="nodo-badge" style="background: #10b981;">N{{ form.nivel }}</span>
                                        <span class="nodo-codigo">{{ form.codigo_cuenta || '???' }}</span>
                                        <span class="nodo-nombre">{{ form.nombre_cuenta || 'Sin nombre' }}</span>
                                        <span class="nodo-editar">EDITANDO</span>
                                    </div>
                                </div>

                                <div class="jerarquia-detalles">
                                    <div class="jerarquia-detalle" v-if="form.cuenta_resultados && !form.es_cuenta_resultados && !form.fondeo_c">
                                        <span class="detalle-label">Resultados:</span>
                                        <span class="detalle-valor" style="color: #8b5cf6; font-weight: 600;">
                                            {{ getCuentaResultadosNombre(form.cuenta_resultados) }}
                                        </span>
                                    </div>
                                    <div class="jerarquia-detalle" v-if="form.fondeo_c">
                                        <span class="detalle-label">Tipo:</span>
                                        <span class="detalle-valor" style="color: #059669; font-weight: 600;">Cuenta fondeadora</span>
                                    </div>
                                    <div class="jerarquia-detalle" v-if="form.es_cuenta_resultados">
                                        <span class="detalle-label">Tipo:</span>
                                        <span class="detalle-valor" style="color: #8b5cf6; font-weight: 600;">Cuenta de resultados</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botón Crear Cuenta Hija -->
                        <div class="crear-hija-container" v-if="cuenta.en_uso">
                            <div class="crear-hija-content">
                                <div class="crear-hija-info">
                                    <svg class="crear-hija-icon" fill="none" stroke="#059669" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                                    </svg>
                                    <div>
                                        <span class="crear-hija-titulo">Crear cuenta hija</span>
                                        <span class="crear-hija-desc">Nueva cuenta de nivel <strong>Nivel {{ form.nivel + 1 }}</strong> bajo esta cuenta</span>
                                    </div>
                                </div>
                                <button type="button" class="btn-crear-hija-premium" @click="abrirModalCrearHija">
                                    <svg class="btn-icon-premium" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                    <span>Crear</span>
                                </button>
                            </div>
                        </div>

                        <!-- ============================================ -->
                        <!-- SECCIÓN 4: DESCRIPCIÓN -->
                        <!-- ============================================ -->
                        <div class="form-section-premium compact">
                            <div class="section-header-premium">
                                <div class="section-icon-premium" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">
                                    <svg class="icon-svg-premium" fill="none" stroke="white" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="section-title-premium">Descripción</h3>
                                    <p class="section-subtitle-premium">Notas adicionales sobre la cuenta</p>
                                </div>
                            </div>

                            <div class="form-grid-premium">
                                <div class="form-group-premium full-width">
                                    <div class="input-wrapper-premium">
                                        <textarea v-model="form.descripcion"
                                                  @input="clearError('descripcion')"
                                                  class="form-textarea-premium"
                                                  :class="{ 'error': form.errors.descripcion }"
                                                  rows="3"
                                                  placeholder="Describe el propósito y uso de esta cuenta contable..."></textarea>
                                    </div>
                                    <div v-if="form.errors.descripcion" class="error-message-premium">
                                        {{ form.errors.descripcion }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Info y acciones -->
                        <div class="info-box-premium">
                            <svg class="info-icon-premium" fill="none" stroke="#667eea" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Los campos con <strong class="text-danger">*</strong> son obligatorios</span>
                            <span v-if="!form.es_cuenta_resultados && !form.fondeo_c" style="margin-left: auto; color: #8b5cf6; font-weight: 600;">
                                ● Debes seleccionar una cuenta de resultados padre
                            </span>
                        </div>

                        <div class="form-actions-premium">
                            <Link :href="route('cuentas.index', { empresa_id: form.id_empresa })" class="btn-cancel-premium">
                                <svg class="btn-icon-premium" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Cancelar
                            </Link>
                            <button type="submit" 
                                    :disabled="form.processing || !isFormValid"
                                    class="btn-submit-premium">
                                <span v-if="form.processing" class="spinner-border spinner-border-sm me-2" role="status"></span>
                                <svg v-else class="btn-icon-premium" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                                </svg>
                                {{ form.processing ? 'Guardando...' : 'Actualizar Cuenta' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ============================================ -->
        <!-- 🔥 MODAL CREAR CUENTA HIJA - MODIFICADO -->
        <!-- OPCIONES VISIBLES PERO DESACTIVADAS -->
        <!-- ============================================ -->
        <div v-if="modalHijaVisible" class="modal-overlay-premium" @click.self="cerrarModalHija">
            <div class="modal-container-premium modal-hija">
                <div class="modal-header-premium">
                    <div class="modal-header-info">
                        <div class="modal-icon-wrapper" style="background: linear-gradient(135deg, #10b981, #059669);">
                            <svg class="modal-icon-premium" fill="none" stroke="white" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="modal-title-premium">Crear Cuenta Hija</h3>
                            <p class="modal-subtitle-premium">
                                Nueva cuenta de nivel <strong>Nivel {{ form.nivel + 1 }}</strong> 
                                bajo "{{ form.nombre_cuenta || 'Sin nombre' }}"
                            </p>
                        </div>
                    </div>
                    <button type="button" @click="cerrarModalHija" class="modal-close-premium" :disabled="guardandoHija">
                        <svg class="icon-svg-sm-premium" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="modal-body-premium">
                    <form @submit.prevent="guardarCuentaHija" id="formHija">
                        <div class="form-grid-modal">
                            <!-- Código -->
                            <div class="form-group-premium">
                                <label class="form-label-premium">
                                    Código <span class="required-star">*</span>
                                    <span v-if="verificandoCodigoHija" class="label-badge-premium" style="background: #f59e0b;">Verificando...</span>
                                    <span v-else-if="codigoExisteHija && formHija.codigo_cuenta" class="label-badge-premium" style="background: #ef4444;">Ocupado</span>
                                    <span v-else-if="!codigoExisteHija && formHija.codigo_cuenta && formHija.codigo_cuenta.length > 0" class="label-badge-premium" style="background: #10b981;">Disponible</span>
                                </label>
                                <div class="input-wrapper-premium input-with-icon">
                                    <svg class="input-icon-premium" fill="none" stroke="#94a3b8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                                    </svg>
                                    <input type="text" v-model="formHija.codigo_cuenta"
                                           @input="clearErrorHija('codigo_cuenta'); verificarCodigoHija()"
                                           class="form-input-premium"
                                           :class="{ 'error': formHija.errors.codigo_cuenta || codigoExisteHija, 'modal-disabled': guardandoHija }"
                                           :disabled="guardandoHija"
                                           placeholder="Ej: AC001-01">
                                </div>
                                <div v-if="formHija.errors.codigo_cuenta" class="error-message-premium">
                                    {{ formHija.errors.codigo_cuenta }}
                                </div>
                            </div>

                            <!-- Nombre -->
                            <div class="form-group-premium">
                                <label class="form-label-premium">
                                    Nombre <span class="required-star">*</span>
                                </label>
                                <div class="input-wrapper-premium input-with-icon">
                                    <svg class="input-icon-premium" fill="none" stroke="#94a3b8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                    <input type="text" v-model="formHija.nombre_cuenta"
                                           @input="clearErrorHija('nombre_cuenta')"
                                           class="form-input-premium"
                                           :class="{ 'error': formHija.errors.nombre_cuenta, 'modal-disabled': guardandoHija }"
                                           :disabled="guardandoHija"
                                           placeholder="Ej: Bancos Nacionales">
                                </div>
                                <div v-if="formHija.errors.nombre_cuenta" class="error-message-premium">
                                    {{ formHija.errors.nombre_cuenta }}
                                </div>
                            </div>

                            <!-- Naturaleza -->
                            <div class="form-group-premium">
                                <label class="form-label-premium">
                                    Naturaleza <span class="required-star">*</span>
                                </label>
                                <div class="radio-group-modal">
                                    <div class="radio-card-modal" 
                                         :class="{ 'selected': formHija.Naturaleza === 'DEUDORA', 'modal-disabled': guardandoHija }"
                                         @click="!guardandoHija && (formHija.Naturaleza = 'DEUDORA'); clearErrorHija('Naturaleza')">
                                        <span class="radio-label-modal">Deudora</span>
                                    </div>
                                    <div class="radio-card-modal" 
                                         :class="{ 'selected': formHija.Naturaleza === 'ACREEDORA', 'modal-disabled': guardandoHija }"
                                         @click="!guardandoHija && (formHija.Naturaleza = 'ACREEDORA'); clearErrorHija('Naturaleza')">
                                        <span class="radio-label-modal">Acreedora</span>
                                    </div>
                                </div>
                                <div v-if="formHija.errors.Naturaleza" class="error-message-premium">
                                    {{ formHija.errors.Naturaleza }}
                                </div>
                            </div>

                            <!-- ============================================ -->
                            <!-- 🔥 CHECKBOXES DEL MODAL - VISIBLES PERO DESACTIVADOS -->
                            <!-- ============================================ -->
                            <div class="form-group-premium full-width-modal">
                                <div class="checkbox-group-inline-modal">
                                    <!-- Cuenta de resultados -->
                                    <label class="checkbox-modal" 
                                           :class="{ 
                                               'checked': formHija.es_cuenta_resultados, 
                                               'modal-disabled': guardandoHija 
                                           }"
                                           @click="!guardandoHija && toggleHijaCuentaResultados()">
                                        <div class="checkbox-custom-modal" :class="{ 'modal-disabled': guardandoHija }">
                                            <svg v-if="formHija.es_cuenta_resultados" class="checkbox-check-premium" fill="none" stroke="white" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                        <span class="checkbox-label-modal" :class="{ 'modal-disabled': guardandoHija }">Cuenta de resultados</span>
                                    </label>

                                    <!-- Cuenta fondeadora -->
                                    <label class="checkbox-modal" 
                                           :class="{ 
                                               'checked': formHija.fondeo_c, 
                                               'modal-disabled': guardandoHija 
                                           }"
                                           @click="!guardandoHija && toggleHijaFondeo()">
                                        <div class="checkbox-custom-modal" :class="{ 'modal-disabled': guardandoHija }">
                                            <svg v-if="formHija.fondeo_c" class="checkbox-check-premium" fill="none" stroke="white" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                        <span class="checkbox-label-modal" :class="{ 'modal-disabled': guardandoHija }">Cuenta fondeadora</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Cuenta de Resultados Padre - OCULTA cuando es cuenta de resultados o fondeadora -->
                            <div class="form-group-premium full-width-modal" 
                                 v-if="!formHija.es_cuenta_resultados && !formHija.fondeo_c">
                                <label class="form-label-premium">
                                    Cuenta de Resultados Padre <span class="required-star">*</span>
                                </label>
                                <div class="input-wrapper-premium input-with-icon">
                                    <svg class="input-icon-premium" fill="none" stroke="#94a3b8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                                    </svg>
                                    <select v-model="formHija.cuenta_resultados" 
                                            @change="clearErrorHija('cuenta_resultados')"
                                            class="form-input-premium"
                                            :class="{ 'error': formHija.errors.cuenta_resultados, 'modal-disabled': guardandoHija }"
                                            :disabled="guardandoHija">
                                        <option value="">-- Selecciona una cuenta de resultados --</option>
                                        <option v-for="cuenta in cuentasResultados" 
                                                :key="cuenta.id_cuenta" 
                                                :value="cuenta.id_cuenta">
                                            {{ cuenta.nombre_cuenta }}
                                        </option>
                                    </select>
                                </div>
                                <div v-if="formHija.errors.cuenta_resultados" class="error-message-premium">
                                    {{ formHija.errors.cuenta_resultados }}
                                </div>
                            </div>

                            <!-- Mensaje informativo para cuentas fondeadoras -->
                            <div class="form-group-premium full-width-modal" 
                                 v-if="formHija.fondeo_c && !formHija.es_cuenta_resultados">
                                <div class="info-box-premium" style="background: #f0fdf4; border-left-color: #059669;">
                                    <svg class="info-icon-premium" fill="none" stroke="#059669" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span style="color: #065f46;">
                                        <strong>Cuenta fondeadora</strong>
                                    </span>
                                </div>
                            </div>

                            <!-- Mensaje informativo para cuenta de resultados -->
                            <div class="form-group-premium full-width-modal" 
                                 v-if="formHija.es_cuenta_resultados">
                                <div class="info-box-premium" style="background: #f3e8ff; border-left-color: #8b5cf6;">
                                    <svg class="info-icon-premium" fill="none" stroke="#8b5cf6" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span style="color: #6d28d9;">
                                        <strong>Cuenta de resultados</strong>
                                    </span>
                                </div>
                            </div>

                            <!-- Descripción -->
                            <div class="form-group-premium full-width-modal">
                                <label class="form-label-premium">Descripción</label>
                                <div class="input-wrapper-premium">
                                    <textarea v-model="formHija.descripcion"
                                              class="form-textarea-premium"
                                              :class="{ 'modal-disabled': guardandoHija }"
                                              :disabled="guardandoHija"
                                              rows="2"
                                              placeholder="Breve descripción de la cuenta hija..."></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="modal-actions-premium">
                            <button type="button" 
                                    @click="cerrarModalHija" 
                                    class="btn-cancel-premium"
                                    :disabled="guardandoHija">
                                Cancelar
                            </button>
                            <button type="submit" 
                                    :disabled="guardandoHija || !isFormHijaValid"
                                    class="btn-submit-premium">
                                <span v-if="guardandoHija" class="spinner-border spinner-border-sm me-2" role="status"></span>
                                <svg v-else class="btn-icon-premium" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                                </svg>
                                {{ guardandoHija ? 'Guardando...' : 'Crear Hija' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <AlertModal ref="alertRef" />
    </AppLayout>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import AlertModal from '@/Components/AlertModal.vue';
import axios from 'axios';
import Swal from 'sweetalert2';

// ============================================
// PROPS
// ============================================
const props = defineProps({
    cuenta: {
        type: Object,
        required: true
    },
    empresas: {
        type: Array,
        default: () => []
    },
    cuentasMadre: {
        type: Array,
        default: () => []
    },
    cuentasResultados: {
        type: Array,
        default: () => []
    }
});

// ============================================
// REFS
// ============================================
const alertRef = ref(null);
const cuentasMadre = ref([]);
const cuentasResultados = ref([]);
const cargandoCuentasMadre = ref(false);
const verificandoCodigo = ref(false);
const codigoExiste = ref(false);
const codigoOriginal = ref('');
let timeoutVerificar = null;

// Modal Hija
const modalHijaVisible = ref(false);
const guardandoHija = ref(false);
const verificandoCodigoHija = ref(false);
const codigoExisteHija = ref(false);
let timeoutVerificarHija = null;

// ============================================
// FORMULARIO PRINCIPAL
// ============================================
const form = useForm({
    id_empresa: props.cuenta.id_empresa || '',
    codigo_cuenta: props.cuenta.codigo_cuenta || '',
    nombre_cuenta: props.cuenta.nombre_cuenta || '',
    descripcion: props.cuenta.descripcion || '',
    Naturaleza: props.cuenta.Naturaleza || '',
    id_cuenta_madre: props.cuenta.id_cuenta_madre || null,
    es_cuenta_resultados: props.cuenta.es_cuenta_resultados || false,
    fondeo_c: props.cuenta.fondeo_c || false,
    nivel: props.cuenta.nivel || '',
    cuenta_resultados: props.cuenta.cuenta_resultados || null,
});

// ============================================
// FORMULARIO HIJA
// ============================================
const formHija = useForm({
    id_empresa: form.id_empresa,
    codigo_cuenta: '',
    nombre_cuenta: '',
    descripcion: '',
    Naturaleza: '',
    id_cuenta_madre: props.cuenta.id_cuenta,
    es_cuenta_resultados: false,
    fondeo_c: false,
    nivel: (parseInt(form.nivel) || 0) + 1,
    cuenta_resultados: null,
});

// ============================================
// COMPUTED
// ============================================
const empresaSeleccionada = computed(() => {
    return props.empresas.find(e => e.id === parseInt(form.id_empresa));
});

const cuentaMadreSeleccionada = computed(() => {
    if (!form.id_cuenta_madre) return null;
    return cuentasMadre.value.find(c => c.id_cuenta === form.id_cuenta_madre);
});

const isFormValid = computed(() => {
    if (!form.id_empresa || form.id_empresa.toString().trim() === '') return false;
    if (!form.codigo_cuenta || form.codigo_cuenta.trim() === '') return false;
    if (!form.nombre_cuenta || form.nombre_cuenta.trim() === '') return false;
    if (!form.Naturaleza || form.Naturaleza.trim() === '') return false;
    if (!form.nivel || form.nivel.toString().trim() === '') return false;
    
    if (form.es_cuenta_resultados) {
        if (parseInt(form.nivel) !== 2) return false;
        if (form.id_cuenta_madre) return false;
        if (form.cuenta_resultados) return false;
        if (form.fondeo_c) return false;
    } else {
        if (form.nivel > 1) {
            if (!form.id_cuenta_madre || form.id_cuenta_madre.toString().trim() === '') return false;
        }
        if (!form.fondeo_c) {
            if (!form.cuenta_resultados || form.cuenta_resultados.toString().trim() === '') return false;
        }
    }
    
    if (codigoExiste.value && form.codigo_cuenta !== codigoOriginal.value) return false;
    return true;
});

const isFormHijaValid = computed(() => {
    if (!formHija.id_empresa || formHija.id_empresa.toString().trim() === '') return false;
    if (!formHija.codigo_cuenta || formHija.codigo_cuenta.trim() === '') return false;
    if (!formHija.nombre_cuenta || formHija.nombre_cuenta.trim() === '') return false;
    if (!formHija.Naturaleza || formHija.Naturaleza.trim() === '') return false;
    if (!formHija.id_cuenta_madre || formHija.id_cuenta_madre.toString().trim() === '') return false;
    if (!formHija.nivel || formHija.nivel.toString().trim() === '') return false;
    
    if (formHija.es_cuenta_resultados) {
        if (formHija.cuenta_resultados) return false;
        if (formHija.fondeo_c) return false;
    } else {
        if (!formHija.fondeo_c) {
            if (!formHija.cuenta_resultados || formHija.cuenta_resultados.toString().trim() === '') return false;
        }
    }
    
    if (codigoExisteHija.value) return false;
    return true;
});

const hasErrors = computed(() => {
    return Object.keys(form.errors).length > 0 || (codigoExiste.value && form.codigo_cuenta !== codigoOriginal.value);
});

const errorCount = computed(() => {
    let count = Object.keys(form.errors).length;
    if (codigoExiste.value && form.codigo_cuenta !== codigoOriginal.value) count++;
    return count;
});

const isComplete = computed(() => {
    return !hasErrors.value && isFormValid.value;
});

const progressPercentage = computed(() => {
    const fields = ['id_empresa', 'codigo_cuenta', 'nombre_cuenta', 'Naturaleza', 'nivel'];
    
    if (!form.es_cuenta_resultados) {
        if (form.nivel > 1) fields.push('id_cuenta_madre');
        if (!form.fondeo_c) fields.push('cuenta_resultados');
    }
    
    const total = fields.length;
    const filled = fields.filter(f => {
        const val = form[f];
        return val && val.toString().trim().length > 0;
    }).length;
    return total > 0 ? (filled / total) * 100 : 0;
});

const statusClass = computed(() => {
    if (hasErrors.value) return 'status-error';
    if (isComplete.value) return 'status-success';
    return 'status-progress';
});

// ============================================
// FUNCIONES
// ============================================
const clearError = (field) => {
    if (form.errors[field]) delete form.errors[field];
};

const clearErrorHija = (field) => {
    if (formHija.errors[field]) delete formHija.errors[field];
};

// ============================================
// TOGGLES
// ============================================
const toggleCuentaResultados = () => {
    form.es_cuenta_resultados = !form.es_cuenta_resultados;
    
    if (form.es_cuenta_resultados) {
        form.nivel = 2;
        form.id_cuenta_madre = null;
        form.fondeo_c = false;
        form.cuenta_resultados = null;
        cuentasMadre.value = [];
    } else {
        if (props.cuenta.nivel) {
            form.nivel = props.cuenta.nivel;
        } else {
            form.nivel = 1;
        }
        if (form.nivel > 1 && form.id_empresa) {
            cargarCuentasMadre();
        }
    }
    
    clearError('nivel');
    clearError('id_cuenta_madre');
    clearError('cuenta_resultados');
};

const toggleFondeo = () => {
    form.fondeo_c = !form.fondeo_c;
    if (form.fondeo_c) {
        form.es_cuenta_resultados = false;
        form.cuenta_resultados = null;
    }
};

const toggleHijaCuentaResultados = () => {
    formHija.es_cuenta_resultados = !formHija.es_cuenta_resultados;
    if (formHija.es_cuenta_resultados) {
        formHija.fondeo_c = false;
        formHija.cuenta_resultados = null;
        formHija.nivel = 2;
    } else {
        const nivelMadre = parseInt(form.nivel);
        formHija.nivel = nivelMadre + 1;
    }
};

const toggleHijaFondeo = () => {
    formHija.fondeo_c = !formHija.fondeo_c;
    if (formHija.fondeo_c) {
        formHija.es_cuenta_resultados = false;
        formHija.cuenta_resultados = null;
    }
};

// ============================================
// VERIFICAR CÓDIGO
// ============================================
const verificarCodigo = async () => {
    const codigo = form.codigo_cuenta?.trim();
    const empresaId = form.id_empresa;
    
    if (codigo === codigoOriginal.value) {
        codigoExiste.value = false;
        return;
    }
    
    if (!codigo || codigo.length < 2 || !empresaId) {
        codigoExiste.value = false;
        return;
    }

    clearTimeout(timeoutVerificar);
    verificandoCodigo.value = true;
    
    timeoutVerificar = setTimeout(async () => {
        try {
            const response = await axios.get('/cuentas/verificar-codigo', {
                params: {
                    empresa_id: empresaId,
                    codigo: codigo
                }
            });
            
            codigoExiste.value = response.data.exists;
            
            if (codigoExiste.value) {
                form.errors.codigo_cuenta = 'El código ya está en uso';
            } else {
                if (form.errors.codigo_cuenta) {
                    delete form.errors.codigo_cuenta;
                }
            }
        } catch (error) {
            console.error('Error al verificar código:', error);
        } finally {
            verificandoCodigo.value = false;
        }
    }, 500);
};

const verificarCodigoHija = async () => {
    const codigo = formHija.codigo_cuenta?.trim();
    const empresaId = formHija.id_empresa;
    
    if (!codigo || codigo.length < 2 || !empresaId) {
        codigoExisteHija.value = false;
        return;
    }

    clearTimeout(timeoutVerificarHija);
    verificandoCodigoHija.value = true;
    
    timeoutVerificarHija = setTimeout(async () => {
        try {
            const response = await axios.get('/cuentas/verificar-codigo', {
                params: {
                    empresa_id: empresaId,
                    codigo: codigo
                }
            });
            
            codigoExisteHija.value = response.data.exists;
            
            if (codigoExisteHija.value) {
                formHija.errors.codigo_cuenta = 'El código ya está en uso';
            } else {
                if (formHija.errors.codigo_cuenta) {
                    delete formHija.errors.codigo_cuenta;
                }
            }
        } catch (error) {
            console.error('Error al verificar código hija:', error);
        } finally {
            verificandoCodigoHija.value = false;
        }
    }, 500);
};

// ============================================
// CARGAR CUENTAS
// ============================================
const cargarCuentasMadre = async () => {
    if (!form.id_empresa || !form.nivel) {
        cuentasMadre.value = [];
        return;
    }

    if (form.nivel == 1 || form.es_cuenta_resultados) {
        cuentasMadre.value = [];
        return;
    }

    cargandoCuentasMadre.value = true;
    
    try {
        const response = await axios.get(route('cuentas.get-cuentas-madre'), {
            params: {
                empresa_id: form.id_empresa,
                nivel: form.nivel
            }
        });
        
        if (response.data.success) {
            cuentasMadre.value = response.data.data;
            
            if (form.id_cuenta_madre) {
                const existe = cuentasMadre.value.some(c => c.id_cuenta === form.id_cuenta_madre);
                if (!existe) {
                    form.id_cuenta_madre = null;
                }
            }
        } else {
            cuentasMadre.value = [];
        }
    } catch (error) {
        console.error('Error al cargar cuentas madre:', error);
        cuentasMadre.value = [];
    } finally {
        cargandoCuentasMadre.value = false;
    }
};

const cargarCuentasResultados = async (empresaId = null) => {
    const id = empresaId || form.id_empresa;
    if (!id) {
        cuentasResultados.value = [];
        return;
    }

    try {
        const response = await axios.get('/cuentas/get-cuentas-resultados', {
            params: {
                empresa_id: id
            }
        });
        
        if (response.data.success) {
            cuentasResultados.value = response.data.data;
        } else {
            cuentasResultados.value = [];
        }
    } catch (error) {
        console.error('Error al cargar cuentas de resultados:', error);
        cuentasResultados.value = [];
    }
};

const getCuentaResultadosNombre = (id) => {
    if (!id) return '';
    const cuenta = cuentasResultados.value.find(c => c.id_cuenta === id);
    return cuenta ? cuenta.nombre_cuenta : '';
};

// ============================================
// MODAL HIJA
// ============================================
const abrirModalCrearHija = () => {
    formHija.id_empresa = form.id_empresa;
    formHija.id_cuenta_madre = props.cuenta.id_cuenta;
    const nivelMadre = parseInt(form.nivel);
    formHija.nivel = nivelMadre + 1;
    formHija.codigo_cuenta = '';
    formHija.nombre_cuenta = '';
    formHija.descripcion = '';
    formHija.Naturaleza = '';
    formHija.es_cuenta_resultados = false;
    formHija.fondeo_c = false;
    formHija.cuenta_resultados = null;
    formHija.clearErrors();
    codigoExisteHija.value = false;
    guardandoHija.value = false;
    
    if (formHija.id_empresa) {
        cargarCuentasResultados(formHija.id_empresa);
    }
    
    modalHijaVisible.value = true;
};

const cerrarModalHija = () => {
    if (guardandoHija.value) return;
    modalHijaVisible.value = false;
    formHija.clearErrors();
    codigoExisteHija.value = false;
};

// ============================================
// GUARDAR CUENTA HIJA
// ============================================
const guardarCuentaHija = () => {
    // Validar código
    if (codigoExisteHija.value) {
        alertRef.value?.show({
            type: 'error',
            title: 'Código no disponible',
            message: 'El código "' + formHija.codigo_cuenta + '" ya está en uso. Por favor, elige otro diferente.',
            buttonText: 'Entendido'
        });
        return;
    }

    // Validar campos obligatorios
    if (!isFormHijaValid.value) {
        let mensaje = 'Por favor, complete todos los campos obligatorios para crear la cuenta hija.';
        
        if (!formHija.es_cuenta_resultados && !formHija.fondeo_c && !formHija.cuenta_resultados) {
            mensaje = 'Las cuentas normales deben tener una cuenta de resultados padre asignada.';
        }
        
        alertRef.value?.show({
            type: 'error',
            title: 'Campos incompletos',
            message: mensaje,
            buttonText: 'Entendido'
        });
        return;
    }

    // Asegurar nivel correcto
    const nivelMadre = parseInt(form.nivel);
    const nivelHijaEsperado = nivelMadre + 1;
    
    if (formHija.es_cuenta_resultados) {
        formHija.nivel = 2;
    } else if (parseInt(formHija.nivel) !== nivelHijaEsperado) {
        formHija.nivel = nivelHijaEsperado;
    }

    // Verificar cuenta madre activa
    const cuentaMadre = props.cuenta;
    if (!cuentaMadre || !cuentaMadre.en_uso) {
        alertRef.value?.show({
            type: 'error',
            title: 'Cuenta madre inactiva',
            message: 'La cuenta madre no está activa. No se puede crear una cuenta hija.',
            buttonText: 'Entendido'
        });
        return;
    }

    if (formHija.es_cuenta_resultados || formHija.fondeo_c) {
        formHija.cuenta_resultados = null;
    }

    guardandoHija.value = true;

    formHija.post(route('cuentas.store'), {
        preserveScroll: true,
        onSuccess: () => {
            guardandoHija.value = false;
            cerrarModalHija();
            
            alertRef.value?.show({
                type: 'success',
                title: '¡Cuenta hija creada!',
                message: 'La cuenta hija se ha registrado exitosamente bajo "' + form.nombre_cuenta + '".',
                buttonText: 'Ir al listado'
            });
            
            setTimeout(() => {
                router.visit(route('cuentas.index', { empresa_id: form.id_empresa }));
            }, 1500);
        },
        onError: (errors) => {
            guardandoHija.value = false;
            
            if (errors.codigo_cuenta) {
                codigoExisteHija.value = true;
            }
            const errorMsg = Object.values(errors).join('<br>');
            alertRef.value?.show({
                type: 'error',
                title: 'Error al crear',
                message: errorMsg || 'Ocurrió un error al crear la cuenta hija.',
                buttonText: 'Intentar de nuevo'
            });
        }
    });
};

// ============================================
// ELIMINAR CUENTA
// ============================================
const confirmarEliminar = () => {
    Swal.fire({
        title: '¿Desactivar cuenta?',
        html: `
            <div class="text-center">
                <div class="flex justify-center mb-3">
                    <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </div>
                </div>
                <p class="font-medium text-gray-800 text-lg">${form.nombre_cuenta}</p>
                <p class="text-sm text-gray-500">Código: <strong>${form.codigo_cuenta}</strong></p>
                <p class="text-sm text-gray-500">Nivel: <strong>${form.nivel}</strong></p>
                <div class="mt-4 p-3 bg-amber-50 rounded-lg border border-amber-200">
                    <p class="text-sm text-amber-700 font-medium">La cuenta quedará <strong>inactiva</strong></p>
                    <p class="text-xs text-amber-600 mt-1">Podrás reactivarla más tarde desde el listado de cuentas</p>
                </div>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, desactivar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        reverseButtons: true,
        allowOutsideClick: false,
        allowEscapeKey: false,
        customClass: {
            popup: 'swal-premium-popup',
            confirmButton: 'swal-premium-confirm',
            cancelButton: 'swal-premium-cancel'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('cuentas.destroy', props.cuenta.id_cuenta), {
                preserveScroll: true,
                onSuccess: () => {
                    Swal.fire({
                        title: '¡Desactivada!',
                        html: `
                            <div class="text-center">
                                <div class="flex justify-center mb-3">
                                    <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center">
                                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                </div>
                                <p class="text-gray-700">Cuenta <strong>${form.nombre_cuenta}</strong></p>
                                <p class="text-sm text-green-600 mt-1">Desactivada correctamente</p>
                            </div>
                        `,
                        icon: 'success',
                        confirmButtonText: 'Aceptar',
                        confirmButtonColor: '#1a3a5c',
                        timer: 3000,
                        timerProgressBar: true
                    }).then(() => {
                        router.visit(route('cuentas.index', { empresa_id: form.id_empresa }));
                    });
                },
                onError: (errors) => {
                    const errorMsg = errors?.error || 'Ocurrió un error al desactivar la cuenta';
                    Swal.fire({
                        title: 'Error',
                        text: errorMsg,
                        icon: 'error',
                        confirmButtonText: 'Entendido',
                        confirmButtonColor: '#ef4444'
                    });
                }
            });
        }
    });
};

// ============================================
// REACTIVAR CUENTA
// ============================================
const confirmarReactivar = () => {
    Swal.fire({
        title: '¿Reactivar cuenta?',
        html: `
            <div class="text-center">
                <div class="flex justify-center mb-3">
                    <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </div>
                </div>
                <p class="font-medium text-gray-800 text-lg">${form.nombre_cuenta}</p>
                <p class="text-sm text-gray-500">Código: <strong>${form.codigo_cuenta}</strong></p>
                <div class="mt-4 p-3 bg-green-50 rounded-lg border border-green-200">
                    <p class="text-sm text-green-700 font-medium">La cuenta volverá a estar <strong>activa</strong></p>
                </div>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, reactivar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#64748b',
        reverseButtons: true,
        allowOutsideClick: false,
        allowEscapeKey: false,
        customClass: {
            popup: 'swal-premium-popup',
            confirmButton: 'swal-premium-confirm',
            cancelButton: 'swal-premium-cancel'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            router.patch(route('cuentas.reactivate', props.cuenta.id_cuenta), {}, {
                preserveScroll: true,
                onSuccess: () => {
                    Swal.fire({
                        title: '¡Reactivada!',
                        html: `
                            <div class="text-center">
                                <div class="flex justify-center mb-3">
                                    <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center">
                                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                </div>
                                <p class="text-gray-700">Cuenta <strong>${form.nombre_cuenta}</strong></p>
                                <p class="text-sm text-green-600 mt-1">Reactivada correctamente</p>
                            </div>
                        `,
                        icon: 'success',
                        confirmButtonText: 'Aceptar',
                        confirmButtonColor: '#1a3a5c',
                        timer: 3000,
                        timerProgressBar: true
                    }).then(() => {
                        router.visit(route('cuentas.index', { empresa_id: form.id_empresa }));
                    });
                },
                onError: (errors) => {
                    const errorMsg = errors?.error || 'Ocurrió un error al reactivar la cuenta';
                    Swal.fire({
                        title: 'Error',
                        text: errorMsg,
                        icon: 'error',
                        confirmButtonText: 'Entendido',
                        confirmButtonColor: '#ef4444'
                    });
                }
            });
        }
    });
};

// ============================================
// SUBMIT
// ============================================
const submit = () => {
    if (!props.cuenta.en_uso) {
        alertRef.value?.show({
            type: 'error',
            title: 'Cuenta inactiva',
            message: 'No se puede editar una cuenta inactiva. Reactívala primero.',
            buttonText: 'Entendido'
        });
        return;
    }

    if (codigoExiste.value && form.codigo_cuenta !== codigoOriginal.value) {
        alertRef.value?.show({
            type: 'error',
            title: 'Código no disponible',
            message: 'El código "' + form.codigo_cuenta + '" ya está en uso. Por favor, elige otro diferente.',
            buttonText: 'Entendido'
        });
        return;
    }

    if (!isFormValid.value) {
        let mensaje = 'Por favor, complete todos los campos obligatorios.';
        
        if (form.es_cuenta_resultados) {
            if (parseInt(form.nivel) !== 2) {
                mensaje = 'Las cuentas de resultados deben ser de Nivel 2.';
            } else if (form.id_cuenta_madre) {
                mensaje = 'Las cuentas de resultados no deben tener cuenta madre.';
            } else if (form.fondeo_c) {
                mensaje = 'Las cuentas de resultados no pueden ser fondeadoras.';
            }
        } else if (!form.fondeo_c && !form.cuenta_resultados) {
            mensaje = 'Las cuentas normales deben tener una cuenta de resultados padre asignada.';
        }
        
        alertRef.value?.show({
            type: 'error',
            title: 'Campos incompletos',
            message: mensaje,
            buttonText: 'Entendido'
        });
        return;
    }

    if (form.es_cuenta_resultados) {
        form.nivel = 2;
        form.id_cuenta_madre = null;
        form.cuenta_resultados = null;
        form.fondeo_c = false;
    }

    form.put(route('cuentas.update', props.cuenta.id_cuenta), {
        onSuccess: () => {
            alertRef.value?.show({
                type: 'success',
                title: '¡Cuenta actualizada!',
                message: 'Los datos de la cuenta se han actualizado exitosamente.',
                buttonText: 'Ir al listado'
            });
            setTimeout(() => {
                router.visit(route('cuentas.index', { empresa_id: form.id_empresa }));
            }, 1500);
        },
        onError: (errors) => {
            if (errors.codigo_cuenta) {
                codigoExiste.value = true;
                alertRef.value?.show({
                    type: 'error',
                    title: 'Código duplicado',
                    message: 'El código "' + form.codigo_cuenta + '" ya existe. Por favor, elige otro diferente.',
                    buttonText: 'Entendido'
                });
            } else {
                const errorMessages = Object.values(errors).join('<br>');
                alertRef.value?.show({
                    type: 'error',
                    title: 'Error al actualizar',
                    message: errorMessages || 'Ocurrió un error al actualizar la cuenta. Verifique los datos e intente nuevamente.',
                    buttonText: 'Intentar de nuevo'
                });
            }
        }
    });
};

// ============================================
// WATCHERS
// ============================================
watch(() => form.id_empresa, (newVal, oldVal) => {
    if (newVal !== oldVal && newVal) {
        if (!form.es_cuenta_resultados && form.nivel > 1) {
            form.id_cuenta_madre = null;
            cargarCuentasMadre();
        }
        cargarCuentasResultados();
    }
});

watch(() => form.nivel, (newVal, oldVal) => {
    if (newVal !== oldVal && newVal && !form.es_cuenta_resultados) {
        form.id_cuenta_madre = null;
        if (form.id_empresa) {
            cargarCuentasMadre();
        }
    }
});

// ============================================
// MOUNTED
// ============================================
onMounted(() => {
    codigoOriginal.value = props.cuenta.codigo_cuenta || '';
    cuentasMadre.value = props.cuentasMadre || [];
    cuentasResultados.value = props.cuentasResultados || [];
    
    if (form.es_cuenta_resultados) {
        form.nivel = 2;
        form.id_cuenta_madre = null;
        form.cuenta_resultados = null;
        form.fondeo_c = false;
    }
    
    if (!form.es_cuenta_resultados && form.nivel > 1 && form.id_empresa) {
        cargarCuentasMadre();
    }
    
    if (form.id_empresa) {
        cargarCuentasResultados();
    }
});
</script>

<style scoped>
/* HEADER */
.header-premium {
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    border-radius: 20px;
    padding: 20px 24px;
    margin-bottom: 8px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
    border: 1px solid #f0f2f5;
}

.header-content-premium {
    display: flex;
    flex-direction: column;
    gap: 12px;
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
    gap: 14px;
}

.header-icon-wrapper {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, #1a3a5c, #2c5282);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(26, 58, 92, 0.2);
}

.header-icon-svg {
    width: 24px;
    height: 24px;
    stroke: white;
}

.header-title-premium {
    font-size: 22px;
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
    font-size: 13px;
    margin: 2px 0 0 0;
}

.subtitle-line {
    width: 20px;
    height: 2px;
    background: linear-gradient(90deg, #1a3a5c, transparent);
    border-radius: 2px;
}

.header-actions-premium {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.btn-back-premium {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    background: #f1f5f9;
    color: #1e293b;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
    text-decoration: none;
    font-size: 13px;
}

.btn-back-premium:hover {
    background: #e2e8f0;
    transform: translateX(-3px);
}

.btn-delete-premium {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    background: #fef2f2;
    color: #dc2626;
    border: 2px solid #fecaca;
    border-radius: 8px;
    font-weight: 600;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-delete-premium:hover {
    background: #fee2e2;
    border-color: #f87171;
    transform: translateY(-1px);
}

.btn-reactivate {
    background: #ecfdf5;
    color: #059669;
    border-color: #a7f3d0;
}

.btn-reactivate:hover {
    background: #d1fae5;
    border-color: #6ee7b7;
}

.btn-icon-premium {
    width: 16px;
    height: 16px;
}

/* STATUS BANNER */
.status-banner-premium {
    background: #ffffff;
    border-radius: 12px;
    padding: 12px 16px;
    margin-bottom: 20px;
    border: 1px solid #f0f2f5;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    transition: all 0.3s ease;
}

.status-banner-premium.status-error {
    border-left: 4px solid #ef4444;
}

.status-banner-premium.status-success {
    border-left: 4px solid #10b981;
}

.status-banner-premium.status-progress {
    border-left: 4px solid #667eea;
}

.status-banner-content {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.status-banner-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    flex-shrink: 0;
}

.status-icon-error,
.status-icon-success,
.status-icon-progress {
    width: 20px;
    height: 20px;
}

.status-banner-text {
    font-weight: 500;
    color: #0f172a;
    flex: 1;
    font-size: 14px;
}

.status-success .status-banner-text {
    color: #10b981;
}

.status-error .status-banner-text {
    color: #ef4444;
}

.status-banner-progress {
    flex: 1;
    height: 5px;
    background: #f1f5f9;
    border-radius: 4px;
    min-width: 100px;
    overflow: hidden;
}

.status-banner-progress-bar {
    height: 100%;
    border-radius: 4px;
    transition: width 0.6s ease;
}

.status-success .status-banner-progress-bar {
    background: linear-gradient(90deg, #10b981, #059669);
}

.status-progress .status-banner-progress-bar {
    background: linear-gradient(90deg, #667eea, #764ba2);
}

.status-error .status-banner-progress-bar {
    background: linear-gradient(90deg, #ef4444, #dc2626);
}

.status-banner-percent {
    font-size: 13px;
    font-weight: 700;
    color: #1a3a5c;
    min-width: 40px;
    text-align: right;
}

/* FORM CARD */
.form-card-premium {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid #f0f2f5;
    padding: 28px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

/* FORM SECTIONS */
.form-section-premium {
    margin-bottom: 24px;
    padding-bottom: 24px;
    border-bottom: 2px solid #f1f5f9;
}

.form-section-premium:last-of-type {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.form-section-premium.compact {
    margin-bottom: 16px;
    padding-bottom: 16px;
}

.section-header-premium {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 16px;
}

.section-icon-premium {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.icon-svg-premium {
    width: 20px;
    height: 20px;
}

.section-title-premium {
    font-size: 16px;
    font-weight: 700;
    color: #0f172a;
    margin: 0;
}

.section-subtitle-premium {
    font-size: 13px;
    color: #94a3b8;
    margin: 1px 0 0 0;
}

/* FORM GRID */
.form-grid-premium {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
}

.full-width {
    grid-column: 1 / -1;
}

/* FORM GROUP */
.form-group-premium {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.form-label-premium {
    font-size: 12px;
    font-weight: 600;
    color: #374151;
    display: flex;
    align-items: center;
    gap: 4px;
    flex-wrap: wrap;
}

.label-badge-premium {
    font-size: 9px;
    font-weight: 700;
    color: white;
    padding: 2px 8px;
    border-radius: 10px;
    margin-left: 2px;
}

.label-icon-wrapper {
    display: inline-flex;
    align-items: center;
}

.label-icon-premium {
    width: 14px;
    height: 14px;
    color: #94a3b8;
}

.required-star {
    color: #ef4444;
    font-weight: 700;
    margin-left: 1px;
}

.input-wrapper-premium {
    position: relative;
}

.input-with-icon {
    position: relative;
}

.input-icon-premium {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    width: 16px;
    height: 16px;
    z-index: 1;
    pointer-events: none;
}

.input-with-icon .form-input-premium {
    padding-left: 36px;
}

.form-input-premium {
    width: 100%;
    padding: 8px 12px;
    font-size: 13px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    background: white;
    color: #1f2937;
    transition: all 0.3s ease;
    outline: none;
    height: 38px;
}

.form-input-premium:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.08);
}

.form-input-premium:hover:not(:focus) {
    border-color: #9ca3af;
}

.form-input-premium.error {
    border-color: #ef4444;
}

.form-input-premium:disabled {
    background: #f1f5f9;
    cursor: not-allowed;
    opacity: 0.7;
}

.form-textarea-premium {
    width: 100%;
    padding: 8px 12px;
    font-size: 13px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    background: white;
    color: #1f2937;
    transition: all 0.3s ease;
    outline: none;
    resize: vertical;
    min-height: 60px;
    font-family: inherit;
}

.form-textarea-premium:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.08);
}

/* ERROR MESSAGES */
.error-message-premium {
    font-size: 11px;
    color: #ef4444;
    display: flex;
    align-items: center;
    gap: 4px;
    margin-top: 2px;
}

.error-icon-premium {
    width: 14px;
    height: 14px;
    flex-shrink: 0;
}

/* ============================================ */
/* CLASIFICACIÓN - DISEÑO MEJORADO */
/* ============================================ */
.naturaleza-section {
    margin-bottom: 16px;
}

.naturaleza-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 8px;
}

.naturaleza-label {
    font-size: 12px;
    font-weight: 600;
    color: #374151;
}

.naturaleza-help {
    font-size: 11px;
    color: #94a3b8;
}

.radio-group-premium {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}

.radio-card-premium {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 14px;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
    background: white;
}

.radio-card-premium:hover {
    border-color: #9ca3af;
    background: #fafbfc;
}

.radio-card-premium.selected {
    border-color: #667eea;
    background: #f0f4ff;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.08);
}

.radio-content {
    display: flex;
    align-items: center;
    gap: 10px;
}

.radio-dot {
    width: 18px;
    height: 18px;
    border: 2px solid #d1d5db;
    border-radius: 50%;
    transition: all 0.3s ease;
    flex-shrink: 0;
}

.radio-card-premium.selected .radio-dot {
    border-color: #667eea;
    background: #667eea;
    box-shadow: inset 0 0 0 3px white;
}

.radio-title-premium {
    font-size: 13px;
    font-weight: 600;
    color: #1f2937;
}

.radio-desc-premium {
    font-size: 11px;
    color: #94a3b8;
}

.radio-check-premium {
    margin-left: auto;
}

.check-icon-premium {
    width: 18px;
    height: 18px;
}

/* CONFIGURACIONES - EN LÍNEA HORIZONTAL */
.configuraciones-section {
    margin-bottom: 12px;
}

.configuraciones-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 8px;
}

.configuraciones-label {
    font-size: 12px;
    font-weight: 600;
    color: #374151;
}

.checkbox-group-horizontal {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}

.checkbox-premium {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 14px;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
    background: white;
    flex: 0 1 auto;
}

.checkbox-premium:hover {
    border-color: #9ca3af;
    background: #fafbfc;
}

.checkbox-premium.checked {
    border-color: #667eea;
    background: #f0f4ff;
}

.checkbox-custom-premium {
    width: 20px;
    height: 20px;
    min-width: 20px;
    border: 2px solid #d1d5db;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.checkbox-premium.checked .checkbox-custom-premium {
    background: linear-gradient(135deg, #667eea, #764ba2);
    border-color: #667eea;
}

.checkbox-check-premium {
    width: 12px;
    height: 12px;
}

.checkbox-label-inline {
    font-size: 13px;
    font-weight: 500;
    color: #1f2937;
}

.checkbox-badge {
    font-size: 9px;
    font-weight: 700;
    padding: 1px 10px;
    border-radius: 10px;
    background: #e5e7eb;
    color: #64748b;
}

.checkbox-premium.checked .checkbox-badge {
    background: #dbeafe;
    color: #667eea;
}

/* ============================================ */
/* JERARQUÍA Y RESULTADOS PADRE - EN LA MISMA LÍNEA */
/* ============================================ */
.jerarquia-resultados-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin: 12px 0;
}

.jerarquia-section {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.jerarquia-header {
    display: flex;
    align-items: center;
    gap: 10px;
}

.jerarquia-icon {
    width: 18px;
    height: 18px;
    color: #667eea;
}

.jerarquia-label {
    font-size: 12px;
    font-weight: 600;
    color: #374151;
}

.jerarquia-badge {
    font-size: 9px;
    font-weight: 700;
    padding: 1px 10px;
    border-radius: 10px;
    background: #dbeafe;
    color: #667eea;
}

.resultados-padre-section {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.resultados-padre-header {
    display: flex;
    align-items: center;
    gap: 10px;
}

.resultados-padre-icon {
    width: 18px;
    height: 18px;
    color: #8b5cf6;
}

.resultados-padre-label {
    font-size: 12px;
    font-weight: 600;
    color: #374151;
}

.resultados-padre-badge {
    font-size: 9px;
    font-weight: 700;
    padding: 1px 10px;
    border-radius: 10px;
    background: #f3e8ff;
    color: #8b5cf6;
}

/* INFO MESSAGES */
.info-messages {
    margin-top: 12px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.info-box-premium {
    background: #f8faff;
    border-left: 4px solid #667eea;
    border-radius: 8px;
    padding: 10px 14px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 12px;
    color: #475569;
}

.info-icon-premium {
    width: 18px;
    height: 18px;
    flex-shrink: 0;
}

/* RESUMEN JERARQUÍA */
.jerarquia-resumen-premium {
    background: linear-gradient(135deg, #f8fafc, #f1f5f9);
    border-radius: 12px;
    padding: 16px 20px;
    border: 1px solid #e2e8f0;
    margin-top: 12px;
}

.jerarquia-resumen-title {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
}

.jerarquia-resumen-title-text {
    font-size: 12px;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.jerarquia-resumen-status {
    font-size: 12px;
    font-weight: 600;
}

.jerarquia-ruta {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 6px;
    padding-bottom: 12px;
    border-bottom: 2px solid #e2e8f0;
}

.jerarquia-nodo {
    display: flex;
    align-items: center;
    gap: 6px;
    background: white;
    padding: 4px 12px;
    border-radius: 8px;
    border: 2px solid #e5e7eb;
    transition: all 0.3s ease;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}

.nodo-empresa {
    border-color: #1a3a5c;
    background: #f0f4f8;
}

.nodo-madre {
    border-color: #667eea;
    background: #f0f4ff;
}

.nodo-actual {
    border-color: #10b981;
    background: #ecfdf5;
    box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.12);
}

.nodo-pendiente {
    border-color: #f59e0b;
    background: #fffbeb;
    border-style: dashed;
}

.nodo-badge {
    font-size: 8px;
    font-weight: 700;
    color: white;
    padding: 1px 6px;
    border-radius: 4px;
    font-family: monospace;
}

.nodo-codigo {
    font-weight: 700;
    font-size: 12px;
    color: #1f2937;
    font-family: monospace;
}

.nodo-nombre {
    font-size: 12px;
    color: #374151;
}

.nodo-editar {
    font-size: 8px;
    font-weight: 700;
    background: #f59e0b;
    color: white;
    padding: 1px 8px;
    border-radius: 4px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.jerarquia-flecha {
    font-size: 14px;
    color: #94a3b8;
    font-weight: 300;
}

.jerarquia-detalles {
    margin-top: 10px;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.jerarquia-detalle {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 12px;
}

.detalle-label {
    font-weight: 600;
    color: #64748b;
    min-width: 80px;
}

.detalle-valor {
    color: #0f172a;
    font-weight: 500;
}

/* CREAR HIJA CONTAINER */
.crear-hija-container {
    margin: 12px 0 16px 0;
    padding: 12px 18px;
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
    border-radius: 10px;
    border: 2px solid #86efac;
}

.crear-hija-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
}

.crear-hija-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.crear-hija-icon {
    width: 28px;
    height: 28px;
    color: #059669;
    flex-shrink: 0;
}

.crear-hija-titulo {
    font-size: 14px;
    font-weight: 700;
    color: #065f46;
}

.crear-hija-desc {
    font-size: 13px;
    color: #065f46;
    margin-left: 4px;
}

.crear-hija-desc strong {
    color: #059669;
}

.btn-crear-hija-premium {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 18px;
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 700;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.25);
}

.btn-crear-hija-premium:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 16px rgba(16, 185, 129, 0.35);
}

.btn-crear-hija-premium .btn-icon-premium {
    width: 16px;
    height: 16px;
}

/* FORM ACTIONS */
.form-actions-premium {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 16px;
    padding-top: 16px;
    border-top: 2px solid #f1f5f9;
}

.btn-cancel-premium {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 24px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    color: #64748b;
    font-weight: 600;
    font-size: 13px;
    transition: all 0.3s ease;
    text-decoration: none;
    background: white;
}

.btn-cancel-premium:hover:not(:disabled) {
    border-color: #1a3a5c;
    color: #1a3a5c;
    background: #f8fafc;
    transform: translateY(-1px);
}

.btn-cancel-premium:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.btn-submit-premium {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 28px;
    background: linear-gradient(135deg, #1a3a5c, #2c5282);
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 700;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 2px 10px rgba(26, 58, 92, 0.2);
}

.btn-submit-premium:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 4px 20px rgba(26, 58, 92, 0.3);
}

.btn-submit-premium:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

/* ============================================ */
/* 🔥 MODAL CON OPCIONES VISIBLES PERO DESACTIVADAS */
/* ============================================ */
.modal-overlay-premium {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(6px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    animation: fadeIn 0.25s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(15px) scale(0.96); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

.modal-container-premium {
    background: white;
    border-radius: 18px;
    width: 100%;
    max-width: 560px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 24px 64px rgba(0, 0, 0, 0.2);
    animation: slideUp 0.25s ease;
}

.modal-header-premium {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    padding: 20px 24px;
    border-bottom: 2px solid #f1f5f9;
}

.modal-header-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.modal-icon-wrapper {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.modal-icon-premium {
    width: 20px;
    height: 20px;
    stroke: white;
}

.modal-title-premium {
    font-size: 17px;
    font-weight: 700;
    color: #0f172a;
    margin: 0;
}

.modal-subtitle-premium {
    font-size: 12px;
    color: #94a3b8;
    margin: 1px 0 0 0;
}

.modal-close-premium {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border: none;
    background: #f1f5f9;
    border-radius: 50%;
    color: #94a3b8;
    cursor: pointer;
    transition: all 0.3s ease;
    flex-shrink: 0;
}

.modal-close-premium:hover:not(:disabled) {
    background: #fecaca;
    color: #dc2626;
    transform: rotate(90deg);
}

.modal-close-premium:disabled {
    opacity: 0.4;
    cursor: not-allowed;
    transform: none;
}

.modal-body-premium {
    padding: 20px 24px 24px;
}

.form-grid-modal {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}

.full-width-modal {
    grid-column: 1 / -1;
}

.radio-group-modal {
    display: flex;
    gap: 6px;
}

.radio-card-modal {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 8px 12px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    background: white;
}

.radio-card-modal:hover:not(.modal-disabled) {
    border-color: #9ca3af;
}

.radio-card-modal.selected {
    border-color: #667eea;
    background: #f0f4ff;
    box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.08);
}

.radio-label-modal {
    font-size: 13px;
    font-weight: 600;
    color: #1f2937;
}

.checkbox-group-inline-modal {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.checkbox-modal {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 12px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    background: white;
}

.checkbox-modal:hover:not(.modal-disabled) {
    border-color: #9ca3af;
}

.checkbox-modal.checked {
    border-color: #667eea;
    background: #f0f4ff;
}

.checkbox-custom-modal {
    width: 16px;
    height: 16px;
    min-width: 16px;
    border: 2px solid #d1d5db;
    border-radius: 5px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.checkbox-modal.checked .checkbox-custom-modal {
    background: linear-gradient(135deg, #667eea, #764ba2);
    border-color: #667eea;
}

.checkbox-label-modal {
    font-size: 12px;
    font-weight: 500;
    color: #1f2937;
    white-space: nowrap;
}

/* 🔥 ESTILOS PARA ELEMENTOS DESACTIVADOS PERO VISIBLES */
.modal-disabled {
    opacity: 0.6 !important;
    cursor: not-allowed !important;
    background-color: #f3f4f6 !important;
}

.modal-disabled.checkbox-custom-modal {
    background: #e5e7eb !important;
    border-color: #d1d5db !important;
}

.modal-disabled.radio-card-modal {
    opacity: 0.6 !important;
    cursor: not-allowed !important;
}

.modal-disabled.form-textarea-premium {
    opacity: 0.6 !important;
    cursor: not-allowed !important;
}

.modal-disabled.form-input-premium {
    opacity: 0.6 !important;
    cursor: not-allowed !important;
    background-color: #f1f5f9 !important;
}

/* Botones del modal */
.modal-actions-premium {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 16px;
    padding-top: 14px;
    border-top: 2px solid #f1f5f9;
}

/* ============================================ */
/* RESPONSIVE */
/* ============================================ */
@media (max-width: 992px) {
    .form-grid-premium {
        grid-template-columns: 1fr 1fr;
    }
    
    .jerarquia-resultados-row {
        grid-template-columns: 1fr;
        gap: 12px;
    }
}

@media (max-width: 768px) {
    .form-grid-premium {
        grid-template-columns: 1fr;
    }
    
    .full-width {
        grid-column: 1;
    }
    
    .header-premium {
        padding: 14px 16px;
    }
    
    .header-title-premium {
        font-size: 18px;
    }
    
    .header-actions-premium {
        flex-direction: column;
        width: 100%;
    }
    
    .btn-back-premium,
    .btn-delete-premium {
        width: 100%;
        justify-content: center;
    }
    
    .form-card-premium {
        padding: 16px;
    }
    
    .radio-group-premium {
        grid-template-columns: 1fr;
    }
    
    .status-banner-content {
        flex-direction: column;
        gap: 6px;
    }
    
    .status-banner-progress {
        width: 100%;
    }
    
    .status-banner-percent {
        text-align: left;
    }
    
    .checkbox-group-horizontal {
        flex-direction: column;
        gap: 8px;
    }
    
    .checkbox-premium {
        width: 100%;
        flex: 1 1 auto;
    }
    
    .jerarquia-ruta {
        flex-direction: column;
        align-items: flex-start;
        gap: 6px;
    }
    
    .jerarquia-flecha {
        transform: rotate(90deg);
        padding: 2px 0;
    }
    
    .jerarquia-nodo {
        width: 100%;
    }
    
    .jerarquia-detalle {
        flex-direction: column;
        align-items: flex-start;
        gap: 2px;
    }
    
    .detalle-label {
        min-width: auto;
    }
    
    .form-actions-premium {
        flex-direction: column;
    }
    
    .btn-cancel-premium,
    .btn-submit-premium {
        width: 100%;
        justify-content: center;
    }
    
    .form-grid-modal {
        grid-template-columns: 1fr;
    }
    
    .full-width-modal {
        grid-column: 1;
    }
    
    .modal-container-premium {
        margin: 12px;
        max-height: 85vh;
    }
    
    .modal-header-premium {
        padding: 14px 16px;
    }
    
    .modal-body-premium {
        padding: 14px 16px 16px;
    }
    
    .radio-group-modal {
        flex-direction: column;
    }
    
    .checkbox-group-inline-modal {
        flex-direction: column;
        gap: 8px;
    }
    
    .checkbox-modal {
        width: 100%;
    }
    
    .crear-hija-content {
        flex-direction: column;
        align-items: stretch;
        text-align: center;
    }
    
    .crear-hija-info {
        justify-content: center;
    }
    
    .btn-crear-hija-premium {
        justify-content: center;
    }
    
    .jerarquia-resultados-row {
        grid-template-columns: 1fr;
        gap: 12px;
    }
}

@media (max-width: 480px) {
    .header-left-premium {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .section-header-premium {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .modal-header-info {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .modal-icon-wrapper {
        width: 36px;
        height: 36px;
    }
    
    .modal-icon-premium {
        width: 18px;
        height: 18px;
    }
}

/* SWEETALERT2 CUSTOM */
:deep(.swal-premium-popup) {
    border-radius: 16px !important;
    padding: 20px !important;
}

:deep(.swal-premium-confirm) {
    background: #1a3a5c !important;
    border-radius: 8px !important;
    font-weight: 600 !important;
    padding: 8px 24px !important;
}

:deep(.swal-premium-cancel) {
    background: #e5e7eb !important;
    color: #64748b !important;
    border-radius: 8px !important;
    font-weight: 600 !important;
    padding: 8px 24px !important;
}

.spinner-border {
    display: inline-block;
    width: 0.9rem;
    height: 0.9rem;
    border: 0.2em solid currentColor;
    border-right-color: transparent;
    border-radius: 50%;
    animation: spinner-border 0.75s linear infinite;
}

.spinner-border-sm {
    width: 0.8rem;
    height: 0.8rem;
    border-width: 0.15em;
}

@keyframes spinner-border {
    to { transform: rotate(360deg); }
}

.text-danger {
    color: #ef4444;
}
</style>