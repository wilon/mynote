<?php
/**
 * 回复帖子主题，即评论投诉爆料
 *
 * @parameter $tid 帖子主题ID
 * @parameter $authorid 用户ID
 * @parameter $message 帖子内容
 *
 * @Author    王伟龙 QQ:973885303
 * @FileName  t_forum_newthread.php
 * @Date      2014-9-12 10:32:27
 *
 */

require("./inc.php");
include_once libfile('function/forum');
include_once libfile('function/core');

// 接受数据
$tid      = intval(trim($_POST['tid']));
$uid      = intval(trim($_POST['uid']));
$message  = trim($_POST['message']);
$time     = time();

// 参数判断
if (empty($tid) || !is_numeric($tid)) {
    $data['data'] = "";
    $data['msg']  = "tid参数不合法！";
    $data['code'] = 40001;
    die(json_encode($data));
}
if (empty($uid) || !is_numeric($uid)) {
    $data['data'] = "";
    $data['msg']  = "uid参数不合法！";
    $data['code'] = 40002;
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
