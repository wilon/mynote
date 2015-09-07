<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: post_newreply.php 22680 2011-05-17 07:38:18Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('function/forumlist');

$isfirstpost = 0;
$showthreadsorts = 0;
$quotemessage = '';

if($special == 5) {
	$debate = array_merge($thread, DB::fetch_first("SELECT * FROM ".DB::table('forum_debate')." WHERE tid='$_G[tid]'"));
	$standquery = DB::query("SELECT stand FROM ".DB::table('forum_debatepost')." WHERE tid='$_G[tid]' AND uid='$_G[uid]' AND stand>'0' ORDER BY dateline LIMIT 1");
	$firststand = DB::result_first("SELECT stand FROM ".DB::table('forum_debatepost')." WHERE tid='$_G[tid]' AND uid='$_G[uid]' AND stand>'0' ORDER BY dateline LIMIT 1");
	$stand = $firststand ? $firststand : intval($_G['gp_stand']);

	if($debate['endtime'] && $debate['endtime'] < TIMESTAMP) {
		showmessage2('Error:debate_end', '', 45101);
	}
}

if(!$_G['uid'] && !((!$_G['forum']['replyperm'] && $_G['group']['allowreply']) || ($_G['forum']['replyperm'] && forumperm($_G['forum']['replyperm'])))) {
	showmessage2('Error:replyperm_login_nopermission', '', 45102);
} elseif(empty($_G['forum']['allowreply'])) {
	if(!$_G['forum']['replyperm'] && !$_G['group']['allowreply']) {
		showmessage2('Error:replyperm_none_nopermission', '', 45103);
	} elseif($_G['forum']['replyperm'] && !forumperm($_G['forum']['replyperm'])) {
		showmessage2('Error:replyperm', '', 45104);
	}
} elseif($_G['forum']['allowreply'] == -1) {
	showmessage2('Error:post_forum_newreply_nopermission', '', 45105);
}

if(!$_G['uid'] && ($_G['setting']['need_avatar'] || $_G['setting']['need_email'] || $_G['setting']['need_friendnum'])) {
	showmessage2('Error:replyperm_login_nopermission', '', 45106);
}

if(empty($thread)) {
	showmessage2('Error:thread_nonexistence', '', 45107);
} elseif($thread['price'] > 0 && $thread['special'] == 0 && !$_G['uid']) {
	showmessage2('Error:group_nopermission', '', 45108);
}

checklowerlimit('reply', 0, 1, $_G['forum']['fid']);

