<?php

namespace App\Services;

use App\Models\User;

class PerformanceCalculatorService
{
    public const TARGET_DURATION = 20;
    public const MAX_ROOMS_COMPLETED = 10;
    public const SPEED_WEIGHT = 0.6;
    public const PRODUCTIVITY_WEIGHT = 0.4;
    public const CLEANING_WEIGHT = 0.5;
    public const ATTENDANCE_WEIGHT = 0.5;

    public function calculatePerformance(User $staff, int $presentCount)
    {
        $efficiencyScore = $this->calculateEfficiencyScore($staff->cleaning_schedules_avg_cleaning_duration ?? 0);
        $productivityScore = $this->calculateProductivityScore($staff->cleaning_schedules_count);
        $cleaningScore = ($efficiencyScore * self::SPEED_WEIGHT) + ($productivityScore * self::PRODUCTIVITY_WEIGHT);
        $totalWorkDays = 22;
        $attendanceScore = $this->calculateAttendanceScore($presentCount, $totalWorkDays);
        $finalScore = ($cleaningScore * self::CLEANING_WEIGHT) + ($attendanceScore * self::ATTENDANCE_WEIGHT);

        return $finalScore;
    }

    private function calculateEfficiencyScore($avgDuration)
    {
        if ($avgDuration == 0)
            return 0;
        return (self::TARGET_DURATION / $avgDuration) * 100;
    }

    private function calculateProductivityScore($totalRooms)
    {
        return ($totalRooms / self::MAX_ROOMS_COMPLETED) * 100;
    }

    private function calculateScore($efficiencyScore, $productivityScore)
    {
        $speedWeight = self::SPEED_WEIGHT;
        $productivityWeight = self::PRODUCTIVITY_WEIGHT;

        return ($efficiencyScore * $speedWeight) + ($productivityScore * $productivityWeight);
    }

    private function calculateAttendanceScore(int $presentCount, int $totalWorkDays): float
    {
        if ($totalWorkDays == 0)
            return 0;

        return ($presentCount / $totalWorkDays) * 100;
    }
}