<?php

namespace App\Models;

class Profesor implements \JsonSerializable
{
    public $id;
    public $numeroEmpleado;
    public $nombres;
    public $apellidos;
    public $horasClase;

    public function __construct($id, $numeroEmpleado, $nombres, $apellidos, $horasClase)
    {
        $this->id = $id;
        $this->numeroEmpleado = $numeroEmpleado;
        $this->nombres = $nombres;
        $this->apellidos = $apellidos;
        $this->horasClase = (int) $horasClase;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'numeroEmpleado' => $this->numeroEmpleado,
            'nombres' => $this->nombres,
            'apellidos' => $this->apellidos,
            'horasClase' => $this->horasClase,
        ];
    }

    public static function fromArray($id, array $data): self
    {
        return new self(
            $id,
            $data['numeroEmpleado'],
            $data['nombres'],
            $data['apellidos'],
            $data['horasClase']
        );
    }
}