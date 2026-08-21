<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BundleItem extends Model
{
    protected $table = 'bundle_items';

    protected $guarded = ['id'];

    public function bundle()
    {
        return $this->belongsTo(Bundle::class, 'bundle_id');
    }

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class, 'menu_item_id');
    }
}
