<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Póliza {{ $movimiento->poliza->folio ?? 'Sin folio' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #1a1a2e;
            padding: 20px;
            background: #ffffff;
        }
        .page {
            max-width: 100%;
            margin: 0 auto;
        }
        .header {
            border-bottom: 3px solid #1a3a5c;
            padding-bottom: 12px;
            margin-bottom: 16px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        .header-title {
            font-size: 18px;
            font-weight: 700;
            color: #1a3a5c;
            letter-spacing: 0.5px;
        }
        .header-subtitle {
            font-size: 12px;
            color: #64748b;
            font-weight: 400;
        }
        .header-right {
            text-align: right;
        }
        .folio-badge {
            font-size: 14px;
            font-weight: 700;
            color: #1a3a5c;
            background: #eef2ff;
            padding: 4px 14px;
            border-radius: 4px;
            display: inline-block;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 16px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .status-badge.capturado { background: #f3f4f6; color: #374151; }
        .status-badge.revisado { background: #dbeafe; color: #1e40af; }
        .status-badge.autorizado { background: #d1fae5; color: #065f46; }
        .status-badge.abonado { background: #fef3c7; color: #92400e; }
        .status-badge.liquidado { background: #e0e7ff; color: #3730a3; }
        .status-badge.pendiente { background: #fef3c7; color: #92400e; }

        /* Secciones */
        .section {
            margin-bottom: 16px;
            page-break-inside: avoid;
        }
        .section-title {
            font-size: 13px;
            font-weight: 700;
            color: #1a3a5c;
            padding-bottom: 6px;
            border-bottom: 2px solid #e2e8f0;
            margin-bottom: 10px;
        }
        .section-title .badge {
            font-size: 9px;
            font-weight: 600;
            padding: 2px 10px;
            border-radius: 4px;
            margin-left: 8px;
        }
        .section-title .badge.fiscal { background: #d1fae5; color: #065f46; }
        .section-title .badge.no-fiscal { background: #f3f4f6; color: #374151; }
        .section-title .badge.traspaso { background: #ede9fe; color: #5b21b6; }

        /* Grids */
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px 20px;
        }
        .grid-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 8px 20px;
        }
        .grid-4 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr;
            gap: 8px 20px;
        }

        /* Items */
        .item {
            padding: 6px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .item .label {
            font-size: 9px;
            font-weight: 600;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            display: block;
        }
        .item .value {
            font-size: 12px;
            font-weight: 500;
            color: #0f172a;
            margin-top: 1px;
            word-break: break-word;
        }
        .item .value.ingreso { color: #2563eb; }
        .item .value.egreso { color: #dc2626; }
        .item .value.saldo-positivo { color: #dc2626; font-weight: 700; }
        .item .value.saldo-negativo { color: #2563eb; }
        .item .value.saldo-cero { color: #10b981; }
        .item .value.vencido { color: #dc2626; font-weight: 700; }
        .item .value.vigente { color: #10b981; }

        /* Tabla de abonos */
        .table-wrapper {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            overflow: hidden;
            margin-top: 6px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        table thead {
            background: #f8fafc;
        }
        table th {
            padding: 8px 12px;
            text-align: left;
            font-weight: 700;
            color: #1e293b;
            text-transform: uppercase;
            font-size: 8px;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e2e8f0;
        }
        table td {
            padding: 7px 12px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
        }
        table tbody tr:last-child td {
            border-bottom: none;
        }
        table tfoot {
            background: #f8fafc;
            font-weight: 700;
        }
        table tfoot td {
            padding: 8px 12px;
            border-top: 2px solid #e2e8f0;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .monto-abonado {
            color: #2563eb;
            font-weight: 600;
        }
        .total-value {
            font-size: 12px;
            font-weight: 700;
        }
        .total-value.saldo-positivo { color: #dc2626; }
        .total-value.saldo-negativo { color: #2563eb; }
        .total-value.saldo-cero { color: #10b981; }

        /* Footer */
        .footer {
            margin-top: 20px;
            padding-top: 12px;
            border-top: 2px solid #e2e8f0;
            font-size: 9px;
            color: #94a3b8;
            display: flex;
            justify-content: space-between;
        }
        .footer .left { text-align: left; }
        .footer .right { text-align: right; }

        /* Montos card */
        .monto-card {
            padding: 10px 14px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            background: #fafbfc;
            text-align: center;
        }
        .monto-card.total {
            background: #eff6ff;
            border-color: #93c5fd;
        }
        .monto-card.traspaso-card {
            background: #ede9fe;
            border-color: #c4b5fd;
        }
        .monto-card .label {
            font-size: 8px;
            font-weight: 600;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .monto-card .value {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            margin-top: 2px;
        }
        .monto-card .value.ingreso { color: #2563eb; }
        .monto-card .value.egreso { color: #dc2626; }
        .monto-card .value.traspaso { color: #7c3aed; }

        .full-width {
            grid-column: 1 / -1;
        }

        .nota-content {
            padding: 10px 14px;
            background: #f8fafc;
            border-left: 3px solid #14b8a6;
            border-radius: 4px;
            color: #1f2937;
            line-height: 1.6;
            font-size: 11px;
        }

        .separator {
            border: none;
            border-top: 1px dashed #e2e8f0;
            margin: 6px 0;
        }

        .uuid-value {
            font-family: 'Courier New', monospace;
            font-size: 9px;
            color: #475569;
            background: #f1f5f9;
            padding: 2px 8px;
            border-radius: 3px;
            display: inline-block;
        }

        .comentario-text {
            font-style: italic;
            color: #475569;
            background: #f1f5f9;
            padding: 6px 10px;
            border-radius: 4px;
            border-left: 3px solid #3b82f6;
        }

        .comentario-text.rechazo {
            border-left-color: #dc2626;
            background: #fef2f2;
            color: #991b1b;
        }

        .metodo-pago {
            display: inline-block;
            padding: 1px 8px;
            background: #f1f5f9;
            border-radius: 3px;
            font-size: 8px;
            font-weight: 600;
            color: #475569;
        }

        .traspaso-icon {
            color: #7c3aed;
            font-weight: 700;
        }

        /* Badge de traspaso en el header */
        .tipo-badge {
            display: inline-block;
            padding: 2px 12px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-left: 6px;
        }
        .tipo-badge.ingreso { background: #dbeafe; color: #1e40af; }
        .tipo-badge.egreso { background: #fecaca; color: #991b1b; }
        .tipo-badge.traspaso { background: #ede9fe; color: #5b21b6; }

        /* Info de cuentas en traspaso */
        .cuentas-info {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            gap: 16px;
            align-items: center;
            padding: 12px 16px;
            background: #f8fafc;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
        }
        .cuentas-info .cuenta-item {
            text-align: center;
        }
        .cuentas-info .cuenta-item .label {
            font-size: 8px;
            font-weight: 600;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            display: block;
        }
        .cuentas-info .cuenta-item .value {
            font-size: 12px;
            font-weight: 600;
            color: #0f172a;
            margin-top: 2px;
        }
        .cuentas-info .cuenta-item .value.origen {
            color: #dc2626;
        }
        .cuentas-info .cuenta-item .value.destino {
            color: #2563eb;
        }
        .cuentas-info .flecha {
            font-size: 20px;
            color: #7c3aed;
            font-weight: 700;
        }

        @media print {
            body { padding: 10px; }
            .section { page-break-inside: avoid; }
            .grid-2, .grid-3, .grid-4 { break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="page">
        <!-- HEADER -->
        <div class="header">
            <div>
                <div class="header-title">
                    Póliza de Movimiento
                    @if($esTraspaso)
                        <span class="tipo-badge traspaso">Traspaso</span>
                    @else
                        <span class="tipo-badge {{ $movimiento->poliza->tipo_poliza == 'INGRESO' ? 'ingreso' : 'egreso' }}">
                            {{ $movimiento->poliza->tipo_poliza ?? '—' }}
                        </span>
                    @endif
                </div>
                <div class="header-subtitle">
                    {{ $empresa->nombre_empresa ?? 'Sin empresa' }}
                    <span style="margin: 0 6px;">|</span>
                    {{ $movimiento->poliza->categoria ?? '—' }}
                </div>
            </div>
            <div class="header-right">
                <div class="folio-badge">{{ $movimiento->poliza->folio ?? 'Sin folio' }}</div>
                <div style="margin-top: 6px;">
                    <span class="status-badge {{ $movimiento->poliza->estatus ? strtolower($movimiento->poliza->estatus) : 'pendiente' }}">
                        {{ $movimiento->poliza->estatus_texto ?? $movimiento->poliza->estatus ?? 'Pendiente' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- RESUMEN -->
        <div class="section">
            <div class="section-title">Resumen</div>
            <div class="grid-4">
                <div class="item">
                    <span class="label">Tipo</span>
                    <span class="value">{{ $movimiento->poliza->tipo_poliza ?? '—' }}</span>
                </div>
                <div class="item">
                    <span class="label">Fecha</span>
                    <span class="value">{{ $movimiento->poliza->fecha_poliza ? date('d/m/Y', strtotime($movimiento->poliza->fecha_poliza)) : '—' }}</span>
                </div>
                <div class="item">
                    <span class="label">Categoría</span>
                    <span class="value">
                        {{ $movimiento->poliza->categoria ?? '—' }}
                        <span class="badge {{ $movimiento->poliza->categoria == 'FISCAL' ? 'fiscal' : 'no-fiscal' }}">
                            {{ $movimiento->poliza->categoria == 'FISCAL' ? 'Fiscal' : 'No Fiscal' }}
                        </span>
                    </span>
                </div>
                <div class="item">
                    <span class="label">Monto</span>
                    <span class="value {{ $esTraspaso ? 'traspaso' : ($movimiento->monto > 0 ? 'ingreso' : ($movimiento->monto < 0 ? 'egreso' : '')) }}">
                        ${{ number_format($montoMostrar, 2) }}
                        @if($esTraspaso)
                            <span style="font-size:9px;color:#7c3aed;font-weight:600;"> (Traspaso)</span>
                        @endif
                    </span>
                </div>
            </div>
        </div>

        <!-- INFORMACIÓN GENERAL -->
        <div class="section">
            <div class="section-title">Información General</div>
            <div class="grid-2">
                <!-- Para INGRESO/EGRESO: mostrar persona -->
                @if(!$esTraspaso)
                <div class="item">
                    <span class="label">Persona</span>
                    <span class="value">{{ $movimiento->poliza->persona->nombre_completo ?? '—' }}</span>
                </div>
                <div class="item">
                    <span class="label">Cuenta</span>
                    <span class="value">{{ $movimiento->cuenta->nombre_cuenta ?? '—' }}</span>
                </div>
                <div class="item">
                    <span class="label">Cuenta Fondeadora</span>
                    <span class="value">{{ $movimiento->cuentaFondeadora->nombre_cuenta ?? '—' }}</span>
                </div>
                @endif

                <!-- Para TRASPASO: mostrar cuentas origen y destino -->
                @if($esTraspaso)
                <div class="item full-width">
                    <span class="label">Cuentas del Traspaso</span>
                    <div class="cuentas-info">
                        <div class="cuenta-item">
                            <span class="label">Cuenta Origen</span>
                            <span class="value origen">{{ $movimiento->cuentaFondeadora->nombre_cuenta ?? '—' }}</span>
                            <span style="font-size:8px;color:#94a3b8;display:block;">Sale</span>
                        </div>
                        <div class="flecha">→</div>
                        <div class="cuenta-item">
                            <span class="label">Cuenta Destino</span>
                            <span class="value destino">{{ $movimiento->cuenta->nombre_cuenta ?? '—' }}</span>
                            <span style="font-size:8px;color:#94a3b8;display:block;">Recibe</span>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Campos comunes -->
                <div class="item">
                    <span class="label">Marcador</span>
                    <span class="value">{{ $movimiento->poliza->marcador->nombre_marcador ?? '—' }}</span>
                </div>
                @if($movimiento->poliza->referencia)
                <div class="item">
                    <span class="label">Referencia</span>
                    <span class="value">{{ $movimiento->poliza->referencia }}</span>
                </div>
                @endif
                <div class="item">
                    <span class="label">Por Pagar</span>
                    <span class="value">{{ $movimiento->poliza->es_por_pagar ? 'Sí' : 'No' }}</span>
                </div>
                @if($movimiento->poliza->es_por_pagar)
                <div class="item">
                    <span class="label">Fecha Vencimiento</span>
                    <span class="value {{ $movimiento->poliza->fecha_vencimiento && strtotime($movimiento->poliza->fecha_vencimiento) < time() ? 'vencido' : 'vigente' }}">
                        {{ $movimiento->poliza->fecha_vencimiento ? date('d/m/Y', strtotime($movimiento->poliza->fecha_vencimiento)) : '—' }}
                        @if($movimiento->poliza->fecha_vencimiento && strtotime($movimiento->poliza->fecha_vencimiento) < time())
                            <span style="color:#dc2626;font-weight:700;font-size:9px;margin-left:6px;">VENCIDO</span>
                        @endif
                    </span>
                </div>
                @endif
            </div>
        </div>

        <!-- MONTOS Y DESGLOSE -->
        <div class="section">
            <div class="section-title">Montos y Desglose</div>
            <div class="grid-3">
                <div class="monto-card {{ $esTraspaso ? 'traspaso-card' : 'total' }}">
                    <div class="label">{{ $esTraspaso ? 'Monto Traspaso' : 'Monto Total' }}</div>
                    <div class="value {{ $esTraspaso ? 'traspaso' : ($movimiento->monto > 0 ? 'ingreso' : ($movimiento->monto < 0 ? 'egreso' : '')) }}">
                        ${{ number_format($montoMostrar, 2) }}
                    </div>
                </div>
                <div class="monto-card">
                    <div class="label">Base Gravable</div>
                    <div class="value">${{ number_format($movimiento->monto_base ?? 0, 2) }}</div>
                </div>
                <div class="monto-card">
                    <div class="label">IVA</div>
                    <div class="value">${{ number_format($movimiento->monto_iva ?? 0, 2) }}</div>
                </div>
            </div>
            @if($movimiento->poliza->es_por_pagar)
            <div style="margin-top:8px;display:flex;gap:20px;padding:8px 14px;background:#f8fafc;border-radius:6px;">
                <div>
                    <span style="font-size:9px;color:#94a3b8;font-weight:600;text-transform:uppercase;">Total Abonado</span>
                    <span style="font-size:13px;font-weight:700;color:#2563eb;display:block;">${{ number_format($totalAbonado, 2) }}</span>
                </div>
                <div>
                    <span style="font-size:9px;color:#94a3b8;font-weight:600;text-transform:uppercase;">Saldo Pendiente</span>
                    <span style="font-size:13px;font-weight:700;color:{{ $saldoPendiente > 0 ? '#dc2626' : ($saldoPendiente < 0 ? '#2563eb' : '#10b981') }};display:block;">
                        ${{ number_format($saldoPendiente, 2) }}
                    </span>
                </div>
            </div>
            @endif
        </div>

        <!-- FACTURACIÓN (SI ES FISCAL) -->
        @if($movimiento->poliza->categoria === 'FISCAL')
        <div class="section">
            <div class="section-title">Facturación</div>
            <div class="grid-2">
                <div class="item">
                    <span class="label">Fecha Factura</span>
                    <span class="value">{{ $movimiento->poliza->fecha_factura ? date('d/m/Y', strtotime($movimiento->poliza->fecha_factura)) : '—' }}</span>
                </div>
                <div class="item">
                    <span class="label">Número Factura</span>
                    <span class="value">{{ $movimiento->poliza->numero_factura ?? '—' }}</span>
                </div>
                @if($movimiento->poliza->serie_factura)
                <div class="item">
                    <span class="label">Serie</span>
                    <span class="value">{{ $movimiento->poliza->serie_factura }}</span>
                </div>
                @endif
                @if($movimiento->poliza->folio_factura)
                <div class="item">
                    <span class="label">Folio Factura</span>
                    <span class="value">{{ $movimiento->poliza->folio_factura }}</span>
                </div>
                @endif
                @if($movimiento->poliza->uuid_factura)
                <div class="item full-width">
                    <span class="label">UUID</span>
                    <span class="value"><span class="uuid-value">{{ $movimiento->poliza->uuid_factura }}</span></span>
                </div>
                @endif
                <div class="item full-width" style="display:flex;gap:16px;padding:6px 0;">
                    <div>
                        <span style="font-size:9px;color:#94a3b8;font-weight:600;text-transform:uppercase;display:block;">PDF</span>
                        <span style="font-size:12px;font-weight:600;color:{{ $movimiento->poliza->ruta_pdf ? '#10b981' : '#94a3b8' }};">
                            {{ $movimiento->poliza->ruta_pdf ? 'Adjunto' : 'No disponible' }}
                        </span>
                    </div>
                    <div>
                        <span style="font-size:9px;color:#94a3b8;font-weight:600;text-transform:uppercase;display:block;">XML</span>
                        <span style="font-size:12px;font-weight:600;color:{{ $movimiento->poliza->ruta_xml ? '#10b981' : '#94a3b8' }};">
                            {{ $movimiento->poliza->ruta_xml ? 'Adjunto' : 'No disponible' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- ABONOS (SI ES POR PAGAR Y TIENE ABONOS) -->
        @if($movimiento->poliza->es_por_pagar && $abonos->count() > 0)
        <div class="section">
            <div class="section-title">
                Historial de Abonos
                <span style="font-size:10px;font-weight:600;color:#1e40af;background:#dbeafe;padding:2px 12px;border-radius:4px;margin-left:8px;">
                    Total: ${{ number_format($totalAbonado, 2) }}
                </span>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Monto</th>
                            <th>Referencia</th>
                            <th>Método Pago</th>
                            <th>Nota</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($abonos as $abono)
                        <tr>
                            <td>{{ $abono->fecha_abono ? date('d/m/Y', strtotime($abono->fecha_abono)) : '—' }}</td>
                            <td class="monto-abonado">${{ number_format($abono->monto_abonado, 2) }}</td>
                            <td>{{ $abono->referencia ?? '—' }}</td>
                            <td><span class="metodo-pago">{{ $abono->metodo_pago ?? '—' }}</span></td>
                            <td>{{ $abono->nota ?? '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-right"><strong>Saldo Pendiente</strong></td>
                            <td colspan="2" class="total-value {{ $saldoPendiente > 0 ? 'saldo-positivo' : ($saldoPendiente < 0 ? 'saldo-negativo' : 'saldo-cero') }}">
                                ${{ number_format($saldoPendiente, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        @endif

        <!-- USUARIOS Y REVISIONES -->
        <div class="section">
            <div class="section-title">Usuarios y Revisiones</div>
            <div class="grid-2">
                <div class="item">
                    <span class="label">Creado por</span>
                    <span class="value">{{ $movimiento->poliza->usuarioCreador->nombre_completo ?? $movimiento->poliza->usuarioCreador->nombre_usuario ?? '—' }}</span>
                </div>
                <div class="item">
                    <span class="label">Fecha creación</span>
                    <span class="value">{{ $movimiento->created_at ? date('d/m/Y H:i', strtotime($movimiento->created_at)) : '—' }}</span>
                </div>
                @if($movimiento->poliza->usuarioRevisor)
                <div class="item">
                    <span class="label">Revisado por</span>
                    <span class="value">{{ $movimiento->poliza->usuarioRevisor->nombre_completo ?? $movimiento->poliza->usuarioRevisor->nombre_usuario }}</span>
                </div>
                @endif
                @if($movimiento->poliza->fecha_revision)
                <div class="item">
                    <span class="label">Fecha revisión</span>
                    <span class="value">{{ date('d/m/Y H:i', strtotime($movimiento->poliza->fecha_revision)) }}</span>
                </div>
                @endif
                @if($movimiento->poliza->usuarioAutorizador)
                <div class="item">
                    <span class="label">Autorizado por</span>
                    <span class="value">{{ $movimiento->poliza->usuarioAutorizador->nombre_completo ?? $movimiento->poliza->usuarioAutorizador->nombre_usuario }}</span>
                </div>
                @endif
                @if($movimiento->poliza->fecha_autorizacion)
                <div class="item">
                    <span class="label">Fecha autorización</span>
                    <span class="value">{{ date('d/m/Y H:i', strtotime($movimiento->poliza->fecha_autorizacion)) }}</span>
                </div>
                @endif
                @if($movimiento->poliza->comentario_revision)
                <div class="item full-width">
                    <span class="label">Comentario revisión</span>
                    <span class="value"><span class="comentario-text">{{ $movimiento->poliza->comentario_revision }}</span></span>
                </div>
                @endif
                @if($movimiento->poliza->comentario_autorizacion)
                <div class="item full-width">
                    <span class="label">Comentario autorización</span>
                    <span class="value"><span class="comentario-text">{{ $movimiento->poliza->comentario_autorizacion }}</span></span>
                </div>
                @endif
                @if($movimiento->poliza->motivo_rechazo)
                <div class="item full-width">
                    <span class="label">Motivo rechazo</span>
                    <span class="value"><span class="comentario-text rechazo">{{ $movimiento->poliza->motivo_rechazo }}</span></span>
                </div>
                @endif
            </div>
        </div>

        <!-- NOTA -->
        @if($movimiento->poliza->nota)
        <div class="section">
            <div class="section-title">Observaciones</div>
            <div class="nota-content">{{ $movimiento->poliza->nota }}</div>
        </div>
        @endif

        <!-- FOOTER -->
        <div class="footer">
            <div class="left">
                {{ $empresa->nombre_empresa ?? 'Sin empresa' }}
                <span style="margin:0 6px;">|</span>
                Documento generado el {{ $fecha_exportacion }}
            </div>
            <div class="right">
                Página 1 de 1
            </div>
        </div>
    </div>
</body>
</html>