<?php

namespace App\Models;

class Alumno implements \JsonSerializable
{
    public $id;
    public $nombres;
    public $apellidos;
    public $matricula;
    public $promedio;

    public function __construct($id, $nombres, $apellidos, $matricula, $promedio)
    {
        $this->id = $id;
        $this->nombres = $nombres;
        $this->apellidos = $apellidos;
        $this->matricula = $matricula;
        $this->promedio = (float) $promedio;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'nombres' => $this->nombres,
            'apellidos' => $this->apellidos,
            'matricula' => $this->matricula,
            'promedio' => $this->promedio,
        ];
    }

    public static function fromArray($id, array $data): self
    {
        return new self(
            $id,
            $data['nombres'],
            $data['apellidos'],
            $data['matricula'],
            $data['promedio']
        );
    }
}