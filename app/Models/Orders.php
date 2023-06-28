<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    use HasFactory;

    protected $table= 'orders';
    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'address',
        'total_price',
        'status',
        'paymentmode',

    ];


    public function orderitems()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }
}
