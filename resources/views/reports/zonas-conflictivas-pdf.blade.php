<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Zonas Conflictivas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 18px;
            margin: 0;
            color: #2c3e50;
        }
        .header p {
            font-size: 12px;
            margin: 5px 0;
            color: #7f8c8d;
        }
        .summary {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f9f9f9;
            border-radius: 5px;
        }
        .summary-item {
            display: inline-block;
            margin-right: 30px;
            text-align: center;
        }
        .summary-item .value {
            font-size: 16px;
            font-weight: bold;
            color: #2980b9;
        }
        .summary-item .label {
            font-size: 10px;
            color: #7f8c8d;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th {
            background-color: #2c3e50;
            color: #ffffff;
            font-weight: normal;
            padding: 8px;
            text-align: left;
            font-size: 11px;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #e0e0e0;
            font-size: 11px;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .risk-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 5px;
            vertical-align: middle;
        }
        .risk-low {
            background-color: #2ecc71;
        }
        .risk-medium {
            background-color: #f39c12;
        }
        .risk-high {
            background-color: #e74c3c;
        }
        .footer {
            margin-top: 30px;
            font-size: 10px;
            text-align: center;
            color: #7f8c8d;
            border-top: 1px solid #e0e0e0;
            padding-top: 10px;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Zonas Conflictivas</h1>
        <p>Generado el {{ date('d/m/Y H:i') }}</p>
        @if(isset($periodo))
            <p>Período: {{ $periodo }}</p>
        @endif
    </div>

    <div class="summary">
        <div class="summary-item">
            <div class="value">{{ $totalZonas }}</div>
            <div class="label">Zonas identificadas</div>
        </div>
        <div class="summary-item">
            <div class="value">{{ $zonasAltoRiesgo }}</div>
            <div class="label">Zonas de alto riesgo</div>
        </div>
        <div class="summary-item">
            <div class="value">{{ number_format($porcentajePatrullaje, 1) }}%</div>
            <div class="label">Con patrullaje</div>
        </div>
    </div>
    
    @if(isset($filtros) && count($filtros) > 0)
        <div style="margin-bottom: 15px; font-size: 11px;">
            <strong>Criterios de filtrado:</strong>
            @foreach($filtros as $filtro => $valor)
                <br>• {{ $filtro }}: {{ $valor }}
            @endforeach
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">N°</th>
                <th style="width: 18%;">Sector</th>
                <th style="width: 15%;">Comuna</th>
                <th style="width: 15%;">Región</th>
                <th style="width: 7%;">Total delitos</th>
                <th style="width: 10%;">Índice</th>
                <th style="width: 15%;">Delito predominante</th>
                <th style="width: 7%;">Tendencia</th>
                <th style="width: 8%;">Patrullaje</th>
            </tr>
        </thead>
        <tbody>
            @foreach($zonas as $index => $zona)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $zona['sector'] }}</td>
                    <td>{{ $zona['comuna'] }}</td>
                    <td>{{ $zona['region'] }}</td>
                    <td style="text-align: center;">{{ $zona['total_delitos'] }}</td>
                    <td>
                        @php
                            $indicador = '';
                            $indice = $zona['indice'];
                            if($indice >= 8) {
                                $indicador = 'risk-high';
                            } elseif($indice >= 4) {
                                $indicador = 'risk-medium';
                            } else {
                                $indicador = 'risk-low';
                            }
                        @endphp
                        <span class="risk-indicator {{ $indicador }}"></span>
                        {{ $zona['indice'] }}/10
                    </td>
                    <td>{{ $zona['delito_predominante'] }}</td>
                    <td>
                        @if($zona['tendencia'] > 0)
                            ↑ {{ abs($zona['tendencia']) }}%
                        @elseif($zona['tendencia'] < 0)
                            ↓ {{ abs($zona['tendencia']) }}%
                        @else
                            → 0%
                        @endif
                    </td>
                    <td>{{ $zona['patrullaje'] ? 'Sí' : 'No' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 20px; font-size: 11px;">
        <strong>Información sobre Índice de Conflictividad:</strong>
        <p style="margin: 5px 0 0 0;">
            El índice considera: total de delitos, gravedad, concentración temporal y reincidencia en la zona.
        </p>
        <div style="margin: 10px 0 0 10px;">
            <div style="margin-bottom: 5px;">
                <span class="risk-indicator risk-low"></span> Bajo (1-3): Monitoreo estándar
            </div>
            <div style="margin-bottom: 5px;">
                <span class="risk-indicator risk-medium"></span> Medio (4-7): Vigilancia incrementada
            </div>
            <div>
                <span class="risk-indicator risk-high"></span> Alto (8-10): Intervención prioritaria
            </div>
        </div>
    </div>

    <div class="footer">
        <p>PREVCRIM - Sistema de Prevención del Crimen y Gestión de Patrullaje</p>
        <p>Documento confidencial para uso interno de la institución</p>
    </div>
</body>
</html>
