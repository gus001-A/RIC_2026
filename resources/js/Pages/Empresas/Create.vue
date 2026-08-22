<template>
    <AppLayout title="Nueva Empresa">
        <template #header>
            <div class="header-wrapper-premium">
                <div class="header-left-premium">
                    <Link :href="route('empresas.index')" class="btn-back-premium">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                    </Link>
                    <div>
                        <h2 class="header-title-premium">
                            <span class="title-highlight">Nueva</span> Empresa
                        </h2>
                        <p class="header-subtitle-premium">Complete el formulario para registrar una nueva empresa en el sistema</p>
                    </div>
                </div>
            </div>
        </template>

        <div class="page-content-premium">
            <div class="container-premium">
                <div class="form-card-premium">
                    <form @submit.prevent="submit" id="empresaForm" novalidate>
                        <!-- ===== TABS ===== -->
                        <div class="tabs-premium">
                            <button type="button" 
                                    class="tab-premium" 
                                    :class="{ active: activeTab === 'empresa' }"
                                    @click="activeTab = 'empresa'">
                                <svg class="tab-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                <span>Empresa</span>
                                <span v-if="hasEmpresaErrors" class="tab-dot error-dot"></span>
                                <span v-else-if="isEmpresaComplete" class="tab-dot success-dot"></span>
                            </button>
                            <button type="button" 
                                    class="tab-premium" 
                                    :class="{ active: activeTab === 'contacto' }"
                                    @click="activeTab = 'contacto'">
                                <svg class="tab-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                <span>Contacto</span>
                                <span v-if="hasContactoErrors" class="tab-dot error-dot"></span>
                            </button>
                            <button type="button" 
                                    class="tab-premium" 
                                    :class="{ active: activeTab === 'direccion' }"
                                    @click="activeTab = 'direccion'">
                                <svg class="tab-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span>Dirección</span>
                                <span v-if="hasDireccionErrors" class="tab-dot error-dot"></span>
                            </button>
                            <button type="button" 
                                    v-if="form.tipo_persona === 'MORAL'"
                                    class="tab-premium" 
                                    :class="{ active: activeTab === 'representante' }"
                                    @click="activeTab = 'representante'">
                                <svg class="tab-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <span>Representante</span>
                                <span v-if="hasRepresentanteErrors" class="tab-dot error-dot"></span>
                                <span v-else-if="isRepresentanteComplete && form.tipo_persona === 'MORAL'" class="tab-dot success-dot"></span>
                            </button>
                        </div>

                        <!-- ===== CONTENIDO DE TABS ===== -->
                        <div class="tab-content-premium">
                            <!-- TAB 1: DATOS DE EMPRESA -->
                            <div v-show="activeTab === 'empresa'" class="tab-pane-premium">
                                <!-- Tipo de Persona -->
                                <div class="section-premium section-tipo">
                                    <div class="section-header-premium">
                                        <div class="section-icon-premium" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                                            <svg class="icon-sm" fill="none" stroke="white" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                            </svg>
                                        </div>
                                        <div class="section-title-wrapper">
                                            <span class="section-title-premium">Tipo de Empresa</span>
                                        </div>
                                    </div>
                                    <div class="radio-group-premium">
                                        <div class="radio-premium" 
                                             :class="{ 'selected': form.tipo_persona === 'FISICA' }"
                                             @click="form.tipo_persona = 'FISICA'; clearError('tipo_persona')">
                                            <svg class="radio-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                            <span class="radio-label">Empresa Física</span>
                                            <div class="radio-check" v-if="form.tipo_persona === 'FISICA'">
                                                <svg class="check-icon" fill="none" stroke="#10b981" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </div>
                                            <input type="radio" v-model="form.tipo_persona" value="FISICA" class="radio-input">
                                        </div>
                                        <div class="radio-premium" 
                                             :class="{ 'selected': form.tipo_persona === 'MORAL' }"
                                             @click="form.tipo_persona = 'MORAL'; clearError('tipo_persona')">
                                            <svg class="radio-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                            </svg>
                                            <span class="radio-label">Empresa Moral</span>
                                            <div class="radio-check" v-if="form.tipo_persona === 'MORAL'">
                                                <svg class="check-icon" fill="none" stroke="#10b981" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </div>
                                            <input type="radio" v-model="form.tipo_persona" value="MORAL" class="radio-input">
                                        </div>
                                    </div>
                                    <div v-if="form.errors.tipo_persona" class="error-premium">{{ form.errors.tipo_persona }}</div>
                                </div>

                                <!-- Datos de la Empresa -->
                                <div class="section-premium section-datos">
                                    <div class="section-header-premium">
                                        <div class="section-icon-premium" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">
                                            <svg class="icon-sm" fill="none" stroke="white" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                            </svg>
                                        </div>
                                        <div class="section-title-wrapper">
                                            <span class="section-title-premium">Datos de la Empresa</span>
                                        </div>
                                    </div>

                                    <div class="grid-empresa">
                                        <!-- Nombre - ocupa toda la fila -->
                                        <div class="field-premium full">
                                            <label class="label-premium">
                                                Nombre de la Empresa 
                                                <span class="star">*</span>
                                            </label>
                                            <div class="input-wrapper-premium">
                                                <input type="text" v-model="form.nombre_empresa"
                                                       @input="clearError('nombre_empresa')"
                                                       class="input-premium"
                                                       :class="{ 'error': form.errors.nombre_empresa }"
                                                       placeholder="Ej: Corporativo RIC S.A. de C.V.">
                                                <svg class="input-icon-premium" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                                </svg>
                                            </div>
                                            <div v-if="form.errors.nombre_empresa" class="error-premium">{{ form.errors.nombre_empresa }}</div>
                                        </div>

                                        <!-- RFC, Clave y Régimen en una sola línea -->
                                        <div class="field-premium">
                                            <label class="label-premium">
                                                RFC 
                                                <span class="star">*</span>
                                            </label>
                                            <div class="input-wrapper-premium">
                                                <input type="text" v-model="form.rfc"
                                                       @input="form.rfc = form.rfc.toUpperCase(); clearError('rfc'); validateRFC()"
                                                       class="input-premium text-uppercase"
                                                       :class="{ 'error': form.errors.rfc || rfcError }"
                                                       placeholder="RIC20260617"
                                                       maxlength="13">
                                                <svg class="input-icon-premium" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                                                </svg>
                                            </div>
                                            <div v-if="form.errors.rfc" class="error-premium">{{ form.errors.rfc }}</div>
                                            <div v-if="rfcError" class="error-premium">{{ rfcError }}</div>
                                        </div>

                                        <div class="field-premium">
                                            <label class="label-premium">Clave</label>
                                            <div class="input-wrapper-premium">
                                                <input type="text" v-model="form.clave"
                                                       @input="form.clave = form.clave.toUpperCase()"
                                                       class="input-premium text-uppercase"
                                                       placeholder="RIC001"
                                                       maxlength="20">
                                                <svg class="input-icon-premium" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                                </svg>
                                            </div>
                                        </div>

                                        <div class="field-premium">
                                            <label class="label-premium">Régimen</label>
                                            <div class="input-wrapper-premium">
                                                <input type="text" v-model="form.regimen"
                                                       class="input-premium"
                                                       placeholder="Ej: General de Ley">
                                                <svg class="input-icon-premium" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- TAB 2: CONTACTO -->
                            <div v-show="activeTab === 'contacto'" class="tab-pane-premium">
                                <div class="section-premium section-contacto">
                                    <div class="section-header-premium">
                                        <div class="section-icon-premium" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                                            <svg class="icon-sm" fill="none" stroke="white" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                            </svg>
                                        </div>
                                        <div class="section-title-wrapper">
                                            <span class="section-title-premium">Contacto de la Empresa</span>
                                            <span class="badge-optional">Opcional</span>
                                        </div>
                                    </div>
                                    <div class="grid-contacto">
                                        <div class="field-premium full">
                                            <label class="label-premium">Correo Electrónico</label>
                                            <div class="input-wrapper-premium">
                                                <input type="email" v-model="form.correo"
                                                       @input="clearError('correo'); validateEmail()"
                                                       class="input-premium"
                                                       :class="{ 'error': form.errors.correo || emailError }"
                                                       placeholder="empresa@correo.com">
                                                <svg class="input-icon-premium" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                                </svg>
                                            </div>
                                            <div v-if="form.errors.correo" class="error-premium">{{ form.errors.correo }}</div>
                                            <div v-if="emailError" class="error-premium">{{ emailError }}</div>
                                        </div>
                                        <div class="field-premium">
                                            <label class="label-premium">Teléfono Personal</label>
                                            <div class="input-wrapper-premium">
                                                <input type="text" v-model="form.telefono_personal"
                                                       @input="validatePhone('telefono_personal')"
                                                       class="input-premium"
                                                       :class="{ 'error': phoneErrors.telefono_personal }"
                                                       placeholder="7771234567"
                                                       maxlength="10">
                                                <svg class="input-icon-premium" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                                </svg>
                                            </div>
                                            <div v-if="phoneErrors.telefono_personal" class="error-premium">{{ phoneErrors.telefono_personal }}</div>
                                        </div>
                                        <div class="field-premium">
                                            <label class="label-premium">Teléfono de Trabajo</label>
                                            <div class="input-wrapper-premium">
                                                <input type="text" v-model="form.telefono_trabajo"
                                                       @input="validatePhone('telefono_trabajo')"
                                                       class="input-premium"
                                                       :class="{ 'error': phoneErrors.telefono_trabajo }"
                                                       placeholder="7771234567"
                                                       maxlength="10">
                                                <svg class="input-icon-premium" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                                </svg>
                                            </div>
                                            <div v-if="phoneErrors.telefono_trabajo" class="error-premium">{{ phoneErrors.telefono_trabajo }}</div>
                                        </div>
                                        <div class="field-premium">
                                            <label class="label-premium">Extensión</label>
                                            <div class="input-wrapper-premium">
                                                <input type="text" v-model="form.extension"
                                                       @input="validateExtension()"
                                                       class="input-premium"
                                                       :class="{ 'error': extensionError }"
                                                       placeholder="101"
                                                       maxlength="4">
                                                <svg class="input-icon-premium" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                                                </svg>
                                            </div>
                                            <div v-if="extensionError" class="error-premium">{{ extensionError }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- TAB 3: DIRECCIÓN -->
                            <div v-show="activeTab === 'direccion'" class="tab-pane-premium">
                                <div class="section-premium section-direccion">
                                    <div class="section-header-premium">
                                        <div class="section-icon-premium" style="background: linear-gradient(135deg, #06b6d4, #0891b2);">
                                            <svg class="icon-sm" fill="none" stroke="white" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                        </div>
                                        <div class="section-title-wrapper">
                                            <span class="section-title-premium">Dirección</span>
                                            <span class="badge-optional">Opcional</span>
                                        </div>
                                    </div>
                                    <div class="grid-direccion">
                                        <div class="field-premium full">
                                            <label class="label-premium">Calle</label>
                                            <div class="input-wrapper-premium">
                                                <input type="text" v-model="form.calle"
                                                       class="input-premium"
                                                       placeholder="Av. Insurgentes Sur">
                                                <svg class="input-icon-premium" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="field-premium">
                                            <label class="label-premium">Número Exterior</label>
                                            <div class="input-wrapper-premium">
                                                <input type="text" v-model="form.numero_exterior"
                                                       @input="validateAlphanumeric('numero_exterior')"
                                                       class="input-premium"
                                                       :class="{ 'error': alphanumericErrors.numero_exterior }"
                                                       placeholder="123">
                                                <svg class="input-icon-premium" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                                                </svg>
                                            </div>
                                            <div v-if="alphanumericErrors.numero_exterior" class="error-premium">{{ alphanumericErrors.numero_exterior }}</div>
                                        </div>
                                        <div class="field-premium">
                                            <label class="label-premium">Número Interior</label>
                                            <div class="input-wrapper-premium">
                                                <input type="text" v-model="form.numero_interior"
                                                       @input="validateAlphanumeric('numero_interior')"
                                                       class="input-premium"
                                                       :class="{ 'error': alphanumericErrors.numero_interior }"
                                                       placeholder="2B">
                                                <svg class="input-icon-premium" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                                                </svg>
                                            </div>
                                            <div v-if="alphanumericErrors.numero_interior" class="error-premium">{{ alphanumericErrors.numero_interior }}</div>
                                        </div>
                                        <div class="field-premium">
                                            <label class="label-premium">Colonia</label>
                                            <div class="input-wrapper-premium">
                                                <input type="text" v-model="form.colonia"
                                                       class="input-premium"
                                                       placeholder="Colonia Centro">
                                                <svg class="input-icon-premium" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="field-premium">
                                            <label class="label-premium">Ciudad</label>
                                            <div class="input-wrapper-premium">
                                                <input type="text" v-model="form.ciudad"
                                                       class="input-premium"
                                                       placeholder="Ciudad de México">
                                                <svg class="input-icon-premium" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1h-2z"/>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="field-premium">
                                            <label class="label-premium">Municipio</label>
                                            <div class="input-wrapper-premium">
                                                <input type="text" v-model="form.municipio"
                                                       class="input-premium"
                                                       placeholder="Cuauhtémoc">
                                                <svg class="input-icon-premium" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="field-premium">
                                            <label class="label-premium">Estado</label>
                                            <div class="input-wrapper-premium">
                                                <select v-model="form.estado" class="select-premium">
                                                    <option value="">Selecciona un estado</option>
                                                    <option v-for="estado in estadosMexico" :key="estado" :value="estado">
                                                        {{ estado }}
                                                    </option>
                                                </select>
                                                <svg class="input-icon-premium" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="field-premium">
                                            <label class="label-premium">Código Postal</label>
                                            <div class="input-wrapper-premium">
                                                <input type="text" v-model="form.codigo_postal"
                                                       @input="validateCodigoPostal()"
                                                       class="input-premium"
                                                       :class="{ 'error': codigoPostalError }"
                                                       placeholder="06000"
                                                       maxlength="5">
                                                <svg class="input-icon-premium" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                                </svg>
                                            </div>
                                            <div v-if="codigoPostalError" class="error-premium">{{ codigoPostalError }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- TAB 4: REPRESENTANTE LEGAL -->
                            <div v-show="activeTab === 'representante'" class="tab-pane-premium">
                                <div class="section-premium section-representante">
                                    <div class="section-header-premium">
                                        <div class="section-icon-premium" style="background: linear-gradient(135deg, #ec4899, #db2777);">
                                            <svg class="icon-sm" fill="none" stroke="white" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                        </div>
                                        <div class="section-title-wrapper">
                                            <span class="section-title-premium">Representante Legal</span>
                                            <span class="badge-required">Requerido</span>
                                        </div>
                                    </div>
                                    <div class="grid-representante">
                                        <div class="field-premium">
                                            <label class="label-premium">
                                                Nombre 
                                                <span class="star">*</span>
                                            </label>
                                            <div class="input-wrapper-premium">
                                                <input type="text" v-model="form.representante_nombre"
                                                       @input="clearError('representante_nombre')"
                                                       class="input-premium"
                                                       :class="{ 'error': form.errors.representante_nombre }"
                                                       placeholder="Juan">
                                                <svg class="input-icon-premium" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                </svg>
                                            </div>
                                            <div v-if="form.errors.representante_nombre" class="error-premium">{{ form.errors.representante_nombre }}</div>
                                        </div>
                                        <div class="field-premium">
                                            <label class="label-premium">
                                                Apellido Paterno 
                                                <span class="star">*</span>
                                            </label>
                                            <div class="input-wrapper-premium">
                                                <input type="text" v-model="form.representante_apellido_paterno"
                                                       @input="clearError('representante_apellido_paterno')"
                                                       class="input-premium"
                                                       :class="{ 'error': form.errors.representante_apellido_paterno }"
                                                       placeholder="Pérez">
                                                <svg class="input-icon-premium" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                </svg>
                                            </div>
                                            <div v-if="form.errors.representante_apellido_paterno" class="error-premium">{{ form.errors.representante_apellido_paterno }}</div>
                                        </div>
                                        <div class="field-premium">
                                            <label class="label-premium">Apellido Materno</label>
                                            <div class="input-wrapper-premium">
                                                <input type="text" v-model="form.representante_apellido_materno"
                                                       class="input-premium"
                                                       placeholder="García">
                                                <svg class="input-icon-premium" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="field-premium">
                                            <label class="label-premium">
                                                RFC 
                                                <span class="star">*</span>
                                            </label>
                                            <div class="input-wrapper-premium">
                                                <input type="text" v-model="form.representante_rfc"
                                                       @input="form.representante_rfc = form.representante_rfc.toUpperCase(); clearError('representante_rfc'); validateRepresentanteRFC()"
                                                       class="input-premium text-uppercase"
                                                       :class="{ 'error': form.errors.representante_rfc || representanteRfcError }"
                                                       placeholder="REPRFC123456"
                                                       maxlength="13">
                                                <svg class="input-icon-premium" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                                                </svg>
                                            </div>
                                            <div v-if="form.errors.representante_rfc" class="error-premium">{{ form.errors.representante_rfc }}</div>
                                            <div v-if="representanteRfcError" class="error-premium">{{ representanteRfcError }}</div>
                                        </div>
                                        <div class="field-premium">
                                            <label class="label-premium">CURP</label>
                                            <div class="input-wrapper-premium">
                                                <input type="text" v-model="form.representante_curp"
                                                       @input="form.representante_curp = form.representante_curp.toUpperCase(); validateCURP()"
                                                       class="input-premium text-uppercase"
                                                       :class="{ 'error': curpError }"
                                                       placeholder="CURP1234567890"
                                                       maxlength="18">
                                                <svg class="input-icon-premium" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                                                </svg>
                                            </div>
                                            <div v-if="curpError" class="error-premium">{{ curpError }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ===== FOOTER ===== -->
                        <div class="footer-premium">
                            <div class="info-premium">
                                <svg class="info-icon-sm" fill="none" stroke="#667eea" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>Los campos con <strong class="text-danger">*</strong> son obligatorios</span>
                            </div>
                            <div class="actions-premium">
                                <Link :href="route('empresas.index')" class="btn-cancel-premium">
                                    <svg class="btn-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    Cancelar
                                </Link>
                                <button type="submit" 
                                        :disabled="form.processing || !isFormValid"
                                        class="btn-submit-premium">
                                    <span v-if="form.processing" class="spinner-border-sm" role="status"></span>
                                    <svg v-else class="btn-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                                    </svg>
                                    {{ form.processing ? 'Guardando...' : 'Crear Empresa' }}
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
import { Link, useForm } from '@inertiajs/vue3';
import AlertModal from '@/Components/AlertModal.vue';

const props = defineProps({
    estadosMexico: {
        type: Array,
        default: () => []
    }
});

const alertRef = ref(null);

// ============================================
// TAB ACTIVO
// ============================================
const activeTab = ref('empresa');

// ============================================
// ESTADOS DE VALIDACIÓN
// ============================================
const rfcError = ref('');
const emailError = ref('');
const extensionError = ref('');
const codigoPostalError = ref('');
const representanteRfcError = ref('');
const curpError = ref('');

const phoneErrors = ref({
    telefono_personal: '',
    telefono_trabajo: ''
});

const alphanumericErrors = ref({
    numero_exterior: '',
    numero_interior: ''
});

// ============================================
// FORMULARIO
// ============================================
const form = useForm({
    nombre_empresa: '',
    rfc: '',
    regimen: '',
    tipo_persona: 'FISICA',
    clave: '',
    estado: '',
    municipio: '',
    ciudad: '',
    colonia: '',
    calle: '',
    numero_exterior: '',
    numero_interior: '',
    codigo_postal: '',
    correo: '',
    telefono_personal: '',
    telefono_trabajo: '',
    extension: '',
    representante_nombre: '',
    representante_apellido_paterno: '',
    representante_apellido_materno: '',
    representante_rfc: '',
    representante_curp: '',
});

// ============================================
// VALIDACIONES
// ============================================
const validateRFC = () => {
    const rfc = form.rfc;
    if (!rfc) {
        rfcError.value = '';
        return;
    }
    const rfcRegex = /^[A-ZÑ&]{3,4}[0-9]{6}[A-Z0-9]{3}$/;
    rfcError.value = rfcRegex.test(rfc) ? '' : 'El RFC debe tener formato válido (3-4 letras, 6 números, 3 caracteres alfanuméricos)';
};

const validateRepresentanteRFC = () => {
    const rfc = form.representante_rfc;
    if (!rfc) {
        representanteRfcError.value = '';
        return;
    }
    const rfcRegex = /^[A-ZÑ&]{3,4}[0-9]{6}[A-Z0-9]{3}$/;
    representanteRfcError.value = rfcRegex.test(rfc) ? '' : 'El RFC debe tener formato válido (3-4 letras, 6 números, 3 caracteres alfanuméricos)';
};

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
    const curpRegex = /^[A-Z]{4}[0-9]{6}[A-Z]{6}[0-9A-Z]{2}$/;
    if (curpLimpio.length !== 18) {
        curpError.value = `La CURP debe tener 18 caracteres (tiene ${curpLimpio.length})`;
        return;
    }
    curpError.value = curpRegex.test(curpLimpio) ? '' : 'Formato de CURP inválido';
};

const validateEmail = () => {
    const email = form.correo;
    if (!email) {
        emailError.value = '';
        return;
    }
    const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    emailError.value = emailRegex.test(email) ? '' : 'Correo electrónico inválido';
};

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
    } else if (value.length > 10) {
        phoneErrors.value[field] = 'El teléfono no puede tener más de 10 dígitos';
    } else {
        phoneErrors.value[field] = '';
    }
};

