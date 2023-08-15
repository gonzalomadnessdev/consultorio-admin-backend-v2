<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstadosTurno extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'estados_turno';

    public static function obtenerIdEstado($slug){
        return self::where('slug', $slug)->first()->id;
    }
}
