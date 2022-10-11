<?php

namespace App\Models\Modelos;

use Illuminate\Database\Eloquent\Model;

class Notificaciones extends Model
{
    protected $table = 'tb_notificaciones';

    protected $fillable = [

        'id',
        'iddoc',
        'tipo_user',
        'codigo_user',
        'mensaje',
        'urldoc',
        'flag',
        'freg',


    ];
}
