<?php

namespace App\Providers\Filament;

use App\Filament\Resources\ApplicationInterviewResource;
use App\Filament\Widgets\ActiveMobilitiesWidget;
use App\Filament\Widgets\ConsortiumStatsOverviewWidget;
use App\Filament\Widgets\LatestApplicationsWidget;
use App\Filament\Widgets\MobilitiesByCountryChartWidget;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
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
            ->login()
            ->brandName('Consorci Erasmus')
            ->colors([
                'primary' => Color::Blue,
                'accent' => Color::Amber,
            ])
            ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('18rem')
            ->maxContentWidth(MaxWidth::ScreenTwoExtraLarge)
            ->navigationGroups([
                NavigationGroup::make()->label('Consorcio y Centros'),
                NavigationGroup::make()->label('Convocatorias y Baremos'),
                NavigationGroup::make()->label('Candidaturas'),
                NavigationGroup::make()->label('Movilidades y Estancias'),
                NavigationGroup::make()->label('Socios y Empresas'),
            ])
            ->navigationItems([
                NavigationItem::make('Crear entrevista')
                    ->group('Candidaturas')
                    ->icon('heroicon-o-plus-circle')
                    ->sort(2)
                    ->url(fn (): string => ApplicationInterviewResource::getUrl('create')),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                ConsortiumStatsOverviewWidget::class,
                ActiveMobilitiesWidget::class,
                MobilitiesByCountryChartWidget::class,
                LatestApplicationsWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
