<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Profesional extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'profesionales';

    protected $fillable = ['nombre', 'apellido', 'documento', 'matricula', 'email', 'telefono'];
}
