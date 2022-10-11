<?php

namespace App\Models\Modelos;

use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    protected $table = 'ta_usuarios';

    protected $fillable = [
        'codigo',
        'nombres',
        'correo',
        'login',
        'clave',
    ];
}
