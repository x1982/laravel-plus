<?php
namespace Landers\LaravelPlus\Components\Geetest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class GeetestRepository
{
    private $instance;

    public function __construct()
    {
        require_once __DIR__ . '/Lib/GeetestLib.php';

        $config = config('geetest');
        if ( !$config ) {
            throw new \Exception('未配置极验参数');
        }

        $captcha_id = array_get($config, 'CAPTCHA_ID') or
        $captcha_id = array_get($config, 'captcha_id');

        $private_key = array_get($config, 'PRIVATE_KEY') or
        $private_key = array_get($config, 'private_key');

        $this->instance = new \GeetestLib($captcha_id, $private_key);
    }

    /**
     * 为显示验证码界面提供参数
     * @return mixed
     */
    public function show()
    {
        $status = $this->instance->pre_process(Session::getId());

        Cache::forever('gtserver', $status);

        return $this->instance->get_response_str();
    }

    /**
     * 验证验证码
     *
     * @param array $data
     * @param string $error_key 用于表单验证：错误信息的键
     * @param mixed $message 验证错误信息
     * @throws ValidationException
     */
    public function validate(array $data, string $error_key = 'captcha', $message = true)
    {
        if ($message === true) {
            $message = '滑块验证失效，请重新刷新滑块验证！';
        }

        $challenge = array_get($data,'geetest_challenge');
        $validate  = array_get($data,'geetest_validate');
        $seccode   = array_get($data, 'geetest_seccode');

        if ((int)Cache::get('gtserver') === 1) {
            $result = $this->instance->success_validate($challenge, $validate, $seccode);
            if (! $result) {
                throw ValidationException::withMessages([$error_key => $message]);
            }
        } else {
            $result = $this->instance->fail_validate($challenge, $validate, $seccode);
            if (! $result) {
                throw ValidationException::withMessages([$error_key => $message]);
            }
        }
    }

}
