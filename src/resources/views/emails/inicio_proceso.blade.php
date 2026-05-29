<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inicio Proceso CAD</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333; line-height: 1.5;">
    <div style="max-width: 600px; margin: 0 auto; padding: 24px;">
        <h1 style="color: #1B2D6B; font-size: 20px;">Universidad Católica del Maule</h1>
        <h2 style="color: #0096D6; font-size: 16px;">Inicio del Proceso de Calificación Académica Docente</h2>

        <p>Estimado/a académico/a,</p>

        <p>
            Se ha publicado el período <strong>{{ $periodo->nombre }}</strong>
            (año {{ $periodo->anio }}).
        </p>

        <p><strong>Cronograma de etapas:</strong></p>
        <table style="width: 100%; border-collapse: collapse; font-size: 14px; margin-bottom: 20px;">
            <thead>
                <tr style="background: #e8ecf5;">
                    <th style="border: 1px solid #ccc; padding: 8px; text-align: left;">Etapa</th>
                    <th style="border: 1px solid #ccc; padding: 8px;">Inicio</th>
                    <th style="border: 1px solid #ccc; padding: 8px;">Fin</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($cronograma as $etapa)
                <tr>
                    <td style="border: 1px solid #ddd; padding: 8px;">{{ $etapa['etapa'] }}</td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">{{ $etapa['fecha_inicio'] }}</td>
                    <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">{{ $etapa['fecha_fin'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <p>
            <a href="{{ $appUrl }}" style="display: inline-block; background: #1B2D6B; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 6px;">
                Ingresar al sistema
            </a>
        </p>

        <p style="font-size: 12px; color: #888; margin-top: 32px;">
            Vicerrectoría Académica — UCM<br>
            Este es un mensaje automático, por favor no responda.
        </p>
    </div>
</body>
</html>
