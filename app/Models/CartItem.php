<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = ['userid', 'cart_item', 'status'];

    protected $casts = [
        'cart_item' => 'json', // Automatically cast 'cart_item' attribute to JSON
    ];

    // Define the relationship with the User model
    public function user()
    {
        return $this->belongsTo(User::class, 'userid');
    }
}
