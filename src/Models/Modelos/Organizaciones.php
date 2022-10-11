<?php

namespace App\Models\Modelos;

use Illuminate\Database\Eloquent\Model;

class Organizaciones extends Model
{
    protected $table = 'tb_organizacion';

    protected $fillable = [

        'num_org',
        'codigo',
        'iduser',
        'nombre_org',
        'tipo_org',
		'tipo_den',
        'fecha_constitucion',
        'num_miem',
        'fines',
        'distrito',
        'latitud',
        'longitud',
        'domicilio_org',
        'fecha_vigente',
        'fecha_fin',
        'estado',

    ];
}
