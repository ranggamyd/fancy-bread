<?php

namespace App\Providers;

use Filament\Facades\Filament;
use Filament\Support\Assets\Css;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Filament\Navigation\NavigationGroup;
use Filament\Support\Facades\FilamentAsset;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->environment('production')) URL::forceScheme('https');

        Model::unguard();

        DB::statement("SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");

        FilamentAsset::register([
            Css::make('custom-stylesheet', __DIR__ . '/../../resources/css/app.css'),
        ]);
        
        Filament::serving(function () {
            Filament::registerNavigationGroups([
                NavigationGroup::make()->label('Fancy Master'),
                NavigationGroup::make()->label('Purchases'),
                NavigationGroup::make()->label('Sales'),
                NavigationGroup::make()->label('Miscellaneous'),
            ]);
        });
    }
}
