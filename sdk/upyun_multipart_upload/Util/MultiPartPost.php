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
 * 辅助类：利用curl发送 multipart/form-data 数据
 */
class UpyunMultiPartPost {

    public static function post($postData, $url, $retryTimes = 5)
    {
        $delimiter = '-------------' . uniqid();
        $data = '';
        foreach($postData as $name => $content) {
            if(is_array($content)) {
                $data .= "--" . $delimiter . "\r\n";
                $filename = isset($content['name']) ? $content['name'] : $name;
                $data .= 'Content-Disposition: form-data; name="' . $name . '"; filename="' . $filename . "\" \r\n";
                $type = isset($content['type']) ? $content['type'] : 'application/octet-stream';
                $data .= 'Content-Type: ' . $type . "\r\n\r\n";
                $data .= $content['data'] . "\r\n";
            } else {
                $data .= "--" . $delimiter . "\r\n";
                $data .= 'Content-Disposition: form-data; name="' . $name . '"';
                $data .= "\r\n\r\n" . $content . "\r\n";
            }
        }
        $data .= "--" . $delimiter . "--";

        $handle = curl_init($url);
        curl_setopt($handle, CURLOPT_POST, true);
        curl_setopt($handle, CURLOPT_HTTPHEADER , array(
                'Content-Type: multipart/form-data; boundary=' . $delimiter,
                'Content-Length: ' . strlen($data))
        );
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($handle, CURLOPT_POSTFIELDS, $data);

        $times = 0;
        do{
            $result = curl_exec($handle);
            $times++;
        } while($result === false && $times < $retryTimes);

        curl_close($handle);
        return $result;
    }
} 