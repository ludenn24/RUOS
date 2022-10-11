<?php

namespace App\Models\Modelos;

use Illuminate\Database\Eloquent\Model;

class Resolucion extends Model
{
    protected $table = 'tb_resolucion';

    protected $fillable = [

        'codigo',
        'num_res',
        'cod_solicitud',
        'cod_user',
        'ruta',
        'fecha_registro',
        'estado',

    ];
}
