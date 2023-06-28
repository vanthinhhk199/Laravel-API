<?php

namespace App\Models;

use App\Models\Products;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cart extends Model
{
    use HasFactory;
    protected $table ='carts';
    protected $fillable =[
        'user_id',
        'prod_id',
        'prod_qty',
      
    ];
    public function product(){
        return $this->belongsTo(Products::class,'prod_id','id');
    }
}
?>