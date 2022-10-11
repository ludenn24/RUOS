<?php

namespace App\Models\Modelos;

use Illuminate\Database\Eloquent\Model;

class JuntasDirectivas extends Model
{
    protected $table = 'tb_junta_directiva';

    protected $fillable = [

        'codigo',
        'cod_org',
        'cod_puesto',
        'descripcion_cargo',
        'dni',
        'nombre',
        'apellido_pat',
        'apellido_mat',
        'fecha_nacimiento',
        'sexo',
        'departamento',
        'provincia',
        'distrito',
        'foto',
        'estado'

    ];
}
