<?php
/**
 * 发布帖子图片
 *
 * @parameter $pid 帖子ID
 * @parameter $uid 用户ID
 * @parameter $file
 *
 * @Author    王伟龙 QQ:973885303
 * @FileName  t_forum_threadimg.php
 * @Date      2014-10-13 14:25:45
 *
 */
 
require("./inc.php");

// 接受数据
$pid = intval(trim($_POST['pid']));
$uid = intval(trim($_POST['uid']));

// 参数判断
$post = C::t("forum_post")->fetch('', $pid);
if (empty($post)) {
    $data['data'] = "";
    $data['msg']  = "pid参数错误，找不到此帖";
    $data['code'] = 40001;
    die(json_encode($data));
}
$tid = $post['tid'];
$uid = $post['uid'];

// 上传图片错误类型
$upfile = $_FILES['file'];
if ($upfile['error']>0) {
    switch($upfile['error']) {
        case 1: $info="上传文件大小超过了php.ini中配置"; break;
        case 2: $info="上传文件大小超过form表单中MAX_FILE_SIZE的设置"; break;
        case 3: $info="文件只有部分被上传"; break;
        case 4: $info="没有文件被上传"; break;
        case 6: $info="找不到临时文件夹"; break;
        case 7: $info="文件写入失败"; break;
        default: $info="未知错误！"; break;
    }
    $data['data'] = "";
    $data['msg']  = $info;
    $data['code'] = 40002;
    die(json_encode($data));
}

// 判断上传文件类型
$typelist = array("image/jpeg", "image/gif", "image/png");
if (!in_array($upfile['type'], $typelist)) {
    $data['data'] = "";
    $data['msg']  = "上传文件类型错误！支持jpg、gif、png.";
    $data['code'] = 40003;
    die(json_encode($data));
}

// 判断上传文件大小
if ($upfile['size'] > 1024*1024) {
    $data['data'] = "";
    $data['msg']  = "上传文件过大！";
    $data['code'] = 40004;
    die(json_encode($data));
}

// 保存图片
$time = time();
// 创建目录
$dir1 = DISCUZ_ROOT . "./data/attachment/forum/";
$dir2 = date("Ym", $time);
$dir3 = date("d", $time);
if (!file_exists($dir1 . $dir2)) {
    dmkdir($dir1 . $dir2, 0777, true);
}
clearstatcache();
if (!file_exists($dir1 . $dir2 . "/" . $dir3)) {
    dmkdir($dir1 . $dir2 . "/" . $dir3, 0777, true);
}
clearstatcache();
$path = $dir1 . $dir2 . "/" . $dir3 . "/";
// 图片名称
$ext = pathinfo($upfile['name'], PATHINFO_EXTENSION); //获取文件的后缀名
$picName = date("Ghs", $time) . strtolower(random(16, 0)) . "." . $ext;
// 保存
if (is_uploaded_file($upfile['tmp_name'])) {
    if (move_uploaded_file($upfile['tmp_name'], $path . $picName)) {
        // 上传成功，保存数据库
        $widthTmp = getimagesize($path . $picName);
        $width = $widthTmp[0];
            // 添加图片到forum_attachment(_n)
            $aid = C::t("forum_attachment")->insert(array(
                'tid' => $tid,
                'pid' => $pid,
                'uid' => $uid,
                'tableid' => $tid % 10
            ), true);
            $imgData = array(
                'aid' => $aid,
                'tid' => $tid,
                'pid' => $pid,
                'uid' => $uid,
                'dateline' => time(),
                'filesize' => $upfile['size'],
                'attachment' => $dir2 . "/" . $dir3 . "/" . $picName,
                'isimage' => 1,
                'width' => $width
            );
            C::t("forum_attachment_n")->insert('tid:' . $tid, $imgData);
            $message = $post['message'] . "[attach]{$aid}[/attach]";
        
            // 更新forum_post帖子`message`
            C::t('forum_post')->update('tid:'.$tid, $pid, array(
                'message' => $message,
                'attachment' => 2
            ));
        $data['data'] = "";
        $data['msg']  = "保存图片成功";
        $data['code'] = 20000;
        die(json_encode($data));
    } else {
        $data['data'] = "";
        $data['msg']  = "移动上传文件错误！";
        $data['code'] = 40005;
        die(json_encode($data));
    }
} else {
    $data['data'] = "";
    $data['msg']  = "不是有效的上传文件！";
    $data['code'] = 40006;
    die(json_encode($data));
}


