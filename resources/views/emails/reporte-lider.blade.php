<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Personal</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: #f4f6f9;
            color: #333;
            font-size: 14px;
        }
        .wrapper {
            max-width: 700px;
            margin: 20px auto;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        /* Header */
        .header {
            background: linear-gradient(135deg, #1a73e8 0%, #0d47a1 100%);
            padding: 28px 32px;
            color: #fff;
        }
        .header h1 {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 4px;
        }
        .header p {
            font-size: 13px;
            opacity: 0.85;
        }
        /* Content */
        .content {
            padding: 28px 32px;
        }
        .greeting {
            font-size: 15px;
            margin-bottom: 6px;
        }
        .greeting strong {
            color: #1a73e8;
        }
        .subtext {
            color: #666;
            font-size: 13px;
            margin-bottom: 24px;
        }
        /* Summary cards */
        .summary {
            display: table;
            width: 100%;
            border-spacing: 8px;
            margin-bottom: 24px;
        }
        .summary-row {
            display: table-row;
        }
        .card {
            display: table-cell;
            text-align: center;
            padding: 14px 10px;
            border-radius: 6px;
            width: 25%;
        }
        .card-total     { background: #e8f0fe; color: #1a73e8; }
        .card-entrada   { background: #e6f4ea; color: #1e8e3e; }
        .card-permiso   { background: #fef7e0; color: #f9ab00; }
        .card-sin       { background: #fce8e6; color: #c5221f; }
        .card .num {
            font-size: 28px;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 4px;
        }
        .card .label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.85;
        }
        /* Table */
        .section-title {
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #666;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        table thead tr {
            background: #f1f3f4;
        }
        table thead th {
            padding: 10px 12px;
            text-align: left;
            font-weight: 600;
            color: #555;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
        table tbody tr:nth-child(even) {
            background: #fafafa;
        }
        table tbody tr:hover {
            background: #f0f4ff;
        }
        table tbody td {
            padding: 10px 12px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }
        .badge {
            display: inline-block;
            padding: 3px 9px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }
        .badge-entrada   { background: #e6f4ea; color: #1e8e3e; }
        .badge-ambos     { background: #c8e6c9; color: #1b5e20; }
        .badge-permiso   { background: #fef7e0; color: #e37400; }
        .badge-sin       { background: #fce8e6; color: #c5221f; }
        .time-cell {
            font-family: 'Courier New', monospace;
            font-size: 13px;
            font-weight: 600;
            color: #333;
        }
        .no-time {
            color: #bbb;
            font-size: 12px;
        }
        /* Footer */
        .footer {
            background: #f8f9fa;
            padding: 18px 32px;
            text-align: center;
            border-top: 1px solid #eee;
            font-size: 12px;
            color: #999;
        }
        .footer a { color: #1a73e8; text-decoration: none; }
    </style>
</head>
<body>
<div class="wrapper">

    {{-- Header --}}
    <div class="header">
        <h1>Reporte de Personal</h1>
        <p>
            {{ $empresa->nombre }} &nbsp;·&nbsp;
            {{ \Carbon\Carbon::parse($fecha)->translatedFormat('l d \d\e F \d\e Y') }}
            &nbsp;·&nbsp; Generado a las {{ now()->format('H:i') }}
        </p>
    </div>

    <div class="content">

        {{-- Saludo --}}
        <p class="greeting">Hola, <strong>{{ $lider->name }}</strong></p>
        <p class="subtext">
            <strong>{{ $empresa->nombre }}</strong> &nbsp;·&nbsp;
            A continuación encontrarás el estado de marcación de tu equipo para el día de hoy.
        </p>

        {{-- Resumen --}}
        <div class="summary">
            <div class="summary-row">
                <div class="card card-total">
                    <div class="num">{{ $resumen['total'] }}</div>
                    <div class="label">Total</div>
                </div>
                <div class="card card-entrada">
                    <div class="num">{{ $resumen['con_entrada'] }}</div>
                    <div class="label">Con Entrada</div>
                </div>
                <div class="card card-permiso">
                    <div class="num">{{ $resumen['con_permiso'] }}</div>
                    <div class="label">Con Permiso</div>
                </div>
                <div class="card card-sin">
                    <div class="num">{{ $resumen['sin_marcacion'] }}</div>
                    <div class="label">Sin Marcación</div>
                </div>
            </div>
        </div>

        {{-- Tabla de empleados --}}
        <p class="section-title">Detalle por empleado</p>
        <table>
            <thead>
                <tr>
                    <th>Empleado</th>
                    <th>Código</th>
                    <th>Departamento</th>
                    <th>Entrada</th>
                    <th>Salida</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($empleados as $emp)
                <tr>
                    <td>
                        <strong>{{ $emp['nombre'] }}</strong><br>
                        <span style="color:#999; font-size:11px;">C.C. {{ $emp['cedula'] }}</span>
                    </td>
                    <td style="color:#666;">{{ $emp['codigo_empleado'] }}</td>
                    <td style="color:#666;">{{ $emp['departamento'] }}</td>
                    <td>
                        @if ($emp['entrada'])
                            <span class="time-cell">{{ $emp['entrada'] }}</span>
                        @else
                            <span class="no-time">—</span>
                        @endif
                    </td>
                    <td>
                        @if ($emp['salida'])
                            <span class="time-cell">{{ $emp['salida'] }}</span>
                        @else
                            <span class="no-time">—</span>
                        @endif
                    </td>
                    <td>
                        @switch($emp['estado'])
                            @case('Entrada y Salida')
                                <span class="badge badge-ambos">Entrada y Salida</span>
                                @break
                            @case('Entrada')
                                <span class="badge badge-entrada">Entrada</span>
                                @break
                            @case('Con Permiso')
                                <span class="badge badge-permiso">
                                    {{ $emp['permiso_tipo'] ?? 'Permiso' }}
                                </span>
                                @break
                            @default
                                <span class="badge badge-sin">Sin Marcación</span>
                        @endswitch
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center; color:#aaa; padding:20px;">
                        No hay empleados asignados a tu equipo.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

    </div>

    {{-- Footer --}}
    <div class="footer">
        <p>Este reporte fue generado automáticamente por <strong>BiometricIP</strong>.</p>
        <p style="margin-top:4px;">Si tienes preguntas, contacta a tu administrador.</p>
    </div>

</div>
</body>
</html>
