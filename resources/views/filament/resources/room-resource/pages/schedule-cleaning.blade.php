<x-filament-panels::page>
    <x-filament::section>
        <h2 class="text-xl font-semibold">
            Jadwalkan Pembersihan untuk Kamar #{{ $record->room_number }}
        </h2>

        <p class="mt-2">
            Status Saat Ini:
            <span
                class="px-2 py-1 text-sm font-medium rounded-full
                @if ($record->status === 'available') bg-green-100 text-green-800
                @elseif($record->status === 'occupied') bg-blue-100 text-blue-800
                @elseif($record->status === 'needs_cleaning') bg-yellow-100 text-yellow-800
                @elseif($record->status === 'maintenance') bg-red-100 text-red-800 @endif">
                @if ($record->status === 'available')
                    Tersedia
                @elseif($record->status === 'occupied')
                    Terisi
                @elseif($record->status === 'needs_cleaning')
                    Perlu Dibersihkan
                @elseif($record->status === 'maintenance')
                    Dalam Perbaikan
                @endif
            </span>
        </p>
    </x-filament::section>

    <form wire:submit="create">
        {{ $this->form }}
    </form>
</x-filament-panels::page>
