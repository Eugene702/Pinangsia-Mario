<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventoryBorrowingResource\Pages;
use App\Exports\BorrowingHistoryExport;
use App\Models\InventoryBorrowing;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Maatwebsite\Excel\Facades\Excel;

class InventoryBorrowingResource extends Resource
{
    protected static ?string $model = InventoryBorrowing::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationGroup = 'Manajemen Inventaris';
    protected static ?string $navigationLabel = 'History Pemakaian';
    protected static ?string $modelLabel = 'History Pemakaian Barang';
    protected static ?string $slug = 'history-pemakaian-barang';
    protected static ?string $pluralModelLabel = 'History Pemakaian Barang';
    // Hide create/edit pages for this resource
    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->history())
            ->columns([
                Tables\Columns\TextColumn::make('inventory.name')
                    ->label('Nama Barang')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('borrowed_quantity')
                    ->label('Jumlah')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Peminjam')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('borrowed_at')
                    ->label('Tgl Pinjam')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('returned_at')
                    ->label('Tgl Kembali')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'borrowed' => 'warning',
                        'returned' => 'success',
                        'damaged' => 'danger',
                        'lost' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'borrowed' => 'Dipinjam',
                        'returned' => 'Dikembalikan',
                        'damaged' => 'Rusak',
                        'lost' => 'Hilang',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('notes')
                    ->label('Catatan')
                    ->limit(30)
                    ->tooltip(fn($record) => $record->notes),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'borrowed' => 'Dipinjam',
                        'returned' => 'Dikembalikan',
                        'damaged' => 'Rusak',
                        'lost' => 'Hilang',
                    ]),

                Tables\Filters\Filter::make('borrowed_at')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['from'],
                                fn($q) => $q->whereDate('borrowed_at', '>=', $data['from'])
                            )
                            ->when(
                                $data['until'],
                                fn($q) => $q->whereDate('borrowed_at', '<=', $data['until'])
                            );
                    })
            ])
            ->actions([
                // Tidak perlu actions untuk riwayat
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Export Excel untuk data terpilih
                    Tables\Actions\BulkAction::make('exportSelectedExcel')
                        ->label('Export Selected (Excel)')
                        ->icon('heroicon-o-arrow-down-on-square')
                        ->action(function ($records) {
                            return Excel::download(
                                new BorrowingHistoryExport($records),
                                'riwayat-peminjaman-selected-' . now()->format('Y-m-d') . '.xlsx'
                            );
                        }),

                    // Export PDF untuk data terpilih
                    Tables\Actions\BulkAction::make('exportSelectedPdf')
                        ->label('Export Selected (PDF)')
                        ->icon('heroicon-o-document-arrow-down')
                        ->action(function ($records) {
                            $pdf = Pdf::loadView('borrowing_history_pdf', [
                                'borrowings' => $records,
                                'title' => 'Riwayat Peminjaman (Selected)'
                            ]);

                            return response()->streamDownload(
                                fn() => print($pdf->output()),
                                'riwayat-peminjaman-selected-' . now()->format('Y-m-d') . '.pdf'
                            );
                        }),
                ]),
            ])
            ->headerActions([
                // Export Excel semua data
                Tables\Actions\Action::make('exportAllExcel')
                    ->label('Export All (Excel)')
                    ->icon('heroicon-o-arrow-down-on-square')
                    ->action(function () {
                        return Excel::download(
                            new BorrowingHistoryExport(),
                            'riwayat-peminjaman-all-' . now()->format('Y-m-d') . '.xlsx'
                        );
                    }),

                // Export PDF semua data
                Tables\Actions\Action::make('exportAllPdf')
                    ->label('Export All (PDF)')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function () {
                        $borrowings = InventoryBorrowing::history()->get();
                        $pdf = Pdf::loadView('borrowing_history_pdf', [
                            'borrowings' => $borrowings,
                            'title' => 'Riwayat Peminjaman (All)'
                        ]);

                        return response()->streamDownload(
                            fn() => print($pdf->output()),
                            'riwayat-peminjaman-all-' . now()->format('Y-m-d') . '.pdf'
                        );
                    }),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventoryBorrowings::route('/'),
            // 'create' => Pages\CreateInventoryBorrowing::route('/create'),
            // 'edit' => Pages\EditInventoryBorrowing::route('/{record}/edit'),
        ];
    }
}