if($_G['setting']['commentnumber'] && !empty($_G['gp_comment'])) {
	$posttable = getposttablebytid($_G['tid']);
	if(!submitcheck('commentsubmit', 0, $seccodecheck, $secqaacheck)) {
		showmessage2('Error:submitcheck_error', '', 45109);
	}
	$post = DB::fetch_first('SELECT * FROM '.DB::table($posttable)." WHERE pid='$_G[gp_pid]'");
	if(!$post) {
		showmessage2('Error:post_nonexistence', '', 45110);
	}
	if($thread['closed'] && !$_G['forum']['ismoderator'] && !$thread['isgroup']) {
		showmessage2('Error:post_thread_closed', '', 45111);
	} elseif(!$thread['isgroup'] && $post_autoclose = checkautoclose($thread)) {
		showmessage2("Error:$post_autoclose", '', 45112);
	} elseif(checkflood()) {
		showmessage2('Error:post_flood_ctrl', '', 45113);
	} elseif(checkmaxpostsperhour()) {
		showmessage2('Error:post_flood_ctrl_posts_per_hour', '', 45114);
	}
	$commentscore = '';
	if(!empty($_G['gp_commentitem']) && !empty($_G['uid']) && $post['authorid'] != $_G['uid']) {
		foreach($_G['gp_commentitem'] as $itemk => $itemv) {
			if($itemv !== '') {
				$commentscore .= strip_tags(trim($itemk)).': <i>'.intval($itemv).'</i> ';
			}
		}
	}
	$comment = cutstr(($commentscore ? $commentscore.'<br />' : '').censor(trim(htmlspecialchars($_G['gp_message'])), '***'), 200, ' ');
	if(!$comment) {
		showmessage2('Error:post_sm_isnull', '', 45115);
	}
	DB::insert('forum_postcomment', array(
		'tid' => $post['tid'],
		'pid' => $post['pid'],
		'author' => $_G['username'],
		'authorid' => $_G['uid'],
		'dateline' => TIMESTAMP,
		'comment' => $comment,
		'score' => $commentscore ? 1 : 0,
		'useip' => $_G['clientip'],
	));
	DB::update($posttable, array('comment' => 1), "pid='$_G[gp_pid]'");
	!empty($_G['uid']) && updatepostcredits('+', $_G['uid'], 'reply', $_G['fid']);
	if(!empty($_G['uid']) && $_G['uid'] != $post['authorid']) {
		notification_add($post['authorid'], 'pcomment', 'comment_add', array(
			'tid' => $_G['tid'],
			'pid' => $_G['gp_pid'],
			'subject' => $thread['subject'],
			'commentmsg' => cutstr(str_replace(array('[b]', '[/b]', '[/color]'), '', preg_replace("/\[color=([#\w]+?)\]/i", "", stripslashes($comment))), 200)
		));
	}
	if($_G['setting']['heatthread']['type'] == 2) {
		update_threadpartake($post['tid']);
	}
	$pcid = DB::result_first("SELECT id FROM ".DB::table('forum_postcomment')." WHERE pid='$_G[gp_pid]' AND authorid='-1'");
	if(!empty($_G['uid']) && $_G['gp_commentitem']) {
		$query = DB::query('SELECT comment FROM '.DB::table('forum_postcomment')." WHERE pid='$_G[gp_pid]' AND score='1'");
		$totalcomment = array();
		while($comment = DB::fetch($query)) {
			$comment['comment'] = addslashes($comment['comment']);
			if(strexists($comment['comment'], '<br />')) {
				if(preg_match_all("/([^:]+?):\s<i>(\d+)<\/i>/", $comment['comment'], $a)) {
					foreach($a[1] as $k => $itemk) {
						$totalcomment[trim($itemk)][] = $a[2][$k];
					}
				}
			}
		}
		$totalv = '';
		foreach($totalcomment as $itemk => $itemv) {
			$totalv .= strip_tags(trim($itemk)).': <i>'.(floatval(sprintf('%1.1f', array_sum($itemv) / count($itemv)))).'</i> ';
		}

		if($pcid) {
			DB::update('forum_postcomment', array('comment' => $totalv, 'dateline' => TIMESTAMP + 1), "id='$pcid'");
		} else {
			DB::insert('forum_postcomment', array(
				'tid' => $post['tid'],
				'pid' => $post['pid'],
				'author' => '',
				'authorid' => '-1',
				'dateline' => TIMESTAMP + 1,
				'comment' => $totalv
			));
		}
	}
	DB::update('forum_postcomment', array('dateline' => TIMESTAMP + 1), "id='$pcid'");
	showmessage2('Error:comment_add_succeed', '', 45116);
}

if($special == 127) {
	$posttable = getposttablebytid($_G['tid']);
	$postinfo = DB::fetch_first("SELECT message FROM ".DB::table($posttable)." WHERE tid='$_G[tid]' AND first='1'");
	$sppos = strrpos($postinfo['message'], chr(0).chr(0).chr(0));
	$specialextra = substr($postinfo['message'], $sppos + 3);
}


