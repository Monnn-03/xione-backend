<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seat extends Model
{
    use HasFactory;

    public function bookings()
    {
        return $this->belongsToMany(Booking::class, 'booking_seat');
    }
}
