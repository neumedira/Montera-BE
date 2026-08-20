<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = ['id'];

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function table()
    {
        return $this->belongsTo(Table::class, 'table_id');
    }
}