if(trim($subject) == '' && trim($message) == '' && $thread['special'] != 2) {
	showmessage2('Error:post_sm_isnull', '', 45117);
} elseif($thread['closed'] && !$_G['forum']['ismoderator'] && !$thread['isgroup']) {
	showmessage2('Error:post_thread_closed', '', 45118);
} elseif(!$thread['isgroup'] && $post_autoclose = checkautoclose($thread)) {
	showmessage2("Error:$post_autoclose", '', 45119);
} elseif($post_invalid = checkpost($subject, $message, $special == 2 && $_G['group']['allowposttrade'])) {
	showmessage2("Error:$post_invalid", '', 45120);
} elseif(checkflood()) {
	showmessage2('Error:post_flood_ctrl', '', 45121);
} elseif(checkmaxpostsperhour()) {
	showmessage2('Error:post_flood_ctrl_posts_per_hour', '', 45122);
}
if(!empty($_G['gp_trade']) && $thread['special'] == 2 && $_G['group']['allowposttrade']) {

	$item_price = floatval($_G['gp_item_price']);
	$item_credit = intval($_G['gp_item_credit']);
	if(!trim($_G['gp_item_name'])) {
		showmessage2('Error:trade_please_name', '', 45123);
	} elseif($_G['group']['maxtradeprice'] && $item_price > 0 && ($_G['group']['mintradeprice'] > $item_price || $_G['group']['maxtradeprice'] < $item_price)) {
		showmessage2('Error:trade_price_between', '', 45124);
	} elseif($_G['group']['maxtradeprice'] && $item_credit > 0 && ($_G['group']['mintradeprice'] > $item_credit || $_G['group']['maxtradeprice'] < $item_credit)) {
		showmessage2('Error:trade_credit_between', '', 45125);
	} elseif(!$_G['group']['maxtradeprice'] && $item_price > 0 && $_G['group']['mintradeprice'] > $item_price) {
		showmessage2('Error:trade_price_more_than', '', 45126);
	} elseif(!$_G['group']['maxtradeprice'] && $item_credit > 0 && $_G['group']['mintradeprice'] > $item_credit) {
		showmessage2('Error:trade_credit_more_than', '', 45127);
	} elseif($item_price <= 0 && $item_credit <= 0) {
		showmessage2('Error:trade_pricecredit_need', '', 45128);
	} elseif($_G['gp_item_number'] < 1) {
		showmessage2('Error:tread_please_number', '', 45129);
	}

}

$attentionon = empty($_G['gp_attention_add']) ? 0 : 1;
$attentionoff = empty($attention_remove) ? 0 : 1;

if($thread['lastposter'] != $_G['member']['username'] && $_G['uid']) {
	if($_G['setting']['heatthread']['type'] == 1 && $_G['setting']['heatthread']['reply']) {
		$posttable = getposttablebytid($_G['tid']);
		$userreplies = DB::result_first("SELECT COUNT(*) FROM ".DB::table($posttable)." WHERE tid='$_G[tid]' AND first='0' AND authorid='$_G[uid]'");
		$thread['heats'] += round($_G['setting']['heatthread']['reply'] * pow(0.8, $userreplies));
		DB::query("UPDATE ".DB::table('forum_thread')." SET heats='$thread[heats]' WHERE tid='$_G[tid]'", 'UNBUFFERED');
	} elseif($_G['setting']['heatthread']['type'] == 2) {
		update_threadpartake($_G['tid']);
	}
}

$bbcodeoff = checkbbcodes($message, !empty($_G['gp_bbcodeoff']));
$smileyoff = checksmilies($message, !empty($_G['gp_smileyoff']));
$parseurloff = !empty($_G['gp_parseurloff']);
$htmlon = $_G['group']['allowhtml'] && !empty($_G['gp_htmlon']) ? 1 : 0;
$usesig = !empty($_G['gp_usesig']) ? 1 : ($_G['uid'] && $_G['group']['maxsigsize'] ? 1 : 0);

$isanonymous = $_G['group']['allowanonymous'] && !empty($_G['gp_isanonymous'])? 1 : 0;
$author = empty($isanonymous) ? $_G['username'] : '';

$pinvisible = $modnewreplies ? -2 : ($thread['displayorder'] == -4 ? -3 : 0);
$message = preg_replace('/\[attachimg\](\d+)\[\/attachimg\]/is', '[attach]\1[/attach]', $message);
$postcomment = in_array(2, $_G['setting']['allowpostcomment']) && $_G['group']['allowcommentreply'] && !$pinvisible && !empty($_G['gp_reppid']) && ($nauthorid != $_G['uid'] || $_G['setting']['commentpostself']) ? messagecutstr($message, 200, ' ') : '';

if(!empty($_G['gp_noticetrimstr'])) {
	$message = $_G['gp_noticetrimstr']."\n\n".$message;
	$bbcodeoff = false;
}

$pid = insertpost(array(
	'fid' => $_G['fid'],
	'tid' => $_G['tid'],
	'first' => '0',
	'author' => $_G['username'],
	'authorid' => $_G['uid'],
	'subject' => $subject,
	'dateline' => $_G['timestamp'],
	'message' => $message,
	'useip' => $_G['clientip'],
	'invisible' => $pinvisible,
	'anonymous' => $isanonymous,
	'usesig' => $usesig,
	'htmlon' => $htmlon,
	'bbcodeoff' => $bbcodeoff,
	'smileyoff' => $smileyoff,
	'parseurloff' => $parseurloff,
	'attachment' => '0',
	'status' => (defined('IN_MOBILE') ? 8 : 0),
));

