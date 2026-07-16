<template>
    <AppLayout title="Editar Persona">
        <template #header>
            <div class="header-wrapper-premium">
                <div class="header-left-premium">
                    <Link :href="route('personas.index')" class="btn-back-premium">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                    </Link>
                    <div>
                        <h2 class="header-title-premium">Editar Persona</h2>
                        <p class="header-subtitle-premium">Actualice la información de la persona en el sistema</p>
                    </div>
                </div>
                <div class="header-right-premium">
                    <div class="status-badge-premium" :class="statusClass">
                        <span v-if="hasErrors">⚠ {{ errorCount }} errores</span>
                        <span v-else-if="isComplete">✓ Completado</span>
                        <span v-else>📝 {{ Math.round(progressPercentage) }}%</span>
                    </div>
                </div>
            </div>
        </template>

        <div class="page-content-premium">
            <div class="container-premium">
                <div class="form-card-premium">
                    <form @submit.prevent="submit" id="personaForm" novalidate>
                        <!-- ===== TABS ===== -->
                        <div class="tabs-premium">
                            <button type="button" 
                                    class="tab-premium" 
                                    :class="{ active: activeTab === 'generales' }"
                                    @click="activeTab = 'generales'">
                                <svg class="tab-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                Datos Generales
                            </button>
                            <button type="button" 
                                    class="tab-premium" 
                                    :class="{ active: activeTab === 'direccion' }"
                                    @click="activeTab = 'direccion'">
                                <svg class="tab-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                Dirección
                            </button>
                            <button type="button" 
                                    v-if="form.tipo_persona === 'MORAL'"
                                    class="tab-premium" 
                                    :class="{ active: activeTab === 'representante' }"
                                    @click="activeTab = 'representante'">
                                <svg class="tab-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                Representante Legal
                            </button>
                        </div>

                        <!-- ===== CONTENIDO DE TABS ===== -->
                        <div class="tab-content-premium">
                            <!-- TAB 1: DATOS GENERALES -->
                            <div v-show="activeTab === 'generales'" class="tab-pane-premium">
                                <!-- Tipo de Persona -->
                                <div class="section-premium section-tipo">
                                    <div class="section-header-premium">
                                        <div class="section-icon-premium" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                                            <svg class="icon-sm" fill="none" stroke="white" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                            </svg>
                                        </div>
                                        <div class="section-title-wrapper">
                                            <span class="section-title-premium">Tipo de Persona</span>
                                            <span class="badge-required">Requerido</span>
                                        </div>
                                    </div>
                                    <div class="radio-group-premium">
                                        <div class="radio-premium" 
                                             :class="{ 'selected': form.tipo_persona === 'FISICA' }"
                                             @click="form.tipo_persona = 'FISICA'; clearError('tipo_persona'); onTipoChange()">
                                            <span class="radio-label">Persona Física</span>
                                            <div class="radio-check" v-if="form.tipo_persona === 'FISICA'">
                                                <svg class="check-icon" fill="none" stroke="#10b981" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </div>
                                            <input type="radio" v-model="form.tipo_persona" value="FISICA" class="radio-input">
                                        </div>
                                        <div class="radio-premium" 
                                             :class="{ 'selected': form.tipo_persona === 'MORAL' }"
                                             @click="form.tipo_persona = 'MORAL'; clearError('tipo_persona'); onTipoChange()">
                                            <span class="radio-label">Persona Moral</span>
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

                                <!-- Datos Personales -->
                                <div class="section-premium section-datos">
                                    <div class="section-header-premium">
                                        <div class="section-icon-premium" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">
                                            <svg class="icon-sm" fill="none" stroke="white" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                        </div>
                                        <div class="section-title-wrapper">
                                            <span class="section-title-premium">Datos Personales</span>
                                            <span class="badge-required">Requerido</span>
                                        </div>
                                    </div>

                                    <!-- Nombre, Paterno, Materno -->
                                    <div class="grid-nombres">
                                        <div class="field-premium">
                                            <label class="label-premium">Nombre(s) <span class="star">*</span></label>
                                            <input type="text" v-model="form.Nombre"
                                                   @input="clearError('Nombre'); autoGenerarRFC(); validateText('Nombre')"
                                                   class="input-premium input-lg"
                                                   :class="{ 'error': form.errors.Nombre || textErrors.Nombre }"
                                                   placeholder="Juan Carlos">
                                            <div v-if="form.errors.Nombre" class="error-premium">{{ form.errors.Nombre }}</div>
                                            <div v-if="textErrors.Nombre" class="error-premium">{{ textErrors.Nombre }}</div>
                                        </div>
                                        <div class="field-premium">
                                            <label class="label-premium">Apellido Paterno <span class="star">*</span></label>
                                            <input type="text" v-model="form.Paterno"
                                                   @input="clearError('Paterno'); autoGenerarRFC(); validateText('Paterno')"
                                                   class="input-premium input-lg"
                                                   :class="{ 'error': form.errors.Paterno || textErrors.Paterno }"
                                                   placeholder="Pérez">
                                            <div v-if="form.errors.Paterno" class="error-premium">{{ form.errors.Paterno }}</div>
                                            <div v-if="textErrors.Paterno" class="error-premium">{{ textErrors.Paterno }}</div>
                                        </div>
                                        <div class="field-premium">
                                            <label class="label-premium">Apellido Materno</label>
                                            <input type="text" v-model="form.Materno"
                                                   @input="autoGenerarRFC(); validateText('Materno')"
                                                   class="input-premium input-lg"
                                                   :class="{ 'error': textErrors.Materno }"
                                                   placeholder="García">
                                            <div v-if="textErrors.Materno" class="error-premium">{{ textErrors.Materno }}</div>
                                        </div>
                                    </div>

                                    <!-- Empleado, Fecha, Sexo -->
                                    <div class="grid-3">
                                        <div class="field-premium">
                                            <label class="label-premium">¿Es empleado?</label>
                                            <select v-model="form.empleado"
                                                    @change="clearError('empleado')"
                                                    class="select-premium select-lg"
                                                    :class="{ 'error': form.errors.empleado }">
                                                <option :value="false">No</option>
                                                <option :value="true">Sí</option>
                                            </select>
                                            <div v-if="form.errors.empleado" class="error-premium">{{ form.errors.empleado }}</div>
                                        </div>
                                        <div class="field-premium">
                                            <label class="label-premium">Fecha Nacimiento <span class="star">*</span></label>
                                            <input type="date" v-model="form.Fecha_nacimiento"
                                                   @change="clearError('Fecha_nacimiento'); validateEdad(); autoGenerarRFC()"
                                                   class="input-premium input-lg date"
                                                   :class="{ 'error': form.errors.Fecha_nacimiento || edadError }"
                                                   :max="fechaMaxima">
                                            <div v-if="form.errors.Fecha_nacimiento" class="error-premium">{{ form.errors.Fecha_nacimiento }}</div>
                                            <div v-if="edadError" class="error-premium">{{ edadError }}</div>
                                        </div>
                                        <div class="field-premium">
                                            <label class="label-premium">Sexo <span class="star">*</span></label>
                                            <div class="radio-group-sm radio-lg">
                                                <div class="radio-sm radio-lg" 
                                                     :class="{ 'selected': form.sexo === 'MASCULINO' }"
                                                     @click="form.sexo = 'MASCULINO'; clearError('sexo')">
                                                    <span>Masculino</span>
                                                    <input type="radio" v-model="form.sexo" value="MASCULINO" class="radio-input">
                                                </div>
                                                <div class="radio-sm radio-lg" 
                                                     :class="{ 'selected': form.sexo === 'FEMENINO' }"
                                                     @click="form.sexo = 'FEMENINO'; clearError('sexo')">
                                                    <span>Femenino</span>
                                                    <input type="radio" v-model="form.sexo" value="FEMENINO" class="radio-input">
                                                </div>
                                            </div>
                                            <div v-if="form.errors.sexo" class="error-premium">{{ form.errors.sexo }}</div>
                                        </div>
                                    </div>

                                    <!-- RFC + CURP -->
                                    <div class="grid-2">
                                        <div class="field-premium">
                                            <label class="label-premium">RFC <span class="star">*</span></label>
                                            <div class="input-with-btn">
                                                <input type="text" v-model="form.rfc"
                                                       @input="clearError('rfc'); form.rfc = form.rfc.toUpperCase(); validateRFC()"
                                                       class="input-premium input-lg"
                                                       :class="{ 'error': form.errors.rfc || rfcError }"
                                                       placeholder="PERE890101XXX"
                                                       maxlength="13">
                                                <button type="button" 
                                                        @click="generarRFC" 
                                                        class="btn-rfc-premium"
                                                        :disabled="generandoRFC || !puedeGenerarRFC">
                                                    <span v-if="generandoRFC" class="spinner-sm"></span>
                                                    <span v-else>Generar RFC</span>
                                                </button>
                                            </div>
                                            <div v-if="form.errors.rfc" class="error-premium">{{ form.errors.rfc }}</div>
                                            <div v-if="rfcError" class="error-premium">{{ rfcError }}</div>
                                        </div>
                                        <div class="field-premium">
                                            <label class="label-premium">CURP <span class="optional">(Opcional)</span></label>
                                            <input type="text" v-model="form.curp"
                                                   @input="clearError('curp'); form.curp = form.curp.toUpperCase(); validateCURP()"
                                                   class="input-premium input-lg"
                                                   :class="{ 'error': form.errors.curp || curpError }"
                                                   placeholder="PERE890101HDFRRN09"
                                                   maxlength="18">
                                            <div v-if="form.errors.curp" class="error-premium">{{ form.errors.curp }}</div>
                                            <div v-if="curpError" class="error-premium">{{ curpError }}</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Contacto -->
                                <div class="section-premium section-contacto">
                                    <div class="section-header-premium">
                                        <div class="section-icon-premium" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                                            <svg class="icon-sm" fill="none" stroke="white" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                            </svg>
                                        </div>
                                        <div class="section-title-wrapper">
                                            <span class="section-title-premium">Contacto</span>
                                            <span class="badge-optional">Opcional</span>
                                        </div>
                                    </div>
                                    <div class="grid-contacto">
                                        <div class="field-premium full">
                                            <label class="label-premium">Correo Electrónico</label>
                                            <input type="email" v-model="form.email"
                                                   @input="clearError('email'); validateEmail()"
                                                   class="input-premium input-lg"
                                                   :class="{ 'error': form.errors.email || emailError }"
                                                   placeholder="contacto@correo.com">
                                            <div v-if="form.errors.email" class="error-premium">{{ form.errors.email }}</div>
                                            <div v-if="emailError" class="error-premium">{{ emailError }}</div>
                                        </div>
                                        <div class="field-premium">
                                            <label class="label-premium">Teléfono Particular</label>
                                            <input type="text" v-model="form.telefono_particular"
                                                   @input="validatePhone('telefono_particular')"
                                                   class="input-premium input-lg"
                                                   :class="{ 'error': phoneErrors.telefono_particular }"
                                                   placeholder="777 123 4567"
                                                   maxlength="10">
                                            <div v-if="phoneErrors.telefono_particular" class="error-premium">{{ phoneErrors.telefono_particular }}</div>
                                        </div>
                                        <div class="field-premium">
                                            <label class="label-premium">Teléfono Trabajo</label>
                                            <input type="text" v-model="form.telefono_trabajo"
                                                   @input="validatePhone('telefono_trabajo')"
                                                   class="input-premium input-lg"
                                                   :class="{ 'error': phoneErrors.telefono_trabajo }"
                                                   placeholder="777 987 6543"
                                                   maxlength="10">
                                            <div v-if="phoneErrors.telefono_trabajo" class="error-premium">{{ phoneErrors.telefono_trabajo }}</div>
                                        </div>
                                        <div class="field-premium">
                                            <label class="label-premium">Extensión</label>
                                            <input type="text" v-model="form.extension_trabajo"
                                                   @input="validateNumeric('extension_trabajo')"
                                                   class="input-premium input-lg"
                                                   :class="{ 'error': numericErrors.extension_trabajo }"
                                                   placeholder="123"
                                                   maxlength="10">
                                            <div v-if="numericErrors.extension_trabajo" class="error-premium">{{ numericErrors.extension_trabajo }}</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Notas -->
                                <div class="section-premium section-notas">
                                    <div class="section-header-premium">
                                        <div class="section-icon-premium" style="background: linear-gradient(135deg, #10b981, #059669);">
                                            <svg class="icon-sm" fill="none" stroke="white" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                            </svg>
                                        </div>
                                        <div class="section-title-wrapper">
                                            <span class="section-title-premium">Notas Adicionales</span>
                                            <span class="badge-optional">Opcional</span>
                                        </div>
                                    </div>
                                    <div class="field-premium full">
                                        <textarea v-model="form.notas"
                                                  class="textarea-premium textarea-lg"
                                                  rows="3"
                                                  placeholder="Información adicional sobre la persona..."
                                                  maxlength="500"></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- TAB 2: DIRECCIÓN -->
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
                                            <input type="text" v-model="form.calle"
                                                   @input="validateText('calle')"
                                                   class="input-premium input-lg"
                                                   :class="{ 'error': textErrors.calle }"
                                                   placeholder="Avenida Reforma">
                                            <div v-if="textErrors.calle" class="error-premium">{{ textErrors.calle }}</div>
                                        </div>
                                        <div class="field-premium">
                                            <label class="label-premium">Número Exterior</label>
                                            <input type="text" v-model="form.numero_exterior"
                                                   @input="validateAlphanumeric('numero_exterior')"
                                                   class="input-premium input-lg"
                                                   :class="{ 'error': alphanumericErrors.numero_exterior }"
                                                   placeholder="123">
                                            <div v-if="alphanumericErrors.numero_exterior" class="error-premium">{{ alphanumericErrors.numero_exterior }}</div>
                                        </div>
                                        <div class="field-premium">
                                            <label class="label-premium">Número Interior</label>
                                            <input type="text" v-model="form.numero_interior"
                                                   @input="validateAlphanumeric('numero_interior')"
                                                   class="input-premium input-lg"
                                                   :class="{ 'error': alphanumericErrors.numero_interior }"
                                                   placeholder="2B">
                                            <div v-if="alphanumericErrors.numero_interior" class="error-premium">{{ alphanumericErrors.numero_interior }}</div>
                                        </div>
                                        <div class="field-premium full">
                                            <label class="label-premium">Colonia</label>
                                            <input type="text" v-model="form.colonia"
                                                   @input="validateText('colonia')"
                                                   class="input-premium input-lg"
                                                   :class="{ 'error': textErrors.colonia }"
                                                   placeholder="Juárez">
                                            <div v-if="textErrors.colonia" class="error-premium">{{ textErrors.colonia }}</div>
                                        </div>
                                        <div class="field-premium">
                                            <label class="label-premium">Ciudad</label>
                                            <input type="text" v-model="form.ciudad"
                                                   @input="validateText('ciudad')"
                                                   class="input-premium input-lg"
                                                   :class="{ 'error': textErrors.ciudad }"
                                                   placeholder="Ciudad de México">
                                            <div v-if="textErrors.ciudad" class="error-premium">{{ textErrors.ciudad }}</div>
                                        </div>
                                        <div class="field-premium">
                                            <label class="label-premium">Municipio</label>
                                            <input type="text" v-model="form.municipio"
                                                   @input="validateText('municipio')"
                                                   class="input-premium input-lg"
                                                   :class="{ 'error': textErrors.municipio }"
                                                   placeholder="Cuauhtémoc">
                                            <div v-if="textErrors.municipio" class="error-premium">{{ textErrors.municipio }}</div>
                                        </div>
                                        <div class="field-premium">
                                            <label class="label-premium">Estado</label>
                                            <input type="text" v-model="form.estado"
                                                   @input="validateText('estado')"
                                                   class="input-premium input-lg"
                                                   :class="{ 'error': textErrors.estado }"
                                                   placeholder="CDMX">
                                            <div v-if="textErrors.estado" class="error-premium">{{ textErrors.estado }}</div>
                                        </div>
                                        <div class="field-premium">
                                            <label class="label-premium">Código Postal</label>
                                            <input type="text" v-model="form.codigo_postal"
                                                   @input="validateCodigoPostal()"
                                                   class="input-premium input-lg"
                                                   :class="{ 'error': codigoPostalError }"
                                                   placeholder="06600"
                                                   maxlength="5">
                                            <div v-if="codigoPostalError" class="error-premium">{{ codigoPostalError }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- TAB 3: REPRESENTANTE LEGAL -->
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
                                            <span class="badge-optional">Opcional</span>
                                        </div>
                                    </div>
                                    <div class="grid-representante">
                                        <!-- Fila 1: Nombre + Apellido Paterno + Apellido Materno -->
                                        <div class="field-premium">
                                            <label class="label-premium">Nombre del Representante</label>
                                            <input type="text" v-model="form.representante_nombre"
                                                   @input="clearError('representante_nombre'); validateText('representante_nombre')"
                                                   class="input-premium input-lg"
                                                   :class="{ 'error': form.errors.representante_nombre || textErrors.representante_nombre }"
                                                   placeholder="María">
                                            <div v-if="form.errors.representante_nombre" class="error-premium">{{ form.errors.representante_nombre }}</div>
                                            <div v-if="textErrors.representante_nombre" class="error-premium">{{ textErrors.representante_nombre }}</div>
                                        </div>
                                        <div class="field-premium">
                                            <label class="label-premium">Apellido Paterno</label>
                                            <input type="text" v-model="form.representante_paterno"
                                                   @input="clearError('representante_paterno'); validateText('representante_paterno')"
                                                   class="input-premium input-lg"
                                                   :class="{ 'error': form.errors.representante_paterno || textErrors.representante_paterno }"
                                                   placeholder="López">
                                            <div v-if="form.errors.representante_paterno" class="error-premium">{{ form.errors.representante_paterno }}</div>
                                            <div v-if="textErrors.representante_paterno" class="error-premium">{{ textErrors.representante_paterno }}</div>
                                        </div>
                                        <div class="field-premium">
                                            <label class="label-premium">Apellido Materno</label>
                                            <input type="text" v-model="form.representante_materno"
                                                   @input="validateText('representante_materno')"
                                                   class="input-premium input-lg"
                                                   :class="{ 'error': textErrors.representante_materno }"
                                                   placeholder="Martínez">
                                            <div v-if="textErrors.representante_materno" class="error-premium">{{ textErrors.representante_materno }}</div>
                                        </div>

                                        <!-- Fila 2: Fecha Nacimiento + Correo + Sexo -->
                                        <div class="field-premium">
                                            <label class="label-premium">Fecha Nacimiento</label>
                                            <input type="date" v-model="form.representante_fecha_nacimiento"
                                                   @change="clearError('representante_fecha_nacimiento')"
                                                   class="input-premium input-lg date"
                                                   :class="{ 'error': form.errors.representante_fecha_nacimiento }"
                                                   :max="fechaMaxima">
                                            <div v-if="form.errors.representante_fecha_nacimiento" class="error-premium">{{ form.errors.representante_fecha_nacimiento }}</div>
                                        </div>
                                        <div class="field-premium">
                                            <label class="label-premium">Sexo</label>
                                            <div class="radio-group-sm radio-lg">
                                                <div class="radio-sm radio-lg" 
                                                     :class="{ 'selected': form.representante_sexo === 'MASCULINO' }"
                                                     @click="form.representante_sexo = 'MASCULINO'">
                                                    <span>Masculino</span>
                                                    <input type="radio" v-model="form.representante_sexo" value="MASCULINO" class="radio-input">
                                                </div>
                                                <div class="radio-sm radio-lg" 
                                                     :class="{ 'selected': form.representante_sexo === 'FEMENINO' }"
                                                     @click="form.representante_sexo = 'FEMENINO'">
                                                    <span>Femenino</span>
                                                    <input type="radio" v-model="form.representante_sexo" value="FEMENINO" class="radio-input">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="field-premium">
                                            <label class="label-premium">Correo del Representante</label>
                                            <input type="email" v-model="form.representante_email"
                                                   @input="validateRepresentanteEmail()"
                                                   class="input-premium input-lg"
                                                   :class="{ 'error': representanteEmailError }"
                                                   placeholder="representante@correo.com">
                                            <div v-if="representanteEmailError" class="error-premium">{{ representanteEmailError }}</div>
                                        </div>
                                        
                                        <!-- Fila 3: Teléfonos -->
                                        <div class="field-premium">
                                            <label class="label-premium">Teléfono Particular</label>
                                            <input type="text" v-model="form.representante_telefono_particular"
                                                   @input="validatePhone('representante_telefono_particular')"
                                                   class="input-premium input-lg"
                                                   :class="{ 'error': phoneErrors.representante_telefono_particular }"
                                                   placeholder="777 123 4567"
                                                   maxlength="10">
                                            <div v-if="phoneErrors.representante_telefono_particular" class="error-premium">{{ phoneErrors.representante_telefono_particular }}</div>
                                        </div>
                                        <div class="field-premium">
                                            <label class="label-premium">Teléfono Trabajo</label>
                                            <input type="text" v-model="form.representante_telefono_trabajo"
                                                   @input="validatePhone('representante_telefono_trabajo')"
                                                   class="input-premium input-lg"
                                                   :class="{ 'error': phoneErrors.representante_telefono_trabajo }"
                                                   placeholder="777 987 6543"
                                                   maxlength="10">
                                            <div v-if="phoneErrors.representante_telefono_trabajo" class="error-premium">{{ phoneErrors.representante_telefono_trabajo }}</div>
                                        </div>
                                        <div class="field-premium">
                                            <label class="label-premium">Extensión</label>
                                            <input type="text" v-model="form.representante_extension_trabajo"
                                                   @input="validateNumeric('representante_extension_trabajo')"
                                                   class="input-premium input-lg"
                                                   :class="{ 'error': numericErrors.representante_extension_trabajo }"
                                                   placeholder="123"
                                                   maxlength="10">
                                            <div v-if="numericErrors.representante_extension_trabajo" class="error-premium">{{ numericErrors.representante_extension_trabajo }}</div>
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
                                <Link :href="route('personas.index')" class="btn-cancel-premium btn-lg">
                                    <svg class="btn-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    Cancelar
                                </Link>
                                <button type="submit" 
                                        :disabled="form.processing || !isFormValid"
                                        class="btn-submit-premium btn-lg">
                                    <span v-if="form.processing" class="spinner-border-sm" role="status"></span>
                                    <svg v-else class="btn-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
// ESTADOS DE VALIDACION
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
// TAB ACTIVO
// ============================================
const activeTab = ref('generales');

// ============================================
// FECHA MAXIMA (18 años)
// ============================================
const fechaMaxima = computed(() => {
    const fecha = new Date();
    fecha.setFullYear(fecha.getFullYear() - 18);
    return fecha.toISOString().split('T')[0];
});

// ============================================
// FUNCION PARA FORMATEAR FECHA
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
// FORMULARIO - CORREGIDO
// ============================================
const form = useForm({
    tipo_persona: props.persona?.tipo_persona || 'FISICA',
    empleado: props.persona?.empleado ?? false, // ✅ CORREGIDO: usa booleano
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
// VALIDACIONES
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

const validateAlphanumeric = (field) => {
    const value = form[field];
    if (!value || value.trim() === '') {
        alphanumericErrors.value[field] = '';
        return;
    }
    if (!/^[a-zA-Z0-9]+$/.test(value)) {
        alphanumericErrors.value[field] = 'Solo letras y números (sin espacios)';
    } else {
        alphanumericErrors.value[field] = '';
    }
};

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
        curpError.value = 'Formato de CURP inválido';
        return;
    }
    curpError.value = '';
};

const validateRFC = () => {
    const rfc = form.rfc;
    if (!rfc) {
        rfcError.value = '';
        return;
    }
    let rfcRegex;
    if (form.tipo_persona === 'FISICA') {
        rfcRegex = /^[A-ZÑ&]{4}[0-9]{6}[A-Z0-9]{3}$/;
        rfcError.value = rfcRegex.test(rfc) ? '' : 'RFC de persona física inválido';
    } else {
        rfcRegex = /^[A-ZÑ&]{3}[0-9]{6}[A-Z0-9]{3}$/;
        rfcError.value = rfcRegex.test(rfc) ? '' : 'RFC de persona moral inválido';
    }
};

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
    edadError.value = edad < 18 ? `Debe ser mayor de edad (${edad} años)` : '';
};

