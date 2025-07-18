<?php

namespace App\Filament\Resources\MonthlyShiftResource\Pages;

use App\Filament\Resources\MonthlyShiftResource;
use App\Models\MonthlyShift;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Facades\DB;

class ManageMonthlyShifts extends ManageRecords
{
    protected static string $resource = MonthlyShiftResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->after(function (MonthlyShift $record, array $data) {
                    if ($data['shift_pattern'] === 'custom' && !empty($data['custom_days'])) {
                        DB::transaction(function () use ($record, $data) {
                            foreach ($data['custom_days'] as $day) {
                                $record->monthlyShiftDays()->create(['day' => $day]);
                            }
                        });
                    }
                }),
        ];
    }
}
