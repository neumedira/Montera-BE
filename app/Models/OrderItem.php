<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $guarded = ['id'];

    // =========================================================
    // ORDER
    // =========================================================

    public function order()
    {
        return $this->belongsTo(
            Order::class,
            'order_id'
        );
    }

    // =========================================================
    // MENU ITEM
    // =========================================================

    public function menuItem()
    {
        return $this->belongsTo(
            MenuItem::class,
            'menu_item_id'
        );
    }

    // =========================================================
    // BUNDLE
    // =========================================================

    public function bundle()
    {
        return $this->belongsTo(
            Bundle::class,
            'bundle_id'
        );
    }

    // =========================================================
    // ADDONS
    // =========================================================

    public function addons()
    {
        return $this->hasMany(
            OrderItemAddon::class,
            'order_item_id'
        );
    }
}
