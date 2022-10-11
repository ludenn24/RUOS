<?php

namespace App\Models\Modelos;

use Illuminate\Database\Eloquent\Model;

class MenuCategoria extends Model
{
    protected $table = 'ta_menu_category';

    protected $fillable = [
        'idcategory',
        'nombre',
        'posicion',
    ];
}
