<?php
/**
 * 获取版块帖子列表，即投诉爆料内容
 *
 * @parameter $fid 板块ID
 * @parameter $page 第几页
 * @parameter $pagesize 每页几条
 *
 * @Author    王伟龙 QQ:973885303
 * @FileName  t_forum.php
 * @Date      2014-9-9 15:22:05 修改2014-9-22 18:05:00
 */

require("./inc.php");

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('function/forumlist');

if($_G['forum']['redirect']) {
	showmessage2('Error:forum_nonexistence', '', 40001);
	//dheader("Location: {$_G[forum][redirect]}");
} elseif($_G['forum']['type'] == 'group') {
	showmessage2('Error:forum_nonexistence', '', 40001);
	//dheader("Location: forum.php?gid=$_G[fid]");
} elseif(empty($_G['forum']['fid'])) {
	showmessage2('Error:forum_nonexistence', '', 40001);
} elseif($_G['fid'] == $_G['setting']['followforumid'] && $_G['adminid'] != 1) {
	showmessage2('Error:forum_nonexistence', '', 40001);
	//dheader("Location: home.php?mod=follow");
}

$st_t = $_G['uid'].'|'.TIMESTAMP;
//dsetcookie('st_t', $st_t.'|'.md5($st_t.$_G['config']['security']['authkey']));

$_G['action']['fid'] = $_G['fid'];

$_GET['specialtype'] = isset($_GET['specialtype']) ? $_GET['specialtype'] : '';
$_GET['dateline'] = isset($_GET['dateline']) ? intval($_GET['dateline']) : 0;
$_GET['digest'] = isset($_GET['digest']) ? 1 : '';
$_GET['archiveid'] = isset($_GET['archiveid']) ? intval($_GET['archiveid']) : 0;

$showoldetails = isset($_GET['showoldetails']) ? $_GET['showoldetails'] : '';
/* switch($showoldetails) {
	case 'no': dsetcookie('onlineforum', ''); break;
	case 'yes': dsetcookie('onlineforum', 1, 31536000); break;
} */

if(!isset($_G['cookie']['atarget'])) {
	if($_G['setting']['targetblank']) {
		//dsetcookie('atarget', 1, 2592000);
		$_G['cookie']['atarget'] = 1;
	}
}

$_G['forum']['name'] = strip_tags($_G['forum']['name']) ? strip_tags($_G['forum']['name']) : $_G['forum']['name'];
$_G['forum']['extra'] = empty($_G['forum']['extra']) ? array() : dunserialize($_G['forum']['extra']);
if(!is_array($_G['forum']['extra'])) {
	$_G['forum']['extra'] = array();
}


$threadtable_info = !empty($_G['cache']['threadtable_info']) ? $_G['cache']['threadtable_info'] : array();
$forumarchive = array();
if($_G['forum']['archive']) {
	foreach(C::t('forum_forum_threadtable')->fetch_all_by_fid($_G['fid']) as $archive) {
		$forumarchive[$archive['threadtableid']] = array(
			'displayname' => dhtmlspecialchars($threadtable_info[$archive['threadtableid']]['displayname']),
			'threads' => $archive['threads'],
			'posts' => $archive['posts'],
		);
		if(empty($forumarchive[$archive['threadtableid']]['displayname'])) {
			$forumarchive[$archive['threadtableid']]['displayname'] = lang('forum/thread', 'forum_archive').' '.$archive['threadtableid'];
		}
	}
}


