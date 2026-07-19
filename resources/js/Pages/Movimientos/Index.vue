<template>
    <AppLayout title="RIC - Movimientos">
        <div class="py-6">
            <div class="max-w-full px-4 sm:px-6 lg:px-8">
                <!-- Selector de Empresa -->
                <div v-if="empresas.length > 0" class="empresa-selector-premium">
                    <div class="empresa-selector-content">
                        <div class="empresa-selector-label">
                            <ShopOutlined style="font-size: 16px; color: #667eea;" />
                            <span>Empresa</span>
                        </div>
                        <div class="empresa-selector-field">
                            <select 
                                v-model="empresaSeleccionada"
                                @change="cambiarEmpresa"
                                class="empresa-select-native"
                            >
                                <option 
                                    v-for="empresa in empresas" 
                                    :key="empresa.id" 
                                    :value="empresa.id"
                                >
                                    {{ empresa.nombre_empresa }}
                                </option>
                            </select>
                        </div>
                        <div class="empresa-selector-actions">
                            <div class="view-toggle-group">
                                <button 
                                    class="view-toggle-btn" 
                                    :class="{ active: vistaActual === 'normal' }"
                                    @click="cambiarVista('normal')"
                                >
                                    <svg class="view-toggle-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                                    </svg>
                                    Polizas
                                </button>
                                <button 
                                    class="view-toggle-btn" 
                                    :class="{ active: vistaActual === 'diferidas' }"
                                    @click="cambiarVista('diferidas')"
                                >
                                    <svg class="view-toggle-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Diferidas
                                </button>
                                <button 
                                    class="view-toggle-btn" 
                                    :class="{ active: vistaActual === 'traspasos' }"
                                    @click="cambiarVista('traspasos')"
                                >
                                    <svg class="view-toggle-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                    </svg>
                                    Traspasos
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BARRA SUPERIOR: FECHAS + EXCEL + PDF + CHECKBOX TODOS + FILTRO FISCAL -->
                <div v-if="empresas.length > 0" class="filtros-superior-premium">
                    <div class="filtros-superior-content">
                        <!-- Fechas -->
                        <div class="fecha-item">
                            <InputLabel>Desde</InputLabel>
                            <input 
                                type="date" 
                                v-model="filtros.fecha_desde"
                                @change="onFechaDesdeChange"
                                :max="fechaActual"
                                class="fecha-input-premium"
                            />
                        </div>
                        <div class="fecha-item">
                            <InputLabel>Hasta</InputLabel>
                            <input 
                                type="date" 
                                v-model="filtros.fecha_hasta"
                                @change="onFechaHastaChange"
                                class="fecha-input-premium"
                            />
                        </div>
                        <div class="fecha-item fecha-actions">
                            <a-button 
                                size="small" 
                                class="btn-hoy-premium"
                                @click="setFechaHoy"
                            >
                                <template #icon>
                                    <CalendarOutlined />
                                </template>
                                Hoy
                            </a-button>
                            <a-button 
                                v-if="filtros.fecha_desde || filtros.fecha_hasta" 
                                size="small" 
                                class="btn-limpiar-fechas"
                                @click="limpiarFechas"
                            >
                                <template #icon>
                                    <CloseOutlined />
                                </template>
                                Limpiar
                            </a-button>
                        </div>

                        <!-- Separador -->
                        <div class="filtros-separator"></div>

                        <!-- FILTRO: SOLO FISCALES -->
                        <div class="fecha-item checkbox-fiscal-item">
                            <label class="checkbox-fiscal-label">
                                <input 
                                    type="checkbox" 
                                    v-model="soloFiscales"
                                    @change="aplicarFiltros"
                                    class="checkbox-fiscal-input"
                                />
                                <span class="checkbox-fiscal-text">
                                    <FilePdfOutlined style="font-size: 14px;" />
                                    Solo Fiscales
                                </span>
                            </label>
                        </div>

                        <!-- Separador -->
                        <div class="filtros-separator"></div>

                        <!-- Botones Exportacion -->
                        <div class="fecha-item fecha-actions-export">
                            <a-button 
                                size="small" 
                                class="btn-export-excel-mini"
                                @click="exportarExcel"
                            >
                                <template #icon>
                                    <FileExcelOutlined />
                                </template>
                                Excel
                            </a-button>
                            <a-button 
                                size="small" 
                                class="btn-export-pdf-mini"
                                @click="exportarPdf"
                            >
                                <template #icon>
                                    <FilePdfOutlined />
                                </template>
                                PDF
                            </a-button>
                        </div>
                    </div>
                </div>

                <!-- Tabla Premium -->
                <div v-if="empresas.length > 0" class="table-wrapper-premium">
                    <!-- Header de la tabla -->
                    <div class="table-header-ultra">
                        <div class="table-header-left-ultra">
                            <a-tag v-if="filtrosActivos" color="blue" class="filter-tag-ultra">
                                <span class="filter-dot-active"></span>
                                Filtros activos
                            </a-tag>
                            <span v-if="vistaActual === 'diferidas' && movimientos.data && movimientos.data.length > 0" class="total-pendiente-badge">
                                <span class="total-pendiente-label">Total pendiente:</span>
                                <span class="total-pendiente-value">${{ formatNumber(totalPendiente) }}</span>
                            </span>
                        </div>
                        <div class="table-header-right-ultra">
                            <div class="btn-group-actions">
                                <!-- SELECTOR DE COLUMNAS -->
                                <ColumnSelector 
                                    :columnas="columnasDisponibles"
                                    v-model="columnasActivas"
                                    :vista="vistaActual"
                                    storage-key="columnas_visibles"
                                />

                                <!-- NUEVA POLIZA -->
                                <Link 
                                    v-if="permisos?.puede_crear" 
                                    :href="route('movimientos.create')" 
                                    class="btn-nueva-poliza"
                                >
                                    <PlusOutlined />
                                    Nueva Poliza
                                </Link>
                                
                                <!-- NOMINA -->
                                <Link 
                                    v-if="permisos?.puede_crear" 
                                    :href="route('movimientos.nomina.create')" 
                                    class="btn-nomina-poliza"
                                >
                                    <svg class="btn-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Nomina
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- TABLA UNIFICADA -->
                    <div v-if="movimientos.data && movimientos.data.length > 0" class="table-scroll-container">
                        <a-table
                            :columns="columnasActuales"
                            :data-source="movimientos.data"
                            :pagination="false"
                            :loading="loading"
                            row-key="id_movimiento"
                            class="movimiento-table-ultra"
                            size="middle"
                            :scroll="{ x: 'max-content', y: 500 }"
                            sticky
                            @change="handleTableChange"
                            :row-class-name="getRowClassName"
                        >
                            <template #bodyCell="{ column, record }">
                                <!-- 🔥 REFERENCIA - MODIFICADA: más compacta y sin texto gris -->
                                <template v-if="column.key === 'referencia'">
                                    <Link 
                                        :href="route('movimientos.show', record.id_movimiento)" 
                                        class="referencia-link"
                                    >
                                        <span class="referencia-text-ultra">{{ record.referencia || '—' }}</span>
                                        <span v-if="record.es_fiscal" class="fiscal-icon" title="Poliza Fiscal">
                                            <FilePdfOutlined style="font-size: 12px; color: #10b981;" />
                                        </span>
                                        <span v-if="record.es_traspaso" class="traspaso-icon" title="Traspaso">
                                            <svg class="btn-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #8b5cf6;">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                            </svg>
                                        </span>
                                    </Link>
                                </template>

                                <!-- TIPO POLIZA -->
                                <template v-if="column.key === 'tipo_poliza'">
                                    <span class="tipo-badge" :class="getTipoClass(record.tipo_poliza)">
                                        {{ record.tipo_poliza || '—' }}
                                    </span>
                                </template>

                                <!-- CUENTA DESTINO (para traspasos) -->
                                <template v-if="column.key === 'cuenta_destino'">
                                    <span class="cuenta-text-ultra">
                                        <span v-if="record.es_traspaso">
                                            <span class="cuenta-destino-label">→</span>
                                            {{ record.cuenta_destino || '—' }}
                                        </span>
                                        <span v-else class="text-gray-400">—</span>
                                    </span>
                                </template>

                                <!-- FECHA POLIZA -->
                                <template v-if="column.key === 'fecha_poliza'">
                                    <div class="fecha-usuario-cell">
                                        <span class="fecha-text-ultra">{{ formatFecha(record.fecha_poliza) }}</span>
                                        <span v-if="record.usuario" class="usuario-fecha-creacion">
                                            {{ record.usuario }} | {{ formatFechaHora(record.created_at) }}
                                        </span>
                                    </div>
                                </template>

                                <!-- ESTATUS -->
                                <template v-if="column.key === 'estatus'">
                                    <span class="estatus-badge" :class="getEstatusClass(record.estatus)">
                                        {{ record.estatus || '—' }}
                                    </span>
                                </template>

                                <!-- PERSONA -->
                                <template v-if="column.key === 'persona'">
                                    <span class="persona-text-ultra">{{ record.persona || '—' }}</span>
                                </template>

                                <!-- CUENTA -->
                                <template v-if="column.key === 'cuenta'">
                                    <span class="cuenta-text-ultra">{{ record.cuenta || '—' }}</span>
                                </template>

                                <!-- CUENTA FONDEADORA -->
                                <template v-if="column.key === 'cuenta_fondeadora'">
                                    <span class="cuenta-text-ultra">{{ record.cuenta_fondeadora || '—' }}</span>
                                </template>

                                <!-- NOTA -->
                                <template v-if="column.key === 'nota'">
                                    <span class="nota-text-ultra">{{ record.nota || '—' }}</span>
                                </template>

                                <!-- MONTO -->
                                <template v-if="column.key === 'monto'">
                                    <span class="monto-text-ultra" :class="getMontoClass(record.monto)">
                                        ${{ formatNumber(Math.abs(record.monto)) }}
                                    </span>
                                    <span v-if="record.es_traspaso" class="traspaso-amount-badge" title="Monto del traspaso">
                                        🔄
                                    </span>
                                </template>

                                <!-- ABONADO -->
                                <template v-if="column.key === 'abonado'">
                                    <span class="monto-text-ultra abonado-text">
                                        ${{ formatNumber(record.abonado || 0) }}
                                    </span>
                                </template>

                                <!-- SALDO PENDIENTE -->
                                <template v-if="column.key === 'saldo_pendiente'">
                                    <span class="monto-text-ultra" :class="getSaldoPendienteClass(record.saldo_pendiente)">
                                        ${{ formatNumber(record.saldo_pendiente || 0) }}
                                    </span>
                                </template>

                                <!-- VENCIMIENTO -->
                                <template v-if="column.key === 'vencimiento'">
                                    <span class="fecha-text-ultra" :class="getVencimientoClass(record.fecha_vencimiento)">
                                        {{ formatFecha(record.fecha_vencimiento) }}
                                    </span>
                                    <span v-if="isVencido(record.fecha_vencimiento)" class="vencido-icon">!</span>
                                </template>

                                <!-- USUARIO -->
                                <template v-if="column.key === 'usuario'">
                                    <div class="usuario-cell">
                                        <span class="usuario-text-ultra">{{ record.usuario || '—' }}</span>
                                        <span v-if="record.created_at" class="usuario-fecha-ultra">
                                            {{ formatFechaHora(record.created_at) }}
                                        </span>
                                    </div>
                                </template>

                                <!-- RECURSO -->
                                <template v-if="column.key === 'recurso'">
                                    <div class="recurso-cell">
                                        <button 
                                            v-if="record.tiene_recurso"
                                            class="btn-recurso btn-recurso-verde"
                                            @click="verRecurso(record)"
                                            title="Ver recurso adjunto"
                                        >
                                            <svg class="btn-recurso-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            <span class="btn-recurso-texto">Ver</span>
                                        </button>
                                        <button 
                                            v-else
                                            class="btn-recurso btn-recurso-azul"
                                            @click="abrirModalSubir(record)"
                                            title="Subir recurso (PDF o imagen)"
                                        >
                                            <svg class="btn-recurso-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                            </svg>
                                            <span class="btn-recurso-texto">Subir</span>
                                        </button>
                                    </div>
                                </template>

                                <!-- PDF FISCAL -->
                                <template v-if="column.key === 'pdf'">
                                    <div class="pdf-cell">
                                        <a-button 
                                            size="small" 
                                            type="link" 
                                            @click="verPdf(record)"
                                            :disabled="!record.tiene_pdf_fiscal"
                                            class="btn-pdf"
                                            :title="record.tiene_pdf_fiscal ? 'Abrir Comprobante Fiscal PDF' : 'Sin PDF Fiscal'"
                                        >
                                            <FilePdfOutlined :style="{ 
                                                color: getPdfColor(record),
                                                fontSize: '18px',
                                                transition: 'color 0.3s ease'
                                            }" />
                                        </a-button>
                                    </div>
                                </template>

                                <!-- ============================================ -->
                                <!-- ACCIONES -->
                                <!-- ============================================ -->
                                <template v-if="column.key === 'acciones'">
                                    <div class="acciones-cell">
                                        <!-- Botón Abonar (disponible en TODAS las vistas cuando corresponda) -->
                                        <button 
                                            v-if="permisos?.puede_autorizar && record.es_por_pagar && record.saldo_pendiente > 0"
                                            class="btn-accion abonar" 
                                            @click="accionAbonar(record)"
                                            :disabled="record.estatus === 'LIQUIDADO' || record.saldo_pendiente === 0"
                                            title="Abonar"
                                        >
                                            <svg class="btn-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                            </svg>
                                            Abonar
                                        </button>
                                        
                                        <!-- Botón Editar (disponible en TODAS las vistas) -->
                                        <button 
                                            v-if="permisos?.puede_editar"
                                            class="btn-accion editar" 
                                            @click="accionEditar(record)"
                                            title="Editar"
                                        >
                                            <svg class="btn-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                            Editar
                                        </button>
                                        
                                        <!-- Botón Ver (si no tiene permiso de editar) -->
                                        <button 
                                            v-if="!permisos?.puede_editar"
                                            class="btn-accion ver" 
                                            @click="accionVer(record)"
                                            title="Ver"
                                        >
                                            <svg class="btn-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            Ver
                                        </button>
                                    </div>
                                </template>
                            </template>
                        </a-table>
                    </div>

                    <!-- Mensaje si no hay movimientos -->
                    <div v-if="(!movimientos.data || movimientos.data.length === 0) && !loading" class="text-center py-12">
                        <div class="text-6xl mb-4">📭</div>
                        <h3 class="text-xl font-semibold text-gray-700 mb-2">No hay movimientos</h3>
                        <p class="text-gray-500">Comienza creando tu primera poliza.</p>
                    </div>

                    <!-- FILTROS INFERIOR -->
                    <div v-if="movimientos.data && movimientos.data.length > 0" class="filtros-inferior-tabla">
                        <div class="filtros-inferior-grid">
                            <div class="filtro-inferior-item" v-if="vistaActual === 'normal' || vistaActual === 'traspasos'">
                                <label class="filtro-inferior-label">Referencia</label>
                                <input 
                                    v-model="filtros.referencia"
                                    @input="aplicarFiltros"
                                    placeholder="Buscar..."
                                    class="filtro-inferior-input"
                                />
                            </div>

                            <div class="filtro-inferior-item" v-if="vistaActual === 'normal' || vistaActual === 'traspasos'">
                                <label class="filtro-inferior-label">Estatus</label>
                                <select 
                                    v-model="filtros.estatus"
                                    @change="aplicarFiltros"
                                    class="filtro-inferior-select"
                                >
                                    <option value="">Todos</option>
                                    <option value="PENDIENTE">Pendiente</option>
                                    <option value="ABONADO">Abonado</option>
                                    <option value="LIQUIDADO">Liquidado</option>
                                </select>
                            </div>

                            <div class="filtro-inferior-item">
                                <label class="filtro-inferior-label">Persona</label>
                                <input 
                                    v-model="filtros.persona"
                                    @input="aplicarFiltros"
                                    placeholder="Buscar..."
                                    class="filtro-inferior-input"
                                />
                            </div>

                            <div class="filtro-inferior-item" v-if="vistaActual === 'normal'">
                                <label class="filtro-inferior-label">Cuenta</label>
                                <input 
                                    v-model="filtros.cuenta"
                                    @input="aplicarFiltros"
                                    placeholder="Buscar..."
                                    class="filtro-inferior-input"
                                />
                            </div>

                            <div class="filtro-inferior-item" v-if="vistaActual === 'normal'">
                                <label class="filtro-inferior-label">Cta. Fondeo</label>
                                <input 
                                    v-model="filtros.cuenta_fondeadora"
                                    @input="aplicarFiltros"
                                    placeholder="Buscar..."
                                    class="filtro-inferior-input"
                                />
                            </div>

                            <div class="filtro-inferior-item">
                                <label class="filtro-inferior-label">Nota</label>
                                <input 
                                    v-model="filtros.nota"
                                    @input="aplicarFiltros"
                                    placeholder="Buscar..."
                                    class="filtro-inferior-input"
                                />
                            </div>

                            <div class="filtro-inferior-item" v-if="vistaActual === 'diferidas' || vistaActual === 'traspasos'">
                                <label class="filtro-inferior-label">Usuario</label>
                                <input 
                                    v-model="filtros.usuario"
                                    @input="aplicarFiltros"
                                    placeholder="Buscar..."
                                    class="filtro-inferior-input"
                                />
                            </div>

                            <div class="filtro-inferior-item filtro-inferior-actions">
                                <button 
                                    v-if="filtrosActivos" 
                                    class="btn-limpiar-filtros-inferior"
                                    @click="limpiarFiltros"
                                >
                                    <CloseOutlined />
                                    Limpiar filtros
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- RESUMEN DE TOTALES -->
                    <div v-if="movimientos.data && movimientos.data.length > 0" class="resumen-totales-wrapper">
                        <div class="resumen-totales-container-grande">
                            <div class="resumen-totales-header-grande">
                                <span class="resumen-totales-title-grande">Resumen de Totales</span>
                            </div>
                            <div class="resumen-totales-items-grande">
                                <div class="total-item-grande total-ingresos">
                                    <div class="total-icon-grande">
                                        <svg class="total-icon-svg-grande" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                        </svg>
                                    </div>
                                    <div class="total-info-grande">
                                        <span class="total-label-grande">Ingresos</span>
                                        <span class="total-value-grande ingresos-value">${{ formatNumber(totalIngresos) }}</span>
                                    </div>
                                </div>

                                <div class="total-item-grande total-egresos">
                                    <div class="total-icon-grande">
                                        <svg class="total-icon-svg-grande" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"/>
                                        </svg>
                                    </div>
                                    <div class="total-info-grande">
                                        <span class="total-label-grande">Egresos</span>
                                        <span class="total-value-grande egresos-value">-${{ formatNumber(totalEgresos) }}</span>
                                    </div>
                                </div>

                                <div class="total-item-grande total-traspasos" v-if="vistaActual === 'traspasos'">
                                    <div class="total-icon-grande traspaso-icon">
                                        <svg class="total-icon-svg-grande" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                        </svg>
                                    </div>
                                    <div class="total-info-grande">
                                        <span class="total-label-grande">Traspasos</span>
                                        <span class="total-value-grande traspaso-value">${{ formatNumber(totalTraspasos) }}</span>
                                    </div>
                                </div>

                                <div class="total-item-grande total-saldo-neto">
                                    <div class="total-icon-grande saldo-icon">
                                        <svg class="total-icon-svg-grande" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                                        </svg>
                                    </div>
                                    <div class="total-info-grande">
                                        <span class="total-label-grande">Saldo Neto</span>
                                        <span class="total-value-grande saldo-net-value">${{ formatNumber(saldoNeto) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Paginacion -->
                    <div v-if="movimientos.data && movimientos.data.length > 0" class="pagination-ultra">
                        <span class="pagination-info-ultra">
                            Mostrando <span class="pagination-highlight-ultra">{{ movimientos.from || 0 }}</span> a 
                            <span class="pagination-highlight-ultra">{{ movimientos.to || 0 }}</span> de 
                            <span class="pagination-highlight-ultra">{{ movimientos.total || 0 }}</span> resultados
                        </span>
                        <Pagination :links="movimientos.links" />
                    </div>
                </div>

                <!-- Mensaje si no hay empresas -->
                <div v-else class="table-wrapper-premium">
                    <div class="text-center py-12">
                        <div class="text-6xl mb-4">🏢</div>
                        <h3 class="text-xl font-semibold text-gray-700 mb-2">No tienes empresas asignadas</h3>
                        <p class="text-gray-500">Contacta al administrador para que te asigne una empresa.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL DE ALERTAS -->
        <ModalAlert ref="modalAlert" />

        <!-- MODAL DE LIQUIDACIÓN -->
        <ModalLiquidacion 
            ref="modalLiquidacion"
            :movimiento="movimientoSeleccionado"
            :cuentas-fondeadoras="cuentasFondeadorasDisponibles"
            @liquidado="onLiquidado"
        />

        <!-- MODAL PARA SUBIR/VER RECURSO -->
        <a-modal
            v-model:open="modalRecursoVisible"
            :title="modalRecursoTitulo"
            width="90%"
            :footer="null"
            class="modal-recurso-premium"
            :style="{ maxWidth: '900px' }"
        >
            <div class="modal-recurso-content">
                <!-- Si es para ver -->
                <div v-if="modalRecursoModo === 'ver' && modalRecursoUrl">
                    <div v-if="modalRecursoTipo === 'pdf'" class="recurso-pdf-wrapper">
                        <iframe :src="modalRecursoUrl" class="recurso-pdf" frameborder="0"></iframe>
                    </div>
                    <div v-else-if="modalRecursoTipo === 'image'" class="recurso-image-wrapper">
                        <img :src="modalRecursoUrl" alt="Recurso adjunto" class="recurso-image" />
                    </div>
                    <div v-else class="recurso-other">
                        <div class="recurso-other-icon">
                            <svg class="recurso-other-svg" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <p class="recurso-other-text">Vista previa no disponible para este tipo de archivo</p>
                        <a :href="modalRecursoUrl" target="_blank" class="recurso-other-link">Descargar archivo</a>
                    </div>
                </div>

                <!-- Si es para subir -->
                <div v-if="modalRecursoModo === 'subir'" class="recurso-upload-wrapper">
                    <div class="recurso-upload-info">
                        <p>Sube un <strong>PDF</strong> o una <strong>imagen</strong> (JPG, PNG, GIF) para esta póliza.</p>
                        <p class="recurso-upload-hint">Solo se permite un archivo por póliza.</p>
                    </div>

                    <form @submit.prevent="subirRecurso">
                        <div class="recurso-drop-zone" 
                             :class="{ 'recurso-drop-zone-dragover': dragging }"
                             @dragover.prevent="dragging = true"
                             @dragleave.prevent="dragging = false"
                             @drop.prevent="onDrop"
                             @click="$refs.recursoFileInput.click()"
                        >
                            <div class="recurso-drop-zone-content">
                                <svg class="recurso-drop-zone-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                <span v-if="!archivoSeleccionado" class="recurso-drop-zone-text">
                                    Arrastra y suelta tu archivo aquí, o haz clic para seleccionarlo
                                </span>
                                <span v-else class="recurso-drop-zone-text archivo-seleccionado">
                                    <svg class="archivo-seleccionado-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    {{ archivoSeleccionado.name }} ({{ formatFileSize(archivoSeleccionado.size) }})
                                </span>
                                <span class="recurso-drop-zone-hint">Formatos permitidos: PDF, JPG, PNG, GIF, WEBP</span>
                            </div>
                            <input 
                                type="file" 
                                ref="recursoFileInput" 
                                @change="onFileSelect" 
                                accept=".pdf,.jpg,.jpeg,.png,.gif,.webp,image/*"
                                class="recurso-file-input-hidden"
                            >
                        </div>

                        <div v-if="errorRecurso" class="recurso-error">
                            <svg class="error-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ errorRecurso }}
                        </div>

                        <div class="recurso-modal-actions">
                            <button type="button" class="btn-modal-cancel" @click="cerrarModalRecurso">Cancelar</button>
                            <button type="submit" class="btn-modal-submit" :disabled="!archivoSeleccionado || subiendoRecurso">
                                <span v-if="subiendoRecurso" class="spinner-border-sm"></span>
                                <span v-else>Subir archivo</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div v-if="modalRecursoModo === 'ver'" class="recurso-footer">
                <button class="btn-modal-cancel" @click="cerrarModalRecurso">Cerrar</button>
                <a :href="modalRecursoUrl" target="_blank" class="btn-modal-submit" download>
                    <svg class="btn-icon-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Descargar
                </a>
            </div>
        </a-modal>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted, nextTick } from 'vue';
import Pagination from '@/Components/Pagination.vue';
import InputLabel from '@/Components/InputLabel.vue';
import ModalAlert from '@/Components/AlertModal.vue';
import ColumnSelector from '@/Components/ColumnSelector.vue';
import ModalLiquidacion from '@/Components/ModalLiquidacion.vue';
import Swal from 'sweetalert2';
import axios from 'axios';
import {
    PlusOutlined,
    CloseOutlined,
    ShopOutlined,
    FileExcelOutlined,
    FilePdfOutlined,
    CalendarOutlined,
} from '@ant-design/icons-vue';
import {
    Button as AButton,
    Table as ATable,
    Tag as ATag,
    Modal as AModal,
} from 'ant-design-vue';

import { useEmpresa } from '@/composables/useEmpresa';

// ============================================
// PERMISOS
// ============================================
const page = usePage();
const permisos = computed(() => page.props.permisos || {});

const props = defineProps({
    movimientos: {
        type: Object,
        default: () => ({ data: [], from: 0, to: 0, total: 0, links: [] })
    },
    empresas: {
        type: Array,
        default: () => []
    },
    filtros: {
        type: Object,
        default: () => ({})
    },
    flash: {
        type: Object,
        default: () => ({})
    },
    empresa_seleccionada: {
        type: [Number, String],
        default: null
    },
    saldo_total: {
        type: [Number, null],
        default: null
    },
    vista: {
        type: String,
        default: 'normal'
    },
    cuentas_fondeadoras: {
        type: Array,
        default: () => []
    }
});

const { empresaSeleccionada, cargarEmpresaGuardada, guardarEmpresa } = useEmpresa();

// ============================================
// REFS
// ============================================
const loading = ref(false);
const modalAlert = ref(null);
const modalLiquidacion = ref(null);
const movimientoSeleccionado = ref(null);

// MODAL RECURSO
const modalRecursoVisible = ref(false);
const modalRecursoModo = ref('ver');
const modalRecursoTitulo = ref('');
const modalRecursoUrl = ref('');
const modalRecursoTipo = ref('');
const modalRecursoMovimiento = ref(null);

// Archivos
const archivoSeleccionado = ref(null);
const subiendoRecurso = ref(false);
const errorRecurso = ref('');
const dragging = ref(false);
const recursoFileInput = ref(null);

const obtenerFechaLocal = () => {
    const hoy = new Date();
    const year = hoy.getFullYear();
    const month = String(hoy.getMonth() + 1).padStart(2, '0');
    const day = String(hoy.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

const fechaActual = ref(obtenerFechaLocal());
const vistaActual = ref(props.vista || 'normal');
const mostrarTodos = ref(false);
const soloFiscales = ref(false);
const columnasActivas = ref([]);
let timeoutId = null;

const cuentasFondeadorasDisponibles = computed(() => {
    if (props.cuentas_fondeadoras && props.cuentas_fondeadoras.length > 0) {
        return props.cuentas_fondeadoras;
    }
    return [];
});

// ============================================
// COLUMNAS
// ============================================
const getColumnasDisponibles = () => {
    if (vistaActual.value === 'diferidas') {
        return [
            { key: 'vencimiento', title: 'Vencimiento', required: true, visibleByDefault: true },
            { key: 'persona', title: 'Persona', required: true, visibleByDefault: true },
            { key: 'nota', title: 'Nota', required: false, visibleByDefault: true },
            { key: 'monto', title: 'Monto', required: true, visibleByDefault: true },
            { key: 'abonado', title: 'Abonado', required: false, visibleByDefault: true },
            { key: 'saldo_pendiente', title: 'Saldo Pendiente', required: true, visibleByDefault: true },
            { key: 'usuario', title: 'Usuario', required: false, visibleByDefault: true },
            { key: 'recurso', title: 'Recurso', required: false, visibleByDefault: true },
            { key: 'acciones', title: 'Acciones', required: true, visibleByDefault: true },
        ];
    } else if (vistaActual.value === 'traspasos') {
        return [
            { key: 'referencia', title: 'Referencia', required: true, visibleByDefault: true },
            { key: 'tipo_poliza', title: 'Tipo', required: true, visibleByDefault: true },
            { key: 'fecha_poliza', title: 'Fecha Poliza', required: false, visibleByDefault: true },
            { key: 'estatus', title: 'Estatus', required: false, visibleByDefault: true },
            { key: 'persona', title: 'Persona', required: true, visibleByDefault: true },
            { key: 'cuenta', title: 'Cuenta Origen', required: true, visibleByDefault: true },
            { key: 'cuenta_destino', title: 'Cuenta Destino', required: true, visibleByDefault: true },
            { key: 'nota', title: 'Nota', required: false, visibleByDefault: true },
            { key: 'monto', title: 'Monto', required: true, visibleByDefault: true },
            { key: 'recurso', title: 'Recurso', required: false, visibleByDefault: true },
            { key: 'pdf', title: 'PDF', required: false, visibleByDefault: true },
        ];
    } else {
        return [
            { key: 'referencia', title: 'Referencia', required: true, visibleByDefault: true },
            { key: 'fecha_poliza', title: 'Fecha Poliza', required: false, visibleByDefault: true },
            { key: 'estatus', title: 'Estatus', required: false, visibleByDefault: true },
            { key: 'persona', title: 'Persona', required: true, visibleByDefault: true },
            { key: 'cuenta', title: 'Cuenta', required: false, visibleByDefault: true },
            { key: 'cuenta_fondeadora', title: 'Cta. Fondeo', required: false, visibleByDefault: true },
            { key: 'nota', title: 'Nota', required: false, visibleByDefault: true },
            { key: 'monto', title: 'Monto', required: true, visibleByDefault: true },
            { key: 'recurso', title: 'Recurso', required: false, visibleByDefault: true },
            { key: 'pdf', title: 'PDF', required: false, visibleByDefault: true },
        ];
    }
};

const columnasDisponibles = computed(() => getColumnasDisponibles());

// 🔥 COLUMNAS CON REFERENCIA MÁS COMPACTA
const columnasNormal = [
    { title: 'Referencia', key: 'referencia', width: '120px', fixed: 'left' },
    { title: 'Fecha Poliza', key: 'fecha_poliza', width: '160px', align: 'center', sorter: true },
    { title: 'Estatus', key: 'estatus', width: '120px', align: 'center' },
    { title: 'Persona', key: 'persona', width: '180px' },
    { title: 'Cuenta', key: 'cuenta', width: '180px' },
    { title: 'Cta. Fondeo', key: 'cuenta_fondeadora', width: '180px' },
    { title: 'Nota', key: 'nota', width: '200px' },
    { title: 'Monto', key: 'monto', width: '150px', align: 'right' },
    { title: 'Recurso', key: 'recurso', width: '100px', align: 'center', fixed: 'right' },
    { title: 'PDF', key: 'pdf', width: '85px', align: 'center', fixed: 'right' }
];

const columnasTraspasos = [
    { title: 'Referencia', key: 'referencia', width: '120px', fixed: 'left' },
    { title: 'Tipo', key: 'tipo_poliza', width: '100px', align: 'center' },
    { title: 'Fecha Poliza', key: 'fecha_poliza', width: '160px', align: 'center', sorter: true },
    { title: 'Estatus', key: 'estatus', width: '120px', align: 'center' },
    { title: 'Persona', key: 'persona', width: '180px' },
    { title: 'Cuenta Origen', key: 'cuenta', width: '180px' },
    { title: 'Cuenta Destino', key: 'cuenta_destino', width: '180px' },
    { title: 'Nota', key: 'nota', width: '200px' },
    { title: 'Monto', key: 'monto', width: '150px', align: 'right' },
    { title: 'Recurso', key: 'recurso', width: '100px', align: 'center', fixed: 'right' },
    { title: 'PDF', key: 'pdf', width: '85px', align: 'center', fixed: 'right' }
];

const columnasDiferidas = [
    { title: 'Vencimiento', key: 'vencimiento', width: '130px', align: 'center', sorter: true },
    { title: 'Persona', key: 'persona', width: '180px' },
    { title: 'Nota', key: 'nota', width: '200px' },
    { title: 'Monto', key: 'monto', width: '130px', align: 'right' },
    { title: 'Abonado', key: 'abonado', width: '130px', align: 'right' },
    { title: 'Saldo Pendiente', key: 'saldo_pendiente', width: '150px', align: 'right' },
    { title: 'Usuario', key: 'usuario', width: '180px' },
    { title: 'Recurso', key: 'recurso', width: '100px', align: 'center', fixed: 'right' },
    { title: 'Acciones', key: 'acciones', width: '280px', align: 'center', fixed: 'right' }
];

const columnasActuales = computed(() => {
    let todasLasColumnas = [];
    if (vistaActual.value === 'diferidas') {
        todasLasColumnas = columnasDiferidas;
    } else if (vistaActual.value === 'traspasos') {
        todasLasColumnas = columnasTraspasos;
    } else {
        todasLasColumnas = columnasNormal;
    }
    if (!columnasActivas.value || columnasActivas.value.length === 0) {
        return todasLasColumnas;
    }
    return todasLasColumnas.filter(col => columnasActivas.value.includes(col.key));
});

// ============================================
// TOTALES
// ============================================
const totalPendiente = computed(() => {
    if (vistaActual.value !== 'diferidas' || !props.movimientos.data) return 0;
    return props.movimientos.data.reduce((sum, item) => sum + (item.saldo_pendiente || 0), 0);
});

const totalIngresos = computed(() => {
    if (!props.movimientos.data || props.movimientos.data.length === 0) return 0;
    const ingresos = props.movimientos.data.filter(item => item.monto > 0);
    if (ingresos.length === 0) return 0;
    return ingresos.reduce((sum, item) => sum + Math.abs(item.monto || 0), 0);
});

const totalEgresos = computed(() => {
    if (!props.movimientos.data || props.movimientos.data.length === 0) return 0;
    const egresos = props.movimientos.data.filter(item => item.monto < 0);
    if (egresos.length === 0) return 0;
    return egresos.reduce((sum, item) => sum + Math.abs(item.monto || 0), 0);
});

const totalTraspasos = computed(() => {
    if (vistaActual.value !== 'traspasos' || !props.movimientos.data) return 0;
    return props.movimientos.data.reduce((sum, item) => sum + Math.abs(item.monto || 0), 0);
});

const saldoNeto = computed(() => {
    return totalIngresos.value - totalEgresos.value;
});

// ============================================
// FILTROS
// ============================================
const hoy = obtenerFechaLocal();

const filtros = ref({
    fecha_desde: props.filtros?.fecha_desde || '',
    fecha_hasta: props.filtros?.fecha_hasta || '',
    referencia: props.filtros?.referencia || '',
    estatus: props.filtros?.estatus || '',
    persona: props.filtros?.persona || '',
    cuenta: props.filtros?.cuenta || '',
    cuenta_fondeadora: props.filtros?.cuenta_fondeadora || '',
    nota: props.filtros?.nota || '',
    usuario: props.filtros?.usuario || '',
    sort_by: props.filtros?.sort_by || 'fecha_poliza',
    sort_order: props.filtros?.sort_order || 'desc',
    vista: vistaActual.value,
    mostrar_todos: false,
    solo_fiscales: false
});

watch(mostrarTodos, (val) => { filtros.value.mostrar_todos = val; });
watch(soloFiscales, (val) => { filtros.value.solo_fiscales = val; });

const filtrosActivos = computed(() => {
    const { fecha_desde, fecha_hasta, vista, sort_by, sort_order, mostrar_todos, solo_fiscales, ...otros } = filtros.value;
    return Object.values(otros).some(v => v !== '' && v !== null && v !== undefined) ||
           fecha_desde || fecha_hasta || mostrar_todos || solo_fiscales;
});

// ============================================
// FUNCIONES DE FECHAS
// ============================================
const onFechaHastaChange = () => {
    if (filtros.value.fecha_hasta) {
        filtros.value.fecha_desde = filtros.value.fecha_hasta;
    }
    aplicarFiltros();
};

const onFechaDesdeChange = () => {
    aplicarFiltros();
};

const aplicarFiltros = () => {
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => {
        if (!empresaSeleccionada.value) return;
        loading.value = true;
        const params = { 
            empresa_id: empresaSeleccionada.value, 
            vista: vistaActual.value,
            mostrar_todos: mostrarTodos.value,
            solo_fiscales: soloFiscales.value
        };
        for (const [key, value] of Object.entries(filtros.value)) {
            if (value !== '' && value !== null && value !== undefined && 
                key !== 'vista' && key !== 'mostrar_todos' && key !== 'solo_fiscales') {
                params[key] = value;
            }
        }
        router.get(route('movimientos.index'), params, {
            preserveState: true,
            replace: true,
            onFinish: () => { loading.value = false; }
        });
    }, 300);
};

const limpiarFiltros = () => {
    filtros.value = {
        fecha_desde: '',
        fecha_hasta: '',
        referencia: '',
        estatus: '',
        persona: '',
        cuenta: '',
        cuenta_fondeadora: '',
        nota: '',
        usuario: '',
        sort_by: vistaActual.value === 'diferidas' ? 'fecha_vencimiento' : 'fecha_poliza',
        sort_order: vistaActual.value === 'diferidas' ? 'asc' : 'desc',
        vista: vistaActual.value,
        mostrar_todos: false,
        solo_fiscales: false
    };
    mostrarTodos.value = false;
    soloFiscales.value = false;
    aplicarFiltros();
};

const limpiarFechas = () => {
    filtros.value.fecha_desde = '';
    filtros.value.fecha_hasta = '';
    aplicarFiltros();
};

const setFechaHoy = () => {
    const hoy = obtenerFechaLocal();
    filtros.value.fecha_desde = hoy;
    filtros.value.fecha_hasta = hoy;
    aplicarFiltros();
};

const cambiarVista = (vista) => {
    if (vistaActual.value === vista) return;
    vistaActual.value = vista;
    filtros.value.vista = vista;
    filtros.value.sort_by = vista === 'diferidas' ? 'fecha_vencimiento' : 'fecha_poliza';
    filtros.value.sort_order = vista === 'diferidas' ? 'asc' : 'desc';
    soloFiscales.value = false;
    filtros.value.solo_fiscales = false;
    aplicarFiltros();
};

const handleTableChange = (pagination, filters, sorter) => {
    if (sorter && sorter.columnKey) {
        const map = { 'vencimiento': 'fecha_vencimiento', 'fecha_poliza': 'fecha_poliza' };
        filtros.value.sort_by = map[sorter.columnKey] || sorter.columnKey;
        filtros.value.sort_order = sorter.order === 'ascend' ? 'asc' : 'desc';
        aplicarFiltros();
    }
};

// ============================================
// CAMBIAR EMPRESA
// ============================================
const cambiarEmpresa = () => {
    if (empresaSeleccionada.value) {
        guardarEmpresa(empresaSeleccionada.value);
        loading.value = true;
        const params = { 
            empresa_id: empresaSeleccionada.value, 
            vista: vistaActual.value,
            mostrar_todos: mostrarTodos.value,
            solo_fiscales: soloFiscales.value
        };
        for (const [key, value] of Object.entries(filtros.value)) {
            if (value !== '' && value !== null && value !== undefined && 
                key !== 'vista' && key !== 'mostrar_todos' && key !== 'solo_fiscales') {
                params[key] = value;
            }
        }
        router.get(route('movimientos.index'), params, {
            preserveState: true,
            replace: true,
            onFinish: () => { loading.value = false; }
        });
    } else {
        router.get(route('movimientos.index'), { vista: vistaActual.value }, {
            preserveState: true,
            replace: true
        });
    }
};

// ============================================
// CLASES Y ESTILOS
// ============================================
const getEstatusClass = (estatus) => {
    const classes = { 'PENDIENTE': 'pendiente', 'ABONADO': 'abonado', 'LIQUIDADO': 'liquidado' };
    return classes[estatus] || 'pendiente';
};

const getTipoClass = (tipo) => {
    const classes = { 'INGRESO': 'ingreso-tipo', 'EGRESO': 'egreso-tipo', 'TRASPASO': 'traspaso-tipo' };
    return classes[tipo] || '';
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
    hoy.setHours(0, 0, 0, 0);
    const fechaVenc = new Date(fecha);
    fechaVenc.setHours(0, 0, 0, 0);
    if (fechaVenc < hoy) return 'vencido';
    return 'vigente';
};

const isVencido = (fecha) => {
    if (!fecha) return false;
    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0);
    const fechaVenc = new Date(fecha);
    fechaVenc.setHours(0, 0, 0, 0);
    return fechaVenc < hoy;
};

const getRowClassName = (record) => {
    if (vistaActual.value !== 'diferidas') return '';
    if (record.fecha_vencimiento) {
        const hoy = new Date();
        hoy.setHours(0, 0, 0, 0);
        const fechaVenc = new Date(record.fecha_vencimiento);
        fechaVenc.setHours(0, 0, 0, 0);
        if (fechaVenc < hoy) return 'row-vencida';
    }
    return '';
};

const getPdfColor = (record) => {
    if (record.es_fiscal && record.tiene_pdf_fiscal) return '#10b981';
    if (record.es_fiscal && !record.tiene_pdf_fiscal) return '#f59e0b';
    return '#d1d5db';
};

// ============================================
// FORMATOS
// ============================================
const formatNumber = (value) => {
    if (value === null || value === undefined || isNaN(value)) return '0.00';
    return Number(value).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const formatFecha = (fecha) => {
    if (!fecha) return '—';
    const d = new Date(fecha);
    return d.toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' });
};

const formatFechaHora = (fecha) => {
    if (!fecha) return '';
    const d = new Date(fecha);
    return d.toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' }) + 
           ' | ' + d.toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' });
};

const formatFileSize = (bytes) => {
    if (!bytes) return '—';
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(1024));
    const value = bytes / Math.pow(1024, i);
    return `${value.toFixed(i > 0 ? 1 : 0)} ${sizes[i]}`;
};

// ============================================
// RECURSO - FUNCIONES DEL MODAL
// ============================================
const abrirModalSubir = (record) => {
    modalRecursoMovimiento.value = record;
    modalRecursoModo.value = 'subir';
    modalRecursoTitulo.value = `Subir recurso - ${record.referencia || 'Póliza'}`;
    modalRecursoUrl.value = '';
    modalRecursoTipo.value = '';
    archivoSeleccionado.value = null;
    errorRecurso.value = '';
    modalRecursoVisible.value = true;
};

const verRecurso = (record) => {
    modalRecursoMovimiento.value = record;
    modalRecursoModo.value = 'ver';
    modalRecursoTitulo.value = `Póliza - ${record.referencia || 'Póliza'}`;
    modalRecursoUrl.value = record.recurso_url || '';
    
    if (record.recurso_tipo) {
        modalRecursoTipo.value = record.recurso_tipo;
    } else {
        const url = modalRecursoUrl.value.toLowerCase();
        if (url.endsWith('.pdf')) {
            modalRecursoTipo.value = 'pdf';
        } else if (url.endsWith('.jpg') || url.endsWith('.jpeg') || url.endsWith('.png') || url.endsWith('.gif') || url.endsWith('.webp')) {
            modalRecursoTipo.value = 'image';
        } else {
            modalRecursoTipo.value = 'other';
        }
    }
    
    modalRecursoVisible.value = true;
};

const cerrarModalRecurso = () => {
    modalRecursoVisible.value = false;
    modalRecursoModo.value = 'ver';
    modalRecursoMovimiento.value = null;
    archivoSeleccionado.value = null;
    errorRecurso.value = '';
    dragging.value = false;
};

// === DROP ZONE ===
const onDrop = (e) => {
    dragging.value = false;
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        procesarArchivo(files[0]);
    }
};

