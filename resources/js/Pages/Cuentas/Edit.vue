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
                            <h2 class="header-title-premium">
                                Editar Cuenta
                            </h2>
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
                        <!-- SECCIÓN 1: NOMBRE Y CÓDIGO -->
                        <!-- ============================================ -->
                        <div class="form-section-premium">
                            <div class="section-header-premium">
                                <div class="section-icon-premium" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                                    <svg class="icon-svg-premium" fill="none" stroke="white" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="section-title-premium">Datos de la Cuenta</h3>
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
                                        <span v-if="verificandoCodigo" class="label-badge-premium" style="background: #f59e0b;">
                                            Verificando...
                                        </span>
                                        <span v-else-if="codigoExiste && form.codigo_cuenta && form.codigo_cuenta !== codigoOriginal" class="label-badge-premium" style="background: #ef4444;">
                                            Ocupado
                                        </span>
                                        <span v-else-if="!codigoExiste && form.codigo_cuenta && form.codigo_cuenta.length > 0 && form.codigo_cuenta !== codigoOriginal" class="label-badge-premium" style="background: #10b981;">
                                            Disponible
                                        </span>
                                        <span v-else-if="form.codigo_cuenta === codigoOriginal" class="label-badge-premium" style="background: #94a3b8;">
                                            Sin cambios
                                        </span>
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
                                    <div v-else-if="codigoExiste && form.codigo_cuenta && form.codigo_cuenta !== codigoOriginal" class="error-message-premium">
                                        <svg class="error-icon-premium" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        El código <strong>"{{ form.codigo_cuenta }}"</strong> ya está en uso
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
                            </div>
                        </div>

                        <!-- ============================================ -->
                        <!-- SECCIÓN 2: CLASIFICACIÓN (Naturaleza + Nivel) -->
                        <!-- ============================================ -->
                        <div class="form-section-premium">
                            <div class="section-header-premium">
                                <div class="section-icon-premium" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">
                                    <svg class="icon-svg-premium" fill="none" stroke="white" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="section-title-premium">Clasificación</h3>
                                    <p class="section-subtitle-premium">Define la naturaleza y nivel de la cuenta</p>
                                </div>
                            </div>

                            <div class="form-grid-premium">
                                <!-- Naturaleza -->
                                <div class="form-group-premium">
                                    <label class="form-label-premium">
                                        <span class="label-icon-wrapper">
                                            <svg class="label-icon-premium" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </span>
                                        Naturaleza <span class="required-star">*</span>
                                    </label>
                                    <div class="radio-group-premium">
                                        <div class="radio-card-premium" 
                                             :class="{ 'selected': form.Naturaleza === 'DEUDORA' }"
                                             @click="form.Naturaleza = 'DEUDORA'; clearError('Naturaleza')">
                                            <span class="radio-emoji-premium">📉</span>
                                            <div>
                                                <div class="radio-title-premium">Deudora</div>
                                                <div class="radio-desc-premium">Activos, Gastos</div>
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
                                            <span class="radio-emoji-premium">📈</span>
                                            <div>
                                                <div class="radio-title-premium">Acreedora</div>
                                                <div class="radio-desc-premium">Pasivos, Capital, Ingresos</div>
                                            </div>
                                            <div class="radio-check-premium" v-if="form.Naturaleza === 'ACREEDORA'">
                                                <svg class="check-icon-premium" fill="none" stroke="#10b981" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-if="form.errors.Naturaleza" class="error-message-premium">
                                        <svg class="error-icon-premium" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ form.errors.Naturaleza }}
                                    </div>
                                </div>

                                <!-- Nivel (NO EDITABLE) -->
                                <div class="form-group-premium">
                                    <label class="form-label-premium">
                                        <span class="label-icon-wrapper">
                                            <svg class="label-icon-premium" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                                            </svg>
                                        </span>
                                        Nivel
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

                                <!-- ============================================ -->
                                <!-- CHECKBOXES EN UNA SOLA LÍNEA -->
                                <!-- ============================================ -->
                                <div class="form-group-premium full-width">
                                    <label class="form-label-premium" style="visibility: hidden;">Configuraciones</label>
                                    <div class="checkbox-group-inline">
                                        <label class="checkbox-premium" 
                                               :class="{ 'checked': form.es_cuenta_resultados }"
                                               @click="toggleCuentaResultados">
                                            <div class="checkbox-custom-premium">
                                                <svg v-if="form.es_cuenta_resultados" class="checkbox-check-premium" fill="none" stroke="white" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </div>
                                            <span class="checkbox-label-inline">Cuenta de resultados</span>
                                        </label>

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

                                        <label class="checkbox-premium" 
                                               :class="{ 'checked': form.fondeo_c }"
                                               @click="form.fondeo_c = !form.fondeo_c">
                                            <div class="checkbox-custom-premium">
                                                <svg v-if="form.fondeo_c" class="checkbox-check-premium" fill="none" stroke="white" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </div>
                                            <span class="checkbox-label-inline">Cuenta fondeadora</span>
                                        </label>
                                    </div>
                                </div>

                                <!-- ============================================ -->
                                <!-- CAMPO: Cuenta de Resultados Padre (solo cuando NO es cuenta de resultados) -->
                                <!-- ============================================ -->
                                <div class="form-group-premium full-width" v-if="!form.es_cuenta_resultados">
                                    <label class="form-label-premium">
                                        <span class="label-icon-wrapper">
                                            <svg class="label-icon-premium" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                                            </svg>
                                        </span>
                                        Cuenta de Resultados Padre <span class="required-star">*</span>
                                        <span class="label-badge-premium" style="background: #8b5cf6;">
                                            Obligatorio
                                        </span>
                                    </label>
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
                                        <svg class="error-icon-premium" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ form.errors.cuenta_resultados }}
                                    </div>
                                    <div class="input-helper-premium">
                                        <span class="text-muted">Selecciona a qué cuenta de resultados pertenece esta cuenta</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ============================================ -->
                        <!-- SECCIÓN 3: JERARQUÍA + BOTÓN CREAR HIJA -->
                        <!-- ============================================ -->
                        <div class="form-section-premium">
                            <div class="section-header-premium">
                                <div class="section-icon-premium" style="background: linear-gradient(135deg, #10b981, #059669);">
                                    <svg class="icon-svg-premium" fill="none" stroke="white" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="section-title-premium">Jerarquía de la Cuenta</h3>
                                    <p class="section-subtitle-premium">Estructura completa en el sistema</p>
                                </div>
                            </div>

                            <div class="form-grid-premium">
                                <!-- Cuenta Madre (si nivel > 1) -->
                                <div class="form-group-premium full-width" v-if="form.nivel > 1 && form.id_empresa">
                                    <label class="form-label-premium">
                                        <span class="label-icon-wrapper">
                                            <svg class="label-icon-premium" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1h-2z"/>
                                            </svg>
                                        </span>
                                        Cuenta Madre <span class="required-star">*</span>
                                        <span class="label-badge-premium">Nivel {{ form.nivel - 1 }}</span>
                                        <span v-if="cargandoCuentasMadre" class="label-badge-premium" style="background: #f59e0b;">
                                            Cargando...
                                        </span>
                                        <span v-else-if="cuentasMadre.length > 0" class="label-badge-premium" style="background: #10b981;">
                                            {{ cuentasMadre.length }} disponibles
                                        </span>
                                    </label>
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
                                        <svg class="error-icon-premium" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ form.errors.id_cuenta_madre }}
                                    </div>
                                    <div class="input-helper-premium">
                                        <span v-if="cuentasMadre.length === 0 && !cargandoCuentasMadre" class="text-warning">
                                            No hay cuentas de nivel {{ form.nivel - 1 }} disponibles
                                        </span>
                                        <span v-else class="text-muted">
                                            La cuenta madre debe ser del nivel inmediato superior
                                        </span>
                                    </div>
                                </div>

                                <div class="form-group-premium full-width">
                                    <div class="jerarquia-resumen-premium">
                                        <!-- Ruta lineal -->
                                        <div class="jerarquia-resumen-ruta">
                                            <div class="jerarquia-resumen-item">
                                                <span class="jerarquia-resumen-badge" style="background: #1a3a5c;">N0</span>
                                                <span class="jerarquia-resumen-nombre">{{ empresaSeleccionada?.nombre_empresa || 'Empresa' }}</span>
                                            </div>

                                            <span class="jerarquia-resumen-flecha">→</span>

                                            <div v-if="cuentaMadreSeleccionada" class="jerarquia-resumen-item">
                                                <span class="jerarquia-resumen-badge" style="background: #667eea;">N{{ cuentaMadreSeleccionada.nivel }}</span>
                                                <span class="jerarquia-resumen-codigo">{{ cuentaMadreSeleccionada.codigo_cuenta }}</span>
                                                <span class="jerarquia-resumen-nombre">{{ cuentaMadreSeleccionada.nombre_cuenta }}</span>
                                            </div>
                                            <span v-if="cuentaMadreSeleccionada" class="jerarquia-resumen-flecha">→</span>
                                            <div v-else-if="form.nivel > 1" class="jerarquia-resumen-item pendiente">
                                                <span class="jerarquia-resumen-badge" style="background: #f59e0b;">N{{ form.nivel - 1 }}</span>
                                                <span class="jerarquia-resumen-nombre" style="color: #f59e0b;">Pendiente</span>
                                            </div>

                                            <div class="jerarquia-resumen-item actual">
                                                <span class="jerarquia-resumen-badge" style="background: #10b981;">N{{ form.nivel }}</span>
                                                <span class="jerarquia-resumen-codigo">{{ form.codigo_cuenta || '???' }}</span>
                                                <span class="jerarquia-resumen-nombre">{{ form.nombre_cuenta || 'Sin nombre' }}</span>
                                                <span class="jerarquia-resumen-badge-editar">EDITANDO</span>
                                            </div>
                                        </div>

                                        <!-- Detalle -->
                                        <div class="jerarquia-resumen-detalle">
                                            <div class="jerarquia-resumen-linea">
                                                <span class="jerarquia-resumen-label">Ruta:</span>
                                                <span class="jerarquia-resumen-valor">
                                                    <span style="color: #1a3a5c; font-weight: 700;">{{ empresaSeleccionada?.nombre_empresa || 'Empresa' }}</span>
                                                    <span v-if="cuentaMadreSeleccionada">
                                                        <span style="color: #94a3b8;"> → </span>
                                                        <span style="color: #667eea; font-weight: 600;">{{ cuentaMadreSeleccionada.codigo_cuenta }} - {{ cuentaMadreSeleccionada.nombre_cuenta }}</span>
                                                    </span>
                                                    <span v-else-if="form.nivel > 1">
                                                        <span style="color: #94a3b8;"> → </span>
                                                        <span style="color: #f59e0b; font-weight: 600;">Seleccionar cuenta madre</span>
                                                    </span>
                                                    <span style="color: #94a3b8;"> → </span>
                                                    <span style="color: #10b981; font-weight: 700;">{{ form.codigo_cuenta || '???' }} - {{ form.nombre_cuenta || 'Sin nombre' }}</span>
                                                </span>
                                            </div>
                                            <div class="jerarquia-resumen-linea" v-if="form.nivel > 1 && !cuentaMadreSeleccionada">
                                                <span class="jerarquia-resumen-label">Advertencia:</span>
                                                <span class="jerarquia-resumen-valor" style="color: #f59e0b; font-weight: 600;">
                                                    Selecciona una cuenta madre de nivel {{ form.nivel - 1 }}
                                                </span>
                                            </div>
                                            <div class="jerarquia-resumen-linea" v-else-if="form.nivel > 1 && cuentaMadreSeleccionada">
                                                <span class="jerarquia-resumen-label">Estado:</span>
                                                <span class="jerarquia-resumen-valor" style="color: #10b981; font-weight: 600;">
                                                    Jerarquía completa — Bajo "{{ cuentaMadreSeleccionada.nombre_cuenta }}"
                                                </span>
                                            </div>
                                            <div class="jerarquia-resumen-linea">
                                                <span class="jerarquia-resumen-label">Estado:</span>
                                                <span class="jerarquia-resumen-valor" :style="{ color: cuenta.en_uso ? '#10b981' : '#ef4444', fontWeight: '600' }">
                                                    {{ cuenta.en_uso ? 'Activa' : 'Inactiva' }}
                                                </span>
                                            </div>
                                            <div class="jerarquia-resumen-linea" v-if="form.cuenta_resultados">
                                                <span class="jerarquia-resumen-label">Cuenta Resultados:</span>
                                                <span class="jerarquia-resumen-valor" style="color: #8b5cf6; font-weight: 600;">
                                                    {{ getCuentaResultadosNombre(form.cuenta_resultados) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Botón para crear cuenta hija -->
                            <div class="crear-hija-container" v-if="cuenta.en_uso">
                                <button type="button" class="btn-crear-hija-premium" @click="abrirModalCrearHija">
                                    <svg class="btn-icon-premium" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                    <span>Crear cuenta hija</span>
                                    <span class="btn-crear-hija-badge">Nuevo</span>
                                </button>
                                <span class="crear-hija-desc">Crea una nueva cuenta que será hija de esta cuenta</span>
                            </div>
                        </div>

                        <!-- ============================================ -->
                        <!-- SECCIÓN 4: DESCRIPCIÓN -->
                        <!-- ============================================ -->
                        <div class="form-section-premium">
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
                                    <label class="form-label-premium">
                                        <span class="label-icon-wrapper">
                                            <svg class="label-icon-premium" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        </span>
                                        Descripción
                                    </label>
                                    <div class="input-wrapper-premium">
                                        <textarea v-model="form.descripcion"
                                                  @input="clearError('descripcion')"
                                                  class="form-textarea-premium"
                                                  :class="{ 'error': form.errors.descripcion }"
                                                  rows="4"
                                                  placeholder="Describe el propósito y uso de esta cuenta contable..."></textarea>
                                    </div>
                                    <div v-if="form.errors.descripcion" class="error-message-premium">
                                        <svg class="error-icon-premium" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ form.errors.descripcion }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ============================================ -->
                        <!-- BOTONES -->
                        <!-- ============================================ -->
                        <div class="info-box-premium">
                            <svg class="info-icon-premium" fill="none" stroke="#667eea" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Los campos con <strong class="text-danger">*</strong> son obligatorios</span>
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
        <!-- MODAL: CREAR CUENTA HIJA (SIMPLIFICADO) -->
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
                    <button type="button" @click="cerrarModalHija" class="modal-close-premium">
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
                                    <span v-if="verificandoCodigoHija" class="label-badge-premium" style="background: #f59e0b;">
                                        Verificando...
                                    </span>
                                    <span v-else-if="codigoExisteHija && formHija.codigo_cuenta" class="label-badge-premium" style="background: #ef4444;">
                                        Ocupado
                                    </span>
                                    <span v-else-if="!codigoExisteHija && formHija.codigo_cuenta && formHija.codigo_cuenta.length > 0" class="label-badge-premium" style="background: #10b981;">
                                        Disponible
                                    </span>
                                </label>
                                <div class="input-wrapper-premium input-with-icon">
                                    <svg class="input-icon-premium" fill="none" stroke="#94a3b8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                                    </svg>
                                    <input type="text" v-model="formHija.codigo_cuenta"
                                           @input="clearErrorHija('codigo_cuenta'); verificarCodigoHija()"
                                           class="form-input-premium"
                                           :class="{ 'error': formHija.errors.codigo_cuenta || codigoExisteHija }"
                                           placeholder="Ej: AC001-01">
                                </div>
                                <div v-if="formHija.errors.codigo_cuenta" class="error-message-premium">
                                    <svg class="error-icon-premium" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
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
                                           :class="{ 'error': formHija.errors.nombre_cuenta }"
                                           placeholder="Ej: Bancos Nacionales">
                                </div>
                                <div v-if="formHija.errors.nombre_cuenta" class="error-message-premium">
                                    <svg class="error-icon-premium" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
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
                                         :class="{ 'selected': formHija.Naturaleza === 'DEUDORA' }"
                                         @click="formHija.Naturaleza = 'DEUDORA'; clearErrorHija('Naturaleza')">
                                        <span class="radio-emoji-modal">📉</span>
                                        <span class="radio-label-modal">Deudora</span>
                                    </div>
                                    <div class="radio-card-modal" 
                                         :class="{ 'selected': formHija.Naturaleza === 'ACREEDORA' }"
                                         @click="formHija.Naturaleza = 'ACREEDORA'; clearErrorHija('Naturaleza')">
                                        <span class="radio-emoji-modal">📈</span>
                                        <span class="radio-label-modal">Acreedora</span>
                                    </div>
                                </div>
                                <div v-if="formHija.errors.Naturaleza" class="error-message-premium">
                                    {{ formHija.errors.Naturaleza }}
                                </div>
                            </div>

                            <!-- ============================================ -->
                            <!-- CHECKBOXES DEL MODAL EN LÍNEA -->
                            <!-- ============================================ -->
                            <div class="form-group-premium full-width-modal">
                                <div class="checkbox-group-inline-modal">
                                    <label class="checkbox-modal" 
                                           :class="{ 'checked': formHija.es_cuenta_resultados }"
                                           @click="formHija.es_cuenta_resultados = !formHija.es_cuenta_resultados">
                                        <div class="checkbox-custom-modal">
                                            <svg v-if="formHija.es_cuenta_resultados" class="checkbox-check-premium" fill="none" stroke="white" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                        <span class="checkbox-label-modal">Cuenta de resultados</span>
                                    </label>

                                    <label class="checkbox-modal" 
                                           :class="{ 'checked': formHija.fondeo_c }"
                                           @click="formHija.fondeo_c = !formHija.fondeo_c">
                                        <div class="checkbox-custom-modal">
                                            <svg v-if="formHija.fondeo_c" class="checkbox-check-premium" fill="none" stroke="white" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                        <span class="checkbox-label-modal">Cuenta fondeadora</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Cuenta de Resultados Padre (solo cuando NO es cuenta de resultados) -->
                            <div class="form-group-premium full-width-modal" v-if="!formHija.es_cuenta_resultados">
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
                                            :class="{ 'error': formHija.errors.cuenta_resultados }">
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

                            <!-- Descripción -->
                            <div class="form-group-premium full-width-modal">
                                <label class="form-label-premium">Descripción</label>
                                <div class="input-wrapper-premium">
                                    <textarea v-model="formHija.descripcion"
                                              class="form-textarea-premium"
                                              rows="2"
                                              placeholder="Breve descripción de la cuenta hija..."></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="modal-actions-premium">
                            <button type="button" @click="cerrarModalHija" class="btn-cancel-premium">
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
    
    if (form.nivel > 1) {
        if (!form.id_cuenta_madre || form.id_cuenta_madre.toString().trim() === '') return false;
    }
    
    // 🔥 Si NO es cuenta de resultados, debe tener cuenta_resultados
    if (!form.es_cuenta_resultados) {
        if (!form.cuenta_resultados || form.cuenta_resultados.toString().trim() === '') return false;
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
    
    // 🔥 Si NO es cuenta de resultados, debe tener cuenta_resultados
    if (!formHija.es_cuenta_resultados) {
        if (!formHija.cuenta_resultados || formHija.cuenta_resultados.toString().trim() === '') return false;
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
    if (form.nivel > 1) {
        fields.push('id_cuenta_madre');
    }
    if (!form.es_cuenta_resultados) {
        fields.push('cuenta_resultados');
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

const toggleCuentaResultados = () => {
    form.es_cuenta_resultados = !form.es_cuenta_resultados;
    if (form.es_cuenta_resultados) {
        form.cuenta_resultados = null;
    }
};

const getCuentaResultadosNombre = (id) => {
    const cuenta = cuentasResultados.value.find(c => c.id_cuenta === id);
    return cuenta ? cuenta.display : 'No seleccionada';
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

// ============================================
// VERIFICAR CÓDIGO HIJA
// ============================================
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
// CARGAR CUENTAS MADRE
// ============================================
const cargarCuentasMadre = async () => {
    if (!form.id_empresa || !form.nivel) {
        cuentasMadre.value = [];
        return;
    }

    if (form.nivel == 1) {
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

// ============================================
// CARGAR CUENTAS DE RESULTADOS
// ============================================
const cargarCuentasResultados = async () => {
    if (!form.id_empresa) {
        cuentasResultados.value = props.cuentasResultados || [];
        return;
    }

    try {
        const response = await axios.get('/cuentas/get-cuentas-resultados', {
            params: {
                empresa_id: form.id_empresa
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

// ============================================
// MODAL HIJA
// ============================================
const abrirModalCrearHija = () => {
    formHija.id_empresa = form.id_empresa;
    formHija.id_cuenta_madre = props.cuenta.id_cuenta;
    formHija.nivel = (parseInt(form.nivel) || 0) + 1;
    formHija.codigo_cuenta = '';
    formHija.nombre_cuenta = '';
    formHija.descripcion = '';
    formHija.Naturaleza = '';
    formHija.es_cuenta_resultados = false;
    formHija.fondeo_c = false;
    formHija.cuenta_resultados = null;
    formHija.clearErrors();
    codigoExisteHija.value = false;
    
    modalHijaVisible.value = true;
};

const cerrarModalHija = () => {
    modalHijaVisible.value = false;
    formHija.clearErrors();
    codigoExisteHija.value = false;
};

const guardarCuentaHija = () => {
    if (codigoExisteHija.value) {
        alertRef.value?.show({
            type: 'error',
            title: 'Código no disponible',
            message: 'El código "' + formHija.codigo_cuenta + '" ya está en uso. Por favor, elige otro diferente.',
            buttonText: 'Entendido'
        });
        return;
    }

    if (!isFormHijaValid.value) {
        alertRef.value?.show({
            type: 'error',
            title: 'Campos incompletos',
            message: 'Por favor, complete todos los campos obligatorios para crear la cuenta hija.',
            buttonText: 'Entendido'
        });
        return;
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
// ELIMINAR CUENTA (DESACTIVAR)
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
        const campos = {
            'id_empresa': 'Empresa',
            'codigo_cuenta': 'Código de cuenta',
            'nombre_cuenta': 'Nombre de cuenta',
            'Naturaleza': 'Naturaleza',
            'nivel': 'Nivel',
            'id_cuenta_madre': 'Cuenta madre (para nivel > 1)',
            'cuenta_resultados': 'Cuenta de resultados padre'
        };
        
        let faltante = '';
        for (const [key, label] of Object.entries(campos)) {
            const val = form[key];
            if (!val || val.toString().trim() === '') {
                faltante = label;
                break;
            }
        }
        
        alertRef.value?.show({
            type: 'error',
            title: 'Campos incompletos',
            message: 'Por favor, complete el campo: <strong>' + faltante + '</strong>',
            buttonText: 'Entendido'
        });
        return;
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
        if (form.nivel > 1) {
            form.id_cuenta_madre = null;
            cargarCuentasMadre();
        }
        cargarCuentasResultados();
    }
});

watch(() => form.nivel, (newVal, oldVal) => {
    if (newVal !== oldVal && newVal) {
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
    
    if (form.nivel > 1 && form.id_empresa) {
        cargarCuentasMadre();
    }
    
    if (form.id_empresa) {
        cargarCuentasResultados();
    }
});
</script>

<style scoped>
/* ===== HEADER PREMIUM ===== */
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
}

.subtitle-line {
    width: 24px;
    height: 2px;
    background: linear-gradient(90deg, #1a3a5c, transparent);
    border-radius: 2px;
}

.header-actions-premium {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.btn-back-premium {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    background: #f1f5f9;
    color: #1e293b;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
    text-decoration: none;
    font-size: 14px;
}

.btn-back-premium:hover {
    background: #e2e8f0;
    transform: translateX(-4px);
}

.btn-delete-premium {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    background: #fef2f2;
    color: #dc2626;
    border: 2px solid #fecaca;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-delete-premium:hover {
    background: #fee2e2;
    border-color: #f87171;
    transform: translateY(-2px);
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
    width: 18px;
    height: 18px;
}

/* ============================================ */
/* STATUS BANNER */
/* ============================================ */
.status-banner-premium {
    background: #ffffff;
    border-radius: 12px;
    padding: 16px 20px;
    margin-bottom: 24px;
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
    gap: 16px;
    flex-wrap: wrap;
}

.status-banner-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    flex-shrink: 0;
}

.status-icon-error {
    width: 24px;
    height: 24px;
}

.status-icon-success {
    width: 24px;
    height: 24px;
}

.status-icon-progress {
    width: 24px;
    height: 24px;
}

.status-banner-text {
    font-weight: 500;
    color: #0f172a;
    flex: 1;
}

.status-success .status-banner-text {
    color: #10b981;
}

.status-error .status-banner-text {
    color: #ef4444;
}

.status-banner-progress {
    flex: 1;
    height: 6px;
    background: #f1f5f9;
    border-radius: 4px;
    min-width: 120px;
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
    font-size: 14px;
    font-weight: 700;
    color: #1a3a5c;
    min-width: 44px;
    text-align: right;
}

/* ============================================ */
/* FORM CARD */
/* ============================================ */
.form-card-premium {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid #f0f2f5;
    padding: 32px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

/* ============================================ */
/* FORM SECTIONS */
/* ============================================ */
.form-section-premium {
    margin-bottom: 32px;
    padding-bottom: 32px;
    border-bottom: 2px solid #f1f5f9;
}

.form-section-premium:last-of-type {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.section-header-premium {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 24px;
}

.section-icon-premium {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.icon-svg-premium {
    width: 24px;
    height: 24px;
}

.section-title-premium {
    font-size: 18px;
    font-weight: 700;
    color: #0f172a;
    margin: 0;
}

.section-subtitle-premium {
    font-size: 14px;
    color: #94a3b8;
    margin: 2px 0 0 0;
}

/* ============================================ */
/* FORM GRID */
/* ============================================ */
.form-grid-premium {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 20px;
}

.full-width {
    grid-column: 1 / -1;
}

/* ============================================ */
/* FORM GROUP */
/* ============================================ */
.form-group-premium {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.form-label-premium {
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}

.label-badge-premium {
    font-size: 10px;
    font-weight: 700;
    color: white;
    padding: 2px 10px;
    border-radius: 12px;
    margin-left: 4px;
}

.label-icon-wrapper {
    display: inline-flex;
    align-items: center;
}

.label-icon-premium {
    width: 16px;
    height: 16px;
    color: #94a3b8;
}

.required-star {
    color: #ef4444;
    font-weight: 700;
    margin-left: 2px;
}

.input-wrapper-premium {
    position: relative;
}

.input-with-icon {
    position: relative;
}

.input-icon-premium {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    width: 18px;
    height: 18px;
    z-index: 1;
    pointer-events: none;
}

.input-with-icon .form-input-premium {
    padding-left: 40px;
}

.form-input-premium {
    width: 100%;
    padding: 10px 14px;
    font-size: 14px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    background: white;
    color: #1f2937;
    transition: all 0.3s ease;
    outline: none;
    height: 44px;
}

.form-input-premium:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.08);
}

.form-input-premium:hover:not(:focus) {
    border-color: #9ca3af;
}

.form-input-premium.error {
    border-color: #ef4444;
}

.form-input-premium.error:focus {
    box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.08);
}

.form-input-premium:disabled {
    background: #f1f5f9;
    cursor: not-allowed;
    opacity: 0.7;
}

.form-textarea-premium {
    width: 100%;
    padding: 10px 14px;
    font-size: 14px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    background: white;
    color: #1f2937;
    transition: all 0.3s ease;
    outline: none;
    resize: vertical;
    min-height: 80px;
    font-family: inherit;
}

.form-textarea-premium:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.08);
}

.form-textarea-premium.error {
    border-color: #ef4444;
}

/* ============================================ */
/* ERROR MESSAGES */
/* ============================================ */
.error-message-premium {
    font-size: 12px;
    color: #ef4444;
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: 4px;
}

.error-icon-premium {
    width: 16px;
    height: 16px;
    flex-shrink: 0;
}

/* ============================================ */
/* INPUT HELPER */
/* ============================================ */
.input-helper-premium {
    margin-top: 4px;
    font-size: 12px;
    color: #94a3b8;
}

.text-warning {
    color: #f59e0b;
}

.text-success {
    color: #10b981;
}

.text-muted {
    color: #94a3b8;
}

/* ============================================ */
/* RADIO GROUP */
/* ============================================ */
.radio-group-premium {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

.radio-card-premium {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
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
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.08);
}

.radio-emoji-premium {
    font-size: 20px;
}

.radio-title-premium {
    font-size: 14px;
    font-weight: 600;
    color: #1f2937;
}

.radio-desc-premium {
    font-size: 12px;
    color: #94a3b8;
}

.radio-check-premium {
    margin-left: auto;
}

.check-icon-premium {
    width: 20px;
    height: 20px;
}

/* ============================================ */
/* CHECKBOX EN LÍNEA */
/* ============================================ */
.checkbox-group-inline {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
    padding-top: 4px;
}

.checkbox-premium {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 16px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    background: white;
}

.checkbox-premium:hover {
    border-color: #9ca3af;
    background: #fafbfc;
}

.checkbox-premium.checked {
    border-color: #667eea;
    background: #f0f4ff;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.08);
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
    width: 14px;
    height: 14px;
}

.checkbox-label-inline {
    font-size: 13px;
    font-weight: 500;
    color: #1f2937;
    white-space: nowrap;
}

/* ============================================ */
/* JERARQUÍA RESUMEN */
/* ============================================ */
.jerarquia-resumen-premium {
    background: linear-gradient(135deg, #f8fafc, #f1f5f9);
    border-radius: 12px;
    padding: 20px 24px;
    border: 1px solid #e2e8f0;
}

.jerarquia-resumen-ruta {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 6px;
    padding-bottom: 16px;
    border-bottom: 2px solid #e2e8f0;
}

.jerarquia-resumen-item {
    display: flex;
    align-items: center;
    gap: 6px;
    background: white;
    padding: 6px 14px;
    border-radius: 8px;
    border: 2px solid #e5e7eb;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
}

.jerarquia-resumen-item.actual {
    border-color: #10b981;
    background: #ecfdf5;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
}

.jerarquia-resumen-item.pendiente {
    border-color: #f59e0b;
    background: #fffbeb;
    border-style: dashed;
}

.jerarquia-resumen-badge {
    font-size: 9px;
    font-weight: 700;
    color: white;
    padding: 2px 8px;
    border-radius: 4px;
    font-family: monospace;
}

.jerarquia-resumen-codigo {
    font-weight: 700;
    font-size: 13px;
    color: #1f2937;
    font-family: monospace;
}

.jerarquia-resumen-nombre {
    font-size: 13px;
    color: #374151;
}

.jerarquia-resumen-badge-editar {
    font-size: 9px;
    font-weight: 700;
    background: #f59e0b;
    color: white;
    padding: 2px 10px;
    border-radius: 4px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.jerarquia-resumen-flecha {
    font-size: 18px;
    color: #94a3b8;
    font-weight: 300;
}

.jerarquia-resumen-detalle {
    margin-top: 16px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.jerarquia-resumen-linea {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 13px;
    flex-wrap: wrap;
}

.jerarquia-resumen-label {
    font-weight: 600;
    color: #64748b;
    min-width: 100px;
}

.jerarquia-resumen-valor {
    color: #0f172a;
    font-weight: 500;
}

/* ============================================ */
/* CREAR HIJA */
/* ============================================ */
.crear-hija-container {
    margin-top: 20px;
    padding: 16px 20px;
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
    border-radius: 12px;
    border: 2px dashed #86efac;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
}

.btn-crear-hija-premium {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 12px 24px;
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    border: none;
    border-radius: 10px;
    font-weight: 700;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 16px rgba(16, 185, 129, 0.3);
}

.btn-crear-hija-premium:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 32px rgba(16, 185, 129, 0.4);
}

.btn-crear-hija-premium .btn-icon-premium {
    width: 20px;
    height: 20px;
}

.btn-crear-hija-badge {
    font-size: 10px;
    font-weight: 700;
    background: rgba(255, 255, 255, 0.25);
    padding: 2px 10px;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.crear-hija-desc {
    font-size: 13px;
    color: #065f46;
    font-weight: 500;
}

/* ============================================ */
/* INFO BOX */
/* ============================================ */
.info-box-premium {
    background: #f8faff;
    border-left: 4px solid #667eea;
    border-radius: 8px;
    padding: 12px 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 24px 0 0 0;
    font-size: 13px;
    color: #475569;
}

.info-icon-premium {
    width: 20px;
    height: 20px;
    flex-shrink: 0;
}

.text-danger {
    color: #ef4444;
}

/* ============================================ */
/* FORM ACTIONS */
/* ============================================ */
.form-actions-premium {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 24px;
    padding-top: 20px;
    border-top: 2px solid #f1f5f9;
}

.btn-cancel-premium {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 28px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    color: #64748b;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s ease;
    text-decoration: none;
    background: white;
}

.btn-cancel-premium:hover {
    border-color: #1a3a5c;
    color: #1a3a5c;
    background: #f8fafc;
    transform: translateY(-2px);
}

.btn-submit-premium {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 32px;
    background: linear-gradient(135deg, #1a3a5c, #2c5282);
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 700;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 2px 12px rgba(26, 58, 92, 0.25);
}

.btn-submit-premium:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 24px rgba(26, 58, 92, 0.35);
}

.btn-submit-premium:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

/* ============================================ */
/* MODAL HIJA */
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
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.modal-container-premium {
    background: white;
    border-radius: 20px;
    width: 100%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 24px 64px rgba(0, 0, 0, 0.2);
    animation: slideUp 0.3s ease;
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px) scale(0.95); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

.modal-header-premium {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    padding: 24px 28px;
    border-bottom: 2px solid #f1f5f9;
}

.modal-header-info {
    display: flex;
    align-items: center;
    gap: 14px;
}

.modal-icon-wrapper {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.modal-icon-premium {
    width: 24px;
    height: 24px;
    stroke: white;
}

.modal-title-premium {
    font-size: 18px;
    font-weight: 700;
    color: #0f172a;
    margin: 0;
}

.modal-subtitle-premium {
    font-size: 13px;
    color: #94a3b8;
    margin: 2px 0 0 0;
}

.modal-close-premium {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border: none;
    background: #f1f5f9;
    border-radius: 50%;
    color: #94a3b8;
    cursor: pointer;
    transition: all 0.3s ease;
    flex-shrink: 0;
}

.modal-close-premium:hover {
    background: #fecaca;
    color: #dc2626;
    transform: rotate(90deg);
}

.modal-body-premium {
    padding: 24px 28px 28px;
}

.form-grid-modal {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.full-width-modal {
    grid-column: 1 / -1;
}

.radio-group-modal {
    display: flex;
    gap: 8px;
}

.radio-card-modal {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 14px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    background: white;
}

.radio-card-modal:hover {
    border-color: #9ca3af;
}

.radio-card-modal.selected {
    border-color: #667eea;
    background: #f0f4ff;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.radio-emoji-modal {
    font-size: 18px;
}

.radio-label-modal {
    font-size: 13px;
    font-weight: 600;
    color: #1f2937;
}

/* ============================================ */
/* CHECKBOX MODAL EN LÍNEA */
/* ============================================ */
.checkbox-group-inline-modal {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
}

.checkbox-modal {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 14px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    background: white;
}

.checkbox-modal:hover {
    border-color: #9ca3af;
}

.checkbox-modal.checked {
    border-color: #667eea;
    background: #f0f4ff;
}

.checkbox-custom-modal {
    width: 18px;
    height: 18px;
    min-width: 18px;
    border: 2px solid #d1d5db;
    border-radius: 6px;
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
    font-size: 13px;
    font-weight: 500;
    color: #1f2937;
    white-space: nowrap;
}

.modal-actions-premium {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 20px;
    padding-top: 16px;
    border-top: 2px solid #f1f5f9;
}

/* ============================================ */
/* RESPONSIVE */
/* ============================================ */
@media (max-width: 992px) {
    .form-grid-premium {
        grid-template-columns: 1fr 1fr;
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
        padding: 20px;
    }
    
    .radio-group-premium {
        grid-template-columns: 1fr;
    }
    
    .status-banner-content {
        flex-direction: column;
        gap: 8px;
    }
    
    .status-banner-progress {
        width: 100%;
    }
    
    .status-banner-percent {
        text-align: left;
    }
    
    .jerarquia-resumen-ruta {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
    
    .jerarquia-resumen-flecha {
        transform: rotate(90deg);
        padding: 4px 0;
    }
    
    .jerarquia-resumen-item {
        width: 100%;
    }
    
    .jerarquia-resumen-linea {
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
    }
    
    .jerarquia-resumen-label {
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
    
    .checkbox-group-inline {
        flex-direction: column;
        gap: 10px;
    }
    
    .checkbox-premium {
        width: 100%;
    }
    
    .form-grid-modal {
        grid-template-columns: 1fr;
    }
    
    .full-width-modal {
        grid-column: 1;
    }
    
    .modal-container-premium {
        margin: 16px;
        max-height: 85vh;
    }
    
    .modal-header-premium {
        padding: 16px 20px;
    }
    
    .modal-body-premium {
        padding: 16px 20px 20px;
    }
    
    .radio-group-modal {
        flex-direction: column;
    }
    
    .checkbox-group-inline-modal {
        flex-direction: column;
        gap: 10px;
    }
    
    .checkbox-modal {
        width: 100%;
    }
    
    .crear-hija-container {
        flex-direction: column;
        align-items: stretch;
        text-align: center;
    }
    
    .btn-crear-hija-premium {
        justify-content: center;
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
        width: 40px;
        height: 40px;
    }
    
    .modal-icon-premium {
        width: 20px;
        height: 20px;
    }
}
</style>