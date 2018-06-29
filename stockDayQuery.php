<?php

/*
Filename:
	stockDayQuery.php

Usage:


Descriptions:
	This module exports functions to query stock prices and trade dates.

*/

include_once("LIB_http.php");
include_once("LIB_parse.php");
include_once("LIB_log.php");
include_once("LIB_mysql.php");

include_once("stockIDQuery.php");

/* Internal Function */

function query_day_price_by_id_y($id, $year, $dayprices)
{
	stopwatch_inter();
	$year_0 = (int)$year;
	$year_1 = $year_0+1;
	$kline = array();

	foreach ($dayprices as $date => $price) {
		$yr = (int)substr($date,0,4);
		if (($yr >= $year_0) AND ($yr < $year_1))
			$kline[$date] = $price[2]; // loCh
	}

	echo_v(LOG_VERBOSE, stopwatch_inter() . " ms to ". "query_day_price_by:" . $id . ":" . $year . "[" . __FUNCTION__ . "]");
	return $kline;
}

function query_day_price_by_id_since($id, $date)
{
	global $conn;
	$query = "SELECT date, close FROM daydata WHERE id = '" . $id . "' AND date >= '" . $date . "' ORDER BY date DESC;";
	stopwatch_inter();
	$result = mysqli_query($conn, $query) or die('MySQL query error');
	$kline = array();
	while($row = mysqli_fetch_array($result)){
		if (is_numeric($row[1]))
			$kline[$row[0]] = $row[1];
	}
	echo_v(LOG_VERBOSE, stopwatch_inter() . " ms to ". formatstr($query) . "[" . __FUNCTION__ . "]");
	return $kline;
}

function query_day_price_lochs_by_id_since($id, $date)
{
	global $conn;
	$query = "SELECT date, low, open, close, high, stock FROM daydata WHERE id = '" . $id . "' AND date >= '" . $date . "' ORDER BY date DESC;";
	$result = mysqli_query($conn, $query) or die('MySQL query error');
	$kline = array();
	while($row = mysqli_fetch_array($result)){
		$kline[$row[0]] = array($row[1], $row[2], $row[3], $row[4], $row[5]);
	}
	return $kline;
}

function get_latest_sii_date_before($date, $sii_kline)
{
	$dates = array_keys($sii_kline);

	if (strtotime($date) >= strtotime($dates[0]))
	{
		$ret_date = $dates[0];
	}
	else if (strtotime($date) < strtotime($dates[count($dates)-1]))
	{
		$ret_date = '2010-01-01';
	}
	else
	{
		while (false === array_search($date, $dates))
	    	$date = date("Y-m-d", (strtotime($date) - 86400));
		$ret_date = $date;
	}

	return $ret_date;
}

function query_day_prices_sorted_on_date($date)
{
	global $conn;
	$query = "SELECT id, close FROM daydata WHERE date = '" . $date . "' AND id != 'sii' AND id != 'otc' ORDER BY close DESC";
	stopwatch_inter();
	$result = mysqli_query($conn, $query) or die('MySQL query error');
	$kline = array();
	while( $row = mysqli_fetch_array( $result)){
		$kline[$row[0]] = $row[1];
	}
	echo_v(LOG_VERBOSE, stopwatch_inter() . " ms to ". formatstr($query) . "[" . __FUNCTION__ . "]");
	return $kline;
}

/******************** Test Utilities ************************/

function check_wotd_price_sii($id)
{
	echo_v(LOG_VERBOSE, "**** check_wotd_price_sii *********************************************");

	$prices = query_day_price_by_id_since($id, '2010-01-01');
	$diffs = array();

	$previous_key = "";
	$previous_value = 0.0;
	foreach($prices as $key=>$value)
	{
		if ($previous_key != "")
		{
			$diffs[$previous_key] = round($previous_value - $value,2);
		}
		$previous_key = $key;
		$previous_value = $value;
	}
	$totals = array(7);
	$incres = array(7);
	for ($ii=0;$ii<7;$ii++)
	{
		$totals[$ii] = $ii;
		$incres[$ii] = $ii;
	}
	foreach($diffs as $key=>$value)
	{
		$totals[date("w" , strtotime($key))]++;
		if ($value > 0)
			$incres[date("w" , strtotime($key))]++;
		//echo_v(LOG_VERBOSE, date("w" , strtotime($key)) . "[" . $key . "] => " . $value);
	}
	foreach($totals as $total)
	{
		echo_v(LOG_VERBOSE, $total);
	}
	foreach($incres as $incre)
	{
		echo_v(LOG_VERBOSE, $incre);
	}
}

function check_wotd_price_otc($id)
{
	echo_v(LOG_VERBOSE, "**** check_wotd_price_sii *********************************************");

	$prices = query_day_price_by_id_since('otc', '2010-01-01');
	$diffs = array();

	$previous_key = "";
	$previous_value = 0.0;
	foreach($prices as $key=>$value)
	{
		if ($previous_key != "")
		{
			$diffs[$previous_key] = round($previous_value - $value,2);
		}
		$previous_key = $key;
		$previous_value = $value;
	}
	$totals = array(7);
	$incres = array(7);
	for ($ii=0;$ii<7;$ii++)
	{
		$totals[$ii] = $ii;
		$incres[$ii] = $ii;
	}
	foreach($diffs as $key=>$value)
	{
		$totals[date("w" , strtotime($key))]++;
		if ($value > 0)
			$incres[date("w" , strtotime($key))]++;
		//echo_v(LOG_VERBOSE, date("w" , strtotime($key)) . "[" . $key . "] => " . $value);
	}
	foreach($totals as $total)
	{
		echo_v(LOG_VERBOSE, $total);
	}
	foreach($incres as $incre)
	{
		echo_v(LOG_VERBOSE, $incre);
	}
}

/******************** Entry Function ************************/

function stockDayQueryTest()
{
	echo_v(LOG_VERBOSE, "");
	echo_v(LOG_VERBOSE, "**********************************************************************");
	echo_v(LOG_VERBOSE, "**** stockDayQueryTest ***********************************************");
	echo_v(LOG_VERBOSE, "**********************************************************************");
	echo_v(LOG_VERBOSE, "");

	// 算星期幾比較容易漲
	//check_wotd_price_sii();
	//check_wotd_price_otc();

	// 2330: sii / 8086: otc
	//$id_array = array('2330', '8086');
	$id_array = array();

	foreach($id_array as $id)
	{
		$stock = query_id_data_by_id($id);

		/* leave if stock not found */
		if ($stock == null)
		{
			echo_v(ERROR_VERBOSE, "[stockDayQueryTest] stock id " . $id . " is not found!");
			return null;
		}
		echo_v(DEBUG_VERBOSE, "[stockDayQueryTest] stock id " . $id . " is found!");

		$id_kline = query_day_price_by_id_since($id, date('Y') . '-01-01');
		$dayprices = query_day_price_by_id_y($id, date('Y'), $id_kline);
		print_r($dayprices);
	}

	// 3126(信億) 從 2015/2/24 之後停止交易至今
	$id_kline = query_day_price_by_id_since('3126', '2015-01-01');
	$dayprices = query_day_price_by_id_y('3126', '2015', $id_kline);
	print_r($dayprices);
}

?>