$forum_up = $_G['cache']['forums'][$_G['forum']['fup']];
if($_G['forum']['type'] == 'forum') {
	$fgroupid = $_G['forum']['fup'];
	if(empty($_GET['archiveid'])) {
		$navigation = ' <em>&rsaquo;</em> <a href="forum.php?gid='.$forum_up['fid'].'">'.$forum_up['name'].'</a><em>&rsaquo;</em> <a href="forum.php?mod=forumdisplay&fid='.$_G['forum']['fid'].'">'.$_G['forum']['name'].'</a>';
	} else {
		$navigation = ' <em>&rsaquo;</em> '.'<a href="forum.php?mod=forumdisplay&fid='.$_G['fid'].'">'.$_G['forum']['name'].'</a> <em>&rsaquo;</em> '.$forumarchive[$_GET['archiveid']]['displayname'];
	}
	$seodata = array('forum' => $_G['forum']['name'], 'fgroup' => $forum_up['name'], 'page' => intval($_GET['page']));
} else {
	$fgroupid = $forum_up['fup'];
	if(empty($_GET['archiveid'])) {
		$forum_top =  $_G['cache']['forums'][$forum_up[fup]];
		$navigation = ' <em>&rsaquo;</em> <a href="forum.php?gid='.$forum_top['fid'].'">'.$forum_top['name'].'</a><em>&rsaquo;</em> <a href="forum.php?mod=forumdisplay&fid='.$forum_up['fid'].'">'.$forum_up['name'].'</a><em>&rsaquo;</em> '.$_G['forum']['name'];
	} else {
		$navigation = ' <em>&rsaquo;</em> <a href="forum.php?mod=forumdisplay&fid='.$_G['forum']['fup'].'">'.$forum_up['name'].'</a> <em>&rsaquo;</em> '.'<a href="forum.php?mod=forumdisplay&fid='.$_G['fid'].'">'.$_G['forum']['name'].'</a> <em>&rsaquo;</em> '.$forumarchive[$_GET['archiveid']]['displayname'];
	}
	$seodata = array('forum' => $_G['forum']['name'], 'fup' => $forum_up['name'], 'fgroup' => $forum_top['name'], 'page' => intval($_GET['page']));
}

$rssauth = $_G['rssauth'];
$rsshead = $_G['setting']['rssstatus'] ? ('<link rel="alternate" type="application/rss+xml" title="'.$_G['setting']['bbname'].' - '.$navtitle.'" href="'.$_G['siteurl'].'forum.php?mod=rss&fid='.$_G['fid'].'&amp;auth='.$rssauth."\" />\n") : '';

$forumseoset = array(
	'seotitle' => $_G['forum']['seotitle'],
	'seokeywords' => $_G['forum']['keywords'],
	'seodescription' => $_G['forum']['seodescription']
);

$seotype = 'threadlist';
if($_G['forum']['status'] == 3) {
	$navtitle = helper_seo::get_title_page($_G['forum']['name'], $_G['page']).' - '.$_G['setting']['navs'][3]['navname'];
	$metakeywords = $_G['forum']['metakeywords'];
	$metadescription = $_G['forum']['description'];
	if($_G['forum']['level'] == -1) {
		showmessage2('Error:group_verify', '', 40002);
	}
	$_G['seokeywords'] = $_G['setting']['seokeywords']['group'];
	$_G['seodescription'] = $_G['setting']['seodescription']['group'];
	$action = getgpc('action') ? $_GET['action'] : 'list';
	require_once libfile('function/group');
	$status = groupperm($_G['forum'], $_G['uid']);
	if($status == -1) {
		showmessage2('Error:forum_not_group', '', 40003);
	} elseif($status == 1) {
		showmessage2('Error:forum_group_status_off', '', 40004);
	} elseif($status == 2) {
		showmessage2('Error:forum_group_noallowed', '', 40005);
	} elseif($status == 3) {
		showmessage2('Error:forum_group_moderated', '', 40006);
	}
	$_G['forum']['icon'] = get_groupimg($_G['forum']['icon'], 'icon');
	$_G['grouptypeid'] = $_G['forum']['fup'];
	$_G['forum']['dateline'] = dgmdate($_G['forum']['dateline'], 'd');

	$nav = get_groupnav($_G['forum']);
	$groupnav = $nav['nav'];
	$onlinemember = grouponline($_G['fid']);
	$groupmanagers = $_G['forum']['moderators'];
	$groupcache = getgroupcache($_G['fid'], array('replies', 'views', 'digest', 'lastpost', 'ranking', 'activityuser', 'newuserlist'));
	$seotype = 'grouppage';
	$seodata['first'] = $nav['first']['name'];
	$seodata['second'] = $nav['second']['name'];
	$seodata['gdes'] = $_G['forum']['description'];
	$forumseoset = array();
}
$_G['forum']['banner'] = get_forumimg($_G['forum']['banner']);

list($navtitle, $metadescription, $metakeywords) = get_seosetting($seotype, $seodata, $forumseoset);

