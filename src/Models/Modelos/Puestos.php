<?php

namespace App\Models\Modelos;

use Illuminate\Database\Eloquent\Model;

class Puestos extends Model
{
    protected $table = 'tb_puestos';

    protected $fillable = [

        'codigo',
        'estado',
        'puesto',
        'star',

    ];
}
