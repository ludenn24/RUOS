<?php

namespace App\Models\Modelos;

use Illuminate\Database\Eloquent\Model;

class TipoOrganizacionDen extends Model
{
    protected $table = 'tb_tipos_orgs_denom';

    protected $fillable = [

        'codigo',
        'codigo_tipo',
        'descripcion',
        'estado',

    ];
}
