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
 * 3. 发帖方法, "./source/post/post_newthread.php"（使用4发帖类）
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
if (empty($fid) || !is_numeric($fid)) {
    $data['data'] = "";
    $data['msg']  = "fid参数不合法！";
    $data['code'] = 40001;
    echo json_encode($data);
    exit();
}
if (empty($uid) || !is_numeric($uid)) {
    $data['data'] = "";
    $data['msg']  = "uid参数不合法！";
    $data['code'] = 40002;
    die(json_encode($data));
}
if (empty($subject)) {
    $data['data'] = "";
    $data['msg']  = "subject参数不能为空！";
    $data['code'] = 40003;
    die(json_encode($data));
}
if (empty($message)) {
    $data['data'] = "";
    $data['msg']  = "message参数不能为空！";
    $data['code'] = 40004;
    die(json_encode($data));
}

global $_G;
$_G['clientip'] = $_SERVER["REMOTE_ADDR"];
$_G['setting']['threadhidethreshold'] = 1;

require './source/post/forum_post.php';
