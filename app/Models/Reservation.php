<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'resource_id', 'start_time', 'end_time',
        'status', 'notes', 'total_price', 'payment_status', 'payment_reference',
    ];

    protected $casts = [
        'start_time'  => 'datetime',
        'end_time'    => 'datetime',
        'total_price' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function resource()
    {
        return $this->belongsTo(Resource::class);
    }

    public function logs()
    {
        return $this->hasMany(ReservationLog::class);
    }

    public function logAction(string $action, ?string $from, ?string $to, ?string $notes = null): void
    {
        $this->logs()->create([
            'user_id'     => auth()->id(),
            'action'      => $action,
            'from_status' => $from,
            'to_status'   => $to,
            'notes'       => $notes,
            'ip_address'  => request()->ip(),
        ]);
    }
}
