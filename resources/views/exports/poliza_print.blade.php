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
            font-family: 'Courier New', 'Courier', monospace;
            font-size: 10px;
            color: #1a1a2e;
            padding: 15px;
            background: #ffffff;
            max-width: 380px;
            margin: 0 auto;
        }
        .ticket {
            border: 1px solid #000;
            padding: 14px 16px;
            position: relative;
        }
        .ticket::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: repeating-linear-gradient(90deg, #000 0px, #000 3px, transparent 3px, transparent 6px);
        }
        .ticket::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: repeating-linear-gradient(90deg, #000 0px, #000 3px, transparent 3px, transparent 6px);
        }

        /* HEADER */
        .header {
            text-align: center;
            border-bottom: 2px dashed #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .header .logo-container {
            margin-bottom: 6px;
        }
        .header .logo {
            max-width: 80px;
            max-height: 50px;
            display: block;
            margin: 0 auto;
        }
        .header .empresa {
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-top: 4px;
        }
        .header .empresa-line {
            font-size: 8px;
            letter-spacing: 1px;
            margin-top: 2px;
            color: #555;
        }
        .header .folio {
            font-size: 11px;
            font-weight: 700;
            margin-top: 6px;
            background: #000;
            color: #fff;
            padding: 2px 14px;
            display: inline-block;
            letter-spacing: 1px;
        }
        .header .tipo {
            font-size: 10px;
            font-weight: 700;
            margin-top: 4px;
            letter-spacing: 1px;
        }
        .header .fecha {
            font-size: 9px;
            color: #555;
            margin-top: 2px;
        }
        .header .status {
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
            margin-top: 4px;
            padding: 2px 12px;
            display: inline-block;
            border: 1px solid #000;
            letter-spacing: 1px;
        }

        /* SECCIONES */
        .section {
            margin-bottom: 6px;
        }
        .section-title {
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            border-bottom: 1px dotted #999;
            padding-bottom: 2px;
            margin-bottom: 4px;
            color: #333;
        }

        /* FILAS */
        .row {
            display: flex;
            justify-content: space-between;
            padding: 2px 0;
            border-bottom: 1px dotted #eee;
        }
        .row:last-child {
            border-bottom: none;
        }
        .row .label {
            font-weight: 600;
            color: #555;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .row .value {
            font-weight: 600;
            text-align: right;
            font-size: 9px;
        }

        /* MONTOS DESTACADOS */
        .monto-destacado {
            text-align: center;
            padding: 8px 0;
            margin: 4px 0;
            background: #f8fafc;
            border: 1px solid #ddd;
        }
        .monto-destacado .label {
            font-size: 7px;
            font-weight: 700;
            text-transform: uppercase;
            color: #666;
            letter-spacing: 1px;
            display: block;
        }
        .monto-destacado .value {
            font-size: 18px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        /* CUENTAS TRASPASO */
        .cuentas-box {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 8px;
            background: #f8fafc;
            border: 1px solid #ddd;
            margin: 4px 0;
        }
        .cuentas-box .cuenta {
            text-align: center;
            padding: 2px 4px;
            flex: 1;
        }
        .cuentas-box .cuenta .label {
            font-size: 6px;
            font-weight: 700;
            text-transform: uppercase;
            color: #666;
            letter-spacing: 1px;
            display: block;
        }
        .cuentas-box .cuenta .value {
            font-size: 8px;
            font-weight: 600;
            margin-top: 2px;
        }
        .cuentas-box .flecha {
            font-size: 14px;
            font-weight: 700;
            color: #666;
            padding: 0 6px;
        }

        /* ABONOS */
        .abono-row {
            display: flex;
            justify-content: space-between;
            padding: 2px 0;
            font-size: 8px;
            border-bottom: 1px dotted #eee;
        }
        .abono-row .fecha { flex: 1; }
        .abono-row .monto { flex: 1; text-align: right; font-weight: 600; }
        .abono-row .ref { flex: 1; text-align: center; font-size: 7px; color: #555; }
        .abono-total {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
            font-weight: 700;
            border-top: 2px solid #000;
            margin-top: 2px;
        }
        .abono-total .label { font-size: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
        .abono-total .value { font-size: 11px; }

        /* DETALLE IVA */
        .iva-row {
            display: flex;
            justify-content: space-between;
            padding: 1px 0;
            font-size: 7px;
            color: #555;
        }

        /* FOOTER */
        .footer {
            text-align: center;
            border-top: 2px dashed #000;
            padding-top: 10px;
            margin-top: 10px;
            font-size: 8px;
            color: #555;
        }
        .footer .line {
            margin: 3px 0;
        }
        .footer .firma {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #000;
            display: flex;
            justify-content: space-between;
        }
        .footer .firma .firma-item {
            text-align: center;
            flex: 1;
            padding: 0 4px;
        }
        .footer .firma .firma-item .linea {
            display: block;
            width: 100%;
            border-bottom: 1px solid #000;
            margin-top: 18px;
            padding-bottom: 2px;
        }
        .footer .firma .firma-item .label {
            font-size: 6px;
            font-weight: 700;
            text-transform: uppercase;
            color: #555;
            letter-spacing: 0.5px;
        }
        .footer .firma .firma-item .nombre {
            font-size: 7px;
            font-weight: 600;
        }

        .nota-text {
            font-size: 8px;
            padding: 4px 8px;
            background: #f8fafc;
            border-left: 2px solid #666;
            margin: 4px 0;
            font-style: italic;
            color: #333;
        }

        .uuid-text {
            font-size: 6px;
            color: #555;
            background: #f1f5f9;
            padding: 1px 6px;
            font-family: 'Courier New', monospace;
            letter-spacing: 0.5px;
        }

        .separator-dots {
            text-align: center;
            color: #999;
            font-size: 8px;
            letter-spacing: 3px;
            margin: 4px 0;
        }

        .impreso-por {
            font-size: 7px;
            color: #666;
            margin-top: 4px;
            letter-spacing: 1px;
        }

        .folio-value {
            font-weight: 700;
            letter-spacing: 1px;
        }

        .tag-fiscal {
            font-size: 6px;
            font-weight: 700;
            padding: 1px 6px;
            border: 1px solid #666;
            margin-left: 4px;
        }

        .badge-por-pagar {
            font-size: 6px;
            font-weight: 700;
            padding: 1px 6px;
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #f59e0b;
            margin-left: 4px;
            border-radius: 2px;
        }

        .badge-vencido {
            font-size: 6px;
            font-weight: 700;
            padding: 1px 6px;
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #ef4444;
            margin-left: 4px;
            border-radius: 2px;
        }

        .text-vencido {
            color: #dc2626;
            font-weight: 700;
        }

        @media print {
            body { 
                padding: 8px; 
                max-width: 100%;
                margin: 0;
            }
            .ticket { 
                border: 1px solid #000; 
                max-width: 380px;
                margin: 0 auto;
            }
            .header .folio { 
                background: #000 !important; 
                color: #fff !important; 
                -webkit-print-color-adjust: exact !important; 
                print-color-adjust: exact !important; 
            }
            .monto-destacado { 
                background: #f8fafc !important; 
                -webkit-print-color-adjust: exact !important; 
                print-color-adjust: exact !important; 
            }
            .cuentas-box { 
                background: #f8fafc !important; 
                -webkit-print-color-adjust: exact !important; 
                print-color-adjust: exact !important; 
            }
            .nota-text { 
                background: #f8fafc !important; 
                -webkit-print-color-adjust: exact !important; 
                print-color-adjust: exact !important; 
            }
            .uuid-text { 
                background: #f1f5f9 !important; 
                -webkit-print-color-adjust: exact !important; 
                print-color-adjust: exact !important; 
            }
            .header .logo { 
                -webkit-print-color-adjust: exact !important; 
                print-color-adjust: exact !important; 
            }
            .badge-por-pagar {
                background: #fef3c7 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .badge-vencido {
                background: #fee2e2 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .text-vencido {
                color: #dc2626 !important;
            }
        }
    </style>
</head>
<body>
    <div class="ticket">
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
            <div class="logo-container">
                <img src="data:image/png;base64,{{ $logoData }}" alt="Logo" class="logo">
            </div>
            @endif
            
            <div class="empresa">{{ $empresa->nombre_empresa ?? 'EMPRESA' }}</div>
            <div class="empresa-line">{{ $empresa->razon_social ?? '—' }}</div>
            <div class="folio">FOLIO: {{ $movimiento->poliza->folio ?? 'SIN FOLIO' }}</div>
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
                <span class="status">
                    {{ $movimiento->poliza->estatus_texto ?? $movimiento->poliza->estatus ?? 'PENDIENTE' }}
                </span>
            </div>
        </div>

        <!-- ========== PERSONA / CUENTAS ========== -->
        <div class="section">
            <div class="section-title">Datos</div>
            
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
                <span class="label">Cta. Fondeadora</span>
                <span class="value">{{ $movimiento->cuentaFondeadora->nombre_cuenta ?? '—' }}</span>
            </div>
            @endif

            @if($esTraspaso)
            <div class="cuentas-box">
                <div class="cuenta">
                    <span class="label">Origen</span>
                    <span class="value">{{ $movimiento->cuentaFondeadora->nombre_cuenta ?? '—' }}</span>
                </div>
                <div class="flecha">→</div>
                <div class="cuenta">
                    <span class="label">Destino</span>
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
                <span class="label">Vencimiento</span>
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
                <span class="label">{{ $esTraspaso ? 'MONTO TRASPASO' : 'TOTAL' }}</span>
                <span class="value">
                    ${{ number_format(abs($montoMostrar), 2) }}
                </span>
            </div>

            <div class="row">
                <span class="label">Base Gravable</span>
                <span class="value">${{ number_format(abs($movimiento->monto_base ?? 0), 2) }}</span>
            </div>
            <div class="row">
                <span class="label">IVA</span>
                <span class="value">${{ number_format(abs($movimiento->monto_iva ?? 0), 2) }}</span>
            </div>
            <div class="row" style="border-top:1px solid #000;padding-top:4px;margin-top:2px;">
                <span class="label" style="font-weight:700;">Total con IVA</span>
                <span class="value" style="font-weight:700;font-size:11px;">
                    ${{ number_format(abs($montoMostrar), 2) }}
                </span>
            </div>
        </div>

        <!-- ========== DETALLE IVA (DOBLE IVA) ========== -->
        @if(($movimiento->monto_iva_cero ?? 0) != 0 || ($movimiento->monto_iva_dieciseis ?? 0) != 0 || ($movimiento->iva_dieciseis ?? 0) != 0)
        <div class="section">
            <div class="section-title">Desglose IVA</div>
            @if(($movimiento->monto_iva_cero ?? 0) != 0)
            <div class="iva-row">
                <span>IVA 0% (Exento)</span>
                <span>${{ number_format(abs($movimiento->monto_iva_cero), 2) }}</span>
            </div>
            @endif
            @if(($movimiento->monto_iva_dieciseis ?? 0) != 0)
            <div class="iva-row">
                <span>IVA 16% (Base)</span>
                <span>${{ number_format(abs($movimiento->monto_iva_dieciseis), 2) }}</span>
            </div>
            <div class="iva-row">
                <span>IVA 16% (Calculado)</span>
                <span>${{ number_format(abs($movimiento->iva_dieciseis), 2) }}</span>
            </div>
            @endif
            <div class="iva-row" style="border-top:1px solid #ccc;padding-top:2px;font-weight:700;">
                <span>TOTAL</span>
                <span>${{ number_format(abs(($movimiento->monto_iva_cero ?? 0) + ($movimiento->monto_iva_dieciseis ?? 0) + ($movimiento->iva_dieciseis ?? 0)), 2) }}</span>
            </div>
        </div>
        @endif

        <!-- ========== SALDOS (SI ES POR PAGAR) ========== -->
        @if($movimiento->poliza->es_por_pagar)
        <div class="section">
            <div class="section-title">Saldos</div>
            <div class="row">
                <span class="label">Total Abonado</span>
                <span class="value">${{ number_format(abs($totalAbonado ?? 0), 2) }}</span>
            </div>
            <div class="row" style="border-top:1px solid #000;padding-top:4px;margin-top:2px;">
                <span class="label" style="font-weight:700;">Saldo Pendiente</span>
                <span class="value" style="font-weight:700;font-size:11px;">
                    ${{ number_format(abs($saldoPendiente ?? 0), 2) }}
                </span>
            </div>
        </div>
        @endif

        <!-- ========== ABONOS ========== -->
        @if($movimiento->poliza->es_por_pagar && isset($abonos) && $abonos->count() > 0)
        <div class="section">
            <div class="section-title">Abonos</div>
            @foreach($abonos as $abono)
            <div class="abono-row">
                <span class="fecha">{{ $abono->fecha_abono ? date('d/m/Y', strtotime($abono->fecha_abono)) : '—' }}</span>
                <span class="ref">{{ $abono->referencia ?? '—' }}</span>
                <span class="monto">${{ number_format(abs($abono->monto_abonado), 2) }}</span>
            </div>
            @endforeach
            <div class="abono-total">
                <span class="label">Saldo Pendiente</span>
                <span class="value">
                    ${{ number_format(abs($saldoPendiente ?? 0), 2) }}
                </span>
            </div>
        </div>
        @endif

        <!-- ========== FACTURACIÓN ========== -->
        @if($movimiento->poliza->categoria === 'FISCAL')
        <div class="section">
            <div class="section-title">Facturación</div>
            <div class="row">
                <span class="label">Fecha Factura</span>
                <span class="value">{{ $movimiento->poliza->fecha_factura ? date('d/m/Y', strtotime($movimiento->poliza->fecha_factura)) : '—' }}</span>
            </div>
            <div class="row">
                <span class="label">Número</span>
                <span class="value">{{ $movimiento->poliza->numero_factura ?? '—' }}</span>
            </div>
            @if($movimiento->poliza->serie_factura)
            <div class="row">
                <span class="label">Serie</span>
                <span class="value">{{ $movimiento->poliza->serie_factura }}</span>
            </div>
            @endif
            @if($movimiento->poliza->folio_factura)
            <div class="row">
                <span class="label">Folio</span>
                <span class="value">{{ $movimiento->poliza->folio_factura }}</span>
            </div>
            @endif
            @if($movimiento->poliza->uuid_factura)
            <div class="row">
                <span class="label">UUID</span>
                <span class="value"><span class="uuid-text">{{ $movimiento->poliza->uuid_factura }}</span></span>
            </div>
            @endif
            <div class="row" style="border:none;padding:2px 0;">
                <span class="label">PDF</span>
                <span class="value">{{ $movimiento->poliza->ruta_pdf ? '✓ Adjunto' : '—' }}</span>
            </div>
            <div class="row" style="border:none;padding:2px 0;">
                <span class="label">XML</span>
                <span class="value">{{ $movimiento->poliza->ruta_xml ? '✓ Adjunto' : '—' }}</span>
            </div>
        </div>
        @endif

        <!-- ========== OBSERVACIONES ========== -->
        @if($movimiento->poliza->nota)
        <div class="section">
            <div class="section-title">Observación</div>
            <div class="nota-text">{{ $movimiento->poliza->nota }}</div>
        </div>
        @endif

        <!-- ========== SEPARADOR ========== -->
        <div class="separator-dots">• • • • • • • • • • • • • • • • • • • • •</div>

        <!-- ========== FOOTER / FIRMAS ========== -->
        <div class="footer">
            <div class="impreso-por">
                RIC | IMPRIME: {{ Auth::user()->nombre_completo ?? 'Sistema' }}
            </div>
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