<?php

namespace App\Providers\Filament;

use App\Filament\Pages\BackupPage;
use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\Auth\Register;
use App\Filament\Resources\CategoryResource;
use App\Filament\Resources\PrinterConfigResource;
use App\Filament\Resources\ProductResource;
use App\Filament\Resources\StockMovementResource;
use App\Filament\Resources\StoreSettingResource;
use App\Filament\Resources\TaxSettingResource;
use App\Filament\Resources\TransactionResource;
use App\Filament\Resources\UserResource;
use App\Filament\Widgets\RecentTransactionsWidget;
use App\Filament\Widgets\RevenueChartWidget;
use App\Filament\Widgets\SalesOverviewWidget;
use App\Filament\Widgets\TopProductsWidget;
use App\Models\User;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(Login::class)
            ->registration(Register::class)
            ->colors([
            'primary' => '#006D5B',
            'gray' => Color::Slate,
        ])
            ->brandName('CAFE KASIRIN AJA')
            ->brandLogo(fn () => asset('images/logo.png'))
            ->brandLogoHeight('3rem')
            ->favicon(fn () => asset('images/logo.png'))
            ->font('Outfit')
            ->navigationGroups([
                NavigationGroup::make('Produk & Stok')
                    ->icon('heroicon-o-shopping-bag'),
                NavigationGroup::make('Transaksi')
                    ->icon('heroicon-o-shopping-cart'),
                NavigationGroup::make('Laporan')
                    ->icon('heroicon-o-chart-bar'),
                NavigationGroup::make('Konfigurasi')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->collapsed(),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                SalesOverviewWidget::class,
                RevenueChartWidget::class,
                TopProductsWidget::class,
                RecentTransactionsWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->authGuard('web');
    }
}
