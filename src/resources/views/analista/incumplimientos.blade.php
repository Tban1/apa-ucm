<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Académicos con Incumplimientos — {{ $periodo->nombre }} {{ $periodo->anio }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #111;
            background: #fff;
            padding: 25px 35px;
        }

        .header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            border-bottom: 2px solid #9b1c1c;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }

        .header-title h1 {
            font-size: 15px;
            font-weight: bold;
            color: #9b1c1c;
        }

        .header-title p {
            font-size: 10px;
            color: #555;
            margin-top: 2px;
        }

        .header-meta {
            text-align: right;
            font-size: 10px;
            color: #555;
        }

        .alerta-box {
            background: #fff5f5;
            border: 1px solid #fca5a5;
            border-radius: 6px;
            padding: 10px 14px;
            margin-bottom: 16px;
            font-size: 10px;
            color: #7f1d1d;
            line-height: 1.6;
        }

        .section {
            margin-bottom: 22px;
        }

        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #9b1c1c;
            border-bottom: 1px solid #fca5a5;
            padding-bottom: 4px;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table-inc {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }

        .table-inc th {
            background: #fef2f2;
            border: 1px solid #fca5a5;
            padding: 5px 7px;
            text-align: left;
            color: #7f1d1d;
            font-weight: bold;
        }

        .table-inc td {
            border: 1px solid #fee2e2;
            padding: 4px 7px;
            color: #333;
        }

        .table-inc tr:nth-child(even) td {
            background: #fff8f8;
        }

        .badge-estado {
            display: inline-block;
            padding: 1px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }

        .badge-pendiente { background: #f3f4f6; color: #374151; }
        .badge-en_carga  { background: #dbeafe; color: #1e40af; }

        .sin-datos {
            text-align: center;
            color: #999;
            padding: 20px 0;
            font-style: italic;
        }

        .resumen-global {
            display: flex;
            gap: 15px;
            margin-bottom: 16px;
        }

        .resumen-item {
            background: #fff5f5;
            border: 1px solid #fca5a5;
            border-radius: 5px;
            padding: 5px 14px;
            text-align: center;
        }

        .resumen-item .val {
            font-size: 18px;
            font-weight: bold;
            color: #9b1c1c;
        }

        .resumen-item .lbl {
            font-size: 9px;
            color: #555;
        }

        .nota-exclusion {
            font-size: 9px;
            color: #666;
            font-style: italic;
            margin-top: 6px;
        }

        .btn-print {
            display: inline-block;
            background: #9b1c1c;
            color: white;
            padding: 7px 18px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-size: 12px;
            margin-bottom: 20px;
        }

        @media print {
            .btn-print { display: none; }
            body { padding: 12px 18px; }
        }
    </style>
</head>
<body>

<button class="btn-print" onclick="window.print()">Imprimir / Guardar como PDF</button>

<div class="header">
    <div class="header-title">
        <h1>Listado de Académicos con Incumplimientos</h1>
        <p>Universidad Católica del Maule — Comisión Calificadora Académica Docente (CCDA)</p>
    </div>
    <div class="header-meta">
        <p><strong>Período:</strong> {{ $periodo->nombre }} {{ $periodo->anio }}</p>
        <p><strong>Fecha de emisión:</strong> {{ now()->format('d/m/Y') }}</p>
    </div>
</div>

<div class="alerta-box">
    <strong>Criterio de incumplimiento:</strong> Académicos cuya recepción de evidencias fue cerrada formalmente por el secretario de facultad
    pero que no completaron el proceso (expediente no evaluado ni cerrado). Se excluyen los casos especiales con licencia médica.
</div>

@php
    $totalIncump = $facultades->sum(fn($f) => $f['academicos']->count());
@endphp

@if ($totalIncump > 0)
<div class="resumen-global">
    <div class="resumen-item">
        <div class="val">{{ $totalIncump }}</div>
        <div class="lbl">Total con incumplimiento</div>
    </div>
    <div class="resumen-item">
        <div class="val">{{ $facultades->count() }}</div>
        <div class="lbl">Facultades afectadas</div>
    </div>
</div>
@endif

@if ($facultades->isEmpty())
    <p class="sin-datos">No se registran incumplimientos para el período {{ $periodo->nombre }} {{ $periodo->anio }}.</p>
@else
    @foreach ($facultades as $f)
    <div class="section">
        <p class="section-title">{{ $f['nombre'] }} — {{ $f['academicos']->count() }} incumplimiento(s)</p>

        <table class="table-inc">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Académico</th>
                    <th>RUT</th>
                    <th>Departamento</th>
                    <th>Estado al cierre</th>
                    <th>Evidencias subidas</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($f['academicos'] as $j => $a)
                @php
                    $estadoLabel = match($a['estado']) {
                        'pendiente' => 'Pendiente',
                        'en_carga'  => 'En revisión',
                        'carga_cerrada' => 'Completo (sin evaluar)',
                        'en_evaluacion' => 'En evaluación',
                        'apelado'   => 'Apelado',
                        default     => $a['estado'],
                    };
                    $badgeClass = match($a['estado']) {
                        'pendiente' => 'badge-pendiente',
                        'en_carga'  => 'badge-en_carga',
                        default     => 'badge-pendiente',
                    };
                @endphp
                <tr>
                    <td>{{ $j + 1 }}</td>
                    <td>{{ $a['nombre'] }}</td>
                    <td>{{ $a['rut'] ?? '—' }}</td>
                    <td>{{ $a['departamento'] ?? '—' }}</td>
                    <td><span class="badge-estado {{ $badgeClass }}">{{ $estadoLabel }}</span></td>
                    <td>{{ $a['evidencias'] }} archivo(s)</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <p class="nota-exclusion">* Se excluyen académicos con licencia médica activa.</p>
    </div>
    @endforeach
@endif

</body>
</html>
