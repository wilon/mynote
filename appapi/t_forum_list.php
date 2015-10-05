<?php
/**
 * 获取版块列表，即投诉爆料部门板块
 *
 * @parameter $fup 板块父ID
 * @parameter $uid 用户ID
 *
 * @Author    王伟龙 QQ:973885303
 * @FileName  t_forum.php
 * @Date      2014-9-9 14:20:18  
 */

// 准备
require("./inc.php");  // $_G加载用户信息

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('function/forumlist');

$gid = intval($_POST['gid']);  // 得到gid

// $showoldetails = get_index_online_details();

// if(!$_G['uid'] && !$gid && $_G['setting']['cacheindexlife'] && !defined('IN_ARCHIVER') && !defined('IN_MOBILE')) {
// 	get_index_page_guest_cache();
// }

$newthreads = round((TIMESTAMP - $_G['member']['lastvisit'] + 600) / 1000) * 1000;

$catlist = $forumlist = $sublist = $forumname = $collapse = $favforumlist = array();
$threads = $posts = $todayposts = $announcepm = 0;
$postdata = $_G['cache']['historyposts'] ? explode("\t", $_G['cache']['historyposts']) : array(0,0);
$postdata[0] = intval($postdata[0]);
$postdata[1] = intval($postdata[1]);

list($navtitle, $metadescription, $metakeywords) = get_seosetting('forum');
if(!$navtitle) {
	$navtitle = $_G['setting']['navs'][2]['navname'];
	$nobbname = false;
} else {
	$nobbname = true;
}
if(!$metadescription) {
	$metadescription = $navtitle;
}
if(!$metakeywords) {
	$metakeywords = $navtitle;
}

// 判断gid
if(!$gid && (!defined('FORUM_INDEX_PAGE_MEMORY') || !FORUM_INDEX_PAGE_MEMORY)) {

    // gid错误，判断板块存在
	$announcements = get_index_announcements();

	$forums = C::t('forum_forum')->fetch_all_by_status(1);
    //die(print_r($forums));
	$fids = array();
	foreach($forums as $forum) {
		$fids[$forum['fid']] = $forum['fid'];
	}
    
	$forum_access = array();
	if(!empty($_G['member']['accessmasks'])) {
		$forum_access = C::t('forum_access')->fetch_all_by_fid_uid($fids, $_G['uid']);
	}

	$forum_fields = C::t('forum_forumfield')->fetch_all($fids);

	foreach($forums as $forum) {
		if($forum_fields[$forum['fid']]['fid']) {
			$forum = array_merge($forum, $forum_fields[$forum['fid']]);
		}
		if($forum_access['fid']) {
			$forum = array_merge($forum, $forum_access[$forum['fid']]);
		}
		$forumname[$forum['fid']] = strip_tags($forum['name']);
		$forum['extra'] = empty($forum['extra']) ? array() : dunserialize($forum['extra']);
		if(!is_array($forum['extra'])) {
			$forum['extra'] = array();
		}

		if($forum['type'] == 'group') {

			if($forum['moderators']) {
			 	$forum['moderators'] = moddisplay($forum['moderators'], 'flat');
			}
			$forum['forumscount'] 	= 0;
			$catlist[$forum['fid']] = $forum;
            // 判断有无子版块
            $gquery = C::t('forum_forum')->fetch_all_info_by_fids($forum['fid']);
            $query = C::t('forum_forum')->fetch_all_info_by_fids(0, 1, 0, $forum['fid'], 1, 0, 0, 'forum');
            if(empty($gquery) || empty($query)) continue;
            
            // 数据
            $data[] = array(
                'fid' => $forum['fid'],
                'name' => $forum['name'],
                //'namecolor' => $forum['extra']['namecolor'],
                //'icon' => $forum['icon']
            );
		}
	}   
    

} else {
    // 处理gid
	require_once './source/forum/misc_category.php';
	
    //die(print_r($forumlist));
    if (empty($forumlist)) 
        showmessage2('板块不存在！', '', 41001);
    foreach ($forumlist as $v) {
        // 处理图标
        $threadtypes = unserialize($v['threadtypes']);
        foreach ($threadtypes['types'] as $k2 => $v2) {
        	$types[] = array(
					'id'   => $k2,
					'name' => $v2
        		);
        }
        preg_match("/<img\ssrc=\"(.*?)\"/i", $v['icon'], $icon);
        $data[] = array(
			'fid'         => $v['fid'],
			'name'        => $v['name'],
			'icon'        => $icon ? $_G['setting']['discuzurl'].'/'.$icon[1] : '',
			'threadtypes' => array(
					'required' => $threadtypes['required'],
					'types'    => $types
            	),
			'todayposts'  => $v['todayposts'],
			'threads'     => $v['threads'],
			'posts'       => $v['posts'],
        );
        unset($types, $icon, $threadtypes);
    }
}


