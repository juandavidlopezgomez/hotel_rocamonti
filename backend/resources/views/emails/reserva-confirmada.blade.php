<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background-color: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background-color: white; }
        .header { background: linear-gradient(135deg, #0071c2 0%, #005a9c 100%); color: white; padding: 30px 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 28px; }
        .header p { margin: 10px 0 0 0; font-size: 16px; opacity: 0.9; }
        .content { padding: 30px 20px; }
        .content h2 { color: #333; font-size: 22px; margin-bottom: 10px; }
        .content p { color: #666; line-height: 1.6; margin: 10px 0; }
        .details { background-color: #f9f9f9; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .details h3 { color: #0071c2; font-size: 18px; margin-top: 0; margin-bottom: 15px; }
        .details ul { list-style: none; padding: 0; margin: 0; }
        .details li { padding: 10px 0; border-bottom: 1px solid #e0e0e0; color: #333; }
        .details li:last-child { border-bottom: none; }
        .details strong { color: #0071c2; display: inline-block; width: 180px; }
        .footer { background: #f0f0f0; padding: 20px; text-align: center; color: #666; font-size: 14px; }
        .footer p { margin: 5px 0; }
        .button { display: inline-block; padding: 12px 30px; background: #0071c2; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .success-icon { text-align: center; font-size: 48px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏨 Hotel Rocamonti</h1>
            <p>Tu refugio junto a la laguna</p>
        </div>
        
        <div class="content">
            <div class="success-icon">✅</div>
            <h2>¡Reserva Confirmada!</h2>
            <p>Hola <strong>{{ $cliente->nombre }} {{ $cliente->apellido }}</strong>,</p>
            <p>Tu reserva ha sido confirmada exitosamente. Estamos emocionados de recibirte pronto.</p>
            
            <div class="details">
                <h3>📋 Detalles de tu reserva:</h3>
                <ul>
                    <li><strong>Número de reserva:</strong> #{{ $reserva->id }}</li>
                    <li><strong>Fecha de entrada:</strong> {{ \Carbon\Carbon::parse($reserva->fecha_entrada)->format('d/m/Y') }}</li>
                    <li><strong>Fecha de salida:</strong> {{ \Carbon\Carbon::parse($reserva->fecha_salida)->format('d/m/Y') }}</li>
                    <li><strong>Habitación:</strong> {{ $reserva->habitacion->numero_habitacion ?? 'Por asignar' }}</li>
                    <li><strong>Número de personas:</strong> {{ $reserva->num_adultos }} adulto(s){{ $reserva->num_ninos > 0 ? ', ' . $reserva->num_ninos . ' niño(s)' : '' }}</li>
                    <li><strong>Total pagado:</strong> ${{ number_format($reserva->precio_total, 0, ',', '.') }} COP</li>
                    <li><strong>Estado:</strong> <span style="color: #10b981; font-weight: bold;">PAGADA</span></li>
                </ul>
            </div>
            
            <div style="background: #e8f5e9; padding: 15px; border-radius: 5px; border-left: 4px solid #10b981; margin: 20px 0;">
                <p style="margin: 0; color: #2e7d32;"><strong>📌 Importante:</strong></p>
                <p style="margin: 5px 0 0 0; color: #2e7d32;">Por favor, presenta tu documento de identidad al momento del check-in.</p>
            </div>
            
            <p>Si tienes alguna pregunta o necesitas hacer cambios a tu reserva, no dudes en contactarnos.</p>
            
            <p style="margin-top: 30px;">¡Te esperamos en Hotel Rocamonti!</p>
        </div>
        
        <div class="footer">
            <p><strong>Hotel Rocamonti</strong></p>
            <p>Laguna de Aquitania, Boyacá</p>
            <p style="margin-top: 15px; font-size: 12px; color: #999;">Este es un correo automático, por favor no responder.</p>
        </div>
    </div>
</body>
</html>
