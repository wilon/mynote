<?php

/**
 * API，发帖方法
 * 修改自(根/source/include/post/post_newthread)
 *      
 * 王伟龙 QQ:973885303 2014-9-18 11:28:37
 */

// 初始，存在常量IN_DISCUZ
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

// 检查条件是否符合

if(empty($_G['forum']['fid']) || $_G['forum']['type'] == 'group') {
	showmessage2('Error:forum_nonexistence', '', 42001);
}

if(($special == 1 && !$_G['group']['allowpostpoll']) || ($special == 2 && !$_G['group']['allowposttrade']) || ($special == 3 && !$_G['group']['allowpostreward']) || ($special == 4 && !$_G['group']['allowpostactivity']) || ($special == 5 && !$_G['group']['allowpostdebate'])) {
	showmessage2('Error:group_nopermission', '', 42002);
}

if($_G['setting']['connect']['allow'] && $_G['setting']['accountguard']['postqqonly'] && !$_G['member']['conisbind']) {
	showmessage2('Error:postperm_qqonly_nopermission', '', 42003);
}

if(!$_G['uid'] && !((!$_G['forum']['postperm'] && $_G['group']['allowpost']) || ($_G['forum']['postperm'] && forumperm($_G['forum']['postperm'])))) {
	if(!defined('IN_MOBILE')) {
		showmessage2('Error:postperm_login_nopermission', '', 42004);
	} else {
		showmessage2('Error:postperm_login_nopermission_mobile', '', 42005);
	}
} elseif(empty($_G['forum']['allowpost'])) {
	if(!$_G['forum']['postperm'] && !$_G['group']['allowpost']) {
		showmessage2('Error:postperm_none_nopermission', '', 42006);
	} elseif($_G['forum']['postperm'] && !forumperm($_G['forum']['postperm'])) {
		showmessage2('Error:postperm_nopermission', '', 42007);
	}
} elseif($_G['forum']['allowpost'] == -1) {
	showmessage2('Error:post_forum_newthread_nopermission', '', 42008);
}

if(!$_G['uid'] && ($_G['setting']['need_avatar'] || $_G['setting']['need_email'] || $_G['setting']['need_friendnum'])) {
	showmessage2('Error:postperm_login_nopermission', '', 42009);
}

checklowerlimit('post', 0, 1, $_G['forum']['fid']);

if($_GET['mygroupid']) {
    $mygroupid = explode('__', $_GET['mygroupid']);
    $mygid = intval($mygroupid[0]);
    if($mygid) {
        $mygname = $mygroupid[1];
        if(count($mygroupid) > 2) {
            unset($mygroupid[0]);
            $mygname = implode('__', $mygroupid);
        }
        $message .= '[groupid='.intval($mygid).']'.$mygname.'[/groupid]';
        C::t('forum_forum')->update_commoncredits(intval($mygroupid[0]));
    }
}

// 导入发帖类
include_once './source/post/model_forum_thread.php';

$modthread = new model_forum_thread2();
$bfmethods = $afmethods = array();

// 添加用户信息,否则是匿名发帖。不能缺少groupid，否则会员消息判断有误
$modthread->member = $_G['member'];

$params = array(
    'subject' => $subject,
    'message' => $message,
    'typeid' => $typeid,
    'sortid' => $sortid,
    'special' => $special,
);

$_GET['save'] = $_G['uid'] ? $_GET['save'] : 0;

if ($_G['group']['allowsetpublishdate'] && $_GET['cronpublish'] && $_GET['cronpublishdate']) {
    $publishdate = strtotime($_GET['cronpublishdate']);
    if ($publishdate > $_G['timestamp']) {
        $_GET['save'] = 1;
    } else {
        $publishdate = $_G['timestamp'];
    }
} else {
    $publishdate = $_G['timestamp'];
}
$params['publishdate'] = $publishdate;
$params['save'] = $_GET['save'];

$params['sticktopic'] = $_GET['sticktopic'];

$params['digest'] = $_GET['addtodigest'];
$params['readperm'] = $readperm;
$params['isanonymous'] = $_GET['isanonymous'];
$params['price'] = $_GET['price'];


if(in_array($special, array(1, 2, 3, 4, 5))) {
    $specials = array(
        1 => 'extend_thread_poll',
        2 => 'extend_thread_trade',
        3 => 'extend_thread_reward',
        4 => 'extend_thread_activity',
        5 => 'extend_thread_debate'
    );
    $bfmethods[] = array('class' => $specials[$special], 'method' => 'before_newthread');
    $afmethods[] = array('class' => $specials[$special], 'method' => 'after_newthread');

    if(!empty($_GET['addfeed'])) {
        $modthread->attach_before_method('feed', array('class' => $specials[$special], 'method' => 'before_feed'));
        if($special == 2) {
            $modthread->attach_before_method('feed', array('class' => $specials[$special], 'method' => 'before_replyfeed'));
        }
    }
}

