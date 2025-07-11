<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'month',
        'schedule_data', // Format: { "1": "pagi", "2": "libur", "3": "siang", ... }
        'notes'
    ];

    protected $casts = [
        'month' => 'date:Y-m',
        'schedule_data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
