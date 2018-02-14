<?php

namespace Landers\LaravelPlus\Supports\ResourceFetch;

use Landers\Substrate2\Utils\Path;
use Landers\Substrate2\Utils\Url;
use Landers\Substrate2\Utils\Str;

/**
 *
 */
class ResourceFetchService
{
    use \Landers\LaravelPlus\Traits\HttpRequestTrait;

    use \Landers\LaravelPlus\Traits\FsoTrait;

    // 远程URL 及其信息
    private $url, $urlInfo;

    // 由URL获取到内容
    public $content;

    // 访问时的web根目录
    private $docPath;

    // 本次抓取存放的根目录
    private $rootDir;

    // 资源的保存路径
    private $saveDir;

    // 生成的页面文件
    private $indexFile;

    function __construct($url, $doc_path, $root_dir = '', $save_dir = '', $index_file = null)
    {
        $this->url = Url::stripRelative($url);
        $this->urlInfo = Url::parse($this->url);

        $this->docPath = Path::rtrimSeparator($doc_path);
        $this->rootDir = Path::trimSeparator($root_dir);
        $this->saveDir = $save_dir ? Path::trimSeparator($save_dir) : '.';
        $this->indexFile = $index_file ?: 'index.html';

        $this->content = $this->getContent($this->url);
    }

    /**
     * 抓取所有
     * @return array|bool
     */
    public function fetchAll()
    {
        $replaces_img = $this->fetchImg();
        //$replaces_img = [];
        $replaces_css = $this->fetchCss();
        //$replaces_css = [];
        $replaces_js = $this->fetchJs();
        //$replaces_js = [];
        $replaces_url = $this->fetchUrl();
        //$replaces_url = [];
        $replaces_embed = $this->fetchEmbed();
        //$replaces_embed = [];

        $replaces = array_merge($replaces_img, $replaces_css, $replaces_js, $replaces_url, $replaces_embed);

        if ($this->indexFile) {
            return $this->saveIndex($replaces);
        } else {
            return $replaces;
        }
    }

    /**
     * 截掉 query 部分
     * @param string $str
     * @return bool|string
     */
    private function cutQuery(string $str)
    {
        if (($p = strrpos($str, '?')) !== false) {
            $str = substr($str, 0, $p);
        }

        return $str;
    }


    /**
     * 过滤路径
     * @param $paths
     * @return array
     */
    private function filtePaths($paths)
    {
        foreach ($paths as $i => &$item) {
            $item = trim($item, '\'"');

            if (!$item) {
                unset($paths[$i]);
            }

            if (strpos($item, 'http') === 0) {
                unset($paths[$i]);
                continue;
            }

            if (strpos($item, '#') === 0) {
                unset($paths[$i]);
                continue;
            }

            if (strpos($item, '//') === 0) {
                $item = 'http:' . $item;
                continue;
            }

            if (strpos($item, '<') !== false || strpos($item, '{') !== false) {
                unset($paths[$i]);
            }

            if (preg_match('/^data:image(.*)/i', $item)) {
                unset($paths[$i]);
            }

            //$item = $this->cutQuery($item);
        }
        $paths = array_values($paths);
        $paths = array_unique($paths);

        return $paths;
    }


    /**
     * 取得内容
     *
     * @param string|null $url
     * @return mixed
     */
    public function getContent(string $url = null)
    {
        if (!$url) {
            return $this->content;
        }

        $content = $this->requestGet($url);

        if (!Str::isUtf8($content)) {
            $content = iconv('gb2312', 'utf-8//ignore', $content);
        }

        $content = preg_replace('/<base.*? href="?(.*?)"?( .*?)?\/?>/i', '', $content);


        $replaces = [
            'gb2312' => 'utf-8',
        ];

        return str_replace(
            array_keys($replaces),
            array_values($replaces),
            $content
        );
    }

    /**
     * 抓取图片文件
     * @return array
     */
    public function fetchImg()
    {
        preg_match_all('/<img.*? src="(.*?)"( .*?)?\/?>/i', $this->content, $matches);
        $paths = $this->filtePaths($matches[1]);
        return $this->saveResource($paths);
    }

    /**
     * 取得css文件
     * @return array
     */
    public function getCssFiles()
    {
        preg_match_all('/<link.+href=\"?(.+\.css)\"?.+(\/)?>/i', $this->content, $matches);
        return $this->filtePaths($matches[1]);
    }

