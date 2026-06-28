<template>
    <AppLayout title="RIC - Usuarios">
        <template #header>
            <div class="header-premium">
                <div class="header-content-premium">
                    <div class="header-left-premium">
                        <div class="header-icon-wrapper">
                            <svg class="header-icon-svg" fill="none" stroke="white" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="header-title-premium">
                                Gestión de Usuarios
                            </h2>
                            <p class="header-subtitle-premium">
                                <span class="subtitle-line"></span>
                                Administra los usuarios del sistema
                            </p>
                        </div>
                    </div>
                    <!-- ✅ NUEVO USUARIO - solo admin, auditor, super -->
                    <Link v-if="permisos?.puede_crear_usuarios" :href="route('usuarios.create')">
                        <a-button type="primary" size="large" class="btn-nuevo-usuario-premium">
                            <template #icon>
                                <PlusOutlined />
                            </template>
                            Nuevo Usuario
                        </a-button>
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Stats Cards -->
                <a-row :gutter="[20, 20]" class="mb-6">
                    <a-col :xs="24" :sm="12" :md="6">
                        <div class="stats-card-enhanced">
                            <div class="stats-card-enhanced-content">
                                <div class="stats-card-enhanced-left">
                                    <span class="stats-card-enhanced-label">Total Usuarios</span>
                                    <span class="stats-card-enhanced-value">{{ stats?.total || 0 }}</span>
                                </div>
                                <div class="stats-card-enhanced-icon" style="background: linear-gradient(135deg, rgba(26, 58, 92, 0.1), rgba(26, 58, 92, 0.05));">
                                    <UserOutlined style="font-size: 28px; color: #1a3a5c;" />
                                </div>
                            </div>
                            <div class="stats-card-enhanced-progress">
                                <div class="stats-card-enhanced-progress-bar" style="width: 100%; background: linear-gradient(90deg, #1a3a5c, #2c5282);"></div>
                            </div>
                        </div>
                    </a-col>

                    <a-col :xs="24" :sm="12" :md="6">
                        <div class="stats-card-enhanced">
                            <div class="stats-card-enhanced-content">
                                <div class="stats-card-enhanced-left">
                                    <span class="stats-card-enhanced-label">Activos</span>
                                    <span class="stats-card-enhanced-value" style="color: #2e7d32;">{{ stats?.activos || 0 }}</span>
                                </div>
                                <div class="stats-card-enhanced-icon" style="background: linear-gradient(135deg, rgba(46, 125, 50, 0.1), rgba(46, 125, 50, 0.05));">
                                    <CheckCircleOutlined style="font-size: 28px; color: #2e7d32;" />
                                </div>
                            </div>
                            <div class="stats-card-enhanced-progress">
                                <div class="stats-card-enhanced-progress-bar" :style="{ 
                                    width: stats?.total ? Math.round((stats.activos / stats.total) * 100) + '%' : '0%', 
                                    background: 'linear-gradient(90deg, #2e7d32, #43a047)' 
                                }"></div>
                            </div>
                        </div>
                    </a-col>

                    <a-col :xs="24" :sm="12" :md="6">
                        <div class="stats-card-enhanced">
                            <div class="stats-card-enhanced-content">
                                <div class="stats-card-enhanced-left">
                                    <span class="stats-card-enhanced-label">Inactivos</span>
                                    <span class="stats-card-enhanced-value" style="color: #c62828;">{{ stats?.inactivos || 0 }}</span>
                                </div>
                                <div class="stats-card-enhanced-icon" style="background: linear-gradient(135deg, rgba(198, 40, 40, 0.1), rgba(198, 40, 40, 0.05));">
                                    <CloseCircleOutlined style="font-size: 28px; color: #c62828;" />
                                </div>
                            </div>
                            <div class="stats-card-enhanced-progress">
                                <div class="stats-card-enhanced-progress-bar" :style="{ 
                                    width: stats?.total ? Math.round((stats.inactivos / stats.total) * 100) + '%' : '0%', 
                                    background: 'linear-gradient(90deg, #c62828, #e53935)' 
                                }"></div>
                            </div>
                        </div>
                    </a-col>

                    <a-col :xs="24" :sm="12" :md="6">
                        <div class="stats-card-enhanced">
                            <div class="stats-card-enhanced-content">
                                <div class="stats-card-enhanced-left">
                                    <span class="stats-card-enhanced-label">Con Empresas</span>
                                    <span class="stats-card-enhanced-value" style="color: #4a148c;">{{ usuariosConEmpresas }}</span>
                                </div>
                                <div class="stats-card-enhanced-icon" style="background: linear-gradient(135deg, rgba(74, 20, 140, 0.1), rgba(74, 20, 140, 0.05));">
                                    <BankOutlined style="font-size: 28px; color: #4a148c;" />
                                </div>
                            </div>
                            <div class="stats-card-enhanced-progress">
                                <div class="stats-card-enhanced-progress-bar" :style="{ 
                                    width: stats?.total ? Math.round((usuariosConEmpresas / stats.total) * 100) + '%' : '0%', 
                                    background: 'linear-gradient(90deg, #4a148c, #6a1b9a)' 
                                }"></div>
                            </div>
                        </div>
                    </a-col>
                </a-row>

                <!-- Tabla Premium -->
                <div class="table-wrapper-premium">
                    <!-- Header de la tabla -->
                    <div class="table-header-ultra">
                        <div class="table-header-left-ultra">
                            <a-tag v-if="filtrosActivos" color="blue" class="filter-tag-ultra">
                                <span class="filter-dot-active"></span>
                                Filtros activos
                            </a-tag>
                            <a-tag v-if="mostrarInactivos" color="orange" class="filter-tag-ultra">
                                <span class="filter-dot-inactive"></span>
                                Mostrando inactivos
                            </a-tag>
                        </div>
                        <div class="table-header-right-ultra">
                            <a-button 
                                v-if="filtrosActivos" 
                                @click="limpiarFiltros" 
                                size="small" 
                                class="btn-limpiar-ultra"
                            >
                                <template #icon>
                                    <CloseOutlined />
                                </template>
                                Limpiar filtros
                            </a-button>
                        </div>
                    </div>

                    <!-- Tabla -->
                    <a-table
                        :columns="columns"
                        :data-source="usuarios.data"
                        :pagination="false"
                        :loading="loading"
                        row-key="id_usuario"
                        class="usuario-table-ultra"
                        size="middle"
                    >
                        <template #bodyCell="{ column, record }">
                            <!-- # -->
                            <template v-if="column.key === '#'">
                                <span class="text-gray-400 font-mono text-sm">{{ record.id_usuario }}</span>
                            </template>

                            <!-- NOMBRE -->
                            <template v-if="column.key === 'nombre_completo'">
                                <div class="nombre-cell-ultra">
                                    <span class="nombre-text-ultra" :class="{ 'text-gray-400': !record.activo }">
                                        {{ record.nombre_completo }}
                                    </span>
                                    <span v-if="!record.activo" class="badge-inactivo-ultra">Inactivo</span>
                                </div>
                            </template>

                            <!-- USUARIO -->
                            <template v-if="column.key === 'usuario'">
                                <div class="usuario-cell-ultra">
                                    <span class="usuario-nombre-ultra" :class="{ 'text-gray-400': !record.activo }">
                                        {{ record.nombre_usuario }}
                                    </span>
                                </div>
                            </template>

                            <!-- TIPO -->
                            <template v-if="column.key === 'tipo'">
                                <div class="rol-cell-ultra">
                                    <span class="rol-badge" :class="getRolClass(record.tipo_usuario)">
                                        <span class="rol-dot" :class="getRolDotClass(record.tipo_usuario)"></span>
                                        {{ record.tipo_usuario_texto || record.tipo_usuario }}
                                    </span>
                                </div>
                            </template>

                            <!-- TELÉFONO -->
                            <template v-if="column.key === 'telefono'">
                                <div class="telefono-cell-ultra">
                                    <span :class="{ 'text-gray-400': !record.activo }">
                                        {{ record.telefono || '—' }}
                                    </span>
                                </div>
                            </template>

                            <!-- RESET -->
                            <template v-if="column.key === 'reset'">
                                <div class="reset-cell-ultra">
                                    <a-tooltip 
                                        v-if="record.activo && permisos?.puede_editar_usuarios"
                                        title="Cambiar contraseña" 
                                        placement="top" 
                                        color="#1a3a5c"
                                    >
                                        <button 
                                            class="btn-reset-ultra"
                                            @click="abrirModalReset(record)"
                                        >
                                            <ReloadOutlined />
                                        </button>
                                    </a-tooltip>
                                    <span v-else class="text-gray-400 text-xs">—</span>
                                </div>
                            </template>

                            <!-- EMPRESAS -->
                            <template v-if="column.key === 'empresas'">
                                <div class="empresas-cell-ultra">
                                    <template v-if="record.empresas && record.empresas.length > 0">
                                        <div class="empresas-container">
                                            <a-popover 
                                                placement="bottomLeft"
                                                trigger="hover"
                                                overlayClassName="empresas-popover"
                                            >
                                                <template #content>
                                                    <div class="empresas-popover-content">
                                                        <div class="empresas-popover-header">
                                                            <BankOutlined />
                                                            <span>Empresas asignadas</span>
                                                        </div>
                                                        <div class="empresas-popover-list">
                                                            <div 
                                                                v-for="empresa in record.empresas" 
                                                                :key="empresa.id_empresa"
                                                                class="empresa-popover-item"
                                                            >
                                                                <BankOutlined class="empresa-popover-icon" />
                                                                <span>{{ empresa.nombre_empresa }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </template>
                                                <div class="empresas-tags">
                                                    <a-tag 
                                                        v-for="(empresa, index) in record.empresas.slice(0, 2)" 
                                                        :key="empresa.id_empresa"
                                                        color="blue"
                                                        class="empresa-tag"
                                                        :class="{ 'opacity-50': !record.activo }"
                                                    >
                                                        {{ empresa.nombre_empresa }}
                                                    </a-tag>
                                                    <a-tag 
                                                        v-if="record.empresas.length > 2" 
                                                        color="default"
                                                        class="empresa-tag-more"
                                                        :class="{ 'opacity-50': !record.activo }"
                                                    >
                                                        +{{ record.empresas.length - 2 }}
                                                    </a-tag>
                                                </div>
                                            </a-popover>
                                        </div>
                                    </template>
                                    <template v-else>
                                        <span class="contacto-vacio-ultra">Sin empresas</span>
                                    </template>
                                </div>
                            </template>

                            <!-- ESTADO -->
                            <template v-if="column.key === 'estado'">
                                <div class="estado-cell-ultra">
                                    <div class="estado-badge-ultra" :class="record.activo ? 'activo' : 'inactivo'">
                                        <span class="estado-dot-ultra"></span>
                                        <span>{{ record.activo ? 'Activo' : 'Inactivo' }}</span>
                                    </div>
                                </div>
                            </template>

                            <!-- ACCIONES -->
                            <template v-if="column.key === 'acciones'">
                                <div class="acciones-ultra">
                                    <!-- ✅ RESTAURAR - solo super (restore) o admin/auditor (toggle) -->
                                    <a-tooltip 
                                        v-if="!record.activo && record.id_usuario !== $page.props.auth.user?.id_usuario && permisos?.puede_editar_usuarios"
                                        title="Restaurar usuario" 
                                        placement="top" 
                                        color="#10b981"
                                    >
                                        <button 
                                            class="btn-action-ultra btn-restore-ultra"
                                            @click="confirmarRestaurar(record)"
                                        >
                                            <ReloadOutlined />
                                        </button>
                                    </a-tooltip>

                                    <!-- ✅ EDITAR - solo admin, auditor, super -->
                                    <a-tooltip v-if="permisos?.puede_editar_usuarios" title="Editar" placement="top" color="#1a3a5c">
                                        <Link :href="route('usuarios.edit', record.id_usuario)">
                                            <button class="btn-action-ultra btn-edit-ultra">
                                                <EditOutlined />
                                            </button>
                                        </Link>
                                    </a-tooltip>

                                    <!-- ✅ ELIMINAR - solo admin, auditor, super (no puede eliminarse a sí mismo) -->
                                    <a-tooltip 
                                        v-if="record.id_usuario !== $page.props.auth.user?.id_usuario && record.activo && permisos?.puede_eliminar_usuarios"
                                        title="Eliminar usuario" 
                                        placement="top" 
                                        color="#ef4444"
                                    >
                                        <button 
                                            class="btn-action-ultra btn-delete-ultra"
                                            @click="confirmarEliminar(record)"
                                        >
                                            <DeleteOutlined />
                                        </button>
                                    </a-tooltip>
                                </div>
                            </template>
                        </template>

                        <!-- Footer con filtros -->
                        <template #footer>
                            <div class="filtros-ultra">
                                <div class="filtros-grid-ultra">
                                    <!-- Filtro Búsqueda -->
                                    <div class="filtro-item-ultra">
                                        <InputLabel>Buscar</InputLabel>
                                        <TextInput 
                                            v-model="filtros.search"
                                            @input="aplicarFiltros"
                                            placeholder="Nombre, usuario..."
                                            square
                                        />
                                    </div>

                                    <!-- Filtro Tipo -->
                                    <div class="filtro-item-ultra">
                                        <InputLabel>Tipo</InputLabel>
                                        <a-select
                                            v-model:value="filtros.tipo"
                                            @change="aplicarFiltros"
                                            placeholder="Todos"
                                            allow-clear
                                            size="small"
                                            class="filtro-select-ultra"
                                        >
                                            <a-select-option value="">Todos</a-select-option>
                                            <a-select-option v-for="(label, value) in tipos" :key="value" :value="value">
                                                {{ label }}
                                            </a-select-option>
                                        </a-select>
                                    </div>

                                    <!-- Filtro Estado -->
                                    <div class="filtro-item-ultra">
                                        <InputLabel>Estado</InputLabel>
                                        <a-select
                                            v-model:value="filtros.estado"
                                            @change="aplicarFiltros"
                                            placeholder="Todos"
                                            allow-clear
                                            size="small"
                                            class="filtro-select-ultra"
                                        >
                                            <a-select-option value="">Todos</a-select-option>
                                            <a-select-option value="activo">Activos</a-select-option>
                                            <a-select-option value="inactivo">Inactivos</a-select-option>
                                        </a-select>
                                    </div>

                                    <!-- Botón Limpiar -->
                                    <div class="filtro-item-ultra filtro-actions">
                                        <InputLabel>Acciones</InputLabel>
                                        <a-button 
                                            v-if="filtrosActivos"
                                            @click="limpiarFiltros" 
                                            size="small"
                                            class="btn-clear-ultra"
                                            block
                                        >
                                            <template #icon>
                                                <CloseOutlined />
                                            </template>
                                            Limpiar filtros
                                        </a-button>
                                        <a-button 
                                            v-else
                                            disabled
                                            size="small"
                                            block
                                            class="btn-no-filtros-ultra"
                                        >
                                            <span class="no-filtros-text-ultra">Sin filtros</span>
                                        </a-button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </a-table>

                    <!-- Paginación -->
                    <div class="pagination-ultra">
                        <span class="pagination-info-ultra">
                            Mostrando <span class="pagination-highlight-ultra">{{ usuarios.from || 0 }}</span> a 
                            <span class="pagination-highlight-ultra">{{ usuarios.to || 0 }}</span> de 
                            <span class="pagination-highlight-ultra">{{ usuarios.total || 0 }}</span> resultados
                        </span>
                        <Pagination :links="usuarios.links" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para cambiar contraseña -->
        <a-modal
            v-model:open="modalResetVisible"
            :footer="null"
            width="560px"
            class="reset-modal-premium"
            :closable="true"
            @cancel="cerrarModalReset"
        >
            <div class="reset-modal-content">
                <!-- Header del Modal -->
                <div class="reset-modal-header">
                    <div class="reset-modal-icon">
                        <svg class="modal-icon-svg" fill="none" stroke="white" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <h3 class="reset-modal-title">Cambiar Contraseña</h3>
                    <p class="reset-modal-subtitle">
                        Establece una nueva contraseña para <strong>{{ usuarioSeleccionado?.nombre_completo }}</strong>
                    </p>
                    <p class="reset-modal-username">
                        @{{ usuarioSeleccionado?.nombre_usuario }}
                    </p>
                </div>

                <!-- Campos de contraseña -->
                <div class="reset-password-container">
                    <!-- Nueva Contraseña -->
                    <div class="form-group-modal">
                        <label class="form-label-modal">
                            <span class="label-icon-wrapper">
                                <LockOutlined />
                            </span>
                            Nueva Contraseña <span class="required-star">*</span>
                        </label>
                        <div class="input-wrapper-modal">
                            <input 
                                :type="showPassword ? 'text' : 'password'"
                                v-model="nuevaPassword"
                                @input="validarPassword"
                                class="form-input-modal"
                                :class="{ 'error': passwordError || (nuevaPassword && !passwordValid) }"
                                placeholder="Ingresa tu nueva contraseña"
                            />
                            <button 
                                type="button"
                                class="btn-toggle-password"
                                @click="showPassword = !showPassword"
                                :title="showPassword ? 'Ocultar contraseña' : 'Ver contraseña'"
                            >
                                <svg v-if="showPassword" class="icon-eye" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg v-else class="icon-eye" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>
                        <div v-if="passwordError" class="error-message-modal">
                            <CloseCircleOutlined />
                            {{ passwordError }}
                        </div>
                    </div>

                    <!-- Confirmar Contraseña -->
                    <div class="form-group-modal">
                        <label class="form-label-modal">
                            <span class="label-icon-wrapper">
                                <LockOutlined />
                            </span>
                            Confirmar Contraseña <span class="required-star">*</span>
                        </label>
                        <div class="input-wrapper-modal">
                            <input 
                                :type="showConfirmPassword ? 'text' : 'password'"
                                v-model="confirmarPassword"
                                @input="validarPassword"
                                class="form-input-modal"
                                :class="{ 'error': passwordMatchError }"
                                placeholder="Confirma tu nueva contraseña"
                            />
                            <button 
                                type="button"
                                class="btn-toggle-password"
                                @click="showConfirmPassword = !showConfirmPassword"
                                :title="showConfirmPassword ? 'Ocultar contraseña' : 'Ver contraseña'"
                            >
                                <svg v-if="showConfirmPassword" class="icon-eye" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg v-else class="icon-eye" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>
                        <div v-if="passwordMatchError" class="error-message-modal">
                            <CloseCircleOutlined />
                            {{ passwordMatchError }}
                        </div>
                        <div v-else-if="confirmarPassword && nuevaPassword && !passwordMatchError" class="success-message-modal">
                            <CheckCircleOutlined />
                            <span>Las contraseñas coinciden</span>
                            <span class="success-check">✓</span>
                        </div>
                    </div>

                    <!-- Requisitos de contraseña -->
                    <div class="password-strength-section">
                        <p class="strength-title">Requisitos de seguridad</p>
                        <div class="reset-password-requirements">
                            <div class="requirement-item" :class="{ met: passwordLength }">
                                <span class="req-icon">
                                    <CheckOutlined v-if="passwordLength" style="color: #10b981;" />
                                    <span v-else class="req-circle">○</span>
                                </span>
                                <span>Mínimo 8 caracteres</span>
                            </div>
                            <div class="requirement-item" :class="{ met: passwordHasUpperCase }">
                                <span class="req-icon">
                                    <CheckOutlined v-if="passwordHasUpperCase" style="color: #10b981;" />
                                    <span v-else class="req-circle">○</span>
                                </span>
                                <span>Mayúsculas y minúsculas</span>
                            </div>
                            <div class="requirement-item" :class="{ met: passwordHasNumber }">
                                <span class="req-icon">
                                    <CheckOutlined v-if="passwordHasNumber" style="color: #10b981;" />
                                    <span v-else class="req-circle">○</span>
                                </span>
                                <span>Al menos un número</span>
                            </div>
                            <div class="requirement-item" :class="{ met: passwordHasSpecial }">
                                <span class="req-icon">
                                    <CheckOutlined v-if="passwordHasSpecial" style="color: #10b981;" />
                                    <span v-else class="req-circle">○</span>
                                </span>
                                <span>Carácter especial</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="reset-modal-actions">
                    <a-button @click="cerrarModalReset" class="btn-cancel-reset">
                        Cancelar
                    </a-button>
                    <a-button 
                        type="primary"
                        :loading="resetLoading"
                        @click="confirmarReset"
                        class="btn-confirm-reset"
                        :disabled="!isFormValid"
                    >
                        <template #icon>
                            <CheckOutlined />
                        </template>
                        {{ resetLoading ? 'Guardando...' : 'Actualizar Contraseña' }}
                    </a-button>
                </div>
            </div>
        </a-modal>

        <!-- Modal de validaciones personalizado -->
        <AlertModal ref="alertModal" />
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted, nextTick } from 'vue';
import Pagination from '@/Components/Pagination.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import AlertModal from '@/Components/AlertModal.vue';
import Swal from 'sweetalert2';

