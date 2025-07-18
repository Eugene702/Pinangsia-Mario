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

        if ($this->shift_pattern === 'custom') {
            $dayMap = [
                '1' => 'Senin',
                '2' => 'Selasa',
                '3' => 'Rabu',
                '4' => 'Kamis',
                '5' => 'Jumat',
                '6' => 'Sabtu',
                '7' => 'Minggu'
            ];

            $dayNames = $this->monthlyShiftDays->map(fn($day) => $dayMap[$day->day] ?? null)
                ->filter()
                ->implode(', ');

            $shiftTime = ucfirst($this->shift_data ?? '');
            return "Shift Custom: {$shiftTime} ({$dayNames})";
        }

        return $this->shift_pattern;
    }

    public function monthlyShiftDays()
    {
        return $this->hasMany(MonthlyShiftDays::class, 'monthlyShiftId', 'id');
    }
}
