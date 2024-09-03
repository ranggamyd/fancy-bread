<?php

namespace App\Providers;

use Filament\Facades\Filament;
use Filament\Navigation\NavigationGroup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

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
        Model::unguard();

        Filament::serving(function () {
            Filament::registerNavigationGroups([
                NavigationGroup::make()->label('Fancy Master'),
                NavigationGroup::make()->label('Transaction'),
                NavigationGroup::make()->label('Miscellaneous'),
            ]);
        });
    }
}
