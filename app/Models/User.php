<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;


class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasRoles;


    /**
     * Kolom yang boleh diisi massal.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'address',
        'pin_code',
        'is_active',
    ];

    /**
     * Kolom yang disembunyikan saat serialisasi.
     */
    protected $hidden = [
        'password',
        'remember_token',
        'pin_code',
    ];

    /**
     * Cast atribut ke tipe yang sesuai.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'pin_code'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    // =====================================================================
    // SCOPES
    // =====================================================================

    /**
     * Filter hanya user yang aktif.
     */
    public function scopeActive($query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Filter berdasarkan role tertentu.
     */
    public function scopeRole($query, string $role): void
    {
        $query->where('role', $role);
    }

    // =====================================================================
    // HELPERS
    // =====================================================================

    /**
     * Cek apakah user adalah super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * Cek apakah user adalah admin atau super admin.
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['super_admin', 'admin']);
    }

    /**
     * Cek apakah user memiliki salah satu role yang diberikan.
     */
    public function hasRole(string|array $roles): bool
    {
        $roles = (array) $roles;
        return in_array($this->role, $roles);
    }

    /**
     * Akses ke Filament panel — hanya user aktif dengan role tertentu.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        $canAccess = $this->is_active && in_array($this->role, ['admin', 'cashier']);
        
        \Illuminate\Support\Facades\Log::info('Filament Access Check:', [
            'email'      => $this->email,
            'role'       => $this->role,
            'is_active'  => $this->is_active,
            'can_access' => $canAccess
        ]);

        return $canAccess;
    }

    // =====================================================================
    // RELATIONS
    // =====================================================================

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'user_id');
    }

    public function voidedTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'voided_by');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'user_id');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(StaffActivityLog::class, 'user_id');
    }

    public function backupLogs(): HasMany
    {
        return $this->hasMany(BackupLog::class, 'created_by');
    }
}
