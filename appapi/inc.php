<?php
define('DZ_APP_DEBUG',isset($_REQUEST['xdbug']) && $_REQUEST['xdbug'] == 'apidebug');

if(DZ_APP_DEBUG)
{
	ini_set('display_errors',1);
	error_reporting(E_ALL);
}

define('APPTYPEID', 4); 

require '../source/class/class_core.php';
require '../source/function/function_forum.php';

include_once libfile('function/core');
include_once '../source/function/function_home.php';

// $_G加载

$discuz = C::app(); 

$cachelist = array('plugin','setting','heats','globalstick','magic','userapp','usergroups', 'diytemplatenamehome','medals');

/*
 * 通过强制设置$_G['cookie']['auth']，得到用户信息
 * Discuz!原方法通过读取cookie内auth信息
 * 参数uid
 */
if (trim($_REQUEST['uid'])) {
    // 得到用户密码
    $uid = $_REQUEST['uid'];
    $config = $discuz->config['db'][1];  // 配置
    $link = @mysql_connect($config['dbhost'], $config['dbuser'], $config['dbpw']);
    mysql_select_db($config['dbname'], $link);
    mysql_set_charset($config['dbcharset']);
    $sql = "SELECT `username`,`password` FROM `{$config['tablepre']}common_member` WHERE `uid`={$uid}";
    $res = mysql_query($sql, $link);
    $row = mysql_fetch_assoc($res);
    if (empty($row)) {
        $sql2 = "SELECT `password` FROM `{$config['tablepre']}ucenter_members` WHERE `uid`={$uid}";
        $res2 = mysql_query($sql2, $link);
        $row2 = mysql_fetch_assoc($res2);
        if (empty($row2)) {
            showmessage2('用户不存在', '', 40000);
        } else {
            showmessage2('您没有发帖权限，详情请登录官网论坛', '', 44001);
        }
    } else {
        $password = $row['password'];
        $username = $row['username'];
    }
    

    // auth加密
    $auth = authcode("{$password}\t{$uid}", 'ENCODE');

    // 设置$_G['cookie']['auth']
    setglobal('auth', $auth, 'cookie');

} else {
    // 否则将读取cookie的值销掉，避免影响
    unset($_G['cookie']['auth']);
} 

// 加载信息到$_G
$discuz->cachelist = $cachelist;

// formhash值
if (trim($_REQUEST['uid']))
    $_GET['formhash'] = substr(md5(substr(time(), 0, -7).$username.$uid.$_G['authkey']), 8, 8);

$discuz->init();

// 设置$_G['forum']信息
if (!empty($_REQUEST['fid'])) {
    loadforum($_REQUEST['fid'], null);
} elseif (!empty($_REQUEST['tid'])) {
    loadforum(null, $_REQUEST['tid']);
}
set_rssauth();
runhooks();

// 输出json数据函数
function showmessage2($msg, $data, $code) {
    $output['msg']  = $msg;
    $output['data'] = $data ? $data : array();
    $output['code'] = $code;
	$output = charsetToUTF8($output);
    die(json_encode($output));
}

// 转格式为utf-8
function charsetToUTF8($mixed)
{
    if (is_array($mixed)) {
        foreach ($mixed as $k => $v) {
            if (is_array($v)) {
                $mixed[$k] = charsetToUTF8($v);
            } else {
                $encode = mb_detect_encoding($v, array('ASCII', 'UTF-8', 'GB2312', 'GBK', 'BIG5'));
                if ($encode == 'EUC-CN') {
                    $mixed[$k] = iconv('GBK', 'UTF-8', $v);
                }
            }
        }
    } else {
        $encode = mb_detect_encoding($mixed, array('ASCII', 'UTF-8', 'GB2312', 'GBK', 'BIG5'));
        if ($encode == 'EUC-CN') {
            $mixed = iconv('GBK', 'UTF-8', $mixed);
        }
    }
    return $mixed;
}

