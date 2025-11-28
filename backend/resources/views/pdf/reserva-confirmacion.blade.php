<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Confirmación de Reserva - Hotel Rocamonti</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .header {
            background: #0071c2;
            color: white;
            padding: 20px;
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0 0 0;
            font-size: 14px;
        }
        .content {
            padding: 20px;
        }
        .content h2 {
            color: #0071c2;
            font-size: 20px;
            margin-bottom: 10px;
        }
        .content p {
            line-height: 1.6;
            margin: 10px 0;
        }
        .details {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border: 1px solid #e0e0e0;
        }
        .details h3 {
            color: #0071c2;
            font-size: 16px;
            margin-top: 0;
            margin-bottom: 15px;
        }
        .details table {
            width: 100%;
            border-collapse: collapse;
        }
        .details table tr {
            border-bottom: 1px solid #e0e0e0;
        }
        .details table tr:last-child {
            border-bottom: none;
        }
        .details table td {
            padding: 10px 0;
        }
        .details table td:first-child {
            color: #0071c2;
            font-weight: bold;
            width: 180px;
        }
        .important-box {
            background: #e8f5e9;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #10b981;
            margin: 20px 0;
        }
        .important-box p {
            margin: 0;
            color: #2e7d32;
        }
        .footer {
            background: #f0f0f0;
            padding: 15px;
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: #666;
        }
        .footer p {
            margin: 5px 0;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            background-color: #10b981;
            color: white;
            border-radius: 4px;
            font-weight: bold;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Hotel Rocamonti</h1>
        <p>Tu refugio junto a la laguna</p>
    </div>

    <div class="content">
        <h2>Confirmación de Reserva</h2>
        <p><strong>Cliente:</strong> {{ $cliente->nombre }} {{ $cliente->apellido }}</p>
        <p>Tu reserva ha sido confirmada exitosamente. Estamos emocionados de recibirte pronto.</p>

        <div class="details">
            <h3>Detalles de tu reserva</h3>
            <table>
                <tr>
                    <td>Número de reserva:</td>
                    <td>#{{ $reserva->id }}</td>
                </tr>
                <tr>
                    <td>Fecha de entrada:</td>
                    <td>{{ \Carbon\Carbon::parse($reserva->fecha_entrada)->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td>Fecha de salida:</td>
                    <td>{{ \Carbon\Carbon::parse($reserva->fecha_salida)->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td>Habitación:</td>
                    <td>{{ $reserva->habitacion->numero_habitacion ?? 'Por asignar' }}</td>
                </tr>
                <tr>
                    <td>Número de personas:</td>
                    <td>{{ $reserva->num_adultos }} adulto(s){{ $reserva->num_ninos > 0 ? ', ' . $reserva->num_ninos . ' niño(s)' : '' }}</td>
                </tr>
                <tr>
                    <td>Total pagado:</td>
                    <td>${{ number_format($reserva->precio_total, 0, ',', '.') }} COP</td>
                </tr>
                <tr>
                    <td>Estado:</td>
                    <td><span class="status-badge">PAGADA</span></td>
                </tr>
            </table>
        </div>

        <div class="important-box">
            <p><strong>Importante:</strong></p>
            <p>Por favor, presenta tu documento de identidad al momento del check-in.</p>
        </div>

        <h3>Datos del cliente</h3>
        <table>
            <tr>
                <td><strong>Cédula:</strong></td>
                <td>{{ $cliente->cedula }}</td>
            </tr>
            <tr>
                <td><strong>Email:</strong></td>
                <td>{{ $cliente->email }}</td>
            </tr>
            <tr>
                <td><strong>Teléfono:</strong></td>
                <td>{{ $cliente->telefono }}</td>
            </tr>
        </table>

        <p style="margin-top: 30px;">Te esperamos en Hotel Rocamonti</p>
    </div>

    <div class="footer">
        <p><strong>Hotel Rocamonti</strong></p>
        <p>Laguna de Aquitania, Boyacá</p>
        <p style="margin-top: 10px; font-size: 11px; color: #999;">Documento generado el {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
    </div>
</body>
</html>
