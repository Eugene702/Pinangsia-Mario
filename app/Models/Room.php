<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_number',
        'status',
        'notes',
    ];

    public function cleaningSchedules()
    {
        return $this->hasMany(CleaningSchedule::class);
    }

    public function requests()
    {
        return $this->hasMany(Request::class);
    }

    public function guests()
    {
        return $this->hasMany(Guest::class);
    }
}
