<?php
namespace Landers\LaravelPlus\Supports\ResourceFetch;

use Landers\LaravelAms\Constraints\Commands\BaseCommand;
use Landers\Substrate2\Utils\Path;
use Landers\Substrate2\Utils\Str;

class ResourceFetchCommand extends BaseCommand
{
    use \Landers\LaravelPlus\Traits\FsoTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'plus:fetch-remote
                            {url : 远程地址}
                            {--dir= : 保存在vendor下的子目录名}';

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

        $this->response->note('正在抓取面页及资源中...');
        //$save_path = $this->option('dir');

        $resource_fetcher = new ResourceFetchService($remote_url, $doc_path);

        if ( $result = $resource_fetcher->fetchAll() ) {
            $this->response->echoSuccess();
            $blade_key = $this->buildBlade($remote_url, $result);
            $route_path = $this->buildRoute($remote_url, $blade_key);
            $this->response->note('查看地址: %s', Path::join(env('APP_URL'), $route_path));
        } else {
            $this->response->error('抓取错误');
        }
    }

    protected function buildBlade($remote_url, $result)
    {
        $view_sub_path = 'internet';

        // 源文件
        $src_file = $result['file'];

        // 确定blade目标文件名
        $remote_path = pathinfo(parse_url($remote_url, PHP_URL_PATH), PATHINFO_FILENAME);
        $blade_key = str_replace('/', '_', trim($remote_path, '/')) ;
        $blade_key = $blade_key ?: 'index';
        $dist_file = $blade_key . '.blade.php';
        $dist_file = resource_path("views/{$view_sub_path}/{$dist_file}");

        // 复制
        $this->fso()->copy($src_file, $dist_file);

        // 返回 blade key
        return "{$view_sub_path}.{$blade_key}";
    }

    protected function buildRoute($remote_url, $blade_key)
    {
        // 确定路由路径
        $route_path = rtrim(parse_url($remote_url, PHP_URL_PATH), '/');

        // 确定路由内容
        $template = $this->fso()->get(__DIR__ . '/route.template');
        $route_content = Str::replace($template, [
            'path' => $route_path,
            'blade' => $blade_key,
        ]);

        // 确定路由文件
        $route_file = base_path('routes/web.php');

        // 追加到路由文件
        $this->fso()->append($route_file, "\n".$route_content);

        // 返回路由路径
        return $route_path;
    }
}