<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class order_item extends Model
{
    //
    protected $table = 'order_items';
    protected $fillable = [
        'order_id',
        'food_id',
        'quantity',
        'price',
        'subtotal',
    ];


     public function order()
    {
        return $this->belongsTo(orders::class);
    }

    public function food()
    {
        return $this->belongsTo(food::class);
    }
}
