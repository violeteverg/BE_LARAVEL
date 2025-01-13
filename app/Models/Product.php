<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'product_code',
        'name',
        'category',
        'price',
        'active',
    ];
    public function productTransactions(){
        return $this->hasMany(ProductTransaction::class);
    }
}
