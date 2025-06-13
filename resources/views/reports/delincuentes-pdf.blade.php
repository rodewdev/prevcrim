<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $titulo ?? 'Reporte de Delincuentes' }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 4px 6px; }
        th { background: #f0f0f0; }
        .section-title { font-size: 16px; font-weight: bold; margin-top: 20px; }
    </style>
</head>
<body>
    <h2>{{ $titulo ?? 'Reporte de Delincuentes' }}</h2>
    <p>Generado por: <strong>{{ $usuario }}</strong> | Institución: <strong>{{ $institucion }}</strong> | Fecha: <strong>{{ $fecha_generacion }}</strong></p>
    @if(!empty($filtros_aplicados))
        <p><strong>Filtros aplicados:</strong> {{ implode(' | ', array_map(fn($k, $v) => "$k: $v", array_keys($filtros_aplicados), $filtros_aplicados)) }}</p>
    @endif
    <table>
        <thead>
            <tr>
                <th>RUT</th>
                <th>Nombre</th>
                <th>Apellidos</th>
                <th>Alias</th>
                <th>Comuna de Residencia</th>
                <th>Último Lugar Visto</th>
                <th>Estado</th>
                <th>Delitos</th>
                <th>Familiares</th>
            </tr>
        </thead>
        <tbody>
        @foreach($delincuentes as $d)
            <tr>
                <td>{{ $d->rut }}</td>
                <td>{{ $d->nombre }}</td>
                <td>{{ $d->apellidos }}</td>
                <td>{{ $d->alias }}</td>
                <td>{{ $d->comuna->nombre ?? '' }}</td>
                <td>{{ $d->ultimo_lugar_visto }}</td>
                <td>{{ $d->estado }}</td>
                <td>
                    @if($d->delitos && $d->delitos->count())
                        @foreach($d->delitos as $delito)
                            {{ $delito->codigoDelito->codigo ?? '' }}<br>
                        @endforeach
                    @else
                        -
                    @endif
                </td>
                <td>
                    @if($d->familiares && $d->familiares->count())
                        @foreach($d->familiares as $f)
                            {{ $f->nombre }} ({{ $f->pivot->parentesco }})<br>
                        @endforeach
                    @else
                        -
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>
