<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    //
    protected $table = 'orders';

    protected $fillable = [
        'order_no',
        'table_id',
        'note',
        'payment_method',
        'payment_status',
        'total',
    ];

    public function foods()
    {
        return $this->belongsTo(Food::class);
    }

    public function table()
    {
        return $this->belongsTo(Table::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }


}
