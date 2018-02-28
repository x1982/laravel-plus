<?php
namespace Landers\LaravelPlus\Traits;

use Illuminate\Support\Facades\Cache;
use Landers\Substrate2\Classes\Http;

trait HttpRequestTrait
{
    /**
     * @var Http
     */
    private $httpClient;

    /**
     * 取得 httpClient 实例
     *
     * @return Http
     */
    private function httpClient()
    {
        $httpClient = &$this->httpClient;

        if ( !$httpClient ) $httpClient = new Http();

        return $httpClient;
    }

    /**
     * 登陆
     * @param string $url
     * @param array $data
     * @param $content
     * @return mixed
     * @throws \Exception
     */
    private function requestLogin( string $url, array $data = [], &$content = null )
    {
        if (
            (!$cookies = $this->getCacheCookie()) ||
            (func_num_args() === 3)
        ){
            $this->httpClient()->post( $url, $data );
            $content = $this->httpClient->contents();
            if ( !$this->httpClient->success() ) {
                throw new \Exception('登陆失败');
            }

            $cookies = $this->httpClient->cookies();
            $this->setCacheCookie( $cookies );
        }

        return $cookies;
    }

    /**
     * @param string $url
     * @param array $params
     * @return bool
     */
    private function requestGet( string $url, array $params = [])
    {
        $cookies = $this->getCacheCookie();
        $this->httpClient()->withCookies($cookies)->get($url, $params);
        if ( !$this->httpClient->success() ) {
            return false;
        }

        return $this->httpClient->contents();
    }

    /**
     * @param string $url
     * @param array $data
     * @return bool
     */
    private function requestPost(string $url, array $data = [])
    {
        $cookies = $this->getCacheCookie();
        $this->httpClient()->withCookies($cookies)->post($url, $data);
        if ( !$this->httpClient->success() ) {
            return false;
        }

        return $this->httpClient->contents();
    }

    /**
     * @param string $url
     * @param array $data
     * @return bool
     */
    private function requestPostJson(string $url, array $data = [])
    {
        $cookies = $this->getCacheCookie();
        $this->httpClient()->withCookies($cookies)->postJson($url, $data);
        if ( !$this->httpClient->success() ) {
            return false;
        }

        return $this->httpClient->contents();
    }

    /**
     * @param bool $bool
     * @return $this
     */
    private function requestDebug(bool $bool = true)
    {
        $this->httpClient()->debug($bool);
        return $this;
    }

    /**
     * cookie键名
     * @return string
     */
    private function cookieKey()
    {
        return static::class . '_cookie';
    }

    /**
     * 取得模拟cookie(缓存中)
     * @return mixed
     */
    private function getCacheCookie()
    {
        $cookies = Cache::get($this->cookieKey());
        return json_decode( $cookies, true ) ?: [];
    }

    /**
     * 设置模拟cookie(缓存中)
     * @param $cookies
     * @param int $minutes
     */
    private function setCacheCookie( $cookies, int $minutes = 60 )
    {
        if ( is_array($cookies) ) {
            $cookies = json_encode($cookies);
        }

        $key = $this->cookieKey();
        if ( is_null($cookies) ) {
            Cache::forget($key);
        } else {
            Cache::put($key, $cookies, $minutes);
        }
    }
}