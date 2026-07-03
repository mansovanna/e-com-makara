<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class orders extends Model
{
    //
    protected $table = 'orders';

    protected $fillable = [
        'order_no',
        'table_id',
        'note',
        'payment_method',
        'status',
        'discount',
        'subtotal',
        'total',
        'coupon_id',
    ];

    public function foods()
    {
        return $this->belongsTo(food::class);
    }

    public function table (){
        return $this->belongsTo(Table::class);
    }

    public function items()
    {
        return $this->hasMany(order_item::class, 'order_id');
    }
}
