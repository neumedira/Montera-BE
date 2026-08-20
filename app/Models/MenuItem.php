<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    protected $table = 'menu_items';

    protected $guarded = ['id'];

    public function category()
    {
        return $this->belongsTo(MenuCategory::class, 'category_id');
    }

    public function addons()
    {
        return $this->belongsToMany(
            Addon::class,
            'menu_item_addons',
            'menu_item_id',
            'addon_id'
        );
    }
}