if(!$navtitle) {
	$navtitle = helper_seo::get_title_page($_G['forum']['name'], $_G['page']);
	$nobbname = false;
} else {
	$nobbname = true;
}
$_GET['typeid'] = intval($_GET['typeid']);
if(!empty($_GET['typeid']) && !empty($_G['forum']['threadtypes']['types'][$_GET['typeid']])) {
	$navtitle = strip_tags($_G['forum']['threadtypes']['types'][$_GET['typeid']]).' - '.$navtitle;
}
if(!$metakeywords) {
	$metakeywords = $_G['forum']['name'];
}
if(!$metadescription) {
	$metadescription = $_G['forum']['name'];
}
if($_G['forum']['viewperm'] && !forumperm($_G['forum']['viewperm']) && !$_G['forum']['allowview']) {
	showmessage2('Error:no_viewperm', '', 40007);
} elseif($_G['forum']['formulaperm']) {
	formulaperm($_G['forum']['formulaperm']);
}

if($_G['forum']['password']) {
	if($_GET['action'] == 'pwverify') {
		if($_GET['pw'] != $_G['forum']['password']) {
			showmessage2('Error:forum_passwd_incorrect', '', 40008);
		} else {
			//dsetcookie('fidpw'.$_G['fid'], $_GET['pw']);
			showmessage2('Error:forum_passwd_correct', '', 40009);
		}
	}/*  elseif($_G['forum']['password'] != $_G['cookie']['fidpw'.$_G['fid']]) {
		//include template('forum/forumdisplay_passwd');
		exit();
	} */
}

if($_G['forum']['price'] && !$_G['forum']['ismoderator']) {
	$membercredits = C::t('common_member_forum_buylog')->get_credits($_G['uid'], $_G['fid']);
	$paycredits = $_G['forum']['price'] - $membercredits;
	if($paycredits > 0) {
		if($_GET['action'] == 'paysubmit') {
			updatemembercount($_G['uid'], array($_G['setting']['creditstransextra'][1] => -$paycredits), 1, 'FCP', $_G['fid']);
			C::t('common_member_forum_buylog')->update_credits($_G['uid'], $_G['fid'], $_G['forum']['price']);
			showmessage2('Error:forum_pay_correct', '', 400010);
		} else {
			if(getuserprofile('extcredits'.$_G['setting']['creditstransextra'][1]) < $paycredits) {
				showmessage2('Error:forum_pay_incorrect', '', 40011);
			} else {
				// include template('forum/forumdisplay_pay');
				exit();
			}
		}
	}
}

if(!isset($_G['cookie']['collapse']) || strpos($_G['cookie']['collapse'], 'forum_rules_'.$_G['fid']) === FALSE) {
	$collapse['forum_rules'] = '';
	$collapse['forum_rulesimg'] = 'no';
} else {
	$collapse['forum_rules'] = 'display: none';
	$collapse['forum_rulesimg'] = 'yes';
}

$forumlastvisit = 0;
if(empty($_G['forum']['picstyle']) && isset($_G['cookie']['forum_lastvisit']) && strexists($_G['cookie']['forum_lastvisit'], 'D_'.$_G['fid'])) {
	preg_match('/D\_'.$_G['fid'].'\_(\d+)/', $_G['cookie']['forum_lastvisit'], $a);
	$forumlastvisit = $a[1];
	unset($a);
}
//dsetcookie('forum_lastvisit', preg_replace("/D\_".$_G['fid']."\_\d+/", '', $_G['cookie']['forum_lastvisit']).'D_'.$_G['fid'].'_'.TIMESTAMP, 604800);

$threadtableids = !empty($_G['cache']['threadtableids']) ? $_G['cache']['threadtableids'] : array();

$tableid = $_GET['archiveid'] && in_array($_GET['archiveid'], $threadtableids) ? intval($_GET['archiveid']) : 0;

if($_G['setting']['allowmoderatingthread'] && $_G['uid']) {
	$threadmodcount = C::t('forum_thread')->count_by_fid_displayorder_authorid($_G['fid'], -2, $_G['uid'], $tableid);
}

$optionadd = $filterurladd = $searchsorton = '';

