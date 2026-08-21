<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bundle extends Model
{
    protected $table = 'bundles';

    protected $guarded = ['id'];

    public function items()
    {
        return $this->hasMany(BundleItem::class, 'bundle_id');
    }
}