if($pid && getstatus($thread['status'], 1)) {
	$postionid = savepostposition($_G['tid'], $pid, true);
}
if(getstatus($thread['status'], 3) && $postionid) {
	$rushstopfloor = DB::result_first("SELECT stopfloor FROM ".DB::table('forum_threadrush')." WHERE tid = '$_G[tid]'");
	if($rushstopfloor > 0 && $thread['closed'] == 0 && $postionid >= $rushstopfloor) {
		DB::query("UPDATE ".DB::table('forum_thread')." SET closed='1' WHERE tid='$_G[tid]'");
	}
}
useractionlog($_G['uid'], 'pid');

$nauthorid = 0;
if(!empty($_G['gp_noticeauthor']) && !$isanonymous && !$modnewreplies) {
	list($ac, $nauthorid) = explode('|', authcode($_G['gp_noticeauthor'], 'DECODE'));
	if($nauthorid != $_G['uid']) {
		if($ac == 'q') {
			notification_add($nauthorid, 'post', 'reppost_noticeauthor', array(
				'tid' => $thread['tid'],
				'subject' => $thread['subject'],
				'fid' => $_G['fid'],
				'pid' => $pid,
			));
		} elseif($ac == 'r') {
			notification_add($nauthorid, 'post', 'reppost_noticeauthor', array(
				'tid' => $thread['tid'],
				'subject' => $thread['subject'],
				'fid' => $_G['fid'],
				'pid' => $pid,
				'from_id' => $thread['tid'],
				'from_idtype' => 'post',
			));
		}
	}

	if($postcomment) {
		$rpid = intval($_G['gp_reppid']);
		if(!$posttable) {
			$posttable = getposttablebytid($thread['tid']);
		}
		if($rpost = DB::fetch_first("SELECT first FROM ".DB::table($posttable)." WHERE pid='$rpid'")) {
			if(!$rpost['first']) {
				DB::insert('forum_postcomment', array(
					'tid' => $thread['tid'],
					'pid' => $rpid,
					'rpid' => $pid,
					'author' => $_G['username'],
					'authorid' => $_G['uid'],
					'dateline' => TIMESTAMP,
					'comment' => $postcomment,
					'score' => 0,
					'useip' => $_G['clientip'],
				));
				DB::update($posttable, array('comment' => 1), "pid='$rpid'");
			}
		}
		unset($postcomment);
	}
}

if($thread['authorid'] != $_G['uid'] && getstatus($thread['status'], 6) && empty($_G['gp_noticeauthor']) && !$isanonymous && !$modnewreplies) {
	$posttable = getposttablebytid($_G['tid']);
	$thapost = DB::fetch_first("SELECT tid, author, authorid, useip, dateline, anonymous, status, message FROM ".DB::table($posttable)." WHERE tid='$_G[tid]' AND first='1' AND invisible='0'");
	notification_add($thapost['authorid'], 'post', 'reppost_noticeauthor', array(
		'tid' => $thread['tid'],
		'subject' => $thread['subject'],
		'fid' => $_G['fid'],
		'pid' => $pid,
		'from_id' => $thread['tid'],
		'from_idtype' => 'post',
	));
}

