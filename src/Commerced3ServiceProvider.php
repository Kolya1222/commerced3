<?php

namespace roilafx\Commerced3;

use EvolutionCMS\ServiceProvider;

class Commerced3ServiceProvider extends ServiceProvider
{
    protected $namespace = 'commerced3';
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->registerRoutingModule(
            'CommerceD3',
            __DIR__ . '/../routes.php',
            'fa fa-trash'
        );
    }
    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/../views', $this->namespace);
        $this->publishes([ 
            __DIR__ . '/../publishable/assets'  => MODX_BASE_PATH . 'assets',
        ]);
    }
}
