<?php
namespace Landers\LaravelPlus;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Route;

class BootstrapProvider extends RouteServiceProvider
{
    public function map()
    {
        Route::middleware('web')
            ->group(__DIR__ . '/routes.php');
    }
}