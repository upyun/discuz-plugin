# Discuz Plugin For UPYUN
 支持 Discuz 版本：`X3` `X3.1` `X3.2`
### 特性
* 单空间配置
* 增加分块上传
* Token 防盗链

*不支持表单直接上传*

### 视频教程

https://techs.b0.upaiyun.com/videos/cdnpage/Discuz.html

### 安装说明

1. 下载插件([点击这里](https://github.com/upyun/discuz-plugin/archive/master.zip))放到 Discuz `source/plugin` 目录下，并重命名目录为 `upyun`

2. 进入 Discuz 应用管理，选择应用“UPYUN 云加速” 点击安装。

3. 安装成功后先启用插件，再点击左侧插件列表的“UPYUN 云加速”进行设置

  填写设置前请阅读以下注意事项：
  * 访问域名需要填写与 UPYUN 空间绑定的域名，并且 http 前缀开头，也可以使用空间的默认域名
  * 防盗链 TOKEN 和防盗链过期时间必须要 UPYUN 访问域名和站点域名一致时，才能填写。否则 Cookie 跨域，会导致附件链接无法访问，出现 403 的情况。如果访问域名填写的是 UPYUN 空间的默认域名，则不能填写防盗链 TOKEN 和过期时间
  * 表单 API 强烈建议填写，否则无法使用分块上传。分块上传能够有效增加大文件上传的稳定性和速度。

4. 最后进入 Discuz 全局 -> 上传设置 -> 基本设置：开启远程附件，其他 ftp 参数不需要设置

### 常见问题
1. 安装时提示文件无法写入
只需按照提示，执行命令即可。(注意：该命令会让文件被所有用户可读写，用户也可以自行调整)

2. 安装时提示文件已经被修改，请手动安装

  由于插件需要修改以下六个系统原文件
  * source/module/forum/forum_attachment.php
  * source/module/portal/portal_attachment.php 
  * source/class/discuz/discuz_ftp.php 
  * source/function/function_attachment.php
  * source/function/function_home.php
  * source/function/function_post.php

  在安装时，插件会提前检查这个六个文件是否被修改，防止覆盖您的自定义修改。建议您将自定义修改的系统文件先备份，然后使用 Discuz 相应版本的原文件暂时代替完成安装（例如 Discuz 3.1 版本的原文件可以通过 source/plugin/upyun/discuz_3_1/uninstall/ 目录下获取。 uninstall 目录保存了各个版本的系统原文件）。文件还原后，重新安装即可。安装完成后，可以将您的自定义修改再追加到新的文件中。（如果卸载插件，该文件会被还原为 Discuz 原文件，所以卸载完成后需要重新追加本地修改）。
  
  代码中标记了`Begin of Insert UPYUN Code`表示本插件修改追加的内容

