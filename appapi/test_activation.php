<?php
require 'inc.php';
require libfile('function/member');
require libfile('function/misc');
require 'lib/class_member.php';
if($_G['setting']['version'] < 'X3.1')
{
    require libfile('function/seccode');
}
runhooks();

define('FORMHASH', $_G['formhash']);
$ctl_obj = new register_ctl();
$ctl_obj->setting = $_G['setting'];
$ctl_obj->on_activation();

header("Content-Type: text/html; charset=utf-8");
