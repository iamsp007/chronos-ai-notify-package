<?php

namespace Iamsp007\ChronosAiNotify;

use Illuminate\Support\ServiceProvider;

class ChronosAiNotifyServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'chronos');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->publishes([
            __DIR__.'/../config/chronos.php' => config_path('chronos.php'),
        ], 'chronos-config');

        $this->publishes([
            __DIR__.'/../resources/js' => public_path('vendor/chronos'),
        ], 'chronos-assets');
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/chronos.php',
            'chronos'
        );
    }
}