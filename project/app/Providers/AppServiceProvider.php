<?php

namespace App\Providers;

use Hyn\Tenancy\Environment;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        $env = app(Environment::class);

        if ($fqdn = optional($env->hostname())->fqdn) {
            config(['database.default' => 'tenant']);
        }

    }
}
