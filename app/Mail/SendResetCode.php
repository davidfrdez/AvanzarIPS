<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendResetCode extends Mailable
{
    use Queueable, SerializesModels;

    // 1. Declaramos una variable pública para el código
    public $code;

    /**
     * En el constructor recibimos el código desde el Controlador
     */
    public function __construct($code)
    {
        $this->code = $code;
    }

    /**
     * El asunto del correo
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Código de recuperación de contraseña',
        );
    }

    /**
     * Definimos la vista que se usará
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.reset_code', // Asegúrate de crear este archivo
        );
    }

    public function attachments(): array
    {
        return [];
    }
}