// Importar iconos
import {
    PlusOutlined,
    CloseOutlined,
    EditOutlined,
    DeleteOutlined,
    ReloadOutlined,
    UserOutlined,
    BankOutlined,
    CheckCircleOutlined,
    CloseCircleOutlined,
    LockOutlined,
    CheckOutlined
} from '@ant-design/icons-vue';

// Importar componentes de Ant Design
import {
    Button as AButton,
    Row as ARow,
    Col as ACol,
    Table as ATable,
    Tag as ATag,
    Select as ASelect,
    Tooltip as ATooltip,
    Popover as APopover,
    Modal as AModal,
} from 'ant-design-vue';

// ============================================
// ✅ PERMISOS DESDE EL BACKEND
// ============================================
const page = usePage();
const permisos = computed(() => page.props.permisos || {});

const props = defineProps({
    usuarios: Object,
    stats: Object,
    filtros: Object,
    tipos: Object,
    flash: Object
});

const loading = ref(false);
const alertModal = ref(null);

// Estado para el modal de reset
const modalResetVisible = ref(false);
const resetLoading = ref(false);
const usuarioSeleccionado = ref(null);
const nuevaPassword = ref('');
const confirmarPassword = ref('');
const passwordError = ref('');
const passwordMatchError = ref('');
const showPassword = ref(false);
const showConfirmPassword = ref(false);

