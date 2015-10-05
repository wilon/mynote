<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: post_newreply.php 33709 2013-08-06 09:06:56Z andyzheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('function/forumlist');

$isfirstpost = 0;
$_G['group']['allowimgcontent'] = 0;
$showthreadsorts = 0;
$quotemessage = '';

if($special == 5) {
	$debate = array_merge($thread, daddslashes(C::t('forum_debate')->fetch($_G['tid'])));
	$firststand = C::t('forum_debatepost')->get_firststand($_G['tid'], $_G['uid']);
	$stand = $firststand ? $firststand : intval($_GET['stand']);

	if($debate['endtime'] && $debate['endtime'] < TIMESTAMP) {
		showmessage2('debate_end', '', 42001);
	}
}

if(!$_G['uid'] && !((!$_G['forum']['replyperm'] && $_G['group']['allowreply']) || ($_G['forum']['replyperm'] && forumperm($_G['forum']['replyperm'])))) {
	showmessage2('replyperm_login_nopermission', '', 42002);
} elseif(empty($_G['forum']['allowreply'])) {
	if(!$_G['forum']['replyperm'] && !$_G['group']['allowreply']) {
		showmessage2('replyperm_none_nopermission', '', 42003);
	} elseif($_G['forum']['replyperm'] && !forumperm($_G['forum']['replyperm'])) {
		showmessage2('replyperm', '', 42004);
	}
} elseif($_G['forum']['allowreply'] == -1) {
	showmessage2('post_forum_newreply_nopermission', '', 42005);
}

if(!$_G['uid'] && ($_G['setting']['need_avatar'] || $_G['setting']['need_email'] || $_G['setting']['need_friendnum'])) {
	showmessage2('replyperm_login_nopermission', '', 42006);
}

if(empty($thread)) {
	showmessage2('thread_nonexistence', '', 42007);
} elseif($thread['price'] > 0 && $thread['special'] == 0 && !$_G['uid']) {
	showmessage2('group_nopermission', '', 42008);
}

checklowerlimit('reply', 0, 1, $_G['forum']['fid']);

if($special == 127) {
	$postinfo = C::t('forum_post')->fetch_threadpost_by_tid_invisible($_G['tid']);
	$sppos = strrpos($postinfo['message'], chr(0).chr(0).chr(0));
	$specialextra = substr($postinfo['message'], $sppos + 3);
}
if(getstatus($thread['status'], 3)) {
	$rushinfo = C::t('forum_threadrush')->fetch($_G['tid']);
	if($rushinfo['creditlimit'] != -996) {
		$checkcreditsvalue = $_G['setting']['creditstransextra'][11] ? getuserprofile('extcredits'.$_G['setting']['creditstransextra'][11]) : $_G['member']['credits'];
		if($checkcreditsvalue < $rushinfo['creditlimit']) {
			$creditlimit_title = $_G['setting']['creditstransextra'][11] ? $_G['setting']['extcredits'][$_G['setting']['creditstransextra'][11]]['title'] : lang('forum/misc', 'credit_total');
			showmessage2('post_rushreply_creditlimit', '', 42009);
		}
	}

}

