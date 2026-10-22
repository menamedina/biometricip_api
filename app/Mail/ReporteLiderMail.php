<?php

namespace App\Mail;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class ReporteLiderMail extends Mailable
{
    use SerializesModels;

    public function __construct(
        public readonly User       $lider,
        public readonly Empresa    $empresa,
        public readonly Collection $empleados,
        public readonly array      $resumen,
        public readonly string     $fecha,
    ) {}

    public function envelope(): Envelope
    {
        $fechaFormateada = \Carbon\Carbon::parse($this->fecha)->translatedFormat('d \d\e F \d\e Y');

        return new Envelope(
            subject: "Reporte de personal {$fechaFormateada} | {$this->empresa->nombre}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reporte-lider',
        );
    }
}
