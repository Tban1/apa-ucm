<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informe de Jefatura — {{ $nomina->academico->name }}</title>
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

        .puntaje-box {
            display: inline-block;
            background: #f0f4ff;
            border: 1px solid #1B2D6B;
            border-radius: 6px;
            padding: 8px 20px;
            text-align: center;
            margin-bottom: 8px;
        }

        .puntaje-box .pts {
            font-size: 28px;
            font-weight: bold;
            color: #1B2D6B;
        }

        .puntaje-box .label {
            font-size: 11px;
            color: #555;
            margin-top: 2px;
        }

        .obs-row {
            margin-bottom: 10px;
        }

        .obs-row .cat-name {
            font-weight: bold;
            color: #333;
            margin-bottom: 2px;
        }

        .obs-row .cat-text {
            color: #555;
            padding-left: 8px;
            border-left: 2px solid #ddd;
            line-height: 1.5;
        }

        .obs-row .empty {
            color: #aaa;
            font-style: italic;
            padding-left: 8px;
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
        <h1>Informe de Calificación de Jefatura Académica</h1>
        <p>Universidad Católica del Maule — Vicerrectoría Académica</p>
    </div>
    <div class="header-meta">
        <p><strong>Período:</strong> {{ $periodo->nombre }} {{ $periodo->anio }}</p>
        <p><strong>Fecha:</strong> {{ now()->format('d/m/Y') }}</p>
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
    </div>
</div>

{{-- Puntaje --}}
<div class="section">
    <p class="section-title">Calificación de Jefatura</p>
    <div class="puntaje-box">
        <div class="pts">{{ $informe->puntaje }} / 100</div>
        <div class="label">{{ $informe->calificacionLabel() }}</div>
    </div>
</div>

{{-- Observaciones por categoría --}}
<div class="section">
    <p class="section-title">Observaciones por Área</p>
    @foreach ($categorias as $categoria)
        <div class="obs-row">
            <p class="cat-name">{{ $categoria->nombre }}</p>
            @php $obs = $observaciones[$categoria->slug] ?? ''; @endphp
            @if ($obs)
                <p class="cat-text">{{ $obs }}</p>
            @else
                <p class="empty">Sin observaciones.</p>
            @endif
        </div>
    @endforeach
</div>

{{-- Observación general --}}
@if (!empty($observaciones['observacion_general']))
<div class="section">
    <p class="section-title">Observación General</p>
    <p style="line-height:1.6; color:#333;">{{ $observaciones['observacion_general'] }}</p>
</div>
@endif

{{-- Firma --}}
<div class="firma-section">
    <div class="firma-box">
        <div class="firma-line"></div>
        <p class="firma-label">Jefe de Departamento / Carrera</p>
    </div>
    <div class="firma-box">
        <div class="firma-line"></div>
        <p class="firma-label">Timbre y Fecha</p>
    </div>
</div>

</body>
</html>
