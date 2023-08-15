<?php

namespace App\Mail;

use App\Models\Turno;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class ConfirmacionTurno extends Mailable
{
    use Queueable, SerializesModels;

    public $turno;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Turno $turno)
    {
        $this->turno = $turno;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        Log::info("CONFIRMACION TURNO MAIL HOST: " . config('app.url'));
        $data = [
            'fechaTurno' => $this->turno->fecha_hora_desde ,
            'id' => Crypt::encryptString($this->turno->id),
            'host' => config('app.url')
        ];

        return $this->view('confirmarTurnoMail', $data );
    }
}
