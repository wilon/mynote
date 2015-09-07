<?php

/**
 * 浏览帖子主题
 *
 * @parameter $tid 主题ID
 *
 * 改自方法："Discuz!/source/module/forum/forum_viewthread.php';
 *
 * @Author    王伟龙 QQ:973885303
 * @FileName  t_forum_viewthread.php
 * @Date      2015-1-17 17:57:52
 *
 */
 

//define("STATICURL", "../static/");
define("IN_MOBILE", ''); // 排除掉手机模式，否则不显示图片
define("TPL_DEFAULT", ''); // 模板
require("./inc.php");


require_once libfile('function/forumlist');
require_once libfile('function/discuzcode');
require_once libfile('function/post');

$thread = & $_G['forum_thread'];
$forum = & $_G['forum'];

if(!$_G['forum_thread'] || !$_G['forum']) {
	showmessage2('Error:thread_nonexistence', '', 43001);

}

$_G['page'] = intval($_REQUEST['page']);
$page = max(1, $_G['page']);

if($_G['setting']['cachethreadlife'] && $_G['forum']['threadcaches'] && !$_G['uid'] && $page == 1 && !$_G['forum']['special'] && empty($_G['gp_do']) && !defined('IN_ARCHIVER') && !defined('IN_MOBILE')) {
	viewthread_loadcache();
}

$threadtableids = !empty($_G['cache']['threadtableids']) ? $_G['cache']['threadtableids'] : array();
$threadtable_info = !empty($_G['cache']['threadtable_info']) ? $_G['cache']['threadtable_info'] : array();

$archiveid = $thread['threadtableid'];
$thread['is_archived'] = $archiveid ? true : false;
$thread['archiveid'] = $archiveid;
$forum['threadtableid'] = $archiveid;
$threadtable = $thread['threadtable'];
$posttableid = $thread['posttableid'];
$posttable = $thread['posttable'];


$_G['action']['fid'] = $_G['fid'];
$_G['action']['tid'] = $_G['tid'];

$_G['gp_authorid'] = !empty($_G['gp_authorid']) ? intval($_G['gp_authorid']) : 0;
$_G['gp_ordertype'] = !empty($_G['gp_ordertype']) ? intval($_G['gp_ordertype']) : 0;
$_G['gp_from'] = $_G['setting']['portalstatus'] && !empty($_G['gp_from']) && $_G['gp_from'] == 'portal' ? 'portal' : '';

$fromuid = $_G['setting']['creditspolicy']['promotion_visit'] && $_G['uid'] ? '&amp;fromuid='.$_G['uid'] : '';
$feeduid = $_G['forum_thread']['authorid'] ? $_G['forum_thread']['authorid'] : 0;
$feedpostnum = $_G['forum_thread']['replies'] > $_G['ppp'] ? $_G['ppp'] : ($_G['forum_thread']['replies'] ? $_G['forum_thread']['replies'] : 1);

if(!empty($_G['gp_extra'])) {
	parse_str($_G['gp_extra'], $extra);
	$_G['gp_extra'] = array();
	foreach($extra as $_k => $_v) {
		if(preg_match('/^\w+$/', $_k)) {
			if(!is_array($_v)) {
				$_G['gp_extra'][] = $_k.'='.$_v;
			} else {
				$_G['gp_extra'][] = http_build_query(array($_k => $_v));
			}
		}
	}
	$_G['gp_extra'] = implode('&', $_G['gp_extra']);
}


$aimgs = array();
$skipaids = array();

$oldthreads = viewthread_oldtopics(!$archiveid ? $_G['tid'] : 0);

$thread['subjectenc'] = rawurlencode($_G['forum_thread']['subject']);
$thread['short_subject'] = cutstr($_G['forum_thread']['subject'], 52);

$navigation = '';
if($_G['gp_from'] == 'portal') {

	$_G['setting']['ratelogon'] = 1;
	$navigation = ' <em>&rsaquo;</em> <a href="portal.php">'.lang('core', 'portal').'</a>';
	$navsubject = $_G['forum_thread']['subject'];
	$navtitle = $_G['forum_thread']['subject'];


} elseif($_G['forum']['status'] == 3) {
	$_G['action']['action'] = 3;
	require_once libfile('function/group');
	$status = groupperm($_G['forum'], $_G['uid']);
	if($status == 1) {
		showmessage2('Error:forum_group_status_off', '', 43002);
	} elseif($status == 2) {
		showmessage2('Error:forum_group_noallowed', '', 43003);
	} elseif($status == 3) {
		showmessage2('Error:forum_group_moderated', '', 43004);
	}
	$nav = get_groupnav($_G['forum']);
	$navigation = ' <em>&rsaquo;</em> <a href="group.php">'.$_G['setting']['navs'][3]['navname'].'</a> '.$nav['nav'];
	$_G['grouptypeid'] = $_G['forum']['fup'];

} else {
	$navigation = ' <em>&rsaquo;</em> <a href="forum.php">'.$_G['setting']['navs'][2]['navname'].'</a>';
	$upnavlink = 'forum.php?mod=forumdisplay&fid='.$_G['fid'].($_G['gp_extra'] && !IS_ROBOT ? '&'.$_G['gp_extra'] : '');

	if($_G['forum']['type'] == 'sub') {
		$fup = $_G['cache']['forums'][$_G['forum']['fup']]['fup'];
		$t_link = $_G['cache']['forums'][$fup]['type'] == 'group' ? 'forum.php?gid='.$fup : 'forum.php?mod=forumdisplay&fid='.$fup;
		$navigation .= ' <em>&rsaquo;</em> <a href="'.$t_link.'">'.strip_tags($_G['cache']['forums'][$fup]['name']).'</a>';
	}

	if($_G['forum']['fup']) {
		$fup = $_G['forum']['fup'];
		$t_link = $_G['cache']['forums'][$fup]['type'] == 'group' ? 'forum.php?gid='.$fup : 'forum.php?mod=forumdisplay&fid='.$fup;
		$navigation .= ' <em>&rsaquo;</em> <a href="'.$t_link.'">'.strip_tags($_G['cache']['forums'][$fup]['name']).'</a>';
	}

	$t_link = 'forum.php?mod=forumdisplay&fid='.$_G['fid'].($_G['gp_extra'] && !IS_ROBOT ? '&'.$_G['gp_extra'] : '');
	$navigation .= ' <em>&rsaquo;</em> <a href="'.$t_link.'">'.strip_tags($_G['forum']['name']).'</a>';

	if($archiveid) {
		if($threadtable_info[$archiveid]['displayname']) {
			$t_name = htmlspecialchars($threadtable_info[$archiveid]['displayname']);
		} else {
			$t_name = lang('core', 'archive').' '.$archiveid;
		}
		$navigation .= ' <em>&rsaquo;</em> <a href="forum.php?mod=forumdisplay&fid='.$_G['fid'].'&archiveid='.$archiveid.'">'.$t_name.'</a>';
	}

	unset($t_link, $t_name);
}


$_G['gp_extra'] = $_G['gp_extra'] ? rawurlencode($_G['gp_extra']) : '';

if(@in_array('forum_viewthread', $_G['setting']['rewritestatus'])) {
	$canonical = rewriteoutput('forum_viewthread', 1, '', $_G['tid'], 1, '', '');
} else {
	$canonical = 'forum.php?mod=viewthread&tid='.$_G['tid'];
}
$_G['setting']['seohead'] .= '<link href="'.$_G['siteurl'].$canonical.'" rel="canonical" />';

$_G['forum_tagscript'] = '';

$threadsort = $thread['sortid'] && isset($_G['forum']['threadsorts']['types'][$thread['sortid']]) ? 1 : 0;
if($threadsort) {
	require_once libfile('function/threadsort');
	$threadsortshow = threadsortshow($thread['sortid'], $_G['tid']);
}

if(empty($_G['forum']['allowview'])) {

	if(!$_G['forum']['viewperm'] && !$_G['group']['readaccess']) {
		showmessage2('Error:group_nopermission', '', 43005);
	} elseif($_G['forum']['viewperm'] && !forumperm($_G['forum']['viewperm'])) {
		showmessage2('Error:viewperm', '', 43006);
	}

} elseif($_G['forum']['allowview'] == -1) {
	showmessage2('Error:forum_access_view_disallow', '', 43007);
}

if($_G['forum']['formulaperm']) {
	formulaperm($_G['forum']['formulaperm']);
}

