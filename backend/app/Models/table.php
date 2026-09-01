<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    //

    protected $table = 'tables';

    protected $fillable = [
        'table_number',
        'status'
    ];



    public function orders()
    {
        return $this->hasMany(Orders::class);
    }
}
