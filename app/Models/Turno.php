<?php

namespace App\Models;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Turno extends Model
{
    use HasFactory;

    protected $table = 'turnos';

    protected $fillable = ['paciente_id', 'profesional_id', 'consultorio_id', 'estado_id', 'titulo', 'descripcion', 'fecha_hora_desde', 'fecha_hora_hasta'];

    public function profesional()
    {
        return $this->belongsTo(Profesional::class, 'profesional_id');
    }
    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }
    public function consultorio()
    {
        return $this->belongsTo(Consultorio::class, 'consultorio_id');
    }
    public function estado()
    {
        return $this->belongsTo(EstadosTurno::class, 'estado_id');
    }

    public static function tratarPendientesDeAyer(){

        $turnosPendientesDeAyer = null;

        $desde = Carbon::now()->subDay()->setTime(0, 0, 0);
        $hasta = Carbon::now()->subDay()->setTime(23, 59, 59);

        try {
            $turnosPendientesDeAyer = self::select()
            ->whereBetween('fecha_hora_desde', [$desde, $hasta])
            ->where('estado_id', EstadosTurno::obtenerIdEstado('pendiente'));

            $turnosPendientesDeAyer->update(['estado_id' => EstadosTurno::obtenerIdEstado('asa')]);

        } catch (Exception $e) {
            Log::info("Fallo la funcion 'tratarPendientesDiaAnterior' del schedule");
            throw $e;
        }
    }

}
