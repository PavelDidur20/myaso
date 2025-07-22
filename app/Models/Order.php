<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    protected $table = 'orders';
    protected $fillable = ['user_id', 'comment', 'status', 'created_at', 'completed_at', 'total_price'];
    public  $timestamps = false;

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);

    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
