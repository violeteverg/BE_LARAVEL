<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductTransaction extends Model
{
    protected $table = 'products_transactions'; 

    protected $fillable = [
        'quantity',
        'transaction_id',
        'product_id'
    ];
    public function transaction(){
        return $this->belongsTo(Transaction::class);
    }
    public function product(){
        return $this->belongsTo(Product::class);
    }
}