const validateEmail = () => {
    const email = form.email;
    if (!email) { emailError.value = ''; return; }
    const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    emailError.value = emailRegex.test(email) ? '' : 'Correo electrónico inválido';
};

const validateRepresentanteEmail = () => {
    const email = form.representante_email;
    if (!email) { representanteEmailError.value = ''; return; }
    const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    representanteEmailError.value = emailRegex.test(email) ? '' : 'Correo electrónico inválido';
};

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

const clearError = (field) => {
    if (form.errors[field]) delete form.errors[field];
};

// ============================================
// GENERACION DE RFC
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
            message: 'Complete el nombre, apellido paterno y fecha de nacimiento para generar el RFC.',
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
    
    if (form.tipo_persona === 'FISICA' && activeTab.value === 'representante') {
        activeTab.value = 'generales';
    }
    
    if (puedeGenerarRFC.value) {
        generarRFC();
    }
};

// ============================================
// COMPUTED
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
// WATCHERS
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
            alertRef.value?.show({
                type: 'success',
                title: 'Persona actualizada',
                message: 'La persona se ha actualizado exitosamente en el sistema.',
                buttonText: 'Ir al listado'
            });
            setTimeout(() => {
                router.visit(route('personas.index'));
            }, 1500);
        },
        onError: (errors) => {
            if (errors.rfc) {
                alertRef.value?.show({
                    type: 'error',
                    title: 'RFC duplicado',
                    message: 'El RFC que ingresaste ya está registrado en otra persona.',
                    buttonText: 'Entendido'
                });
            } else {
                alertRef.value?.show({
                    type: 'error',
                    title: 'Error al actualizar',
                    message: 'Ocurrió un error al actualizar la persona.',
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
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.8);
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: #6b7280;
    transition: all 0.3s ease;
}

.btn-back-premium:hover {
    background: white;
    color: #1f2937;
    transform: translateX(-2px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.header-title-premium {
    font-size: 1.25rem;
    font-weight: 700;
    color: #111827;
    margin: 0;
    line-height: 1.3;
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
    padding: 6px 18px;
    border-radius: 20px;
    font-size: 0.8rem;
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
}

.status-progress {
    background: linear-gradient(135deg, #e0e7ff, #c7d2fe);
    color: #4338ca;
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
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
    border: 1px solid #f0f2f5;
    padding: 1rem 1.25rem;
    display: flex;
    flex-direction: column;
    max-height: calc(100vh - 180px);
}

/* ===== TABS ===== */
.tabs-premium {
    display: flex;
    gap: 6px;
    margin-bottom: 14px;
    border-bottom: 2px solid #f0f2f5;
    padding-bottom: 2px;
    flex-shrink: 0;
}

.tab-premium {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 22px;
    background: transparent;
    border: none;
    border-bottom: 3px solid transparent;
    font-size: 0.9rem;
    font-weight: 600;
    color: #6b7280;
    cursor: pointer;
    transition: all 0.25s ease;
    border-radius: 8px 8px 0 0;
    white-space: nowrap;
}

.tab-premium:hover {
    color: #1f2937;
    background: #f8f9fa;
}

.tab-premium.active {
    color: #667eea;
    border-bottom-color: #667eea;
    background: rgba(102, 126, 234, 0.06);
}

.tab-icon {
    width: 18px;
    height: 18px;
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
    background: #f1f1f1;
    border-radius: 3px;
}

.tab-content-premium::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 3px;
}

.tab-content-premium::-webkit-scrollbar-thumb:hover {
    background: #9ca3af;
}

.tab-pane-premium {
    display: flex;
    flex-direction: column;
    gap: 12px;
    animation: fadeIn 0.25s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(6px); }
    to { opacity: 1; transform: translateY(0); }
}

/* ===== SECCIONES ===== */
.section-premium {
    background: #fafbfc;
    border-radius: 10px;
    padding: 10px 14px 12px;
    border: 1px solid #f0f2f5;
    transition: all 0.2s ease;
    flex-shrink: 0;
}

.section-premium:hover {
    border-color: #e5e7eb;
    background: #f8f9fa;
}

.section-tipo { border-left: 4px solid #667eea; }
.section-datos { border-left: 4px solid #8b5cf6; }
.section-contacto { border-left: 4px solid #f59e0b; }
.section-direccion { border-left: 4px solid #06b6d4; }
.section-representante { border-left: 4px solid #ec4899; }
.section-notas { border-left: 4px solid #10b981; }

.section-header-premium {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
}

.section-icon-premium {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    border-radius: 8px;
    color: white;
    flex-shrink: 0;
}

.icon-sm {
    width: 15px;
    height: 15px;
}

.section-title-wrapper {
    display: flex;
    align-items: center;
    gap: 8px;
    flex: 1;
    flex-wrap: wrap;
}

.section-title-premium {
    font-size: 0.85rem;
    font-weight: 700;
    color: #1f2937;
    letter-spacing: 0.3px;
}

.badge-required {
    background: #fecaca;
    color: #991b1b;
    padding: 2px 10px;
    border-radius: 4px;
    font-size: 0.6rem;
    font-weight: 700;
}

.badge-optional {
    background: #f3f4f6;
    color: #6b7280;
    padding: 2px 10px;
    border-radius: 4px;
    font-size: 0.6rem;
    font-weight: 600;
}

.optional {
    font-weight: 400;
    color: #9ca3af;
    font-size: 0.65rem;
}

/* ===== GRID SISTEM ===== */
.grid-nombres {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 8px 12px;
}

.grid-3 {
    display: grid;
    grid-template-columns: 1fr 1.2fr 1fr;
    gap: 8px 12px;
}

.grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px 12px;
}

.grid-contacto {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr 1fr;
    gap: 8px 12px;
}

.grid-direccion {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px 12px;
}

.grid-representante {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 8px 12px;
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
    font-size: 0.75rem;
    font-weight: 600;
    color: #4b5563;
    display: flex;
    align-items: center;
    gap: 4px;
}

.star {
    color: #ef4444;
}

/* ===== INPUTS ===== */
.input-premium {
    width: 100%;
    padding: 8px 12px;
    font-size: 0.85rem;
    border: 1.5px solid #e5e7eb;
    border-radius: 8px;
    background: white;
    color: #1f2937;
    transition: all 0.2s ease;
    outline: none;
    height: 40px;
}

.input-premium:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.input-premium.error {
    border-color: #ef4444;
}

.input-premium.error:focus {
    box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
}

.input-premium.date {
    padding: 8px 10px;
}

.select-premium {
    width: 100%;
    padding: 8px 32px 8px 12px;
    font-size: 0.85rem;
    border: 1.5px solid #e5e7eb;
    border-radius: 8px;
    background: white;
    color: #1f2937;
    outline: none;
    height: 40px;
    appearance: none;
    cursor: pointer;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 10px center;
    background-size: 16px;
}

.select-premium:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.select-premium.error {
    border-color: #ef4444;
}

.select-premium.error:focus {
    box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
}

.textarea-premium {
    width: 100%;
    padding: 10px 12px;
    font-size: 0.85rem;
    border: 1.5px solid #e5e7eb;
    border-radius: 8px;
    background: white;
    color: #1f2937;
    outline: none;
    font-family: inherit;
    resize: none;
    min-height: 70px;
    max-height: 120px;
    transition: all 0.2s ease;
}

.textarea-premium:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

/* ===== RADIO ===== */
.radio-group-premium {
    display: flex;
    gap: 12px;
}

.radio-premium {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    padding: 8px 18px;
    background: white;
    border: 1.5px solid #e5e7eb;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 0.85rem;
    font-weight: 500;
    color: #4b5563;
    height: 38px;
    position: relative;
    flex: 1;
}

.radio-premium:hover {
    border-color: #667eea;
}

.radio-premium.selected {
    border-color: #667eea;
    background: #f0f4ff;
    color: #1f2937;
}

.radio-check .check-icon {
    width: 18px;
    height: 18px;
}

.radio-group-sm {
    display: flex;
    gap: 6px;
}

.radio-sm {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 6px 16px;
    background: white;
    border: 1.5px solid #e5e7eb;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 0.8rem;
    font-weight: 600;
    color: #4b5563;
    height: 34px;
    min-width: 60px;
    position: relative;
    flex: 1;
}

.radio-sm:hover {
    border-color: #667eea;
}

.radio-sm.selected {
    border-color: #667eea;
    background: #f0f4ff;
    color: #1f2937;
}

.radio-input {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}

/* ===== INPUT CON BOTON ===== */
.input-with-btn {
    position: relative;
}

.input-with-btn .input-premium {
    padding-right: 100px;
}

.btn-rfc-premium {
    position: absolute;
    right: 6px;
    top: 50%;
    transform: translateY(-50%);
    padding: 5px 16px;
    background: linear-gradient(135deg, #8b5cf6, #7c3aed);
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 0.7rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    white-space: nowrap;
}

.btn-rfc-premium:hover:not(:disabled) {
    transform: translateY(-50%) scale(1.03);
    box-shadow: 0 2px 8px rgba(139, 92, 246, 0.3);
}

.btn-rfc-premium:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.spinner-sm {
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.6s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* ===== ERROR ===== */
.error-premium {
    font-size: 0.7rem;
    color: #ef4444;
    margin-top: 2px;
    display: flex;
    align-items: center;
    gap: 4px;
}

/* ===== FOOTER ===== */
.footer-premium {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 12px;
    margin-top: 10px;
    border-top: 1.5px solid #f0f2f5;
    flex-shrink: 0;
    background: white;
    padding-bottom: 2px;
}

.info-premium {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.75rem;
    color: #6b7280;
}

.info-icon-sm {
    width: 18px;
    height: 18px;
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
    padding: 8px 28px;
    font-weight: 600;
    border: 1.5px solid #e5e7eb;
    border-radius: 10px;
    font-size: 0.85rem;
    transition: all 0.3s ease;
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
}

.btn-submit-premium {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 32px;
    font-weight: 600;
    border: none;
    border-radius: 10px;
    font-size: 0.85rem;
    transition: all 0.3s ease;
    cursor: pointer;
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    height: 42px;
}

.btn-submit-premium:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(16, 185, 129, 0.3);
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
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-right-color: transparent;
    border-radius: 50%;
    animation: spin 0.75s linear infinite;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    .tabs-premium {
        flex-wrap: wrap;
        gap: 4px;
    }
    .tab-premium {
        font-size: 0.75rem;
        padding: 8px 14px;
        flex: 1;
        justify-content: center;
        min-width: 0;
    }
    .tab-icon { width: 16px; height: 16px; }
    
    .grid-nombres,
    .grid-3,
    .grid-2,
    .grid-contacto,
    .grid-direccion,
    .grid-representante {
        grid-template-columns: 1fr;
        gap: 6px;
    }
    
    .form-card-premium {
        padding: 0.8rem 1rem;
        max-height: calc(100vh - 200px);
    }
    
    .footer-premium {
        flex-direction: column;
        gap: 8px;
        align-items: stretch;
    }
    .info-premium { justify-content: center; }
    .actions-premium { justify-content: center; }
    .btn-cancel-premium,
    .btn-submit-premium {
        flex: 1;
        justify-content: center;
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
    .section-premium { padding: 8px 10px 10px; }
    .input-premium {
        font-size: 0.8rem;
        height: 36px;
        padding: 6px 10px;
    }
    .select-premium {
        font-size: 0.8rem;
        height: 36px;
        padding: 6px 28px 6px 10px;
    }
    .label-premium { font-size: 0.7rem; }
    .section-title-premium { font-size: 0.8rem; }
    .btn-cancel-premium,
    .btn-submit-premium {
        font-size: 0.8rem;
        height: 38px;
        padding: 6px 18px;
    }
    .status-badge-premium {
        font-size: 0.7rem;
        padding: 4px 12px;
    }
    .header-title-premium { font-size: 1.05rem; }
    .radio-premium {
        font-size: 0.8rem;
        height: 34px;
        padding: 5px 12px;
    }
    .radio-sm {
        font-size: 0.75rem;
        height: 30px;
        min-width: 50px;
        padding: 4px 12px;
    }
    .btn-rfc-premium {
        font-size: 0.65rem;
        height: 26px;
        padding: 3px 10px;
    }
    .textarea-premium {
        font-size: 0.8rem;
        min-height: 60px;
        padding: 8px 10px;
    }
    .info-premium { font-size: 0.65rem; }
    .form-card-premium { padding: 0.5rem 0.6rem; }
    .grid-nombres,
    .grid-3,
    .grid-2,
    .grid-contacto,
    .grid-direccion,
    .grid-representante {
        gap: 4px;
    }
}
</style>