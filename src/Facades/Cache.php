<?php
namespace Landers\LaravelPlus\Facades;

use Illuminate\Support\Facades\Cache as LaravelCache;

class Cache extends LaravelCache
{
    private static $verification_prefix_key = 'cache_verification';

    /**
     * [driver description]
     * @param  string $driver [description]
     * @return [type]         [description]
     */
    private static function driver(string $driver = 'file')
    {
        return parent::store($driver);
    }

    /**
     * 作比较
     * @param $cache_key
     * @param $value
     * @return bool
     */
    public static function compare( $cache_key, $value )
    {
        $cache_value = self::driver()->get($cache_key);
        if ( !is_null($cache_value) && $cache_value === $value ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 取得验证用的缓存
     *
     * @param $key
     */
    public static function getForVerification(string $key)
    {
        $key = self::$verification_prefix_key . '_' . $key;
        return self::driver()->get($key);
    }

    /**
     * 设置验证用的缓存
     *
     * @param string $key
     * @param $data
     * @param $expireMinute
     */
    public static function putForVerification(string $key, $data, $expireMinute)
    {
        $key = self::$verification_prefix_key . '_' . $key;
        return self::driver()->set($key, $data, $expireMinute);
    }

    /**
     * 清除验证用的缓存
     *
     * @param string $key
     */
    public static function forgetForVerification(string $key)
    {
        $key = self::$verification_prefix_key . '_' . $key;
        return self::driver()->forget($key);
    }

    /**
     * 清除验证用的缓存
     *
     * @param string $key
     * @param $data
     * @param $expireMinute
     */
    public static function compareForVerification(string $key, $data)
    {
        $key = self::$verification_prefix_key . '_' . $key;
        return self::compare($key, $data);
    }
}