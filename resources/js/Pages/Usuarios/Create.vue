<template>
    <AppLayout title="Nuevo Usuario">
        <template #header>
            <div class="header-wrapper">
                <div class="header-left">
                    <Link :href="route('usuarios.index')" class="btn-back">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                    </Link>
                    <div class="header-content">
                        <h2 class="header-title">👤 Nuevo Usuario</h2>
                        <p class="header-subtitle">Complete el formulario para registrar un nuevo usuario en el sistema</p>
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
                    <form @submit.prevent="submit" id="usuarioForm" novalidate>
                        <!-- ============================================ -->
                        <!-- SECCIÓN 1: DATOS PERSONALES -->
                        <!-- ============================================ -->
                        <div class="form-section">
                            <div class="section-header">
                                <div class="section-icon" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                                    <svg class="icon-svg" fill="none" stroke="white" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                                <div class="section-title-group">
                                    <h3 class="section-title">Datos Personales</h3>
                                    <p class="section-subtitle">Información principal del usuario</p>
                                </div>
                            </div>

                            <div class="form-grid">
                                <!-- Nombre Completo -->
                                <div class="form-group full-width">
                                    <label class="form-label">
                                        <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        Nombre Completo <span class="required-star">*</span>
                                    </label>
                                    <div class="input-wrapper">
                                        <input type="text" v-model="form.nombre_completo"
                                               @input="clearError('nombre_completo')"
                                               class="form-input"
                                               :class="{ 'error': form.errors.nombre_completo }"
                                               placeholder="Ej: Juan Pérez García"
                                               maxlength="100">
                                        <div class="input-icon">
                                            <svg class="icon-svg-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div v-if="form.errors.nombre_completo" class="error-message">
                                        <svg class="error-icon" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ form.errors.nombre_completo }}
                                    </div>
                                </div>

                                <!-- Nombre Usuario -->
                                <div class="form-group">
                                    <label class="form-label">
                                        <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                        </svg>
                                        Nombre de Usuario <span class="required-star">*</span>
                                    </label>
                                    <div class="input-wrapper">
                                        <input type="text" v-model="form.nombre_usuario"
                                               @input="form.nombre_usuario = form.nombre_usuario.toLowerCase(); clearError('nombre_usuario')"
                                               class="form-input"
                                               :class="{ 'error': form.errors.nombre_usuario }"
                                               placeholder="Ej: jperez"
                                               maxlength="50">
                                        <div class="input-icon">
                                            <svg class="icon-svg-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div v-if="form.errors.nombre_usuario" class="error-message">
                                        <svg class="error-icon" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ form.errors.nombre_usuario }}
                                    </div>
                                </div>

                                <!-- Email -->
                                <div class="form-group">
                                    <label class="form-label">
                                        <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                        Correo Electrónico <span class="required-star">*</span>
                                    </label>
                                    <div class="input-wrapper">
                                        <input type="email" v-model="form.email"
                                               @input="clearError('email'); validateEmail()"
                                               class="form-input"
                                               :class="{ 'error': form.errors.email || emailError }"
                                               placeholder="usuario@correo.com"
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

                                <!-- Teléfono -->
                                <div class="form-group">
                                    <label class="form-label">
                                        <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                        </svg>
                                        Teléfono
                                    </label>
                                    <div class="input-wrapper">
                                        <input type="text" v-model="form.telefono"
                                               @input="validatePhone()"
                                               class="form-input"
                                               :class="{ 'error': phoneError }"
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
                                    <div v-if="phoneError" class="error-message">
                                        <svg class="error-icon" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ phoneError }}
                                    </div>
                                    <div v-if="form.errors.telefono" class="error-message">
                                        <svg class="error-icon" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ form.errors.telefono }}
                                    </div>
                                </div>

                                <!-- Tipo de Usuario -->
                                <div class="form-group">
                                    <label class="form-label">
                                        <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                                        </svg>
                                        Tipo de Usuario <span class="required-star">*</span>
                                    </label>
                                    <div class="input-wrapper">
                                        <select v-model="form.tipo_usuario" 
                                                @change="clearError('tipo_usuario')"
                                                class="form-input form-select"
                                                :class="{ 'error': form.errors.tipo_usuario }">
                                            <option value="">Selecciona un tipo</option>
                                            <option v-for="(label, value) in tipos" :key="value" :value="value">
                                                {{ label }}
                                            </option>
                                        </select>
                                        <div class="input-icon">
                                            <svg class="icon-svg-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div v-if="form.errors.tipo_usuario" class="error-message">
                                        <svg class="error-icon" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ form.errors.tipo_usuario }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ============================================ -->
                        <!-- SECCIÓN 2: CREDENCIALES DE ACCESO -->
                        <!-- ============================================ -->
                        <div class="form-section">
                            <div class="section-header">
                                <div class="section-icon" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">
                                    <svg class="icon-svg" fill="none" stroke="white" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                </div>
                                <div class="section-title-group">
                                    <h3 class="section-title">Credenciales de Acceso</h3>
                                    <p class="section-subtitle">Configure la contraseña del usuario</p>
                                </div>
                                <span class="badge-required">Obligatorio</span>
                            </div>

                            <div class="form-grid">
                                <!-- Contraseña -->
                                <div class="form-group full-width">
                                    <label class="form-label">
                                        <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                        Contraseña <span class="required-star">*</span>
                                    </label>
                                    <div class="input-wrapper">
                                        <input :type="showPassword ? 'text' : 'password'" 
                                               v-model="form.password"
                                               @input="clearError('password'); validatePassword(); validatePasswordMatch()"
                                               class="form-input"
                                               :class="{ 'error': form.errors.password || passwordError }"
                                               placeholder="Contraseña (mínimo 8 caracteres)">
                                        <div class="input-icon password-toggle" @click="showPassword = !showPassword" style="cursor: pointer; pointer-events: auto;">
                                            <svg v-if="showPassword" class="icon-svg-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            <svg v-else class="icon-svg-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                            </svg>
                                        </div>
                                        <button type="button" 
                                                @click="generatePassword" 
                                                class="btn-generate-password"
                                                title="Generar contraseña aleatoria">
                                            <svg class="icon-svg-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                            </svg>
                                            Generar
                                        </button>
                                    </div>
                                    <div v-if="form.errors.password" class="error-message">
                                        <svg class="error-icon" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ form.errors.password }}
                                    </div>
                                    <div v-if="passwordError" class="error-message">
                                        <svg class="error-icon" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ passwordError }}
                                    </div>
                                    <div class="password-requirements">
                                        <span class="requirement" :class="{ 'met': passwordLength }">
                                            <svg class="req-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            Mínimo 8 caracteres
                                        </span>
                                        <span class="requirement" :class="{ 'met': hasUpperCase }">
                                            <svg class="req-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            Mayúscula
                                        </span>
                                        <span class="requirement" :class="{ 'met': hasLowerCase }">
                                            <svg class="req-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            Minúscula
                                        </span>
                                        <span class="requirement" :class="{ 'met': hasNumber }">
                                            <svg class="req-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            Número
                                        </span>
                                    </div>
                                </div>

                                <!-- Confirmar Contraseña -->
                                <div class="form-group full-width">
                                    <label class="form-label">
                                        <svg class="label-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                        Confirmar Contraseña <span class="required-star">*</span>
                                    </label>
                                    <div class="input-wrapper">
                                        <input :type="showConfirmPassword ? 'text' : 'password'" 
                                               v-model="form.password_confirmation"
                                               @input="clearError('password_confirmation'); validatePasswordMatch()"
                                               class="form-input"
                                               :class="{ 'error': passwordMatchError || form.errors.password_confirmation }"
                                               placeholder="Confirme la contraseña">
                                        <div class="input-icon password-toggle" @click="showConfirmPassword = !showConfirmPassword" style="cursor: pointer; pointer-events: auto;">
                                            <svg v-if="showConfirmPassword" class="icon-svg-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            <svg v-else class="icon-svg-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div v-if="form.errors.password_confirmation" class="error-message">
                                        <svg class="error-icon" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ form.errors.password_confirmation }}
                                    </div>
                                    <div v-if="passwordMatchError" class="error-message">
                                        <svg class="error-icon" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ passwordMatchError }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ============================================ -->
                        <!-- SECCIÓN 3: EMPRESAS ASIGNADAS -->
                        <!-- ============================================ -->
                        <div class="form-section">
                            <div class="section-header">
                                <div class="section-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                                    <svg class="icon-svg" fill="none" stroke="white" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                </div>
                                <div class="section-title-group">
                                    <h3 class="section-title">Empresas Asignadas</h3>
                                    <p class="section-subtitle">Seleccione las empresas a las que tendrá acceso el usuario</p>
                                </div>
                                <span class="badge-optional">Opcional</span>
                            </div>

                            <div class="form-grid">
                                <div class="form-group full-width">
                                    <!-- Buscador de empresas -->
                                    <div class="search-wrapper" v-if="empresas.length > 5">
                                        <div class="input-wrapper">
                                            <input type="text" v-model="searchEmpresa"
                                                   class="form-input"
                                                   placeholder="🔍 Buscar empresa...">
                                            <div class="input-icon">
                                                <svg class="icon-svg-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="empresas-grid">
                                        <div v-for="empresa in empresasFiltradas" :key="empresa.id" 
                                             class="empresa-card"
                                             :class="{ 'selected': isEmpresaSelected(empresa.id) }"
                                             @click="toggleEmpresa(empresa.id)">
                                            <div class="empresa-check">
                                                <svg v-if="isEmpresaSelected(empresa.id)" 
                                                     class="check-icon" fill="none" stroke="#10b981" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                <div v-else class="empty-check"></div>
                                            </div>
                                            <div class="empresa-info">
                                                <span class="empresa-nombre">{{ empresa.nombre_empresa }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Contador de seleccionadas -->
                                    <div class="selected-count" v-if="form.empresas.length > 0">
                                        <span class="count-badge">
                                            {{ form.empresas.length }} empresa{{ form.empresas.length > 1 ? 's' : '' }} seleccionada{{ form.empresas.length > 1 ? 's' : '' }}
                                        </span>
                                    </div>
                                    
                                    <div v-if="form.errors.empresas" class="error-message">
                                        <svg class="error-icon" fill="none" stroke="#ef4444" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ form.errors.empresas }}
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
                                <Link :href="route('usuarios.index')" class="btn btn-cancel">
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
                                    {{ form.processing ? 'Guardando...' : 'Crear Usuario' }}
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

