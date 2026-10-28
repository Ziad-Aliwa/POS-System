<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeldOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'customer_id',
        'reference_note',
        'cart_data',
        'subtotal',
        'discount',
        'total',
    ];

    protected $casts = [
        'cart_data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
