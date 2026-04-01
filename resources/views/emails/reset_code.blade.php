<!DOCTYPE html>
<html>
<head>
    <style>
        .container { font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto; border: 1px solid #eee; border-radius: 10px; padding: 20px; }
        .header { text-align: center; border-bottom: 2px solid #4f46e5; padding-bottom: 10px; }
        .code-box { background: #f3f4f6; border-radius: 8px; padding: 20px; text-align: center; margin: 20px 0; }
        .code { font-size: 32px; font-weight: bold; letter-spacing: 10px; color: #4f46e5; }
        .btn { display: block; width: 200px; margin: 20px auto; text-align: center; background: #4f46e5; color: white; padding: 12px; border-radius: 5px; text-decoration: none; font-weight: bold; }
        .footer { font-size: 12px; color: #999; text-align: center; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Recuperación de Cuenta</h2>
        </div>
        <p>Hola,</p>
        <p>Has solicitado restablecer tu contraseña. Utiliza el siguiente código de verificación:</p>
        
        <div class="code-box">
            <span class="code">{{ $code }}</span>
        </div>

        <p>Este código es válido solo por <strong>5 minutos</strong>.</p>
        
        <p>También puedes continuar el proceso haciendo clic en el siguiente botón:</p>
        <a href="{{ $url }}" class="btn">Restablecer ahora</a>

        <div class="footer">
            <p>Si no solicitaste este cambio, puedes ignorar este correo con seguridad.</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>