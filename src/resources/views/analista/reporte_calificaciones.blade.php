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
            font-size: 10px;
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

        .filtro-form {
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .filtro-form label { font-size: 11px; color: #555; }
        .filtro-form select {
            font-size: 11px;
            padding: 5px 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .section { margin-bottom: 22px; }

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
            gap: 10px;
            margin-bottom: 10px;
            flex-wrap: wrap;
        }

        .resumen-item {
            background: #f4f6fb;
            border: 1px solid #dde2f0;
            border-radius: 5px;
            padding: 5px 10px;
            text-align: center;
        }

        .resumen-item .val {
            font-size: 14px;
            font-weight: bold;
            color: #1B2D6B;
        }

        .resumen-item .lbl {
            font-size: 8px;
            color: #555;
        }

        .table-reporte {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5px;
        }

        .table-reporte th {
            background: #e8ecf5;
            border: 1px solid #ccc;
            padding: 4px 5px;
            text-align: center;
            color: #1B2D6B;
            font-weight: bold;
            vertical-align: bottom;
        }

        .table-reporte td {
            border: 1px solid #e5e5e5;
            padding: 3px 5px;
            color: #333;
            text-align: center;
        }

        .table-reporte td.text-left { text-align: left; }

        .table-reporte tr:nth-child(even) td {
            background: #fafbff;
        }

        .table-reporte tr.licencia td {
            background: #fffbeb;
        }

        .badge {
            display: inline-block;
            padding: 1px 5px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }

        .badge-excelente  { background: #d1fae5; color: #065f46; }
        .badge-muy-bueno  { background: #dbeafe; color: #1e40af; }
        .badge-bueno      { background: #e0e7ff; color: #3730a3; }
        .badge-regular    { background: #fef9c3; color: #854d0e; }
        .badge-deficiente { background: #fee2e2; color: #991b1b; }
        .badge-sin-calif  { background: #f3f4f6; color: #6b7280; }
        .badge-licencia   { background: #fef3c7; color: #92400e; }

        .resumen-global {
            background: #f0f4ff;
            border: 1px solid #1B2D6B;
            border-radius: 6px;
            padding: 10px 16px;
            margin-bottom: 18px;
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            align-items: center;
        }

        .resumen-global .item { text-align: center; }
        .resumen-global .item .v { font-size: 16px; font-weight: bold; color: #1B2D6B; }
        .resumen-global .item .l { font-size: 8px; color: #555; }

        .page-break { page-break-before: always; }

        .btn-print, .btn-filter {
            display: inline-block;
            background: #1B2D6B;
            color: white;
            padding: 6px 14px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-size: 11px;
            text-decoration: none;
        }

        .btn-filter { background: #0096D6; }

        @media print {
            .no-print { display: none; }
            body { padding: 10px 14px; }
            .table-reporte { font-size: 7.5px; }
        }
    </style>
</head>
<body>

<div class="no-print" style="margin-bottom: 16px;">
    <button class="btn-print" onclick="window.print()">Imprimir / Guardar como PDF</button>
</div>

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

<form method="GET" class="filtro-form no-print">
    <label for="facultad_id"><strong>Filtrar por facultad:</strong></label>
    <select name="facultad_id" id="facultad_id">
        <option value="">Todas las facultades</option>
        @foreach ($todasFacultades as $f)
            <option value="{{ $f->id }}" @selected($facultadFiltro == $f->id)>{{ $f->nombre }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn-filter">Aplicar filtro</button>
    @if ($facultadFiltro)
        <a href="{{ route('analista.reporte-calificaciones') }}" style="font-size:11px;color:#555;">Quitar filtro</a>
    @endif
</form>

@php
    $totalGlobal    = $facultades->sum(fn($f) => $f['resumen']['total']);
    $conCalifGlobal = $facultades->sum(fn($f) => $f['resumen']['con_calif']);
    $excelenteGlobal = $facultades->sum(fn($f) => $f['resumen']['excelente']);
    $muBuenoGlobal   = $facultades->sum(fn($f) => $f['resumen']['muy_bueno']);
    $buenoGlobal     = $facultades->sum(fn($f) => $f['resumen']['bueno']);
    $regularGlobal   = $facultades->sum(fn($f) => $f['resumen']['regular']);
    $deficGlobal     = $facultades->sum(fn($f) => $f['resumen']['deficiente']);
@endphp

<div class="resumen-global">
    <div class="item"><div class="v">{{ $totalGlobal }}</div><div class="l">Total académicos</div></div>
    <div class="item"><div class="v">{{ $conCalifGlobal }}</div><div class="l">Evaluados</div></div>
    <div class="item"><div class="v">{{ $excelenteGlobal }}</div><div class="l">Excelente</div></div>
    <div class="item"><div class="v">{{ $muBuenoGlobal }}</div><div class="l">Muy Bueno</div></div>
    <div class="item"><div class="v">{{ $buenoGlobal }}</div><div class="l">Bueno</div></div>
    <div class="item"><div class="v">{{ $regularGlobal }}</div><div class="l">Regular</div></div>
    <div class="item"><div class="v">{{ $deficGlobal }}</div><div class="l">Deficiente</div></div>
    <div class="item"><div class="v">{{ $totalGlobal - $conCalifGlobal }}</div><div class="l">Sin evaluar</div></div>
</div>

@foreach ($facultades as $i => $f)
    @if ($i > 0)
        <div class="page-break"></div>
    @endif

    <div class="section">
        <p class="section-title">{{ $f['nombre'] }}</p>

        <div class="resumen-row">
            <div class="resumen-item"><div class="val">{{ $f['resumen']['total'] }}</div><div class="lbl">Total</div></div>
            <div class="resumen-item"><div class="val">{{ $f['resumen']['con_calif'] }}</div><div class="lbl">Evaluados</div></div>
            <div class="resumen-item"><div class="val">{{ $f['resumen']['excelente'] }}</div><div class="lbl">Excelente</div></div>
            <div class="resumen-item"><div class="val">{{ $f['resumen']['muy_bueno'] }}</div><div class="lbl">Muy Bueno</div></div>
            <div class="resumen-item"><div class="val">{{ $f['resumen']['bueno'] }}</div><div class="lbl">Bueno</div></div>
            <div class="resumen-item"><div class="val">{{ $f['resumen']['regular'] }}</div><div class="lbl">Regular</div></div>
            <div class="resumen-item"><div class="val">{{ $f['resumen']['deficiente'] }}</div><div class="lbl">Deficiente</div></div>
        </div>

        <table class="table-reporte">
            <thead>
                <tr>
                    <th rowspan="2">#</th>
                    <th rowspan="2">Académico</th>
                    <th rowspan="2">RUT</th>
                    <th rowspan="2">Categoría</th>
                    <th colspan="2">Calificación anterior</th>
                    <th colspan="5">Nota por área APA</th>
                    <th colspan="2">Calificación final</th>
                    <th rowspan="2">Estado</th>
                </tr>
                <tr>
                    <th>Nota</th>
                    <th>Concepto</th>
                    <th>Doc.</th>
                    <th>Inv.</th>
                    <th>Ext.</th>
                    <th>Adm.</th>
                    <th>Otras</th>
                    <th>Nota</th>
                    <th>Concepto</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($f['academicos'] as $j => $a)
                @php
                    $badgeClass = match($a['concepto_final'] ?? '') {
                        'Excelente'  => 'badge-excelente',
                        'Muy Bueno'  => 'badge-muy-bueno',
                        'Bueno'      => 'badge-bueno',
                        'Regular'    => 'badge-regular',
                        'Deficiente' => 'badge-deficiente',
                        default      => 'badge-sin-calif',
                    };
                @endphp
                <tr @class(['licencia' => $a['tiene_licencia']])>
                    <td>{{ $j + 1 }}</td>
                    <td class="text-left">{{ $a['nombre'] }}</td>
                    <td>{{ $a['rut'] ?? '—' }}</td>
                    <td>{{ $a['categoria'] ?? '—' }}</td>
                    <td>{{ $a['nota_anterior'] !== null ? number_format($a['nota_anterior'], 1) : '—' }}</td>
                    <td>{{ $a['concepto_anterior'] ?? '—' }}</td>
                    <td>{{ $a['nota_docencia'] !== null ? number_format($a['nota_docencia'], 1) : '—' }}</td>
                    <td>{{ $a['nota_investigacion'] !== null ? number_format($a['nota_investigacion'], 1) : '—' }}</td>
                    <td>{{ $a['nota_vinculacion'] !== null ? number_format($a['nota_vinculacion'], 1) : '—' }}</td>
                    <td>{{ $a['nota_gestion'] !== null ? number_format($a['nota_gestion'], 1) : '—' }}</td>
                    <td>{{ $a['nota_formacion'] !== null ? number_format($a['nota_formacion'], 1) : '—' }}</td>
                    <td>{{ $a['nota_final'] !== null ? number_format($a['nota_final'], 2) : '—' }}</td>
                    <td>
                        @if ($a['concepto_final'])
                            <span class="badge {{ $badgeClass }}">{{ $a['concepto_final'] }}</span>
                        @else
                            —
                        @endif
                    </td>
                    <td class="text-left">
                        @if ($a['tiene_licencia'])
                            <span class="badge badge-licencia">{{ $a['estado_reporte'] }}</span>
                        @else
                            {{ $a['estado_reporte'] }}
                        @endif
                    </td>
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
