<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = 'menus';

    protected $fillable = ['id', 'parent_id', 'name', 'description', 'icon', 'url_access', 'controller', 'visible', 'description', 'status', 'user_id'];

}