const validateExtension = () => {
    const ext = form.extension;
    if (!ext) {
        extensionError.value = '';
        return;
    }
    extensionError.value = /^\d+$/.test(ext) ? '' : 'Solo se permiten números';
};

const validateAlphanumeric = (field) => {
    const value = form[field];
    if (!value || value.trim() === '') {
        alphanumericErrors.value[field] = '';
        return;
    }
    alphanumericErrors.value[field] = /^[A-Za-z0-9]+$/.test(value) ? '' : 'Solo se permiten letras y números';
};

const validateCodigoPostal = () => {
    const cp = form.codigo_postal;
    if (!cp) {
        codigoPostalError.value = '';
        return;
    }
    codigoPostalError.value = /^\d{5}$/.test(cp) ? '' : 'El código postal debe tener 5 dígitos';
};

const clearError = (field) => {
    if (form.errors[field]) delete form.errors[field];
};

// ============================================
// COMPUTED PARA ERRORES POR TAB
// ============================================
const hasEmpresaErrors = computed(() => {
    return form.errors.tipo_persona || form.errors.nombre_empresa || form.errors.rfc || rfcError.value;
});

const isEmpresaComplete = computed(() => {
    return form.tipo_persona && form.nombre_empresa && form.rfc && !rfcError.value;
});