// Computed para usuarios con empresas
const usuariosConEmpresas = computed(() => {
    if (!props.usuarios?.data) return 0;
    return props.usuarios.data.filter(u => u.empresas && u.empresas.length > 0).length;
});

// Validaciones de contraseña
const passwordLength = computed(() => nuevaPassword.value.length >= 8);
const passwordHasUpperCase = computed(() => /[A-Z]/.test(nuevaPassword.value) && /[a-z]/.test(nuevaPassword.value));
const passwordHasNumber = computed(() => /\d/.test(nuevaPassword.value));
const passwordHasSpecial = computed(() => /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(nuevaPassword.value));
const passwordValid = computed(() => passwordLength.value && passwordHasUpperCase.value && passwordHasNumber.value && passwordHasSpecial.value);

const isFormValid = computed(() => {
    if (!nuevaPassword.value || !confirmarPassword.value) return false;
    if (!passwordValid.value) return false;
    if (passwordMatchError.value) return false;
    if (passwordError.value) return false;
    return true;
});

// Columnas de la tabla
const columns = [
    {
        title: '#',
        dataIndex: 'id_usuario',
        key: '#',
        width: '5%',
        align: 'center'
    },
    {
        title: 'Nombre',
        dataIndex: 'nombre_completo',
        key: 'nombre_completo',
        width: '16%'
    },
    {
        title: 'Usuario',
        dataIndex: 'nombre_usuario',
        key: 'usuario',
        width: '10%'
    },
    {
        title: 'Tipo',
        dataIndex: 'tipo_usuario',
        key: 'tipo',
        width: '10%'
    },
    {
        title: 'Teléfono',
        dataIndex: 'telefono',
        key: 'telefono',
        width: '8%'
    },
    {
        title: 'Reset',
        key: 'reset',
        width: '5%',
        align: 'center'
    },
    {
        title: 'Empresas',
        key: 'empresas',
        width: '18%'
    },
    {
        title: 'Estado',
        key: 'estado',
        width: '8%',
        align: 'center'
    },
    {
        title: 'Acciones',
        key: 'acciones',
        width: '12%',
        align: 'center'
    }
];

