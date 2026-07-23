<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Cuentas de Resultados - {{ $empresa }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Inter', 'Segoe UI', 'Arial', sans-serif;
            font-size: 9.5px;
            padding: 15px 20px;
            background: #ffffff;
            color: #1a202c;
        }

        .reporte-container {
            max-width: 1100px;
            margin: 0 auto;
            background: #ffffff;
            padding: 15px 20px;
        }

        /* ===== HEADER - ALINEADO CORRECTAMENTE ===== */
        .header {
            display: table;
            width: 100%;
            padding-bottom: 12px;
            margin-bottom: 14px;
            border-bottom: 2px solid #e8edf4;
        }
        .header-left {
            display: table-cell;
            vertical-align: middle;
            width: 140px;
            text-align: left;
        }
        .header-left img {
            max-width: 130px;
            max-height: 80px;
            object-fit: contain;
        }
        .header-center {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
            padding: 0 10px;
        }
        .header-center h1 {
            font-size: 18px;
            font-weight: 700;
            color: #1a365d;
            letter-spacing: 0.5px;
        }
        .header-center .empresa {
            font-size: 13px;
            font-weight: 600;
            color: #2d3748;
        }
        .header-center .subtitle {
            font-size: 10px;
            color: #718096;
        }
        .header-center .fecha {
            font-size: 9px;
            color: #a0aec0;
            margin-top: 2px;
        }
        .header-center .fecha span {
            font-weight: 600;
            color: #4a5568;
        }
        .header-right {
            display: table-cell;
            vertical-align: middle;
            width: 80px;
            text-align: right;
        }
        .header-right .badge {
            background: #1a365d;
            color: white;
            padding: 4px 12px;
            font-size: 11px;
            font-weight: 700;
            border-radius: 6px;
            display: inline-block;
        }

        /* ===== FILTROS ===== */
        .filtros {
            background: #f8fafc;
            padding: 8px 14px;
            border-radius: 6px;
            margin-bottom: 14px;
            border: 1px solid #e9edf4;
            font-size: 8.5px;
            color: #4a5568;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 4px 16px;
        }
        .filtros strong {
            color: #1a202c;
            font-weight: 700;
        }
        .filtros .item {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            white-space: nowrap;
        }
        .filtros .item .label {
            font-weight: 600;
            color: #718096;
        }
        .filtros .item .value {
            font-weight: 600;
            color: #2d3748;
        }
        .filtros .item .value.destacado {
            color: #2b6cb0;
            font-weight: 700;
        }

        /* ===== TABLA ===== */
        .tabla-wrapper {
            border-radius: 6px;
            overflow: hidden;
            border: 1px solid #e9edf4;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5px;
        }
        table thead {
            background: #1a365d;
            color: white;
        }
        table thead th {
            padding: 7px 10px;
            text-align: center;
            font-weight: 600;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            border: none;
        }
        table thead th.text-left {
            text-align: left !important;
        }
        table thead th.text-right {
            text-align: right !important;
        }
        table tbody td {
            padding: 5px 10px;
            border-bottom: 1px solid #f0f2f5;
            text-align: center;
            color: #2d3748;
        }
        table tbody td.text-left {
            text-align: left !important;
        }
        table tbody td.text-right {
            text-align: right !important;
        }
        table tbody tr:last-child td {
            border-bottom: none;
        }
        table tbody tr:nth-child(even) {
            background: #fafbfc;
        }

        /* ===== FILAS MADRE ===== */
        .row-madre {
            background: #f0f7ff !important;
            font-weight: 700;
        }
        .row-madre td {
            border-top: 1px solid #d6e8ff !important;
            border-bottom: 1px solid #d6e8ff !important;
            padding: 6px 10px !important;
        }
        .row-madre td:first-child {
            border-left: 3px solid #3182ce;
            padding-left: 12px !important;
        }

        /* ===== FILAS HIJA ===== */
        .row-hija td:first-child {
            padding-left: 30px !important;
            text-align: left !important;
        }
        .row-hija .nivel-2 {
            padding-left: 42px !important;
        }
        .row-hija .nivel-3 {
            padding-left: 54px !important;
        }
        .row-hija .nivel-4 {
            padding-left: 66px !important;
        }

        /* ===== MONTOS ===== */
        .monto-positive { color: #2b6cb0; font-weight: 600; }
        .monto-negative { color: #e53e3e; font-weight: 600; }
        .monto-neutral { color: #a0aec0; }

        /* ===== BADGES ===== */
        .fiscal-badge {
            display: inline-block;
            padding: 1px 10px;
            background: #48bb78;
            color: white;
            border-radius: 10px;
            font-size: 6.5px;
            font-weight: 700;
            margin-left: 6px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        /* ===== TOTALES ===== */
        .totales {
            margin-top: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .total-card {
            background: #fafbfc;
            border-radius: 6px;
            padding: 5px 14px;
            border: 1px solid #e9edf4;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .total-card .label {
            font-size: 7px;
            font-weight: 700;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .total-card .value {
            font-size: 14px;
            font-weight: 700;
            color: #1a202c;
        }
        .total-card .value.ingreso { color: #2b6cb0; }
        .total-card .value.egreso { color: #e53e3e; }
        .total-card .value.neutro { color: #1a202c; }
        .total-card.destacada {
            background: #1a365d;
            border-color: #1a365d;
        }
        .total-card.destacada .label {
            color: rgba(255, 255, 255, 0.7);
        }
        .total-card.destacada .value {
            color: white;
        }
        .separador {
            color: #d1d5db;
            font-size: 16px;
            font-weight: 300;
        }

        /* ===== UTILIDADES ===== */
        .text-left { text-align: left !important; }
        .text-right { text-align: right !important; }
        .text-center { text-align: center !important; }

        /* ===== IMPRESIÓN ===== */
        @media print {
            body { padding: 10px; }
            .reporte-container { padding: 10px 12px; }
            .header-left img { max-width: 100px; max-height: 60px; }
            .header-center h1 { font-size: 16px; }
            table { font-size: 7.5px; }
            table thead th { padding: 4px 6px; font-size: 7px; }
            table tbody td { padding: 3px 6px; }
            .total-card { padding: 3px 10px; }
            .total-card .value { font-size: 12px; }
            .badge { background: #1a365d !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            .fiscal-badge { background: #48bb78 !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            .row-madre { background: #f0f7ff !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            .filtros { background: #f8fafc !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            .total-card { background: #fafbfc !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            .total-card.destacada { background: #1a365d !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        }

        @media (max-width: 768px) {
            .totales { flex-wrap: wrap; }
            .separador { display: none; }
            .header { display: flex; flex-wrap: wrap; justify-content: center; gap: 6px; }
            .header-left { display: block; width: auto; text-align: center; }
            .header-center { display: block; width: 100%; }
            .header-right { display: block; width: auto; text-align: center; }
            .filtros { gap: 4px 10px; }
        }
    </style>
</head>
<body>
    <div class="reporte-container">
        <!-- ===== HEADER ===== -->
        <div class="header">
            <div class="header-left">
                @php
                    $logoPath = public_path('logos/logo.png');
                    $logoBase64 = '';
                    if (file_exists($logoPath)) {
                        $logoData = file_get_contents($logoPath);
                        $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
                    }
                @endphp
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" alt="Logo">
                @endif
            </div>
            <div class="header-center">
                <h1>Cuentas de Resultados</h1>
                <div class="empresa">{{ $empresa }}</div>
                <div class="subtitle">Reporte de Cuentas de Resultados</div>
                <div class="fecha">
                    Generado: <span>{{ $fecha_exportacion }}</span> &nbsp;|&nbsp; Total de cuentas: <span>{{ $total_cuentas }}</span>
                </div>
            </div>
            <div class="header-right">
                <span class="badge">{{ $total_cuentas }}</span>
            </div>
        </div>

        <!-- ===== FILTROS ===== -->
        <div class="filtros">
            <strong>Filtros:</strong>
            <span class="item">
                <span class="label">Fecha:</span>
                <span class="value">{{ $fecha_desde ?: 'Inicio' }} - {{ $fecha_hasta ?: 'Actual' }}</span>
            </span>
            <span class="item">
                <span class="label">Tipo:</span>
                <span class="value">{{ ucfirst($tipo_filtro) }}</span>
            </span>
            <span class="item">
                <span class="label">Cuentas:</span>
                <span class="value">{{ $total_cuentas }}</span>
            </span>
            <span class="item">
                <span class="label">Saldo:</span>
                <span class="value destacado">${{ number_format(abs($saldo_total), 2) }}</span>
            </span>
        </div>

        <!-- ===== TABLA ===== -->
        <div class="tabla-wrapper">
            <table>
                <thead>
                    <tr>
                        <th style="width: 70%;" class="text-left">Nombre de la Cuenta</th>
                        <th style="width: 30%;" class="text-right">Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cuentas as $padre)
                        @php
                            $saldoPadre = (float) ($padre['subtotal'] ?? 0);
                            $saldoPadreAbs = abs($saldoPadre);
                            $claseSaldoPadre = $saldoPadre >= 0 ? 'monto-positive' : 'monto-negative';
                            $tieneHijas = !empty($padre['hijas']);
                        @endphp

                        <tr class="row-madre">
                            <td class="text-left">
                                <strong>{{ $padre['nombre_cuenta'] }}</strong>
                                @if($tipo_filtro === 'fiscales')
                                    <span class="fiscal-badge">FISCAL</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <span class="{{ $claseSaldoPadre }}">${{ number_format($saldoPadreAbs, 2) }}</span>
                            </td>
                        </tr>

                        @if($tieneHijas)
                            @foreach($padre['hijas'] as $hija)
                                @php
                                    $saldoHija = (float) ($hija['saldo'] ?? 0);
                                    $saldoHijaAbs = abs($saldoHija);
                                    $claseSaldoHija = $saldoHija >= 0 ? 'monto-positive' : 'monto-negative';
                                    $nivel = (int) ($hija['nivel'] ?? 2);
                                    $claseNivel = 'nivel-' . min($nivel, 4);
                                @endphp
                                <tr class="row-hija">
                                    <td class="text-left {{ $claseNivel }}">
                                        {{ $hija['nombre_cuenta'] }}
                                        @if(isset($hija['es_fiscal']) && $hija['es_fiscal'])
                                            <span class="fiscal-badge">FISCAL</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <span class="{{ $claseSaldoHija }}">${{ number_format($saldoHijaAbs, 2) }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    @empty
                        <tr>
                            <td colspan="2" style="text-align: center; padding: 30px; color: #a0aec0; font-size: 12px;">
                                No hay cuentas de resultados para mostrar
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- ===== TOTALES ===== -->
        <div class="totales">
            <div class="total-card">
                <span class="label">Cuentas</span>
                <span class="value neutro">{{ $total_cuentas }}</span>
            </div>
            <span class="separador">|</span>
            <div class="total-card destacada">
                <span class="label">Saldo Total</span>
                <span class="value">${{ number_format(abs($saldo_total), 2) }}</span>
            </div>
        </div>
    </div>
</body>
</html>