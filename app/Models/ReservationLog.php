<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservationLog extends Model
{
    protected $fillable = [
        'reservation_id', 'user_id', 'action',
        'from_status', 'to_status', 'notes', 'ip_address',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
