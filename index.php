﻿<?php

/*
Filename:
	index.php

Usage:
	use browser

Descriptions:
	This webpage is used to start stock evaluation process.
*/

include_once("LIB_log.php");

include_once("stockEvaluate.php");

function stockIDCheck($id)
{
	// 某日股價的排序, 從第幾名往後取n名, stockDayQuery.php
	$sii_kline = query_day_price_by_id_since('sii', Day1);
	$date = get_latest_sii_date_before(today(), $sii_kline);
	$id_day_prices = query_day_prices_sorted_on_date($date);

	// 某月月營收年增率的排序, 從第幾名往後取n名, stockMonthQuery.php
	$month = get_latest_scheduled_month(today());
	$id_monthly_revenue_yoys = query_month_revenue_yoys_sorted_on_month($month);

	stockEvaluateTest($id, Day1, $id_day_prices, $id_monthly_revenue_yoys, $sii_kline);
}

function nameCheck($name)
{
	$id = query_id_by_name($name);
	if ($id == '')
		return;

	// 某日股價的排序, 從第幾名往後取n名, stockDayQuery.php
	$sii_kline = query_day_price_by_id_since('sii', Day1);
	$date = get_latest_sii_date_before(today(), $sii_kline);
	$id_day_prices = query_day_prices_sorted_on_date($date);

	// 某月月營收年增率的排序, 從第幾名往後取n名, stockMonthQuery.php
	$month = get_latest_scheduled_month(today());
	$id_monthly_revenue_yoys = query_month_revenue_yoys_sorted_on_month($month);

	stockEvaluateTest($id, Day1, $id_day_prices, $id_monthly_revenue_yoys, $sii_kline);
}

function highpriceCheck($begin)
{
	// 某日股價的排序, 從第幾名往後取n名, stockDayQuery.php
	$sii_kline = query_day_price_by_id_since('sii', Day1);
	$date = get_latest_sii_date_before(today(), $sii_kline);
	$id_day_prices = query_day_prices_sorted_on_date($date);

	$top_prices = query_topN_ids_from_start($id_day_prices, $begin, 10);

	// 某月月營收年增率的排序, 從第幾名往後取n名, stockMonthQuery.php
	$month = get_latest_scheduled_month(today());
	$id_monthly_revenue_yoys = query_month_revenue_yoys_sorted_on_month($month);

	$ids = array();
	$ids = array_merge($ids, array_keys($top_prices));

	foreach($ids as $id)
		stockEvaluateTest($id, Day1, $id_day_prices, $id_monthly_revenue_yoys, $sii_kline);
}

function monthyoyCheck($begin)
{
	// 某日股價的排序, 從第幾名往後取n名, stockDayQuery.php
	$sii_kline = query_day_price_by_id_since('sii', Day1);
	$date = get_latest_sii_date_before(today(), $sii_kline);
	$id_day_prices = query_day_prices_sorted_on_date($date);

	// 某月月營收年增率的排序, 從第幾名往後取n名, stockMonthQuery.php
	$month = get_latest_scheduled_month(today());
	$id_monthly_revenue_yoys = query_month_revenue_yoys_sorted_on_month($month);

	$top_yoys = query_topN_ids_from_start($id_monthly_revenue_yoys, $begin, 10);

	$ids = array();
	$ids = array_merge($ids, array_keys($top_yoys));

	foreach($ids as $id)
		stockEvaluateTest($id, Day1, $id_day_prices, $id_monthly_revenue_yoys, $sii_kline);
}

date_default_timezone_set ("Asia/Taipei");
header('Content-Type: text/html; charset=utf-8');

if (isset($_SERVER['HTTP_USER_AGENT'])) echo "<pre>";
echo_v(NO_VERBOSE, "Start time: " . date("Y-m-d") . " " . date("h:i:sa"));
if (isset($_SERVER['HTTP_USER_AGENT'])) echo "</pre>";

$t1 = round(microtime(true) * 1000);

// 網頁頭
show_webpage_header('FrontEnd');

// 網頁內容
show_frontend_updater(basename(__FILE__));

if(isset($_GET['do']) && isset($_GET['begin']) && function_exists($_GET['do']))
	call_user_func($_GET['do'], $_GET['begin']);
else if(isset($_GET['do']) && function_exists($_GET['do']))
	call_user_func($_GET['do']);
else if(isset($_GET['stockid']))
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