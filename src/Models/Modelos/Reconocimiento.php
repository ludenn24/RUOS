<?php

namespace App\Models\Modelos;

use Illuminate\Database\Eloquent\Model;

class Reconocimiento extends Model
{
    protected $table = 'tb_reconocimiento';

    protected $fillable = [

        'codigo',
        'num_rec',
        'cod_usuario',
        'inicio',
        'fin',
        'fec_aprobacion',
        'fec_archivamiento',
        'observacion',
        'estado',

    ];
}
