<?php

namespace Modules\Software\Providers;

use Caffeinated\Modules\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the module services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadTranslationsFrom(module_path('software', 'Resources/Lang', 'app'), 'software');
        $this->loadViewsFrom(module_path('software', 'Resources/Views', 'app'), 'software');
        $this->loadMigrationsFrom(module_path('software', 'Database/Migrations', 'app'), 'software');
        $this->loadConfigsFrom(module_path('software', 'Config', 'app'));
        $this->loadFactoriesFrom(module_path('software', 'Database/Factories', 'app'));
    }

    /**
     * Register the module services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
    }
}
