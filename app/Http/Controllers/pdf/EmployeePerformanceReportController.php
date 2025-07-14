<?php

namespace App\Http\Controllers\pdf;

use App\Http\Controllers\Controller;
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
            $user = User::where('role', '=', 'housekeeping')
                ->withCount([
                    'cleaningSchedules' => function (Builder $query) use ($month, $year) {
                        $query->where('status', 'completed')
                            ->whereYear('created_at', $year)
                            ->whereMonth('created_at', $month);
                    }
                ])
                ->withAvg([
                    'cleaningSchedules' => function (Builder $query) use ($month, $year) {
                        $query->where('status', 'completed')
                            ->whereYear('created_at', $year)
                            ->whereMonth('created_at', $month);
                    }
                ], 'cleaning_duration')
                ->get();

            $data = $user->map(function ($record) use ($performanceCalculatorService) {
                return [
                    ...$record->toArray(),
                    'score' => $performanceCalculatorService->calculatePerformance($record),
                ];
            });

            $totalRooms = $data->sum('cleaning_schedules_count');
            $avgDuration = $data->avg('cleaning_schedules_avg_cleaning_duration');
            $highestScore = $data->max('score');
            return Pdf::view("pdf.employeePerformanceReport", ['user' => $data, 'month' => $month, 'year' => $year, 'totalRooms' => $totalRooms, 'avgDuration' => $avgDuration, 'highestScore' => $highestScore])
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
