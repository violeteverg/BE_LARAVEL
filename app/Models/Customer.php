<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'id_customer',
        'name',
        'address',
        'gender',
        'active',
    ];
    
    public function transactions(){
        return $this->hasMany(Transaction::class);
    }
}
