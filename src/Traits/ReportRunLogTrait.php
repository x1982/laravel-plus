<?php
namespace Landers\LaravelPlus\Traits;

use Illuminate\Support\Str;
use Landers\LaravelPlus\Utils\Filesystem;

trait ReportRunLogTrait
{
    private $reportWays = [];

    /**
     * 上报日志
     * @param array|string $data
     */
    protected function reportLog( $data )
    {
        $data = is_array($data) ? implode("\n", $data) : $data;
        $data .= str_repeat(PHP_EOL, 3);

        $ways = $this->getReportWays();

        array_walk($ways, function($type, $way) use (&$data) {
            try {
                switch ($type) {
                    case 'FUNCTION':
                        $way($data);
                        break;

                    case 'DIRECTORY':
                        $app_name = strtolower(str_replace([':', '\\'], ['-', ''], Str::snake(static::class)));
                        $today = date('Y-m-d');

                        $filename = $way . ("/{$today}/{$app_name}.log");
                        app(Filesystem::class)->append($filename, $data, FILE_APPEND);
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
            return $this->reportWays();
        }

        if ( $this->reportWays ) {
            return $this->reportWays;
        }

        return [];
    }
}