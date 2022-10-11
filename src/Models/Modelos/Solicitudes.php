<?php

namespace App\Models\Modelos;

use Illuminate\Database\Eloquent\Model;

class Solicitudes extends Model
{
    protected $table = 'tb_solicitudes';

    protected $fillable = [
        'codigo',
        'num_sol',
        'cod_org',
        'cod_usuario',
        'tipo_sol',
        'tipo_mod',
        'comentario',
        'flag',
        'fec_revision',
        'fec_venci',
        'fec_probacion',
        'freg',
        'inicio',
        'fin',
        'res_dis',

    ];
}
