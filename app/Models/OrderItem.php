<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $table = 'order_items';
    protected $fillable = ['order_id', 'count', 'product_id'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    public function  product()
    {
        return $this->belongsTo(Product::class);
    }

}