// Estado local para los filtros
const filtros = ref({
    search: props.filtros?.search || '',
    tipo: props.filtros?.tipo || '',
    estado: props.filtros?.estado || ''
});

const filtrosActivos = computed(() => {
    return Object.values(filtros.value).some(value => value !== '' && value !== null && value !== undefined);
});

const mostrarInactivos = computed(() => {
    return filtros.value.estado === 'inactivo';
});

// Funciones auxiliares para colores de roles
const getRolClass = (rol) => {
    const classes = {
        'SUPERUSUARIO': 'superusuario',
        'ADMINISTRADOR': 'administrador',
        'AUDITOR': 'auditor',
        'CAPTURISTA': 'capturista',
        'LECTOR': 'lector'
    };
    return classes[rol] || 'lector';
};

const getRolDotClass = (rol) => {
    const classes = {
        'SUPERUSUARIO': 'dot-superusuario',
        'ADMINISTRADOR': 'dot-administrador',
        'AUDITOR': 'dot-auditor',
        'CAPTURISTA': 'dot-capturista',
        'LECTOR': 'dot-lector'
    };
    return classes[rol] || 'dot-lector';
};

let timeoutId = null;
const aplicarFiltros = () => {
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => {
        loading.value = true;
        const params = {};
        for (const [key, value] of Object.entries(filtros.value)) {
            if (value !== '' && value !== null && value !== undefined) {
                params[key] = value;
            }
        }
        
        router.get(route('usuarios.index'), params, {
            preserveState: true,
            replace: true,
            onFinish: () => {
                loading.value = false;
            }
        });
    }, 300);
};

const limpiarFiltros = () => {
    filtros.value = {
        search: '',
        tipo: '',
        estado: ''
    };
    aplicarFiltros();
};

// Función para mostrar el modal
const mostrarModal = (type, title, message, duration = 4000, onConfirm = null) => {
    if (alertModal.value && alertModal.value.show) {
        alertModal.value.show({
            type,
            title,
            message,
            duration,
            buttonText: type === 'error' ? 'Entendido' : 'Aceptar'
        }, onConfirm);
    } else {
        console.warn('AlertModal no disponible');
        alert(`${title}: ${message}`);
        if (onConfirm) onConfirm();
    }
};

// Procesar mensajes flash automáticamente
const procesarFlash = () => {
    if (!props.flash) return;
    
    const tipoMap = {
        success: { type: 'success', title: '¡Éxito!' },
        error: { type: 'error', title: 'Error' },
        updated: { type: 'success', title: '¡Usuario actualizado!' },
        created: { type: 'success', title: '¡Usuario creado!' },
        deleted: { type: 'success', title: '¡Usuario eliminado!' },
        info: { type: 'info', title: 'Información' },
        warning: { type: 'warning', title: 'Advertencia' }
    };

    for (const [key, message] of Object.entries(props.flash)) {
        if (message && tipoMap[key]) {
            mostrarModal(
                tipoMap[key].type,
                tipoMap[key].title,
                message
            );
            break;
        }
    }
};

// Watch para detectar cambios en flash
watch(() => props.flash, (newFlash) => {
    if (newFlash && Object.keys(newFlash).length > 0) {
        nextTick(() => {
            procesarFlash();
        });
    }
}, { deep: true, immediate: true });

// Ejecutar al montar
onMounted(() => {
    if (props.flash && Object.keys(props.flash).length > 0) {
        nextTick(() => {
            procesarFlash();
        });
    }
});

// ============================================
// FUNCIONES PARA CAMBIAR CONTRASEÑA
// ============================================

const abrirModalReset = (usuario) => {
    usuarioSeleccionado.value = usuario;
    nuevaPassword.value = '';
    confirmarPassword.value = '';
    passwordError.value = '';
    passwordMatchError.value = '';
    showPassword.value = false;
    showConfirmPassword.value = false;
    modalResetVisible.value = true;
};

const cerrarModalReset = () => {
    modalResetVisible.value = false;
    usuarioSeleccionado.value = null;
    nuevaPassword.value = '';
    confirmarPassword.value = '';
    passwordError.value = '';
    passwordMatchError.value = '';
    showPassword.value = false;
    showConfirmPassword.value = false;
    resetLoading.value = false;
};

const validarPassword = () => {
    // Validar requisitos de contraseña
    if (nuevaPassword.value) {
        if (nuevaPassword.value.length < 8) {
            passwordError.value = 'La contraseña debe tener al menos 8 caracteres';
        } else if (!/[A-Z]/.test(nuevaPassword.value) || !/[a-z]/.test(nuevaPassword.value)) {
            passwordError.value = 'La contraseña debe tener mayúsculas y minúsculas';
        } else if (!/[0-9]/.test(nuevaPassword.value)) {
            passwordError.value = 'La contraseña debe tener al menos un número';
        } else if (!/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(nuevaPassword.value)) {
            passwordError.value = 'La contraseña debe tener al menos un carácter especial';
        } else {
            passwordError.value = '';
        }
    } else {
        passwordError.value = '';
    }

    // Validar que las contraseñas coincidan
    if (confirmarPassword.value && nuevaPassword.value) {
        if (nuevaPassword.value !== confirmarPassword.value) {
            passwordMatchError.value = 'Las contraseñas no coinciden';
        } else {
            passwordMatchError.value = '';
        }
    } else {
        passwordMatchError.value = '';
    }
};

