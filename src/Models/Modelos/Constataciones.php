<?php

namespace App\Models\Modelos;

use Illuminate\Database\Eloquent\Model;

class Constataciones extends Model
{
    protected $table = 'tb_constataciones';

    protected $fillable = [
        'cod_constatacion',
        'cod_solicitud',
        'fecha_inicio',
        'fecha_fin',
        'ruta',
        'estado',
        'observaciones',
        'fecha_registro',
        'created_at'
    ];
}
