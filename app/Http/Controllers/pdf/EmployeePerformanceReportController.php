<?php

namespace App\Http\Controllers\pdf;

use App\Http\Controllers\Controller;
use App\Models\MonthlyShift;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Spatie\LaravelPdf\Facades\Pdf;

class EmployeePerformanceReportController extends Controller
{
    public function export(Request $request)
    {
        try {
            $performanceCalculatorService = app(\App\Services\PerformanceCalculatorService::class);
            $month = $request->input('month', now()->month);
            $year = $request->input('year', now()->year);

            $users = User::where('role', 'housekeeping')
                ->withCount([
                    'cleaningSchedules' => function (Builder $query) use ($month, $year) {
                        $query->where('status', 'completed')
                            ->whereYear('completed_at', $year)
                            ->whereMonth('completed_at', $month);
                    },
                    'attendance as present_count' => function (Builder $query) use ($month, $year) {
                        $query->whereIn('status', ['tepat_waktu', 'terlambat'])
                            ->whereYear('clock_in_time', $year)
                            ->whereMonth('clock_in_time', $month);
                    }
                ])
                ->withAvg([
                    'cleaningSchedules' => function (Builder $query) use ($month, $year) {
                        $query->where('status', 'completed')
                            ->whereYear('completed_at', $year)
                            ->whereMonth('completed_at', $month);
                    }
                ], 'cleaning_duration')
                ->get();

            $data = $users->map(function ($record) use ($performanceCalculatorService) {
                return (object) [
                    'name' => $record->name,
                    'cleaning_schedules_count' => $record->cleaning_schedules_count,
                    'cleaning_schedules_avg_cleaning_duration' => $record->cleaning_schedules_avg_cleaning_duration,
                    'present_count' => $record->present_count,
                    'score' => $performanceCalculatorService->calculatePerformance($record, $record->present_count),
                ];
            });

            $sortedData = $data->sortByDesc('score');

            $totalRooms = $users->sum('cleaning_schedules_count');
            $presentDays = $users->sum('present_count');
            $highestScore = $sortedData->max('score');

            $activeStaff = $users->where('cleaning_schedules_count', '>', 0);
            $avgDuration = $activeStaff->avg('cleaning_schedules_avg_cleaning_duration');

            $totalExpectedWorkdays = 0;
            $date = \Carbon\Carbon::create($year, $month);
            $daysInMonth = $date->daysInMonth;

            $monthlyShifts = MonthlyShift::with('monthlyShiftDays')
                ->whereIn('user_id', $users->pluck('id'))
                ->whereYear('month', $year)
                ->whereMonth('month', '>=', $month)
                ->get()
                ->keyBy('user_id');

            foreach ($users as $staff) {
                $shift = $monthlyShifts[$staff->id] ?? null;
                if ($shift) {
                    if ($shift->shift_pattern === 'regular') {
                        for ($day = 1; $day <= $daysInMonth; $day++) {
                            if ($date->copy()->day($day)->isWeekday()) {
                                $totalExpectedWorkdays++;
                            }
                        }
                    } elseif ($shift->shift_pattern === 'custom') {
                        $totalExpectedWorkdays += $shift->monthlyShiftDays->count();
                    }
                }
            }

            $attendanceRate = ($totalExpectedWorkdays > 0)
                ? ($presentDays / $totalExpectedWorkdays) * 100
                : 0;

            return Pdf::view("pdf.employeePerformanceReport", [
                'users' => $sortedData,
                'month' => $month,
                'year' => $year,
                'totalRooms' => $totalRooms,
                'avgDuration' => $avgDuration,
                'highestScore' => $highestScore,
                'attendanceRate' => $attendanceRate,
            ])
                ->name('laporan-kinerja-karyawan.pdf')
                ->format('a4')
                ->margins(top: 15, right: 15, bottom: 20, left: 15)
                ->download();
        } catch (\Exception $e) {
            if (config('app.debug')) {
                dd($e);
            }

            return response()->json([
                'error' => 'Terjadi kesalahan saat membuat laporan.'
            ], 500);
        }
    }
}
