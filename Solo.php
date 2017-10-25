<?php

require_once('inc/aventurien-solo-functions.php');

ini_set('display_errors', 1);
error_reporting(E_ALL);

$module = @$_GET['module'];
$title = @$_GET['title'];
$last_pid = @$_POST['pid'];
$passage = @$_POST['passage'];
$debug = @$_POST['debug'];

echo(aventurien_solo_display($module, $title, $last_pid, $passage, $debug));

?>