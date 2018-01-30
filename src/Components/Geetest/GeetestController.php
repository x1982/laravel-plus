<?php
/**
 * Created by PhpStorm.
 * User: hdd
 * Date: 17-10-24
 * Time: 上午11:33
 */

namespace Landers\LaravelPlus\Components\Geetest;

use Illuminate\Support\Facades\Session;
use Landers\LaravelAms\Constraints\Controllers\BaseController;
use Landers\LaravelPlus\Facades\Cache;

class GeetController extends BaseController
{
    /**
     * 取得geetest验证码
     *
     * @return mixed
     */
    public function captcha()
    {
        $config = config('geetest');
        if ( !$config ) {
            self::throwGeneralException('未配置极验参数');
        }

        $status = $this->service
            ->config(array_get($config, 'CAPTCHA_ID'), array_get($config, 'PRIVATE_KEY'))
            ->pre_process(Session::getId());

        Cache::forever('gtserver', $status);

        return $this->service->get_response_str();
    }

}