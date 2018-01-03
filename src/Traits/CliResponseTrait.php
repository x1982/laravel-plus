<?php
namespace Landers\LaravelPlus\Traits;

use Landers\Substrate2\Classes\CliResponse;

Trait CliResponseTrait
{

    /**
     * @var CliResponse
     */
    protected $response;

    /**
     * @var
     */
    protected $reportFormat;

    /**
     * 初始化 response
     */
    private function initResponse() {
        if ($this->response) return;
        $this->response = new CliResponse();
        $this->response->clear()->setOptions([
            'report' => function($data) {
                if (method_exists($this, 'reportLog')) {
                    $this->reportLog($data);
                }
            }
        ]);
    }

    /**
     * 命令完成
     */
    protected function cliComplete() {
        $this->response->complete();
    }

    /**
     * 命令开始
     */
    protected function cliStart()
    {
        $this->initResponse();
        $job_name = $this->getJobName();

        $msg = "【{$job_name}】启动工作";
        $this->response->start($msg);
    }

    /**
     * 命令中断
     * @return bool
     */
    protected function cliHalt() {
        return $this->response->halt();
    }

    /**
     * 取得任务名称
     * @return mixed
     */
    private function getJobName()
    {
        if ( property_exists($this, 'description')) {
            if ( isset($this->description) ) {
                return $this->description;
            }
        }
        if ( property_exists($this, 'name')) {
            if ( isset($this->name) ) {
                return $this->name;
            }
        }

        return static::class;
    }
}