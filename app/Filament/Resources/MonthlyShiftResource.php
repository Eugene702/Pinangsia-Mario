<?php

namespace App\Filament\Resources;

use App\Exports\ShiftExport;
use App\Filament\Resources\MonthlyShiftResource\Pages;
use App\Models\MonthlyShift;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Tables\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MonthlyShiftResource extends Resource
{
    protected static ?string $model = MonthlyShift::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $modelLabel = 'Jadwal Kerja Staff';
    protected static ?string $navigationLabel = 'Jadwal Kerja Staff';
    protected static ?string $navigationGroup = 'Manajemen Housekeeping';
    protected static ?string $slug = 'jadwal-kerja';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('user_id')
                    ->label('Staff Housekeeping')
                    ->options(User::where('role', 'housekeeping')->pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->rules([
                        function ($get) {
                            return function (string $attribute, $value, $fail) use ($get) {
                                $month = $get('month');

                                if (!$month) {
                                    return;
                                }

                                // Parse tanggal dan format ke Y-m (tahun-bulan)
                                $monthYear = date('Y-m', strtotime($month));

                                $existingSchedule = MonthlyShift::where('user_id', $value)
                                    ->whereRaw("DATE_FORMAT(month, '%Y-%m') = ?", [$monthYear])
                                    ->when($get('id'), function ($query, $id) {
                                        $query->where('id', '!=', $id);
                                    })
                                    ->exists();

                                if ($existingSchedule) {
                                    $fail('Staff ini sudah memiliki jadwal shift di bulan yang dipilih.');
                                }
                            };
                        }
                    ]),

                DatePicker::make('month')
                    ->label('Bulan')
                    ->format('Y-m-d')
                    ->displayFormat('F Y')
                    ->required()
                    ->rules([
                        function ($get) {
                            return function (string $attribute, $value, $fail) use ($get) {
                                $userId = $get('user_id');

                                if (!$userId) {
                                    return;
                                }

                                $monthYear = date('Y-m', strtotime($value));

                                $existingSchedule = MonthlyShift::where('user_id', $userId)
                                    ->whereRaw("DATE_FORMAT(month, '%Y-%m') = ?", [$monthYear])
                                    ->when($get('id'), function ($query, $id) {
                                        $query->where('id', '!=', $id);
                                    })
                                    ->exists();

                                if ($existingSchedule) {
                                    $fail('Staff yang dipilih sudah memiliki jadwal shift di bulan ini.');
                                }
                            };
                        }
                    ]),

                Select::make('shift_pattern')
                    ->label('Pola Shift')
                    ->options([
                        'regular' => 'Shift Regular (Pagi: Senin-Jumat)',
                        'custom' => 'Shift Custom'
                    ])
                    ->required()
                    ->columnSpanFull()
                    ->live(),

                Radio::make('shift_data')
                    ->label('Shift Custom')
                    ->options([
                        'pagi' => 'Shift Pagi (07:00-17:00)',
                        'siang' => 'Shift Siang (16:00-01:00)',
                        'malam' => 'Shift Malam (00:00-08:00)'
                    ])
                    ->hidden(fn(Forms\Get $get) => $get('shift_pattern') !== 'custom')
                    ->required(fn(Forms\Get $get) => $get('shift_pattern') === 'custom')
                    ->columnSpanFull()
                    ->columns(3),

                CheckboxList::make('custom_days')
                    ->label('Pilih Hari untuk Shift Custom')
                    ->options([
                        '1' => 'Senin',
                        '2' => 'Selasa',
                        '3' => 'Rabu',
                        '4' => 'Kamis',
                        '5' => 'Jumat',
                        '6' => 'Sabtu',
                        '7' => 'Minggu',
                    ])
                    ->columns(4)
                    ->gridDirection('row')
                    ->hidden(fn(Forms\Get $get) => $get('shift_pattern') !== 'custom')
                    ->required(fn(Forms\Get $get) => $get('shift_pattern') === 'custom')
                    ->columnSpanFull(),

                Textarea::make('notes')
                    ->label('Catatan')
                    ->columnSpanFull()
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['user', 'monthlyShiftDays']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Staff')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('month')
                    ->label('Bulan')
                    ->date('F Y')
                    ->sortable(),

                TextColumn::make('shift_type')
                    ->label('Tipe Shift')
                    ->description(fn(MonthlyShift $record) => $record->notes),
            ])
            ->filters([
                SelectFilter::make('month')
                    ->label('Filter Bulan')
                    ->options(function () {
                        return MonthlyShift::query()
                            ->selectRaw('DISTINCT month')
                            ->get()
                            ->pluck('month', 'month')
                            ->map(fn($date) => \Carbon\Carbon::parse($date)->translatedFormat('F Y'));
                    })
                    ->searchable(),

                SelectFilter::make('user_id')
                    ->label('Filter Staff')
                    ->options(User::where('role', 'housekeeping')->pluck('name', 'id'))
                    ->searchable()
            ])
            ->headerActions([
                Tables\Actions\Action::make('exportExcel')
                    ->label('Export Excel')
                    ->icon('heroicon-o-document-text')
                    ->color('success')
                    ->action(function () {
                        $month = request()->input('tableFilters.month.value');
                        return Excel::download(new ShiftExport($month), 'jadwal-shift-' . ($month ?: 'all') . '.xlsx');
                    }),

                Tables\Actions\Action::make('exportPdf')
                    ->label('Export PDF')
                    ->icon('heroicon-o-document')
                    ->color('danger')
                    ->action(function () {
                        $month = request()->input('tableFilters.month.value');

                        $shifts = MonthlyShift::query()
                            ->with('user')
                            ->when($month, fn($q) => $q->where('month', $month))
                            ->get()
                            ->map(function ($shift) {
                                // dd($shift);
                                return [
                                    'staff' => preg_replace('/[^\x20-\x7E]/', '', $shift->user->name),
                                    'pattern' => $shift->shift_pattern === 'regular' ? 'Regular' : 'Custom',
                                    'shifts' => $shift->shift_pattern === 'regular'
                                        ? 'Pagi (Senin-Jumat)'
                                        : $shift->shift_data,
                                ];
                            });

                        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('shifts_pdf', [
                            'shifts' => $shifts,
                            'month' => $month ? date('F Y', strtotime($month)) : 'Semua Bulan',
                            'printed_date' => date('d/m/Y H:i')
                        ]);

                        // return $pdf->download('jadwal-shift.pdf');
                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->stream();
                        }, 'jadwal-shift-' . ($month ?: 'all') . '.pdf');
                    }),
            ])
            ->actions([
                DeleteAction::make(),
                Tables\Actions\EditAction::make()
                    ->fillForm(function (MonthlyShift $record): array {
                        $data = $record->toArray();
                        $data['custom_days'] = $record->monthlyShiftDays->pluck('day')->toArray();
                        return $data;
                    })
                    ->after(function (MonthlyShift $record, array $data) {
                        if ($data['shift_pattern'] === 'custom') {
                            DB::transaction(function () use ($record, $data) {
                                $record->monthlyShiftDays()->delete();
                                if (!empty($data['custom_days'])) {
                                    foreach ($data['custom_days'] as $day) {
                                        $record->monthlyShiftDays()->create(['day' => $day]);
                                    }
                                }
                            });
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageMonthlyShifts::route('/'),
        ];
    }
}
