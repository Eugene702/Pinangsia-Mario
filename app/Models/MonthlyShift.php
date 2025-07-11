<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlyShift extends Model
{
    protected $fillable = [
        'user_id',
        'month',
        'shift_pattern',
        'shift_data',
        'notes'
    ];

    protected $casts = [
        'month' => 'date:Y-m',
        'shift_data' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Helper method untuk menentukan shift
    public function getShiftTypeAttribute()
    {
        if ($this->shift_pattern === 'regular') {
            return 'Shift Regular (Pagi: Senin-Jumat)';
        }

        return 'Shift Custom: ' . collect($this->shift_data)->implode(', ');
    }
}