// 发帖方法
    include_once './source/post/model_forum_post.php';
	$modpost = new model_forum_post2();
	$bfmethods = $afmethods = array();


	$params = array(
		'subject' => $subject,
		'message' => $message,
		'special' => $special,
		'extramessage' => $extramessage,
		'bbcodeoff' => $_GET['bbcodeoff'],
		'smileyoff' => $_GET['smileyoff'],
		'htmlon' => $_GET['htmlon'],
		'parseurloff' => $_GET['parseurloff'],
		'usesig' => $_GET['usesig'],
		'isanonymous' => $_GET['isanonymous'],
		'noticetrimstr' => $_GET['noticetrimstr'],
		'noticeauthor' => $_GET['noticeauthor'],
		'from' => $_GET['from'],
		'sechash' => $_GET['sechash'],
		'geoloc' => diconv($_GET['geoloc'], 'UTF-8'),
	);


	if(!empty($_GET['trade']) && $thread['special'] == 2 && $_G['group']['allowposttrade']) {
		$bfmethods[] = array('class' => 'extend_thread_trade', 'method' => 'before_newreply');
	}




	$attentionon = empty($_GET['attention_add']) ? 0 : 1;
	$attentionoff = empty($attention_remove) ? 0 : 1;
	$bfmethods[] = array('class' => 'extend_thread_rushreply', 'method' => 'before_newreply');
	if($_G['group']['allowat']) {
		$bfmethods[] = array('class' => 'extend_thread_allowat', 'method' => 'before_newreply');
	}

	$bfmethods[] = array('class' => 'extend_thread_comment', 'method' => 'before_newreply');
	$modpost->attach_before_method('newreply', array('class' => 'extend_thread_filter', 'method' => 'before_newreply'));



	if($_G['group']['allowat']) {
		$afmethods[] = array('class' => 'extend_thread_allowat', 'method' => 'after_newreply');
	}


	$afmethods[] = array('class' => 'extend_thread_rushreply', 'method' => 'after_newreply');



		$afmethods[] = array('class' => 'extend_thread_comment', 'method' => 'after_newreply');



	if(helper_access::check_module('follow') && !empty($_GET['adddynamic'])) {
		$afmethods[] = array('class' => 'extend_thread_follow', 'method' => 'after_newreply');
	}


	if($thread['replycredit'] > 0 && $thread['authorid'] != $_G['uid'] && $_G['uid']) {
		$afmethods[] = array('class' => 'extend_thread_replycredit', 'method' => 'after_newreply');
	}


	if($special == 5) {
		$afmethods[] = array('class' => 'extend_thread_debate', 'method' => 'after_newreply');
	}



	$afmethods[] = array('class' => 'extend_thread_image', 'method' => 'after_newreply');



	if($special == 2 && $_G['group']['allowposttrade'] && $thread['authorid'] == $_G['uid']) {
		$afmethods[] = array('class' => 'extend_thread_trade', 'method' => 'after_newreply');
	}
	$afmethods[] = array('class' => 'extend_thread_filter', 'method' => 'after_newreply');





		if($_G['forum']['allowfeed']) {
			if($special == 2 && !empty($_GET['trade'])) {
				$modpost->attach_before_method('replyfeed', array('class' => 'extend_thread_trade', 'method' => 'before_replyfeed'));
				$modpost->attach_after_method('replyfeed', array('class' => 'extend_thread_trade', 'method' => 'after_replyfeed'));
			} elseif($special == 3 && $thread['authorid'] != $_G['uid']) {
				$modpost->attach_before_method('replyfeed', array('class' => 'extend_thread_reward', 'method' => 'before_replyfeed'));
			} elseif($special == 5 && $thread['authorid'] != $_G['uid']) {
				$modpost->attach_before_method('replyfeed', array('class' => 'extend_thread_debate', 'method' => 'before_replyfeed'));
			}
		}




	if(!isset($_GET['addfeed'])) {
		$space = array();
		space_merge($space, 'field_home');
		$_GET['addfeed'] = $space['privacy']['feed']['newreply'];
	}

	$modpost->attach_before_methods('newreply', $bfmethods);
	$modpost->attach_after_methods('newreply', $afmethods);

	$return = $modpost->newreply($params);
	$pid = $modpost->pid;

	if($specialextra) {

		@include_once DISCUZ_ROOT.'./source/plugin/'.$_G['setting']['threadplugins'][$specialextra]['module'].'.class.php';
		$classname = 'threadplugin_'.$specialextra;
		if(class_exists($classname) && method_exists($threadpluginclass = new $classname, 'newreply_submit_end')) {
			$threadpluginclass->newreply_submit_end($_G['fid'], $_G['tid']);
		}

	}

	if($modpost->pid && !$modpost->param('modnewreplies')) {

		if(!empty($_GET['addfeed'])) {
			$modpost->replyfeed();
		}
	}


	if($modpost->param('modnewreplies')) {
		$url = "forum.php?mod=viewthread&tid=".$_G['tid'];
	} else {

		$antitheft = '';
		if(!empty($_G['setting']['antitheft']['allow']) && empty($_G['setting']['antitheft']['disable']['thread']) && empty($_G['forum']['noantitheft'])) {
			$sign = helper_antitheft::get_sign($_G['tid'], 'tid');
			if($sign) {
				$antitheft = '&_dsign='.$sign;
			}
		}

		$url = "forum.php?mod=viewthread&tid=".$_G['tid']."&pid=".$modpost->pid."&page=".$modpost->param('page')."$antitheft&extra=".$extra."#pid".$modpost->pid;
	}

	// 保存帖子图片
	$pic  = trim($_POST['pic']);
	$time = time();
	$tid  = $_G['tid'];
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

	showmessage2($return , $pid, 20000);


//}

?>