if($thread['replycredit'] > 0 && $thread['authorid'] != $_G['uid'] && $_G['uid']) {

	$replycredit_rule = DB::fetch_first("SELECT * FROM ".DB::table('forum_replycredit')." WHERE tid = '$_G[tid]' LIMIT 1");
	$have_replycredit = DB::result_first("SELECT COUNT(*) FROM ".DB::table('common_credit_log')." WHERE relatedid = '{$_G[tid]}' AND uid = '{$_G[uid]}' AND operation = 'RCA' LIMIT {$replycredit_rule['times']} ");
	if($replycredit_rule['membertimes'] - $have_replycredit > 0 && $thread['replycredit'] - $replycredit_rule['extcredits'] >= 0) {
		$replycredit_rule['extcreditstype'] = $replycredit_rule['extcreditstype'] ? $replycredit_rule['extcreditstype'] : $_G['setting']['creditstransextra'][10];
		if($replycredit_rule['random'] > 0) {
			$rand = rand(1, 100);
			$rand_replycredit = $rand <= $replycredit_rule['random'] ? true : false ;
		} else {
			$rand_replycredit = true;
		}
		if($rand_replycredit) {
			if(!$posttable) {
				$posttable = getposttablebytid($_G['tid']);
			}
			updatemembercount($_G['uid'], array($replycredit_rule['extcreditstype'] => $replycredit_rule['extcredits']), 1, 'RCA', $_G[tid]);
			DB::update($posttable, array('replycredit' => $replycredit_rule['extcredits']), array('pid' => $pid));
			DB::update("forum_thread", array('replycredit' => $thread['replycredit'] - $replycredit_rule['extcredits']), array('tid' => $_G[tid]));
		}
	}
}

if($special == 5) {

	if(!DB::num_rows($standquery)) {
		if($stand == 1) {
			DB::query("UPDATE ".DB::table('forum_debate')." SET affirmdebaters=affirmdebaters+1 WHERE tid='$_G[tid]'");
		} elseif($stand == 2) {
			DB::query("UPDATE ".DB::table('forum_debate')." SET negadebaters=negadebaters+1 WHERE tid='$_G[tid]'");
		}
	} else {
		$stand = $firststand;
	}
	if($stand == 1) {
		DB::query("UPDATE ".DB::table('forum_debate')." SET affirmreplies=affirmreplies+1 WHERE tid='$_G[tid]'");
	} elseif($stand == 2) {
		DB::query("UPDATE ".DB::table('forum_debate')." SET negareplies=negareplies+1 WHERE tid='$_G[tid]'");
	}
	DB::query("INSERT INTO ".DB::table('forum_debatepost')." (tid, pid, uid, dateline, stand, voters, voterids) VALUES ('$_G[tid]', '$pid', '$_G[uid]', '$_G[timestamp]', '$stand', '0', '')");
}

($_G['group']['allowpostattach'] || $_G['group']['allowpostimage']) && ($_G['gp_attachnew'] || $special == 2 && $_G['gp_tradeaid']) && updateattach($thread['displayorder'] == -4 || $modnewreplies, $_G['tid'], $pid, $_G['gp_attachnew']);

$replymessage = 'post_reply_succeed';
if($special == 2 && $_G['group']['allowposttrade'] && $thread['authorid'] == $_G['uid'] && !empty($_G['gp_trade']) && !empty($_G['gp_item_name'])) {

	require_once libfile('function/trade');
	trade_create(array(
		'tid' => $_G['tid'],
		'pid' => $pid,
		'aid' => $_G['gp_tradeaid'],
		'item_expiration' => $_G['gp_item_expiration'],
		'thread' => $thread,
		'discuz_uid' => $_G['uid'],
		'author' => $author,
		'seller' => empty($_G['gp_paymethod']) && $_G['gp_seller'] ? dhtmlspecialchars(trim($_G['gp_seller'])) : '',
		'item_name' => $_G['gp_item_name'],
		'item_price' => $_G['gp_item_price'],
		'item_number' => $_G['gp_item_number'],
		'item_quality' => $_G['gp_item_quality'],
		'item_locus' => $_G['gp_item_locus'],
		'transport' => $_G['gp_transport'],
		'postage_mail' => $_G['gp_postage_mail'],
		'postage_express' => $_G['gp_postage_express'],
		'postage_ems' => $_G['gp_postage_ems'],
		'item_type' => $_G['gp_item_type'],
		'item_costprice' => $_G['gp_item_costprice'],
		'item_credit' => $_G['gp_item_credit'],
		'item_costcredit' => $_G['gp_item_costcredit']
	));

	$replymessage = 'trade_add_succeed';
	if(!empty($_G['gp_tradeaid'])) {
		convertunusedattach($_G['gp_tradeaid'], $_G['tid'], $pid);
	}

}

if($specialextra) {

	@include_once DISCUZ_ROOT.'./source/plugin/'.$_G['setting']['threadplugins'][$specialextra]['module'].'.class.php';
	$classname = 'threadplugin_'.$specialextra;
	if(class_exists($classname) && method_exists($threadpluginclass = new $classname, 'newreply_submit_end')) {
		$threadpluginclass->newreply_submit_end($_G['fid'], $_G['tid']);
	}

}

