<?php

/*
Filename:
	case.php

Usage:
	use browser

Descriptions:
	This webpage is used to start stock index calculation process.
*/

include_once("LIB_log.php");

include_once("stockIndicator.php");

function stockIDCheck($id)
{
	stockIndicators($id);
}

function nameCheck($name)
{
	$id = query_id_by_name($name);
	if ($id == '')
		return;

	stockIndicators($id);
}

date_default_timezone_set ("Asia/Taipei");
header('Content-Type: text/html; charset=utf-8');

if (isset($_SERVER['HTTP_USER_AGENT'])) echo "<pre>";
echo_v(NO_VERBOSE, "Start time: " . date("Y-m-d") . " " . date("h:i:sa"));
if (isset($_SERVER['HTTP_USER_AGENT'])) echo "</pre>";

$t1 = round(microtime(true) * 1000);

// 網頁頭
show_webpage_header('Case Study');

// 網頁內容
show_casestudy_updater(basename(__FILE__));

if(isset($_GET['stockid']))
	stockIDCheck($_GET['stockid']);
else if(isset($_GET['stockname']))
	nameCheck($_GET['stockname']);

// 網頁尾
show_webpage_tail();

if (isset($_SERVER['HTTP_USER_AGENT'])) echo "<pre>";
$t2 = round(microtime(true) * 1000);
echo_v(NO_VERBOSE, "End time: " . date("Y-m-d") . " " . date("h:i:sa"));
echo_v(NO_VERBOSE, "Duration: " . ($t2 - $t1) . "ms");
if (isset($_SERVER['HTTP_USER_AGENT'])) echo "</pre>";

?>