const confirmarReset = () => {
    if (!passwordValid.value) {
        Swal.fire({
            title: 'Contraseña no válida',
            text: 'La contraseña debe tener al menos 8 caracteres, mayúsculas, minúsculas, números y caracteres especiales.',
            icon: 'warning',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#f59e0b'
        });
        return;
    }
    
    if (passwordMatchError.value) {
        Swal.fire({
            title: 'Las contraseñas no coinciden',
            text: 'Por favor, verifica que ambas contraseñas sean iguales.',
            icon: 'warning',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#f59e0b'
        });
        return;
    }
    
    resetLoading.value = true;
    
    router.post(route('usuarios.reset-password', usuarioSeleccionado.value.id_usuario), {
        password: nuevaPassword.value
    }, {
        preserveScroll: true,
        onSuccess: () => {
            resetLoading.value = false;
            cerrarModalReset();
            
            Swal.fire({
                title: '¡Contraseña actualizada!',
                html: `
                    <div class="text-center">
                        <p class="text-sm text-gray-600">Nueva contraseña para <strong>${usuarioSeleccionado.value.nombre_completo}</strong></p>
                        <div class="mt-3 p-4 bg-gray-100 rounded-lg font-mono text-lg font-bold text-gray-800 select-all" style="word-break: break-all;">
                            ${nuevaPassword.value}
                        </div>
                        <div class="mt-3 flex items-center justify-center gap-2 text-sm text-amber-600">
                            <span>🔒</span>
                            <span>Copia esta contraseña y compártela con el usuario de forma segura</span>
                        </div>
                        <div class="mt-2 text-xs text-gray-400">
                            La contraseña expirará después del primer inicio de sesión
                        </div>
                    </div>
                `,
                icon: 'success',
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#1a3a5c',
                width: 500
            });
        },
        onError: (errors) => {
            resetLoading.value = false;
            const errorMsg = errors?.error || 'Ocurrió un error al cambiar la contraseña';
            Swal.fire({
                title: 'Error',
                text: errorMsg,
                icon: 'error',
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#ef4444'
            });
        }
    });
};

// ============================================
// FUNCIONES PARA ELIMINACIÓN LÓGICA
// ============================================