if($special == 1) {


} elseif($special == 3) {


} elseif($special == 4) {
} elseif($special == 5) {


} elseif($specialextra) {

    @include_once DISCUZ_ROOT.'./source/plugin/'.$_G['setting']['threadplugins'][$specialextra]['module'].'.class.php';
    $classname = 'threadplugin_'.$specialextra;
    if(class_exists($classname) && method_exists($threadpluginclass = new $classname, 'newthread_submit')) {
        $threadpluginclass->newthread_submit($_G['fid']);
    }
    $special = 127;
    $params['special'] = 127;
    $params['message'] .= chr(0).chr(0).chr(0).$specialextra;

}

$params['typeexpiration'] = $_GET['typeexpiration'];






$params['ordertype'] = $_GET['ordertype'];

$params['hiddenreplies'] = $_GET['hiddenreplies'];

$params['allownoticeauthor'] = $_GET['allownoticeauthor'];
$params['tags'] = $_GET['tags'];
$params['bbcodeoff'] = $_GET['bbcodeoff'];
$params['smileyoff'] = $_GET['smileyoff'];
$params['parseurloff'] = $_GET['parseurloff'];
$params['usesig'] = $_GET['usesig'];
$params['htmlon'] = $_GET['htmlon'];
if($_G['group']['allowimgcontent']) {
    $params['imgcontent'] = $_GET['imgcontent'];
    $params['imgcontentwidth'] = $_G['setting']['imgcontentwidth'] ? intval($_G['setting']['imgcontentwidth']) : 100;
}

$params['geoloc'] = diconv($_GET['geoloc'], 'UTF-8');

if($_GET['rushreply']) {
    $bfmethods[] = array('class' => 'extend_thread_rushreply', 'method' => 'before_newthread');
    $afmethods[] = array('class' => 'extend_thread_rushreply', 'method' => 'after_newthread');
}

$bfmethods[] = array('class' => 'extend_thread_replycredit', 'method' => 'before_newthread');
$afmethods[] = array('class' => 'extend_thread_replycredit', 'method' => 'after_newthread');

if($sortid) {
    $bfmethods[] = array('class' => 'extend_thread_sort', 'method' => 'before_newthread');
    $afmethods[] = array('class' => 'extend_thread_sort', 'method' => 'after_newthread');
}
$bfmethods[] = array('class' => 'extend_thread_allowat', 'method' => 'before_newthread');
$afmethods[] = array('class' => 'extend_thread_allowat', 'method' => 'after_newthread');
$afmethods[] = array('class' => 'extend_thread_image', 'method' => 'after_newthread');

if(!empty($_GET['adddynamic'])) {
    $afmethods[] = array('class' => 'extend_thread_follow', 'method' => 'after_newthread');
}

$modthread->attach_before_methods('newthread', $bfmethods);
$modthread->attach_after_methods('newthread', $afmethods);

// 判断分类id符合与否
if ($sortid && !array_key_exists($sortid, $_G['forum']['threadsorts']['types']))
    showmessage2('分类id不属于此版块！', '', 42010);
// 发帖
$return = $modthread->newthread($params);
$tid = $modthread->tid;
$pid = $modthread->pid;

