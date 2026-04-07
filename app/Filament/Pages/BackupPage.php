<?php

namespace App\Filament\Pages;

use App\Models\BackupLog;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;

class BackupPage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = null;
    protected static ?string $navigationLabel = 'Backup & Restore';
    protected static string|\UnitEnum|null $navigationGroup = 'Konfigurasi';
    protected static ?int $navigationSort = 5;
    protected string $view = 'filament.pages.backup-page';
    protected static ?string $title = 'Backup & Restore';

    public array $backupFiles = [];

    public function mount(): void
    {
        $this->loadBackups();
    }

    /**
     * Muat daftar file backup dari storage.
     */
    public function loadBackups(): void
    {
        $this->backupFiles = BackupLog::latest()->limit(20)->get()->toArray();
    }

    /**
     * Jalankan backup sekarang menggunakan Artisan command dari spatie/laravel-backup.
     */
    public function runBackup(): void
    {
        // Hanya admin yang boleh backup
        if (! Auth::user()?->isAdmin()) {
            Notification::make()->title('Anda tidak memiliki akses.')->danger()->send();
            return;
        }

        try {
            Artisan::call('backup:run', ['--only-db' => true]);

            // Catat ke backup_logs
            BackupLog::create([
                'created_by' => Auth::id(),
                'file_name'  => 'backup-' . now()->format('Y-m-d-H-i-s') . '.zip',
                'file_path'  => 'backups',
                'file_size'  => 0,
                'status'     => 'success',
                'notes'      => 'Manual backup via Admin Panel',
            ]);

            $this->loadBackups();
            Notification::make()->title('Backup berhasil dibuat!')->success()->send();
        } catch (\Exception $e) {
            Log::error('Backup failed: ' . $e->getMessage());
            Notification::make()->title('Backup gagal: ' . $e->getMessage())->danger()->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('backup')
                ->label('Backup Sekarang')
                ->icon('heroicon-o-arrow-down-on-square')
                ->color('success')
                ->requiresConfirmation()
                ->action('runBackup'),
        ];
    }
}
