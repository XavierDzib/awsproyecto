<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profesor extends Model
{
    use HasFactory;

    protected $table = 'profesores';

    protected $fillable = [
        'numeroEmpleado',
        'nombres',
        'apellidos',
        'horasClase'
    ];

    // Forzamos el casteo de horasClase a int
    protected $casts = [
        'horasClase' => 'integer',
        'numeroEmpleado' => 'integer',
    ];
}