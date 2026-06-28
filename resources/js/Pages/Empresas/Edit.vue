<template>
    <AppLayout title="Editar Empresa">
        <template #header>
            <div class="header-wrapper">
                <div class="header-left">
                    <Link :href="route('empresas.index')" class="btn-back">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                    </Link>
                    <div class="header-content">
                        <h2 class="header-title">Editar Empresa</h2>
                        <p class="header-subtitle">{{ empresa.nombre_empresa }}</p>
                    </div>
                </div>
                <div class="header-right">
                    <div class="status-badge" :class="statusClass">
                        <span v-if="hasErrors">⚠️ {{ errorCount }} errores</span>
                        <span v-else-if="isComplete">✅ Completado</span>
                        <span v-else>📝 {{ Math.round(progressPercentage) }}%</span>
                    </div>
                </div>
            </div>
        </template>

        <div class="page-content">
            <div class="container-custom">
                <div class="form-card">
                    <form @submit.prevent="submit" id="empresaForm" novalidate>
                        <!-- ============================================ -->
                        <!-- SECCIÓN 1: DATOS DE LA EMPRESA -->
                        <!-- ============================================ -->
                        <div class="form-section">
                            <div class="section-header">
                                <div class="section-icon" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                                    <svg class="icon-svg" fill="none" stroke="white" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                </div>
                                <div class="section-title-group">
                                    <h3 class="section-title">Datos de la Empresa</h3>
                                    <p class="section-subtitle">Información principal de la empresa</p>
                                </div>
                            </div>

                            <div class="form-grid">
                                <!-- Fila 1: Tipo de Persona -->
                                <div class="form-group full-width">
                                    <label class="form-label">
                                        <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                        Tipo de Persona <span class="required-star">*</span>
                                    </label>
                                    <div class="radio-group-horizontal">
                                        <div class="radio-card-mini" 
                                             :class="{ 'selected': form.tipo_persona === 'FISICA' }"
                                             @click="form.tipo_persona = 'FISICA'; clearError('tipo_persona')">
                                            <span class="radio-emoji">👤</span>
                                            <div>
                                                <div class="radio-title-mini">Persona Física</div>
                                                <div class="radio-desc-mini">Individual</div>
                                            </div>
                                            <div class="radio-check-mini" v-if="form.tipo_persona === 'FISICA'">
                                                <svg class="check-icon" fill="none" stroke="#10b981" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </div>
                                            <input type="radio" v-model="form.tipo_persona" value="FISICA" class="radio-input">
                                        </div>

                                        <div class="radio-card-mini" 
                                             :class="{ 'selected': form.tipo_persona === 'MORAL' }"
                                             @click="form.tipo_persona = 'MORAL'; clearError('tipo_persona')">
                                            <span class="radio-emoji">🏢</span>
                                            <div>
                                                <div class="radio-title-mini">Persona Moral</div>
                                                <div class="radio-desc-mini">Empresa o sociedad</div>
                                            </div>
                                            <div class="radio-check-mini" v-if="form.tipo_persona === 'MORAL'">
                                                <svg class="check-icon" fill="none" stroke="#10b981" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </div>
                                            <input type="radio" v-model="form.tipo_persona" value="MORAL" class="radio-input">
                                        </div>
                                    </div>
                                    <div v-if="form.errors.tipo_persona" class="error-message">
                                        <svg class="error-icon" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ form.errors.tipo_persona }}
                                    </div>
                                </div>

                                <!-- Fila 2: Nombre Empresa y RFC -->
                                <div class="form-group">
                                    <label class="form-label">
                                        <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                        Nombre de la Empresa <span class="required-star">*</span>
                                    </label>
                                    <div class="input-wrapper">
                                        <input type="text" v-model="form.nombre_empresa"
                                               @input="clearError('nombre_empresa')"
                                               class="form-input"
                                               :class="{ 'error': form.errors.nombre_empresa }"
                                               placeholder="Ej: Corporativo RIC S.A. de C.V.">
                                        <div class="input-icon">
                                            <svg class="icon-svg-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div v-if="form.errors.nombre_empresa" class="error-message">
                                        <svg class="error-icon" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ form.errors.nombre_empresa }}
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">
                                        <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                                        </svg>
                                        RFC <span class="required-star">*</span>
                                    </label>
                                    <div class="input-wrapper">
                                        <input type="text" v-model="form.rfc"
                                               @input="form.rfc = form.rfc.toUpperCase(); clearError('rfc'); validateRFC()"
                                               class="form-input text-uppercase"
                                               :class="{ 'error': form.errors.rfc || rfcError }"
                                               placeholder="Ej: RIC20260617"
                                               maxlength="13">
                                        <div class="input-icon">
                                            <svg class="icon-svg-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div v-if="form.errors.rfc" class="error-message">
                                        <svg class="error-icon" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ form.errors.rfc }}
                                    </div>
                                    <div v-if="rfcError" class="error-message">
                                        <svg class="error-icon" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ rfcError }}
                                    </div>
                                </div>

                                <!-- Fila 3: Clave y Régimen -->
                                <div class="form-group">
                                    <label class="form-label">
                                        <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                        </svg>
                                        Clave
                                    </label>
                                    <div class="input-wrapper">
                                        <input type="text" v-model="form.clave"
                                               @input="form.clave = form.clave.toUpperCase()"
                                               class="form-input text-uppercase"
                                               placeholder="Ej: RIC001"
                                               maxlength="20">
                                        <div class="input-icon">
                                            <svg class="icon-svg-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                            </svg>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">
                                        <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                                        </svg>
                                        Régimen
                                    </label>
                                    <div class="input-wrapper">
                                        <input type="text" v-model="form.regimen"
                                               class="form-input"
                                               placeholder="Ej: Régimen General de Ley">
                                        <div class="input-icon">
                                            <svg class="icon-svg-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ============================================ -->
                        <!-- SECCIÓN 2: CONTACTO DE LA EMPRESA -->
                        <!-- ============================================ -->
                        <div class="form-section">
                            <div class="section-header">
                                <div class="section-icon" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">
                                    <svg class="icon-svg" fill="none" stroke="white" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                </div>
                                <div class="section-title-group">
                                    <h3 class="section-title">Contacto de la Empresa</h3>
                                    <p class="section-subtitle">Medios de contacto</p>
                                </div>
                                <span class="badge-optional">Opcional</span>
                            </div>

                            <div class="form-grid">
                                <div class="form-group full-width">
                                    <label class="form-label">
                                        <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                        Correo Electrónico
                                    </label>
                                    <div class="input-wrapper">
                                        <input type="email" v-model="form.correo"
                                               @input="clearError('correo'); validateEmail()"
                                               class="form-input"
                                               :class="{ 'error': form.errors.correo || emailError }"
                                               placeholder="empresa@correo.com">
                                        <div class="input-icon">
                                            <svg class="icon-svg-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div v-if="form.errors.correo" class="error-message">
                                        <svg class="error-icon" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ form.errors.correo }}
                                    </div>
                                    <div v-if="emailError" class="error-message">
                                        <svg class="error-icon" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ emailError }}
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">
                                        <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                        </svg>
                                        Teléfono Personal
                                    </label>
                                    <div class="input-wrapper">
                                        <input type="text" v-model="form.telefono_personal"
                                               @input="validatePhone('telefono_personal')"
                                               class="form-input"
                                               :class="{ 'error': phoneErrors.telefono_personal }"
                                               placeholder="7771234567"
                                               maxlength="10">
                                        <div class="input-icon">
                                            <svg class="icon-svg-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div v-if="phoneErrors.telefono_personal" class="error-message">
                                        <svg class="error-icon" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ phoneErrors.telefono_personal }}
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">
                                        <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                        </svg>
                                        Teléfono de Trabajo
                                    </label>
                                    <div class="input-wrapper">
                                        <input type="text" v-model="form.telefono_trabajo"
                                               @input="validatePhone('telefono_trabajo')"
                                               class="form-input"
                                               :class="{ 'error': phoneErrors.telefono_trabajo }"
                                               placeholder="7771234567"
                                               maxlength="10">
                                        <div class="input-icon">
                                            <svg class="icon-svg-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div v-if="phoneErrors.telefono_trabajo" class="error-message">
                                        <svg class="error-icon" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ phoneErrors.telefono_trabajo }}
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">
                                        <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                                        </svg>
                                        Extensión
                                    </label>
                                    <div class="input-wrapper">
                                        <input type="text" v-model="form.extension"
                                               @input="validateExtension()"
                                               class="form-input"
                                               :class="{ 'error': extensionError }"
                                               placeholder="101"
                                               maxlength="4">
                                        <div class="input-icon">
                                            <svg class="icon-svg-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div v-if="extensionError" class="error-message">
                                        <svg class="error-icon" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ extensionError }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ============================================ -->
                        <!-- SECCIÓN 3: DIRECCIÓN -->
                        <!-- ============================================ -->
                        <div class="form-section">
                            <div class="section-header">
                                <div class="section-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                                    <svg class="icon-svg" fill="none" stroke="white" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                                <div class="section-title-group">
                                    <h3 class="section-title">Dirección</h3>
                                    <p class="section-subtitle">Ubicación de la empresa</p>
                                </div>
                                <span class="badge-optional">Opcional</span>
                            </div>

                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">
                                        <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                        </svg>
                                        Estado
                                    </label>
                                    <div class="input-wrapper">
                                        <select v-model="form.estado" class="form-input form-select">
                                            <option value="">Selecciona un estado</option>
                                            <option v-for="estado in estadosMexico" :key="estado" :value="estado">
                                                {{ estado }}
                                            </option>
                                        </select>
                                        <div class="input-icon">
                                            <svg class="icon-svg-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">
                                        <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                        Municipio
                                    </label>
                                    <div class="input-wrapper">
                                        <input type="text" v-model="form.municipio"
                                               class="form-input"
                                               placeholder="Ej: Cuauhtémoc">
                                        <div class="input-icon">
                                            <svg class="icon-svg-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                            </svg>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">
                                        <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1h-2z"/>
                                        </svg>
                                        Ciudad
                                    </label>
                                    <div class="input-wrapper">
                                        <input type="text" v-model="form.ciudad"
                                               class="form-input"
                                               placeholder="Ej: Ciudad de México">
                                        <div class="input-icon">
                                            <svg class="icon-svg-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1h-2z"/>
                                            </svg>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">
                                        <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                        Colonia
                                    </label>
                                    <div class="input-wrapper">
                                        <input type="text" v-model="form.colonia"
                                               class="form-input"
                                               placeholder="Ej: Colonia Centro">
                                        <div class="input-icon">
                                            <svg class="icon-svg-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                            </svg>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group full-width">
                                    <label class="form-label">
                                        <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                        </svg>
                                        Calle
                                    </label>
                                    <div class="input-wrapper">
                                        <input type="text" v-model="form.calle"
                                               class="form-input"
                                               placeholder="Ej: Av. Insurgentes Sur">
                                        <div class="input-icon">
                                            <svg class="icon-svg-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                            </svg>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">
                                        <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                                        </svg>
                                        Número Exterior
                                    </label>
                                    <div class="input-wrapper">
                                        <input type="text" v-model="form.numero_exterior"
                                               @input="validateNumber('numero_exterior')"
                                               class="form-input"
                                               :class="{ 'error': numberErrors.numero_exterior }"
                                               placeholder="123">
                                        <div class="input-icon">
                                            <svg class="icon-svg-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div v-if="numberErrors.numero_exterior" class="error-message">
                                        <svg class="error-icon" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ numberErrors.numero_exterior }}
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">
                                        <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                                        </svg>
                                        Número Interior
                                    </label>
                                    <div class="input-wrapper">
                                        <input type="text" v-model="form.numero_interior"
                                               @input="validateNumber('numero_interior')"
                                               class="form-input"
                                               :class="{ 'error': numberErrors.numero_interior }"
                                               placeholder="2B">
                                        <div class="input-icon">
                                            <svg class="icon-svg-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div v-if="numberErrors.numero_interior" class="error-message">
                                        <svg class="error-icon" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ numberErrors.numero_interior }}
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">
                                        <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                        Código Postal
                                    </label>
                                    <div class="input-wrapper">
                                        <input type="text" v-model="form.codigo_postal"
                                               @input="validatePostalCode()"
                                               class="form-input"
                                               :class="{ 'error': postalCodeError }"
                                               placeholder="06000"
                                               maxlength="5">
                                        <div class="input-icon">
                                            <svg class="icon-svg-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div v-if="postalCodeError" class="error-message">
                                        <svg class="error-icon" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ postalCodeError }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ============================================ -->
                        <!-- SECCIÓN 4: REPRESENTANTE LEGAL -->
                        <!-- ============================================ -->
                        <div v-if="form.tipo_persona === 'MORAL'" class="form-section">
                            <div class="section-header">
                                <div class="section-icon" style="background: linear-gradient(135deg, #ec4899, #db2777);">
                                    <svg class="icon-svg" fill="none" stroke="white" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                                <div class="section-title-group">
                                    <h3 class="section-title">Representante Legal</h3>
                                    <p class="section-subtitle">Datos del representante legal de la empresa</p>
                                </div>
                                <span class="badge-required">Obligatorio</span>
                            </div>

                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">
                                        <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        Nombre <span class="required-star">*</span>
                                    </label>
                                    <div class="input-wrapper">
                                        <input type="text" v-model="form.representante_nombre"
                                               @input="clearError('representante_nombre')"
                                               class="form-input"
                                               :class="{ 'error': form.errors.representante_nombre }"
                                               placeholder="Juan">
                                        <div class="input-icon">
                                            <svg class="icon-svg-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div v-if="form.errors.representante_nombre" class="error-message">
                                        <svg class="error-icon" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ form.errors.representante_nombre }}
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">
                                        <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        Apellido Paterno <span class="required-star">*</span>
                                    </label>
                                    <div class="input-wrapper">
                                        <input type="text" v-model="form.representante_apellido_paterno"
                                               @input="clearError('representante_apellido_paterno')"
                                               class="form-input"
                                               :class="{ 'error': form.errors.representante_apellido_paterno }"
                                               placeholder="Pérez">
                                        <div class="input-icon">
                                            <svg class="icon-svg-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div v-if="form.errors.representante_apellido_paterno" class="error-message">
                                        <svg class="error-icon" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ form.errors.representante_apellido_paterno }}
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">
                                        <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        Apellido Materno
                                    </label>
                                    <div class="input-wrapper">
                                        <input type="text" v-model="form.representante_apellido_materno"
                                               class="form-input"
                                               placeholder="García">
                                        <div class="input-icon">
                                            <svg class="icon-svg-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">
                                        <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                                        </svg>
                                        RFC <span class="required-star">*</span>
                                    </label>
                                    <div class="input-wrapper">
                                        <input type="text" v-model="form.representante_rfc"
                                               @input="form.representante_rfc = form.representante_rfc.toUpperCase(); clearError('representante_rfc'); validateRepresentanteRFC()"
                                               class="form-input text-uppercase"
                                               :class="{ 'error': form.errors.representante_rfc || representanteRfcError }"
                                               placeholder="REPRFC123456"
                                               maxlength="13">
                                        <div class="input-icon">
                                            <svg class="icon-svg-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div v-if="form.errors.representante_rfc" class="error-message">
                                        <svg class="error-icon" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ form.errors.representante_rfc }}
                                    </div>
                                    <div v-if="representanteRfcError" class="error-message">
                                        <svg class="error-icon" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ representanteRfcError }}
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">
                                        <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                                        </svg>
                                        CURP
                                    </label>
                                    <div class="input-wrapper">
                                        <input type="text" v-model="form.representante_curp"
                                               @input="form.representante_curp = form.representante_curp.toUpperCase(); validateCURP()"
                                               class="form-input text-uppercase"
                                               :class="{ 'error': curpError }"
                                               placeholder="CURP1234567890"
                                               maxlength="18">
                                        <div class="input-icon">
                                            <svg class="icon-svg-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div v-if="curpError" class="error-message">
                                        <svg class="error-icon" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ curpError }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ============================================ -->
                        <!-- SECCIÓN 5: ESTADO DE LA EMPRESA -->
                        <!-- ============================================ -->
                        <div class="form-section">
                            <div class="section-header">
                                <div class="section-icon" style="background: linear-gradient(135deg, #22c55e, #16a34a);">
                                    <svg class="icon-svg" fill="none" stroke="white" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div class="section-title-group">
                                    <h3 class="section-title">Estado de la Empresa</h3>
                                    <p class="section-subtitle">Activar o desactivar la empresa</p>
                                </div>
                            </div>

                            <div class="form-grid">
                                <div class="form-group full-width">
                                    <div class="toggle-container">
                                        <label class="toggle-switch">
                                            <input type="checkbox" v-model="form.activo" class="toggle-input">
                                            <span class="toggle-slider"></span>
                                        </label>
                                        <div class="toggle-label">
                                            <span v-if="form.activo" class="text-green-600 font-semibold">Empresa Activa</span>
                                            <span v-else class="text-red-600 font-semibold">Empresa Inactiva</span>
                                            <span class="text-sm text-gray-500 ml-2">(Los usuarios no podrán operar con esta empresa)</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ============================================ -->
                        <!-- INFO ADICIONAL Y BOTONES -->
                        <!-- ============================================ -->
                        <div class="info-box-mini">
                            <svg class="info-icon" fill="none" stroke="#667eea" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>Los campos con <strong class="text-danger">*</strong> son obligatorios</span>
                        </div>

                        <div class="form-actions">
                            <div class="actions-right">
                                <Link :href="route('empresas.index')" class="btn btn-cancel">
                                    <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    Cancelar
                                </Link>
                                <button type="submit" 
                                        :disabled="form.processing || !isFormValid"
                                        class="btn btn-submit">
                                    <span v-if="form.processing" class="spinner-border spinner-border-sm me-2" role="status"></span>
                                    <svg v-else class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    {{ form.processing ? 'Guardando...' : 'Actualizar Empresa' }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Alert Component -->
        <AlertModal ref="alertRef" />
    </AppLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';
import AlertModal from '@/Components/AlertModal.vue';

const props = defineProps({
    empresa: Object,
    estadosMexico: {
        type: Array,
        default: () => []
    }
});

// Referencia al componente de alerta
const alertRef = ref(null);

// Validaciones adicionales
const rfcError = ref('');
const emailError = ref('');
const extensionError = ref('');
const postalCodeError = ref('');
const representanteRfcError = ref('');
const curpError = ref('');

const phoneErrors = ref({
    telefono_personal: '',
    telefono_trabajo: ''
});

const numberErrors = ref({
    numero_exterior: '',
    numero_interior: ''
});

// ============================================
// INICIALIZAR FORMULARIO CON DATOS DE LA EMPRESA
// ============================================
const form = useForm({
    // Datos generales
    nombre_empresa: props.empresa.nombre_empresa || '',
    rfc: props.empresa.rfc || '',
    regimen: props.empresa.regimen || '',
    tipo_persona: props.empresa.tipo_persona || 'FISICA',
    clave: props.empresa.clave || '',
    
    // Dirección
    estado: props.empresa.estado || '',
    municipio: props.empresa.municipio || '',
    ciudad: props.empresa.ciudad || '',
    colonia: props.empresa.colonia || '',
    calle: props.empresa.calle || '',
    numero_exterior: props.empresa.numero_exterior || '',
    numero_interior: props.empresa.numero_interior || '',
    codigo_postal: props.empresa.codigo_postal || '',
    
    // Contacto
    correo: props.empresa.correo || '',
    telefono_personal: props.empresa.telefono_personal || '',
    telefono_trabajo: props.empresa.telefono_trabajo || '',
    extension: props.empresa.extension || '',
    
    // Representante
    representante_nombre: props.empresa.representante_nombre || '',
    representante_apellido_paterno: props.empresa.representante_apellido_paterno || '',
    representante_apellido_materno: props.empresa.representante_apellido_materno || '',
    representante_rfc: props.empresa.representante_rfc || '',
    representante_curp: (props.empresa.representante_curp || '').trim().toUpperCase(),
    
    // Estado
    activo: props.empresa.activo ?? true
});

const validateCURP = () => {
    const curp = form.representante_curp;
    
    if (!curp || curp.trim() === '') {
        curpError.value = '';
        return;
    }
    
    const curpLimpio = curp.trim().replace(/\s/g, '').toUpperCase();
    
    if (form.representante_curp !== curpLimpio) {
        form.representante_curp = curpLimpio;
        return;
    }
    
    // ✅ FORMATO CORRECTO DE CURP
    // [4 letras][6 números][6 letras][2 caracteres alfanuméricos]
    // Los últimos 2 pueden ser números o letras
    const curpRegex = /^[A-Z]{4}[0-9]{6}[A-Z]{6}[0-9A-Z]{2}$/;
    
    if (curpLimpio.length !== 18) {
        curpError.value = `La CURP debe tener 18 caracteres (tiene ${curpLimpio.length})`;
        return;
    }
    
    if (!curpRegex.test(curpLimpio)) {
        curpError.value = 'Formato de CURP inválido. Debe ser: 4 letras, 6 números, 6 letras, 2 dígitos (números o letras). Ejemplo: GOPM900415MDFRRA00';
        return;
    }
    
    curpError.value = '';
};

// ============================================
// VALIDACIÓN DE RFC
// ============================================
const validateRFC = () => {
    const rfc = form.rfc;
    if (!rfc) {
        rfcError.value = '';
        return;
    }
    
    const rfcRegex = /^[A-ZÑ&]{3,4}[0-9]{6}[A-Z0-9]{3}$/;
    if (!rfcRegex.test(rfc)) {
        rfcError.value = 'El RFC debe tener formato válido (3-4 letras, 6 números, 3 caracteres alfanuméricos)';
    } else {
        rfcError.value = '';
    }
};

// ============================================
// VALIDACIÓN DE REPRESENTANTE RFC
// ============================================
const validateRepresentanteRFC = () => {
    const rfc = form.representante_rfc;
    if (!rfc) {
        representanteRfcError.value = '';
        return;
    }
    
    const rfcRegex = /^[A-ZÑ&]{3,4}[0-9]{6}[A-Z0-9]{3}$/;
    if (!rfcRegex.test(rfc)) {
        representanteRfcError.value = 'El RFC debe tener formato válido (3-4 letras, 6 números, 3 caracteres alfanuméricos)';
    } else {
        representanteRfcError.value = '';
    }
};

// ============================================
// VALIDACIÓN DE EMAIL
// ============================================
const validateEmail = () => {
    const email = form.correo;
    if (!email) {
        emailError.value = '';
        return;
    }
    
    const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    if (!emailRegex.test(email)) {
        emailError.value = 'Ingrese un correo electrónico válido (ejemplo@dominio.com)';
    } else {
        emailError.value = '';
    }
};

// ============================================
// VALIDACIÓN DE TELÉFONO
// ============================================
const validatePhone = (field) => {
    const value = form[field];
    if (!value) {
        phoneErrors.value[field] = '';
        return;
    }
    
    if (!/^\d+$/.test(value)) {
        phoneErrors.value[field] = 'Solo se permiten números';
    } else if (value.length < 10) {
        phoneErrors.value[field] = 'El teléfono debe tener 10 dígitos';
    } else {
        phoneErrors.value[field] = '';
    }
};

// ============================================
// VALIDACIÓN DE EXTENSIÓN
// ============================================
const validateExtension = () => {
    const ext = form.extension;
    if (!ext) {
        extensionError.value = '';
        return;
    }
    
    if (!/^\d+$/.test(ext)) {
        extensionError.value = 'Solo se permiten números';
    } else {
        extensionError.value = '';
    }
};

// ============================================
// VALIDACIÓN DE NÚMEROS (EXT/INT)
// ============================================
const validateNumber = (field) => {
    const value = form[field];
    if (!value) {
        numberErrors.value[field] = '';
        return;
    }
    
    if (!/^[A-Za-z0-9]+$/.test(value)) {
        numberErrors.value[field] = 'Solo se permiten letras y números';
    } else {
        numberErrors.value[field] = '';
    }
};

// ============================================
// VALIDACIÓN DE CÓDIGO POSTAL
// ============================================
const validatePostalCode = () => {
    const cp = form.codigo_postal;
    if (!cp) {
        postalCodeError.value = '';
        return;
    }
    
    if (!/^\d{5}$/.test(cp)) {
        postalCodeError.value = 'El código postal debe tener 5 dígitos';
    } else {
        postalCodeError.value = '';
    }
};

// ============================================
// LIMPIAR ERRORES
// ============================================
const clearError = (field) => {
    if (form.errors[field]) {
        delete form.errors[field];
    }
};

// ============================================
// COMPUTED PARA VALIDAR EL FORMULARIO COMPLETO
// ============================================
const isFormValid = computed(() => {
    const requiredFields = ['tipo_persona', 'nombre_empresa', 'rfc'];
    if (form.tipo_persona === 'MORAL') {
        requiredFields.push('representante_nombre', 'representante_apellido_paterno', 'representante_rfc');
    }
    
    const hasRequiredErrors = requiredFields.some(field => {
        const val = form[field];
        return !val || val.toString().trim().length === 0;
    });
    
    if (hasRequiredErrors) return false;
    
    if (rfcError.value) return false;
    if (emailError.value) return false;
    if (extensionError.value) return false;
    if (postalCodeError.value) return false;
    if (representanteRfcError.value) return false;
    if (curpError.value) return false;
    
    if (phoneErrors.value.telefono_personal) return false;
    if (phoneErrors.value.telefono_trabajo) return false;
    
    if (numberErrors.value.numero_exterior) return false;
    if (numberErrors.value.numero_interior) return false;
    
    return true;
});

// ============================================
// WATCHERS PARA VALIDACIÓN EN TIEMPO REAL
// ============================================
watch(() => form.rfc, () => {
    if (form.rfc) validateRFC();
}, { immediate: true });

watch(() => form.correo, () => {
    if (form.correo) validateEmail();
}, { immediate: true });

watch(() => form.representante_rfc, () => {
    if (form.representante_rfc) validateRepresentanteRFC();
}, { immediate: true });

watch(() => form.representante_curp, (newValue) => {
    if (newValue && newValue.trim() !== '') {
        validateCURP();
    } else {
        curpError.value = '';
    }
}, { immediate: true });

// ============================================
// COMPUTED PARA ESTADOS
// ============================================
const hasErrors = computed(() => Object.keys(form.errors).length > 0 || 
    rfcError.value || emailError.value || extensionError.value || 
    postalCodeError.value || representanteRfcError.value || curpError.value ||
    phoneErrors.value.telefono_personal || phoneErrors.value.telefono_trabajo ||
    numberErrors.value.numero_exterior || numberErrors.value.numero_interior);

const errorCount = computed(() => {
    let count = Object.keys(form.errors).length;
    if (rfcError.value) count++;
    if (emailError.value) count++;
    if (extensionError.value) count++;
    if (postalCodeError.value) count++;
    if (representanteRfcError.value) count++;
    if (curpError.value) count++;
    if (phoneErrors.value.telefono_personal) count++;
    if (phoneErrors.value.telefono_trabajo) count++;
    if (numberErrors.value.numero_exterior) count++;
    if (numberErrors.value.numero_interior) count++;
    return count;
});

const requiredFields = computed(() => {
    const fields = ['tipo_persona', 'nombre_empresa', 'rfc'];
    if (form.tipo_persona === 'MORAL') {
        fields.push('representante_nombre', 'representante_apellido_paterno', 'representante_rfc');
    }
    return fields;
});

const progressPercentage = computed(() => {
    const total = requiredFields.value.length;
    const filled = requiredFields.value.filter(f => {
        const val = form[f];
        return val && val.toString().trim().length > 0;
    }).length;
    return total > 0 ? (filled / total) * 100 : 0;
});

const isComplete = computed(() => progressPercentage.value === 100 && !hasErrors.value);

const statusClass = computed(() => {
    if (hasErrors.value) return 'status-error';
    if (isComplete.value) return 'status-success';
    return 'status-progress';
});

// ============================================
// SUBMIT
// ============================================
const submit = () => {
    // Validar todos los campos
    validateRFC();
    validateEmail();
    validateRepresentanteRFC();
    validateCURP();
    
    if (form.telefono_personal) validatePhone('telefono_personal');
    if (form.telefono_trabajo) validatePhone('telefono_trabajo');
    if (form.extension) validateExtension();
    if (form.numero_exterior) validateNumber('numero_exterior');
    if (form.numero_interior) validateNumber('numero_interior');
    if (form.codigo_postal) validatePostalCode();
    
    if (!isFormValid.value) {
        alertRef.value?.show({
            type: 'error',
            title: 'Error de validación',
            message: 'Por favor, corrija los errores en el formulario antes de continuar.',
            buttonText: 'Entendido'
        });
        return;
    }
    
    form.put(route('empresas.update', props.empresa.id), {
        onSuccess: () => {
            alertRef.value?.show({
                type: 'success',
                title: '¡Empresa actualizada!',
                message: `La empresa "${form.nombre_empresa}" se ha actualizado exitosamente.`,
                buttonText: 'Ir al listado'
            });
        },
        onError: (errors) => {
            if (errors.rfc) {
                alertRef.value?.show({
                    type: 'error',
                    title: 'RFC duplicado',
                    message: 'El RFC que ingresaste ya está registrado en otra empresa. Por favor, verifica el RFC.',
                    buttonText: 'Entendido'
                });
            } else {
                alertRef.value?.show({
                    type: 'error',
                    title: 'Error al actualizar',
                    message: 'Ocurrió un error al actualizar la empresa. Verifique los datos e intente nuevamente.',
                    buttonText: 'Intentar de nuevo'
                });
            }
        }
    });
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

.status-badge {
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    transition: all 0.3s ease;
}

.status-success {
    background: linear-gradient(135deg, #d1fae5, #a7f3d0);
    color: #065f46;
}

.status-error {
    background: linear-gradient(135deg, #fecaca, #fca5a5);
    color: #991b1b;
    animation: shake 0.5s ease;
}

.status-progress {
    background: linear-gradient(135deg, #e0e7ff, #c7d2fe);
    color: #4338ca;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
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

/* ========== FORM CARD ========== */
.form-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
    border: 1px solid #f3f4f6;
    padding: 2rem;
    transition: all 0.3s ease;
}

.form-card:hover {
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
}

/* ========== SECTIONS ========== */
.form-section {
    margin-bottom: 2rem;
    animation: fadeIn 0.5s ease;
}

.form-section:last-of-type {
    margin-bottom: 1.5rem;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.section-header {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 2px solid #f3f4f6;
}

.section-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    border-radius: 12px;
    color: white;
    flex-shrink: 0;
    transition: all 0.3s ease;
}

.section-header:hover .section-icon {
    transform: scale(1.05) rotate(-5deg);
}

.icon-svg {
    width: 22px;
    height: 22px;
}

.section-title-group {
    flex: 1;
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

.badge-required {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: white;
    padding: 4px 14px;
    border-radius: 20px;
    font-size: 0.7rem;
    font-weight: 600;
    white-space: nowrap;
}

.badge-optional {
    background: #f3f4f6;
    color: #6b7280;
    padding: 4px 14px;
    border-radius: 20px;
    font-size: 0.7rem;
    font-weight: 600;
    white-space: nowrap;
}

/* ========== FORM GRID ========== */
.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

/* ========== LABELS ========== */
.form-label {
    font-size: 0.85rem;
    font-weight: 600;
    color: #374151;
    display: flex;
    align-items: center;
    gap: 6px;
}

.label-icon {
    width: 18px;
    height: 18px;
    color: #667eea;
    flex-shrink: 0;
}

.required-star {
    color: #ef4444;
    font-weight: 700;
}

/* ========== RADIO CARDS ========== */
.radio-group-horizontal {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

.radio-card-mini {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
}

.radio-card-mini:hover {
    border-color: #667eea;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.1);
}

.radio-card-mini.selected {
    border-color: #667eea;
    background: linear-gradient(135deg, #f8f7ff, #f0eeff);
    box-shadow: 0 4px 16px rgba(102, 126, 234, 0.15);
}

.radio-emoji {
    font-size: 1.8rem;
    flex-shrink: 0;
}

.radio-title-mini {
    font-weight: 600;
    color: #111827;
    font-size: 0.9rem;
}

.radio-desc-mini {
    font-size: 0.75rem;
    color: #6b7280;
}

.radio-check-mini {
    margin-left: auto;
    animation: popIn 0.3s ease;
}

.check-icon {
    width: 22px;
    height: 22px;
}

@keyframes popIn {
    0% { transform: scale(0); }
    50% { transform: scale(1.3); }
    100% { transform: scale(1); }
}

.radio-input {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}

/* ========== INPUTS ========== */
.input-wrapper {
    position: relative;
}

.form-input {
    width: 100%;
    padding: 10px 40px 10px 14px;
    font-size: 0.9rem;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    background: white;
    color: #1f2937;
    transition: all 0.3s ease;
    outline: none;
    height: 44px;
}

.form-input:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.form-input:hover:not(:focus) {
    border-color: #9ca3af;
}

.form-input.error {
    border-color: #ef4444;
}

.form-input.error:focus {
    box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
}

.form-input.text-uppercase {
    text-transform: uppercase;
}

.form-select {
    appearance: none;
    cursor: pointer;
    padding-right: 40px;
}

.form-select option {
    padding: 8px;
}

.input-icon {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    pointer-events: none;
    transition: color 0.3s ease;
}

.icon-svg-sm {
    width: 18px;
    height: 18px;
}

.input-wrapper:focus-within .input-icon {
    color: #667eea;
}

.error-message {
    font-size: 0.75rem;
    color: #ef4444;
    display: flex;
    align-items: center;
    gap: 4px;
    margin-top: 2px;
}

.error-icon {
    width: 16px;
    height: 16px;
    flex-shrink: 0;
}

/* ========== TOGGLE SWITCH ========== */
.toggle-container {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 12px 0;
}

.toggle-switch {
    position: relative;
    display: inline-block;
    width: 60px;
    height: 34px;
    flex-shrink: 0;
}

.toggle-switch .toggle-input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #e5e7eb;
    transition: .4s;
    border-radius: 34px;
}

.toggle-slider:before {
    position: absolute;
    content: "";
    height: 26px;
    width: 26px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.toggle-input:checked + .toggle-slider {
    background: linear-gradient(135deg, #22c55e, #16a34a);
}

.toggle-input:checked + .toggle-slider:before {
    transform: translateX(26px);
}

.toggle-input:focus + .toggle-slider {
    box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.2);
}

.toggle-label {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

/* ========== INFO BOX ========== */
.info-box-mini {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 18px;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.06), rgba(118, 75, 162, 0.06));
    border-radius: 12px;
    border-left: 4px solid #667eea;
    font-size: 0.85rem;
    color: #4b5563;
    margin-top: 4px;
}

.info-icon {
    width: 20px;
    height: 20px;
    flex-shrink: 0;
}

.text-danger {
    color: #ef4444;
}

/* ========== BUTTONS ========== */
.form-actions {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 12px;
    margin-top: 24px;
    padding-top: 20px;
    border-top: 2px solid #f3f4f6;
}

.actions-right {
    display: flex;
    gap: 12px;
    align-items: center;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 28px;
    font-weight: 600;
    border: none;
    border-radius: 50px;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    cursor: pointer;
    text-decoration: none;
    height: 44px;
}

.btn-icon {
    width: 18px;
    height: 18px;
}

.btn-cancel {
    background: #f3f4f6;
    color: #6b7280;
}

.btn-cancel:hover {
    background: #fecaca;
    color: #dc2626;
    transform: translateY(-2px);
}

.btn-submit {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: white;
}

.btn-submit:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 24px rgba(245, 158, 11, 0.35);
}

.btn-submit:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

/* ========== SPINNER ========== */
.spinner-border {
    display: inline-block;
    width: 1rem;
    height: 1rem;
    border: 0.2em solid currentColor;
    border-right-color: transparent;
    border-radius: 50%;
    animation: spinner 0.75s linear infinite;
}

@keyframes spinner {
    to { transform: rotate(360deg); }
}

.me-2 {
    margin-right: 0.5rem;
}

/* ========== TEXT COLORS ========== */
.text-green-600 {
    color: #16a34a;
}

.text-red-600 {
    color: #dc2626;
}

.text-gray-500 {
    color: #6b7280;
}

.font-semibold {
    font-weight: 600;
}

.text-sm {
    font-size: 0.875rem;
}

.ml-2 {
    margin-left: 0.5rem;
}

/* ========== RESPONSIVE ========== */
@media (max-width: 768px) {
    .form-card {
        padding: 1.25rem;
    }

    .form-grid {
        grid-template-columns: 1fr;
        gap: 14px;
    }

    .form-group.full-width {
        grid-column: 1;
    }

    .radio-group-horizontal {
        grid-template-columns: 1fr;
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

    .form-actions {
        flex-direction: column-reverse;
        align-items: stretch;
    }

    .actions-right {
        width: 100%;
        justify-content: center;
        flex-direction: column;
    }

    .btn {
        width: 100%;
        justify-content: center;
        padding: 10px 20px;
        height: 44px;
    }

    .container-custom {
        padding: 0 0.75rem;
    }

    .status-badge {
        font-size: 0.7rem;
        padding: 4px 12px;
    }

    .section-icon {
        width: 38px;
        height: 38px;
    }

    .icon-svg {
        width: 18px;
        height: 18px;
    }

    .section-title {
        font-size: 0.95rem;
    }

    .toggle-container {
        flex-direction: column;
        align-items: flex-start;
    }
}

@media (max-width: 480px) {
    .form-card {
        padding: 1rem;
        border-radius: 16px;
    }

    .header-title {
        font-size: 1.1rem;
    }

    .section-header {
        gap: 10px;
    }

    .section-icon {
        width: 34px;
        height: 34px;
    }

    .icon-svg {
        width: 16px;
        height: 16px;
    }

    .radio-emoji {
        font-size: 1.5rem;
    }

    .radio-title-mini {
        font-size: 0.8rem;
    }
}
</style>