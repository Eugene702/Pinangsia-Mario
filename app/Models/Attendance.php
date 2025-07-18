<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'clock_in_time',
        'clock_in_latitude',
        'clock_in_longitude',
        'status',
        'clock_in_location',
    ];

    protected $casts = [
        'clock_in_location' => 'array'
    ];
}
