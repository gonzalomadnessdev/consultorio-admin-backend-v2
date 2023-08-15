<?php

namespace App\Jobs;

use App\Mail\ConfirmacionTurno;
use App\Models\EstadosTurno;
use App\Models\Turno;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ConfirmacionTurnoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $turno;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Turno $turno)
    {
        $this->turno = $turno;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::to($this->turno->paciente->email)->send(new ConfirmacionTurno($this->turno));

        if( count(Mail::failures()) == 0 ) {
            $this->turno->estado_id = EstadosTurno::obtenerIdEstado('pendiente');
            $this->turno->save();
        }
    }
}
