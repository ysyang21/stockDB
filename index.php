<?php

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

// 網頁頭
$t1 = show_webpage_header('FrontEnd');

// 網頁內容
show_frontend_updater(basename(__FILE__));

// 網頁尾
show_webpage_tail($t1);

?>