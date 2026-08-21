<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItemAddon extends Model
{
    protected $table = 'menu_item_addons';

    protected $guarded = ['id'];

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class, 'menu_item_id');
    }

    public function addon()
    {
        return $this->belongsTo(Addon::class, 'addon_id');
    }
}
