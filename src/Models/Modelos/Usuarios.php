<?php

namespace App\Models\Modelos;

use Illuminate\Database\Eloquent\Model;

class Usuarios extends Model
{
    protected $table = 'tb_solicitante';

    protected $fillable = [
        'usuario',
        'tipo_user',
        'nombres',
        'apellidopaterno',
        'apellidomaterno',
        'correo',
        'telefono',
        'casa',
        'dni',
        'clave',
        'fecha_nacimiento',
        'distrito',
        'direccion',
    ];
}
