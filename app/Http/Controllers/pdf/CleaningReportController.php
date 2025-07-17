<?php

namespace App\Http\Controllers\pdf;

use App\Http\Controllers\Controller;
use App\Models\CleaningSchedule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Spatie\LaravelPdf\Facades\Pdf;

class CleaningReportController extends Controller
{
    public function export(Request $request)
    {
        try {
            $staff = $request->input('staff');
            $status = $request->input('status');
            $date = $request->input('date');

            $cleaningSchedules = CleaningSchedule::when(!empty($staff), function($query) use($staff){
                return $query->where('assigned_to', '=', $staff);
            })
            ->when(!empty($status), function($query) use($status){
                return $query->where('status', '=', $status);
            })
            ->when(!empty($date), function($query) use($date){
                return $query->whereDate('scheduled_at', $date);
            }, function(Builder $query) {
                return $query->whereMonth('scheduled_at', now()->month)
                    ->whereYear('scheduled_at', now()->year);
            })
            ->with(['assignedStaff'])
            ->get();

            $totalRooms = $cleaningSchedules->unique('room_id')->count();
            $totalStaff = $cleaningSchedules->unique('assigned_to')->count();
            $period = $date ? \Carbon\Carbon::parse($date)->format('F Y') : now()->format('F Y');
            $summary = [
                'complete' => $cleaningSchedules->where('status', '=', 'completed')->count(),
                'in_progress' => $cleaningSchedules->where('status', '=', 'in_progress')->count(),
                'waiting' => $cleaningSchedules->where('status', '=', 'scheduled')->count(),
            ];

            return Pdf::view("pdf.cleaningReport", compact("cleaningSchedules", "totalRooms", "totalStaff", "summary", "period"))
                ->name('laporan-pembersihan.pdf')
                ->format('a4')
                ->margins(top: 15, right: 15, bottom: 20, left: 15)
                ->download();
        } catch (\Exception $e) {
            if (config("app.debug")) {
                dd($e);
            }

            return response()->json([
                'error' => 'Ada kesalahan pada server!'
            ], 500);
        }
    }
}