if($_G['forum']['password'] && $_G['forum']['password'] != $_G['cookie']['fidpw'.$_G['fid']]) {
	dheader("Location: $_G[siteurl]forum.php?mod=forumdisplay&fid=$_G[fid]");
}

if($_G['forum_thread']['readperm'] && $_G['forum_thread']['readperm'] > $_G['group']['readaccess'] && !$_G['forum']['ismoderator'] && $_G['forum_thread']['authorid'] != $_G['uid']) {
	showmessage2('Error:thread_nopermission', '', 43008);
}

$usemagic = array('user' => array(), 'thread' => array());

$replynotice = getstatus($_G['forum_thread']['status'], 6);

$hiddenreplies = getstatus($_G['forum_thread']['status'], 2);

$rushreply = getstatus($_G['forum_thread']['status'], 3);

$savepostposition = getstatus($_G['forum_thread']['status'], 1);

$_G['forum_threadpay'] = FALSE;
if($_G['forum_thread']['price'] > 0 && $_G['forum_thread']['special'] == 0) {
	if($_G['setting']['maxchargespan'] && TIMESTAMP - $_G['forum_thread']['dateline'] >= $_G['setting']['maxchargespan'] * 3600) {
		DB::query("UPDATE ".DB::table($threadtable)." SET price='0' WHERE tid='$_G[tid]'");
		$_G['forum_thread']['price'] = 0;
	} else {
		$exemptvalue = $_G['forum']['ismoderator'] ? 128 : 16;
		if(!($_G['group']['exempt'] & $exemptvalue) && $_G['forum_thread']['authorid'] != $_G['uid']) {
			$query = DB::query("SELECT relatedid FROM ".DB::table('common_credit_log')." WHERE relatedid='$_G[tid]' AND uid='$_G[uid]' AND operation='BTC'");
			if(!DB::num_rows($query)) {
				require_once libfile('thread/pay', 'include');
				$_G['forum_threadpay'] = TRUE;
			}
		}
	}
}

if($rushreply) {
	$rewardfloor = '';
	$rushresult = $rewardfloorarr = $rewardfloorarray = array();
	$rushresult = DB::fetch_first("SELECT * FROM ".DB::table('forum_threadrush')." WHERE tid='$_G[tid]'");

	if((TIMESTAMP < $rushresult['starttimefrom'] || ($rushresult['starttimeto'] && TIMESTAMP > $rushresult['starttimeto']) || ($rushresult['stopfloor'] && $_G['forum_thread']['replies'] + 1 >= $rushresult['stopfloor'])) && $_G['forum_thread']['closed'] == 0) {
		DB::query("UPDATE ".DB::table('forum_thread')." SET closed='1' WHERE tid='$_G[tid]'");
	} elseif(($rushresult['starttimefrom'] && TIMESTAMP > $rushresult['starttimefrom']) && $_G['forum_thread']['closed'] == 1) {
		if(!$rushresult['starttimeto'] && !$rushresult['stopfloor']) {
			DB::query("UPDATE ".DB::table('forum_thread')." SET closed='0' WHERE tid='$_G[tid]'");
		} else {
			if(($rushresult['starttimeto'] && TIMESTAMP < $rushresult['starttimeto']) || ($rushresult['stopfloor'] && $_G['forum_thread']['replies'] + 1 < $rushresult['stopfloor'])) {
				DB::query("UPDATE ".DB::table('forum_thread')." SET closed='0' WHERE tid='$_G[tid]'");
			}
		}
	}
	$rushresult['starttimefrom'] = $rushresult['starttimefrom'] ? dgmdate($rushresult['starttimefrom']) : '';
	$rushresult['starttimeto'] = $rushresult['starttimeto'] ? dgmdate($rushresult['starttimeto']) : '';
}

if($_G['forum_thread']['replycredit'] > 0) {
	$_G['forum_thread']['replycredit_rule'] = DB::fetch_first("SELECT * FROM ".DB::table('forum_replycredit')." WHERE tid = '{$thread['tid']}' LIMIT 1");
	$_G['forum_thread']['replycredit_rule']['remaining'] = $_G['forum_thread']['replycredit'] / $_G['forum_thread']['replycredit_rule']['extcredits'];
	$_G['forum_thread']['replycredit_rule']['extcreditstype'] = $_G['forum_thread']['replycredit_rule']['extcreditstype'] ? $_G['forum_thread']['replycredit_rule']['extcreditstype'] : $_G['setting']['creditstransextra'][10] ;
}
$_G['group']['raterange'] = $_G['setting']['modratelimit'] && $adminid == 3 && !$_G['forum']['ismoderator'] ? array() : $_G['group']['raterange'];

$_G['group']['allowgetattach'] = !empty($_G['forum']['allowgetattach']) || ($_G['group']['allowgetattach'] && !$_G['forum']['getattachperm']) || forumperm($_G['forum']['getattachperm']);
$_G['group']['allowgetimage'] = !empty($_G['forum']['allowgetimage']) || ($_G['group']['allowgetimage'] && !$_G['forum']['getattachperm']) || forumperm($_G['forum']['getattachperm']);
$_G['getattachcredits'] = '';
if($_G['forum_thread']['attachment']) {
	$exemptvalue = $_G['forum']['ismoderator'] ? 32 : 4;
	if(!($_G['group']['exempt'] & $exemptvalue)) {
		$creditlog = updatecreditbyaction('getattach', $_G['uid'], array(), '', 1, 0, $_G['forum_thread']['fid']);
		$p = '';
		if($creditlog['updatecredit']) for($i = 1;$i <= 8;$i++) {
			if($policy = $creditlog['extcredits'.$i]) {
				$_G['getattachcredits'] .= $p.$_G['setting']['extcredits'][$i]['title'].' '.$policy.' '.$_G['setting']['extcredits'][$i]['unit'];
				$p = ', ';
			}
		}
	}
}

$exemptvalue = $_G['forum']['ismoderator'] ? 64 : 8;
$_G['forum_attachmentdown'] = $_G['group']['exempt'] & $exemptvalue;

$seccodecheck = ($_G['setting']['seccodestatus'] & 4) && (!$_G['setting']['seccodedata']['minposts'] || getuserprofile('posts') < $_G['setting']['seccodedata']['minposts']);
$secqaacheck = $_G['setting']['secqaa']['status'] & 2 && (!$_G['setting']['secqaa']['minposts'] || getuserprofile('posts') < $_G['setting']['secqaa']['minposts']);

$postlist = $_G['forum_attachtags'] = $attachlist = $_G['forum_threadstamp'] = array();
$aimgcount = 0;
$_G['forum_attachpids'] = -1;

if(!empty($_G['gp_action']) && $_G['gp_action'] == 'printable' && $_G['tid']) {
	require_once libfile('thread/printable', 'include');
	dexit();
}

if($_G['forum_thread']['stamp'] >= 0) {
	$_G['forum_threadstamp'] = $_G['cache']['stamps'][$_G['forum_thread']['stamp']];
}

$lastmod = viewthread_lastmod($_G['forum_thread']);

$showsettings = str_pad(decbin($_G['setting']['showsettings']), 3, '0', STR_PAD_LEFT);

$showsignatures = $showsettings{0};
$showavatars = $showsettings{1};
$_G['setting']['showimages'] = $showsettings{2};

$highlightstatus = isset($_G['gp_highlight']) && str_replace('+', '', $_G['gp_highlight']) ? 1 : 0;

$_G['forum']['allowreply'] = isset($_G['forum']['allowreply']) ? $_G['forum']['allowreply'] : '';
$_G['forum']['allowpost'] = isset($_G['forum']['allowpost']) ? $_G['forum']['allowpost'] : '';

$allowpostreply = ($_G['forum']['allowreply'] != -1) && (($_G['forum_thread']['isgroup'] || (!$_G['forum_thread']['closed'] && !checkautoclose($_G['forum_thread']))) || $_G['forum']['ismoderator']) && ((!$_G['forum']['replyperm'] && $_G['group']['allowreply']) || ($_G['forum']['replyperm'] && forumperm($_G['forum']['replyperm'])) || $_G['forum']['allowreply']);
if(!$_G['uid'] && ($_G['setting']['need_avatar'] || $_G['setting']['need_email'] || $_G['setting']['need_friendnum']) || !$_G['adminid'] && (!cknewuser(1) || $_G['setting']['newbiespan'] && (!getuserprofile('lastpost') || TIMESTAMP - getuserprofile('lastpost') < $_G['setting']['newbiespan'] * 60) && TIMESTAMP - $_G['member']['regdate'] < $_G['setting']['newbiespan'] * 60)) {
	$allowpostreply = false;
}
$_G['group']['allowpost'] = $_G['forum']['allowpost'] != -1 && ((!$_G['forum']['postperm'] && $_G['group']['allowpost']) || ($_G['forum']['postperm'] && forumperm($_G['forum']['postperm'])) || $_G['forum']['allowpost']);