    /**
     * 抓取css文件
     * @return array
     */
    public function fetchCss()
    {
        $paths = $this->getCssFiles();
        $replaces = $this->saveResource($paths);
        foreach ($replaces as $item) {
            $local_path = $item['local_path'];
            $remote_url = $item['remote_url'];
            $file_name = str_replace(Path::join($this->docPath, $this->rootDir), '', $local_path);

            $dirname = pathinfo($file_name, PATHINFO_DIRNAME);
            $basename = pathinfo($file_name, PATHINFO_BASENAME);

            $self = new self($remote_url, $this->docPath, $this->rootDir, $dirname, $basename);
            $replaces_paths = $self->fetchUrl();
            $self->saveIndex($replaces_paths);
        }

        return $replaces;
    }

    /**
     * 抓取js文件
     * @return array
     */
    public function fetchJs()
    {
        preg_match_all('/<script.*? src="?(.*?)"?( .*?)?\/?>/i', $this->content, $matches);
        $paths = $this->filtePaths($matches[1]);
        return $this->saveResource($paths);
    }

    /**
     * 抓取embed文件
     * @return array
     */
    public function fetchEmbed()
    {
        preg_match_all('/<embed.*? src="?(.*?)"?( .*?)?\/?>/i', $this->content, $matches);
        $paths = $this->filtePaths($matches[1]);
        return $this->saveResource($paths);
    }

    /**
     * 抓取url()中文件
     * @return array
     */
    public function fetchUrl()
    {
        preg_match_all('/url\((.*?)\)+/i', $this->content, $matches);
        $paths = $this->filtePaths($matches[1]);
        return $this->saveResource($paths);
    }

    /**
     * 保存首页
     * @param $replaces
     * @return array
     * @throws \Exception
     */
    public function saveIndex(array $replaces)
    {
        $replaces = array_map(function ($item) {
            return $item['local_url'];
        }, $replaces);

        if ($this->indexFile) {
            $content = strtr($this->content, $replaces);
            $file = Path::join($this->docPath, $this->rootDir, $this->saveDir, $this->indexFile);

            if (!$this->fso()->put($file, $content)) {
                throw new \Exception("写入首页文件{$file}失败");
            } else {
                return [
                    'url' => Path::toUrl($file, $this->docPath),
                    'file' => $file,
                ];
            }
        }
    }

    /**
     * 取得保存路径
     * @return string
     */
    private function getSavePath($filename)
    {
        //$debug = $filename == '/src/images/banner-bg-2.png';

        $filename = explode('?', $filename)[0];
        if (Path::isAbsolute($filename)) {
            $path = $filename;
        } else {
            $path = Path::join($this->saveDir, $filename);
            $path = Path::stripRelative($path);
        }
        $path = Path::join($this->docPath, $this->rootDir, $path);
        //if ($debug) dp([$this->docPath, $this->rootDir, $path]);

        return $path;
    }

    private function debug($url, $str, $print = null)
    {
        $print or $print = $url;
        if (strpos($url, $str) !== false) {
            dp($print);
        }
    }

    /**
     * 保存资源
     * @param $_paths
     * @return array
     * @throws \Exception
     */
    private function saveResource($_paths)
    {
        //返回要替换的数组：key替换目标， value替换内容
        $replaces = [];

        //将路径中每个元素中有逗号的分离出来，成为路径中单独元素
        $paths = [];
        foreach ($_paths as $item) {
            $arr = explode(',', $item);
            $paths = array_merge($paths, $arr);
            unset($arr);
        }
        $paths = array_unique($paths);

        foreach ($paths as $item) {
            if (!$item) continue;

            // 原url
            if (Url::getHost($item)) {
                $remote_url = $item;
            } else {
                if (strpos($item, '/') === 0) {
                    $remote_url = Path::join(Url::buildHostUrl($this->urlInfo), $item, '/');
                } else {
                    $remote_url = Path::join(Url::dirname($this->url), $item, '/');
                }
            }

            //拼装完合本地存储路径
            $local_path = $this->getSavePath($item);

            //生成资源本地url
            $local_url = Path::toUrl($local_path, Path::join($this->docPath));

            //组装返回结果
            $replaces[$item] = [
                'remote_url' => $remote_url,
                'local_path' => $local_path,
                'local_url' => $local_url,
            ];
            //dp($replaces, 0);

            //获取远程内容并保存
            if (is_file($local_path)) continue;
            if ($content = $this->requestGet($remote_url)) {
                if (!preg_match('/404 Not Found/', $content)) {
                    if (!$this->fso()->put($local_path, $content)) {
                        throw new \Exception("写入文件{$local_path}失败");
                    }
                }
            }
        }

        //dp($replaces, false);
        return $replaces;
    }
}