const hasContactoErrors = computed(() => {
    return form.errors.correo || emailError.value || 
           phoneErrors.value.telefono_personal || phoneErrors.value.telefono_trabajo || 
           extensionError.value;
});

const hasDireccionErrors = computed(() => {
    return alphanumericErrors.value.numero_exterior || alphanumericErrors.value.numero_interior || codigoPostalError.value;
});

const hasRepresentanteErrors = computed(() => {
    return form.errors.representante_nombre || form.errors.representante_apellido_paterno || 
           form.errors.representante_rfc || representanteRfcError.value || curpError.value;
});

const isRepresentanteComplete = computed(() => {
    if (form.tipo_persona !== 'MORAL') return false;
    return form.representante_nombre && form.representante_apellido_paterno && 
           form.representante_rfc && !representanteRfcError.value;
});

// ============================================
// COMPUTED
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
    if (codigoPostalError.value) return false;
    if (representanteRfcError.value) return false;
    if (curpError.value) return false;
    
    if (phoneErrors.value.telefono_personal) return false;
    if (phoneErrors.value.telefono_trabajo) return false;
    
    if (alphanumericErrors.value.numero_exterior) return false;
    if (alphanumericErrors.value.numero_interior) return false;
    
    return true;
});

const hasErrors = computed(() => {
    let count = Object.keys(form.errors).length;
    if (rfcError.value) count++;
    if (emailError.value) count++;
    if (extensionError.value) count++;
    if (codigoPostalError.value) count++;
    if (representanteRfcError.value) count++;
    if (curpError.value) count++;
    if (phoneErrors.value.telefono_personal) count++;
    if (phoneErrors.value.telefono_trabajo) count++;
    if (alphanumericErrors.value.numero_exterior) count++;
    if (alphanumericErrors.value.numero_interior) count++;
    return count > 0;
});

