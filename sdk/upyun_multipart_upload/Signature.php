<?php
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
 * 签名操作
 * Class Signature
 * @package Crocodile
 */
class UpyunMultipartSignature {
    /**
     * @var string: 使用表单API的key
     */
    protected $formApiKey;
    /**
     * @var string: 接口返回的 token
     */
    protected $tokenSecret;
    /**
     * @var array: 服务端返回的数据KEY
     */
    protected $paramsKey;

    public function __construct($formApiKey = '')
    {
        $this->setFormApiKey($formApiKey);
        $this->paramsKey = array('path', 'content_type', 'content_length',
                                 'image_width', 'image_height', 'image_frames',
                                 'last_modified', 'signature');
    }

    public function setFormApiKey($key)
    {
        $this->formApiKey = $key;
    }

    public function setTokenSecret($key)
    {
        $this->tokenSecret = $key;
    }

    /**
     * 生成签名
     * @param $data
     * @param bool $isFormApiKey: 签名生成方式 e.g: true 使用表单API的key生成
     * @return bool|string
     */
    public function createSign($data, $isFormApiKey = true)
    {
        if(is_array($data)) {
            ksort($data);
            $string = '';
            foreach($data as $k => $v) {
                $string .= "$k$v";
            }
            $string .= $isFormApiKey ? $this->formApiKey : $this->tokenSecret;
            $sign = md5($string);
            return $sign;
        }
        return false;
    }

    /**
     * 获取 Policy 值
     * @param $metaData
     * @return bool|string
     */
    public function createPolicy($metaData)
    {
        if(is_array($metaData)) {
            $jsonStr = json_encode($metaData);
            return base64_encode($jsonStr);
        }
        return false;
    }

    /**
     * 验证回调签名
     * @param $data
     * @param bool $isFormApiKey
     * @return bool
     */
    protected function validateSign($data, $isFormApiKey = true)
    {
        if(! isset($data['signature'])) {
            return false;
        }

        $sign = $data['signature'];
        unset($data['signature']);

        return $this->createSign($data, $isFormApiKey) === $sign;
    }

    /**
     * 客户端同步跳转回调验证
     * @return bool
     */
    public function returnValidate()
    {
        $data = array();
        foreach($this->paramsKey as $key) {
            if(isset($_GET[$key])) {
                $data[$key] = $_GET[$key];
            }
        }
        return $this->validateSign($data);
    }
    /**
     * 异步通知回调验证
     * @return bool
     */
    public function notifyValidate()
    {
        $data = array();
        foreach($this->paramsKey as $key) {
            if(isset($_POST[$key])) {
                $data[$key] = $_GET[$key];
            }
        }
        return $this->validateSign($data);
    }

    /**
     * 服务端直接返回 json验证
     * @param $data: 服务端返回的待验证数据
     * @return bool
     */
    public function syncJsonValidate($data)
    {
        return $this->validateSign($data, true);
    }
}