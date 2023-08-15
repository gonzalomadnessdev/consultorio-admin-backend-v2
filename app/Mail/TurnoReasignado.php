<?php

namespace App\Mail;

use App\Models\Turno;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TurnoReasignado extends Mailable
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
        $this->turno = $turno->withoutRelations();
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $data = [
            'horarioDesde' => $this->turno->fecha_hora_desde,
            'horarioHasta' => $this->turno->fecha_hora_hasta,
        ];
        return $this->view('turnoReasignadoMail', $data);
    }
}
