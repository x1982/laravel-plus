<?php
namespace Landers\LaravelPlus\Utils;

use Illuminate\Filesystem\Filesystem as LaravelFilesystem;

class Filesystem extends LaravelFilesystem
{
    /**
     * 为保存文件创建目录
     * @param string $filename
     * @throws \Exception
     */
    private function makeDirectoryForSaveFile( string $filename )
    {
        $dir_name = $this->dirname($filename);

        if ( !$this->isDirectory($dir_name) ) {
            if ( !$this->makeDirectory($dir_name, 0755, true) ) {
                throw new \Exception('目录创建失败');
            }
        }
    }

    /**
     * 转换内容到字符串
     * @param $contents
     * @return string
     */
    private function convContents( $contents )
    {
        if ( is_string($contents) ) {
            return $contents;
        }

        return json_encode($contents, JSON_UNESCAPED_UNICODE);
    }


    /**
     * Write the contents of a file.
     *
     * @param  string  $path
     * @param  string  $contents
     * @param  bool  $lock
     * @return int
     */
    public function put($path, $contents, $lock = false)
    {
        $this->makeDirectoryForSaveFile($path);
        $contents = $this->convContents($contents);
        return parent::put($path, $contents, $lock);
    }


    /**
     * Append to a file.
     *
     * @param  string  $path
     * @param  string  $data
     * @return int
     */
    public function append($path, $data)
    {
        $this->makeDirectoryForSaveFile($path);
        $contents = $this->convContents($data);
        return parent::append($path, $contents);
    }
}