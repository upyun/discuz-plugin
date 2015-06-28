<?php

/**
 *      (c) 2005 - 2099 UpYun.com, All Rights Reserved.
 *      If you have any problem,please connect with us:  https://www.upyun.com
 *      $Id: discuz_ftp.php     BiangBiang:  biangbiangguoguo@163.com
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

include_once DISCUZ_ROOT . "source/plugin/upyun/sdk/upyun.class.php";
include_once DISCUZ_ROOT . "source/plugin/upyun/sdk/upyun_multipart_upload/Upload.php";
include_once DISCUZ_ROOT . "source/plugin/upyun/sdk/upyun_multipart_upload/Signature.php";
include_once DISCUZ_ROOT . "source/plugin/upyun/sdk/upyun_multipart_upload/File.php";


class discuz_ftp
{

	var $enabled = false;
	var $config = array();
    var $api_access = array(UpYun::ED_AUTO, UpYun::ED_TELECOM, UpYun::ED_CNC, UpYun::ED_CTT);
	var $connectid;
	var $_error;
    var $upyun_config = array();

	function &instance($config = array()) {
		static $object;
		if(empty($object)) {
			$object = new discuz_ftp($config);
		}
		return $object;
	}

	function __construct($config = array()) {
        global $_G;
		$this->set_error(0);
        loadcache('plugin');
        $this->upyun_config = getglobal('cache/plugin/upyun');
		$this->config = !$config ? getglobal('setting/ftp') : $config;
		$this->enabled = false;
        $this->config['host'] = discuz_ftp::clear($this->config['host']);
        $this->config['port'] = intval($this->config['port']);
        $this->config['ssl'] = intval($this->config['ssl']);
        $this->config['bucketname'] = $this->config['host'];
        $this->config['username'] = discuz_ftp::clear($this->config['username']);
        $this->config['password'] = authcode($this->config['password'], 'DECODE', md5(getglobal('config/security/authkey')));
        $this->config['timeout'] = intval($this->config['timeout']);
        $this->config['api_access'] = $this->api_access[$this->config['port']];
        $this->connectid = true;
        $this->enabled = true;
	}

	function upload($source, $target) {
        $file = new UpyunMultiPartFile($source);
        if($file->getSize() > 1024 * 1024 && $this->upyun_config['form_api_key']) {
            $sign = new UpyunMultipartSignature($this->upyun_config['form_api_key']);
            $upload = new UpyunMultipartUpload($sign);
            $upload->setBucketName($this->upyun_config['bucket_name']);
            try {
                $result = $upload->upload($file, array(
                    'path' => '/' . ltrim($target, '/')
                ));
                return $result;
            } catch(Exception $e) {
                return 0;
            }
        } else {
            $fh = fopen($source, 'rb');
            if(!$fh) {
                return 0;
            }
            $upyun = new UpYun(
                $this->upyun_config['bucket_name'],
                $this->upyun_config['operator_name'],
                $this->upyun_config['operator_pwd']
            );
            $rsp = $upyun->writeFile('/'. ltrim($target, '/'), $fh, true);
            return $rsp;
        }
	}

	function connect() {
        return 1;
	}

	function set_error($code = 0) {
		$this->_error = $code;
	}

	function error() {
		return $this->_error;
	}

	function clear($str) {
		return str_replace(array( "\n", "\r", '..'), '', $str);
	}

	function ftp_rmdir($directory) {
		return 1;
	}

	function ftp_size($remote_file) {
        $upyun = new UpYun($this->config['bucketname'],$this->config['username'],$this->config['password'],$this->config['api_access']);
		$remote_file = discuz_ftp::clear($remote_file);
        try{
            $rsp = $upyun->getFileInfo('/'.$this->config['attachdir'].'/'.$remote_file);
            return $rsp['x-upyun-file-size'];
        }
        catch(Exception $e){
            return -1;
        }
	}

	function ftp_close() {
		return 1;
	}

	function ftp_delete($path) {
        $upyun = new UpYun($this->config['bucketname'],$this->config['username'],$this->config['password'],$this->config['api_access']);
        $path = discuz_ftp::clear($path);
        try{
            $rsp = $upyun->delete('/'.$this->config['attachdir'].'/'.$path);
            return $rsp;
        }
        catch(Exception $e){
            return 0;
        }
	}

	function ftp_get($local_file, $remote_file, $mode, $resumepos = 0) {
        $upyun = new UpYun($this->config['bucketname'],$this->config['username'],$this->config['password'],$this->config['api_access']);
		$remote_file = discuz_ftp::clear($remote_file);
		$local_file = discuz_ftp::clear($local_file);
        try{
            if($fh = fopen($local_file,'wb')){
            $rsp = $upyun->readFile('/'.$this->config['attachdir'].'/'.$remote_file,$fh);
            fclose($fh);
            return $rsp;
            }else{
                return 0;
            }
        }
        catch(Exception $e){
            return 0;
        }
	}

}
