<?php
namespace Landers\LaravelPlus\Traits;

use Illuminate\Support\Str;
use Landers\LaravelPlus\Utils\Filesystem;
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
                // 日志内容
                $content = implode("\n", $data) . str_repeat(PHP_EOL, 3);
                $canReport = false;

                //上报日志
                if ( function_exists('dispatch_logger')) {
                    dispatch_logger($content);
                    $canReport = true;
                }

                if (method_exists($this, 'reportLog')) {
                    $this->reportLog($content);
                    $canReport = true;
                }

                if ( !$canReport ) {
                    $app_name = strtolower(str_replace([':', '\\'], ['-', ''], Str::snake(static::class)));
                    $today = date('Y-m-d');
                    $filename = storage_path("logs/cli/{$today}/{$app_name}.log");
                    app(Filesystem::class)->put($filename, $content, FILE_APPEND);
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