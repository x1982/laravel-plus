<?php
namespace Landers\LaravelPlus\Facades;

use Illuminate\Support\Facades\Cache as LaravelCache;

class Cache extends LaravelCache
{
    public static function compare( $cache_key, $value )
    {
        $cache_value = parent::get($cache_key);
        if ( !is_null($cache_value) && $cache_value === $value ) {
            return true;
        } else {
            return false;
        }
    }
}