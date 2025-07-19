<x-filament::page>
    <div class="space-y-6">
        {{-- @php
            dd($performanceData);
        @endphp --}}
        <x-filament::section>
            <x-slot name="heading">Filter Laporan</x-slot>
            <x-slot name="actions">
                @if ($performanceData)
                    <x-filament::button wire:click="generatePDF" icon="heroicon-o-document-arrow-down" color="success">
                        Export PDF
                    </x-filament::button>
                @endif
            </x-slot>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Filter controls tetap sama -->
                <div>
                    <label for="selectedStaff" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Pilih Staff
                    </label>
                    <select id="selectedStaff" wire:model.live="selectedStaff"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="">-- Pilih Staff --</option>
                        @foreach ($this->getStaffOptions() as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="periodStart" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Dari Tanggal
                    </label>
                    <input type="date" id="periodStart" wire:model.live="periodStart"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>

                <div>
                    <label for="periodEnd" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Sampai Tanggal
                    </label>
                    <input type="date" id="periodEnd" wire:model.live="periodEnd"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>
            </div>
        </x-filament::section>

        @php
            $performanceData = $this->getPerformanceData();
        @endphp

        @if ($performanceData['staff'])
            <x-filament::section>
                <x-slot name="heading">Ringkasan Kinerja {{ $performanceData['staff']->name }}</x-slot>
                <x-slot name="actions">
                    <x-filament::button wire:click="generatePDF" icon="heroicon-o-document-arrow-down" size="sm"
                        color="gray">
                        Export
                    </x-filament::button>
                </x-slot>

                <!-- Konten ringkasan kinerja tetap sama -->
                <div class="sm:flex gap-4">
                    <div class="w-full bg-white dark:bg-gray-800 rounded-lg shadow p-4 text-center">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Total Kamar Dibersihkan</span>
                        <div class="text-3xl font-bold text-primary-600 dark:text-primary-500 mt-1">
                            {{ $performanceData['totalRooms'] }}</div>
                    </div>

                    <div class="w-full bg-white dark:bg-gray-800 rounded-lg shadow p-4 text-center">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Rata-rata Waktu (Menit)</span>
                        <div class="text-3xl font-bold text-primary-600 dark:text-primary-500 mt-1">
                            {{ $performanceData['avgDuration'] }}</div>
                    </div>

                    <div class="w-full bg-white dark:bg-gray-800 rounded-lg shadow p-4 text-center">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Total Hari Hadir</span>
                        <div class="text-3xl font-bold text-primary-600 dark:text-primary-500 mt-1">
                            {{ $performanceData['presentCount'] }}
                        </div>
                    </div>

                    <div class="w-full bg-white dark:bg-gray-800 rounded-lg shadow p-4 text-center">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Permintaan Diselesaikan</span>
                        <div class="text-3xl font-bold text-primary-600 dark:text-primary-500 mt-1">
                            {{ $performanceData['requests'] }}</div>
                    </div>

                    <div class="w-full bg-white dark:bg-gray-800 rounded-lg shadow p-4 text-center">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Kamar per Hari</span>
                        <div class="text-3xl font-bold text-primary-600 dark:text-primary-500 mt-1">
                            {{ $performanceData['roomsPerDay'] }}</div>
                    </div>
                </div>
            </x-filament::section>

            <!-- Bagian evaluasi tetap sama -->
            <x-filament::section>
                <x-slot name="heading">Tambahkan Evaluasi</x-slot>

                <form wire:submit="saveEvaluation" class="space-y-4">
                    <div>
                        <label for="rating" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Penilaian (1-5)
                        </label>
                        <input type="number" id="rating" wire:model="rating" min="1" max="5"
                            step="0.1"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        @error('rating')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Catatan Evaluasi
                        </label>
                        <textarea id="notes" wire:model="notes" rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end mt-4">
                        <button type="submit"
                            class="inline-flex items-center justify-center py-1 gap-1 font-medium rounded-lg border transition-colors focus:outline-none focus:ring-offset-2 focus:ring-2 focus:ring-inset dark:focus:ring-offset-0 min-h-[2.25rem] px-4 text-sm text-white shadow focus:ring-white border-primary-600 bg-primary-600 hover:bg-primary-500 hover:border-primary-500 focus:bg-primary-700 focus:border-primary-700 focus:ring-offset-primary-700">
                            Simpan Evaluasi
                        </button>
                    </div>
                </form>
            </x-filament::section>
        @else
            <x-filament::section>
                <div class="p-4 text-center">
                    <p class="text-gray-500 dark:text-gray-400">Silakan pilih staff untuk melihat laporan kinerja</p>
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament::page>
