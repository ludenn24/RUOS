<?php

namespace App\Models\Modelos;

use Illuminate\Database\Eloquent\Model;

class TipoOrganizaciones extends Model
{
    protected $table = 'tb_tipos_orgs';

    protected $fillable = [

        'codigo',
        'descripcion',
        'estado',

    ];
}