$_G['forum']['threadcaches'] && deletethreadcaches($_G['tid']);

include_once libfile('function/stat');
updatestat($thread['isgroup'] ? 'grouppost' : 'post');

$param = array('fid' => $_G['fid'], 'tid' => $_G['tid'], 'pid' => $pid, 'from' => $_G['gp_from'], 'sechash' => !empty($_G['gp_sechash']) ? $_G['gp_sechash'] : '');

dsetcookie('clearUserdata', 'forum');

if($modnewreplies) {
	updatemoderate('pid', $pid);
	unset($param['pid']);
	DB::query("UPDATE ".DB::table('forum_forum')." SET todayposts=todayposts+1, modworks='1' WHERE fid='$_G[fid]'", 'UNBUFFERED');
	$url = empty($_POST['portal_referer']) ? ("forum.php?mod=viewthread&tid={$thread[tid]}") :  $_POST['portal_referer'];
	manage_addnotify('verifypost');
	if(!isset($inspacecpshare)) {
		showmessage2('Error:post_reply_mod_succeed', '', 45130);
	}
} else {
	$lastpostsql = $thread['lastpost'] < $_G['timestamp'] ? "lastpost='$_G[timestamp]'," : '';
	DB::query("UPDATE ".DB::table('forum_thread')." SET lastposter='$author', $lastpostsql replies=replies+1 WHERE tid='$_G[tid]'", 'UNBUFFERED');

	if($thread['displayorder'] != -4) {
		updatepostcredits('+', $_G['uid'], 'reply', $_G['fid']);
		if($_G['forum']['status'] == 3) {
			if($_G['forum']['closed'] > 1) {
				DB::query("UPDATE ".DB::table('forum_thread')." SET lastposter='$author', $lastpostsql replies=replies+1 WHERE tid='".$_G['forum']['closed']."'", 'UNBUFFERED');
			}
			DB::query("UPDATE ".DB::table('forum_groupuser')." SET replies=replies+1, lastupdate='".TIMESTAMP."' WHERE uid='$_G[uid]' AND fid='$_G[fid]'");
			updateactivity($_G['fid'], 0);
			require_once libfile('function/grouplog');
			updategroupcreditlog($_G['fid'], $_G['uid']);
		}

		$lastpost = "$thread[tid]\t".addslashes($thread['subject'])."\t$_G[timestamp]\t$author";
		DB::query("UPDATE ".DB::table('forum_forum')." SET lastpost='$lastpost', posts=posts+1, todayposts=todayposts+1 WHERE fid='$_G[fid]'", 'UNBUFFERED');
		if($_G['forum']['type'] == 'sub') {
			DB::query("UPDATE ".DB::table('forum_forum')." SET lastpost='$lastpost' WHERE fid='".$_G['forum']['fup']."'", 'UNBUFFERED');
		}
	}

	$feed = array();
	if(!isset($_G['gp_addfeed'])) {
		$space = array();
		space_merge($space, 'field_home');
		$_G['gp_addfeed'] = $space['privacy']['feed']['newreply'];
	}
	if(!empty($_G['gp_addfeed']) && $_G['forum']['allowfeed'] && !$isanonymous) {
		if($special == 2 && !empty($_G['gp_trade'])) {
			$feed['icon'] = 'goods';
			$feed['title_template'] = 'feed_thread_goods_title';
			if($_G['gp_item_price'] > 0) {
				if($_G['setting']['creditstransextra'][5] != -1 && $_G['gp_item_credit']) {
					$feed['body_template'] = 'feed_thread_goods_message_1';
				} else {
					$feed['body_template'] = 'feed_thread_goods_message_2';
				}
			} else {
				$feed['body_template'] = 'feed_thread_goods_message_3';
			}
			$feed['body_data'] = array(
				'itemname'=> "<a href=\"forum.php?mod=viewthread&do=tradeinfo&tid=$_G[tid]&pid=$pid\">$_G[gp_item_name]</a>",
				'itemprice'=> $_G['gp_item_price'],
				'itemcredit'=> $_G['gp_item_credit'],
				'creditunit'=> $_G['setting']['extcredits'][$_G['setting']['creditstransextra'][5]]['unit'].$_G['setting']['extcredits'][$_G['setting']['creditstransextra'][5]]['title'],
			);
			if($_G['gp_tradeaid']) {
				$feed['images'] = array(getforumimg($_G['gp_tradeaid']));
				$feed['image_links'] = array("forum.php?mod=viewthread&do=tradeinfo&tid=$_G[tid]&pid=$pid");
			}
		} elseif($special == 3 && $thread['authorid'] != $_G['uid']) {
			$feed['icon'] = 'reward';
			$feed['title_template'] = 'feed_reply_reward_title';
			$feed['title_data'] = array(
				'subject' => "<a href=\"forum.php?mod=viewthread&tid=$_G[tid]\">$thread[subject]</a>",
				'author' => "<a href=\"home.php?mod=space&uid=$thread[authorid]\">$thread[author]</a>"
			);
		} elseif($special == 5 && $thread['authorid'] != $_G['uid']) {
			$feed['icon'] = 'debate';
			if($stand == 1) {
				$feed['title_template'] = 'feed_thread_debatevote_title_1';
			} elseif($stand == 2) {
				$feed['title_template'] = 'feed_thread_debatevote_title_2';
			} else {
				$feed['title_template'] = 'feed_thread_debatevote_title_3';
			}
			$feed['title_data'] = array(
				'subject' => "<a href=\"forum.php?mod=viewthread&tid=$_G[tid]\">$thread[subject]</a>",
				'author' => "<a href=\"home.php?mod=space&uid=$thread[authorid]\">$thread[author]</a>"
			);
		} elseif($thread['authorid'] != $_G['uid']) {
			$post_url = "forum.php?mod=redirect&goto=findpost&pid=$pid&ptid=$_G[tid]";

			$feed['icon'] = 'post';
			$feed['title_template'] = !empty($thread['author']) ? 'feed_reply_title' : 'feed_reply_title_anonymous';
			$feed['title_data'] = array(
				'subject' => "<a href=\"$post_url\">$thread[subject]</a>",
				'author' => "<a href=\"home.php?mod=space&uid=$thread[authorid]\">$thread[author]</a>"
			);
			if(!empty($_G['forum_attachexist'])) {
				$firstaid = DB::result_first("SELECT aid FROM ".DB::table(getattachtablebytid($_G['tid']))." WHERE pid='$pid' AND dateline>'0' AND isimage='1' ORDER BY dateline LIMIT 1");
				if($firstaid) {
					$feed['images'] = array(getforumimg($firstaid));
					$feed['image_links'] = array($post_url);
				}
			}
		}
		$feed['title_data']['hash_data'] = "tid{$_G[tid]}";
		$feed['id'] = $pid;
		$feed['idtype'] = 'pid';
		if($feed['icon']) {
			postfeed($feed);
		}
	}

	$page = getstatus($thread['status'], 4) ? 1 : @ceil(($thread['special'] ? $thread['replies'] + 1 : $thread['replies'] + 2) / $_G['ppp']);

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
	        // 判断图片是否正常
	        $attArrTmp = getimagesize($dir . $picName);
	        if (!$attArrTmp) continue;
	        // 存储filesize、attachment、width
	        $attArr[$k]['filesize'] = filesize($dir . $picName);
	        $attArr[$k]['attachment'] = $dir2 . "/" . $dir3 . "/" . $picName;
	        $attArr[$k]['width'] = $attArrTmp[0];
	        unset($picName);
	    }

	    // 添加图片到forum_attachment(_n)
	    foreach ($attArr as $v2) {
	        $aid = DB::insert('forum_attachment', array(
                'tid' => $tid,
                'pid' => $pid,
                'uid' => $_G['uid'],
                'tableid' => $tid % 10
            ), true);
	        $table = getattachtablebyaid($aid);
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
	        DB::insert($table, $imgData);
	        $message .= "[attach]{$aid}[/attach]";
	        unset($aid, $imgData);
	    }
	    // 更新forum_post帖子`message`
	    $postUpdate =  array(
		    'message' => $message,
		    'attachment' => 2
	    );
	    DB::update('forum_post', $postUpdate, 'tid='.$tid);
	}

	// 成功
	unset($param['fid'], $param['from'], $param['sechash']);
	showmessage2('post_newreply_succeed', $param, 20000);
}


?>