$quicksearchlist = array();
if(!empty($_G['forum']['threadsorts']['types'])) {
	require_once libfile('function/threadsort');

	$showpic = intval($_GET['showpic']);
	$templatearray = $sortoptionarray = array();
	foreach($_G['forum']['threadsorts']['types'] as $stid => $sortname) {
		loadcache(array('threadsort_option_'.$stid, 'threadsort_template_'.$stid));
		sortthreadsortselectoption($stid);
		$templatearray[$stid] = $_G['cache']['threadsort_template_'.$stid]['subject'];
		$sortoptionarray[$stid] = $_G['cache']['threadsort_option_'.$stid];
	}

	if(!empty($_G['forum']['threadsorts']['defaultshow']) && empty($_GET['sortid']) && empty($_GET['sortall'])) {
		$_GET['sortid'] = $_G['forum']['threadsorts']['defaultshow'];
		$_GET['filter'] = 'sortid';
		$_SERVER['QUERY_STRING'] = $_SERVER['QUERY_STRING'] ? $_SERVER['QUERY_STRING'].'&sortid='.$_GET['sortid'] : 'sortid='.$_GET['sortid'];
		$filterurladd = '&amp;filter=sort';
	}

	$_GET['sortid'] = $_GET['sortid'] ? $_GET['sortid'] : $_GET['searchsortid'];
	if(isset($_GET['sortid']) && $_G['forum']['threadsorts']['types'][$_GET['sortid']]) {
		$searchsortoption = $sortoptionarray[$_GET['sortid']];
		$quicksearchlist = quicksearch($searchsortoption);
		$_G['forum_optionlist'] = $_G['cache']['threadsort_option_'.$_GET['sortid']];
		$forum_optionlist = getsortedoptionlist();
	}
}
$_GET['sortid'] = intval($_GET['sortid']);
$moderatedby = $_G['forum']['status'] != 3 ? moddisplay($_G['forum']['moderators'], 'forumdisplay') : '';
$_GET['highlight'] = empty($_GET['highlight']) ? '' : dhtmlspecialchars($_GET['highlight']);
if($_G['forum']['autoclose']) {
	$closedby = $_G['forum']['autoclose'] > 0 ? 'dateline' : 'lastpost';
	$_G['forum']['autoclose'] = abs($_G['forum']['autoclose']) * 86400;
}

$subexists = 0;
foreach($_G['cache']['forums'] as $sub) {
	if($sub['type'] == 'sub' && $sub['fup'] == $_G['fid'] && (!$_G['setting']['hideprivate'] || !$sub['viewperm'] || forumperm($sub['viewperm']) || strstr($sub['users'], "\t$_G[uid]\t"))) {
		if(!$sub['status']) {
			continue;
		}
		$subexists = 1;
		$sublist = array();
		$query = C::t('forum_forum')->fetch_all_info_by_fids(0, 'available', 0, $_G['fid'], 1, 0, 0, 'sub');

		if(!empty($_G['member']['accessmasks'])) {
			$fids = array_keys($query);
			$accesslist = C::t('forum_access')->fetch_all_by_fid_uid($fids, $_G['uid']);
			foreach($query as $key => $val) {
				$query[$key]['allowview'] = $accesslist[$key];
			}
		}
		foreach($query as $sub) {
			$sub['extra'] = dunserialize($sub['extra']);
			if(!is_array($sub['extra'])) {
				$sub['extra'] = array();
			}
			if(forum($sub)) {
				$sub['orderid'] = count($sublist);
				$sublist[] = $sub;
			}
		}
		break;
	}
}

if(!empty($_GET['archiveid']) && in_array($_GET['archiveid'], $threadtableids)) {
	$subexists = 0;
}

if($subexists) {
	if($_G['forum']['forumcolumns']) {
		$_G['forum']['forumcolwidth'] = (floor(100 / $_G['forum']['forumcolumns']) - 0.1).'%';
		$_G['forum']['subscount'] = count($sublist);
		$_G['forum']['endrows'] = '';
		if($colspan = $_G['forum']['subscount'] % $_G['forum']['forumcolumns']) {
			while(($_G['forum']['forumcolumns'] - $colspan) > 0) {
				$_G['forum']['endrows'] .= '<td>&nbsp;</td>';
				$colspan ++;
			}
			$_G['forum']['endrows'] .= '</tr>';
		}
	}
	if(empty($_G['cookie']['collapse']) || strpos($_G['cookie']['collapse'], 'subforum_'.$_G['fid']) === FALSE) {
		$collapse['subforum'] = '';
		$collapseimg['subforum'] = 'collapsed_no.gif';
	} else {
		$collapse['subforum'] = 'display: none';
		$collapseimg['subforum'] = 'collapsed_yes.gif';
	}
}