showmessage2('OK', $data, 20000);
/* if(defined('IN_ARCHIVER')) {
	include loadarchiver('forum/discuz');
	exit();
}
categorycollapse();

if($gid && !empty($catlist)) {
	$_G['category'] = $catlist[$gid];
	$forumseoset = array(
		'seotitle' => $catlist[$gid]['seotitle'],
		'seokeywords' => $catlist[$gid]['keywords'],
		'seodescription' => $catlist[$gid]['seodescription']
	);
	$seodata = array('fgroup' => $catlist[$gid]['name']);
	list($navtitle, $metadescription, $metakeywords) = get_seosetting('threadlist', $seodata, $forumseoset);
	if(empty($navtitle)) {
		$navtitle = $navtitle_g;
		$nobbname = false;
	} else {
		$nobbname = true;
	}
	$_G['fid'] = $gid;
} */


// include template('diy:forum/discuz:'.$gid);  删除加载模板



function get_index_announcements() {
	global $_G;
	$announcements = '';
	if($_G['cache']['announcements']) {
		$readapmids = !empty($_G['cookie']['readapmid']) ? explode('D', $_G['cookie']['readapmid']) : array();
		foreach($_G['cache']['announcements'] as $announcement) {
			if(!$announcement['endtime'] || $announcement['endtime'] > TIMESTAMP && (empty($announcement['groups']) || in_array($_G['member']['groupid'], $announcement['groups']))) {
				if(empty($announcement['type'])) {
					$announcements .= '<li><span><a href="forum.php?mod=announcement&id='.$announcement['id'].'" target="_blank" class="xi2">'.$announcement['subject'].
						'</a></span><em>('.dgmdate($announcement['starttime'], 'd').')</em></li>';
				} elseif($announcement['type'] == 1) {
					$announcements .= '<li><span><a href="'.$announcement['message'].'" target="_blank" class="xi2">'.$announcement['subject'].
						'</a></span><em>('.dgmdate($announcement['starttime'], 'd').')</em></li>';
				}
			}
		}
	}
	return $announcements;
}

function get_index_page_guest_cache() {
	global $_G;
	$indexcache = getcacheinfo(0);
	if(TIMESTAMP - $indexcache['filemtime'] > $_G['setting']['cacheindexlife']) {
		@unlink($indexcache['filename']);
		define('CACHE_FILE', $indexcache['filename']);
	} elseif($indexcache['filename']) {
		@readfile($indexcache['filename']);
		$updatetime = dgmdate($indexcache['filemtime'], 'H:i:s');
		$gzip = $_G['gzipcompress'] ? ', Gzip enabled' : '';
		echo "<script type=\"text/javascript\">
			if($('debuginfo')) {
				$('debuginfo').innerHTML = '. This page is cached  at $updatetime $gzip .';
			}
			</script>";
		exit();
	}
}

function get_index_memory_by_groupid($key) {
	$enable = getglobal('setting/memory/forumindex');
	if($enable !== null && memory('check')) {
		if(IS_ROBOT) {
			$key = 'for_robot';
		}
		$ret = memory('get', 'forum_index_page_'.$key);
		define('FORUM_INDEX_PAGE_MEMORY', $ret ? 1 : 0);
		if($ret) {
			return $ret;
		}
	}
	return array('none' => null);
}

function get_index_online_details() {
	$showoldetails = getgpc('showoldetails');
	switch($showoldetails) {
		//case 'no': dsetcookie('onlineindex', ''); break;
		//case 'yes': dsetcookie('onlineindex', 1, 86400 * 365); break;
	}
	return $showoldetails;
}

function do_forum_bind_domains() {
	global $_G;
	if($_G['setting']['binddomains'] && $_G['setting']['forumdomains']) {
		$loadforum = isset($_G['setting']['binddomains'][$_SERVER['HTTP_HOST']]) ? max(0, intval($_G['setting']['binddomains'][$_SERVER['HTTP_HOST']])) : 0;
		if($loadforum) {
			dheader('Location: '.$_G['setting']['siteurl'].'/forum.php?mod=forumdisplay&fid='.$loadforum);
		}
	}
}

function categorycollapse() {
	global $_G, $collapse, $catlist;
	if(!$_G['uid']) {
		return;
	}
	foreach($catlist as $fid => $forum) {
		if(!isset($_G['cookie']['collapse']) || strpos($_G['cookie']['collapse'], '_category_'.$fid.'_') === FALSE) {
			$catlist[$fid]['collapseimg'] = 'collapsed_no.gif';
			$collapse['category_'.$fid] = '';
		} else {
			$catlist[$fid]['collapseimg'] = 'collapsed_yes.gif';
			$collapse['category_'.$fid] = 'display: none';
		}
	}

	for($i = -2; $i <= 0; $i++) {
		if(!isset($_G['cookie']['collapse']) || strpos($_G['cookie']['collapse'], '_category_'.$i.'_') === FALSE) {
			$collapse['collapseimg_'.$i] = 'collapsed_no.gif';
			$collapse['category_'.$i] = '';
		} else {
			$collapse['collapseimg_'.$i] = 'collapsed_yes.gif';
			$collapse['category_'.$i] = 'display: none';
		}
	}
}
