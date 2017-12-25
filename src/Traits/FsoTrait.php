<?php
namespace Landers\LaravelPlus\Traits;

use Landers\LaravelPlus\Utils\Filesystem;

trait FsoTrait
{

    /**
     * @var Filesystem
     */
    private static $_fso;

    /**
     * 从容器对象中取得fso对象
     * @return \Illuminate\Foundation\Application|mixed
     */
    private function fso()
    {
        if ( !self::$_fso ) {
            self::$_fso = app(Filesystem::class);
        }

        return self::$_fso;
    }
}