const onFileSelect = (e) => {
    const files = e.target.files;
    if (files.length > 0) {
        procesarArchivo(files[0]);
    }
    e.target.value = '';
};

const procesarArchivo = (file) => {
    const tiposPermitidos = ['application/pdf', 'image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    const extensionesPermitidas = ['.pdf', '.jpg', '.jpeg', '.png', '.gif', '.webp'];
    
    const extension = '.' + file.name.split('.').pop().toLowerCase();
    
    if (!tiposPermitidos.includes(file.type) && !extensionesPermitidas.includes(extension)) {
        errorRecurso.value = 'Tipo de archivo no permitido. Solo PDF e imágenes (JPG, PNG, GIF, WEBP).';
        archivoSeleccionado.value = null;
        return;
    }
    
    if (file.size > 10 * 1024 * 1024) {
        errorRecurso.value = 'El archivo no debe exceder 10 MB.';
        archivoSeleccionado.value = null;
        return;
    }
    
    errorRecurso.value = '';
    archivoSeleccionado.value = file;
};

// === SUBIR RECURSO ===
const subirRecurso = async () => {
    if (!archivoSeleccionado.value || !modalRecursoMovimiento.value) return;
    
    subiendoRecurso.value = true;
    errorRecurso.value = '';
    
    const formData = new FormData();
    formData.append('archivo', archivoSeleccionado.value);
    formData.append('id_poliza', modalRecursoMovimiento.value.id_poliza || modalRecursoMovimiento.value.id_movimiento);
    
    try {
        const response = await axios.post(
            route('movimientos.archivos.subir', modalRecursoMovimiento.value.id_poliza || modalRecursoMovimiento.value.id_movimiento),
            formData,
            { headers: { 'Content-Type': 'multipart/form-data' } }
        );
        
        if (response.data.success) {
            mostrarModal('success', 'Éxito', 'Recurso subido correctamente');
            cerrarModalRecurso();
            router.reload({ only: ['movimientos'] });
        } else {
            throw new Error(response.data.message || 'Error al subir el recurso');
        }
    } catch (error) {
        errorRecurso.value = error.response?.data?.message || error.message || 'Error al subir el recurso';
        mostrarModal('error', 'Error', errorRecurso.value);
    } finally {
        subiendoRecurso.value = false;
    }
};

// ============================================
// ACCIONES
// ============================================
const mostrarModal = (type, title, message, duration = 4000) => {
    if (modalAlert.value && modalAlert.value.show) {
        modalAlert.value.show({ type, title, message, buttonText: type === 'error' ? 'Entendido' : 'Aceptar', duration });
    } else {
        const iconMap = { success: 'success', error: 'error', info: 'info', warning: 'warning' };
        Swal.fire({ icon: iconMap[type] || 'info', title: title || 'Información', text: message, confirmButtonColor: '#1a3a5c', confirmButtonText: 'Aceptar', timer: duration || 4000, timerProgressBar: true });
    }
};

const accionAbonar = (record) => {
    if (record.saldo_pendiente <= 0) {
        mostrarModal('warning', 'Ya liquidado', 'Esta poliza ya está completamente liquidada.');
        return;
    }
    router.get(route('movimientos.abono', record.id_movimiento));
};

const accionLiquidar = (record) => {
    if (record.saldo_pendiente <= 0) {
        mostrarModal('warning', 'Ya liquidado', 'Esta poliza ya está completamente liquidada.');
        return;
    }
    movimientoSeleccionado.value = record;
    if (modalLiquidacion.value) {
        modalLiquidacion.value.open();
    } else {
        mostrarModal('error', 'Error', 'No se pudo abrir el modal de liquidación.');
    }
};

const onLiquidado = (data) => {
    mostrarModal('success', 'Liquidación exitosa', data.message || 'La poliza ha sido liquidada completamente.');
    setTimeout(() => {
        window.location.reload();
    }, 1500);
};

const accionEditar = (record) => {
    console.log('🟢 Editando póliza ID:', record.id_movimiento);
    router.get(route('movimientos.edit', record.id_movimiento));
};

const accionVer = (record) => {
    console.log('🔵 Viendo póliza ID:', record.id_movimiento);
    router.get(route('movimientos.show', record.id_movimiento));
};

const verPdf = (record) => {
    if (record.es_fiscal && record.tiene_pdf_fiscal && record.pdf_url) {
        window.open(record.pdf_url, '_blank');
    } else if (record.es_fiscal && !record.tiene_pdf_fiscal) {
        mostrarModal('info', 'PDF no disponible', 'Esta poliza fiscal no tiene un PDF asociado.');
    } else {
        mostrarModal('info', 'Sin PDF Fiscal', 'Esta poliza no es fiscal.');
    }
};

const exportarExcel = () => {
    if (!empresaSeleccionada.value) {
        mostrarModal('warning', 'Sin empresa', 'Selecciona una empresa primero');
        return;
    }
    const params = new URLSearchParams();
    params.append('empresa_id', empresaSeleccionada.value);
    params.append('vista', vistaActual.value);
    params.append('mostrar_todos', mostrarTodos.value);
    params.append('solo_fiscales', soloFiscales.value);
    for (const [key, value] of Object.entries(filtros.value)) {
        if (value !== '' && value !== null && value !== undefined && 
            key !== 'vista' && key !== 'mostrar_todos' && key !== 'solo_fiscales') {
            params.append(key, value);
        }
    }
    window.open(route('movimientos.export.excel') + '?' + params.toString(), '_blank');
};

const exportarPdf = () => {
    if (!empresaSeleccionada.value) {
        mostrarModal('warning', 'Sin empresa', 'Selecciona una empresa primero');
        return;
    }
    const params = new URLSearchParams();
    params.append('empresa_id', empresaSeleccionada.value);
    params.append('vista', vistaActual.value);
    params.append('mostrar_todos', mostrarTodos.value);
    params.append('solo_fiscales', soloFiscales.value);
    for (const [key, value] of Object.entries(filtros.value)) {
        if (value !== '' && value !== null && value !== undefined && 
            key !== 'vista' && key !== 'mostrar_todos' && key !== 'solo_fiscales') {
            params.append(key, value);
        }
    }
    window.open(route('movimientos.export.pdf') + '?' + params.toString(), '_blank');
};

// ============================================
// LIFECYCLE
// ============================================
onMounted(() => {
    if (columnasActivas.value.length === 0) {
        const defaultKeys = getColumnasDisponibles().filter(col => col.visibleByDefault !== false).map(col => col.key);
        columnasActivas.value = defaultKeys;
    }
    
    const empresaGuardada = cargarEmpresaGuardada();
    
    if (empresaGuardada && props.empresas.some(e => e.id === empresaGuardada)) {
        empresaSeleccionada.value = empresaGuardada;
    } else if (props.empresa_seleccionada) {
        empresaSeleccionada.value = parseInt(props.empresa_seleccionada);
        guardarEmpresa(props.empresa_seleccionada);
    } else if (props.empresas && props.empresas.length > 0) {
        empresaSeleccionada.value = props.empresas[0].id;
        guardarEmpresa(props.empresas[0].id);
    }
    
    if (empresaSeleccionada.value) {
        const hoy = obtenerFechaLocal();
        if (!filtros.value.fecha_desde && !filtros.value.fecha_hasta) {
            filtros.value.fecha_desde = hoy;
            filtros.value.fecha_hasta = hoy;
        }
        aplicarFiltros();
    }
    
    if (props.flash && Object.keys(props.flash).length > 0) {
        nextTick(() => {
            const tipoMap = {
                success: { type: 'success', title: 'Exito' },
                error: { type: 'error', title: 'Error' },
                updated: { type: 'success', title: 'Actualizado' },
                created: { type: 'success', title: 'Creado' },
                deleted: { type: 'success', title: 'Eliminado' },
                info: { type: 'info', title: 'Informacion' },
                warning: { type: 'warning', title: 'Advertencia' }
            };
            for (const [key, message] of Object.entries(props.flash)) {
                if (message && tipoMap[key]) {
                    mostrarModal(tipoMap[key].type, tipoMap[key].title, message);
                    break;
                }
            }
        });
    }
});
</script>

<style scoped>
/* ========== ESTILOS COMPLETOS ========== */
.empresa-selector-premium {
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #f0f2f5;
    padding: 10px 20px;
    margin-bottom: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.empresa-selector-content {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

@media (min-width: 640px) {
    .empresa-selector-content {
        flex-direction: row;
        align-items: center;
        gap: 12px;
    }
}

.empresa-selector-label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    font-weight: 600;
    color: #0f172a;
    white-space: nowrap;
}

.empresa-selector-field {
    flex: 1;
    min-width: 150px;
}

.empresa-select-native {
    width: 100%;
    padding: 6px 12px;
    border: 2px solid #e2e8f0;
    border-radius: 6px;
    font-size: 14px;
    background: #fafbfc;
    color: #0f172a;
    transition: all 0.3s ease;
    outline: none;
    height: 36px;
    cursor: pointer;
}

.empresa-select-native:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    background: #ffffff;
}

.view-toggle-group {
    display: flex;
    align-items: center;
    gap: 0;
    border-radius: 8px;
    overflow: hidden;
    border: 2px solid #e2e8f0;
    background: #f8fafc;
}

.view-toggle-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 6px 16px;
    border: none;
    background: transparent;
    font-size: 12px;
    font-weight: 600;
    color: #64748b;
    cursor: pointer;
    transition: all 0.3s ease;
}