// 第几页、每页多少条参数
$page = intval($_POST['page']);
$_G['tpp'] = intval($_POST['pagesize']);
if (empty($_G['tpp']))
    $_G['tpp'] = 15;

$subforumonly = $_G['forum']['simple'] & 1;
$simplestyle = !$_G['forum']['allowside'] || $page > 1 ? true : false;

/* if($subforumonly) {
	$_G['setting']['fastpost'] = false;
	$_GET['orderby'] = '';
	if(!defined('IN_ARCHIVER')) {
		include template('diy:forum/forumdisplay:'.$_G['fid']);
	} else {
		include loadarchiver('forum/forumdisplay');
	}
	exit();
} */
if($_GET['filter'] != 'hot') {
	$page = $_G['setting']['threadmaxpages'] && $page > $_G['setting']['threadmaxpages'] ? 1 : $page;
}

if($_G['forum']['modrecommend'] && $_G['forum']['modrecommend']['open']) {
	$_G['forum']['recommendlist'] = recommendupdate($_G['fid'], $_G['forum']['modrecommend'], '', 1);
}
$recommendgroups = array();
if($_G['forum']['status'] != 3 && helper_access::check_module('group')) {
	loadcache('forumrecommend');
	$recommendgroups = $_G['cache']['forumrecommend'][$_G['fid']];
}

if($recommendgroups) {
	if(empty($_G['cookie']['collapse']) || strpos($_G['cookie']['collapse'], 'recommendgroups_'.$_G['fid']) === FALSE) {
		$collapse['recommendgroups'] = '';
		$collapseimg['recommendgroups'] = 'collapsed_no.gif';
	} else {
		$collapse['recommendgroups'] = 'display: none';
		$collapseimg['recommendgroups'] = 'collapsed_yes.gif';
	}
}
if(!$simplestyle || !$_G['forum']['allowside'] && $page == 1) {
	if($_G['cache']['announcements_forum'] && (!$_G['cache']['announcements_forum']['endtime'] || $_G['cache']['announcements_forum']['endtime'] > TIMESTAMP)) {
		$announcement = $_G['cache']['announcements_forum'];
		$announcement['starttime'] = dgmdate($announcement['starttime'], 'd');
	} else {
		$announcement = NULL;
	}
}

$filteradd = $sortoptionurl = $sp = '';
$sorturladdarray = $selectadd = array();
$forumdisplayadd = array('orderby' => '');
$specialtype = array('poll' => 1, 'trade' => 2, 'reward' => 3, 'activity' => 4, 'debate' => 5);
$filterfield = array('digest', 'recommend', 'sortall', 'typeid', 'sortid', 'dateline', 'page', 'orderby', 'specialtype', 'author', 'view', 'reply', 'lastpost', 'hot');

foreach($filterfield as $v) {
	$forumdisplayadd[$v] = '';
}

$filter = isset($_GET['filter']) && in_array($_GET['filter'], $filterfield) ? $_GET['filter'] : '';
$filterbool = !empty($filter);
$filterarr = $multiadd = array();
$threadclasscount = array();

