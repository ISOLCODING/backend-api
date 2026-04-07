<x-filament-panels::page>

    <div class="space-y-6">
        {{-- Info Card --}}
        <x-filament::section>
            <x-slot name="heading">Informasi Backup</x-slot>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Backup database akan menyimpan seluruh data transaksi, produk, dan pengaturan toko.
                File backup disimpan di folder <code>storage/app/backup</code>.
            </p>
        </x-filament::section>

        {{-- Daftar Backup --}}
        <x-filament::section>
            <x-slot name="heading">Riwayat Backup (20 Terakhir)</x-slot>

            @if(count($backupFiles) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-4 py-2">File</th>
                                <th class="px-4 py-2">Status</th>
                                <th class="px-4 py-2">Oleh</th>
                                <th class="px-4 py-2">Waktu</th>
                                <th class="px-4 py-2">Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($backupFiles as $backup)
                            <tr class="border-t dark:border-gray-700">
                                <td class="px-4 py-2 font-mono">{{ $backup['file_name'] }}</td>
                                <td class="px-4 py-2">
                                    <span class="px-2 py-1 text-xs rounded {{ $backup['status'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                        {{ ucfirst($backup['status']) }}
                                    </span>
                                </td>
                                <td class="px-4 py-2">{{ $backup['notes'] ?? '-' }}</td>
                                <td class="px-4 py-2">{{ \Carbon\Carbon::parse($backup['created_at'])->format('d M Y, H:i') }}</td>
                                <td class="px-4 py-2 text-gray-500">{{ $backup['notes'] ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-500 text-sm">Belum ada riwayat backup. Klik tombol "Backup Sekarang" untuk membuat backup pertama.</p>
            @endif
        </x-filament::section>
    </div>

</x-filament-panels::page>
