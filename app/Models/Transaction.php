<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'bill_id',
        'date',
        'subtotal',
        'customer_id',
    ];
    public function customer(){
        return $this->belongsTo(Customer::class);
    }
    public function productTransactions(){
        return $this->hasMany(ProductTransaction::class);
    }
}