// 填写分类信息
if (!empty($sortid)) {
    // 图片附件，保存图片，添加数据
    if (!empty($pic)) {
        // 创建目录
        $dir1 = DISCUZ_ROOT . "/data/attachment/forum/";
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
        // 保存图片
        
        $dir = $dir1 . $dir2 . "/" . $dir3 . "/";

        // 创建图片文件
        $picName = date("Ghs", $time) . strtolower(random(16, 0)) . ".jpg";
        @file_put_contents($dir . $picName, base64_decode($pic));
        @chmod($dir . $picName, 0777);
        
        // 存储filesize、attachment、width
        $att['filesize'] = filesize($dir . $picName);
        $att['attachment'] = $dir2 . "/" . $dir3 . "/" . $picName;
        $attWidth = getimagesize($dir . $picName);
        $att['width'] = $attWidth[0];
        
        // 添加图片到forum_attachment(_n)
        $aid = C::t("forum_attachment")->insert(array(
            'tid' => $tid,
            'pid' => $pid,
            'uid' => $_G['uid'],
            'tableid' => $tid % 10
        ), true);
        $imgData = array(
            'aid' => $aid,
            'tid' => $tid,
            'pid' => $pid,
            'uid' => $_G['uid'],
            'dateline' => time(),
            'filesize' => $att['filesize'],
            'attachment' => $att['attachment'],
            'isimage' => 1,
            'width' =>$att['width']
        );
        C::t("forum_attachment_n")->insert('tid:' . $tid, $imgData);
        
        $aidLegth = strlen($aid);
    }
    $pic = 'a:2:{s:3:"aid";s:'
        . $aidLegth 
        . ':"'
        . $aid
        . '";s:3:"url";s:58:"data/attachment/forum/'
        . $att['attachment']
        . '";}';
    
    $types = $_G['forum_checkoption'];
    //die(print_r($types));
    $typeexpiration = $_POST['typeexpiration'];
    foreach ($types as $typeKey=>$typeVal) {
        if ($typeKey == 'pic') {
            $postVal = $pic;
        } else {
            $postVal = $_POST[$typeKey];
        }
        C::t('forum_typeoptionvar')->insert(array(
            'sortid' => $sortid,
            'tid' => $tid,
            'fid' => $fid,
            'optionid' => $typeVal['optionid'],
            'value' => $postVal,
            'expiration' => ($typeexpiration ? time() + $typeexpiration : 0),
        ));
        
        // 得到字段、值
        $filednameArr[] = $typeKey;
        $valuelistArr[] = "'" . $postVal . "'";
    }
    
    $filedname = implode($filednameArr, ",");
    $valuelist = implode($valuelistArr, ",");
    
    C::t('forum_optionvalue')->insert($sortid, "($filedname, tid, fid) VALUES ($valuelist, '{$tid}', '$fid')");
}

//dsetcookie('clearUserdata', 'forum');
if($specialextra) {
    $classname = 'threadplugin_'.$specialextra;
    if(class_exists($classname) && method_exists($threadpluginclass = new $classname, 'newthread_submit_end')) {
        $threadpluginclass->newthread_submit_end($_G['fid'], $modthread->tid);
    }
}
if(!$modthread->param('modnewthreads') && !empty($_GET['addfeed'])) {
    $modthread->feed();
}
/* （弃用）发表成功，跳转
if(!empty($_G['setting']['rewriterule']['forum_viewthread']) && in_array('forum_viewthread', $_G['setting']['rewritestatus'])) {
    $returnurl = rewriteoutput('forum_viewthread', 1, '', $modthread->tid, 1, '', $extra);
} else {
    $returnurl = "forum.php?mod=viewthread&tid={$modthread->tid}&extra=$extra";
}
$values = array('fid' => $modthread->forum('fid'), 'tid' => $modthread->tid, 'pid' => $modthread->pid, 'coverimg' => '', 'sechash' => !empty($_GET['sechash']) ? $_GET['sechash'] : '');
showmessage($return, $returnurl, array_merge($values, (array)$modthread->param('values')), $modthread->param('param'));
*/


// 图片附件，保存图片，添加数据
if (!empty($pic)) {
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
    // 保存图片
    $picArr = explode(",", $pic);
    $dir = $dir1 . $dir2 . "/" . $dir3 . "/";
    $attArr = array();
    foreach ($picArr as $k=>$v) {
        // 创建图片文件
        $picName = date("Ghs", $time) . strtolower(random(16, 0)) . ".jpg";
        @file_put_contents($dir . $picName, base64_decode($v));
        @chmod($dir . $picName, 0777);
        // 存储filesize、attachment、width
        $attArr[$k]['filesize'] = filesize($dir . $picName);
        $attArr[$k]['attachment'] = $dir2 . "/" . $dir3 . "/" . $picName;
        $attArrTmp = getimagesize($dir . $picName);
        $attArr[$k]['width'] = $attArrTmp[0];
        unset($picName);
    }

    // 添加图片到forum_attachment(_n)
    foreach ($attArr as $v2) {
        $aid = C::t("forum_attachment")->insert(array(
            'tid' => $tid,
            'pid' => $pid,
            'uid' => $_G['uid'],
            'tableid' => $tid % 10
        ), true);
        $imgData = array(
            'aid' => $aid,
            'tid' => $tid,
            'pid' => $pid,
            'uid' => $_G['uid'],
            'dateline' => time(),
            'filesize' => $v2['filesize'],
            'attachment' => $v2['attachment'],
            'isimage' => 1,
            'width' =>$v2['width']
        );
        C::t("forum_attachment_n")->insert('tid:' . $tid, $imgData);
        $message .= "[attach]{$aid}[/attach]";
        unset($aid, $imgData);
    }
    // 更新forum_post帖子`message`
    C::t('forum_post')->update('tid:'.$tid, $pid, array(
    'message' => $message,
    'attachment' => 2
    ));
}

// 发表成功
showmessage2($return, $pid, 20000);

?>