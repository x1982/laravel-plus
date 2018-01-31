<?php
namespace Landers\LaravelPlus\Components\Geetest;

use Landers\LaravelAms\Constraints\Controllers\BaseController;

class GeetestController extends BaseController
{

    public function __construct(GeetestRepository $geetestRepository)
    {
        $this->repo = $geetestRepository;
        parent::__construct();
    }

    /**
     * 取得geetest验证码
     *
     * @return mixed
     */
    public function captcha()
    {
        return $this->repo->show();
    }

}