.view-toggle-btn:hover {
    color: #1a3a5c;
    background: #f1f5f9;
}

.view-toggle-btn.active {
    background: #1a3a5c;
    color: white;
}

.view-toggle-icon {
    width: 16px;
    height: 16px;
}

.view-toggle-btn:not(:last-child) {
    border-right: 1px solid #e2e8f0;
}

/* --- FILTROS SUPERIOR --- */
.filtros-superior-premium {
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #f0f2f5;
    padding: 12px 20px;
    margin-bottom: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.filtros-superior-content {
    display: flex;
    flex-direction: row;
    align-items: flex-end;
    gap: 12px;
    flex-wrap: wrap;
}

.fecha-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 120px;
}

.fecha-item :deep(.input-label) {
    font-size: 11px !important;
    font-weight: 600 !important;
    color: #475569 !important;
    margin-bottom: 0 !important;
}

.fecha-input-premium {
    padding: 6px 10px;
    border: 2px solid #d1d5db;
    border-radius: 6px;
    font-size: 12px;
    height: 36px;
    background: #ffffff;
    color: #0f172a;
    transition: all 0.3s ease;
    outline: none;
    width: 100%;
    min-width: 120px;
}

.fecha-input-premium:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.filtros-separator {
    width: 1px;
    height: 36px;
    background: #e2e8f0;
    flex-shrink: 0;
}

