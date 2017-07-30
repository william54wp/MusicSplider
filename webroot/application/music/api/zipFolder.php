<?php
namespace app\music\api;

use ZipArchive;

class zipFolder
{
    /**
    * PHP ZipArchive压缩文件夹，实现将目录及子目录中的所有文件压缩为zip文件
    * @author 吴先成 wuxiancheng.cn
    * @param string $folderPath 要压缩的目录路径
    * @param string $zipAs 压缩文件的文件名，可以带路径
    * @return bool 成功时返回true，否则返回false
    */
    function zipFolder($folderPath, $zipAs)
    {
        $folderPath = (string)$folderPath;
        $zipAs = (string)$zipAs;
        if (!class_exists('ZipArchive')) {
            return false;
        }
        if (!$files=$this->scanFolder($folderPath, true, true)) {
            return false;
        }
        $za = new ZipArchive;
        if (true!==$za->open($zipAs, ZipArchive::OVERWRITE | ZipArchive::CREATE)) {
            return false;
        }
        $za->setArchiveComment(base64_decode('LS0tIHd1eGlhbmNoZW5nLmNuIC0tLQ==').PHP_EOL.date('Y-m-d H:i:s'));
        foreach ($files as $aPath => $rPath) {
            $za->addFile($aPath, $rPath);
        }
        if (!$za->close()) {
            return false;
        }
        return true;
    }

    /**
    * 扫描文件夹，获取文件列表
    * @author 吴先成 wuxiancheng.cn
    * @param string $path 需要扫描的目录路径
    * @param bool   $recursive 是否扫描子目录
    * @param bool   $noFolder 结果中只包含文件，不包含任何目录，为false时，文件列表中的目录统一在末尾添加/符号
    * @param bool   $returnAbsolutePath 文件列表使用绝对路径，默认将返回相对于指定目录的相对路径
    * @param int    $depth 子目录层级，此参数供系统使用，禁止手动指定或修改
    * @return array|bool 返回目录的文件列表，如果$returnAbsolutePath为true，返回索引数组，否则返回键名为绝对路径，键值为相对路径的关联数组
    */

    function scanFolder($path = '', $recursive = true, $noFolder = true, $returnAbsolutePath = false, $depth = 0)
    {
        $path = (string)$path;
        if (!($path=realpath($path))) {
            return false;
        }
        $path = str_replace('\\', '/', $path);
        if (!($h=opendir($path))) {
            return false;
        }
        $files = array();
        static $topPath;
        $topPath = $depth===0||empty($topPath)?$path:$topPath;
        while (false!==($file=readdir($h))) {
            if ($file!=='..' && $file!=='.') {
                $fp = $path.'/'.$file;
                if (!is_readable($fp)) {
                    continue;
                }
                if (is_dir($fp)) {
                    $fp .= '/';
                    if (!$noFolder) {
                        $files[$fp] = $returnAbsolutePath?$fp:ltrim(str_replace($topPath, '', $fp), '/');
                    }
                    if (!$recursive) {
                        continue;
                    }
                    $function = __FUNCTION__;
                    $subFolderFiles = $this->$function($fp, $recursive, $noFolder, $returnAbsolutePath, $depth+1);
                    if (is_array($subFolderFiles)) {
                        $files = array_merge($files, $subFolderFiles);
                    }
                } else {
                    $files[$fp] = $returnAbsolutePath?$fp:ltrim(str_replace($topPath, '', $fp), '/');
                }
            }
        }
        return $returnAbsolutePath?array_values($files):$files;
    }
}
