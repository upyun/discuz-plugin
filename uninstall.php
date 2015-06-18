<?php


if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
$fc_arr = array();
if($fc_arr = file(DISCUZ_ROOT."/source/module/forum/forum_attachment.php")){
    $isChange = 0;
    foreach($fc_arr as $k=>$v){
        if(strpos($v,'Begin Of Upyun Insert Code')){
            $arr_insert = array();
            $arr_insert[0] = 'if($attach[\'remote\'] && !$_G[\'setting\'][\'ftp\'][\'hideurl\'] && $isimage) {'."\r\n";
            $arr_insert[1] = "\t".'dheader(\'location:\'.$_G[\'setting\'][\'ftp\'][\'attachurl\'].\'forum/\'.$attach[\'attachment\']);'."\r\n";
            $arr_insert[2] = '}'."\r\n";
            array_splice($fc_arr,$k,24,$arr_insert);
            $isChange++;
            break;
        }
    }
    if($isChange>0){
        $fc_str = implode("",$fc_arr);
        if($fp = fopen(DISCUZ_ROOT."/source/module/forum/forum_attachment.php",'w+')){
            fwrite($fp,$fc_str);
            fclose($fp);
        }
    }
}


$fc_arr = array();
if($fc_arr = file(DISCUZ_ROOT."/source/module/portal/portal_attachment.php")){
    $isChange = 0;
    foreach($fc_arr as $k=>$v){
        if(strpos($v,'Begin Of Upyun Insert Code')){
            $arr_insert = array();
            $arr_insert[0] = "\t".'if($attach[\'remote\'] && !$_G[\'setting\'][\'ftp\'][\'hideurl\'] && $attach[\'isimage\']) {'."\r\n";
            $arr_insert[1] = "\t\t".'dheader(\'location:\'.$_G[\'setting\'][\'ftp\'][\'attachurl\'].\'portal/\'.$attach[\'attachment\']);'."\r\n";
            $arr_insert[2] = "\t".'}'."\r\n";
            array_splice($fc_arr,$k,24,$arr_insert);
            $isChange++;
            break;
        }
    }
    if($isChange>0){
        $fc_str = implode("",$fc_arr);
        if($fp = fopen(DISCUZ_ROOT."/source/module/portal/portal_attachment.php",'w+')){
            fwrite($fp,$fc_str);
            fclose($fp);
        }
    }
}


$fc_arr = array();
if($fc_arr = file(DISCUZ_ROOT."/source/function/function_core.php")){
    $isChange = 0;
    foreach($fc_arr as $k=>$v){
        if(strpos($v,'Begin Of Upyun Insert Code')){
            $arr_insert = array();
            $arr_insert[0] = "\t".'$ftpon = getglobal(\'setting/ftp/on\');'."\r\n";
            $arr_insert[1] = "\t".'if(!$ftpon) {'."\r\n";
            $arr_insert[2] = "\t\t".'return $cmd == \'error\' ? -101 : 0;'."\r\n";
            $arr_insert[3] = "\t".'} elseif($ftp == null) {'."\r\n";
            $arr_insert[4] = "\t\t".'$ftp = & discuz_ftp::instance();'."\r\n";
            $arr_insert[5] = "\t".'}'."\r\n";
            $arr_insert[6] = "\t".'if(!$ftp->enabled) {'."\r\n";
            $arr_insert[7] = "\t\t".'return $ftp->error();'."\r\n";
            $arr_insert[8] = "\t".'} elseif($ftp->enabled && !$ftp->connectid) {'."\r\n";
            $arr_insert[9] = "\t\t".'$ftp->connect();'."\r\n";
            array_splice($fc_arr,$k,29,$arr_insert);
            $isChange++;
            break;
        }
    }
    if($isChange>0){
        $fc_str = implode("",$fc_arr);
        if($fp = fopen(DISCUZ_ROOT."/source/function/function_core.php",'w+')){
            fwrite($fp,$fc_str);
            fclose($fp);
        }
    }
}


$fc_arr = array();
if($fc_arr = file(DISCUZ_ROOT."/source/function/function_post.php")){
    $isChange = 0;
    foreach($fc_arr as $k=>$v){
        if(strpos($v,'Begin Of Upyun Insert Code')){
            $arr_insert = array();
            $arr_insert[0] = "\t\t\t\t\t".'dunlink($attach);'."\r\n";
            array_splice($fc_arr,$k,5,$arr_insert);
            $isChange++;
            break;
        }
    }
    if($isChange>0){
        $fc_str = implode("",$fc_arr);
        if($fp = fopen(DISCUZ_ROOT."/source/function/function_post.php",'w+')){
            fwrite($fp,$fc_str);
            fclose($fp);
        }
    }
}

if($ftp_str = file_get_contents(DISCUZ_ROOT."/source/plugin/upyun_upload/sdk/discuz_ftp.php")){
    if($fp = fopen(DISCUZ_ROOT."source/class/discuz/discuz_ftp.php",'w+')){
        fwrite($fp,$ftp_str);
        fclose($fp);
    }
}



$settingNew = array();
$settingNew['ftp'] = $_G['setting']['ftp'];
$settingNew['ftp']['on'] = 0;
C::t('common_setting')->update_batch($settingNew);
require_once DISCUZ_ROOT . './source/function/function_cache.php';
updatecache('setting');


$finish = true;
?>
