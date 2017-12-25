<?php
namespace Landers\LaravelPlus\Facades;

use Illuminate\Support\Facades\Redis as LaravelRedis;

class Redis extends LaravelRedis
{
    /**
     * 与Redis中的值比较
     * @param $key
     * @param $value
     * @return bool
     */
    public static function compareWithSet($key, $value) {
        $value = json_encode($value);
        $old_value = self::get($key);
        if ( $old_value === $value ) {
            return true;
        } else {
            self::set($key, $value);
            if ( is_null($old_value) ) {
                //首次写入, 比较结果认为是一致
                return true;
            } else {
                return false;
            }
        }
    }
}
