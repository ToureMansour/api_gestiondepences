<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $resetToken;
    public string $email;

    public function __construct(string $resetToken, string $email)
    {
        $this->resetToken = $resetToken;
        $this->email = $email;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reinitialisation de votre mot de passe',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.reset-password',
        );
    }
}
