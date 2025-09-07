<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'price',
        'stock_quantity',
        'min_order_quantity',
        'image',
    ];


    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getImageAttribute($value){

        return $value ? url('uploads/'.asset($value)) : url(asset('uploads/defult.jpg'));
    }
}