.fecha-actions {
    display: flex;
    flex-direction: row;
    align-items: flex-end;
    gap: 8px;
    min-width: auto;
}

.fecha-actions-export {
    display: flex;
    flex-direction: row;
    align-items: flex-end;
    gap: 8px;
    min-width: auto;
    margin-left: auto;
}

@media (max-width: 768px) {
    .fecha-actions-export {
        margin-left: 0;
        width: 100%;
        flex-wrap: wrap;
    }
}

.btn-hoy-premium {
    border-radius: 6px !important;
    background: linear-gradient(135deg, #1a3a5c, #2c5282) !important;
    border: none !important;
    color: white !important;
    height: 36px !important;
    font-weight: 600 !important;
    padding: 0 16px !important;
    transition: all 0.3s ease !important;
}

.btn-hoy-premium:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(26, 58, 92, 0.3) !important;
}

.btn-limpiar-fechas {
    border-radius: 6px !important;
    color: #64748b !important;
    border: 2px solid #d1d5db !important;
    height: 36px !important;
    font-weight: 600 !important;
    background: #ffffff !important;
    transition: all 0.3s ease !important;
}

.btn-limpiar-fechas:hover {
    color: #1a3a5c !important;
    border-color: #1a3a5c !important;
    transform: translateY(-2px);
}

