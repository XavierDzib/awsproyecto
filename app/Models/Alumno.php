<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alumno extends Model
{
    use HasFactory;

    protected $table = 'alumnos';

    protected $fillable = [
        'nombres',
        'apellidos',
        'matricula',
        'promedio',
        'password',
        'fotoPerfilUrl'
    ];

    // Ocultamiento de la contraseña en las respuestas JSON
    protected $hidden = [
        'password',
    ];

    // Forzamos el casteo del promedio a float
    protected $casts = [
        'promedio' => 'float',
    ];
}