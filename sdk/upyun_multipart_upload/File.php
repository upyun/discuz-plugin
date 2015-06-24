<?php
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
/**
 * Crocodile - UpYun分块上传 PHP-SDK
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

/**
 * 文件处理
 * Class File
 * @package Crocodile
 */
class UpyunMultiPartFile {
    /**
     * @var string: 文件绝对路径
     */
    protected $realPath;
    /**
     * @var int:文件大小,单位Byte
     */
    protected $size;
    /**
     * @var string:文件HASH
     */
    protected $md5FileHash;
    /**
     * @var resource:文件句柄
     */
    protected $handler;

    public function __construct($path){
        $this->realPath = realpath($path);
        if(!($this->realPath && file_exists($this->realPath))) {
            throw new Exception('upload file not exists');
        }
        $this->size = filesize($path);
        $this->md5FileHash = md5_file($this->realPath);
    }

    public function getSize()
    {
        return $this->size;
    }

    public function getMd5FileHash()
    {
        return $this->md5FileHash;
    }

    public function getHandler()
    {
        if(is_resource($this->handler) === false) {
            $this->handler = fopen($this->realPath, 'rb');
        }
        return $this->handler;
    }

    /**
     * 读取文件块
     * @param $currentPosition: 文件当前读取位置
     * @param $endPosition: 文件读取结束位置
     * @param int $len: 每次读取的字节数
     * @return string
     */
    public function readBlock($currentPosition, $endPosition, $len = 8192)
    {
        $data = '';
        while($currentPosition < $endPosition) {
            if($currentPosition + $len > $endPosition) {
                $len = $endPosition - $currentPosition;
            }

            fseek($this->getHandler(), $currentPosition);
            $data .= fread($this->getHandler(), $len);
            $currentPosition = $currentPosition + $len;
        }
        return $data;
    }

    public function getRealPath()
    {
        return $this->realPath;
    }

    public function __destruct()
    {
        if(is_resource($this->handler)) {
            fclose($this->handler);
        }
    }
} 