.btn-export-excel-mini {
    border-radius: 6px !important;
    background: linear-gradient(135deg, #217346, #2e7d32) !important;
    border: none !important;
    color: white !important;
    height: 36px !important;
    font-weight: 600 !important;
    padding: 0 16px !important;
    transition: all 0.3s ease !important;
}

.btn-export-excel-mini:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(33, 115, 70, 0.3) !important;
}

.btn-export-pdf-mini {
    border-radius: 6px !important;
    background: linear-gradient(135deg, #dc2626, #b91c1c) !important;
    border: none !important;
    color: white !important;
    height: 36px !important;
    font-weight: 600 !important;
    padding: 0 16px !important;
    transition: all 0.3s ease !important;
}

.btn-export-pdf-mini:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3) !important;
}

.checkbox-fiscal-item {
    min-width: auto;
    justify-content: center;
}

.checkbox-fiscal-label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    padding: 4px 12px;
    border-radius: 6px;
    transition: all 0.3s ease;
    height: 36px;
    background: #f0fdf4;
    border: 2px solid #86efac;
}

.checkbox-fiscal-label:hover {
    background: #dcfce7;
}

.checkbox-fiscal-input {
    width: 18px;
    height: 18px;
    cursor: pointer;
    accent-color: #10b981;
}

