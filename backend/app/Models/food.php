<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Food extends Model
{
    //

     protected  $table = 'foods';

    protected $fillable = [
        'category_id',
        'name',
        'image',
        'price',
        'description',
        'status'
    ];

    protected $appends = [
        'image_url'
    ];

    public  function getImageUrlAttribute()
    {
        return asset($this->image);
    }

    public function category()
    {
        return $this->belongsTo(category::class);
    }



    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
