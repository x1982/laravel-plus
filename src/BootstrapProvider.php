<?php
namespace Landers\LaravelPlus;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class BootstrapProvider extends LaravelServiceProvider
{
    private $providers = [

    ];

    public function boot( )
    {
        foreach ($this->providers as $item) {
            $this->app->register($item);
        }
    }
}