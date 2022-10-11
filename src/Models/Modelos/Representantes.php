<?php

namespace App\Models\Modelos;

use Illuminate\Database\Eloquent\Model;

class Representantes extends Model
{
    protected $table = 'tb_representantes';

    protected $fillable = [

        'codigo',
        'cod_sol',
        'cod_puesto',
        'descripcion_cargo',                
        'cod_tipo_dni',        
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
