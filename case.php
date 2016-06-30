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
	stockIndicators($id, 'expert');
}

function nameCheck($name)
{
	$id = query_id_by_name($name);
	if ($id == '')
		return;

	stockIndicators($id, 'expert');
}

// 網頁頭
$t1 = show_webpage_header('Case Study');

// 網頁內容
show_casestudy_updater(basename(__FILE__));

// 網頁尾
show_webpage_tail($t1);

?>