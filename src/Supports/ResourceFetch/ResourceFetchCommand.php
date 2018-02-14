<?php
namespace Landers\LaravelPlus\Supports\ResourceFetch;

use Landers\LaravelAms\Constraints\Commands\BaseCommand;
use Landers\Substrate2\Utils\Path;

class ResourceFetchCommand extends BaseCommand
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'plus:fetch-remote
                            {url : 远程地址}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '抓取远程资源';


    protected function handleMain()
    {
        $remote_url = $this->argument('url');
        $doc_path = myams_guard_path('internet');

        //$url = 'https://www.xxx.com:99/xxx/yyy/zzz.php?a=1&b=2#ccccc';
        //dp(dirname($url));

        $this->response->note('正在抓取面页及资源中...');
        $resource_fetcher = new ResourceFetchService($remote_url, $doc_path);

        if ( $url = $resource_fetcher->fetchAll() ) {
            $this->response->echoSuccess();
            $this->response->note('查看地址: %s', Path::join(env('APP_URL'), $url));
        } else {
            $this->response->error('抓取错误');
        }
    }
}