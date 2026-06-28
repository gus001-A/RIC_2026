<template>
    <AppLayout title="Editar Persona">
        <template #header>
            <div class="header-wrapper">
                <div class="header-left">
                    <Link :href="route('personas.index')" class="btn-back">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                    </Link>
                    <div class="header-content">
                        <h2 class="header-title">Editar Persona</h2>
                        <p class="header-subtitle">Actualice la información de la persona</p>
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
                    <form @submit.prevent="submit" id="personaForm" novalidate>
                        <!-- ============================================ -->
                        <!-- SECCIÓN 1: TIPO DE PERSONA -->
                        <!-- ============================================ -->
                        <div class="form-section">
                            <div class="section-header">
                                <div class="section-icon" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                                    <svg class="icon-svg" fill="none" stroke="white" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                </div>
                                <div class="section-title-group">
                                    <h3 class="section-title">Tipo de Persona</h3>
                                    <p class="section-subtitle">Seleccione el tipo de persona que desea registrar</p>
                                </div>
                            </div>

                            <div class="form-grid">
                                <div class="form-group full-width">
                                    <div class="radio-group-horizontal">
                                        <div class="radio-card-mini" 
                                             :class="{ 'selected': form.tipo_persona === 'FISICA' }"
                                             @click="form.tipo_persona = 'FISICA'; clearError('tipo_persona'); onTipoChange()">
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
                                             @click="form.tipo_persona = 'MORAL'; clearError('tipo_persona'); onTipoChange()">
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
                            </div>
                        </div>

                        <!-- ============================================ -->
                        <!-- SECCIÓN 2: DATOS PERSONALES (MISMOS PARA AMBOS) -->
                        <!-- ============================================ -->
                        <div class="form-section">
                            <div class="section-header">
                                <div class="section-icon" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">
                                    <svg class="icon-svg" fill="none" stroke="white" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                                <div class="section-title-group">
                                    <h3 class="section-title">Datos Personales</h3>
                                    <p class="section-subtitle">Información principal de la persona</p>
                                </div>
                            </div>

                            <div class="form-grid">
                                <!-- Nombre (SIEMPRE el mismo label) -->
                                <div class="form-group full-width">
                                    <label class="form-label">
                                        <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                        Nombre(s) <span class="required-star">*</span>
                                    </label>
                                    <div class="input-wrapper">
                                        <input type="text" v-model="form.Nombre"
                                               @input="clearError('Nombre'); autoGenerarRFC(); validateText('Nombre')"
                                               class="form-input"
                                               :class="{ 'error': form.errors.Nombre || textErrors.Nombre }"
                                               placeholder="Ej: Juan Carlos"
                                               maxlength="100">
                                        <div class="input-icon">
                                            <svg class="icon-svg-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div v-if="form.errors.Nombre" class="error-message">
                                        <svg class="error-icon" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ form.errors.Nombre }}
                                    </div>
                                    <div v-if="textErrors.Nombre" class="error-message">
                                        <svg class="error-icon" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ textErrors.Nombre }}
                                    </div>
                                </div>

                                <!-- Apellido Paterno (SIEMPRE el mismo label) -->
                                <div class="form-group">
                                    <label class="form-label">
                                        <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                        Apellido Paterno <span class="required-star">*</span>
                                    </label>
                                    <div class="input-wrapper">
                                        <input type="text" v-model="form.Paterno"
                                               @input="clearError('Paterno'); autoGenerarRFC(); validateText('Paterno')"
                                               class="form-input"
                                               :class="{ 'error': form.errors.Paterno || textErrors.Paterno }"
                                               placeholder="Ej: Pérez"
                                               maxlength="50">
                                        <div class="input-icon">
                                            <svg class="icon-svg-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div v-if="form.errors.Paterno" class="error-message">
                                        <svg class="error-icon" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ form.errors.Paterno }}
                                    </div>
                                    <div v-if="textErrors.Paterno" class="error-message">
                                        <svg class="error-icon" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ textErrors.Paterno }}
                                    </div>
                                </div>

                                <!-- Apellido Materno (SIEMPRE el mismo label) -->
                                <div class="form-group">
                                    <label class="form-label">
                                        <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                        Apellido Materno
                                    </label>
                                    <div class="input-wrapper">
                                        <input type="text" v-model="form.Materno"
                                               @input="autoGenerarRFC(); validateText('Materno')"
                                               class="form-input"
                                               :class="{ 'error': textErrors.Materno }"
                                               placeholder="Ej: García"
                                               maxlength="50">
                                        <div class="input-icon">
                                            <svg class="icon-svg-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div v-if="textErrors.Materno" class="error-message">
                                        <svg class="error-icon" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ textErrors.Materno }}
                                    </div>
                                </div>

                                <!-- Fecha de Nacimiento -->
                                <div class="form-group full-width">
                                    <label class="form-label">
                                        <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        Fecha de Nacimiento <span class="required-star">*</span>
                                        <span class="helper-label">(Debe ser mayor de edad)</span>
                                    </label>
                                    <div class="input-wrapper">
                                        <input type="date" v-model="form.Fecha_nacimiento"
                                               @change="clearError('Fecha_nacimiento'); validateEdad(); autoGenerarRFC()"
                                               class="form-input date-input"
                                               :class="{ 'error': form.errors.Fecha_nacimiento || edadError }"
                                               :max="fechaMaxima">
                                        <div class="input-icon">
                                            <svg class="icon-svg-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div v-if="form.errors.Fecha_nacimiento" class="error-message">
                                        <svg class="error-icon" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ form.errors.Fecha_nacimiento }}
                                    </div>
                                    <div v-if="edadError" class="error-message">
                                        <svg class="error-icon" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ edadError }}
                                    </div>
                                </div>

                                <!-- Sexo (SIEMPRE el mismo label) -->
                                <div class="form-group full-width">
                                    <label class="form-label">
                                        <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                        </svg>
                                        Sexo <span class="required-star">*</span>
                                    </label>
                                    <div class="radio-group-horizontal">
                                        <div class="radio-card-mini" 
                                             :class="{ 'selected': form.sexo === 'MASCULINO' }"
                                             @click="form.sexo = 'MASCULINO'; clearError('sexo')">
                                            <span class="radio-emoji">♂️</span>
                                            <div>
                                                <div class="radio-title-mini">Masculino</div>
                                            </div>
                                            <div class="radio-check-mini" v-if="form.sexo === 'MASCULINO'">
                                                <svg class="check-icon" fill="none" stroke="#10b981" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </div>
                                            <input type="radio" v-model="form.sexo" value="MASCULINO" class="radio-input">
                                        </div>

                                        <div class="radio-card-mini" 
                                             :class="{ 'selected': form.sexo === 'FEMENINO' }"
                                             @click="form.sexo = 'FEMENINO'; clearError('sexo')">
                                            <span class="radio-emoji">♀️</span>
                                            <div>
                                                <div class="radio-title-mini">Femenino</div>
                                            </div>
                                            <div class="radio-check-mini" v-if="form.sexo === 'FEMENINO'">
                                                <svg class="check-icon" fill="none" stroke="#10b981" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </div>
                                            <input type="radio" v-model="form.sexo" value="FEMENINO" class="radio-input">
                                        </div>
                                    </div>
                                    <div v-if="form.errors.sexo" class="error-message">
                                        <svg class="error-icon" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ form.errors.sexo }}
                                    </div>
                                </div>

                                <!-- RFC -->
                                <div class="form-group">
                                    <label class="form-label">
                                        <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18M5 18h14M7 6h10"/>
                                        </svg>
                                        RFC <span class="required-star">*</span>
                                    </label>
                                    <div class="input-wrapper">
                                        <input type="text" v-model="form.rfc"
                                               @input="clearError('rfc'); form.rfc = form.rfc.toUpperCase(); validateRFC()"
                                               class="form-input text-uppercase"
                                               :class="{ 'error': form.errors.rfc || rfcError }"
                                               placeholder="Ej: PERE890101XXX"
                                               maxlength="13">
                                        <button type="button" 
                                                @click="generarRFC" 
                                                class="btn-generate-rfc"
                                                :disabled="generandoRFC || !puedeGenerarRFC">
                                            <span v-if="generandoRFC" class="spinner-small"></span>
                                            <span v-else>Generar RFC</span>
                                        </button>
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
                                    <div class="input-helper">
                                        <span class="helper-text">
                                            <svg class="helper-icon" fill="none" stroke="#6b7280" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            {{ form.tipo_persona === 'FISICA' ? 'RFC de persona física (13 caracteres)' : 'RFC de persona moral (12 caracteres)' }}
                                        </span>
                                    </div>
                                </div>

                                <!-- CURP -->
                                <div class="form-group">
                                    <label class="form-label">
                                        <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18M5 18h14M7 6h10"/>
                                        </svg>
                                        CURP
                                        <span class="helper-label">(Opcional)</span>
                                    </label>
                                    <div class="input-wrapper">
                                        <input type="text" v-model="form.curp"
                                               @input="clearError('curp'); form.curp = form.curp.toUpperCase(); validateCURP()"
                                               class="form-input text-uppercase"
                                               :class="{ 'error': form.errors.curp || curpError }"
                                               placeholder="Ej: PERE890101HDFRRN09"
                                               maxlength="18">
                                        <div class="input-icon">
                                            <svg class="icon-svg-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18M5 18h14M7 6h10"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div v-if="form.errors.curp" class="error-message">
                                        <svg class="error-icon" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ form.errors.curp }}
                                    </div>
                                    <div v-if="curpError" class="error-message">
                                        <svg class="error-icon" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ curpError }}
                                    </div>
                                    <div class="input-helper">
                                        <span class="helper-text">
                                            <svg class="helper-icon" fill="none" stroke="#6b7280" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            Formato: 4 letras, 6 números, 6 letras, 2 dígitos
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ============================================ -->
                        <!-- SECCIÓN 3: CONTACTO Y TELÉFONOS -->
                        <!-- ============================================ -->
                        <div class="form-section">
                            <div class="section-header">
                                <div class="section-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                                    <svg class="icon-svg" fill="none" stroke="white" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                </div>
                                <div class="section-title-group">
                                    <h3 class="section-title">Información de Contacto</h3>
                                    <p class="section-subtitle">Teléfonos y correo electrónico</p>
                                </div>
                                <span class="badge-optional">Opcional</span>
                            </div>

                            <div class="form-grid">
                                <!-- Correo Electrónico -->
                                <div class="form-group full-width">
                                    <label class="form-label">
                                        <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                        Correo Electrónico
                                    </label>
                                    <div class="input-wrapper">
                                        <input type="email" v-model="form.email"
                                               @input="clearError('email'); validateEmail()"
                                               class="form-input"
                                               :class="{ 'error': form.errors.email || emailError }"
                                               placeholder="contacto@correo.com"
                                               maxlength="100">
                                        <div class="input-icon">
                                            <svg class="icon-svg-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div v-if="form.errors.email" class="error-message">
                                        <svg class="error-icon" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ form.errors.email }}
                                    </div>
                                    <div v-if="emailError" class="error-message">
                                        <svg class="error-icon" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ emailError }}
                                    </div>
                                </div>

                                <!-- Teléfono Particular -->
                                <div class="form-group">
                                    <label class="form-label">
                                        <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                        </svg>
                                        Teléfono Particular
                                    </label>
                                    <div class="input-wrapper">
                                        <input type="text" v-model="form.telefono_particular"
                                               @input="validatePhone('telefono_particular')"
                                               class="form-input"
                                               :class="{ 'error': phoneErrors.telefono_particular }"
                                               placeholder="7771234567"
                                               maxlength="10"
                                               inputmode="numeric"
                                               pattern="[0-9]*">
                                        <div class="input-icon">
                                            <svg class="icon-svg-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div v-if="phoneErrors.telefono_particular" class="error-message">
                                        <svg class="error-icon" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ phoneErrors.telefono_particular }}
                                    </div>
                                </div>

                                <!-- Teléfono de Trabajo -->
                                <div class="form-group">
                                    <label class="form-label">
                                        <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                        Teléfono de Trabajo
                                    </label>
                                    <div class="input-wrapper">
                                        <input type="text" v-model="form.telefono_trabajo"
                                               @input="validatePhone('telefono_trabajo')"
                                               class="form-input"
                                               :class="{ 'error': phoneErrors.telefono_trabajo }"
                                               placeholder="7779876543"
                                               maxlength="10"
                                               inputmode="numeric"
                                               pattern="[0-9]*">
                                        <div class="input-icon">
                                            <svg class="icon-svg-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
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

                                <!-- Extensión -->
                                <div class="form-group">
                                    <label class="form-label">
                                        <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                        </svg>
                                        Extensión
                                    </label>
                                    <div class="input-wrapper">
                                        <input type="text" v-model="form.extension_trabajo"
                                               @input="validateNumeric('extension_trabajo')"
                                               class="form-input"
                                               :class="{ 'error': numericErrors.extension_trabajo }"
                                               placeholder="Ej: 123"
                                               maxlength="10"
                                               inputmode="numeric"
                                               pattern="[0-9]*">
                                        <div class="input-icon">
                                            <svg class="icon-svg-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div v-if="numericErrors.extension_trabajo" class="error-message">
                                        <svg class="error-icon" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ numericErrors.extension_trabajo }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ============================================ -->
                        <!-- SECCIÓN 4: DIRECCIÓN COMPLETA -->
                        <!-- ============================================ -->
                        <div class="form-section">
                            <div class="section-header">
                                <div class="section-icon" style="background: linear-gradient(135deg, #06b6d4, #0891b2);">
                                    <svg class="icon-svg" fill="none" stroke="white" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                                <div class="section-title-group">
                                    <h3 class="section-title">Dirección</h3>
                                    <p class="section-subtitle">Información completa de ubicación</p>
                                </div>
                                <span class="badge-optional">Opcional</span>
                            </div>

                            <div class="form-grid">
                                <!-- Calle -->
                                <div class="form-group full-width">
                                    <label class="form-label">
                                        <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        </svg>
                                        Calle
                                    </label>
                                    <div class="input-wrapper">
                                        <input type="text" v-model="form.calle"
                                               @input="validateText('calle')"
                                               class="form-input"
                                               :class="{ 'error': textErrors.calle }"
                                               placeholder="Ej: Avenida Reforma"
                                               maxlength="100">
                                        <div class="input-icon">
                                            <svg class="icon-svg-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div v-if="textErrors.calle" class="error-message">
                                        <svg class="error-icon" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ textErrors.calle }}
                                    </div>
                                </div>

                                <!-- Número Exterior -->
                                <div class="form-group">
                                    <label class="form-label">
                                        <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                        </svg>
                                        Número Exterior
                                    </label>
                                    <div class="input-wrapper">
                                        <input type="text" v-model="form.numero_exterior"
                                               @input="validateAlphanumeric('numero_exterior')"
                                               class="form-input"
                                               :class="{ 'error': alphanumericErrors.numero_exterior }"
                                               placeholder="Ej: 123"
                                               maxlength="10">
                                        <div class="input-icon">
                                            <svg class="icon-svg-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div v-if="alphanumericErrors.numero_exterior" class="error-message">
                                        <svg class="error-icon" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ alphanumericErrors.numero_exterior }}
                                    </div>
                                </div>

                                <!-- Número Interior -->
                                <div class="form-group">
                                    <label class="form-label">
                                        <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                        </svg>
                                        Número Interior
                                    </label>
                                    <div class="input-wrapper">
                                        <input type="text" v-model="form.numero_interior"
                                               @input="validateAlphanumeric('numero_interior')"
                                               class="form-input"
                                               :class="{ 'error': alphanumericErrors.numero_interior }"
                                               placeholder="Ej: 2B"
                                               maxlength="10">
                                        <div class="input-icon">
                                            <svg class="icon-svg-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div v-if="alphanumericErrors.numero_interior" class="error-message">
                                        <svg class="error-icon" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ alphanumericErrors.numero_interior }}
                                    </div>
                                </div>

                                <!-- Colonia -->
                                <div class="form-group full-width">
                                    <label class="form-label">
                                        <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        </svg>
                                        Colonia
                                    </label>
                                    <div class="input-wrapper">
                                        <input type="text" v-model="form.colonia"
                                               @input="validateText('colonia')"
                                               class="form-input"
                                               :class="{ 'error': textErrors.colonia }"
                                               placeholder="Ej: Juárez"
                                               maxlength="100">
                                        <div class="input-icon">
                                            <svg class="icon-svg-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div v-if="textErrors.colonia" class="error-message">
                                        <svg class="error-icon" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ textErrors.colonia }}
                                    </div>
                                </div>

                                <!-- Ciudad -->
                                <div class="form-group">
                                    <label class="form-label">
                                        <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                        Ciudad
                                    </label>
                                    <div class="input-wrapper">
                                        <input type="text" v-model="form.ciudad"
                                               @input="validateText('ciudad')"
                                               class="form-input"
                                               :class="{ 'error': textErrors.ciudad }"
                                               placeholder="Ej: Ciudad de México"
                                               maxlength="100">
                                        <div class="input-icon">
                                            <svg class="icon-svg-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div v-if="textErrors.ciudad" class="error-message">
                                        <svg class="error-icon" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ textErrors.ciudad }}
                                    </div>
                                </div>

                                <!-- Municipio -->
                                <div class="form-group">
                                    <label class="form-label">
                                        <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                        Municipio
                                    </label>
                                    <div class="input-wrapper">
                                        <input type="text" v-model="form.municipio"
                                               @input="validateText('municipio')"
                                               class="form-input"
                                               :class="{ 'error': textErrors.municipio }"
                                               placeholder="Ej: Cuauhtémoc"
                                               maxlength="100">
                                        <div class="input-icon">
                                            <svg class="icon-svg-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div v-if="textErrors.municipio" class="error-message">
                                        <svg class="error-icon" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ textErrors.municipio }}
                                    </div>
                                </div>

                                <!-- Estado -->
                                <div class="form-group">
                                    <label class="form-label">
                                        <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                        Estado
                                    </label>
                                    <div class="input-wrapper">
                                        <input type="text" v-model="form.estado"
                                               @input="validateText('estado')"
                                               class="form-input"
                                               :class="{ 'error': textErrors.estado }"
                                               placeholder="Ej: CDMX"
                                               maxlength="100">
                                        <div class="input-icon">
                                            <svg class="icon-svg-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div v-if="textErrors.estado" class="error-message">
                                        <svg class="error-icon" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ textErrors.estado }}
                                    </div>
                                </div>

                                <!-- Código Postal -->
                                <div class="form-group">
                                    <label class="form-label">
                                        <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18M5 18h14M7 6h10"/>
                                        </svg>
                                        Código Postal
                                    </label>
                                    <div class="input-wrapper">
                                        <input type="text" v-model="form.codigo_postal"
                                               @input="validateCodigoPostal()"
                                               class="form-input"
                                               :class="{ 'error': codigoPostalError }"
                                               placeholder="Ej: 06600"
                                               maxlength="5"
                                               inputmode="numeric"
                                               pattern="[0-9]*">
                                        <div class="input-icon">
                                            <svg class="icon-svg-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18M5 18h14M7 6h10"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div v-if="codigoPostalError" class="error-message">
                                        <svg class="error-icon" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ codigoPostalError }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ============================================ -->
                        <!-- SECCIÓN 5: REPRESENTANTE LEGAL -->
                        <!-- ============================================ -->
                        <div class="form-section">
                            <div class="section-header">
                                <div class="section-icon" style="background: linear-gradient(135deg, #ec4899, #db2777);">
                                    <svg class="icon-svg" fill="none" stroke="white" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                                <div class="section-title-group">
                                    <h3 class="section-title">Representante Legal</h3>
                                    <p class="section-subtitle">Información del representante legal de la persona</p>
                                </div>
                                <span class="badge-optional">Opcional</span>
                            </div>

                            <div class="form-grid">
                                <!-- Nombre del Representante -->
                                <div class="form-group full-width">
                                    <label class="form-label">
                                        <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                        Nombre del Representante
                                    </label>
                                    <div class="input-wrapper">
                                        <input type="text" v-model="form.representante_nombre"
                                               @input="clearError('representante_nombre'); validateText('representante_nombre')"
                                               class="form-input"
                                               :class="{ 'error': form.errors.representante_nombre || textErrors.representante_nombre }"
                                               placeholder="Ej: María"
                                               maxlength="100">
                                        <div class="input-icon">
                                            <svg class="icon-svg-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div v-if="form.errors.representante_nombre" class="error-message">
                                        <svg class="error-icon" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ form.errors.representante_nombre }}
                                    </div>
                                    <div v-if="textErrors.representante_nombre" class="error-message">
                                        <svg class="error-icon" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ textErrors.representante_nombre }}
                                    </div>
                                </div>

                                <!-- Apellido Paterno Representante -->
                                <div class="form-group">
                                    <label class="form-label">
                                        <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                        Apellido Paterno
                                    </label>
                                    <div class="input-wrapper">
                                        <input type="text" v-model="form.representante_paterno"
                                               @input="clearError('representante_paterno'); validateText('representante_paterno')"
                                               class="form-input"
                                               :class="{ 'error': form.errors.representante_paterno || textErrors.representante_paterno }"
                                               placeholder="Ej: López"
                                               maxlength="50">
                                        <div class="input-icon">
                                            <svg class="icon-svg-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div v-if="form.errors.representante_paterno" class="error-message">
                                        <svg class="error-icon" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ form.errors.representante_paterno }}
                                    </div>
                                    <div v-if="textErrors.representante_paterno" class="error-message">
                                        <svg class="error-icon" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ textErrors.representante_paterno }}
                                    </div>
                                </div>

                                <!-- Apellido Materno Representante -->
                                <div class="form-group">
                                    <label class="form-label">
                                        <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                        Apellido Materno
                                    </label>
                                    <div class="input-wrapper">
                                        <input type="text" v-model="form.representante_materno"
                                               @input="validateText('representante_materno')"
                                               class="form-input"
                                               :class="{ 'error': textErrors.representante_materno }"
                                               placeholder="Ej: Martínez"
                                               maxlength="50">
                                        <div class="input-icon">
                                            <svg class="icon-svg-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div v-if="textErrors.representante_materno" class="error-message">
                                        <svg class="error-icon" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ textErrors.representante_materno }}
                                    </div>
                                </div>

                                <!-- Fecha de Nacimiento Representante -->
                                <div class="form-group">
                                    <label class="form-label">
                                        <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        Fecha de Nacimiento
                                    </label>
                                    <div class="input-wrapper">
                                        <input type="date" v-model="form.representante_fecha_nacimiento"
                                               @change="clearError('representante_fecha_nacimiento')"
                                               class="form-input date-input"
                                               :class="{ 'error': form.errors.representante_fecha_nacimiento }"
                                               :max="fechaMaxima">
                                        <div class="input-icon">
                                            <svg class="icon-svg-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div v-if="form.errors.representante_fecha_nacimiento" class="error-message">
                                        <svg class="error-icon" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ form.errors.representante_fecha_nacimiento }}
                                    </div>
                                </div>

                                <!-- Sexo Representante -->
                                <div class="form-group full-width">
                                    <label class="form-label">
                                        <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                        </svg>
                                        Sexo del Representante
                                    </label>
                                    <div class="radio-group-horizontal">
                                        <div class="radio-card-mini" 
                                             :class="{ 'selected': form.representante_sexo === 'MASCULINO' }"
                                             @click="form.representante_sexo = 'MASCULINO'">
                                            <span class="radio-emoji">♂️</span>
                                            <div>
                                                <div class="radio-title-mini">Masculino</div>
                                            </div>
                                            <div class="radio-check-mini" v-if="form.representante_sexo === 'MASCULINO'">
                                                <svg class="check-icon" fill="none" stroke="#10b981" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </div>
                                            <input type="radio" v-model="form.representante_sexo" value="MASCULINO" class="radio-input">
                                        </div>

                                        <div class="radio-card-mini" 
                                             :class="{ 'selected': form.representante_sexo === 'FEMENINO' }"
                                             @click="form.representante_sexo = 'FEMENINO'">
                                            <span class="radio-emoji">♀️</span>
                                            <div>
                                                <div class="radio-title-mini">Femenino</div>
                                            </div>
                                            <div class="radio-check-mini" v-if="form.representante_sexo === 'FEMENINO'">
                                                <svg class="check-icon" fill="none" stroke="#10b981" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </div>
                                            <input type="radio" v-model="form.representante_sexo" value="FEMENINO" class="radio-input">
                                        </div>
                                    </div>
                                </div>

                                <!-- Correo Representante -->
                                <div class="form-group">
                                    <label class="form-label">
                                        <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                        Correo del Representante
                                    </label>
                                    <div class="input-wrapper">
                                        <input type="email" v-model="form.representante_email"
                                               @input="validateRepresentanteEmail()"
                                               class="form-input"
                                               :class="{ 'error': representanteEmailError }"
                                               placeholder="representante@correo.com"
                                               maxlength="100">
                                        <div class="input-icon">
                                            <svg class="icon-svg-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div v-if="representanteEmailError" class="error-message">
                                        <svg class="error-icon" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ representanteEmailError }}
                                    </div>
                                </div>

                                <!-- Teléfono Particular Representante -->
                                <div class="form-group">
                                    <label class="form-label">
                                        <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                        </svg>
                                        Teléfono Particular
                                    </label>
                                    <div class="input-wrapper">
                                        <input type="text" v-model="form.representante_telefono_particular"
                                               @input="validatePhone('representante_telefono_particular')"
                                               class="form-input"
                                               :class="{ 'error': phoneErrors.representante_telefono_particular }"
                                               placeholder="7771234567"
                                               maxlength="10"
                                               inputmode="numeric"
                                               pattern="[0-9]*">
                                        <div class="input-icon">
                                            <svg class="icon-svg-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div v-if="phoneErrors.representante_telefono_particular" class="error-message">
                                        <svg class="error-icon" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ phoneErrors.representante_telefono_particular }}
                                    </div>
                                </div>

                                <!-- Teléfono Trabajo Representante -->
                                <div class="form-group">
                                    <label class="form-label">
                                        <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                        Teléfono de Trabajo
                                    </label>
                                    <div class="input-wrapper">
                                        <input type="text" v-model="form.representante_telefono_trabajo"
                                               @input="validatePhone('representante_telefono_trabajo')"
                                               class="form-input"
                                               :class="{ 'error': phoneErrors.representante_telefono_trabajo }"
                                               placeholder="7779876543"
                                               maxlength="10"
                                               inputmode="numeric"
                                               pattern="[0-9]*">
                                        <div class="input-icon">
                                            <svg class="icon-svg-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div v-if="phoneErrors.representante_telefono_trabajo" class="error-message">
                                        <svg class="error-icon" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ phoneErrors.representante_telefono_trabajo }}
                                    </div>
                                </div>

                                <!-- Extensión Representante -->
                                <div class="form-group">
                                    <label class="form-label">
                                        <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                        </svg>
                                        Extensión
                                    </label>
                                    <div class="input-wrapper">
                                        <input type="text" v-model="form.representante_extension_trabajo"
                                               @input="validateNumeric('representante_extension_trabajo')"
                                               class="form-input"
                                               :class="{ 'error': numericErrors.representante_extension_trabajo }"
                                               placeholder="Ej: 123"
                                               maxlength="10"
                                               inputmode="numeric"
                                               pattern="[0-9]*">
                                        <div class="input-icon">
                                            <svg class="icon-svg-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div v-if="numericErrors.representante_extension_trabajo" class="error-message">
                                        <svg class="error-icon" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ numericErrors.representante_extension_trabajo }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ============================================ -->
                        <!-- SECCIÓN 6: NOTAS -->
                        <!-- ============================================ -->
                        <div class="form-section">
                            <div class="section-header">
                                <div class="section-icon" style="background: linear-gradient(135deg, #10b981, #059669);">
                                    <svg class="icon-svg" fill="none" stroke="white" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                </div>
                                <div class="section-title-group">
                                    <h3 class="section-title">Notas Adicionales</h3>
                                    <p class="section-subtitle">Información complementaria sobre la persona</p>
                                </div>
                                <span class="badge-optional">Opcional</span>
                            </div>

                            <div class="form-grid">
                                <div class="form-group full-width">
                                    <label class="form-label">
                                        <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                        </svg>
                                        Notas
                                    </label>
                                    <div class="input-wrapper">
                                        <textarea v-model="form.notas"
                                                  class="form-textarea"
                                                  rows="4"
                                                  placeholder="Información adicional sobre la persona..."
                                                  maxlength="500"></textarea>
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
                                <Link :href="route('personas.index')" class="btn btn-cancel">
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
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                                    </svg>
                                    {{ form.processing ? 'Guardando...' : 'Actualizar Persona' }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <AlertModal ref="alertRef" />
    </AppLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import AlertModal from '@/Components/AlertModal.vue';
import axios from 'axios';

// ============================================
// PROPS
// ============================================
const props = defineProps({
    persona: {
        type: Object,
        required: true
    }
});

const alertRef = ref(null);

// ============================================
// ESTADOS DE VALIDACIÓN ADICIONALES
// ============================================
const emailError = ref('');
const rfcError = ref('');
const curpError = ref('');
const codigoPostalError = ref('');
const representanteEmailError = ref('');
const edadError = ref('');
const generandoRFC = ref(false);

const textErrors = ref({
    Nombre: '',
    Paterno: '',
    Materno: '',
    calle: '',
    colonia: '',
    ciudad: '',
    municipio: '',
    estado: '',
    representante_nombre: '',
    representante_paterno: '',
    representante_materno: ''
});

const numericErrors = ref({
    extension_trabajo: '',
    representante_extension_trabajo: ''
});

const alphanumericErrors = ref({
    numero_exterior: '',
    numero_interior: ''
});

const phoneErrors = ref({
    telefono_particular: '',
    telefono_trabajo: '',
    representante_telefono_particular: '',
    representante_telefono_trabajo: ''
});

// ============================================
// FECHA MÁXIMA (18 años atrás)
// ============================================
const fechaMaxima = computed(() => {
    const fecha = new Date();
    fecha.setFullYear(fecha.getFullYear() - 18);
    return fecha.toISOString().split('T')[0];
});

// ============================================
// FUNCIÓN PARA FORMATEAR FECHA
// ============================================
const formatDate = (date) => {
    if (!date) return '';
    if (typeof date === 'string' && date.match(/^\d{4}-\d{2}-\d{2}$/)) {
        return date;
    }
    const d = new Date(date);
    if (isNaN(d.getTime())) return '';
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

// ============================================
// FORMULARIO
// ============================================
const form = useForm({
    tipo_persona: props.persona?.tipo_persona || 'FISICA',
    Nombre: props.persona?.Nombre || '',
    Paterno: props.persona?.Paterno || '',
    Materno: props.persona?.Materno || '',
    Fecha_nacimiento: formatDate(props.persona?.Fecha_nacimiento) || '',
    sexo: props.persona?.sexo || 'NO_ESPECIFICADO',
    rfc: props.persona?.rfc || '',
    curp: props.persona?.curp || '',
    email: props.persona?.email || '',
    telefono_particular: props.persona?.telefono_particular || '',
    telefono_trabajo: props.persona?.telefono_trabajo || '',
    extension_trabajo: props.persona?.extension_trabajo || '',
    direccion: props.persona?.direccion || '',
    calle: props.persona?.calle || '',
    numero_exterior: props.persona?.numero_exterior || '',
    numero_interior: props.persona?.numero_interior || '',
    colonia: props.persona?.colonia || '',
    ciudad: props.persona?.ciudad || '',
    municipio: props.persona?.municipio || '',
    estado: props.persona?.estado || '',
    codigo_postal: props.persona?.codigo_postal || '',
    representante_nombre: props.persona?.representante_nombre || '',
    representante_paterno: props.persona?.representante_paterno || '',
    representante_materno: props.persona?.representante_materno || '',
    representante_fecha_nacimiento: formatDate(props.persona?.representante_fecha_nacimiento) || '',
    representante_sexo: props.persona?.representante_sexo || 'NO_ESPECIFICADO',
    representante_email: props.persona?.representante_email || '',
    representante_telefono_particular: props.persona?.representante_telefono_particular || '',
    representante_telefono_trabajo: props.persona?.representante_telefono_trabajo || '',
    representante_extension_trabajo: props.persona?.representante_extension_trabajo || '',
    notas: props.persona?.notas || '',
});

// ============================================
// VALIDACIÓN DE TEXTO (SOLO LETRAS Y ESPACIOS)
// ============================================
const validateText = (field) => {
    const value = form[field];
    if (!value || value.trim() === '') {
        textErrors.value[field] = '';
        return;
    }
    const textRegex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/;
    if (!textRegex.test(value)) {
        textErrors.value[field] = 'Solo se permiten letras y espacios';
    } else {
        textErrors.value[field] = '';
    }
};

// ============================================
// VALIDACIÓN DE NÚMEROS
// ============================================
const validateNumeric = (field) => {
    const value = form[field];
    if (!value || value.trim() === '') {
        numericErrors.value[field] = '';
        return;
    }
    if (!/^\d+$/.test(value)) {
        numericErrors.value[field] = 'Solo se permiten números';
    } else {
        numericErrors.value[field] = '';
    }
};

// ============================================
// VALIDACIÓN ALFANUMÉRICA
// ============================================
const validateAlphanumeric = (field) => {
    const value = form[field];
    if (!value || value.trim() === '') {
        alphanumericErrors.value[field] = '';
        return;
    }
    if (!/^[a-zA-Z0-9]+$/.test(value)) {
        alphanumericErrors.value[field] = 'Solo se permiten letras y números (sin espacios)';
    } else {
        alphanumericErrors.value[field] = '';
    }
};

// ============================================
// VALIDACIÓN DE CÓDIGO POSTAL
// ============================================
const validateCodigoPostal = () => {
    const value = form.codigo_postal;
    if (!value || value.trim() === '') {
        codigoPostalError.value = '';
        return;
    }
    if (!/^\d+$/.test(value)) {
        codigoPostalError.value = 'Solo se permiten números';
    } else if (value.length !== 5) {
        codigoPostalError.value = 'El código postal debe tener 5 dígitos';
    } else {
        codigoPostalError.value = '';
    }
};

// ============================================
// VALIDACIÓN DE CURP
// ============================================
const validateCURP = () => {
    const curp = form.curp;
    if (!curp || curp.trim() === '') {
        curpError.value = '';
        return;
    }
    const curpLimpio = curp.trim().replace(/\s/g, '').toUpperCase();
    if (form.curp !== curpLimpio) {
        form.curp = curpLimpio;
        return;
    }
    const curpRegex = /^[A-Z]{4}[0-9]{6}[A-Z]{6}[0-9A-Z]{2}$/;
    if (curpLimpio.length !== 18) {
        curpError.value = `La CURP debe tener 18 caracteres (tiene ${curpLimpio.length})`;
        return;
    }
    if (!curpRegex.test(curpLimpio)) {
        curpError.value = 'Formato de CURP inválido. Debe ser: 4 letras, 6 números, 6 letras, 2 dígitos. Ejemplo: GOPM900415MDFRRA00';
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
    let rfcRegex;
    if (form.tipo_persona === 'FISICA') {
        rfcRegex = /^[A-ZÑ&]{4}[0-9]{6}[A-Z0-9]{3}$/;
        rfcError.value = rfcRegex.test(rfc) ? '' : 'RFC de persona física inválido (4 letras, 6 números, 3 caracteres)';
    } else {
        rfcRegex = /^[A-ZÑ&]{3}[0-9]{6}[A-Z0-9]{3}$/;
        rfcError.value = rfcRegex.test(rfc) ? '' : 'RFC de persona moral inválido (3 letras, 6 números, 3 caracteres)';
    }
};

// ============================================
// VALIDACIÓN DE EDAD
// ============================================
const validateEdad = () => {
    const fecha = form.Fecha_nacimiento;
    if (!fecha) {
        edadError.value = '';
        return;
    }
    const fechaNac = new Date(fecha);
    const hoy = new Date();
    let edad = hoy.getFullYear() - fechaNac.getFullYear();
    const mes = hoy.getMonth() - fechaNac.getMonth();
    if (mes < 0 || (mes === 0 && hoy.getDate() < fechaNac.getDate())) edad--;
    edadError.value = edad < 18 ? `La persona debe ser mayor de edad (tiene ${edad} años)` : '';
};

// ============================================
// VALIDACIÓN DE EMAIL
// ============================================
const validateEmail = () => {
    const email = form.email;
    if (!email) { emailError.value = ''; return; }
    const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    emailError.value = emailRegex.test(email) ? '' : 'Ingrese un correo electrónico válido (ejemplo@dominio.com)';
};

// ============================================
// VALIDACIÓN DE EMAIL REPRESENTANTE
// ============================================
const validateRepresentanteEmail = () => {
    const email = form.representante_email;
    if (!email) { representanteEmailError.value = ''; return; }
    const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    representanteEmailError.value = emailRegex.test(email) ? '' : 'Ingrese un correo electrónico válido (ejemplo@dominio.com)';
};

// ============================================
// VALIDACIÓN DE TELÉFONO
// ============================================
const validatePhone = (field) => {
    const phone = form[field];
    if (!phone || phone.trim() === '') {
        phoneErrors.value[field] = '';
        return;
    }
    const cleaned = phone.replace(/\D/g, '');
    if (cleaned !== phone) {
        form[field] = cleaned;
        return;
    }
    if (phone.length < 10) {
        phoneErrors.value[field] = 'El teléfono debe tener 10 dígitos';
    } else if (phone.length > 10) {
        phoneErrors.value[field] = 'El teléfono no puede tener más de 10 dígitos';
    } else {
        phoneErrors.value[field] = '';
    }
};

// ============================================
// LIMPIAR ERRORES
// ============================================
const clearError = (field) => {
    if (form.errors[field]) delete form.errors[field];
};

// ============================================
// GENERACIÓN DE RFC
// ============================================
const puedeGenerarRFC = computed(() => {
    return form.Nombre && form.Paterno && form.Fecha_nacimiento && !edadError.value;
});

const autoGenerarRFC = () => {
    if (puedeGenerarRFC.value && !form.rfc) {
        generarRFC();
    }
};

const generarRFC = async () => {
    if (!puedeGenerarRFC.value) {
        alertRef.value?.show({
            type: 'warning',
            title: 'Datos incompletos',
            message: 'Complete el nombre, apellido paterno y fecha de nacimiento para generar el RFC automáticamente.',
            buttonText: 'Entendido'
        });
        return;
    }

    generandoRFC.value = true;

    try {
        const response = await axios.post(route('personas.generar-rfc'), {
            tipo_persona: form.tipo_persona,
            Nombre: form.Nombre,
            Paterno: form.Paterno,
            Materno: form.Materno,
            Fecha_nacimiento: form.Fecha_nacimiento
        });

        if (response.data.success) {
            form.rfc = response.data.rfc;
            clearError('rfc');
            rfcError.value = '';
        }
    } catch (error) {
        const message = error.response?.data?.message || 'Error al generar el RFC';
        alertRef.value?.show({
            type: 'error',
            title: 'Error',
            message: message,
            buttonText: 'Entendido'
        });
    } finally {
        generandoRFC.value = false;
    }
};

// ============================================
// EVENTOS
// ============================================
const onTipoChange = () => {
    form.representante_nombre = '';
    form.representante_paterno = '';
    form.representante_materno = '';
    form.representante_fecha_nacimiento = '';
    form.representante_sexo = 'NO_ESPECIFICADO';
    form.representante_email = '';
    form.representante_telefono_particular = '';
    form.representante_telefono_trabajo = '';
    form.representante_extension_trabajo = '';
    
    curpError.value = '';
    rfcError.value = '';
    
    if (puedeGenerarRFC.value) {
        generarRFC();
    }
};

// ============================================
// COMPUTED PARA VALIDAR EL FORMULARIO COMPLETO
// ============================================
const isFormValid = computed(() => {
    const requiredFields = ['tipo_persona', 'Nombre', 'Paterno', 'Fecha_nacimiento', 'sexo', 'rfc'];
    const hasRequiredErrors = requiredFields.some(field => {
        const val = form[field];
        return !val || val.toString().trim().length === 0;
    });
    if (hasRequiredErrors) return false;
    
    const textFields = ['Nombre', 'Paterno', 'Materno'];
    const hasTextErrors = textFields.some(field => textErrors.value[field]);
    if (hasTextErrors) return false;
    
    if (emailError.value) return false;
    if (rfcError.value) return false;
    if (curpError.value) return false;
    if (edadError.value) return false;
    if (codigoPostalError.value) return false;
    if (representanteEmailError.value) return false;
    
    if (phoneErrors.value.telefono_particular) return false;
    if (phoneErrors.value.telefono_trabajo) return false;
    if (phoneErrors.value.representante_telefono_particular) return false;
    if (phoneErrors.value.representante_telefono_trabajo) return false;
    
    if (numericErrors.value.extension_trabajo) return false;
    if (numericErrors.value.representante_extension_trabajo) return false;
    
    if (alphanumericErrors.value.numero_exterior) return false;
    if (alphanumericErrors.value.numero_interior) return false;
    
    return true;
});

// ============================================
// COMPUTED PARA EL PROGRESS Y ESTADO
// ============================================
const hasErrors = computed(() => {
    let errorCount = Object.keys(form.errors).length;
    if (emailError.value) errorCount++;
    if (rfcError.value) errorCount++;
    if (curpError.value) errorCount++;
    if (edadError.value) errorCount++;
    if (codigoPostalError.value) errorCount++;
    if (representanteEmailError.value) errorCount++;
    
    Object.values(textErrors.value).forEach(err => { if (err) errorCount++; });
    Object.values(numericErrors.value).forEach(err => { if (err) errorCount++; });
    Object.values(alphanumericErrors.value).forEach(err => { if (err) errorCount++; });
    Object.values(phoneErrors.value).forEach(err => { if (err) errorCount++; });
    
    return errorCount > 0;
});

const errorCount = computed(() => {
    let count = Object.keys(form.errors).length;
    if (emailError.value) count++;
    if (rfcError.value) count++;
    if (curpError.value) count++;
    if (edadError.value) count++;
    if (codigoPostalError.value) count++;
    if (representanteEmailError.value) count++;
    
    Object.values(textErrors.value).forEach(err => { if (err) count++; });
    Object.values(numericErrors.value).forEach(err => { if (err) count++; });
    Object.values(alphanumericErrors.value).forEach(err => { if (err) count++; });
    Object.values(phoneErrors.value).forEach(err => { if (err) count++; });
    
    return count;
});

const requiredFields = computed(() => {
    return ['tipo_persona', 'Nombre', 'Paterno', 'Fecha_nacimiento', 'sexo', 'rfc'];
});

const progressPercentage = computed(() => {
    const total = requiredFields.value.length;
    const filled = requiredFields.value.filter(f => {
        const val = form[f];
        return val && val.toString().trim().length > 0;
    }).length;
    return total > 0 ? (filled / total) * 100 : 0;
});

const isComplete = computed(() => {
    return progressPercentage.value === 100 && !hasErrors.value;
});

const statusClass = computed(() => {
    if (hasErrors.value) return 'status-error';
    if (isComplete.value) return 'status-success';
    return 'status-progress';
});

// ============================================
// WATCHERS PARA VALIDACIÓN EN TIEMPO REAL
// ============================================
watch(() => form.email, () => {
    if (form.email) validateEmail();
}, { immediate: true });

watch(() => form.rfc, () => {
    if (form.rfc) validateRFC();
}, { immediate: true });

watch(() => form.curp, () => {
    if (form.curp) validateCURP();
}, { immediate: true });

watch(() => form.telefono_particular, () => {
    if (form.telefono_particular) validatePhone('telefono_particular');
}, { immediate: true });

watch(() => form.telefono_trabajo, () => {
    if (form.telefono_trabajo) validatePhone('telefono_trabajo');
}, { immediate: true });

watch(() => form.codigo_postal, () => {
    if (form.codigo_postal) validateCodigoPostal();
}, { immediate: true });

watch(() => form.representante_email, () => {
    if (form.representante_email) validateRepresentanteEmail();
}, { immediate: true });

// ============================================
// SUBMIT
// ============================================
const submit = () => {
    if (form.email) validateEmail();
    if (form.rfc) validateRFC();
    if (form.curp) validateCURP();
    if (form.telefono_particular) validatePhone('telefono_particular');
    if (form.telefono_trabajo) validatePhone('telefono_trabajo');
    if (form.Fecha_nacimiento) validateEdad();
    if (form.codigo_postal) validateCodigoPostal();
    if (form.representante_email) validateRepresentanteEmail();
    
    ['Nombre', 'Paterno', 'Materno'].forEach(field => validateText(field));
    
    if (!isFormValid.value) {
        alertRef.value?.show({
            type: 'error',
            title: 'Error de validación',
            message: 'Por favor, corrija los errores en el formulario antes de continuar.',
            buttonText: 'Entendido'
        });
        return;
    }
    
    form.put(route('personas.update', props.persona.id_persona), {
        onSuccess: () => {
            // El controlador redirige con flash
        },
        onError: (errors) => {
            if (errors.rfc) {
                alertRef.value?.show({
                    type: 'error',
                    title: 'RFC duplicado',
                    message: 'El RFC que ingresaste ya está registrado en otra persona. Por favor, verifica el RFC.',
                    buttonText: 'Entendido'
                });
            } else {
                alertRef.value?.show({
                    type: 'error',
                    title: 'Error al actualizar',
                    message: 'Ocurrió un error al actualizar la persona. Verifique los datos e intente nuevamente.',
                    buttonText: 'Intentar de nuevo'
                });
            }
        }
    });
};
</script>


<style scoped>
/* ===== TODOS LOS ESTILOS DE CREATE ===== */
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

.page-content {
    padding: 1.5rem 0;
}

.container-custom {
    max-width: 72rem;
    margin: 0 auto;
    padding: 0 1.5rem;
}

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

.form-label {
    font-size: 0.85rem;
    font-weight: 600;
    color: #374151;
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
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

.helper-label {
    font-size: 0.7rem;
    font-weight: 400;
    color: #6b7280;
    margin-left: 4px;
}

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

.input-wrapper {
    position: relative;
}

.form-input {
    width: 100%;
    padding: 10px 120px 10px 14px;
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

.form-input.date-input {
    padding-right: 40px;
    text-transform: none;
}

.form-textarea {
    width: 100%;
    padding: 10px 14px;
    font-size: 0.9rem;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    background: white;
    color: #1f2937;
    transition: all 0.3s ease;
    outline: none;
    font-family: inherit;
    resize: vertical;
    min-height: 80px;
}

.form-textarea:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.form-textarea:hover:not(:focus) {
    border-color: #9ca3af;
}

.form-textarea.error {
    border-color: #ef4444;
}

.form-textarea.error:focus {
    box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
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

.input-wrapper:focus-within .input-icon {
    color: #667eea;
}

.icon-svg-sm {
    width: 18px;
    height: 18px;
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

.input-helper {
    margin-top: 4px;
}

.helper-text {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 0.75rem;
    color: #6b7280;
}

.helper-icon {
    width: 14px;
    height: 14px;
    flex-shrink: 0;
}

.btn-generate-rfc {
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    padding: 6px 16px;
    background: linear-gradient(135deg, #8b5cf6, #7c3aed);
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    white-space: nowrap;
    z-index: 2;
    height: 32px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.btn-generate-rfc:hover:not(:disabled) {
    transform: translateY(-50%) scale(1.05);
    box-shadow: 0 4px 12px rgba(139, 92, 246, 0.4);
}

.btn-generate-rfc:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: translateY(-50%);
}

.spinner-small {
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spinner 0.6s linear infinite;
}

@keyframes spinner {
    to { transform: rotate(360deg); }
}

.toggle-group {
    display: flex;
    gap: 12px;
}

.toggle-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 24px;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    background: white;
    color: #6b7280;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.toggle-btn:hover {
    border-color: #9ca3af;
    transform: translateY(-2px);
}

.toggle-btn.active {
    border-color: #10b981;
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
    color: #065f46;
}

.toggle-btn.active .toggle-icon {
    color: #10b981;
}

.toggle-icon {
    width: 18px;
    height: 18px;
}

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
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
}

.btn-submit:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 24px rgba(16, 185, 129, 0.35);
}

.btn-submit:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.spinner-border {
    display: inline-block;
    width: 1rem;
    height: 1rem;
    border: 0.2em solid currentColor;
    border-right-color: transparent;
    border-radius: 50%;
    animation: spinner 0.75s linear infinite;
}

.me-2 {
    margin-right: 0.5rem;
}

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
    .form-input {
        padding-right: 110px;
    }
    .btn-generate-rfc {
        font-size: 0.65rem;
        padding: 4px 10px;
        right: 6px;
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
    .form-input {
        padding-right: 90px;
        font-size: 0.8rem;
    }
    .btn-generate-rfc {
        font-size: 0.6rem;
        padding: 3px 8px;
        height: 28px;
    }
}
</style>