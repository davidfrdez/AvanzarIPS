<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperación de Cuenta – Avanzar IPS</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,300&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background-color: #eef0f8;
            font-family: 'DM Sans', Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
            padding: 40px 16px;
        }

        .wrapper {
            max-width: 560px;
            margin: 0 auto;
        }

        /* ── Top brand strip ── */
        .brand-strip {
            text-align: center;
            margin-bottom: 8px;
        }

        .brand-strip .logo-circle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4f46e5 0%, #6d66f0 100%);
            box-shadow: 0 6px 20px rgba(79, 70, 229, 0.35);
            margin-bottom: 10px;
        }

        .brand-strip .logo-circle span {
            color: #fff;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .brand-strip h1 {
            font-size: 17px;
            font-weight: 700;
            color: #1e1b4b;
            letter-spacing: -0.3px;
        }

        .brand-strip p {
            font-size: 12px;
            color: #8b8fad;
            font-weight: 400;
            margin-top: 2px;
        }

        /* ── Card ── */
        .card {
            background: #ffffff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 32px rgba(79, 70, 229, 0.08), 0 1px 4px rgba(0,0,0,0.05);
            margin-top: 20px;
        }

        /* Header accent bar */
        .card-header {
            background: linear-gradient(135deg, #4f46e5 0%, #7c74f3 100%);
            padding: 32px 40px 28px;
            position: relative;
            overflow: hidden;
        }

        .card-header::before {
            content: '';
            position: absolute;
            top: -30px; right: -30px;
            width: 130px; height: 130px;
            border-radius: 50%;
            background: rgba(255,255,255,0.07);
        }

        .card-header::after {
            content: '';
            position: absolute;
            bottom: -20px; left: 30px;
            width: 80px; height: 80px;
            border-radius: 50%;
            background: rgba(255,255,255,0.05);
        }

        .card-header .tag {
            display: inline-block;
            background: rgba(255,255,255,0.18);
            border: 1px solid rgba(255,255,255,0.25);
            border-radius: 100px;
            padding: 4px 12px;
            font-size: 11px;
            font-weight: 600;
            color: #e0deff;
            letter-spacing: 0.6px;
            text-transform: uppercase;
            margin-bottom: 12px;
        }

        .card-header h2 {
            font-size: 24px;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: -0.5px;
            line-height: 1.2;
        }

        .card-header p {
            font-size: 13.5px;
            color: rgba(255,255,255,0.75);
            margin-top: 6px;
            font-weight: 300;
        }

        /* Body */
        .card-body {
            padding: 36px 40px;
        }

        .greeting {
            font-size: 15px;
            color: #374151;
            line-height: 1.6;
            margin-bottom: 6px;
        }

        .greeting strong {
            color: #1e1b4b;
            font-weight: 600;
        }

        .instruction {
            font-size: 14px;
            color: #6b7280;
            line-height: 1.6;
            margin-bottom: 28px;
        }

        /* Code box */
        .code-section {
            background: linear-gradient(135deg, #f5f4ff 0%, #ededff 100%);
            border: 1.5px solid #d4d0fa;
            border-radius: 14px;
            padding: 28px 20px;
            text-align: center;
            margin-bottom: 28px;
            position: relative;
        }

        .code-section .code-label {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            color: #8b8fad;
            margin-bottom: 12px;
        }

        .code-section .code {
            font-family: 'DM Mono', 'Courier New', monospace;
            font-size: 38px;
            font-weight: 500;
            letter-spacing: 12px;
            color: #4f46e5;
            line-height: 1;
            display: block;
            padding-left: 12px; /* optical compensation for letter-spacing */
        }

        .code-section .timer-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-top: 14px;
            background: #fff;
            border: 1px solid #e0deff;
            border-radius: 100px;
            padding: 5px 14px;
            font-size: 12px;
            color: #6b63d6;
            font-weight: 500;
        }

        .code-section .timer-badge svg {
            flex-shrink: 0;
        }

        /* Divider */
        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
        }

        .divider hr {
            flex: 1;
            border: none;
            border-top: 1px solid #e9eaf0;
        }

        .divider span {
            font-size: 12px;
            color: #b0b3c6;
            font-weight: 500;
            white-space: nowrap;
        }

        /* CTA Button */
        .btn-wrapper {
            text-align: center;
            margin-bottom: 28px;
        }

        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #4f46e5 0%, #6d66f0 100%);
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 36px;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            letter-spacing: -0.2px;
            box-shadow: 0 4px 16px rgba(79, 70, 229, 0.35), 0 1px 3px rgba(79,70,229,0.2);
            transition: opacity 0.2s;
        }

        /* Notice */
        .notice {
            background: #fefce8;
            border-left: 3px solid #fbbf24;
            border-radius: 0 8px 8px 0;
            padding: 12px 16px;
            font-size: 12.5px;
            color: #92400e;
            line-height: 1.5;
            margin-bottom: 28px;
        }

        /* Separator */
        .sep {
            border: none;
            border-top: 1px solid #f0f0f5;
            margin: 0 0 24px;
        }

        /* Footer */
        .footer {
            font-size: 12px;
            color: #9ca3af;
            text-align: center;
            line-height: 1.7;
            padding: 0 40px 36px;
        }

        .footer a {
            color: #7c74f3;
            text-decoration: none;
        }

        .footer .divider-dot {
            display: inline-block;
            margin: 0 6px;
            color: #d1d5db;
        }
    </style>
