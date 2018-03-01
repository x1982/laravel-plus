<?php
namespace Landers\LaravelPlus\Traits;

use Illuminate\Support\Str as Str1;
use Landers\Substrate2\Utils\Str as Str2;
use Landers\LaravelPlus\Utils\Filesystem;

trait ReportRunLogTrait
{
    private $reportWays = [];

    /**
     * 上报日志
     * @param array|string $data
     */
    protected function reportLog( $data, $ways = [] )
    {
        $data = is_array($data) ? implode("\n", $data) : $data;

        $ways or $ways = $this->getReportWays();

        array_walk($ways, function($type, $way) use (&$data) {
            try {
                switch ($type) {
                    case 'FUNCTION':
                        if ( function_exists($way) ){
                            $way($data);
                        }
                        break;

                    case 'LOCAL_FILE':
                        $filename = $this->buildLogFilename($way);
                        app(Filesystem::class)->append($filename, $data);
                }
            } catch (\Exception $e) {
                // todo 暂未定
            }
        });
    }

    /**
     * 取得上报途径
     * @return array
     */
    private function getReportWays()
    {
        if ( method_exists($this,'reportWays') ) {
            return (array)$this->reportWays();
        }

        if ( $this->reportWays ) {
            return (array)$this->reportWays;
        }

        return [];
    }


    /**
     * 取得当前运行的应用程序
     * @return string
     */
    private function getReportAppName()
    {

        $app_name = Str1::snake(static::class);
        $app_name = str_replace([':', '\\'], ['-', ''], $app_name);
        return $app_name;
    }


    /**
     * 生成日志文件路径
     * @param string $tpl_filename
     * @return string
     */
    private function buildLogFilename( string $tpl_filename )
    {
        $replaces = [
            'app_name' => $this->getReportAppName(),
            'date' => date('Y-m-d'),
            'sapi' => php_sapi_name(),
        ];

        $filename = Str2::replace($tpl_filename, $replaces);

        return base_path($filename);
    }
}