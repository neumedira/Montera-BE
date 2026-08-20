<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Addon extends Model
{
    protected $table = 'addons';

    protected $guarded = ['id'];

    public function menuItems()
    {
        return $this->belongsToMany(
            MenuItem::class,
            'menu_item_addons',
            'addon_id',
            'menu_item_id'
        );
    }
}
