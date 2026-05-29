<?php

namespace App\Mail;

use App\Models\Periodo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InicioProcesoMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @param  array<int, array{etapa: string, fecha_inicio: string, fecha_fin: string}>  $cronograma
     */
    public function __construct(
        public Periodo $periodo,
        public array $cronograma,
        public string $appUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Inicio Proceso CAD {$this->periodo->nombre} - UCM",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.inicio_proceso',
        );
    }
}