if($filter && $filter != 'hot') {
	if($query_string = $_SERVER['QUERY_STRING']) {
		$query_string = substr($query_string, (strpos($query_string, "&") + 1));
		parse_str($query_string, $geturl);
		$geturl = daddslashes($geturl, 1);
		if($geturl && is_array($geturl)) {
			$issort = isset($_GET['sortid']) && isset($_G['forum']['threadsorts']['types'][$_GET['sortid']]) && $quicksearchlist ? TRUE : FALSE;
			$selectadd = $issort ? $geturl : array();
			foreach($filterfield as $option) {
				foreach($geturl as $field => $value) {
					if(in_array($field, $filterfield) && $option != $field && $field != 'page' && ($field != 'orderby' || !in_array($option, array('author', 'reply', 'view', 'lastpost', 'heat')))) {
						if(!(in_array($option, array('digest', 'recommend')) && in_array($field, array('digest', 'recommend')))) {
							$forumdisplayadd[$option] .= '&'.rawurlencode($field).'='.rawurlencode($value);
						}
					}
				}
				if($issort) {
					$sfilterfield = array_merge(array('filter', 'sortid', 'orderby', 'fid'), $filterfield);
					foreach($geturl as $soption => $value) {
						$forumdisplayadd[$soption] .= !in_array($soption, $sfilterfield) ? '&'.rawurlencode($soption).'='.rawurlencode($value) : '';
					}
					unset($sfilterfield);
				}
			}
			if($issort && is_array($quicksearchlist)) {
				foreach($quicksearchlist as $option) {
					$identifier = $option['identifier'];
					foreach($geturl as $option => $value) {
						$sorturladdarray[$identifier] .= !in_array($option, array('filter', 'sortid', 'orderby', 'fid', 'searchsort', $identifier)) ? '&amp;'.rawurlencode($option).'='.rawurlencode($value) : '';
					}
				}
			}

			foreach($geturl as $field => $value) {
				if($field != 'page' && $field != 'fid' && $field != 'searchoption') {
					$multiadd[] = rawurlencode($field).'='.rawurlencode($value);
					if(in_array($field, $filterfield)) {
						if($field == 'digest') {
							$filterarr['digest'] = 1;
						} elseif($field == 'recommend') {
							$filterarr['recommends'] = intval($_G['setting']['recommendthread']['iconlevels'][0]);
						} elseif($field == 'specialtype') {
							$filterarr['special'] = $specialtype[$value];
							$filterarr['specialthread'] = 1;
							if($value == 'reward') {
								if($_GET['rewardtype'] == 1) {
									$filterarr['pricemore'] = 0;
								} elseif($_GET['rewardtype'] == 2) {
									$filterarr['pricesless'] = 0;
								}
							}
						} elseif($field == 'dateline') {
							if($value) {
								$filterarr['lastpostmore'] = TIMESTAMP - $value;
							}
						} elseif($field == 'typeid' || $field == 'sortid') {
							$fieldstr = $field == 'typeid' ? 'intype' : 'insort';
							$filterarr[$fieldstr] = $value;
						}
						$sp = ' ';
					}
				}
			}
			if(count($filterarr) == 1) {
				foreach($filterarr as $key => $value) {
					if($key == 'intype') {
						$threadclasscount = array('id' => $value, 'idtype' => 'typeid');
					} elseif($key == 'insort') {
						$threadclasscount = array('id' => $value, 'idtype' => 'sortid');
					}
				}
			}
		}
	}
	$simplestyle = true;
}

if(!empty($_GET['orderby']) && !$_G['setting']['closeforumorderby'] && in_array($_GET['orderby'], array('lastpost', 'dateline', 'replies', 'views', 'recommends', 'heats'))) {
	$forumdisplayadd['orderby'] .= '&orderby='.$_GET['orderby'];
} else {
	$_GET['orderby'] = isset($_G['cache']['forums'][$_G['fid']]['orderby']) ? $_G['cache']['forums'][$_G['fid']]['orderby'] : 'lastpost';
}

$_GET['ascdesc'] = isset($_G['cache']['forums'][$_G['fid']]['ascdesc']) ? $_G['cache']['forums'][$_G['fid']]['ascdesc'] : 'DESC';

$check = array();
$check[$filter] = $check[$_GET['orderby']] = $check[$_GET['ascdesc']] = 'selected="selected"';

if(($_G['forum']['status'] != 3 && $_G['forum']['allowside']) || !empty($_G['forum']['threadsorts']['templatelist'])) {
	updatesession();
	$onlinenum = C::app()->session->count_by_fid($_G['fid']);
	if(!IS_ROBOT && ($_G['setting']['whosonlinestatus'] == 2 || $_G['setting']['whosonlinestatus'] == 3)) {
		$_G['setting']['whosonlinestatus'] = 1;
		$detailstatus = $showoldetails == 'yes' || (((!isset($_G['cookie']['onlineforum']) && !$_G['setting']['whosonline_contract']) || $_G['cookie']['onlineforum']) && !$showoldetails);

		if($detailstatus) {
			$actioncode = lang('forum/action');
			$whosonline = array();
			$forumname = strip_tags($_G['forum']['name']);

			$whosonline = C::app()->session->fetch_all_by_fid($_G['fid'], 12);
			$_G['setting']['whosonlinestatus'] = 1;
		}
	} else {
		$_G['setting']['whosonlinestatus'] = 0;
	}
}

