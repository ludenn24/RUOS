<?php

namespace App\Models\Modelos;

use Illuminate\Database\Eloquent\Model;

class Documentos extends Model
{
    protected $table = 'tb_docs';

    protected $fillable = [

        'codigo',
        'idsol',
        'tipo_sol',
        'iduser',
        'tipdoc',
        'urldoc',
        'flag',
        'freg',

    ];
}
