<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $titulo }}</title>
    <style>        @page {
            margin: 1cm;
            size: A4 landscape;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            margin: 0;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            color: #1e40af;
            font-size: 18px;
            font-weight: bold;
        }
        .header h3 {
            margin: 5px 0 0 0;
            color: #64748b;
            font-size: 12px;
            font-weight: normal;
        }
        .info-section {
            margin-bottom: 20px;
            background-color: #f8fafc;
            padding: 10px;
            border-radius: 5px;
            border-left: 4px solid #2563eb;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .info-item {
            margin: 3px 0;
        }
        .info-label {
            font-weight: bold;
            color: #374151;
        }
        .info-value {
            color: #6b7280;
        }
        .filters-section {
            margin-bottom: 20px;
            background-color: #fef3c7;
            padding: 10px;
            border-radius: 5px;
            border-left: 4px solid #f59e0b;
        }
        .filters-title {
            font-weight: bold;
            color: #92400e;
            margin-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 9px;
        }
        th {
            background-color: #1e40af;
            color: white;
            padding: 8px 4px;
            text-align: left;
            font-weight: bold;
            font-size: 8px;
        }        td {
            padding: 8px 4px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
            line-height: 1.3;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        tr:hover {
            background-color: #f3f4f6;
        }
        .total-section {
            margin-top: 20px;
            padding: 15px;
            background-color: #ecfdf5;
            border-radius: 5px;
            border-left: 4px solid #10b981;
        }
        .total-label {
            font-weight: bold;
            color: #065f46;
            font-size: 12px;
        }
        .footer {
            margin-top: 30px;
            border-top: 1px solid #d1d5db;
            padding-top: 10px;
            text-align: center;
            color: #6b7280;
            font-size: 8px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: bold;
        }
        .badge-success {
            background-color: #dcfce7;
            color: #166534;
        }
        .badge-warning {
            background-color: #fef3c7;
            color: #92400e;
        }
        .badge-danger {
            background-color: #fee2e2;
            color: #991b1b;
        }        .no-break {
            page-break-inside: avoid;
        }
        .multiple-values {
            font-size: 8px;
            line-height: 1.2;
        }
        .delincuente-item {
            display: block;
            margin-bottom: 2px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $titulo }}</h1>
        <h3>Sistema de Prevención Criminal - {{ $institucion }}</h3>
    </div>

    <div class="info-section">
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Fecha de Generación:</span>
                <span class="info-value">{{ $fecha_generacion }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Generado por:</span>
                <span class="info-value">{{ $usuario }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Total de Registros:</span>
                <span class="info-value">{{ $delitos->count() }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Periodo:</span>
                <span class="info-value">{{ $periodo ?? 'Todos los registros' }}</span>
            </div>
        </div>
    </div>    {{-- Sección de filtros aplicados eliminada a petición del usuario --}}

    <table>
        <thead>
            <tr>
                <th style="width: 6%;">ID</th>
                <th style="width: 15%;">Delincuente</th>
                <th style="width: 10%;">RUT</th>
                <th style="width: 8%;">Código</th>
                <th style="width: 20%;">Descripción</th>
                <th style="width: 12%;">Región</th>
                <th style="width: 12%;">Comuna</th>
                <th style="width: 10%;">Sector</th>
                <th style="width: 10%;">Fecha</th>
                <th style="width: 12%;">Institución</th>
            </tr>
        </thead>
        <tbody>            @forelse($delitos as $delito)
            <tr class="no-break">
                <td class="text-center">{{ $delito->id }}</td>                <td>
                    @php
                        $delincuentesNombres = [];
                        try {
                            if ($delito->delincuentes && $delito->delincuentes->count() > 0) {
                                foreach ($delito->delincuentes as $delincuente) {
                                    $nombre = trim(($delincuente->nombre ?? '') . ' ' . ($delincuente->apellidos ?? ''));
                                    if (!empty($nombre)) {
                                        $delincuentesNombres[] = $nombre;
                                    }
                                }
                            }
                        } catch (\Exception $e) {
                            $delincuentesNombres = ['Error al cargar'];
                        }
                    @endphp
                    
                    @if(count($delincuentesNombres) > 0)
                        @if(count($delincuentesNombres) == 1)
                            {{ $delincuentesNombres[0] }}
                        @else
                            <div class="multiple-values">
                                @foreach($delincuentesNombres as $nombre)
                                    <span class="delincuente-item">{{ $nombre }}</span>
                                @endforeach
                            </div>
                        @endif
                    @else
                        N/A
                    @endif
                </td>
                <td>
                    @php
                        $delincuentesRuts = [];
                        try {
                            if ($delito->delincuentes && $delito->delincuentes->count() > 0) {
                                foreach ($delito->delincuentes as $delincuente) {
                                    $rut = $delincuente->rut ?? '';
                                    if (!empty($rut)) {
                                        $delincuentesRuts[] = $rut;
                                    }
                                }
                            }
                        } catch (\Exception $e) {
                            $delincuentesRuts = ['Error'];
                        }
                    @endphp
                    
                    @if(count($delincuentesRuts) > 0)
                        @if(count($delincuentesRuts) == 1)
                            {{ $delincuentesRuts[0] }}
                        @else
                            <div class="multiple-values">
                                @foreach($delincuentesRuts as $rut)
                                    <span class="delincuente-item">{{ $rut }}</span>
                                @endforeach
                            </div>
                        @endif
                    @else
                        N/A
                    @endif
                </td>
                <td>{{ $delito->codigoDelito->codigo ?? 'N/A' }}</td>
                <td>{{ Str::limit($delito->descripcion, 40) }}</td>
                <td>{{ $delito->region->nombre ?? 'N/A' }}</td>
                <td>{{ $delito->comuna->nombre ?? 'N/A' }}</td>
                <td>{{ $delito->sector->nombre ?? 'N/A' }}</td>
                <td class="text-center">
                    @if($delito->fecha)
                        @if(is_string($delito->fecha))
                            {{ \Carbon\Carbon::parse($delito->fecha)->format('d/m/Y') }}
                        @else
                            {{ $delito->fecha->format('d/m/Y') }}
                        @endif
                    @else
                        N/A
                    @endif
                </td>
                <td>{{ $delito->institucion->nombre ?? 'N/A' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="text-center" style="padding: 20px; color: #6b7280;">
                    No se encontraron registros con los filtros aplicados
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="total-section">
        <div class="total-label">
            Resumen del Reporte
        </div>
        <div style="margin-top: 10px;">
            <div class="info-item">
                <span class="info-label">Total de delitos:</span>
                <span class="info-value">{{ $delitos->count() }}</span>
            </div>            @if($delitos->count() > 0)
            <div class="info-item">
                <span class="info-label">Primer delito:</span>
                <span class="info-value">
                    @php
                        $fechas = $delitos->pluck('fecha')->filter()->map(function($fecha) {
                            return is_string($fecha) ? \Carbon\Carbon::parse($fecha) : $fecha;
                        });
                        $primeraFecha = $fechas->min();
                    @endphp
                    {{ $primeraFecha ? $primeraFecha->format('d/m/Y') : 'N/A' }}
                </span>
            </div>
            <div class="info-item">
                <span class="info-label">Último delito:</span>
                <span class="info-value">
                    @php
                        $ultimaFecha = $fechas->max();
                    @endphp
                    {{ $ultimaFecha ? $ultimaFecha->format('d/m/Y') : 'N/A' }}
                </span>
            </div>
            @endif
        </div>
    </div>

    <div class="footer">
        <p>Reporte generado el {{ $fecha_generacion }} por el Sistema de Prevención Criminal</p>
        <p>Este documento contiene información confidencial y está destinado únicamente para uso oficial</p>
    </div>
</body>
</html>
