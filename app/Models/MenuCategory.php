<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuCategory extends Model
{
    protected $table = 'menu_categories';

    public function menuItems()
    {
        return $this->hasMany(MenuItem::class, 'category_id');
    }
}
