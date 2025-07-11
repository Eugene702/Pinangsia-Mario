<div class="p-4">
    <h3 class="text-lg font-bold mb-4">
        Jadwal {{ $schedule->user->name }} - {{ $schedule->month->format('F Y') }}
    </h3>

    <div class="grid grid-cols-7 gap-2">
        @foreach ($schedule->month->daysUntil($schedule->month->copy()->endOfMonth()) as $day)
            @php
                $shift = $schedule->schedule_data[$day->day] ?? null;
                $bgColor = match ($shift) {
                    'pagi' => 'bg-blue-100',
                    'siang' => 'bg-green-100',
                    'malam' => 'bg-yellow-100',
                    'libur' => 'bg-red-100',
                    default => 'bg-gray-100',
                };
            @endphp

            <div class="border rounded p-2 {{ $bgColor }}">
                <div class="font-bold">{{ $day->format('d') }}</div>
                <div class="text-sm">{{ $shift ?: '-' }}</div>
            </div>
        @endforeach
    </div>
</div>
