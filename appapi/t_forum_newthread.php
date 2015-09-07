<?php
/**
 * 发布帖子主题，即发布投诉爆料
 *
 * @parameter $fid 板块ID
 * @parameter $uid 用户ID
 * @parameter $subject 帖子标题
 * @parameter $message 帖子内容
 * @parameter $pics 帖子图片（base64流数据）
 *
 * 仿照discuz发帖方法，流程如下：
 * 1. 加载$_G, "./inc.php"
 * 2. 判断权限, "./source/post/forum_post.php"
 * 3. 发帖方法, "./source/post/post_newthread.php"
 * 4. 发帖类, "./source/post/post_newthread.php"
 *
 * @Author    王伟龙 QQ:973885303
 * @FileName  t_forum_newthread.php
 * @Date      2014-9-10 10:42:27
 *
 */
 
require("./inc.php");

// 接受数据
$fid      = intval(trim($_POST['fid']));
$uid      = intval(trim($_POST['uid']));
$subject  = trim($_POST['subject']);
$message  = trim($_POST['message']);
$pic      = trim($_POST['pic']);
$time     = time();

// 参数判断
if (empty($fid) || !is_numeric($fid)) showmessage2('fid参数不能为空', '', 44101);

if (empty($uid) || !is_numeric($uid)) showmessage2('uid参数不能为空', '', 44102);

if (empty($subject)) showmessage2('subject参数不能为空', '', 44103);

if (empty($message)) showmessage2('message参数不能为空', '', 44104);

global $_G;
$_G['clientip'] = $_SERVER["REMOTE_ADDR"];
$_G['setting']['threadhidethreshold'] = 1;

require './source/post/forum_post.php';
