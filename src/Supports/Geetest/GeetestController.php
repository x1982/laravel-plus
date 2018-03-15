<?php
namespace Landers\LaravelPlus\Supports\Geetest;

use Illuminate\Routing\Controller as LaravelController;

class GeetestController extends LaravelController
{
    private $repo;

    public function __construct(GeetestRepository $geetestRepository)
    {
        $this->repo = $geetestRepository;
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