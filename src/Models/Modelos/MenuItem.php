<?php

namespace App\Models\Modelos;

use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    protected $table = 'ta_menu_item';

    protected $fillable = [
        'iditem',
        'idcategory',
        'nombre',
        'tag',
    ];
}
