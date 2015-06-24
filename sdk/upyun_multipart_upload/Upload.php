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
include_once 'Util/MultiPartPost.php';

class UpyunMultipartUpload {
    /**
     * @var string:请求的接口地址
     */
    public $api = "http://m0.api.upyun.com/";
    /**
     * @var string:文件分块数
     */
    protected $blocks;
    /**
     * @var int: 文件块大小
     */
    protected $blockSize;
    /**
     * @var int: 文件过期时间
     */
    protected $expiration;
    /**
     * @var string: save_token
     */
    protected $saveToken;
    /**
     * @var string: 上传的空间名
     */
    protected $bucketName;
    /**
     * @var array: 文件块的状态 e.g: [1,0,1] 表示共三个文件块，第1块和第3块上传成功
     */
    protected $status;

    /**
     * @var string: UPYUN 请求唯一id, 出现错误时, 可以将该id报告给 UPYUN,进行调试
     */
    protected $x_request_id;

    /**
     * @var Signature: 签名
     */
    protected $signature;

    public function __construct($signature)
    {
        //default 5MB
        $this->blockSize = 5 * 1024 * 1024;
        $this->signature = $signature;
    }

    public function setBlockSize($size)
    {
        if($size < 1024 * 1024) {
            $size = 1024 * 1024;
        } else if($size > 5 * 1024 * 1024) {
            $size = 5 * 1024 * 1024;
        }
        $this->blockSize = $size;
    }

    public function setBucketName($bucketName)
    {
        $this->bucketName = $bucketName;
    }

    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * 分块上传本地文件
     * @param File $file: 等待上传的文件
     * @param array $data : 上传的参数, 必须包含路径选项 'path' => '/yourpath/file.ext'
     * @return mixed
     * @throws Exception
     */
    public function upload($file, $data)
    {
        if(!isset($data['path'])) {
            throw new Exception('please set upload path');
        }

        $this->blocks = intval(ceil($file->getSize() / $this->blockSize));
        $result = $this->initUpload($file, $data);
        $this->updateStatus($result);

        $times = 0;
        do {
            for($blockIndex = 0; $blockIndex < $this->blocks; $blockIndex++) {
                if(isset($this->status[$blockIndex]) && !$this->status[$blockIndex]) {
                    $result = $this->blockUpload($blockIndex, $file);
                    $this->updateStatus($result);
                }
            }
            $times++;
        } while(!$this->isUploadSuccess() && $times < 3);

        if($this->isUploadSuccess()) {
            $result = $this->endUpload();
            return $result;
        } else {
            throw new Exception(sprintf("chunk upload failed! status is : [%s]", implode(',', $this->status)));
        }
    }

    /**
     * 初始化，将文件信息发送给服务器
     * @param File $file
     * @param array $data : 附加参数 必须包含路径选项 'path' => '/yourpath/file.ext'
     *
     * @return mixed
     */
    public function initUpload($file, $data)
    {
        $this->expiration = time() + 186400;

        $metaData = array(
            'expiration' => $this->expiration,
            'file_blocks' => $this->blocks,
            'file_hash' => $file->getMd5FileHash(),
            'file_size' => $file->getSize(),
        );
        $metaData = array_merge($metaData, $data);
        $policy = $this->signature->createPolicy($metaData);
        $signature = $this->signature->createSign($metaData);
        $postData = compact('policy', 'signature');

        $result = $this->postData($postData);
        $this->saveToken = $result['save_token'];
        $this->signature->setTokenSecret($result['token_secret']);
        return $result;
    }


    /**
     * 上传单个文件块
     * @param $index : 文件块索引， 从0开始
     * @param File $file
     * @param array $data: 附加参数,可选
     * @return mixed
     */
    public function blockUpload($index, $file, $data = array())
    {
        $startPosition = $index * $this->blockSize;
        $endPosition = $index >= $this->blocks - 1 ? $file->getSize() : $startPosition + $this->blockSize;

        $fileBlock = $file->readBlock($startPosition, $endPosition);
        $hash = md5($fileBlock);

        $metaData = array(
            'save_token' => $this->saveToken,
            'expiration' => $this->expiration,
            'block_index' => $index,
            'block_hash' => $hash,
        );
        $metaData = array_merge($metaData, $data);
        $postData['policy'] = $this->signature->createPolicy($metaData);
        $postData['signature'] = $this->signature->createSign($metaData, false);
        $postData['file'] = array('data' => $fileBlock);

        $result = UpyunMultiPartPost::post($postData, $this->api . $this->bucketName . "/");
        $result = $this->parseResult($result);
        return $result;
    }

    /**
     * 文件块全部上传成功后，请求服务器，终止文件上传
     * @param array $data:  附加参数,可选
     * @return mixed
     */
    public function endUpload($data = array())
    {
        $metaData['save_token'] = $this->saveToken;
        $metaData['expiration'] = $this->expiration;
        $metaData = array_merge($metaData, $data);

        $policy = $this->signature->createPolicy($metaData);
        $signature = $this->signature->createSign($metaData, false);

        $postData = compact('policy', 'signature');
        $result = $this->postData($postData);
        return $result;
    }

    /**
     * 判断所有文件块是否上传成功
     * @return bool
     */
    public function isUploadSuccess()
    {
        return array_sum($this->status) === count($this->status);
    }

    public function getXRequestId()
    {
        return $this->x_request_id;
    }

    protected function parseResult($result)
    {
        $data = json_decode($result, true);
        if(isset($data['error_code'])) {
            throw new Exception(
                sprintf("upload failed, error code: %s, message: %s",
                    $data['error_code'],
                    $data['message'] . " X-Request-Id:" . $this->getXRequestId()
                ));
        }
        return $data;
    }

    /**
     * 发送 Content-Type: application/x-www-form-urlencoded 的POST请求
     * @param array $postData
     * @param array $headers
     * @param int $retryTimes : 重试次数
     * @throws \Exception
     * @return mixed
     */
    protected function postData($postData, $headers = array(), $retryTimes = 3)
    {
        $url = $this->api . $this->bucketName . "/";
        $ch = curl_init($url);
        $headers = array_merge($headers,
            array('Content-Type: application/x-www-form-urlencoded'));
        curl_setopt_array($ch, array(
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => http_build_query($postData),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
        ));

        $times = 0;
        do{
            $result = curl_exec($ch);
            $times++;
        } while($result === false && $times < $retryTimes);

        if($result === false) {
            throw new Exception("curl failed");
        }

        list($headers, $body) = $this->parseHttpResponse($result);
        $this->x_request_id = isset($headers['X-Request-Id']) ? $headers['X-Request-Id'] : '';

        $data = $this->parseResult($body);
        curl_close($ch);
        return $data;
    }

    /**
     * 根据上传接口返回的数据，更新文件块上传状态
     * @param $result: 接口返回的数据
     */
    protected function updateStatus($result)
    {
        if(isset($result['status'])) {
            $this->status = $result['status'];
        }
    }

    private function parseHttpResponse($response) {
        if(!$response) {
            return false;
        }

        $response_array = explode("\r\n\r\n", $response, 2);
        $header_string = $response_array[0];
        $body = isset($response_array[1]) ? $response_array[1] : '';

        $headers = array();
        foreach (explode("\n", $header_string) as $header) {
            $headerArr = explode(':', $header, 2);
            if(isset($headerArr[1])) {
                $key = $headerArr[0];
                $headers[$key] = trim($headerArr[1]);
            }
        }
        return array($headers, $body);
    }
}