// Props
const props = defineProps({
    empresas: {
        type: Array,
        default: () => []
    },
    tipos: {
        type: Object,
        default: () => ({})
    }
});

// Referencia al componente de alerta
const alertRef = ref(null);

// Validaciones adicionales
const emailError = ref('');
const passwordError = ref('');
const phoneError = ref('');
const passwordMatchError = ref('');

// Mostrar/ocultar contraseña
const showPassword = ref(false);
const showConfirmPassword = ref(false);

// Búsqueda de empresas
const searchEmpresa = ref('');

// Inicializar el formulario
const form = useForm({
    nombre_completo: '',
    nombre_usuario: '',
    email: '',
    telefono: '',
    tipo_usuario: '',
    password: '',
    password_confirmation: '',
    empresas: []
});

// ============================================
// FUNCIONES PARA GENERAR CONTRASEÑA
// ============================================

const generatePassword = () => {
    const length = 12;
    const charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+-=';
    let password = '';
    
    // Asegurar al menos una mayúscula, una minúscula y un número
    password += 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'[Math.floor(Math.random() * 26)];
    password += 'abcdefghijklmnopqrstuvwxyz'[Math.floor(Math.random() * 26)];
    password += '0123456789'[Math.floor(Math.random() * 10)];
    
    // Completar el resto
    for (let i = 3; i < length; i++) {
        password += charset[Math.floor(Math.random() * charset.length)];
    }
    
    // Mezclar la contraseña
    password = password.split('').sort(() => Math.random() - 0.5).join('');
    
    form.password = password;
    form.password_confirmation = password;
    
    // Validar después de generar
    validatePassword();
    validatePasswordMatch();
};

