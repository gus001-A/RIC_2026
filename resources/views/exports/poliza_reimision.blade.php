<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reimisión - {{ $movimiento->poliza->folio ?? 'Sin folio' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            color: #000;
            background: #fff;
            padding: 15px;
            max-width: 100%;
            margin: 0 auto;
            line-height: 1.4;
        }

        .documento {
            border: 2px solid #000;
            padding: 20px 25px;
            background: #fff;
            max-width: 210mm;
            margin: 0 auto;
            page-break-after: avoid;
        }

        /* HEADER */
        .header {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 12px;
            margin-bottom: 12px;
        }

        .header .empresa {
            font-size: 18px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .header .razon {
            font-size: 11px;
            color: #555;
        }

        .header .folio {
            font-size: 14px;
            font-weight: 700;
            margin-top: 6px;
            background: #000;
            color: #fff;
            padding: 3px 20px;
            display: inline-block;
            letter-spacing: 1px;
        }

        .header .tipo {
            font-size: 12px;
            font-weight: 700;
            margin-top: 4px;
        }

        .header .fecha {
            font-size: 10px;
            color: #333;
            margin-top: 2px;
        }

        .header .status {
            font-size: 9px;
            font-weight: 700;
            margin-top: 4px;
            padding: 2px 14px;
            border: 1.5px solid #000;
            display: inline-block;
            letter-spacing: 0.5px;
        }

        .tag-fiscal {
            font-size: 8px;
            font-weight: 700;
            padding: 1px 8px;
            border: 1px solid #000;
            margin-left: 4px;
        }

        .badge-por-pagar {
            font-size: 8px;
            font-weight: 700;
            padding: 1px 8px;
            border: 1px solid #000;
            margin-left: 4px;
        }

        .badge-vencido {
            font-size: 8px;
            font-weight: 700;
            padding: 1px 8px;
            border: 1px solid #000;
            margin-left: 4px;
        }

        .logo-img {
            max-width: 60px;
            max-height: 40px;
            margin-bottom: 4px;
        }

        /* SECTIONS */
        .section {
            margin-bottom: 8px;
        }

        .section-title {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 1.5px solid #000;
            padding-bottom: 2px;
            margin-bottom: 4px;
        }

        /* ROWS */
        .row {
            display: flex;
            justify-content: space-between;
            padding: 2px 0;
            border-bottom: 1px dotted #ccc;
        }

        .row:last-child {
            border-bottom: none;
        }

        .row .label {
            font-weight: 600;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .row .value {
            font-weight: 600;
            text-align: right;
            font-size: 10px;
        }

        /* MONTO DESTACADO */
        .monto-destacado {
            text-align: center;
            padding: 8px 0;
            margin: 4px 0 6px;
            border: 2px solid #000;
            background: #f4f4f4;
        }

        .monto-destacado .label {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: block;
        }

        .monto-destacado .value {
            font-size: 22px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        /* CUENTAS TRASPASO */
        .cuentas-box {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 12px;
            border: 1.5px solid #000;
            margin: 4px 0 6px;
            background: #f4f4f4;
        }

        .cuentas-box .cuenta {
            text-align: center;
            flex: 1;
        }

        .cuentas-box .cuenta .label {
            font-size: 7px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: block;
        }

        .cuentas-box .cuenta .value {
            font-size: 10px;
            font-weight: 600;
            margin-top: 2px;
        }

        .cuentas-box .flecha {
            font-size: 18px;
            font-weight: 700;
            padding: 0 10px;
        }

        /* GRIDS */
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .grid-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 8px;
        }

        .grid-item {
            padding: 4px 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .grid-item .label-sm {
            font-size: 7px;
            text-transform: uppercase;
            color: #666;
            letter-spacing: 0.3px;
        }

        .grid-item .value-sm {
            font-size: 13px;
            font-weight: 700;
            margin-top: 1px;
        }

        /* ABONOS */
        .abono-row {
            display: flex;
            justify-content: space-between;
            padding: 2px 0;
            font-size: 9px;
            border-bottom: 1px dotted #ccc;
        }

        .abono-row .fecha {
            flex: 1;
        }

        .abono-row .ref {
            flex: 1.5;
            text-align: center;
            font-size: 8px;
            color: #333;
        }

        .abono-row .monto {
            flex: 1;
            text-align: right;
            font-weight: 600;
        }

        .abono-total {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
            font-weight: 700;
            border-top: 2px solid #000;
            margin-top: 2px;
        }

        .abono-total .label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .abono-total .value {
            font-size: 12px;
        }

        /* IVA ROWS */
        .iva-row {
            display: flex;
            justify-content: space-between;
            padding: 1px 0;
            font-size: 8px;
            color: #000;
        }

        /* NOTA */
        .nota-text {
            font-size: 9px;
            padding: 4px 10px;
            border-left: 3px solid #000;
            margin: 4px 0;
            font-style: italic;
            background: #f4f4f4;
        }

        .uuid-text {
            font-size: 8px;
            background: #eee;
            padding: 1px 6px;
            font-family: 'Courier New', monospace;
            letter-spacing: 0.3px;
            word-break: break-all;
        }

        /* FOOTER */
        .footer {
            text-align: center;
            border-top: 3px double #000;
            padding-top: 12px;
            margin-top: 12px;
            font-size: 9px;
        }

        .footer .line {
            margin: 2px 0;
        }

        .footer .reimision-text {
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 2px;
            margin-bottom: 6px;
        }

        .firma {
            margin-top: 12px;
            padding-top: 10px;
            border-top: 1.5px solid #000;
            display: flex;
            justify-content: space-between;
            gap: 20px;
        }

        .firma .firma-item {
            text-align: center;
            flex: 1;
        }

        .firma .firma-item .linea {
            display: block;
            width: 100%;
            border-bottom: 1px solid #000;
            margin-top: 20px;
            padding-bottom: 2px;
        }

        .firma .firma-item .label {
            font-size: 7px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .firma .firma-item .nombre {
            font-size: 9px;
            font-weight: 600;
        }

        /* ============================================================ */
        /* PRINT */
        /* ============================================================ */
        @media print {
            body {
                padding: 10px;
                background: #fff;
            }

            .documento {
                border: 2px solid #000;
                padding: 15px 20px;
                max-width: 100%;
                box-shadow: none;
            }

            .monto-destacado,
            .cuentas-box,
            .nota-text,
            .uuid-text {
                background: #f4f4f4 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .header .folio {
                background: #000 !important;
                color: #fff !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .logo-img {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .section {
                page-break-inside: avoid;
                break-inside: avoid;
            }

            .firma {
                page-break-inside: avoid;
                break-inside: avoid;
            }
        }

        /* ============================================================ */
        /* RESPONSIVE */
        /* ============================================================ */
        @media screen and (max-width: 768px) {
            body {
                padding: 8px;
                font-size: 10px;
            }

            .documento {
                padding: 12px 15px;
            }

            .header .empresa {
                font-size: 15px;
            }

            .header .folio {
                font-size: 12px;
                padding: 2px 14px;
            }

            .monto-destacado .value {
                font-size: 18px;
            }

            .grid-2 {
                grid-template-columns: 1fr;
            }

            .grid-3 {
                grid-template-columns: 1fr 1fr;
            }

            .firma {
                flex-direction: column;
                gap: 10px;
            }

            .firma .firma-item .linea {
                margin-top: 12px;
            }
        }

        @media screen and (max-width: 480px) {
            body {
                padding: 4px;
                font-size: 9px;
            }

            .documento {
                padding: 8px 10px;
                border-width: 1px;
            }

            .header .empresa {
                font-size: 13px;
            }

            .header .folio {
                font-size: 10px;
                padding: 2px 10px;
            }

            .monto-destacado .value {
                font-size: 15px;
            }

            .grid-3 {
                grid-template-columns: 1fr;
            }

            .cuentas-box {
                flex-direction: column;
                gap: 4px;
            }

            .cuentas-box .flecha {
                transform: rotate(90deg);
                padding: 2px 0;
            }

            .row {
                flex-direction: column;
                align-items: flex-start;
                padding: 3px 0;
            }

            .row .value {
                text-align: left;
                width: 100%;
            }

            .abono-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 1px;
            }

            .abono-row .ref {
                text-align: left;
            }

            .abono-row .monto {
                text-align: left;
            }

            .firma {
                flex-direction: column;
                gap: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="documento">
        <!-- ========== HEADER ========== -->
        <div class="header">
            @php
                $logoPath = public_path('logos/logo.png');
                $logoData = null;
                if (file_exists($logoPath)) {
                    $imageData = file_get_contents($logoPath);
                    $logoData = base64_encode($imageData);
                }
            @endphp

            @if($logoData)
                <div>
                    <img src="data:image/png;base64,{{ $logoData }}" alt="Logo" class="logo-img">
                </div>
            @endif

            <div class="empresa">{{ $empresa->nombre_empresa ?? 'EMPRESA' }}</div>
            <div class="razon">{{ $empresa->razon_social ?? '—' }}</div>
            <div class="folio">REIMISIÓN: {{ $movimiento->poliza->folio ?? 'SIN FOLIO' }}</div>
            <div class="tipo">
                {{ $esTraspaso ? 'TRASPASO' : ($movimiento->poliza->tipo_poliza ?? '—') }}
                @if($movimiento->poliza->categoria == 'FISCAL')
                    <span class="tag-fiscal">FISCAL</span>
                @endif
                @if($movimiento->poliza->es_por_pagar)
                    <span class="badge-por-pagar">POR PAGAR</span>
                @endif
            </div>
            <div class="fecha">{{ $movimiento->poliza->fecha_poliza ? date('d/m/Y', strtotime($movimiento->poliza->fecha_poliza)) : '—' }}</div>
            <div>
                <span class="status">{{ $movimiento->poliza->estatus_texto ?? $movimiento->poliza->estatus ?? 'PENDIENTE' }}</span>
            </div>
        </div>

        <!-- ========== DATOS ========== -->
        <div class="section">
            <div class="section-title">Datos de la Póliza</div>

            @if(!$esTraspaso)
                <div class="row">
                    <span class="label">Persona</span>
                    <span class="value">{{ $movimiento->poliza->persona->nombre_completo ?? '—' }}</span>
                </div>
                <div class="row">
                    <span class="label">Cuenta</span>
                    <span class="value">{{ $movimiento->cuenta->nombre_cuenta ?? '—' }}</span>
                </div>
                <div class="row">
                    <span class="label">Cuenta Fondeadora</span>
                    <span class="value">{{ $movimiento->cuentaFondeadora->nombre_cuenta ?? '—' }}</span>
                </div>
            @endif

            @if($esTraspaso)
                <div class="cuentas-box">
                    <div class="cuenta">
                        <span class="label">Cuenta Origen</span>
                        <span class="value">{{ $movimiento->cuentaFondeadora->nombre_cuenta ?? '—' }}</span>
                    </div>
                    <div class="flecha">→</div>
                    <div class="cuenta">
                        <span class="label">Cuenta Destino</span>
                        <span class="value">{{ $movimiento->cuenta->nombre_cuenta ?? '—' }}</span>
                    </div>
                </div>
            @endif

            <div class="row">
                <span class="label">Marcador</span>
                <span class="value">{{ $movimiento->poliza->marcador->nombre_marcador ?? '—' }}</span>
            </div>

            @if($movimiento->poliza->referencia)
                <div class="row">
                    <span class="label">Referencia</span>
                    <span class="value">{{ $movimiento->poliza->referencia }}</span>
                </div>
            @endif

            @if($movimiento->poliza->es_por_pagar)
                <div class="row">
                    <span class="label">Fecha de Vencimiento</span>
                    <span class="value">
                        {{ $movimiento->poliza->fecha_vencimiento ? date('d/m/Y', strtotime($movimiento->poliza->fecha_vencimiento)) : '—' }}
                        @if($movimiento->poliza->fecha_vencimiento && strtotime($movimiento->poliza->fecha_vencimiento) < time())
                            <span class="badge-vencido">VENCIDO</span>
                        @endif
                    </span>
                </div>
            @endif
        </div>

        <!-- ========== MONTOS ========== -->
        <div class="section">
            <div class="section-title">Montos</div>

            <div class="monto-destacado">
                <span class="label">{{ $esTraspaso ? 'MONTO TRASPASO' : 'TOTAL DE LA PÓLIZA' }}</span>
                <span class="value">${{ number_format(abs($montoMostrar), 2) }}</span>
            </div>

            <div class="row">
                <span class="label">Base Gravable</span>
                <span class="value">${{ number_format(abs($movimiento->monto_base ?? 0), 2) }}</span>
            </div>
            <div class="row">
                <span class="label">IVA</span>
                <span class="value">${{ number_format(abs($movimiento->monto_iva ?? 0), 2) }}</span>
            </div>
            <div class="row" style="border-top:2px solid #000;padding-top:4px;margin-top:2px;">
                <span class="label" style="font-weight:700;font-size:11px;">Total con IVA</span>
                <span class="value" style="font-weight:700;font-size:14px;">${{ number_format(abs($montoMostrar), 2) }}</span>
            </div>
        </div>

        <!-- ========== DESGLOSE IVA ========== -->
        @if(($movimiento->monto_iva_cero ?? 0) != 0 || ($movimiento->monto_iva_dieciseis ?? 0) != 0 || ($movimiento->iva_dieciseis ?? 0) != 0)
            <div class="section">
                <div class="section-title">Desglose de IVA</div>
                <div class="grid-3">
                    @if(($movimiento->monto_iva_cero ?? 0) != 0)
                        <div class="grid-item">
                            <div class="label-sm">IVA 0% (Exento)</div>
                            <div class="value-sm">${{ number_format(abs($movimiento->monto_iva_cero), 2) }}</div>
                        </div>
                    @endif
                    @if(($movimiento->monto_iva_dieciseis ?? 0) != 0)
                        <div class="grid-item">
                            <div class="label-sm">IVA 16% (Base)</div>
                            <div class="value-sm">${{ number_format(abs($movimiento->monto_iva_dieciseis), 2) }}</div>
                        </div>
                        <div class="grid-item">
                            <div class="label-sm">IVA 16% (Calculado)</div>
                            <div class="value-sm">${{ number_format(abs($movimiento->iva_dieciseis), 2) }}</div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- ========== SALDOS (POR PAGAR) ========== -->
        @if($movimiento->poliza->es_por_pagar)
            <div class="section">
                <div class="section-title">Saldos</div>
                <div class="row">
                    <span class="label">Total Abonado</span>
                    <span class="value">${{ number_format(abs($totalAbonado ?? 0), 2) }}</span>
                </div>
                <div class="row" style="border-top:2px solid #000;padding-top:4px;margin-top:2px;">
                    <span class="label" style="font-weight:700;font-size:11px;">Saldo Pendiente</span>
                    <span class="value" style="font-weight:700;font-size:14px;">${{ number_format(abs($saldoPendiente ?? 0), 2) }}</span>
                </div>
            </div>
        @endif

        <!-- ========== ABONOS ========== -->
        @if($movimiento->poliza->es_por_pagar && isset($abonos) && $abonos->count() > 0)
            <div class="section">
                <div class="section-title">Historial de Abonos</div>
                <div>
                    @foreach($abonos as $abono)
                        <div class="abono-row">
                            <span class="fecha">{{ $abono->fecha_abono ? date('d/m/Y', strtotime($abono->fecha_abono)) : '—' }}</span>
                            <span class="ref">{{ $abono->referencia ?? '—' }}</span>
                            <span class="monto">${{ number_format(abs($abono->monto_abonado), 2) }}</span>
                        </div>
                    @endforeach
                    <div class="abono-total">
                        <span class="label">Saldo Pendiente</span>
                        <span class="value">${{ number_format(abs($saldoPendiente ?? 0), 2) }}</span>
                    </div>
                </div>
            </div>
        @endif

        <!-- ========== FACTURACIÓN ========== -->
        @if($movimiento->poliza->categoria === 'FISCAL')
            <div class="section">
                <div class="section-title">Información Fiscal</div>
                <div class="grid-2">
                    <div>
                        <div class="label-sm">Fecha Factura</div>
                        <div style="font-weight:600;font-size:10px;">{{ $movimiento->poliza->fecha_factura ? date('d/m/Y', strtotime($movimiento->poliza->fecha_factura)) : '—' }}</div>
                    </div>
                    <div>
                        <div class="label-sm">Número Factura</div>
                        <div style="font-weight:600;font-size:10px;">{{ $movimiento->poliza->numero_factura ?? '—' }}</div>
                    </div>
                    @if($movimiento->poliza->serie_factura)
                        <div>
                            <div class="label-sm">Serie</div>
                            <div style="font-weight:600;font-size:10px;">{{ $movimiento->poliza->serie_factura }}</div>
                        </div>
                    @endif
                    @if($movimiento->poliza->folio_factura)
                        <div>
                            <div class="label-sm">Folio</div>
                            <div style="font-weight:600;font-size:10px;">{{ $movimiento->poliza->folio_factura }}</div>
                        </div>
                    @endif
                    @if($movimiento->poliza->uuid_factura)
                        <div style="grid-column: span 2;">
                            <div class="label-sm">UUID</div>
                            <div style="font-weight:600;font-size:9px;font-family:'Courier New',monospace;word-break:break-all;background:#f4f4f4;padding:2px 6px;border-radius:2px;">{{ $movimiento->poliza->uuid_factura }}</div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- ========== OBSERVACIONES ========== -->
        @if($movimiento->poliza->nota)
            <div class="section">
                <div class="section-title">Observaciones</div>
                <div class="nota-text">{{ $movimiento->poliza->nota }}</div>
            </div>
        @endif

        <!-- ========== FOOTER ========== -->
        <div class="footer">
            <div class="reimision-text">★ REIMISIÓN DE PÓLIZA ★</div>
            <div class="line">RIC | IMPRIME: {{ Auth::user()->nombre_completo ?? 'Sistema' }}</div>
            <div class="line">{{ $fecha_exportacion }}</div>

            <div class="firma">
                <div class="firma-item">
                    <span class="nombre">{{ Auth::user()->nombre_completo ?? '—' }}</span>
                    <span class="linea"></span>
                    <span class="label">NOMBRE Y FIRMA DE QUIEN ENTREGA</span>
                </div>
                <div class="firma-item">
                    <span class="nombre">{{ $movimiento->poliza->persona->nombre_completo ?? '—' }}</span>
                    <span class="linea"></span>
                    <span class="label">NOMBRE Y FIRMA DE QUIEN RECIBE</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>