</head>
<body>
    <div class="wrapper">

        <!-- Brand -->
        <div class="brand-strip">
            <div class="logo-circle"><span>A</span></div>
            <h1>Avanzar IPS</h1>
            <p>Portal de Historias Clínicas</p>
        </div>

        <!-- Card -->
        <div class="card">

            <!-- Header -->
            <div class="card-header">
                <div class="tag">🔐 Seguridad de cuenta</div>
                <h2>Recuperación<br>de Contraseña</h2>
                <p>Recibimos una solicitud para restablecer tu acceso</p>
            </div>

            <!-- Body -->
            <div class="card-body">
                <p class="greeting">Hola, <strong>buen día 👋</strong></p>
                <p class="instruction">
                    Hemos recibido una solicitud para restablecer la contraseña de tu cuenta en el Portal de Historias Clínicas.
                    Usa el siguiente código de verificación para continuar:
                </p>

                <!-- Code -->
                <div class="code-section">
                    <div class="code-label">Código de verificación</div>
                    <span class="code">{{ $code }}</span>
                    <div>
                        <span class="timer-badge">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#6b63d6" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                            </svg>
                            Válido por <strong>&nbsp;5 minutos</strong>
                        </span>
                    </div>
                </div>

                <!-- Divider -->
                <div class="divider">
                    <hr><span>o también puedes</span><hr>
                </div>

                <!-- CTA -->
                <div class="btn-wrapper">
                    <a href="{{ $url }}" class="btn">Restablecer contraseña →</a>
                </div>

                <!-- Warning -->
                <div class="notice">
                    ⚠️ <strong>¿No solicitaste este cambio?</strong> Si no reconoces esta acción, ignora este correo. Tu contraseña permanecerá igual y nadie más podrá acceder a tu cuenta.
                </div>
            </div>

            <hr class="sep">

            <!-- Footer -->
            <div class="footer">
                <p>
                    Este correo fue generado automáticamente, por favor no respondas a él.
                </p>
                <p style="margin-top:8px;">
                    <a href="#">Política de privacidad</a>
                    <span class="divider-dot">·</span>
                    <a href="#">Soporte</a>
                    <span class="divider-dot">·</span>
                    <a href="#">Avanzar IPS</a>
                </p>
                <p style="margin-top:10px; color:#c4c7d6;">
                    &copy; {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.
                </p>
            </div>

        </div><!-- /card -->

    </div><!-- /wrapper -->
</body>
</html>