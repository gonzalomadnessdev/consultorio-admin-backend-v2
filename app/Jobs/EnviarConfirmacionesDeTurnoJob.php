<?php

namespace App\Jobs;

use App\Models\EstadosTurno;
use App\Models\Turno;
use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EnviarConfirmacionesDeTurnoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $turnos = null;

        try {

            $fechaDesde = Carbon::now()->addDays(config('custom.diasAntelacionTurno'))->setTime(0,0,0);
            $fechaHasta = Carbon::now()->addDays(config('custom.diasAntelacionTurno'))->setTime(23,59,59);

            $turnos = Turno::select()->whereBetween('fecha_hora_desde', [$fechaDesde, $fechaHasta])
            ->where('estado_id', EstadosTurno::obtenerIdEstado('pendiente'))->get();

            if(!empty($turnos)){
                foreach($turnos as $turno){
                    ConfirmacionTurnoJob::dispatch($turno);
                }
            }

        } catch (Exception $e) {
            throw $e;
        }

    }

}
