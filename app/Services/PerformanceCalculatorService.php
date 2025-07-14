<?php

namespace App\Services;

use App\Models\User;

class PerformanceCalculatorService{
    public const TARGET_DURATION = 20;
    public const MAX_ROOMS_COMPLETED = 10;
    public const SPEED_WEIGHT = 0.6;
    public const PRODUCTIVITY_WEIGHT = 0.4;

    public function calculatePerformance(User $staff){
        $efficiencyScore = $this->calculateEfficiencyScore($staff->cleaning_schedules_avg_cleaning_duration ?? 0);
        $productivityScore = $this->calculateProductivityScore($staff->cleaning_schedules_count);
        $finalScore = $this->calculateScore($efficiencyScore, $productivityScore);
        return $finalScore;
    }

    private function calculateEfficiencyScore($avgDuration){
        if($avgDuration == 0) return 0;
        return (self::TARGET_DURATION / $avgDuration) * 100;
    }

    private function calculateProductivityScore($totalRooms){
        return ($totalRooms / self::MAX_ROOMS_COMPLETED) * 100;
    }

    private function calculateScore($efficiencyScore, $productivityScore){
        $speedWeight = self::SPEED_WEIGHT;
        $productivityWeight = self::PRODUCTIVITY_WEIGHT;

        return ($efficiencyScore * $speedWeight) + ($productivityScore * $productivityWeight);
    }
}