if($_G['forum']['threadsorts']['types'] && $sortoptionarray && ($_GET['searchoption'] || $_GET['searchsort'])) {
	$sortid = intval($_GET['sortid']);

	if($_GET['searchoption']){
		$forumdisplayadd['page'] = '&sortid='.$sortid;
		foreach($_GET['searchoption'] as $optionid => $option) {
			$optionid = intval($optionid);
			$searchoption = '';
			if(is_array($option['value'])) {
				foreach($option['value'] as $v) {
					$v = rawurlencode((string)$v);
					$searchoption .= "&searchoption[$optionid][value][$v]=$v";
				}
			} else {
				$option['value'] = rawurlencode((string)$option['value']);
				$option['value'] && $searchoption = "&searchoption[$optionid][value]=$option[value]";
			}
			$option['type'] = rawurlencode((string)$option['type']);
			$identifier = $sortoptionarray[$sortid][$optionid]['identifier'];
			$forumdisplayadd['page'] .= $searchoption ? "$searchoption&searchoption[$optionid][type]=$option[type]" : '';
		}
	}

	$searchsorttids = sortsearch($_GET['sortid'], $sortoptionarray, $_GET['searchoption'], $selectadd, $_G['fid']);
	$filterarr['intids'] = $searchsorttids ? $searchsorttids : array(0);
}

if(isset($_GET['searchoption'])) {
    $_GET['searchoption'] = dhtmlspecialchars($_GET['searchoption']);
}

if($_G['forum']['relatedgroup']) {
	$relatedgroup = explode(',', $_G['forum']['relatedgroup']);
	$relatedgroup[] = $_G['fid'];
	$filterarr['inforum'] = $relatedgroup;
} else {
	$filterarr['inforum'] = $_G['fid'];
}
if(empty($filter) && empty($_GET['sortid']) && empty($_G['forum']['relatedgroup'])) {
	if($forumarchive) {
		if($_GET['archiveid']) {
			$_G['forum_threadcount'] = $forumarchive[$_GET['archiveid']]['threads'];
		} else {
			$primarytabthreads = $_G['forum']['threads'];
			foreach($forumarchive as $arcid => $avalue) {
				if($arcid) {
					$primarytabthreads = $primarytabthreads - $avalue['threads'];
				}
			}
			$_G['forum_threadcount'] = $primarytabthreads;
		}
	} else {
		$_G['forum_threadcount'] = $_G['forum']['threads'];
	}
} else {
	$filterarr['sticky'] = 0;
	$_G['forum_threadcount'] = C::t('forum_thread')->count_search($filterarr, $tableid);
	if($threadclasscount) {
		threadclasscount($_G['fid'], $threadclasscount['id'], $threadclasscount['idtype'], $_G['forum_threadcount']);
	}
}

$thisgid = $_G['forum']['type'] == 'forum' ? $_G['forum']['fup'] : (!empty($_G['cache']['forums'][$_G['forum']['fup']]['fup']) ? $_G['cache']['forums'][$_G['forum']['fup']]['fup'] : 0);
$forumstickycount = $stickycount = 0;
$stickytids = '';
$showsticky = !defined('MOBILE_HIDE_STICKY') || !MOBILE_HIDE_STICKY;
if($showsticky) {
	$forumstickytids = array();
	if($_G['page'] !== 1 || $filterbool === false) {
		if($_G['setting']['globalstick'] && $_G['forum']['allowglobalstick']) {
			$stickytids = explode(',', str_replace("'", '', $_G['cache']['globalstick']['global']['tids']));
			if(!empty($_G['cache']['globalstick']['categories'][$thisgid]['count'])) {
				$stickytids = array_merge($stickytids, explode(',', str_replace("'", '', $_G['cache']['globalstick']['categories'][$thisgid]['tids'])));
			}

			if($_G['forum']['status'] != 3) {
				$stickycount = $_G['cache']['globalstick']['global']['count'];
				if(!empty($_G['cache']['globalstick']['categories'][$thisgid])) {
					$stickycount += $_G['cache']['globalstick']['categories'][$thisgid]['count'];
				}
			}
		}

		if($_G['forum']['allowglobalstick']) {
			$forumstickycount = 0;
			$forumstickfid = $_G['forum']['status'] != 3 ? $_G['fid'] : $_G['forum']['fup'];
			if(isset($_G['cache']['forumstick'][$forumstickfid])) {
				$forumstickycount = count($_G['cache']['forumstick'][$forumstickfid]);
				$forumstickytids = $_G['cache']['forumstick'][$forumstickfid];
			}
			if(!empty($forumstickytids)) {
				$stickytids = array_merge($stickytids, $forumstickytids);
			}
			$stickycount += $forumstickycount;
		}
	}
}

