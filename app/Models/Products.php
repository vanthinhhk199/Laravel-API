<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    use HasFactory;
    protected $table = 'products';
    protected $fillable = [
        'cate_id',
        'name',
        'slug',
        'description',
        'price',
        'image'=>'json',
        'qty',
        
    ];
    public function category(){
        return $this->belongsTo(Category::class,'cate_id','id');
    }
}
?>
