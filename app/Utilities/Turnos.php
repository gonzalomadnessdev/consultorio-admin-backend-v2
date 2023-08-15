<?php

namespace App\Utilities;

use App\Exceptions\ApiException;
use App\Models\Turno;
use Carbon\Carbon;
use Exception;

class Turnos
{
    public static function  evaluarInterseccionHorarios($horarioDesde, $horarioHasta, $consultorioId, $profesionalId, $turnoId = null)
    {
        $desde = new Carbon($horarioDesde->format('Y-m-d H:i:s'));
        $hasta = new Carbon($horarioHasta->format('Y-m-d H:i:s'));

        $turnosGuardados = null;

        $turnosGuardadosQuery = Turno::select()

        ->where(function ($query) use ($desde, $hasta) {
            $query->whereBetween('fecha_hora_desde', [$desde->setTime(0, 0, 0), $hasta->setTime(23, 59, 59)])
                  ->orWhereBetween('fecha_hora_hasta', [$desde->setTime(0, 0, 0), $hasta->setTime(23, 59, 59)]);
        })
        ->where(function ($query) use ($consultorioId, $profesionalId) {
            $query->where('consultorio_id', $consultorioId)
                  ->orWhere('profesional_id', $profesionalId);
        });

        if($turnoId !== null){
            $turnosGuardadosQuery->where('id', '<>' , $turnoId);
        }

        $turnosGuardados = $turnosGuardadosQuery->get();

        foreach ($turnosGuardados as $turnoGuardado) {

            $turnoGuardadoDesde = new Carbon($turnoGuardado['fecha_hora_desde']);
            $turnoGuardadoHasta = new Carbon($turnoGuardado['fecha_hora_hasta']);

            if (!(
                ($horarioDesde >= $turnoGuardadoHasta && $horarioDesde > $turnoGuardadoDesde)  ||
                ($horarioHasta <= $turnoGuardadoDesde && $horarioHasta < $turnoGuardadoHasta)
            )) {
                if($turnoGuardado->profesional_id == $profesionalId){
                    throw new ApiException('No se puede guardar ya que el profesional tiene un turno ya asignado en ese horario.');
                }else{
                    throw new ApiException('No se puede guardar ya que el consultorio tiene un turno ya asignado en ese horario.');
                }
            }
        }
    }
}