// ============================================
// FUNCIONES DE VALIDACIÓN
// ============================================

const validateEmail = () => {
    const email = form.email;
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

const validatePassword = () => {
    const password = form.password;
    if (!password) {
        passwordError.value = '';
        return;
    }
    
    if (password.length < 8) {
        passwordError.value = 'La contraseña debe tener al menos 8 caracteres';
    } else if (!/[A-Z]/.test(password)) {
        passwordError.value = 'La contraseña debe tener al menos una mayúscula';
    } else if (!/[a-z]/.test(password)) {
        passwordError.value = 'La contraseña debe tener al menos una minúscula';
    } else if (!/[0-9]/.test(password)) {
        passwordError.value = 'La contraseña debe tener al menos un número';
    } else {
        passwordError.value = '';
    }
};

const validatePasswordMatch = () => {
    if (!form.password_confirmation) {
        passwordMatchError.value = '';
        return;
    }
    
    if (form.password !== form.password_confirmation) {
        passwordMatchError.value = 'Las contraseñas no coinciden';
    } else {
        passwordMatchError.value = '';
    }
};

const validatePhone = () => {
    const phone = form.telefono;
    if (!phone) {
        phoneError.value = '';
        return;
    }
    
    if (!/^\d+$/.test(phone)) {
        phoneError.value = 'Solo se permiten números';
    } else if (phone.length !== 10) {
        phoneError.value = 'El teléfono debe tener 10 dígitos';
    } else {
        phoneError.value = '';
    }
};

const clearError = (field) => {
    if (form.errors[field]) {
        delete form.errors[field];
    }
};

// ============================================
// FUNCIONES PARA EMPRESAS
// ============================================

const isEmpresaSelected = (id) => {
    return form.empresas.includes(id);
};

const toggleEmpresa = (id) => {
    const index = form.empresas.indexOf(id);
    if (index > -1) {
        form.empresas.splice(index, 1);
    } else {
        form.empresas.push(id);
    }
};

const empresasFiltradas = computed(() => {
    if (!searchEmpresa.value) return props.empresas;
    const search = searchEmpresa.value.toLowerCase();
    return props.empresas.filter(emp => 
        emp.nombre_empresa.toLowerCase().includes(search) ||
        emp.id.toString().includes(search)
    );
});

// ============================================
// COMPUTED PARA VALIDACIÓN DEL FORMULARIO
// ============================================

const passwordLength = computed(() => {
    return form.password && form.password.length >= 8;
});

const hasUpperCase = computed(() => {
    return form.password && /[A-Z]/.test(form.password);
});

const hasLowerCase = computed(() => {
    return form.password && /[a-z]/.test(form.password);
});

const hasNumber = computed(() => {
    return form.password && /[0-9]/.test(form.password);
});

const isFormValid = computed(() => {
    // Campos requeridos
    const requiredFields = ['nombre_completo', 'nombre_usuario', 'email', 'tipo_usuario', 'password', 'password_confirmation'];
    
    // Verificar campos vacíos
    const hasRequiredErrors = requiredFields.some(field => {
        const val = form[field];
        return !val || val.toString().trim().length === 0;
    });
    
    if (hasRequiredErrors) return false;
    
    // Validaciones adicionales
    if (emailError.value) return false;
    if (phoneError.value) return false;
    if (passwordError.value) return false;
    if (passwordMatchError.value) return false;
    
    return true;
});

// ============================================
// COMPUTED PARA EL PROGRESS Y ESTADO
// ============================================

const hasErrors = computed(() => {
    const errorCount = Object.keys(form.errors).length;
    return errorCount > 0 || emailError.value || passwordError.value || phoneError.value || passwordMatchError.value;
});

const errorCount = computed(() => {
    let count = Object.keys(form.errors).length;
    if (emailError.value) count++;
    if (passwordError.value) count++;
    if (phoneError.value) count++;
    if (passwordMatchError.value) count++;
    return count;
});

const requiredFields = computed(() => {
    return ['nombre_completo', 'nombre_usuario', 'email', 'tipo_usuario', 'password', 'password_confirmation'];
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

watch(() => form.password, () => {
    if (form.password) validatePassword();
}, { immediate: true });

watch(() => form.password_confirmation, () => {
    if (form.password_confirmation) validatePasswordMatch();
}, { immediate: true });

watch(() => form.telefono, () => {
    if (form.telefono) validatePhone();
}, { immediate: true });

// ============================================
// SUBMIT
// ============================================

const submit = () => {
    // Validar todos los campos antes de enviar
    if (form.email) validateEmail();
    if (form.telefono) validatePhone();
    if (form.password) validatePassword();
    if (form.password_confirmation) validatePasswordMatch();
    
    if (!isFormValid.value) {
        alertRef.value?.show({
            type: 'error',
            title: '❌ Error de validación',
            message: 'Por favor, corrija los errores en el formulario antes de continuar.',
            buttonText: 'Entendido'
        });
        return;
    }
    
    form.post(route('usuarios.store'), {
        onSuccess: () => {
            alertRef.value?.show({
                type: 'success',
                title: '✅ ¡Usuario creado!',
                message: 'El usuario se ha registrado exitosamente en el sistema.',
                buttonText: 'Ir al listado'
            });
        },
        onError: (errors) => {
            alertRef.value?.show({
                type: 'error',
                title: '❌ Error al crear',
                message: 'Ocurrió un error al registrar el usuario. Verifique los datos e intente nuevamente.',
                buttonText: 'Intentar de nuevo'
            });
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

/* ========== INPUTS ========== */
.input-wrapper {
    position: relative;
}

.form-input {
    width: 100%;
    padding: 10px 110px 10px 14px;
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

.password-toggle {
    pointer-events: auto !important;
    cursor: pointer;
    z-index: 2;
}

.password-toggle:hover {
    color: #667eea;
}

.btn-generate-password {
    position: absolute;
    right: 44px;
    top: 50%;
    transform: translateY(-50%);
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 4px 12px;
    background: linear-gradient(135deg, #8b5cf6, #7c3aed);
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    z-index: 2;
    pointer-events: auto;
}

.btn-generate-password:hover {
    transform: translateY(-50%) scale(1.05);
    box-shadow: 0 4px 12px rgba(139, 92, 246, 0.4);
}

.btn-generate-password:active {
    transform: translateY(-50%) scale(0.95);
}

.btn-generate-password .icon-svg-sm {
    width: 14px;
    height: 14px;
}

.icon-svg-sm {
    width: 18px;
    height: 18px;
}

.input-wrapper:focus-within .input-icon:not(.password-toggle) {
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

/* ========== PASSWORD REQUIREMENTS ========== */
.password-requirements {
    display: flex;
    gap: 16px;
    margin-top: 6px;
    flex-wrap: wrap;
}

.requirement {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 0.75rem;
    color: #6b7280;
    transition: all 0.3s ease;
}

.requirement.met {
    color: #10b981;
}

.req-icon {
    width: 14px;
    height: 14px;
}

/* ========== EMPRESAS GRID ========== */
.search-wrapper {
    margin-bottom: 12px;
}

.empresas-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 10px;
    max-height: 300px;
    overflow-y: auto;
    padding: 4px 2px;
}

.empresa-card {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 14px;
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.empresa-card:hover {
    border-color: #667eea;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.1);
}

.empresa-card.selected {
    border-color: #10b981;
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
    box-shadow: 0 4px 16px rgba(16, 185, 129, 0.15);
}

.empresa-check {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    flex-shrink: 0;
}

.check-icon {
    width: 22px;
    height: 22px;
}

.empty-check {
    width: 22px;
    height: 22px;
    border: 2px solid #d1d5db;
    border-radius: 50%;
}

.empresa-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
    flex: 1;
    min-width: 0;
}

.empresa-nombre {
    font-size: 0.85rem;
    font-weight: 500;
    color: #111827;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.selected-count {
    margin-top: 12px;
    display: flex;
    justify-content: flex-end;
}

.count-badge {
    background: linear-gradient(135deg, #d1fae5, #a7f3d0);
    color: #065f46;
    padding: 4px 14px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
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

    .empresas-grid {
        grid-template-columns: 1fr;
        max-height: 200px;
    }

    .password-requirements {
        flex-direction: column;
        gap: 4px;
    }

    .form-input {
        padding-right: 90px;
    }

    .btn-generate-password {
        right: 40px;
        padding: 3px 8px;
        font-size: 0.7rem;
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

    .form-input {
        padding-right: 80px;
    }

    .btn-generate-password {
        right: 36px;
        padding: 2px 6px;
        font-size: 0.65rem;
    }
}
</style>