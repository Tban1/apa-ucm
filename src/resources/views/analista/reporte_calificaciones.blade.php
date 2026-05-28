<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Consolidado de Calificaciones — {{ $periodo->nombre }} {{ $periodo->anio }}</title>
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
            border-bottom: 2px solid #1B2D6B;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }

        .header-title h1 {
            font-size: 15px;
            font-weight: bold;
            color: #1B2D6B;
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

        .section {
            margin-bottom: 22px;
        }

        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #1B2D6B;
            border-bottom: 1px solid #ddd;
            padding-bottom: 4px;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .resumen-row {
            display: flex;
            gap: 12px;
            margin-bottom: 10px;
            flex-wrap: wrap;
        }

        .resumen-item {
            background: #f4f6fb;
            border: 1px solid #dde2f0;
            border-radius: 5px;
            padding: 5px 12px;
            text-align: center;
        }

        .resumen-item .val {
            font-size: 16px;
            font-weight: bold;
            color: #1B2D6B;
        }

        .resumen-item .lbl {
            font-size: 9px;
            color: #555;
        }

        .table-reporte {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }

        .table-reporte th {
            background: #e8ecf5;
            border: 1px solid #ccc;
            padding: 5px 7px;
            text-align: left;
            color: #1B2D6B;
            font-weight: bold;
        }

        .table-reporte td {
            border: 1px solid #e5e5e5;
            padding: 4px 7px;
            color: #333;
        }

        .table-reporte tr:nth-child(even) td {
            background: #fafbff;
        }

        .badge {
            display: inline-block;
            padding: 1px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }

        .badge-muy-bueno  { background: #d1fae5; color: #065f46; }
        .badge-bueno      { background: #dbeafe; color: #1e40af; }
        .badge-aceptable  { background: #fef9c3; color: #854d0e; }
        .badge-deficiente { background: #fee2e2; color: #991b1b; }
        .badge-sin-calif  { background: #f3f4f6; color: #6b7280; }

        .resumen-global {
            background: #f0f4ff;
            border: 1px solid #1B2D6B;
            border-radius: 6px;
            padding: 10px 16px;
            margin-bottom: 18px;
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            align-items: center;
        }

        .resumen-global .item {
            text-align: center;
        }

        .resumen-global .item .v {
            font-size: 18px;
            font-weight: bold;
            color: #1B2D6B;
        }

        .resumen-global .item .l {
            font-size: 9px;
            color: #555;
        }

        .page-break { page-break-before: always; }

        .btn-print {
            display: inline-block;
            background: #1B2D6B;
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
        <h1>Reporte Consolidado de Calificaciones Académicas</h1>
        <p>Universidad Católica del Maule — Comisión Calificadora Académica Docente (CCDA)</p>
    </div>
    <div class="header-meta">
        <p><strong>Período:</strong> {{ $periodo->nombre }} {{ $periodo->anio }}</p>
        <p><strong>Fecha de emisión:</strong> {{ now()->format('d/m/Y') }}</p>
        <p><strong>Estado del período:</strong> {{ $periodo->estado === 'activo' ? 'Activo' : 'Cerrado' }}</p>
    </div>
</div>

{{-- Resumen global --}}
@php
    $totalGlobal     = $facultades->sum(fn($f) => $f['resumen']['total']);
    $conCalifGlobal  = $facultades->sum(fn($f) => $f['resumen']['con_calif']);
    $muBuenoGlobal   = $facultades->sum(fn($f) => $f['resumen']['muy_bueno']);
    $buenoGlobal     = $facultades->sum(fn($f) => $f['resumen']['bueno']);
    $aceptableGlobal = $facultades->sum(fn($f) => $f['resumen']['aceptable']);
    $deficGlobal     = $facultades->sum(fn($f) => $f['resumen']['deficiente']);
@endphp

<div class="resumen-global">
    <div class="item"><div class="v">{{ $totalGlobal }}</div><div class="l">Total</div></div>
    <div class="item"><div class="v">{{ $conCalifGlobal }}</div><div class="l">Con calificación</div></div>
    <div class="item"><div class="v">{{ $muBuenoGlobal }}</div><div class="l">Muy Bueno</div></div>
    <div class="item"><div class="v">{{ $buenoGlobal }}</div><div class="l">Bueno</div></div>
    <div class="item"><div class="v">{{ $aceptableGlobal }}</div><div class="l">Aceptable</div></div>
    <div class="item"><div class="v">{{ $deficGlobal }}</div><div class="l">Deficiente</div></div>
    <div class="item"><div class="v">{{ $totalGlobal - $conCalifGlobal }}</div><div class="l">Sin calificación</div></div>
</div>

{{-- Por facultad --}}
@foreach ($facultades as $i => $f)
    @if ($i > 0 && $f['academicos']->count() > 15)
        <div class="page-break"></div>
    @endif

    <div class="section">
        <p class="section-title">{{ $f['nombre'] }}</p>

        {{-- Mini resumen --}}
        <div class="resumen-row">
            <div class="resumen-item">
                <div class="val">{{ $f['resumen']['total'] }}</div>
                <div class="lbl">Total</div>
            </div>
            <div class="resumen-item">
                <div class="val">{{ $f['resumen']['muy_bueno'] }}</div>
                <div class="lbl">Muy Bueno</div>
            </div>
            <div class="resumen-item">
                <div class="val">{{ $f['resumen']['bueno'] }}</div>
                <div class="lbl">Bueno</div>
            </div>
            <div class="resumen-item">
                <div class="val">{{ $f['resumen']['aceptable'] }}</div>
                <div class="lbl">Aceptable</div>
            </div>
            <div class="resumen-item">
                <div class="val">{{ $f['resumen']['deficiente'] }}</div>
                <div class="lbl">Deficiente</div>
            </div>
            <div class="resumen-item">
                <div class="val">{{ $f['resumen']['total'] - $f['resumen']['con_calif'] }}</div>
                <div class="lbl">Sin calificación</div>
            </div>
        </div>

        <table class="table-reporte">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Académico</th>
                    <th>RUT</th>
                    <th>Departamento</th>
                    <th>Calificación</th>
                    <th>Puntaje</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($f['academicos'] as $j => $a)
                @php
                    $badgeClass = match($a['calificacion'] ?? '') {
                        'muy_bueno'  => 'badge-muy-bueno',
                        'bueno'      => 'badge-bueno',
                        'aceptable'  => 'badge-aceptable',
                        'deficiente' => 'badge-deficiente',
                        default      => 'badge-sin-calif',
                    };
                    $labelCalif = match($a['calificacion'] ?? '') {
                        'muy_bueno'  => 'Muy Bueno',
                        'bueno'      => 'Bueno',
                        'aceptable'  => 'Aceptable',
                        'deficiente' => 'Deficiente',
                        default      => 'Sin calificación',
                    };
                @endphp
                <tr>
                    <td>{{ $j + 1 }}</td>
                    <td>{{ $a['nombre'] }}</td>
                    <td>{{ $a['rut'] ?? '—' }}</td>
                    <td>{{ $a['departamento'] ?? '—' }}</td>
                    <td>
                        <span class="badge {{ $badgeClass }}">{{ $labelCalif }}</span>
                        @if ($a['es_apelacion'])
                            <span style="font-size:8px;color:#92400e;"> (apel.)</span>
                        @endif
                    </td>
                    <td>{{ $a['puntaje'] !== null ? $a['puntaje'].' pts' : '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endforeach

@if ($facultades->isEmpty())
    <p style="color:#999;text-align:center;padding:30px 0;">No hay datos de calificaciones para este período.</p>
@endif

</body>
</html>
