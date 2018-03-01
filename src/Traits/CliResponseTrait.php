<?php
namespace Landers\LaravelPlus\Traits;

use Illuminate\Support\Str;
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
     * @return CliResponse
     */
    public function initResponse() {
        if ($this->response) {
            return $this->response;
        }
        $this->response = new CliResponse();
        return $this->response->clear()->setOptions([
            'report' => function($data) {
                if (method_exists($this, 'reportLog')) {
                    $data = array_merge($data, ["\n", "\n", "\n"]);
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
        $job_type = $this->getJobType();
        $job_name = $job_type ? "{$job_type}: {$job_name}" : $job_name;

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

    /**
     * 取得任务类型：Command、Job、Listener
     * @return string
     */
    private function getJobType()
    {
        $snake = Str::snake(static::class, '_');
        $arr = explode('_', $snake);
        $suffix = last($arr);
        $types = [
            'command' => '命令',
            'job' => '队列任务',
            'listener' => '监听器'
        ];

        return array_get($types, $suffix, '');
    }
}