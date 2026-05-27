<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cronograma — {{ $periodo->nombre }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #111;
            background: #fff;
            padding: 30px 40px;
        }

        .btn-print {
            display: inline-block;
            background: #1B2D6B;
            color: white;
            padding: 8px 20px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-size: 13px;
            margin-bottom: 24px;
        }

        .header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            border-bottom: 3px solid #1B2D6B;
            padding-bottom: 14px;
            margin-bottom: 24px;
        }

        .header-title h1 {
            font-size: 17px;
            font-weight: bold;
            color: #1B2D6B;
        }

        .header-title p {
            font-size: 11px;
            color: #555;
            margin-top: 3px;
        }

        .header-meta {
            text-align: right;
            font-size: 11px;
            color: #555;
            line-height: 1.6;
        }

        .periodo-info {
            background: #f0f4ff;
            border-left: 4px solid #1B2D6B;
            padding: 10px 14px;
            margin-bottom: 24px;
            border-radius: 0 6px 6px 0;
        }

        .periodo-info h2 {
            font-size: 14px;
            font-weight: bold;
            color: #1B2D6B;
        }

        .periodo-info p {
            font-size: 11px;
            color: #444;
            margin-top: 2px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead tr {
            background: #1B2D6B;
            color: white;
        }

        thead th {
            padding: 10px 14px;
            text-align: left;
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        tbody tr {
            border-bottom: 1px solid #e5e7eb;
        }

        tbody tr:last-child {
            border-bottom: none;
        }

        tbody tr:nth-child(even) {
            background: #f9fafb;
        }

        tbody td {
            padding: 11px 14px;
            font-size: 12px;
            color: #333;
        }

        .n-col {
            width: 36px;
            color: #9ca3af;
            font-weight: bold;
        }

        .etapa-col {
            font-weight: 600;
            color: #1B2D6B;
        }

        .estado-badge {
            display: inline-block;
            font-size: 10px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 99px;
        }

        .badge-vigente   { background: #dcfce7; color: #166534; }
        .badge-terminado { background: #f3f4f6; color: #6b7280; }
        .badge-pendiente { background: #fef9c3; color: #854d0e; }

        .footer {
            margin-top: 32px;
            border-top: 1px solid #e5e7eb;
            padding-top: 12px;
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            color: #9ca3af;
        }

        @media print {
            .btn-print { display: none; }
            body { padding: 15px 20px; }
        }
    </style>
</head>
<body>

<button class="btn-print" onclick="window.print()">Imprimir / Guardar como PDF</button>

<div class="header">
    <div class="header-title">
        <h1>Cronograma del Proceso APA</h1>
        <p>Universidad Católica del Maule — Vicerrectoría Académica</p>
    </div>
    <div class="header-meta">
        <p><strong>Generado:</strong> {{ now()->format('d/m/Y H:i') }}</p>
    </div>
</div>

<div class="periodo-info">
    <h2>{{ $periodo->nombre }}</h2>
    <p>
        Período {{ $periodo->anio }} &nbsp;·&nbsp;
        Inicio: {{ \Carbon\Carbon::parse($periodo->fecha_inicio)->format('d/m/Y') }}
        &nbsp;·&nbsp;
        Cierre: {{ \Carbon\Carbon::parse($periodo->fecha_cierre)->format('d/m/Y') }}
    </p>
</div>

<table>
    <thead>
        <tr>
            <th class="n-col">#</th>
            <th>Etapa</th>
            <th>Fecha de Inicio</th>
            <th>Fecha de Cierre</th>
            <th>Estado</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($cronogramas as $i => $etapa)
        <tr>
            <td class="n-col">{{ $i + 1 }}</td>
            <td class="etapa-col">{{ $etapa['etapa'] }}</td>
            <td>{{ $etapa['fecha_inicio'] }}</td>
            <td>{{ $etapa['fecha_fin'] }}</td>
            <td>
                @if ($etapa['vigente'])
                    <span class="estado-badge badge-vigente">Vigente</span>
                @elseif ($etapa['terminado'])
                    <span class="estado-badge badge-terminado">Finalizada</span>
                @else
                    <span class="estado-badge badge-pendiente">Pendiente</span>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="footer">
    <span>Sistema de Gestión de Calificaciones Académicas · UCM</span>
    <span>{{ now()->format('d/m/Y') }}</span>
</div>

</body>
</html>