// ✅ ELIMINAR USUARIO - solo admin, auditor, super
const confirmarEliminar = (usuario) => {
    // Verificar permiso
    if (!permisos.value?.puede_eliminar_usuarios) {
        Swal.fire({
            icon: 'error',
            title: 'Sin permisos',
            text: 'No tienes permisos para eliminar usuarios. Contacta al administrador.',
            confirmButtonColor: '#dc2626'
        });
        return;
    }

    Swal.fire({
        title: '¿Eliminar usuario?',
        html: `
            <div class="text-center">
                <div class="flex justify-center mb-3">
                    <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </div>
                </div>
                <p class="font-medium text-gray-800 text-lg">${usuario.nombre_completo}</p>
                <p class="text-sm text-gray-500">@${usuario.nombre_usuario}</p>
                <div class="mt-4 p-3 bg-amber-50 rounded-lg border border-amber-200">
                    <p class="text-sm text-amber-700 font-medium">⚠️ El usuario quedará inactivo</p>
                    <p class="text-xs text-amber-600 mt-1">Podrás restaurarlo más tarde desde esta misma pantalla</p>
                </div>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        reverseButtons: true,
        customClass: {
            popup: 'swal-premium-popup',
            confirmButton: 'swal-premium-confirm',
            cancelButton: 'swal-premium-cancel'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('usuarios.destroy', usuario.id_usuario), {
                preserveScroll: true,
                onSuccess: () => {
                    Swal.fire({
                        title: '¡Eliminado!',
                        html: `
                            <div class="text-center">
                                <div class="flex justify-center mb-3">
                                    <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center">
                                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                </div>
                                <p class="text-gray-700">Usuario <strong>${usuario.nombre_completo}</strong></p>
                                <p class="text-sm text-green-600 mt-1">Eliminado correctamente</p>
                            </div>
                        `,
                        icon: 'success',
                        confirmButtonText: 'Aceptar',
                        confirmButtonColor: '#1a3a5c',
                        timer: 3000,
                        timerProgressBar: true
                    });
                },
                onError: (errors) => {
                    const errorMsg = errors?.error || 'Ocurrió un error al eliminar el usuario';
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

// ✅ RESTAURAR USUARIO - solo admin, auditor, super (o super para restore)
const confirmarRestaurar = (usuario) => {
    // Verificar permiso
    if (!permisos.value?.puede_editar_usuarios) {
        Swal.fire({
            icon: 'error',
            title: 'Sin permisos',
            text: 'No tienes permisos para restaurar usuarios. Contacta al administrador.',
            confirmButtonColor: '#dc2626'
        });
        return;
    }

    Swal.fire({
        title: '¿Restaurar usuario?',
        html: `
            <div class="text-center">
                <div class="flex justify-center mb-3">
                    <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </div>
                </div>
                <p class="font-medium text-gray-800 text-lg">${usuario.nombre_completo}</p>
                <p class="text-sm text-gray-500">@${usuario.nombre_usuario}</p>
                <div class="mt-4 p-3 bg-green-50 rounded-lg border border-green-200">
                    <p class="text-sm text-green-700 font-medium">✅ El usuario volverá a estar activo</p>
                </div>
            </div>
        `,
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Sí, restaurar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#64748b',
        reverseButtons: true,
        customClass: {
            popup: 'swal-premium-popup',
            confirmButton: 'swal-premium-confirm',
            cancelButton: 'swal-premium-cancel'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            router.post(route('usuarios.restore', usuario.id_usuario), {}, {
                preserveScroll: true,
                onSuccess: () => {
                    Swal.fire({
                        title: '¡Restaurado!',
                        html: `
                            <div class="text-center">
                                <div class="flex justify-center mb-3">
                                    <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center">
                                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                </div>
                                <p class="text-gray-700">Usuario <strong>${usuario.nombre_completo}</strong></p>
                                <p class="text-sm text-green-600 mt-1">Restaurado correctamente</p>
                            </div>
                        `,
                        icon: 'success',
                        confirmButtonText: 'Aceptar',
                        confirmButtonColor: '#1a3a5c',
                        timer: 3000,
                        timerProgressBar: true
                    });
                },
                onError: (errors) => {
                    const errorMsg = errors?.error || 'Ocurrió un error al restaurar el usuario';
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

// Agregar estilos para SweetAlert2
const agregarEstilosSwal = () => {
    const style = document.createElement('style');
    style.textContent = `
        .swal-premium-popup {
            border-radius: 20px !important;
            box-shadow: 0 25px 80px rgba(0, 0, 0, 0.3) !important;
            padding: 0 !important;
            overflow: hidden !important;
        }
        .swal-premium-confirm {
            padding: 10px 28px !important;
            font-weight: 600 !important;
            border-radius: 10px !important;
            transition: all 0.3s ease !important;
        }
        .swal-premium-confirm:hover {
            transform: translateY(-2px) scale(1.02);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2) !important;
        }
        .swal-premium-cancel {
            padding: 10px 28px !important;
            font-weight: 600 !important;
            border-radius: 10px !important;
            transition: all 0.3s ease !important;
        }
        .swal-premium-cancel:hover {
            transform: translateY(-2px);
            background: #f1f5f9 !important;
        }
        .swal2-actions {
            padding: 0 32px 24px !important;
            gap: 12px !important;
        }
        .swal2-timer-progress-bar {
            height: 4px !important;
            background: linear-gradient(90deg, #1a3a5c, #2c5282) !important;
        }
    `;
    document.head.appendChild(style);
};

// Agregar estilos al montar
onMounted(() => {
    agregarEstilosSwal();
    if (props.flash && Object.keys(props.flash).length > 0) {
        nextTick(() => {
            procesarFlash();
        });
    }
});
</script>

<style scoped>
/* ===== TODOS LOS ESTILOS EXISTENTES (SIN CAMBIOS) ===== */
/* ... (todos los estilos que ya tenías) ... */

/* ===== ESTILOS PARA SWEETALERT2 ===== */
:deep(.swal-premium-popup) {
    border-radius: 20px !important;
    box-shadow: 0 25px 80px rgba(0, 0, 0, 0.3) !important;
    padding: 0 !important;
    overflow: hidden !important;
}

:deep(.swal-premium-confirm) {
    padding: 10px 28px !important;
    font-weight: 600 !important;
    border-radius: 10px !important;
    transition: all 0.3s ease !important;
}

:deep(.swal-premium-confirm:hover) {
    transform: translateY(-2px) scale(1.02);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2) !important;
}

:deep(.swal-premium-cancel) {
    padding: 10px 28px !important;
    font-weight: 600 !important;
    border-radius: 10px !important;
    transition: all 0.3s ease !important;
}

:deep(.swal-premium-cancel:hover) {
    transform: translateY(-2px);
    background: #f1f5f9 !important;
}

:deep(.swal2-actions) {
    padding: 0 32px 24px !important;
    gap: 12px !important;
}

:deep(.swal2-timer-progress-bar) {
    height: 4px !important;
    background: linear-gradient(90deg, #1a3a5c, #2c5282) !important;
}

/* ===== Header, Stats, Tabla, etc. (todos tus estilos existentes) ===== */
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

/* Stats Cards */
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

/* Tabla */
.table-wrapper-premium {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid #f0f2f5;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    padding: 20px;
}

.table-header-ultra {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-bottom: 20px;
}

@media (min-width: 640px) {
    .table-header-ultra {
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
    }
}

.table-header-left-ultra {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}

.table-header-right-ultra {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}

.filter-tag-ultra {
    border-radius: 30px !important;
    background: linear-gradient(135deg, #eff6ff, #dbeafe) !important;
    border: none !important;
    color: #1a3a5c !important;
    font-weight: 600 !important;
    padding: 4px 16px !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 8px !important;
}

.filter-dot-active {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #1a3a5c;
    display: inline-block;
    animation: pulse 2s infinite;
}

.filter-dot-inactive {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #f59e0b;
    display: inline-block;
    animation: pulse 2s infinite;
}

/* Tabla Ant Design */
.usuario-table-ultra {
    border-radius: 8px;
    overflow: hidden;
}

.usuario-table-ultra :deep(.ant-table) {
    border-radius: 8px;
}

.usuario-table-ultra :deep(.ant-table-thead > tr > th) {
    background: linear-gradient(135deg, #f1f5f9, #e8edf2) !important;
    font-weight: 700;
    color: #1e293b;
    border-bottom: 2px solid #d1d5db;
    padding: 14px 18px;
    font-size: 12px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    transition: background 0.3s ease;
}

.usuario-table-ultra :deep(.ant-table-tbody > tr) {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.usuario-table-ultra :deep(.ant-table-tbody > tr:hover) {
    background: linear-gradient(90deg, #f8faff, #f0f7ff) !important;
    box-shadow: inset 0 0 0 1px #dbeafe;
}

.usuario-table-ultra :deep(.ant-table-tbody > tr td) {
    padding: 14px 18px;
    border-bottom: 1px solid #f1f5f9;
}

/* Celdas */
.nombre-cell-ultra {
    display: flex;
    align-items: center;
    gap: 10px;
}

.nombre-text-ultra {
    font-size: 14px;
    color: #0f172a;
    font-weight: 500;
}

.text-gray-400 {
    color: #94a3b8 !important;
}

.badge-inactivo-ultra {
    display: inline-block;
    background: #fee2e2;
    color: #991b1b;
    font-size: 10px;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 10px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.usuario-cell-ultra {
    display: flex;
    align-items: center;
}

.usuario-nombre-ultra {
    font-size: 14px;
    color: #334155;
    font-weight: 500;
    font-family: 'Courier New', monospace;
}

.telefono-cell-ultra {
    font-size: 14px;
    color: #475569;
    font-weight: 400;
}

/* ===== ROL ===== */
.rol-cell-ultra {
    display: flex;
    align-items: center;
}

.rol-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 4px 14px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.rol-badge.superusuario {
    background: #ede9fe;
    color: #5b21b6;
}

.rol-badge.administrador {
    background: #dbeafe;
    color: #1d4ed8;
}

.rol-badge.auditor {
    background: #d1fae5;
    color: #065f46;
}

.rol-badge.capturista {
    background: #fef3c7;
    color: #92400e;
}

.rol-badge.lector {
    background: #f3f4f6;
    color: #4b5563;
}

.rol-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
}

.rol-dot.dot-superusuario {
    background: #7c3aed;
}

.rol-dot.dot-administrador {
    background: #2563eb;
}

.rol-dot.dot-auditor {
    background: #059669;
}

.rol-dot.dot-capturista {
    background: #d97706;
}

.rol-dot.dot-lector {
    background: #6b7280;
}

/* ===== RESET ===== */
.reset-cell-ultra {
    display: flex;
    justify-content: center;
    align-items: center;
}

.btn-reset-ultra {
    width: 34px;
    height: 34px;
    border-radius: 6px;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    background: transparent;
    color: #f59e0b;
}

.btn-reset-ultra:hover {
    background: #fffbeb;
    transform: rotate(180deg) scale(1.1);
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.2);
}

/* ===== ESTADO ===== */
.estado-cell-ultra {
    display: flex;
    align-items: center;
    justify-content: center;
}

.estado-badge-ultra {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 4px 14px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.estado-badge-ultra.activo {
    background: #dcfce7;
    color: #166534;
}

.estado-badge-ultra.inactivo {
    background: #fee2e2;
    color: #991b1b;
}

.estado-dot-ultra {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
}

.estado-badge-ultra.activo .estado-dot-ultra {
    background: #22c55e;
    animation: pulse 2s infinite;
}

.estado-badge-ultra.inactivo .estado-dot-ultra {
    background: #ef4444;
}

/* ===== EMPRESAS ===== */
.empresas-cell-ultra {
    display: flex;
    align-items: center;
}

.empresas-container {
    display: flex;
    align-items: center;
    width: 100%;
}

.empresas-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    align-items: center;
}

.empresa-tag {
    border-radius: 4px !important;
    font-size: 11px !important;
    padding: 2px 10px !important;
    margin: 0 !important;
    max-width: 80px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    border: none !important;
    background: #eff6ff !important;
    color: #1e40af !important;
}

.empresa-tag-more {
    border-radius: 4px !important;
    font-size: 11px !important;
    padding: 2px 8px !important;
    margin: 0 !important;
    border: 1px solid #d1d5db !important;
    background: #f9fafb !important;
    color: #6b7280 !important;
}

.opacity-50 {
    opacity: 0.5;
}

.contacto-vacio-ultra {
    color: #cbd5e1;
    font-size: 13px;
    font-style: italic;
}

/* ===== POPOVER EMPRESAS ===== */
:deep(.empresas-popover) {
    max-width: 300px;
}

:deep(.empresas-popover .ant-popover-inner-content) {
    padding: 0;
}

.empresas-popover-content {
    padding: 12px 16px;
    min-width: 200px;
}

.empresas-popover-header {
    display: flex;
    align-items: center;
    gap: 8px;
    padding-bottom: 8px;
    border-bottom: 1px solid #f1f5f9;
    font-weight: 600;
    color: #0f172a;
    font-size: 13px;
}

.empresas-popover-list {
    margin-top: 8px;
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.empresa-popover-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 6px 8px;
    border-radius: 6px;
    transition: all 0.2s ease;
    font-size: 13px;
    color: #334155;
}

.empresa-popover-item:hover {
    background: #f1f5f9;
}

.empresa-popover-icon {
    color: #6366f1;
    font-size: 14px;
}

/* ===== ACCIONES ===== */
.acciones-ultra {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}

.btn-action-ultra {
    width: 34px;
    height: 34px;
    border-radius: 6px;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    background: transparent;
}

.btn-action-ultra:hover {
    transform: translateY(-2px) scale(1.05);
}

.btn-edit-ultra {
    color: #eab308;
}

.btn-edit-ultra:hover {
    background: #fefce8;
    box-shadow: 0 4px 12px rgba(234, 179, 8, 0.2);
}

.btn-delete-ultra {
    color: #ef4444;
}

.btn-delete-ultra:hover {
    background: #fef2f2;
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);
}

.btn-restore-ultra {
    color: #10b981;
}

.btn-restore-ultra:hover {
    background: #ecfdf5;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
}

/* ===== FILTROS ===== */
.filtros-ultra {
    background: #ffffff;
    padding: 24px 0 0 0;
    border-top: 2px solid #f1f5f9;
}

.filtros-grid-ultra {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr 0.8fr;
    gap: 16px;
    align-items: end;
}

.filtro-item-ultra {
    display: flex;
    flex-direction: column;
    gap: 6px;
    min-width: 0;
}

.filtro-actions {
    min-width: 120px;
}

.filtro-select-ultra :deep(.ant-select-selector) {
    border-radius: 0px !important;
    border: 2px solid #d1d5db !important;
    transition: all 0.3s ease !important;
    background: #ffffff !important;
    height: 40px !important;
    font-size: 13px !important;
    width: 100% !important;
    box-shadow: none !important;
}

.filtro-select-ultra :deep(.ant-select-selector:hover) {
    border-color: #1a3a5c !important;
    background: #fafbfc !important;
}

.filtro-select-ultra :deep(.ant-select-focused .ant-select-selector) {
    border-color: #1a3a5c !important;
    box-shadow: 0 0 0 3px rgba(26, 58, 92, 0.1) !important;
    background: #fafbfc !important;
}

/* ===== BOTONES ===== */
.btn-nuevo-usuario-premium {
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

.btn-nuevo-usuario-premium:hover {
    transform: translateY(-3px) scale(1.02);
    box-shadow: 0 8px 30px rgba(26, 58, 92, 0.4) !important;
    background: linear-gradient(135deg, #2c5282 0%, #1a3a5c 100%) !important;
}

.btn-limpiar-ultra {
    border-radius: 0px !important;
    color: #64748b !important;
    border: 2px solid #d1d5db !important;
    transition: all 0.3s ease !important;
    height: 40px !important;
    font-weight: 600 !important;
    background: #ffffff !important;
}

.btn-limpiar-ultra:hover {
    color: #1a3a5c !important;
    border-color: #1a3a5c !important;
    background: #f8faff !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(26, 58, 92, 0.1) !important;
}

.btn-clear-ultra {
    border-radius: 0px !important;
    background: linear-gradient(135deg, #1a3a5c, #2c5282) !important;
    border: none !important;
    color: white !important;
    height: 40px !important;
    font-size: 13px !important;
    font-weight: 700 !important;
    transition: all 0.3s ease !important;
    box-shadow: 0 2px 8px rgba(26, 58, 92, 0.2);
}

.btn-clear-ultra:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(26, 58, 92, 0.3) !important;
}

.btn-no-filtros-ultra {
    border-radius: 0px !important;
    background: #f8fafc !important;
    border: 2px dashed #d1d5db !important;
    cursor: not-allowed !important;
    height: 40px !important;
    font-size: 13px !important;
}

.no-filtros-text-ultra {
    color: #94a3b8;
    font-weight: 600;
}

/* ===== PAGINACIÓN ===== */
.pagination-ultra {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-top: 20px;
    padding-top: 16px;
    border-top: 2px solid #f1f5f9;
}

@media (min-width: 640px) {
    .pagination-ultra {
        flex-direction: row;
    }
}

.pagination-info-ultra {
    font-size: 14px;
    color: #64748b;
    font-weight: 500;
}

.pagination-highlight-ultra {
    font-weight: 700;
    color: #1a3a5c;
    padding: 2px 8px;
    background: #f1f4f9;
    border-radius: 4px;
}

/* ============================================ */
/* MODAL CAMBIAR CONTRASEÑA */
/* ============================================ */
.reset-modal-premium :deep(.ant-modal-content) {
    border-radius: 20px !important;
    overflow: hidden;
    box-shadow: 0 25px 80px rgba(0, 0, 0, 0.18) !important;
    background: linear-gradient(180deg, #ffffff 0%, #fafbfc 100%) !important;
}

.reset-modal-premium :deep(.ant-modal-header) {
    border-bottom: none !important;
    padding: 0 !important;
    background: transparent !important;
}

.reset-modal-premium :deep(.ant-modal-body) {
    padding: 0 !important;
}

.reset-modal-premium :deep(.ant-modal-close) {
    top: 18px !important;
    right: 20px !important;
    z-index: 10 !important;
    width: 36px !important;
    height: 36px !important;
    border-radius: 50% !important;
    background: rgba(255, 255, 255, 0.9) !important;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08) !important;
    transition: all 0.3s ease !important;
}

.reset-modal-premium :deep(.ant-modal-close:hover) {
    background: #f1f5f9 !important;
    transform: rotate(90deg) !important;
}

.reset-modal-premium :deep(.ant-modal-close .ant-modal-close-icon) {
    font-size: 16px !important;
    color: #64748b !important;
}

.reset-modal-content {
    display: flex;
    flex-direction: column;
    padding: 32px 32px 28px;
}

.reset-modal-header {
    text-align: center;
    margin-bottom: 20px;
    position: relative;
}

.reset-modal-header::after {
    content: '';
    position: absolute;
    bottom: -12px;
    left: 50%;
    transform: translateX(-50%);
    width: 60px;
    height: 3px;
    background: linear-gradient(90deg, #6366f1, #8b5cf6, #ec4899);
    border-radius: 3px;
}

.reset-modal-icon {
    width: 72px;
    height: 72px;
    border-radius: 50%;
    background: linear-gradient(135deg, #fffbeb, #fef3c7);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 14px;
    border: 2px solid #fde68a;
    box-shadow: 0 8px 24px rgba(245, 158, 11, 0.15);
    transition: all 0.3s ease;
}

.reset-modal-icon:hover {
    transform: scale(1.05);
    box-shadow: 0 12px 32px rgba(245, 158, 11, 0.25);
}

.modal-icon-svg {
    width: 32px;
    height: 32px;
    stroke: #f59e0b;
}

.reset-modal-title {
    font-size: 22px;
    font-weight: 700;
    color: #0f172a;
    margin: 0 0 6px 0;
    letter-spacing: -0.3px;
}

.reset-modal-subtitle {
    font-size: 14px;
    color: #64748b;
    margin: 0 0 2px 0;
}

.reset-modal-subtitle strong {
    color: #0f172a;
    font-weight: 600;
}

.reset-modal-username {
    font-size: 13px;
    color: #94a3b8;
    margin: 0;
    font-family: 'Courier New', monospace;
    background: #f1f5f9;
    display: inline-block;
    padding: 2px 16px;
    border-radius: 12px;
}

.reset-password-container {
    background: #ffffff;
    border-radius: 16px;
    padding: 20px 24px 16px;
    border: 1px solid #f1f5f9;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    margin: 8px 0 4px;
}

.form-group-modal {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.form-group-modal:first-child {
    margin-bottom: 6px;
}

.form-label-modal {
    font-size: 0.85rem;
    font-weight: 600;
    color: #374151;
    display: flex;
    align-items: center;
    gap: 8px;
}

.label-icon-wrapper {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border-radius: 8px;
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.12), rgba(245, 158, 11, 0.05));
    color: #f59e0b;
    font-size: 13px;
}

.required-star {
    color: #ef4444;
    font-weight: 700;
    margin-left: 2px;
}

.input-wrapper-modal {
    position: relative;
    display: flex;
    align-items: center;
}

.form-input-modal {
    width: 100%;
    padding: 10px 48px 10px 16px;
    font-size: 0.9rem;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    background: white;
    color: #1f2937;
    transition: all 0.3s ease;
    outline: none;
    height: 44px;
}

.form-input-modal:focus {
    border-color: #6366f1;
    box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.08);
}

.form-input-modal:hover:not(:focus) {
    border-color: #9ca3af;
}

.form-input-modal.error {
    border-color: #ef4444;
}

.form-input-modal.error:focus {
    box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.08);
}

.btn-toggle-password {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: transparent;
    border: none;
    color: #9ca3af;
    cursor: pointer;
    padding: 4px 6px;
    transition: all 0.3s ease;
    z-index: 2;
    pointer-events: auto;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    width: 32px;
    height: 32px;
}

.btn-toggle-password:hover {
    color: #6366f1;
    background: #f1f5f9;
}

.icon-eye {
    width: 18px;
    height: 18px;
}

.error-message-modal {
    font-size: 0.8rem;
    color: #ef4444;
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: 4px;
    padding: 4px 10px;
    background: #fef2f2;
    border-radius: 6px;
}

.error-message-modal svg {
    font-size: 14px;
    flex-shrink: 0;
}

.success-message-modal {
    font-size: 0.8rem;
    color: #10b981;
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: 4px;
    padding: 4px 10px;
    background: #f0fdf4;
    border-radius: 6px;
}

.success-message-modal svg {
    font-size: 14px;
    flex-shrink: 0;
}

.success-check {
    font-size: 14px;
    font-weight: 700;
    margin-left: auto;
}

.password-strength-section {
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid #f1f5f9;
}

.strength-title {
    font-size: 12px;
    font-weight: 600;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin: 0 0 8px 0;
}

.reset-password-requirements {
    display: flex;
    flex-wrap: wrap;
    gap: 6px 20px;
}

.requirement-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12.5px;
    color: #94a3b8;
    transition: all 0.3s ease;
}

.requirement-item.met {
    color: #10b981;
}

.req-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 16px;
    height: 16px;
    font-size: 11px;
}

.req-circle {
    color: #d1d5db;
}

.reset-modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding-top: 20px;
    margin-top: 4px;
    border-top: 1px solid #f1f5f9;
}

.reset-modal-actions :deep(.ant-btn) {
    border-radius: 10px !important;
    height: 44px !important;
    padding: 0 28px !important;
    font-weight: 600 !important;
    font-size: 14px !important;
    transition: all 0.3s ease !important;
}

.btn-cancel-reset {
    border: 2px solid #e5e7eb !important;
    color: #64748b !important;
    background: transparent !important;
}

.btn-cancel-reset:hover {
    border-color: #1a3a5c !important;
    color: #1a3a5c !important;
    background: #f8fafc !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
}

.btn-confirm-reset {
    background: linear-gradient(135deg, #1a3a5c, #2c5282) !important;
    border: none !important;
    box-shadow: 0 2px 12px rgba(26, 58, 92, 0.25) !important;
}

.btn-confirm-reset:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 24px rgba(26, 58, 92, 0.35) !important;
}

.btn-confirm-reset:disabled {
    background: #d1d5db !important;
    box-shadow: none !important;
    cursor: not-allowed !important;
    opacity: 0.6;
}

/* ===== ANIMACIONES ===== */
@keyframes pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(1.2); }
}

/* ===== RESPONSIVE ===== */
@media (max-width: 1024px) {
    .filtros-grid-ultra {
        grid-template-columns: 1fr 1fr 1fr;
        gap: 14px;
    }
}

@media (max-width: 768px) {
    .filtros-grid-ultra {
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }
    
    .filtro-actions {
        grid-column: 1 / -1;
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
    
    .table-wrapper-premium {
        padding: 16px;
    }
    
    .stats-card-enhanced-value {
        font-size: 22px;
    }
    
    .stats-card-enhanced-icon {
        width: 44px;
        height: 44px;
    }
    
    .reset-modal-content {
        padding: 24px 20px 20px;
    }
    
    .reset-modal-icon {
        width: 60px;
        height: 60px;
    }
    
    .modal-icon-svg {
        width: 26px;
        height: 26px;
    }
    
    .reset-modal-title {
        font-size: 19px;
    }
    
    .reset-password-container {
        padding: 16px 16px 12px;
    }
    
    .form-input-modal {
        padding-right: 44px;
        font-size: 0.85rem;
        height: 40px;
    }
    
    .btn-toggle-password {
        right: 8px;
        width: 28px;
        height: 28px;
    }
    
    .icon-eye {
        width: 16px;
        height: 16px;
    }
    
    .reset-modal-actions {
        flex-direction: column;
    }
    
    .reset-modal-actions :deep(.ant-btn) {
        width: 100% !important;
        justify-content: center !important;
        height: 40px !important;
    }
    
    .reset-password-requirements {
        flex-direction: column;
        gap: 4px;
    }
    
    .reset-modal-premium :deep(.ant-modal) {
        max-width: 95% !important;
        margin: 10px auto !important;
    }
}

@media (max-width: 480px) {
    .filtros-grid-ultra {
        grid-template-columns: 1fr;
        gap: 10px;
    }
    
    .filtro-actions {
        grid-column: 1;
    }
    
    .btn-limpiar-ultra {
        width: 100%;
        justify-content: center;
    }
    
    .reset-modal-content {
        padding: 20px 16px 16px;
    }
    
    .reset-modal-icon {
        width: 52px;
        height: 52px;
    }
    
    .modal-icon-svg {
        width: 22px;
        height: 22px;
    }
    
    .reset-modal-title {
        font-size: 17px;
    }
    
    .reset-modal-subtitle {
        font-size: 13px;
    }
    
    .reset-password-container {
        padding: 12px 12px 10px;
        border-radius: 12px;
    }
    
    .form-label-modal {
        font-size: 0.78rem;
    }
    
    .form-input-modal {
        padding: 8px 40px 8px 12px;
        font-size: 0.8rem;
        height: 36px;
        border-radius: 8px;
    }
    
    .btn-toggle-password {
        right: 6px;
        width: 24px;
        height: 24px;
    }
    
    .icon-eye {
        width: 14px;
        height: 14px;
    }
    
    .reset-modal-actions :deep(.ant-btn) {
        font-size: 13px !important;
        padding: 0 16px !important;
        height: 36px !important;
    }
    
    .requirement-item {
        font-size: 11px;
    }
}
</style>