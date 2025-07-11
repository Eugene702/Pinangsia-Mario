<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CleaningSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'assigned_to',
        'scheduled_at',
        'started_at',
        'completed_at',
        'cleaning_duration',
        'notes',
        'status',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function assignedStaff()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // Method untuk menghitung durasi pembersihan
    public function calculateDuration()
    {
        if ($this->started_at && $this->completed_at) {
            $this->cleaning_duration = $this->started_at->diffInMinutes($this->completed_at);
            return $this->cleaning_duration;
        }

        return null;
    }
}
