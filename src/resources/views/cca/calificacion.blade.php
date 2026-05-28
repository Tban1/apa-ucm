<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informe de Calificación CCA — {{ $nomina->academico->name }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #111;
            background: #fff;
            padding: 30px 40px;
        }

        .header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            border-bottom: 2px solid #1B2D6B;
            padding-bottom: 14px;
            margin-bottom: 20px;
        }

        .header-title h1 {
            font-size: 16px;
            font-weight: bold;
            color: #1B2D6B;
        }

        .header-title p {
            font-size: 11px;
            color: #555;
            margin-top: 2px;
        }

        .header-meta {
            text-align: right;
            font-size: 11px;
            color: #555;
        }

        .section {
            margin-bottom: 18px;
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

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px 20px;
        }

        .info-row {
            display: flex;
            gap: 6px;
        }

        .info-label {
            font-weight: bold;
            color: #444;
            min-width: 100px;
        }

        .calificacion-box {
            display: inline-block;
            background: #f0f4ff;
            border: 1px solid #1B2D6B;
            border-radius: 6px;
            padding: 12px 30px;
            text-align: center;
            margin-bottom: 10px;
        }

        .calificacion-box .calificacion-label {
            font-size: 22px;
            font-weight: bold;
            color: #1B2D6B;
        }

        .calificacion-box .calificacion-pts {
            font-size: 13px;
            color: #555;
            margin-top: 4px;
        }

        .table-eval {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        .table-eval th {
            background: #f4f6fb;
            border: 1px solid #ddd;
            padding: 5px 8px;
            text-align: left;
            color: #333;
            font-weight: bold;
        }

        .table-eval td {
            border: 1px solid #eee;
            padding: 5px 8px;
            color: #444;
        }

        .table-eval tr:nth-child(even) td {
            background: #fafafa;
        }

        .observacion-box {
            background: #f9f9f9;
            border-left: 3px solid #1B2D6B;
            padding: 8px 12px;
            color: #333;
            line-height: 1.6;
        }

        .badge-apelacion {
            display: inline-block;
            background: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
            border-radius: 4px;
            padding: 2px 8px;
            font-size: 10px;
            font-weight: bold;
            margin-left: 8px;
        }

        .firma-section {
            margin-top: 50px;
            display: flex;
            gap: 60px;
        }

        .firma-box {
            flex: 1;
            text-align: center;
        }

        .firma-line {
            border-top: 1px solid #555;
            margin-bottom: 4px;
        }

        .firma-label {
            font-size: 10px;
            color: #555;
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
        <h1>
            Informe de Calificación Académica — CCA
            @if ($calificacion->es_apelacion)
                <span class="badge-apelacion">Apelación</span>
            @endif
        </h1>
        <p>Universidad Católica del Maule — Comisión Calificadora Académica</p>
    </div>
    <div class="header-meta">
        <p><strong>Período:</strong> {{ $periodo->nombre }} {{ $periodo->anio }}</p>
        <p><strong>Fecha emisión:</strong> {{ now()->format('d/m/Y') }}</p>
    </div>
</div>

{{-- Datos del académico --}}
<div class="section">
    <p class="section-title">Datos del Académico</p>
    <div class="info-grid">
        <div class="info-row">
            <span class="info-label">Nombre:</span>
            <span>{{ $nomina->academico->name }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">RUT:</span>
            <span>{{ $nomina->academico->rut }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Correo:</span>
            <span>{{ $nomina->academico->email }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Departamento:</span>
            <span>{{ $nomina->academico->departamento?->nombre ?? '—' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Facultad:</span>
            <span>{{ $nomina->academico->facultad?->nombre ?? '—' }}</span>
        </div>
    </div>
</div>

{{-- Calificación final --}}
<div class="section">
    <p class="section-title">Calificación Final</p>
    <div class="calificacion-box">
        <div class="calificacion-label">{{ $calificacion->calificacionLabel() }}</div>
        <div class="calificacion-pts">{{ $calificacion->puntaje_total }} / 100 puntos</div>
    </div>
    <div style="margin-top: 6px; font-size: 11px; color: #555;">
        <strong>Fecha de calificación:</strong> {{ $calificacion->fecha->format('d/m/Y') }}
        &nbsp;·&nbsp;
        <strong>Determinada por:</strong> {{ $calificacion->determinadaPor->name }}
    </div>
</div>

{{-- Evaluaciones individuales --}}
@if ($evaluaciones->count() > 0)
<div class="section">
    <p class="section-title">Evaluaciones Individuales de la CCA</p>
    <table class="table-eval">
        <thead>
            <tr>
                <th>Evaluador</th>
                <th>Docencia</th>
                <th>Investigación</th>
                <th>Vinculación</th>
                <th>Gestión</th>
                <th>Formación</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($evaluaciones as $ev)
            <tr>
                <td>{{ $ev->evaluador->name }}</td>
                <td>{{ $ev->puntaje_docencia }}</td>
                <td>{{ $ev->puntaje_investigacion }}</td>
                <td>{{ $ev->puntaje_vinculacion }}</td>
                <td>{{ $ev->puntaje_gestion }}</td>
                <td>{{ $ev->puntaje_formacion }}</td>
                <td><strong>{{ $ev->puntajeTotal() }}</strong></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- Observación de la CCA --}}
@if ($calificacion->observacion)
<div class="section">
    <p class="section-title">Observación de la CCA</p>
    <div class="observacion-box">{{ $calificacion->observacion }}</div>
</div>
@endif

{{-- Firma --}}
<div class="firma-section">
    <div class="firma-box">
        <div class="firma-line"></div>
        <p class="firma-label">Presidente de la Comisión Calificadora Académica</p>
    </div>
    <div class="firma-box">
        <div class="firma-line"></div>
        <p class="firma-label">Académico Evaluado — Recepción conforme</p>
    </div>
    <div class="firma-box">
        <div class="firma-line"></div>
        <p class="firma-label">Timbre y Fecha</p>
    </div>
</div>

</body>
</html>
