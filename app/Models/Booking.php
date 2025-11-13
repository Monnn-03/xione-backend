<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Booking extends Model
{
    use HasFactory;
    protected $fillable = [
        'customer_name',
        'customer_whatsapp',
        'total_price',
        'status',
        'payment_method',
    ];

    public function seats()
    {
        return $this->belongsToMany(Seat::class, 'booking_seat');
    }
}
