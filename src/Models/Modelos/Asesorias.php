<?php

namespace App\Models\Modelos;

use Illuminate\Database\Eloquent\Model;

class Asesorias extends Model
{
    protected $table = 'tb_asesoria';

    protected $fillable = [

        'codigo',
        'dni',
        'nombres',
        'apellido_pat',
	      'apellido_mat',
        'correo',
        'telefono',
        'distrito',
        'provincia',
        'departamento',
        'foto',
        'tipo_asesoria',
        'nom_org',
        'dist_org',
        'fecha_ase',
        'hora_ase',
        'medio',

    ];
}