if($_G['forum']['picstyle']) {
	$forumdefstyle = isset($_GET['forumdefstyle']) ? $_GET['forumdefstyle'] : '';
	/* if($forumdefstyle) {
		switch($forumdefstyle) {
			case 'no': dsetcookie('forumdefstyle', ''); break;
			case 'yes': dsetcookie('forumdefstyle', 1, 31536000); break;
		}
	} */
	if(empty($_G['cookie']['forumdefstyle'])) {
		if(!empty($_G['setting']['forumpicstyle']['thumbnum'])) {
			$_G['tpp'] = $_G['setting']['forumpicstyle']['thumbnum'];
		}
		$stickycount = $showsticky = 0;
	}
}

$start_limit = ($page - 1) * $_G['tpp'];

$forumdisplayadd['page'] = !empty($forumdisplayadd['page']) ? $forumdisplayadd['page'] : '';
$multipage_archive = $_GET['archiveid'] && in_array($_GET['archiveid'], $threadtableids) ? "&archiveid={$_GET['archiveid']}" : '';
$multipage = multi($_G['forum_threadcount'], $_G['tpp'], $page, "forum.php?mod=forumdisplay&fid=$_G[fid]".$forumdisplayadd['page'].($multiadd ? '&'.implode('&', $multiadd) : '')."$multipage_archive", $_G['setting']['threadmaxpages']);

$realpages = @ceil($_G['forum_threadcount']/$_G['tpp']);
$maxpage = ($_G['setting']['threadmaxpages'] && $_G['setting']['threadmaxpages'] < $realpages) ? $_G['setting']['threadmaxpages'] : $realpages;
$nextpage = ($page + 1) > $maxpage ? 1 : ($page + 1);
$multipage_more = "forum.php?mod=forumdisplay&fid=$_G[fid]".$forumdisplayadd['page'].($multiadd ? '&'.implode('&', $multiadd) : '')."$multipage_archive".'&page='.$nextpage;

$extra = rawurlencode(!IS_ROBOT ? 'page='.$page.($forumdisplayadd['page'] ? '&filter='.$filter.$forumdisplayadd['page'] : '') : 'page=1');

$separatepos = 0;
$_G['forum_threadlist'] = $threadids = array();
$_G['forum_colorarray'] = array('', '#EE1B2E', '#EE5023', '#996600', '#3C9D40', '#2897C5', '#2B65B7', '#8F2A90', '#EC1282');

$filterarr['sticky'] = 4;
$filterarr['displayorder'] = !$filterbool && $stickycount ? array(0, 1) : array(0, 1, 2, 3, 4);

$threadlist = array();
$indexadd = '';
$_order = "displayorder DESC, $_GET[orderby] $_GET[ascdesc]";
if($filterbool) {
	if($filterarr['digest']) {
		$indexadd = " FORCE INDEX (digest) ";
	}
} elseif($showsticky && $stickytids && is_array($stickytids)) {
	$filterarr1 = $filterarr;
	$filterarr1['inforum'] = '';
	$filterarr1['intids'] = $stickytids;
	$filterarr1['displayorder'] = array(2, 3, 4);
	$threadlist = C::t('forum_thread')->fetch_all_search($filterarr1, $tableid, $start_limit, $_G['tpp'], $_order, '');
	unset($filterarr1);
}
$threadlist = array_merge($threadlist, C::t('forum_thread')->fetch_all_search($filterarr, $tableid, $start_limit, $_G['tpp'], $_order, '', $indexadd));
unset($_order);
if(empty($threadlist) && $page <= ceil($_G['forum_threadcount'] / $_G['tpp'])) {
	require_once libfile('function/post');
	updateforumcount($_G['fid']);
}


// die(print_r($threadlist));

foreach ($threadlist as $v) {
    $data[] = array(
            'tid' => $v['tid'],
            'fid' => $v['fid'],
            'author' => $v['author'],
            'authorid' => $v['authorid'],
            'subject' => $v['subject'],
            'dateline' => date("m-d H:i", $v['dateline']),
            'displayorder' => $v['displayorder'],
            'special' => $v['special'],
            'views' => $v['views'],
            'replies' => $v['replies'],
        );
}

showmessage2("OK", $data, 20000);