const errorCount = computed(() => {
    let count = Object.keys(form.errors).length;
    if (rfcError.value) count++;
    if (emailError.value) count++;
    if (extensionError.value) count++;
    if (codigoPostalError.value) count++;
    if (representanteRfcError.value) count++;
    if (curpError.value) count++;
    if (phoneErrors.value.telefono_personal) count++;
    if (phoneErrors.value.telefono_trabajo) count++;
    if (alphanumericErrors.value.numero_exterior) count++;
    if (alphanumericErrors.value.numero_interior) count++;
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
// WATCHERS
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
// SUBMIT
// ============================================
const submit = () => {
    validateRFC();
    validateEmail();
    validateRepresentanteRFC();
    validateCURP();
    
    if (form.telefono_personal) validatePhone('telefono_personal');
    if (form.telefono_trabajo) validatePhone('telefono_trabajo');
    if (form.extension) validateExtension();
    if (form.numero_exterior) validateAlphanumeric('numero_exterior');
    if (form.numero_interior) validateAlphanumeric('numero_interior');
    if (form.codigo_postal) validateCodigoPostal();
    
    if (!isFormValid.value) {
        alertRef.value?.show({
            type: 'error',
            title: 'Error de validación',
            message: 'Por favor, corrija los errores en el formulario antes de continuar.',
            buttonText: 'Entendido'
        });
        return;
    }
    
    form.post(route('empresas.store'), {
        onSuccess: () => {
            alertRef.value?.show({
                type: 'success',
                title: 'Empresa creada exitosamente',
                message: 'La empresa se ha registrado exitosamente en el sistema.',
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
                    title: 'Error al crear',
                    message: 'Ocurrió un error al registrar la empresa. Verifique los datos e intente nuevamente.',
                    buttonText: 'Intentar de nuevo'
                });
            }
        }
    });
};
</script>

<style scoped>
/* ===== HEADER PREMIUM ===== */
.header-wrapper-premium {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    padding: 2px 0;
}

.header-left-premium {
    display: flex;
    align-items: center;
    gap: 12px;
}

.btn-back-premium {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.9);
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: #6b7280;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.btn-back-premium:hover {
    background: white;
    color: #1f2937;
    transform: translateX(-3px) scale(1.02);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
}

