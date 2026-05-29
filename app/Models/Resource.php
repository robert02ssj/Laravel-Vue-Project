<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Resource extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id', 'name', 'slug', 'description', 'location',
        'capacity', 'image', 'active', 'open_time', 'close_time',
        'slot_duration', 'price_per_slot', 'available_days',
    ];

    protected $casts = [
        'active'         => 'boolean',
        'available_days' => 'array',
        'price_per_slot' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(fn ($r) => $r->slug ??= Str::slug($r->name));
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function isAvailableAt(\Carbon\Carbon $start, \Carbon\Carbon $end, ?int $excludeId = null): bool
    {
        $query = $this->reservations()
            ->whereNotIn('status', ['cancelled'])
            ->where(fn ($q) => $q
                ->whereBetween('start_time', [$start, $end->copy()->subSecond()])
                ->orWhereBetween('end_time', [$start->copy()->addSecond(), $end])
                ->orWhere(fn ($q) => $q->where('start_time', '<=', $start)->where('end_time', '>=', $end))
            );

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->doesntExist();
    }
}