.checkbox-fiscal-text {
    font-size: 13px;
    font-weight: 600;
    color: #166534;
    white-space: nowrap;
    display: flex;
    align-items: center;
    gap: 6px;
}

/* --- BOTONES DE ACCION --- */
.btn-nueva-poliza {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 0 20px;
    height: 36px;
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.2);
}

.btn-nueva-poliza:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(16, 185, 129, 0.4);
    color: white;
}

.btn-nomina-poliza {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 0 20px;
    height: 36px;
    background: linear-gradient(135deg, #8b5cf6, #7c3aed);
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    box-shadow: 0 2px 8px rgba(139, 92, 246, 0.2);
}

.btn-nomina-poliza:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(139, 92, 246, 0.4);
    color: white;
}

.btn-icon-sm {
    width: 16px;
    height: 16px;
}

/* --- TABLA --- */
.table-wrapper-premium {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid #f0f2f5;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    padding: 20px;
}

.table-scroll-container {
    overflow: hidden;
    border-radius: 8px;
    border: 1px solid #f1f5f9;
}

.table-scroll-container :deep(.ant-table-body) {
    max-height: 500px !important;
    overflow-y: auto !important;
}

.table-scroll-container :deep(.ant-table-body)::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}

.table-scroll-container :deep(.ant-table-body)::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 4px;
}

.table-scroll-container :deep(.ant-table-body)::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}

.table-scroll-container :deep(.ant-table-body)::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

.table-header-ultra {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-bottom: 16px;
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
    gap: 12px;
    flex-wrap: wrap;
}