.header-title-premium {
    font-size: 1.25rem;
    font-weight: 700;
    margin: 0;
    line-height: 1.3;
}

.title-highlight {
    background: linear-gradient(135deg, #667eea, #764ba2);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.header-title-premium span:not(.title-highlight) {
    color: #1f2937;
}

.header-subtitle-premium {
    font-size: 0.85rem;
    color: #6b7280;
    margin: 0;
}

.header-right-premium {
    display: flex;
    align-items: center;
    gap: 10px;
}

.status-badge-premium {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 6px 18px;
    border-radius: 24px;
    font-size: 0.8rem;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.badge-icon {
    width: 16px;
    height: 16px;
}

.status-success {
    background: linear-gradient(135deg, #d1fae5, #a7f3d0);
    color: #065f46;
    border: 1px solid #6ee7b7;
}

.status-error {
    background: linear-gradient(135deg, #fecaca, #fca5a5);
    color: #991b1b;
    border: 1px solid #f87171;
    animation: shake 0.5s ease;
}

.status-progress {
    background: linear-gradient(135deg, #e0e7ff, #c7d2fe);
    color: #4338ca;
    border: 1px solid #a5b4fc;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-4px); }
    75% { transform: translateX(4px); }
}

/* ===== PAGE CONTENT ===== */
.page-content-premium {
    padding: 0.75rem 0;
}

.container-premium {
    max-width: 100%;
    margin: 0 auto;
    padding: 0 1.25rem;
}

.form-card-premium {
    background: white;
    border-radius: 20px;
    box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
    border: 1px solid #f1f3f5;
    padding: 1rem 1.25rem;
    display: flex;
    flex-direction: column;
    max-height: calc(100vh - 180px);
    transition: all 0.3s ease;
}

.form-card-premium:hover {
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
}

/* ===== TABS ===== */
.tabs-premium {
    display: flex;
    gap: 4px;
    margin-bottom: 14px;
    border-bottom: 2px solid #f1f3f5;
    padding-bottom: 2px;
    flex-shrink: 0;
    flex-wrap: wrap;
    background: #fafbfc;
    border-radius: 12px;
    padding: 4px;
}

.tab-premium {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 20px;
    background: transparent;
    border: none;
    border-radius: 10px;
    font-size: 0.85rem;
    font-weight: 600;
    color: #6b7280;
    cursor: pointer;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    white-space: nowrap;
    position: relative;
}

.tab-premium:hover {
    color: #1f2937;
    background: rgba(102, 126, 234, 0.06);
}

.tab-premium.active {
    color: #667eea;
    background: white;
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.12);
}

.tab-icon {
    width: 18px;
    height: 18px;
    flex-shrink: 0;
}

.tab-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
    flex-shrink: 0;
}

.error-dot {
    background: #ef4444;
    animation: pulse-dot 1.5s ease-in-out infinite;
}

.success-dot {
    background: #10b981;
}

@keyframes pulse-dot {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(0.8); }
}

/* ===== TAB CONTENT ===== */
.tab-content-premium {
    flex: 1;
    overflow-y: auto;
    padding-right: 4px;
    min-height: 0;
}

.tab-content-premium::-webkit-scrollbar {
    width: 5px;
}

.tab-content-premium::-webkit-scrollbar-track {
    background: #f1f3f5;
    border-radius: 4px;
}

.tab-content-premium::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 4px;
}

.tab-content-premium::-webkit-scrollbar-thumb:hover {
    background: #9ca3af;
}

.tab-pane-premium {
    display: flex;
    flex-direction: column;
    gap: 14px;
    animation: fadeIn 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
}

/* ===== SECCIONES ===== */
.section-premium {
    background: linear-gradient(135deg, #fafbfc, #ffffff);
    border-radius: 12px;
    padding: 14px 18px 16px;
    border: 1px solid #f1f3f5;
    transition: all 0.25s ease;
    flex-shrink: 0;
}

.section-premium:hover {
    border-color: #e5e7eb;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
}

.section-tipo { border-left: 4px solid #667eea; }
.section-datos { border-left: 4px solid #8b5cf6; }
.section-contacto { border-left: 4px solid #f59e0b; }
.section-direccion { border-left: 4px solid #06b6d4; }
.section-representante { border-left: 4px solid #ec4899; }

.section-header-premium {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 12px;
}

.section-icon-premium {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 10px;
    color: white;
    flex-shrink: 0;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.icon-sm {
    width: 16px;
    height: 16px;
}

.section-title-wrapper {
    display: flex;
    align-items: center;
    gap: 8px;
    flex: 1;
    flex-wrap: wrap;
}

.section-title-premium {
    font-size: 0.9rem;
    font-weight: 700;
    color: #1f2937;
    letter-spacing: 0.3px;
}

.badge-required {
    background: linear-gradient(135deg, #dbeafe, #bfdbfe);
    color: #1e40af;
    padding: 2px 12px;
    border-radius: 20px;
    font-size: 0.6rem;
    font-weight: 700;
    border: 1px solid #93c5fd;
}

.badge-optional {
    background: linear-gradient(135deg, #f3f4f6, #e5e7eb);
    color: #6b7280;
    padding: 2px 12px;
    border-radius: 20px;
    font-size: 0.6rem;
    font-weight: 600;
    border: 1px solid #d1d5db;
}

.star {
    color: #ef4444;
    font-weight: 700;
}

/* ===== GRID SISTEM ===== */
.grid-empresa {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 10px 14px;
}

.grid-contacto {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr 1fr;
    gap: 10px 14px;
}

.grid-direccion {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px 14px;
}

.grid-representante {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 10px 14px;
}

.field-premium {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.field-premium.full {
    grid-column: 1 / -1;
}

/* ===== LABELS ===== */
.label-premium {
    font-size: 0.78rem;
    font-weight: 600;
    color: #4b5563;
    display: flex;
    align-items: center;
    gap: 4px;
    flex-wrap: wrap;
}

/* ===== INPUT WRAPPER ===== */
.input-wrapper-premium {
    position: relative;
}

.input-wrapper-premium .input-premium,
.input-wrapper-premium .select-premium {
    padding-right: 38px;
}

.input-icon-premium {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    width: 18px;
    height: 18px;
    color: #9ca3af;
    pointer-events: none;
    transition: all 0.2s ease;
}

.input-wrapper-premium:focus-within .input-icon-premium {
    color: #667eea;
}

/* ===== INPUTS ===== */
.input-premium {
    width: 100%;
    padding: 9px 38px 9px 14px;
    font-size: 0.85rem;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    background: white;
    color: #1f2937;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    outline: none;
    height: 42px;
}

.input-premium:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    transform: translateY(-1px);
}

.input-premium:hover:not(:focus) {
    border-color: #9ca3af;
}

.input-premium.error {
    border-color: #ef4444;
    background: #fef2f2;
}

.input-premium.error:focus {
    box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
}

.input-premium.text-uppercase {
    text-transform: uppercase;
}

/* ===== SELECT ===== */
.select-premium {
    width: 100%;
    padding: 9px 38px 9px 14px;
    font-size: 0.85rem;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    background: white;
    color: #1f2937;
    outline: none;
    height: 42px;
    appearance: none;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
    background-size: 18px;
}

.select-premium:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    transform: translateY(-1px);
}

.select-premium:hover:not(:focus) {
    border-color: #9ca3af;
}

/* ===== RADIO ===== */
.radio-group-premium {
    display: flex;
    gap: 12px;
}

.radio-premium {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 18px;
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    font-size: 0.85rem;
    font-weight: 500;
    color: #4b5563;
    height: 42px;
    position: relative;
    flex: 1;
}

.radio-premium:hover {
    border-color: #667eea;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.08);
}

.radio-premium.selected {
    border-color: #667eea;
    background: linear-gradient(135deg, #f0f4ff, #e8edff);
    color: #1f2937;
    box-shadow: 0 4px 16px rgba(102, 126, 234, 0.12);
}

.radio-icon {
    width: 20px;
    height: 20px;
    flex-shrink: 0;
    color: #6b7280;
}

.radio-premium.selected .radio-icon {
    color: #667eea;
}

.radio-check {
    margin-left: auto;
    animation: popIn 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.check-icon {
    width: 20px;
    height: 20px;
}

@keyframes popIn {
    0% { transform: scale(0); opacity: 0; }
    50% { transform: scale(1.3); }
    100% { transform: scale(1); opacity: 1; }
}

.radio-input {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}

/* ===== ERROR ===== */
.error-premium {
    font-size: 0.72rem;
    color: #ef4444;
    margin-top: 2px;
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 2px 8px;
    background: #fef2f2;
    border-radius: 6px;
    border-left: 3px solid #ef4444;
}

/* ===== FOOTER ===== */
.footer-premium {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 14px;
    margin-top: 12px;
    border-top: 2px solid #f1f3f5;
    flex-shrink: 0;
    background: white;
    padding-bottom: 2px;
}

.info-premium {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.78rem;
    color: #6b7280;
}

.info-icon-sm {
    width: 18px;
    height: 18px;
    flex-shrink: 0;
}

.text-danger {
    color: #ef4444;
}

.actions-premium {
    display: flex;
    gap: 12px;
}

.btn-cancel-premium {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 9px 28px;
    font-weight: 600;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    font-size: 0.85rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
    text-decoration: none;
    color: #6b7280;
    background: white;
    height: 42px;
}

.btn-cancel-premium:hover {
    background: #fef2f2;
    border-color: #fca5a5;
    color: #dc2626;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.08);
}

.btn-submit-premium {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 9px 36px;
    font-weight: 600;
    border: none;
    border-radius: 12px;
    font-size: 0.85rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    height: 42px;
    box-shadow: 0 2px 12px rgba(16, 185, 129, 0.2);
}

.btn-submit-premium:hover:not(:disabled) {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(16, 185, 129, 0.35);
}

.btn-submit-premium:active:not(:disabled) {
    transform: translateY(-1px);
}

.btn-submit-premium:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.btn-icon-sm {
    width: 18px;
    height: 18px;
}

.spinner-border-sm {
    display: inline-block;
    width: 18px;
    height: 18px;
    border: 2.5px solid rgba(255, 255, 255, 0.3);
    border-right-color: transparent;
    border-radius: 50%;
    animation: spin 0.75s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    .tabs-premium {
        gap: 4px;
        padding: 4px;
        overflow-x: auto;
        flex-wrap: nowrap;
        -webkit-overflow-scrolling: touch;
    }
    .tab-premium {
        font-size: 0.75rem;
        padding: 6px 14px;
        flex: 0 0 auto;
        min-width: 0;
    }
    .tab-icon { width: 16px; height: 16px; }
    .tab-premium span:not(.tab-dot) { font-size: 0.7rem; }
    
    .grid-empresa {
        grid-template-columns: 1fr;
        gap: 8px;
    }
    .grid-contacto,
    .grid-direccion,
    .grid-representante {
        grid-template-columns: 1fr;
        gap: 8px;
    }
    
    .form-card-premium {
        padding: 0.8rem 1rem;
        max-height: calc(100vh - 200px);
        border-radius: 16px;
    }
    
    .footer-premium {
        flex-direction: column;
        gap: 10px;
        align-items: stretch;
    }
    .info-premium { justify-content: center; font-size: 0.7rem; }
    .actions-premium { 
        justify-content: center;
        flex-wrap: wrap;
    }
    .btn-cancel-premium,
    .btn-submit-premium {
        flex: 1;
        justify-content: center;
        padding: 8px 18px;
        font-size: 0.8rem;
        height: 38px;
    }
    .header-wrapper-premium {
        flex-wrap: wrap;
        gap: 8px;
    }
    .header-right-premium {
        width: 100%;
        justify-content: flex-start;
    }
}

@media (max-width: 480px) {
    .section-premium { 
        padding: 10px 12px 12px; 
        border-radius: 10px;
    }
    .input-premium {
        font-size: 0.8rem;
        height: 38px;
        padding: 7px 34px 7px 12px;
    }
    .select-premium {
        font-size: 0.8rem;
        height: 38px;
        padding: 7px 34px 7px 12px;
    }
    .label-premium { 
        font-size: 0.72rem; 
    }
    .section-title-premium { 
        font-size: 0.82rem; 
    }
    .status-badge-premium {
        font-size: 0.7rem;
        padding: 4px 12px;
    }
    .header-title-premium { 
        font-size: 1.05rem; 
    }
    .radio-premium {
        font-size: 0.8rem;
        height: 36px;
        padding: 6px 14px;
    }
    .form-card-premium { 
        padding: 0.6rem 0.8rem; 
        border-radius: 14px;
    }
    .grid-empresa,
    .grid-contacto,
    .grid-direccion,
    .grid-representante {
        gap: 6px;
    }
    .input-icon-premium {
        width: 16px;
        height: 16px;
        right: 10px;
    }
    .badge-required,
    .badge-optional {
        font-size: 0.55rem;
        padding: 1px 8px;
    }
    .btn-cancel-premium,
    .btn-submit-premium {
        font-size: 0.75rem;
        height: 36px;
        padding: 6px 14px;
    }
    .btn-icon-sm {
        width: 16px;
        height: 16px;
    }
}
</style>