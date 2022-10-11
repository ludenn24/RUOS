<?php

namespace App\Models\Modelos;

use Illuminate\Database\Eloquent\Model;

class DetalleReconocimiento extends Model
{
    protected $table = 'tb_detalle_recono';

    protected $fillable = [

        'codigo',
        'cod_solicitud',
        'cod_recon',
        'estado'

    ];
}