.table-header-right-ultra {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.btn-group-actions {
    display: flex;
    gap: 10px;
    align-items: center;
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

.total-pendiente-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 4px 16px;
    background: linear-gradient(135deg, #dcfce7, #bbf7d0);
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    color: #166534;
}

.total-pendiente-value {
    font-weight: 700;
    font-size: 14px;
    color: #166534;
}

/* --- ESTILOS DE LA TABLA --- */
.movimiento-table-ultra {
    width: 100%;
}

.movimiento-table-ultra :deep(.ant-table-thead > tr > th) {
    background: linear-gradient(135deg, #1a3a5c 0%, #2c5282 100%) !important;
    font-weight: 700;
    color: #ffffff !important;
    border-bottom: none !important;
    padding: 12px 14px !important;
    font-size: 11px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    position: sticky;
    top: 0;
    z-index: 10;
    box-shadow: 0 2px 8px rgba(26, 58, 92, 0.15);
}

.movimiento-table-ultra :deep(.ant-table-tbody > tr) {
    transition: all 0.2s ease;
}

.movimiento-table-ultra :deep(.ant-table-tbody > tr.row-vencida) {
    background: linear-gradient(90deg, #fef2f2, #fecaca) !important;
    border-left: 4px solid #dc2626 !important;
}

.movimiento-table-ultra :deep(.ant-table-tbody > tr.row-vencida:hover) {
    background: linear-gradient(90deg, #fecaca, #fca5a5) !important;
    box-shadow: inset 0 0 0 1px #dc2626 !important;
}

.movimiento-table-ultra :deep(.ant-table-tbody > tr.row-vencida td) {
    color: #991b1b !important;
    font-weight: 600 !important;
}

.movimiento-table-ultra :deep(.ant-table-tbody > tr:not(.row-vencida):hover) {
    background: linear-gradient(90deg, #f8faff, #f0f7ff) !important;
    box-shadow: inset 0 0 0 1px #667eea;
}

.movimiento-table-ultra :deep(.ant-table-tbody > tr:nth-child(even)) {
    background: #fafbfc;
}

.movimiento-table-ultra :deep(.ant-table-tbody > tr:nth-child(even):not(.row-vencida):hover) {
    background: linear-gradient(90deg, #f8faff, #f0f7ff) !important;
}

.movimiento-table-ultra :deep(.ant-table-cell) {
    padding: 10px 12px !important;
    border-bottom: 1px solid #f1f5f9;
    font-size: 13px;
}

.movimiento-table-ultra :deep(.ant-table-cell-fix-left),
.movimiento-table-ultra :deep(.ant-table-cell-fix-right) {
    background: #ffffff;
    z-index: 5;
    box-shadow: 0 0 8px rgba(0, 0, 0, 0.04);
}

.movimiento-table-ultra :deep(.ant-table-tbody > tr.row-vencida .ant-table-cell-fix-left),
.movimiento-table-ultra :deep(.ant-table-tbody > tr.row-vencida .ant-table-cell-fix-right) {
    background: linear-gradient(90deg, #fef2f2, #fecaca) !important;
}

.movimiento-table-ultra :deep(.ant-table-tbody > tr.row-vencida:hover .ant-table-cell-fix-left),
.movimiento-table-ultra :deep(.ant-table-tbody > tr.row-vencida:hover .ant-table-cell-fix-right) {
    background: linear-gradient(90deg, #fecaca, #fca5a5) !important;
}

/* --- CELDAS --- */
.referencia-link {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    color: #0f172a;
    text-decoration: none;
    transition: all 0.3s ease;
    cursor: pointer;
    font-weight: 600;
}

.referencia-link:hover {
    color: #667eea;
    text-decoration: underline;
}

.referencia-text-ultra {
    font-size: 13px;
    color: inherit;
}

.fiscal-icon {
    display: inline-flex;
    margin-left: 4px;
}

.traspaso-icon {
    display: inline-flex;
    margin-left: 4px;
}

.fecha-usuario-cell {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
}

.fecha-text-ultra {
    font-size: 13px;
    font-weight: 500;
    color: #475569;
}

.fecha-text-ultra.vencido {
    color: #dc2626;
    font-weight: 700;
}

.fecha-text-ultra.vigente {
    color: #10b981;
    font-weight: 600;
}

.usuario-fecha-creacion {
    font-size: 9px;
    color: #94a3b8;
    font-weight: 400;
    background: #f1f5f9;
    padding: 1px 8px;
    border-radius: 4px;
    white-space: nowrap;
}

.vencido-icon {
    font-size: 14px;
    margin-left: 4px;
}

.estatus-badge {
    display: inline-block;
    padding: 3px 12px;
    border-radius: 4px;
    font-size: 11px;
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

.persona-text-ultra {
    font-size: 13px;
    color: #0f172a;
    font-weight: 500;
}

.cuenta-text-ultra {
    font-size: 13px;
    color: #475569;
}

.nota-text-ultra {
    font-size: 13px;
    color: #64748b;
    font-style: italic;
}

.monto-text-ultra {
    font-size: 14px;
    font-weight: 700;
    font-family: 'Courier New', monospace;
}

.monto-text-ultra.ingreso {
    color: #2563eb;
}

.monto-text-ultra.egreso {
    color: #dc2626;
}

.monto-text-ultra.abonado-text {
    color: #2563eb;
}

.monto-text-ultra.saldo-positivo {
    color: #dc2626;
    font-weight: 700;
}

.monto-text-ultra.saldo-negativo {
    color: #2563eb;
}

.monto-text-ultra.saldo-cero {
    color: #10b981;
}

.usuario-cell {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.usuario-text-ultra {
    font-size: 13px;
    font-weight: 600;
    color: #0f172a;
}

.usuario-fecha-ultra {
    font-size: 10px;
    color: #94a3b8;
}

/* --- RECURSO --- */
.recurso-cell {
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-recurso {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 12px;
    border: none;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    font-family: inherit;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.btn-recurso-verde {
    background: #dcfce7;
    color: #166534;
    border: 2px solid #86efac;
}

.btn-recurso-verde:hover {
    background: #bbf7d0;
    transform: scale(1.05);
    box-shadow: 0 2px 8px rgba(22, 101, 52, 0.15);
}

.btn-recurso-azul {
    background: #dbeafe;
    color: #1e40af;
    border: 2px solid #93c5fd;
}

.btn-recurso-azul:hover {
    background: #bfdbfe;
    transform: scale(1.05);
    box-shadow: 0 2px 8px rgba(30, 64, 175, 0.15);
}

.btn-recurso-icon {
    width: 14px;
    height: 14px;
}

.btn-recurso-texto {
    font-size: 10px;
}

/* --- PDF --- */
.pdf-cell {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
}

.btn-pdf {
    font-size: 16px !important;
    padding: 0 4px !important;
    height: 28px !important;
    width: 28px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    transition: all 0.3s ease !important;
}

.btn-pdf:hover:not(:disabled) {
    transform: scale(1.15);
    background: rgba(16, 185, 129, 0.05) !important;
}

.btn-pdf:disabled {
    cursor: not-allowed;
    transform: none !important;
}

/* --- ACCIONES --- */
.acciones-cell {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    flex-wrap: nowrap;
}

.btn-accion {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    padding: 3px 10px;
    border: none;
    border-radius: 5px;
    font-size: 9px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s ease;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    white-space: nowrap;
    min-width: 38px;
    text-align: center;
    justify-content: center;
    height: 24px;
}

.btn-accion.abonar {
    background: #dbeafe;
    color: #1e40af;
}

.btn-accion.abonar:hover:not(:disabled) {
    background: #bfdbfe;
    transform: scale(1.05);
    box-shadow: 0 2px 8px rgba(30, 64, 175, 0.15);
}

.btn-accion.editar {
    background: #fef3c7;
    color: #92400e;
}

.btn-accion.editar:hover {
    background: #fde68a;
    transform: scale(1.05);
    box-shadow: 0 2px 8px rgba(146, 64, 14, 0.15);
}

.btn-accion.ver {
    background: #e0e7ff;
    color: #3730a3;
}

.btn-accion.ver:hover {
    background: #c7d2fe;
    transform: scale(1.05);
    box-shadow: 0 2px 8px rgba(55, 48, 163, 0.15);
}

.btn-accion:disabled {
    opacity: 0.4;
    cursor: not-allowed;
    transform: none !important;
}

/* --- FILTROS INFERIOR --- */
.filtros-inferior-tabla {
    margin-top: 16px;
    padding: 16px 20px;
    background: #f8fafc;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
}

.filtros-inferior-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 12px;
    align-items: end;
}

.filtro-inferior-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.filtro-inferior-label {
    font-size: 10px;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.filtro-inferior-input {
    padding: 6px 10px;
    border: 2px solid #d1d5db;
    border-radius: 6px;
    font-size: 12px;
    height: 32px;
    background: #ffffff;
    color: #0f172a;
    transition: all 0.3s ease;
    outline: none;
    width: 100%;
}

.filtro-inferior-input:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.filtro-inferior-input::placeholder {
    color: #94a3b8;
}

.filtro-inferior-select {
    padding: 6px 10px;
    border: 2px solid #d1d5db;
    border-radius: 6px;
    font-size: 12px;
    height: 32px;
    background: #ffffff;
    color: #0f172a;
    transition: all 0.3s ease;
    outline: none;
    width: 100%;
    cursor: pointer;
}

.filtro-inferior-select:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.filtro-inferior-actions {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    min-width: 150px;
}

.btn-limpiar-filtros-inferior {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 16px;
    border: 2px solid #d1d5db;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    color: #64748b;
    background: #ffffff;
    cursor: pointer;
    transition: all 0.3s ease;
    height: 32px;
    white-space: nowrap;
}

.btn-limpiar-filtros-inferior:hover {
    color: #dc2626;
    border-color: #dc2626;
    background: #fef2f2;
    transform: translateY(-1px);
}

/* --- RESUMEN DE TOTALES --- */
.resumen-totales-wrapper {
    margin-top: 20px;
    display: flex;
    justify-content: flex-end;
}

.resumen-totales-container-grande {
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    border-radius: 16px;
    border: 2px solid #e2e8f0;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
    padding: 20px 28px;
    display: flex;
    align-items: center;
    gap: 28px;
    flex-wrap: wrap;
    min-width: 400px;
    width: 100%;
    justify-content: center;
    transition: all 0.3s ease;
}

.resumen-totales-container-grande:hover {
    box-shadow: 0 6px 24px rgba(0, 0, 0, 0.08);
    border-color: #cbd5e1;
}

.resumen-totales-header-grande {
    display: flex;
    align-items: center;
    gap: 10px;
    padding-right: 20px;
    border-right: 2px solid #e2e8f0;
}

.resumen-totales-title-grande {
    font-size: 16px;
    font-weight: 700;
    color: #0f172a;
    letter-spacing: 0.3px;
    white-space: nowrap;
}

.resumen-totales-items-grande {
    display: flex;
    align-items: center;
    gap: 20px;
    flex: 1;
    flex-wrap: wrap;
}

.total-item-grande {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 8px 16px;
    transition: all 0.3s ease;
    border-radius: 10px;
    background: #ffffff;
    border: 1px solid #f1f5f9;
    min-width: 140px;
    flex: 1;
}

.total-item-grande:hover {
    background: #f8fafc;
    border-color: #e2e8f0;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
}

.total-icon-grande {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.total-ingresos .total-icon-grande {
    background: #eff6ff;
}

.total-egresos .total-icon-grande {
    background: #fef2f2;
}

.total-traspasos .total-icon-grande {
    background: #ede9fe;
}

.total-saldo-neto .total-icon-grande {
    background: #f0fdf4;
}

.total-icon-svg-grande {
    width: 20px;
    height: 20px;
}

.total-ingresos .total-icon-svg-grande {
    color: #2563eb;
}

.total-egresos .total-icon-svg-grande {
    color: #dc2626;
}

.total-traspasos .total-icon-svg-grande {
    color: #7c3aed;
}

.total-saldo-neto .total-icon-svg-grande {
    color: #10b981;
}

.total-info-grande {
    display: flex;
    flex-direction: column;
    gap: 2px;
    min-width: 0;
}

.total-label-grande {
    font-size: 11px;
    font-weight: 600;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.total-value-grande {
    font-size: 18px;
    font-weight: 700;
    font-family: 'Courier New', monospace;
    line-height: 1.2;
}

.ingresos-value {
    color: #2563eb;
}

.egresos-value {
    color: #dc2626;
}

.traspaso-value {
    color: #7c3aed;
}

.saldo-net-value {
    color: #10b981;
}

/* --- PAGINACION --- */
.pagination-ultra {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-top: 16px;
    padding-top: 16px;
    border-top: 2px solid #f1f5f9;
}

@media (min-width: 640px) {
    .pagination-ultra {
        flex-direction: row;
    }
}

.pagination-info-ultra {
    font-size: 13px;
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

/* --- MODAL RECURSO --- */
.modal-recurso-premium :deep(.ant-modal-header) {
    background: linear-gradient(135deg, #1a3a5c, #2c5282);
    border-radius: 8px 8px 0 0;
    padding: 16px 24px;
}

.modal-recurso-premium :deep(.ant-modal-title) {
    color: white;
    font-weight: 700;
    font-size: 1.1rem;
}

.modal-recurso-premium :deep(.ant-modal-close) {
    color: white;
}

.modal-recurso-premium :deep(.ant-modal-close:hover) {
    color: #fca5a5;
}

.modal-recurso-premium :deep(.ant-modal-body) {
    padding: 0;
}

.modal-recurso-content {
    min-height: 300px;
    padding: 24px;
}

.recurso-pdf-wrapper {
    width: 100%;
    height: 70vh;
    min-height: 400px;
}

.recurso-pdf {
    width: 100%;
    height: 100%;
    border: none;
}

.recurso-image-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 300px;
    background: #f8fafc;
    border-radius: 8px;
}

.recurso-image {
    max-width: 100%;
    max-height: 70vh;
    object-fit: contain;
    border-radius: 4px;
}

.recurso-other {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 300px;
    gap: 12px;
}

.recurso-other-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 80px;
    height: 80px;
    background: #f3f4f6;
    border-radius: 50%;
}

.recurso-other-svg {
    width: 40px;
    height: 40px;
    color: #94a3b8;
}

.recurso-other-text {
    color: #64748b;
    font-size: 0.95rem;
}

.recurso-other-link {
    color: #2563eb;
    font-weight: 600;
    text-decoration: none;
    padding: 6px 16px;
    border: 1px solid #2563eb;
    border-radius: 6px;
    transition: all 0.3s ease;
}

.recurso-other-link:hover {
    background: #eff6ff;
}

.recurso-footer {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding: 16px 24px;
    background: white;
    border-top: 1px solid #f3f4f6;
}

.recurso-upload-wrapper {
    padding: 8px 0;
}

.recurso-upload-info {
    margin-bottom: 16px;
    font-size: 0.9rem;
    color: #475569;
}

.recurso-upload-info strong {
    color: #0f172a;
}

.recurso-upload-hint {
    font-size: 0.8rem;
    color: #94a3b8;
    margin-top: 4px;
}

.recurso-drop-zone {
    border: 2px dashed #d1d5db;
    border-radius: 12px;
    padding: 32px 24px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    background: #fafbfc;
}

.recurso-drop-zone:hover {
    border-color: #8b5cf6;
    background: #f8f7ff;
}

.recurso-drop-zone-dragover {
    border-color: #8b5cf6;
    background: #ede9fe;
    transform: scale(1.02);
}

.recurso-drop-zone-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}

.recurso-drop-zone-icon {
    width: 48px;
    height: 48px;
    color: #94a3b8;
}

.recurso-drop-zone-text {
    font-size: 0.9rem;
    color: #64748b;
}

.recurso-drop-zone-text.archivo-seleccionado {
    color: #10b981;
    font-weight: 600;
}

.archivo-seleccionado-icon {
    width: 20px;
    height: 20px;
    display: inline-block;
    vertical-align: middle;
    margin-right: 6px;
}

.recurso-drop-zone-hint {
    font-size: 0.7rem;
    color: #94a3b8;
}

.recurso-file-input-hidden {
    display: none;
}

.recurso-error {
    margin-top: 12px;
    padding: 8px 14px;
    background: #fee2e2;
    border-radius: 6px;
    color: #991b1b;
    font-size: 0.85rem;
    display: flex;
    align-items: center;
    gap: 8px;
}

.recurso-modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 20px;
    padding-top: 16px;
    border-top: 1px solid #f3f4f6;
}

.btn-modal-cancel {
    padding: 8px 24px;
    background: #f1f5f9;
    color: #64748b;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.85rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-modal-cancel:hover {
    background: #e2e8f0;
}

.btn-modal-submit {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 24px;
    background: linear-gradient(135deg, #1a3a5c, #2c5282);
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.85rem;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
}

.btn-modal-submit:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(26, 58, 92, 0.3);
}

.btn-modal-submit:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.spinner-border-sm {
    display: inline-block;
    width: 1rem;
    height: 1rem;
    border: 0.15em solid currentColor;
    border-right-color: transparent;
    border-radius: 50%;
    animation: spinner 0.75s linear infinite;
}

.error-icon-sm {
    width: 18px;
    height: 18px;
    flex-shrink: 0;
}

@keyframes pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(1.2); }
}

@keyframes spinner {
    to { transform: rotate(360deg); }
}

/* --- RESPONSIVE --- */
@media (max-width: 768px) {
    .filtros-superior-content {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filtros-separator {
        display: none;
    }
    
    .fecha-actions-export {
        margin-left: 0;
        width: 100%;
        flex-wrap: wrap;
    }

    .btn-export-excel-mini,
    .btn-export-pdf-mini {
        flex: 1;
        justify-content: center;
    }

    .checkbox-fiscal-item {
        align-items: flex-start;
    }

    .filtros-inferior-grid {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    }

    .table-wrapper-premium {
        padding: 12px;
    }
    
    .btn-nueva-poliza,
    .btn-nomina-poliza {
        width: 100%;
        justify-content: center;
    }

    .btn-group-actions {
        width: 100%;
        flex-direction: column;
    }
    
    .btn-group-actions .column-selector-wrapper {
        width: 100%;
    }
    
    .btn-group-actions .column-selector-wrapper .btn-column-selector {
        width: 100%;
        justify-content: center;
    }

    .table-header-right-ultra {
        width: 100%;
        flex-direction: column;
    }

    .view-toggle-group {
        width: 100%;
    }

    .view-toggle-btn {
        flex: 1;
        justify-content: center;
    }

    .acciones-cell {
        flex-wrap: wrap;
        gap: 3px;
    }

    .btn-accion {
        font-size: 8px;
        padding: 2px 6px;
        min-width: 30px;
        height: 20px;
    }

    .btn-icon-sm {
        width: 10px;
        height: 10px;
    }
    
    .recurso-pdf-wrapper {
        height: 50vh;
        min-height: 300px;
    }
}

@media (max-width: 480px) {
    .empresa-selector-actions {
        flex-direction: column;
        width: 100%;
    }

    .view-toggle-group {
        width: 100%;
    }

    .fecha-actions-export {
        flex-direction: column;
    }

    .btn-export-excel-mini,
    .btn-export-pdf-mini {
        width: 100%;
        justify-content: center;
    }

    .filtros-inferior-grid {
        grid-template-columns: 1fr;
    }

    .filtro-inferior-actions {
        justify-content: center;
    }

    .resumen-totales-container-grande {
        min-width: 100%;
        width: 100%;
        padding: 12px 16px;
        flex-direction: column;
        gap: 12px;
    }
    
    .resumen-totales-header-grande {
        padding-right: 0;
        border-right: none;
        border-bottom: 2px solid #e2e8f0;
        padding-bottom: 8px;
        width: 100%;
        justify-content: center;
    }

    .total-item-grande {
        min-width: 100%;
        flex: 1 1 100%;
    }

    .acciones-cell {
        gap: 2px;
    }

    .btn-accion {
        font-size: 7px;
        padding: 2px 4px;
        min-width: 24px;
        height: 18px;
    }

    .btn-icon-sm {
        width: 8px;
        height: 8px;
    }
    
    .checkbox-fiscal-text {
        font-size: 12px;
    }
    
    .recurso-pdf-wrapper {
        height: 40vh;
        min-height: 250px;
    }
    
    .recurso-image {
        max-height: 50vh;
    }
}
</style>