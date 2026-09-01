<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    //
    protected $table = 'categories';
    protected $fillable = [
        'name',
        'image',
        'status'
    ];

    protected $appends = [
        'image_url'
    ];

    public function getImageUrlAttribute()
    {
        return asset($this->image);
    }

    public function foods()
    {
        return $this->belongsTo(Food::class);
    }


    // public function foods() { return $this->hasMany(Food::class); }
}