$_G['forum']['allowpostattach'] = isset($_G['forum']['allowpostattach']) ? $_G['forum']['allowpostattach'] : '';
$allowpostattach = $allowpostreply && ($_G['forum']['allowpostattach'] != -1 && ($_G['forum']['allowpostattach'] == 1 || (!$_G['forum']['postattachperm'] && $_G['group']['allowpostattach']) || ($_G['forum']['postattachperm'] && forumperm($_G['forum']['postattachperm']))));
if(!$allowpostattach && $allowpostreply) {
	$_G['forum']['allowpostimage'] = isset($_G['forum']['allowpostimage']) ? $_G['forum']['allowpostimage'] : '';
	$allowpostattach = $_G['forum']['allowpostimage'] != -1 && ($_G['forum']['allowpostimage'] == 1 || (!$_G['forum']['postimageperm'] && $_G['group']['allowpostimage']) || ($_G['forum']['postimageperm'] && forumperm($_G['forum']['postimageperm'])));
}

if($_G['group']['allowpost']) {
	$_G['group']['allowpostpoll'] = $_G['group']['allowpostpoll'] && ($_G['forum']['allowpostspecial'] & 1);
	$_G['group']['allowposttrade'] = $_G['group']['allowposttrade'] && ($_G['forum']['allowpostspecial'] & 2);
	$_G['group']['allowpostreward'] = $_G['group']['allowpostreward'] && ($_G['forum']['allowpostspecial'] & 4) && isset($_G['setting']['extcredits'][$_G['setting']['creditstrans']]);
	$_G['group']['allowpostactivity'] = $_G['group']['allowpostactivity'] && ($_G['forum']['allowpostspecial'] & 8);
	$_G['group']['allowpostdebate'] = $_G['group']['allowpostdebate'] && ($_G['forum']['allowpostspecial'] & 16);
} else {
	$_G['group']['allowpostpoll'] = $_G['group']['allowposttrade'] = $_G['group']['allowpostreward'] = $_G['group']['allowpostactivity'] = $_G['group']['allowpostdebate'] = FALSE;
}

$_G['forum']['threadplugin'] = $_G['group']['allowpost'] && $_G['setting']['threadplugins'] ? is_array($_G['forum']['threadplugin']) ? $_G['forum']['threadplugin'] : unserialize($_G['forum']['threadplugin']) : array();

$_G['setting']['visitedforums'] = $_G['setting']['visitedforums'] ? visitedforums() : '';



$relatedthreadlist = array();
$relatedthreadupdate = $tagupdate = FALSE;
$relatedkeywords = $tradekeywords = $_G['forum_firstpid'] = '';

if(!isset($_G['cookie']['collapse']) || strpos($_G['cookie']['collapse'], 'modarea_c') === FALSE) {
	$collapseimg['modarea_c'] = 'collapsed_no';
	$collapse['modarea_c'] = '';
} else {
	$collapseimg['modarea_c'] = 'collapsed_yes';
	$collapse['modarea_c'] = 'display: none';
}

$threadtag = array();

viewthread_updateviews($threadtable);

$_G['setting']['infosidestatus']['posts'] = $_G['setting']['infosidestatus'][1] && isset($_G['setting']['infosidestatus']['f'.$_G['fid']]['posts']) ? $_G['setting']['infosidestatus']['f'.$_G['fid']]['posts'] : $_G['setting']['infosidestatus']['posts'];


$postfieldsadd = $specialadd1 = $specialadd2 = $specialextra = '';

if($_G['forum_thread']['special'] == 2) {
	if(!empty($_G['gp_do']) && $_G['gp_do'] == 'tradeinfo') {
		require_once libfile('thread/trade', 'include');
	}
	$query = DB::query("SELECT pid FROM ".DB::table('forum_trade')." WHERE tid='$_G[tid]'");
	while($trade = DB::fetch($query)) {
		$tpids[] = $trade['pid'];
	}
	$specialadd2 = " AND pid NOT IN (".dimplode($tpids).")";

} elseif($_G['forum_thread']['special'] == 5) {
	$_G['gp_stand'] = isset($_G['gp_stand']) && in_array($_G['gp_stand'], array(0, 1, 2)) ? $_G['gp_stand'] : null;
	if(isset($_G['gp_stand'])) {
		$specialadd1 .= "LEFT JOIN ".DB::table('forum_debatepost')." dp ON p.pid=dp.pid";
		if($_G['gp_stand']) {
			$specialadd2 .= "AND (dp.stand='$_G[gp_stand]' OR p.first='1')";
		} else {
			$specialadd2 .= "AND (dp.stand='0' OR dp.stand IS NULL OR p.first='1')";
		}
		$specialextra = "&amp;stand=$_G[gp_stand]";
	} else {
		$specialadd1 = "LEFT JOIN ".DB::table('forum_debatepost')." dp ON p.pid=dp.pid";
	}
	$postfieldsadd .= ", dp.stand, dp.voters";
}

$onlyauthoradd = $threadplughtml = '';

