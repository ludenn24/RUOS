<?php

namespace App\Models\Modelos;

use Illuminate\Database\Eloquent\Model;

class Verificacion extends Model
{
    protected $table = 'tb_verificacion';

    protected $fillable = [

        'codigo',
        'cod_recon',
        'cod_user',
        'num_ver',
        'fecha',
        'url_doc',
        'estado',

    ];
}