if(empty($_G['gp_viewpid'])) {

	$ordertype = empty($_G['gp_ordertype']) && getstatus($_G['forum_thread']['status'], 4) ? 1 : $_G['gp_ordertype'];

	$sticklist = array();
	if($_G['forum_thread']['stickreply'] && $page == 1 && !$_G['gp_authorid'] && !$ordertype) {
		$query = DB::query("SELECT p.*, ps.position FROM ".DB::table('forum_poststick')." ps
			LEFT JOIN ".DB::table($posttable)." p USING(pid)
			WHERE ps.tid='$_G[tid]' ORDER BY ps.dateline DESC");
		while($post = DB::fetch($query)) {
			$post['message'] = messagecutstr($post['message'], 400);
			$post['avatar'] = avatar($post['authorid'], 'small');
			$sticklist[$post['pid']] = $post;
		}
		$stickcount = count($sticklist);
	}
	if($rushreply) {
		$rushids = $rushpids = $rushpositionlist = $preg = $arr = array();
		$str = ',,';
		$preg_str = rushreply_rule($rushresult);
		if($_G['gp_checkrush']) {
			for($i = 1; $i <= $_G['forum_thread']['replies'] + 1; $i++) {
				$str = $str.$i.',,';
			}
			preg_match_all($preg_str, $str, $arr);
			$arr = $arr[0];
			foreach($arr as $var) {
				$var = str_replace(',', '', $var);
				$rushids[$var] = $var;
			}
			$temp_reply = $_G['forum_thread']['replies'];
			$_G['forum_thread']['replies'] = count($rushids) - 1;
			$rushids = array_slice($rushids, ($page - 1) * $_G['ppp'], $_G['ppp']);
			$rushquery = DB::query("SELECT pid, position FROM ".DB::table('forum_postposition')." WHERE tid='$_G[tid]' AND position IN (".dimplode($rushids).") ORDER BY position");
			while($post = DB::fetch($rushquery)) {
				$rushpids[] = $post['pid'];
				$rushpositionlist[$post['pid']] = $post['position'];
			}
		} else {
			$maxposition = DB::result_first("SELECT position FROM ".DB::table('forum_postposition')." WHERE tid='$_G[tid]' ORDER BY position DESC LIMIT 1");
			$_G['forum_thread']['replies'] = $maxposition;
			for($i = ($page - 1) * $_G['ppp'] + 1; $i <= $page * $_G['ppp']; $i++) {
				$str = $str.$i.',,';
			}
			preg_match_all($preg_str, $str, $arr);
			$arr = $arr[0];
			foreach($arr as $var) {
				$var = str_replace(',', '', $var);
				$rushids[$var] = $var;
			}
			$_G['forum_thread']['replies'] = $_G['forum_thread']['replies'] - 1;
		}
	}

	if($_G['gp_authorid']) {
		$_G['forum_thread']['replies'] = DB::result_first("SELECT COUNT(*) FROM ".DB::table($posttable)." WHERE tid='$_G[tid]' AND invisible='0' AND authorid='$_G[gp_authorid]'");
		$_G['forum_thread']['replies']--;
		if($_G['forum_thread']['replies'] < 0) {
			showmessage2('Error:undefined_action', '', 43009);
		}
		$onlyauthoradd = "AND p.authorid='$_G[gp_authorid]'";
	} elseif($_G['forum_thread']['special'] == 5) {
		if(isset($_G['gp_stand']) && $_G['gp_stand'] >= 0 && $_G['gp_stand'] < 3) {
			$_G['forum_thread']['replies'] = DB::result_first("SELECT COUNT(*) FROM ".DB::table('forum_debatepost')." WHERE tid='$_G[tid]' AND stand='$_G[gp_stand]'");
		} else {
			$_G['forum_thread']['replies'] = DB::result_first("SELECT COUNT(*) FROM ".DB::table($posttable)." WHERE tid='$_G[tid]' AND invisible='0'");
			$_G['forum_thread']['replies'] > 0 && $_G['forum_thread']['replies']--;
		}
	} elseif($_G['forum_thread']['special'] == 2) {
		$tradenum = DB::result_first("SELECT count(*) FROM ".DB::table('forum_trade')." WHERE tid='$_G[tid]'");
		$_G['forum_thread']['replies'] -= $tradenum;
	}

	$_G['ppp'] = $_G['forum']['threadcaches'] && !$_G['uid'] ? $_G['setting']['postperpage'] : $_G['ppp'];
	$totalpage = ceil(($_G['forum_thread']['replies'] + 1) / $_G['ppp']);
	// $page > $totalpage && $page = $totalpage;
	// ajax请求页，到最后一页时
    $page > $totalpage && die;
	$_G['forum_pagebydesc'] = $page > 50 && $page > ($totalpage / 2) ? TRUE : FALSE;

	if($_G['forum_pagebydesc']) {
		$firstpagesize = ($_G['forum_thread']['replies'] + 1) % $_G['ppp'];
		$_G['forum_ppp3'] = $_G['forum_ppp2'] = $page == $totalpage && $firstpagesize ? $firstpagesize : $_G['ppp'];
		$realpage = $totalpage - $page + 1;
		if($firstpagesize == 0) {
			$firstpagesize = $_G['ppp'];
		}
		$start_limit = max(0, ($realpage - 2) * $_G['ppp'] + $firstpagesize);
		$_G['forum_numpost'] = ($page - 1) * $_G['ppp'];
		if($ordertype != 1) {
			$pageadd =  "ORDER BY p.dateline DESC LIMIT $start_limit, ".$_G['forum_ppp2'];
		} else {
			$_G['forum_numpost'] = $_G['forum_thread']['replies'] + 2 - $_G['forum_numpost'] + ($page > 1 ? 1 : 0);
			$pageadd = "ORDER BY p.first ASC, p.dateline ASC LIMIT $start_limit, ".$_G['forum_ppp2'];
		}
	} else {
		$start_limit = $_G['forum_numpost'] = max(0, ($page - 1) * $_G['ppp']);
		if($start_limit > $_G['forum_thread']['replies']) {
			$start_limit = $_G['forum_numpost'] = 0;
			$page = 1;
		}
		if($ordertype != 1) {
			$pageadd = "ORDER BY p.dateline LIMIT $start_limit, $_G[ppp]";
		} else {
			$_G['forum_numpost'] = $_G['forum_thread']['replies'] + 2 - $_G['forum_numpost'] + ($page > 1 ? 1 : 0);
			$pageadd = "ORDER BY p.first DESC, p.dateline DESC LIMIT $start_limit, $_G[ppp]";
		}
	}
	$multipage = multi($_G['forum_thread']['replies'] + 1, $_G['ppp'], $page, 'forum.php?mod=viewthread&tid='.$_G['tid'].
		($_G['forum_thread']['is_archived'] ? '&archive='.$_G['forum_thread']['archiveid'] : '').
		'&amp;extra='.$_G['gp_extra'].
		($ordertype && $ordertype != getstatus($_G['forum_thread']['status'], 4) ? '&amp;ordertype='.$ordertype : '').
		(isset($_G['gp_highlight']) ? '&amp;highlight='.rawurlencode($_G['gp_highlight']) : '').
		(!empty($_G['gp_authorid']) ? '&amp;authorid='.$_G['gp_authorid'] : '').
		(!empty($_G['gp_from']) ? '&amp;from='.$_G['gp_from'] : '').
		(!empty($_G['gp_checkrush']) ? '&amp;checkrush='.$_G['gp_checkrush'] : '').
		(!empty($_G['gp_modthreadkey']) ? '&amp;modthreadkey='.rawurlencode($_G['gp_modthreadkey']) : '').
		$specialextra);
} else {
	$_G['gp_viewpid'] = intval($_G['gp_viewpid']);
	$pageadd = "AND p.pid='$_G[gp_viewpid]'";
}

$_G['forum_newpostanchor'] = $_G['forum_postcount'] = $_G['forum_ratelogpid'] = $_G['forum_commonpid'] = 0;

$_G['forum_onlineauthors'] = array();

$query = "SELECT p.* $postfieldsadd FROM ".DB::table($posttable)." p $specialadd1 ";

$isdel_post = $cachepids = $positionlist = $postusers = $skipaids = array();
if($savepostposition && empty($onlyauthoradd) && empty($specialadd2) && empty($_G['gp_viewpid']) && $ordertype != 1) {
	$start = ($page - 1) * $_G['ppp'] + 1;
	$end = $start + $_G['ppp'];
	$q2 = DB::query("SELECT pid, position FROM ".DB::table('forum_postposition')." WHERE tid='$_G[tid]' AND position>='$start' AND position<'$end' ORDER BY position");
	$realpost = $lastposition = 0;
	while ($post = DB::fetch($q2)) {
		$cachepids[$post[position]] = $post['pid'];
		$positionlist[$post['pid']] = $post['position'];
		$lastposition = $post['position'];
	}
	$realpost = count($positionlist);
	if($realpost != $_G['ppp']) {
		$k = 0;
		for($i = $start; $i < $end; $i ++) {
			if(!empty($cachepids[$i])) {
				$k = $cachepids[$i];
				$isdel_post[$k] = array('message' => lang('forum/misc', 'post_deleted'), 'number' => $i);
			} elseif($i < $maxposition || ($lastposition && $i < $lastposition)) {
				$isdel_post[$k] = array('message' => lang('forum/misc', 'post_deleted'), 'number' => $i);
			}
			$k ++;
		}
	}
	$cachepids = dimplode($cachepids);
	$pagebydesc = false;
}
if($_G['gp_checkrush'] && $rushreply) {
	$cachepids = dimplode($rushpids);
	$_G['forum_thread']['replies'] = $temp_reply;
}

$query .= $savepostposition && $cachepids ? "WHERE p.pid IN ($cachepids)" : ("WHERE p.tid='$_G[gp_tid]'".($_G['forum_auditstatuson'] || in_array($_G['forum_thread']['displayorder'], array(-2, -3, -4)) && $_G['forum_thread']['authorid'] == $_G['uid'] ? '' : " AND p.invisible='0'")." $specialadd2 $onlyauthoradd $pageadd");
$summary = '';
$query = DB::query($query);
while($post = DB::fetch($query)) {
	if(($onlyauthoradd && $post['anonymous'] == 0) || !$onlyauthoradd) {
		$postusers[$post['authorid']] = array();
		if($post['first']) {
			$_G['forum_firstpid'] = $post['pid'];
			if(IS_ROBOT || $_G['adminid'] == 1) $summary = str_replace(array("\r", "\n"), '', messagecutstr(strip_tags($post['message']), 160));
			$tagarray_all = $posttag_array = array();
			$tagarray_all = explode("\t", $post['tags']);
			if($tagarray_all) {
				foreach($tagarray_all as $var) {
					if($var) {
						$tag = explode(',', $var);
						$posttag_array[] = $tag;
						$tagnames[] = $tag[1];
					}
				}
			}
			$post['tags'] = $posttag_array;
			if($post['tags']) {
				$post['relateitem'] = getrelateitem($post['tags'], $post['tid']);
			}
		}
		$postlist[$post['pid']] = $post;
	}
}

$seodata = array('forum' => $_G['forum']['name'], 'fup' => $_G['cache']['forums'][$fup]['name'], 'subject' => $_G['forum_thread']['subject'], 'summary' => $summary, 'tags' => @implode(',', $tagnames), 'page' => intval($_G['gp_page']));
if($_G['forum']['status'] != 3) {
	$seotype = 'viewthread';
} else {
	$seotype = 'viewthread_group';
	$seodata['first'] = $nav['first']['name'];
	$seodata['second'] = $nav['second']['name'];
}

list($navtitle, $metadescription, $metakeywords) = get_seosetting($seotype, $seodata);
if(!$navtitle) {
	$navtitle = get_title_page($_G['forum_thread']['subject'], $_G['page']).' - '.strip_tags($_G['forum']['name']);
	$nobbname = false;
} else {
	$nobbname = true;
}
if(!$metakeywords) {
	$metakeywords = strip_tags($thread['subject']);
}
if(!$metadescription) {
	$metadescription = $summary.' '.strip_tags($_G['forum_thread']['subject']);
}

$postno = & $_G['cache']['custominfo']['postno'];
if($postusers) {
	$verifyadd = '';
	$fieldsadd = $_G['cache']['custominfo']['fieldsadd'];
	if($_G['setting']['verify']['enabled']) {
		$verifyadd = "LEFT JOIN ".DB::table('common_member_verify')." mv USING(uid)";
		$fieldsadd .= ', mv.verify1, mv.verify2, mv.verify3, mv.verify4, mv.verify5, mv.verify6, mv.verify7';
	}
	$query = DB::query("SELECT m.uid, m.username, m.groupid, m.adminid, m.regdate, m.credits, m.email, m.status AS memberstatus,
			ms.lastactivity, ms.lastactivity, ms.invisible AS authorinvisible,
			mc.*, mp.gender, mp.site, mp.icq, mp.qq, mp.yahoo, mp.msn, mp.taobao, mp.alipay,
			mf.medals, mf.sightml AS signature, mf.customstatus, mh.privacy $fieldsadd
			FROM ".DB::table('common_member')." m
			LEFT JOIN ".DB::table('common_member_field_forum')." mf USING(uid)
			LEFT JOIN ".DB::table('common_member_status')." ms USING(uid)
			LEFT JOIN ".DB::table('common_member_count')." mc USING(uid)
			LEFT JOIN ".DB::table('common_member_profile')." mp USING(uid)
			LEFT JOIN ".DB::table('common_member_field_home')." mh USING(uid)
			$verifyadd
			WHERE m.uid IN (".dimplode(array_keys($postusers)).")");
	while($postuser = DB::fetch($query)) {
		$postuser['privacy'] = unserialize($postuser['privacy']);
		unset($postuser['privacy']['feed'], $postuser['privacy']['view']);
		$postusers[$postuser['uid']] = $postuser;
	}
	$_G['medal_list'] = array();
	foreach($postlist as $pid => $post) {
		$post = array_merge($postlist[$pid], $postusers[$post['authorid']]);
		$postlist[$pid] = viewthread_procpost($post, $_G['member']['lastvisit'], $ordertype);
	}

}

if($savepostposition && $positionlist) {
	foreach ($positionlist as $pid => $position) {
		if($postlist[$pid]){
			$postlist[$pid]['number'] = $position;
			if($rushreply) {
				$postlist[$pid] = checkrushreply($postlist[$pid]);
			}
		}
	}
}
if($_G['gp_checkrush'] && $rushreply) {
	foreach ($rushpositionlist as $pid => $position)
	if($postlist[$pid]){
		$postlist[$pid]['number'] = $position;
		$postlist[$pid]['rewardfloor'] = 1;
	}
}

if($_G['forum_thread']['special'] > 0 && (empty($_G['gp_viewpid']) || $_G['gp_viewpid'] == $_G['forum_firstpid'])) {
	$_G['forum_thread']['starttime'] = gmdate($_G['forum_thread']['dateline']);
	$_G['forum_thread']['remaintime'] = '';
	switch($_G['forum_thread']['special']) {
		case 1: require_once libfile('thread/poll', 'include'); break;
		case 2: require_once libfile('thread/trade', 'include'); break;
		case 3: require_once libfile('thread/reward', 'include'); break;
		case 4: require_once libfile('thread/activity', 'include'); break;
		case 5: require_once libfile('thread/debate', 'include'); break;
		case 127:
			if($_G['forum_firstpid']) {
				$sppos = strpos($postlist[$_G['forum_firstpid']]['message'], chr(0).chr(0).chr(0));
				$specialextra = substr($postlist[$_G['forum_firstpid']]['message'], $sppos + 3);
				$postlist[$_G['forum_firstpid']]['message'] = substr($postlist[$_G['forum_firstpid']]['message'], 0, $sppos);
				if($specialextra) {
					if(array_key_exists($specialextra, $_G['setting']['threadplugins'])) {
						@include_once DISCUZ_ROOT.'./source/plugin/'.$_G['setting']['threadplugins'][$specialextra]['module'].'.class.php';
						$classname = 'threadplugin_'.$specialextra;
						if(class_exists($classname) && method_exists($threadpluginclass = new $classname, 'viewthread')) {
							$threadplughtml = $threadpluginclass->viewthread($_G['tid']);
						}
					}
				}
			}
			break;
	}
}
if($rushreply && !empty($isdel_post)) {
	foreach($postlist as $id => $post) {
		$isdel_post[$id] = $post;
	}
	ksort($isdel_post);
	$postlist = $isdel_post;
}
if(empty($_G['gp_authorid']) && empty($postlist)) {
	if($rushreply) {
		dheader("Location: forum.php?mod=redirect&tid=$_G[tid]&goto=lastpost");
	} else {
		$replies = DB::result_first("SELECT COUNT(*) FROM ".DB::table($posttable)." WHERE tid='$_G[tid]' AND invisible='0'");
		$replies = intval($replies) - 1;
		if($_G['forum_thread']['replies'] != $replies && $replies > 0) {
			DB::query("UPDATE ".DB::table($threadtable)." SET replies='$replies' WHERE tid='$_G[tid]'");
			dheader("Location: forum.php?mod=redirect&tid=$_G[tid]&goto=lastpost");
		}
	}
}

if($_G['forum_pagebydesc'] && (!$savepostposition || $_G['gp_ordertype'] == 1)) {
	$postlist = array_reverse($postlist, TRUE);
}

if($_G['setting']['vtonlinestatus'] == 2 && $_G['forum_onlineauthors']) {
	$query = DB::query("SELECT uid FROM ".DB::table('common_session')." WHERE uid IN(".dimplode($_G['forum_onlineauthors']).") AND invisible=0");
	$_G['forum_onlineauthors'] = array();
	while($author = DB::fetch($query)) {
		$_G['forum_onlineauthors'][$author['uid']] = 1;
	}
} else {
	$_G['forum_onlineauthors'] = array();
}
$ratelogs = array();
if($_G['forum_ratelogpid']) {
	$query = DB::query("SELECT * FROM ".DB::table('forum_ratelog')." WHERE pid IN (".$_G['forum_ratelogpid'].") ORDER BY dateline DESC");
	while($ratelog = DB::fetch($query)) {
		if(count($postlist[$ratelog['pid']]['ratelog']) < $_G['setting']['ratelogrecord']) {
			$ratelogs[$ratelog['pid']][$ratelog['uid']]['username'] = $ratelog['username'];
			$ratelogs[$ratelog['pid']][$ratelog['uid']]['score'][$ratelog['extcredits']] += $ratelog['score'];
			empty($ratelogs[$ratelog['pid']][$ratelog['uid']]['reason']) && $ratelogs[$ratelog['pid']][$ratelog['uid']]['reason'] = dhtmlspecialchars($ratelog['reason']);
			$postlist[$ratelog['pid']]['ratelog'][$ratelog['uid']] = $ratelogs[$ratelog['pid']][$ratelog['uid']];
		}
		$postlist[$ratelog['pid']]['ratelogextcredits'][$ratelog['extcredits']] += $ratelog['score'];

		if(!$postlist[$ratelog['pid']]['totalrate'] || !in_array($ratelog['uid'], $postlist[$ratelog['pid']]['totalrate'])) {
			$postlist[$ratelog['pid']]['totalrate'][] = $ratelog['uid'];
		}
	}
	foreach($postlist as $key => $val) {
		if(!empty($val['ratelogextcredits'])) {
			ksort($postlist[$key]['ratelogextcredits']);
		}
	}
}

$comments = $commentcount = $totalcomment = array();
if($_G['forum_commonpid'] && $_G['setting']['commentnumber']) {
	$query = DB::query("SELECT * FROM ".DB::table('forum_postcomment')." WHERE pid IN (".$_G['forum_commonpid'].') ORDER BY dateline DESC');
	while($comment = DB::fetch($query)) {
		if($comment['authorid'] > '-1') {
			$commentcount[$comment['pid']]++;
		}
		if(count($comments[$comment['pid']]) < $_G['setting']['commentnumber'] && $comment['authorid'] > '-1') {
			$comment['avatar'] = avatar($comment['authorid'], 'small');
			$comment['dateline'] = dgmdate($comment['dateline'], 'u');
			$comment['comment'] = str_replace(array('[b]', '[/b]', '[/color]'), array('<b>', '</b>', '</font>'), preg_replace("/\[color=([#\w]+?)\]/i", "<font color=\"\\1\">", $comment['comment']));
			$comments[$comment['pid']][] = $comment;
		}
		if($comment['authorid'] == '-1') {
			$cic = 0;
			$totalcomment[$comment['pid']] = preg_replace('/<i>([\.\d]+)<\/i>/e', "'<i class=\"cmstarv\" style=\"background-position:20px -'.(intval(\\1) * 16).'px\">'.sprintf('%1.1f', \\1).'</i>'.(\$cic++ % 2 ? '<br />' : '');", $comment['comment']);
		}
	}
}

if($_G['forum_attachpids'] != '-1' && !defined('IN_ARCHIVER')) {
	require_once libfile('function/attachment');
	if(is_array($threadsortshow) && !empty($threadsortshow['sortaids'])) {
		$skipaids = $threadsortshow['sortaids'];
	}
	parseattach($_G['forum_attachpids'], $_G['forum_attachtags'], $postlist, $skipaids);
}

if(empty($postlist)) {
	showmessage2('Error:post_not_found', '', 43010);
} else {
	foreach($postlist as $pid => $post) {
		$postlist[$pid]['message'] = preg_replace("/\[attach\]\d+\[\/attach\]/i", '', $postlist[$pid]['message']);
	}
}

if(defined('IN_ARCHIVER')) {  //手机简化版
	include loadarchiver('forum/viewthread');
	exit();
}

foreach ($postlist as $k=>$v) {
    // 得到用户头像
    $size = 'small';
    $type = '';
    $avatar = $_G['setting']['ucenterurl'] . '/data/avatar/' . get_avatar($v['authorid'], $size, $type);
    if (file_get_contents($avatar)) {
        $avatar_url = $avatar;
    } else {
        $avatar_url = $_G['setting']['ucenterurl'] . '/images/noavatar_'.$size.'.gif';
    }
    
    // 分类信息
    $preMsg = '';
    if ($v['position'] == 1) {
        if (!empty($threadsortshow)) {
            // 标题
            foreach ($_G['forum']['threadsorts']['types'] as $k2=>$v2) {
                if ($_G['thread']['sortid'] == $k2) {
                    $preTit = '[' . $v2 . ']';
                }
            }
            // 表格
            $preMsg .= '<table cellpadding="0" cellspacing="0" class="am-table"><tbody>';
            foreach ($threadsortshow['optionlist'] as $sortInfo) {
                $preMsg .= '<tr><th>'.$sortInfo['title'].'</th><td>';
                $preMsg .= $sortInfo['value'] ? $sortInfo['value'] : '-';
                $preMsg .= '</td></tr>';
            }
            $preMsg .= '</tbody></table>';
            $preMsg = preg_replace("/<a href=\"(.*?)\" target=\"_blank\">点击查看<\/a>/i", '<img src="\1">', $preMsg);
        }
    }
    $message = $preMsg . $v['message'];
    
    // 更改标签为绝对地址
    $message = preg_replace("/\"static\/image/i", '"'.$_G['setting']['discuzurl'].'/static/image', $message);
    $message = preg_replace("/\"(\.\.\/)?data\/attachment/i", '"'.$_G['setting']['discuzurl'].'/data/attachment', $message);
    $message = preg_replace("/\n/i", '', $message);
    
    // 处理图片src
    preg_match_all("/\<img(.*?)\/>/i", $message, $imgs);

    if (!empty($imgs)) {
        foreach ($imgs[0] as $img) {
            preg_match("/id=\"(.*?)\"(.*?)zoomfile=\"(.*?)\"/i", $img, $src);
            if (empty($src)) continue;
            $replacement = '<img src="'.$src[3].'"/>';
            $message = preg_replace("/<ignore_js_op>\s*<img\sid=\"{$src[1]}\"(.*?)<\/ignore_js_op>/i", $replacement, $message);
            unset($src, $replacement);
        }
    }
    
    // 返回数据
    $return[] = array(
            'pid' => $v['pid'],
            'fid' => $v['fid'],
            'tid' => $v['tid'],
            'author' => $v['author'],
            'authorid' => $v['authorid'],
            'authorimg' => $avatar_url,
            'subject' => $preTit.$v['subject'],
            'dateline' => $v['dateline'],
            'message' => $message,
            'position' => $v['number'],
        );
        
     unset($avatar_url, $message, $preMsg, $preTit);
}

// showmessage2("ok", $return, 20000);
$subject = $return[0]['subject'];
if ($_GET['plus'] == 1) {
    foreach ($return as $vs) {
        $ajax .= '<div style="float:left;width:100%;">';
        $ajax .= '<div style="float:left;"><img width="30px" src="' .$vs["authorimg"]. '"></div>';
        
        $ajax .= '<div style="float:left;margin:3px 8px;"><font width="20px" >';
        $ajax .= $vs['author'];
        $ajax .= '</font></div>';
        
        $ajax .= '<div style="float:left;margin:3px 10px;"><font width="20px" >';
        $ajax .= $vs['dateline'];
        $ajax .= '</font></div>';
        
        $ajax .= '<div style="float:right;margin:3px 14px;"><font width="20px" >';
        $ajax .= $vs['position'] . "楼";
        $ajax .= '</font></div>';
        
        $ajax .= '</div>';
        $ajax .= '<div style="float:left;padding:6px;width:100%;">' .$vs['message']. '</div>';
        
        $ajax .= '<hr/>';
    }
    echo json_encode(charsetToUTF8($ajax));
} else {
    require_once "./tpl/viewthread.html";
}
//

    function get_avatar($uid, $size = 'middle', $type = '') {
        $size = in_array($size, array('big', 'middle', 'small')) ? $size : 'middle';
        $uid = abs(intval($uid));
        $uid = sprintf("%09d", $uid);
        $dir1 = substr($uid, 0, 3);
        $dir2 = substr($uid, 3, 2);
        $dir3 = substr($uid, 5, 2);
        $typeadd = $type == 'real' ? '_real' : '';
        return $dir1.'/'.$dir2.'/'.$dir3.'/'.substr($uid, -2).$typeadd."_avatar_$size.jpg";
    }



function viewthread_updateviews($threadtable) {
	global $_G;
	if($_G['setting']['delayviewcount'] == 1 || $_G['setting']['delayviewcount'] == 3) {
		$_G['forum_logfile'] = './data/cache/forum_threadviews_'.intval(getgpc('config/server/id')).'.log';
		if(substr(TIMESTAMP, -2) == '00') {
			require_once libfile('function/misc');
			updateviews($threadtable, 'tid', 'views', $_G['forum_logfile']);
		}
		if(@$fp = fopen(DISCUZ_ROOT.$_G['forum_logfile'], 'a')) {
			fwrite($fp, "$_G[tid]\n");
			fclose($fp);
		} elseif($adminid == 1) {
			showmessage2('Error:view_log_invalid', '', 43011);
		}
	} else {

		DB::query("UPDATE LOW_PRIORITY ".DB::table($threadtable)." SET views=views+1 WHERE tid='$_G[tid]'", 'UNBUFFERED');

	}
}

function viewthread_procpost($post, $lastvisit, $ordertype, $special = 0) {
	global $_G, $rushreply;

	if(!$_G['forum_newpostanchor'] && $post['dateline'] > $lastvisit) {
		$post['newpostanchor'] = '<a name="newpost"></a>';
		$_G['forum_newpostanchor'] = 1;
	} else {
		$post['newpostanchor'] = '';
	}

	$post['lastpostanchor'] = ($ordertype != 1 && $_G['forum_numpost'] == $_G['forum_thread']['replies']) || ($ordertype == 1 && $_G['forum_numpost'] == $_G['forum_thread']['replies'] + 2) ? '<a name="lastpost"></a>' : '';

	if($_G['forum_pagebydesc']) {
		if($ordertype != 1) {
			$post['number'] = $_G['forum_numpost'] + $_G['forum_ppp2']--;
		} else {
			$post['number'] = $post['first'] == 1 ? 1 : $_G['forum_numpost'] - $_G['forum_ppp2']--;
		}
	} else {
		if($ordertype != 1) {
			$post['number'] = ++$_G['forum_numpost'];
		} else {
			$post['number'] = $post['first'] == 1 ? 1 : --$_G['forum_numpost'];
		}
	}

	$_G['forum_postcount']++;

	$post['dbdateline'] = $post['dateline'];
	if($_G['setting']['dateconvert']) {
		$post['dateline'] = dgmdate($post['dateline'], 'u');
	} else {
		$dformat = getglobal('setting/dateformat');
		$tformat = getglobal('setting/timeformat');
		$post['dateline'] = dgmdate($post['dateline'], $dformat.' '.str_replace(":i", ":i:s", $tformat));
	}
	$post['groupid'] = $_G['cache']['usergroups'][$post['groupid']] ? $post['groupid'] : 7;

	if($post['username']) {

		$_G['forum_onlineauthors'][] = $post['authorid'];
		$post['usernameenc'] = rawurlencode($post['username']);
		$post['readaccess'] = $_G['cache']['usergroups'][$post['groupid']]['readaccess'];
		if($_G['cache']['usergroups'][$post['groupid']]['userstatusby'] == 1) {
			$post['authortitle'] = $_G['cache']['usergroups'][$post['groupid']]['grouptitle'];
			$post['stars'] = $_G['cache']['usergroups'][$post['groupid']]['stars'];
		}
		$post['upgradecredit'] = false;
		if($_G['cache']['usergroups'][$post['groupid']]['type'] == 'member' && $_G['cache']['usergroups'][$post['groupid']]['creditslower'] != 999999999) {
			$post['upgradecredit'] = $_G['cache']['usergroups'][$post['groupid']]['creditslower'] - $post['credits'];
		}

		$post['taobaoas'] = addslashes($post['taobao']);
		$post['regdate'] = dgmdate($post['regdate'], 'd');
		$post['lastdate'] = dgmdate($post['lastactivity'], 'd');

		$post['authoras'] = !$post['anonymous'] ? ' '.addslashes($post['author']) : '';

		if($post['medals']) {
			loadcache('medals');
			foreach($post['medals'] = explode("\t", $post['medals']) as $key => $medalid) {
				list($medalid, $medalexpiration) = explode("|", $medalid);
				if(isset($_G['cache']['medals'][$medalid]) && (!$medalexpiration || $medalexpiration > TIMESTAMP)) {
					$post['medals'][$key] = $_G['cache']['medals'][$medalid];
					$post['medals'][$key]['medalid'] = $medalid;
					$_G['medal_list'][$medalid] = $_G['cache']['medals'][$medalid];
				} else {
					unset($post['medals'][$key]);
				}
			}
		}

		$post['avatar'] = avatar($post['authorid']);
		$post['groupicon'] = $post['avatar'] ? g_icon($post['groupid'], 1) : '';
		$post['banned'] = $post['status'] & 1;
		$post['warned'] = ($post['status'] & 2) >> 1;

	} else {
		if(!$post['authorid']) {
			$post['useip'] = substr($post['useip'], 0, strrpos($post['useip'], '.')).'.x';
		}
	}
	$post['attachments'] = array();
	$post['imagelist'] = $post['attachlist'] = '';

	if($post['attachment']) {
		
		if($_G['group']['allowgetattach'] || $_G['group']['allowgetimage']) {
			$_G['forum_attachpids'] .= ",$post[pid]";
			$post['attachment'] = 0;
			if(preg_match_all("/\[attach\](\d+)\[\/attach\]/i", $post['message'], $matchaids)) {
				$_G['forum_attachtags'][$post['pid']] = $matchaids[1];
			}
		} else {
			$post['message'] = preg_replace("/\[attach\](\d+)\[\/attach\]/i", '', $post['message']);
		}
	}

	$_G['forum_ratelogpid'] .= ($_G['setting']['ratelogrecord'] && $post['ratetimes']) ? ','.$post['pid'] : '';
	if($_G['setting']['commentnumber'] && ($post['first'] && $_G['setting']['commentfirstpost'] || !$post['first'])) {
		$_G['forum_commonpid'] .= $post['comment'] ? ','.$post['pid'] : '';
	}
	$post['allowcomment'] = $_G['setting']['commentnumber'] && in_array(1, $_G['setting']['allowpostcomment']) && ($_G['setting']['commentpostself'] || $post['authorid'] != $_G['uid']) &&
		($post['first'] && $_G['setting']['commentfirstpost'] && in_array($_G['group']['allowcommentpost'], array(1, 3)) ||
		(!$post['first'] && in_array($_G['group']['allowcommentpost'], array(2, 3))));
	$_G['forum']['allowbbcode'] = $_G['forum']['allowbbcode'] ? -$post['groupid'] : 0;
	$post['signature'] = $post['usesig'] ? ($_G['setting']['sigviewcond'] ? (strlen($post['message']) > $_G['setting']['sigviewcond'] ? $post['signature'] : '') : $post['signature']) : '';
	if(!defined('IN_ARCHIVER')) {
		$post['message'] = discuzcode($post['message'], $post['smileyoff'], $post['bbcodeoff'], $post['htmlon'] & 1, $_G['forum']['allowsmilies'], $_G['forum']['allowbbcode'], ($_G['forum']['allowimgcode'] && $_G['setting']['showimages'] ? 1 : 0), $_G['forum']['allowhtml'], ($_G['forum']['jammer'] && $post['authorid'] != $_G['uid'] ? 1 : 0), 0, $post['authorid'], $_G['cache']['usergroups'][$post['groupid']]['allowmediacode'] && $_G['forum']['allowmediacode'], $post['pid'], $_G['setting']['lazyload']);
		if($post['first']) {
			if(!$_G['forum_thread']['isgroup']) {
				$_G['relatedlinks'] = getrelatedlink('forum');
			} else {
				$_G['relatedlinks'] = getrelatedlink('group');
			}
		}
	}
	$_G['forum_firstpid'] = intval($_G['forum_firstpid']);
	$post['custominfo'] = viewthread_custominfo($post);
	return $post;
}

function viewthread_loadcache() {
	global $_G;
	$_G['forum']['livedays'] = ceil((TIMESTAMP - $_G['forum']['dateline']) / 86400);
	$_G['forum']['lastpostdays'] = ceil((TIMESTAMP - $_G['forum']['lastthreadpost']) / 86400);
	$threadcachemark = 100 - (
	$_G['forum']['displayorder'] * 15 +
	$_G['thread']['digest'] * 10 +
	min($_G['thread']['views'] / max($_G['forum']['livedays'], 10) * 2, 50) +
	max(-10, (15 - $_G['forum']['lastpostdays'])) +
	min($_G['thread']['replies'] / $_G['setting']['postperpage'] * 1.5, 15));
	if($threadcachemark < $_G['forum']['threadcaches']) {

		$threadcache = getcacheinfo($_G['tid']);

		if(TIMESTAMP - $threadcache['filemtime'] > $_G['setting']['cachethreadlife']) {
			@unlink($threadcache['filename']);
			define('CACHE_FILE', $threadcache['filename']);
		} else {
			readfile($threadcache['filename']);

			viewthread_updateviews($_G['forum_thread']['threadtable']);
			$_G['setting']['debug'] && debuginfo();
			$_G['setting']['debug'] ? die('<script type="text/javascript">document.getElementById("debuginfo").innerHTML = " '.($_G['setting']['debug'] ? 'Updated at '.gmdate("H:i:s", $threadcache['filemtime'] + 3600 * 8).', Processed in '.$debuginfo['time'].' second(s), '.$debuginfo['queries'].' Queries'.($_G['gzipcompress'] ? ', Gzip enabled' : '') : '').'";</script>') : die();
		}
	}
}

function viewthread_lastmod(&$thread) {
	global $_G;
	if(!$thread['moderated']) {
		return array();
	}

	$lastmod = DB::fetch_first("SELECT uid AS moduid, username AS modusername, dateline AS moddateline, action AS modaction, magicid, stamp, reason
		FROM ".DB::table('forum_threadmod')."
		WHERE tid='$thread[tid]' ORDER BY dateline DESC LIMIT 1");
	if($lastmod) {
		$modactioncode = lang('forum/modaction');
		$lastmod['modusername'] = $lastmod['modusername'] ? $lastmod['modusername'] : 'System';
		$lastmod['moddateline'] = dgmdate($lastmod['moddateline'], 'u');
		$lastmod['modactiontype'] = $lastmod['modaction'];
		if($modactioncode[$lastmod['modaction']]) {
			$lastmod['modaction'] = $modactioncode[$lastmod['modaction']].($lastmod['modaction'] != 'SPA' ? '' : ' '.$_G['cache']['stamps'][$lastmod['stamp']]['text']);
		} elseif(substr($lastmod['modaction'], 0, 1) == 'L' && preg_match('/L(\d\d)/', $lastmod['modaction'], $a)) {
			$lastmod['modaction'] = $modactioncode['SLA'].' '.$_G['cache']['stamps'][intval($a[1])]['text'];
		} else {
			$lastmod['modaction'] = '';
		}
		if($lastmod['magicid']) {
			loadcache('magics');
			$lastmod['magicname'] = $_G['cache']['magics'][$lastmod['magicid']]['name'];
		}
	} else {
		DB::query("UPDATE ".DB::table($thread['threadtable'])." SET moderated='0' WHERE tid='$thread[tid]'", 'UNBUFFERED');
		$thread['moderated'] = 0;
	}
	return $lastmod;
}

function viewthread_custominfo($post) {
	global $_G;

	$types = array('left', 'menu');
	foreach($types as $type) {
		if(!is_array($_G['cache']['custominfo']['setting'][$type])) {
			continue;
		}
		$data = '';
		foreach($_G['cache']['custominfo']['setting'][$type] as $key => $order) {
			$v = '';
			if(substr($key, 0, 10) == 'extcredits') {
				$i = substr($key, 10);
				$extcredit = $_G['setting']['extcredits'][$i];
				$v = '<dt>'.($extcredit['img'] ? $extcredit['img'].' ' : '').$extcredit['title'].'</dt><dd>'.$post['extcredits'.$i].' '.$extcredit['unit'].'</dd>';
			} elseif(substr($key, 0, 6) == 'field_') {
				require_once libfile('function/profile');
				$v = profile_show(substr($key, 6), $post);
				if($v) {
					$v = '<dt>'.$_G['cache']['custominfo']['profile'][$key][0].'</dt><dd title="'.htmlspecialchars(strip_tags($v)).'">'.$v.'</dd>';
				}
			} else {
				switch($key) {
					case 'uid': $v = $post['uid'];break;
					case 'posts': $v = '<a href="home.php?mod=space&uid='.$post['uid'].'&do=thread&type=reply&view=me&from=space" target="_blank" class="xi2">'.$post['posts'].'</a>';break;
					case 'threads': $v = '<a href="home.php?mod=space&uid='.$post['uid'].'&do=thread&type=thread&view=me&from=space" target="_blank" class="xi2">'.$post['threads'].'</a>';break;
					case 'doings': $v = '<a href="home.php?mod=space&uid='.$post['uid'].'&do=doing&view=me&from=space" target="_blank" class="xi2">'.$post['doings'].'</a>';break;
					case 'blogs': $v = '<a href="home.php?mod=space&uid='.$post['uid'].'&do=blog&view=me&from=space" target="_blank" class="xi2">'.$post['blogs'].'</a>';break;
					case 'albums': $v = '<a href="home.php?mod=space&uid='.$post['uid'].'&do=album&view=me&from=space" target="_blank" class="xi2">'.$post['albums'].'</a>';break;
					case 'sharings': $v = '<a href="home.php?mod=space&uid='.$post['uid'].'&do=share&view=me&from=space" target="_blank" class="xi2">'.$post['sharings'].'</a>';break;
					case 'friends': $v = '<a href="home.php?mod=space&uid='.$post['uid'].'&do=friend&view=me&from=space" target="_blank" class="xi2">'.$post['friends'].'</a>';break;
					case 'digest': $v = $post['digestposts'];break;
					case 'credits': $v = $post['credits'];break;
					case 'readperm': $v = $post['readaccess'];break;
					case 'regtime': $v = $post['regdate'];break;
					case 'lastdate': $v = $post['lastdate'];break;
					case 'oltime': $v = $post['oltime'].' '.lang('space', 'viewthread_userinfo_hour');break;
				}
				if($v !== '') {
					$v = '<dt>'.lang('space', 'viewthread_userinfo_'.$key).'</dt><dd>'.$v.'</dd>';
				}
			}
			$data .= $v;
		}
		$return[$type] = $data;
	}
	return $return;
}

function remaintime($time) {
	$days = intval($time / 86400);
	$time -= $days * 86400;
	$hours = intval($time / 3600);
	$time -= $hours * 3600;
	$minutes = intval($time / 60);
	$time -= $minutes * 60;
	$seconds = $time;
	return array((int)$days, (int)$hours, (int)$minutes, (int)$seconds);
}

function getrelateitem($tagarray, $tid = 0, $type = 'tid') {
	global $_G;
	$tagidarray = $relatearray = $relateitem = array();
	$limit = $_G['setting']['relatenum'];
	$limitsum = 2 * $limit;
	if(!$limit) {
		return '';
	}
	foreach($tagarray as $var) {
		$tagidarray[] = $var['0'];
	}
	if(!$tagidarray) {
		return '';
	}
	$query = DB::query("SELECT itemid FROM ".DB::table('common_tagitem')." WHERE tagid IN (".dimplode($tagidarray).") AND idtype='$type' LIMIT $limitsum");
	$i = 1;
	while($result = DB::fetch($query)) {
		if($result['itemid'] != $tid) {
			if($i > $limit) {
				break;
			}
			if($relatearray[$result[itemid]] == '') {
				$i++;
			}
			if($result['itemid']) {
				$relatearray[$result[itemid]] = $result['itemid'];
			}

		}
	}
	if(!empty($relatearray)) {
		$query = DB::query("SELECT tid,subject FROM ".DB::table('forum_thread')." WHERE tid IN (".dimplode($relatearray).")");
		while($result = DB::fetch($query)) {
			$relateitem[] = $result;
		}
	}
	return $relateitem;
}

function viewthread_oldtopics($tid = 0) {
	global $_G;

	$oldthreads = array();

	$oldtopics = isset($_G['cookie']['oldtopics']) ? $_G['cookie']['oldtopics'] : 'D';

	if($_G['setting']['visitedthreads']) {
		$oldtids = array_slice(explode('D', $oldtopics), 0, $_G['setting']['visitedthreads']);
		$oldtidsnew = array();
		foreach($oldtids as $oldtid) {
			$oldtid && $oldtidsnew[] = $oldtid;
		}
		if($oldtidsnew) {
			$query = DB::query("SELECT tid, subject FROM ".DB::table('forum_thread')." WHERE tid IN (".dimplode($oldtidsnew).")");
			while($oldthread = DB::fetch($query)) {
				$oldthreads[$oldthread['tid']] = $oldthread['subject'];
			}
		}
		array_unshift($oldtidsnew, $tid);
		dsetcookie('oldtopics', implode('D', array_slice($oldtidsnew, 0, $_G['setting']['visitedthreads'])), 3600);		;
	}

	if($_G['member']['lastvisit'] < $_G['forum_thread']['lastpost'] && (!isset($_G['cookie']['fid'.$_G['fid']]) || $_G['forum_thread']['lastpost'] > $_G['cookie']['fid'.$_G['fid']])) {
		dsetcookie('fid'.$_G['fid'], $_G['forum_thread']['lastpost'], 3600);
	}

	return $oldthreads;
}

function rushreply_rule () {
	global $rushresult;
	if(!empty($rushresult['rewardfloor'])) {
		$rushresult['rewardfloor'] = preg_replace('/\*+/', '*', $rushresult['rewardfloor']);
		$rewardfloorarr = explode(',', $rushresult['rewardfloor']);
		if($rewardfloorarr) {
			foreach($rewardfloorarr as $var) {
				$var = trim($var);
				if(strlen($var) > 1) {
					$var = str_replace('*', '[^,]?[\d]+', $var);
				} else {
					$var = str_replace('*', '[^,]?', $var);
				}
				$preg[] = "(,$var,)";
			}
			$preg_str = "/".implode('|', $preg)."/";
		}
	}
	return $preg_str;
}

function checkrushreply($post) {
	global $_G, $rushids;
	if($_G['gp_authorid'] || $_G['gp_ordertype'] == 1 || $_G['gp_checkrush']) {
		return $post;
	}
	if(in_array($post['number'], $rushids)) {
		$post['rewardfloor'] = 1;
	}
	return $post;
}

function viewthread_is_search_referer() {
    $regex = "((http|https)\:\/\/)?";
    $regex .= "([a-z]*.)?(ask.com|yahoo.com|cn.yahoo.com|bing.com|baidu.com|soso.com|google.com|google.cn)(.[a-z]{2,3})?\/";
    if(preg_match("/^$regex/", $_SERVER['HTTP_REFERER'])) {
        return true;
    }
    return false;
}

?>