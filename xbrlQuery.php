<?php

/*
Filename:
	xbrlQuery.php

Usage:

Descriptions:
	Exports functions to query xbrldata database.
*/

include_once("LIB_log.php");
include_once("LIB_mysql.php");

include_once("stockDayQuery.php");

$id_seasonly_xbrl = [];

function prepare_id_seasonly_xbrl($id)
{
	global $conn;
	global $id_seasonly_xbrl;

	$query = "SELECT season, revenue, stock, eps, eps2, publish FROM xbrldata WHERE id = " . $id;
	stopwatch_inter();
	$result = mysqli_query($conn, $query) or die('MySQL query error');
	$id_seasonly_xbrl = [];
	while($row = mysqli_fetch_array($result)){
		$id_seasonly_xbrl[$row[0]] = array((int)$row[1], (int)$row[2], (float)$row[3], (float)$row[4], $row[5]);
	}

	echo_v(LOG_VERBOSE, stopwatch_inter() . " ms to ". formatstr($query) . "[" . __FUNCTION__ . "]");
}

function query_xbrl_inventory($id)
{
	global $conn;
	$query = "SELECT season, inventory FROM xbrldata WHERE id = " . $id;
	stopwatch_inter();
	$result = mysqli_query($conn, $query) or die('MySQL query error');
	$inventory = [];
	while($row = mysqli_fetch_array($result)){
		$inventory[$row[0]] = $row[1];
	}

	echo_v(LOG_VERBOSE, stopwatch_inter() . " ms to ". formatstr($query) . "[" . __FUNCTION__ . "]");
	return $inventory;
}

function query_xbrl_income_statement($id)
{
	global $conn;
	$query = "SELECT season, revenue, income, eps, eps2 FROM xbrldata WHERE id = " . $id;
	stopwatch_inter();
	$result = mysqli_query($conn, $query) or die('MySQL query error');
	$inventory = [];
	while($row = mysqli_fetch_array($result)){
		$inventory[$row[0]] = $row[1];
	}

	echo_v(LOG_VERBOSE, stopwatch_inter() . " ms to ". formatstr($query) . "[" . __FUNCTION__ . "]");
	return $inventory;
}

function query_yearly_revenue($id, $year)
{
	global $id_seasonly_xbrl;
	$revenue = -1;
	$season = $year . "04";
	stopwatch_inter();

	if (array_key_exists($season, $id_seasonly_xbrl))
		$revenue = $id_seasonly_xbrl[$season][0];

	echo_v(LOG_VERBOSE, '<span style="color:#0000FF">' . stopwatch_inter() . " ms to " . "query_yearly_revenue_by:" . $id . ":" . $season . "[" . __FUNCTION__ . "]" . '</span>');
	if($revenue == -1)
		echo_v(DEBUG_VERBOSE, "[query_yearly_revenue] id = " . $id . " has no yearly revenue data on " . $year);
	return $revenue;
}

function query_seasonly_revenue_plain($id, $season)
{
	global $id_seasonly_xbrl;
	$revenue = -1;

	stopwatch_inter();

	if (array_key_exists($season, $id_seasonly_xbrl))
		$revenue = $id_seasonly_xbrl[$season][0];

	echo_v(LOG_VERBOSE, stopwatch_inter() . " ms to " . "query_seasonly_revenue_by:" . $id . ":" . $season . "[" . __FUNCTION__ . "]");
	if($revenue == -1)
		echo_v(DEBUG_VERBOSE, "[query_seasonly_revenue] id = " . $id . " has no seasonly revenue data on " . $season);
	return $revenue;
}

function query_seasonly_revenue($id, $season)
{
	$year = substr($season, 0, 4);
	$quarter = substr($season, 4, 2);
	if ((int)$year > 2012)
	{
		return query_seasonly_revenue_plain($id, $season);
	}
	else if ((int)$quarter == 1 or (int)$quarter == 4)
	{
		return query_seasonly_revenue_plain($id, $season);
	}
	else
	{
		$revenue1 = query_seasonly_revenue_plain($id, $season);
		$revenue2 = -1;
		if ((int)$quarter == 3)
			$revenue2 = query_seasonly_revenue_plain($id, $year . '02');
		else if ((int)$quarter == 2)
			$revenue2 = query_seasonly_revenue_plain($id, $year . '01');
		
		if ($revenue1 != -1 and $revenue2 != -1)
			return ($revenue1 - $revenue2);
		else if ($revenue1 != -1 and $revenue2 == -1)
			return $revenue1;
		else
			return (-1);
	}
}

function query_seasonly_publish($id, $season)
{
	global $id_seasonly_xbrl;

	$publish = "";
	stopwatch_inter();

	if (array_key_exists($season, $id_seasonly_xbrl))
		$publish = $id_seasonly_xbrl[$season][4];

	echo_v(LOG_VERBOSE, stopwatch_inter() . " ms to ". "query_seasonly_publish_by:" . $id . ":" . $season . "[" . __FUNCTION__ . "]");
	if($publish == "")
		echo_v(DEBUG_VERBOSE, "[query_seasonly_publish] id = " . $id . " has no seasonly publish data on " . $season);
	return $publish;
}

function query_year_stock($id, $year)
{
	global $id_seasonly_xbrl;

	$stock = -1;
	$year_season = $year . "04";
	stopwatch_inter();

	if (array_key_exists($year_season, $id_seasonly_xbrl))
		$stock = $id_seasonly_xbrl[$year_season][1];

	echo_v(LOG_VERBOSE, stopwatch_inter() . " ms to ". "query_year_stock_by:" . $id . ":" . $year_season . "[" . __FUNCTION__ . "]");
	if($stock == -1)
		echo_v(ERROR_VERBOSE, "[query_year_stock] id = " . $id . " has no stock data on " . $year);
	return $stock;
}

function query_year_eps($id, $year, $season = "04")
{
	global $id_seasonly_xbrl;

	$eps = -1;
	$year_season = $year . $season;
	stopwatch_inter();

	if (array_key_exists($year_season, $id_seasonly_xbrl))
		$eps = $id_seasonly_xbrl[$year_season][2];

	echo_v(LOG_VERBOSE, stopwatch_inter() . " ms to " . "query_year_eps_by:" . $id . ":" . $year_season . "[" . __FUNCTION__ . "]");
	if($eps == -1)
		echo_v(ERROR_VERBOSE, "[query_year_eps] id = " . $id . " has no yearly eps data on " . $year);
	return $eps;
}

function query_year_eps2($id, $year)
{
	global $id_seasonly_xbrl;

	$eps2 = -1;
	$year_season = $year . "04";
	stopwatch_inter();

	if (array_key_exists($year_season, $id_seasonly_xbrl))
		$eps2 = $id_seasonly_xbrl[$year_season][3];

	echo_v(LOG_VERBOSE, stopwatch_inter() . " ms to " . "query_year_eps2_by:" . $id . ":" . $year_season . "[" . __FUNCTION__ . "]");
	if($eps2 == -1)
		echo_v(ERROR_VERBOSE, "[query_year_eps2] id = " . $id . " has no yearly eps2 data on " . $year);
	return $eps2;
}

function query_est_profitax_over_revenue($id, $date)
{
	global $conn;
	$ratio = -1;

	$year = substr($date, 0, 4);
	
	$season_list = array();
	$year_1 = (string)((int)$year - 1);
	$year_2 = (string)((int)$year - 2);

	//Q1-5/15, Q2-8/14, Q3-11/14, Q4+Y-3/31

	$dayOfYear = date( 'z', strtotime($date));

	if ($dayOfYear >= date( 'z', strtotime($year . "/11/14")))
	{
		array_push($season_list, $year . "03", $year . "02", $year . "01", $year_1 . "04");
	}
	else if ($dayOfYear >= date( 'z', strtotime($year . "/10/01")))
	{
		$pubdate = query_seasonly_publish($id, $year . "03");
		if ("" != $pubdate)
		{
			if ($dayOfYear >= date( 'z', strtotime($pubdate)))
				array_push($season_list, $year . "03", $year . "02", $year . "01", $year_1 . "04");
			else
				array_push($season_list, $year . "02", $year . "01", $year_1 . "04", $year_1 . "03");
		}
		else
		{
			array_push($season_list, $year . "02", $year . "01", $year_1 . "04", $year_1 . "03");
			//echo_v (ERROR_VERBOSE, "[query_est_profitax_over_revenue] no xbrl data for id " . $id . " on season " . $year . "03");
		}
	}
	else if ($dayOfYear >= date( 'z', strtotime($year . "/08/14")))
	{
		array_push($season_list, $year . "02", $year . "01", $year_1 . "04", $year_1 . "03");
	}
	else if ($dayOfYear >= date( 'z', strtotime($year . "/07/01")))
	{
		$pubdate = query_seasonly_publish($id, $year . "02");
		if ("" != $pubdate)
		{
			if ($dayOfYear >= date( 'z', strtotime($pubdate)))
				array_push($season_list, $year . "02", $year . "01", $year_1 . "04", $year_1 . "03");
			else
				array_push($season_list, $year . "01", $year_1 . "04", $year_1 . "03", $year_1 . "02");
		}
		else
		{
			array_push($season_list, $year . "01", $year_1 . "04", $year_1 . "03", $year_1 . "02");
			//echo_v (ERROR_VERBOSE, "[query_est_profitax_over_revenue] no xbrl data for id " . $id . " on season " . $year . "02");
		}
	}
	else if ($dayOfYear >= date( 'z', strtotime($year . "/05/15")))
	{
		array_push($season_list, $year . "01", $year_1 . "04", $year_1 . "03", $year_1 . "02");
	}
	else if ($dayOfYear >= date( 'z', strtotime($year . "/04/01")))
	{
		$pubdate = query_seasonly_publish($id, $year . "01");
		if ("" != $pubdate)
		{
			if ($dayOfYear >= date( 'z', strtotime($pubdate)))
				array_push($season_list, $year . "01", $year_1 . "04", $year_1 . "03", $year_1 . "02");
			else
				array_push($season_list, $year_1 . "04", $year_1 . "03", $year_1 . "02", $year_1 . "01");
		}
		else
		{
			array_push($season_list, $year_1 . "04", $year_1 . "03", $year_1 . "02", $year_1 . "01");
			//echo_v (ERROR_VERBOSE, "[query_est_profitax_over_revenue] no xbrl data for id " . $id . " on season " . $year . "01");
		}
	}
	else if ($dayOfYear >= date( 'z', strtotime($year . "/01/01")))
	{
		$pubdate = query_seasonly_publish($id, $year_1 . "04");
		if ("" != $pubdate)
		{
			if ($dayOfYear >= date( 'z', strtotime($pubdate)))
				array_push($season_list, $year_1 . "04", $year_1 . "03", $year_1 . "02", $year_1 . "01");
			else
				array_push($season_list, $year_1 . "03", $year_1 . "02", $year_1 . "01", $year_2 . "04");
		}
		else
		{
			array_push($season_list, $year_1 . "03", $year_1 . "02", $year_1 . "01", $year_2 . "04");
			//echo_v (ERROR_VERBOSE, "[query_est_profitax_over_revenue] no xbrl data for id " . $id . " on season " . $year_1 . "04");
		}
	}

	$query = "SELECT AVG(nopat/revenue) FROM xbrldata WHERE id = " . $id . " AND (season = " . $season_list[0] .
		" OR season = " . $season_list[1] .
		" OR season = " . $season_list[2] .
		" OR season = " . $season_list[3] . ") " .
		"ORDER BY season DESC";
	stopwatch_inter();
	$result = mysqli_query($conn, $query) or die('MySQL query error');
	while($row = mysqli_fetch_array($result)){
		$ratio = $row[0];
	}
	echo_v(LOG_VERBOSE, stopwatch_inter() . " ms to ". formatstr($query) . "[" . __FUNCTION__ . "]");
	return $ratio;
}

// if given xbrl is published in given date
function query_xbrl_on_date($id, $year, $season, $date)
{
	global $conn;
	$publish = "";
	$query = "SELECT publish FROM xbrldata WHERE id = " . $id . " AND season = " . $year . $season . " AND report = 'ci-cr'";
	stopwatch_inter();
	$result = mysqli_query($conn, $query) or die('MySQL query error');
	while($row = mysqli_fetch_array($result)){
		$publish = $row[0];
	}
	echo_v(LOG_VERBOSE, stopwatch_inter() . " ms to ". formatstr($query) . "[" . __FUNCTION__ . "]");

	if ($publish != "")
	{
		if (strtotime($date) > strtotime($publish))
			return true;
		else
			return false;
	}
	else
	{
		$publish = "";
		$query = "SELECT publish FROM xbrldata WHERE id = " . $id . " AND season = " . $year . $season . " AND report = 'ci-ir'";
		stopwatch_inter();
		$result = mysqli_query($conn, $query) or die('MySQL query error');
		while($row = mysqli_fetch_array($result)){
			$publish = $row[0];
		}
		echo_v(LOG_VERBOSE, stopwatch_inter() . " ms to ". formatstr($query) . "[" . __FUNCTION__ . "]");

		if ($publish != "")
		{
			if (strtotime($date) > strtotime($publish))
				return true;
			else
				return false;
		}
		else
		{
			return false;
		}
	}
}

// get all dates either new monData or new xbrlData is coming
function query_evaluate_dates_since($id, $since_date, $sii_kline)
{
	global $conn;
	$date = $since_date;
	$evaluate_dates = array();

	$query = "SELECT publish FROM xbrldata WHERE id = " . $id;
	stopwatch_inter();
	$result = mysqli_query($conn, $query) or die('MySQL query error');

	while($row = mysqli_fetch_array($result)){
		$publish = $row[0];
		if (strtotime($date) <= strtotime($publish) and strtotime(today()) >= strtotime($publish))
			array_push($evaluate_dates, $publish);
	}
	echo_v(LOG_VERBOSE, stopwatch_inter() . " ms to ". formatstr($query) . "[" . __FUNCTION__ . "]");

	// iterating from since_date to today
	while (strtotime($date) < strtotime(today()))
	{
		if ("-10" == substr($date, strlen($date)-3, 3))
		{
			$adjusted_date = get_latest_sii_date_before($date, $sii_kline);
			array_push($evaluate_dates, $adjusted_date);
		}

		$date = date("Y-m-d", (strtotime($date) + 86400));
	}
	$date=get_latest_sii_date_before(today(), $sii_kline);
	array_push($evaluate_dates, $date);
	$evaluate_dates = array_unique($evaluate_dates);
	sort($evaluate_dates);
	return $evaluate_dates;
}

function get_latest_xbrldata_season($id)
{
	global $season_enum;

	// 輸入日期, 按照財報死線推算肯定已經在xbrldata的最新財報季度
	$latest_scheduled_season = get_latest_scheduled_season(today());

	// 例如說今天是 8/13, 雖然還沒到公布第Q2財報的死線, 但是我們推論它很可能已經被更新到xbrldata當中了
	$might_have_been_published_season = $season_enum[array_search($latest_scheduled_season, $season_enum) - 1];

	$query = "SELECT season FROM xbrldata WHERE id = " . $id . " AND season = " . $might_have_been_published_season;

	$latest_season = (exist_by($query)?$might_have_been_published_season:$latest_scheduled_season);

	return $latest_season;
}

class xbrlData
{
	public $season = "";	// 季度

	public $arn = 0;				// 應收帳款淨額-A5
	public $arnr = 0;				// 應收帳款關係人淨額-A6
	public $inventory = 0;			// 存貨-A9
	public $othercurrentassets = 0;	// 其他流動資產-A13
	public $currentassets = 0;		// 流動資產-A14
	public $fixedassets = 0;		// 固定資產-A18
	public $assets = 0;				// 資產-A25
	public $currentliabilities = 0;	// 流動負債-A41
	public $othernoncurrentliabilities = 0;	// 其他非流動負債-A23
	public $noncurrentliabilities = 0;	// 非流動負債-A24
	public $liabilities = 0;		// 負債-A54
	public $stock = 0;				// 股本-A56
	public $noncontrol = 0;			// 少數股權-A73
	public $equity = 0;				// 淨值-A74

	public $revenue = 0;	// 營業收入-B1
	public $costs = 0;		// 營業成本-B2
	public $profit = 0;		// 營業毛利-B5
	public $income = 0;		// 營業利益-B11
	public $nopbt = 0;		// 稅前淨利-B18
	public $nopat = 0;		// 稅後淨利-B21
	public $nopatc = 0;		// 稅後淨利業主-B29 or B21
	public $eps = 0;		// 每股盈餘-B33
	public $eps2 = 0;		// 稀釋每股盈餘-B34

	public $interestexpense = 0; // 利息費用-C5
	public $cashoa = 0;		// 營運活動現金流量-C34
	public $cashia = 0;		// 投資活動現金流量-C49

	public $publish = "";	// 財報公佈日
}

function load_seasonly_xbrl($id, $num)
{
	global $season_enum;
	global $conn;

	$latest_season = get_latest_xbrldata_season($id);

	$start = array_search($latest_season, $season_enum);
	// # of seasons in current year + 8 seasons + $num seasons,
	// which is for the need of calculating finantial indexes
	$len = 8 + (int)substr($latest_season, 4, 2) + $num;

	// $start 指向 xbrldata 目前有資料的最新季報
	// $len 為在這個routine當中要load的季報筆數, 目前設定為今年到現在的財報跟過去八季的財報

	$season_list = array();
	for ($ii=$start;$ii<min(count($season_enum),$start+$len);$ii++)
		array_push($season_list, $season_enum[$ii]);

	$xbrls = array();

	// get 'current' xbrl for assigned year x season
	$sql = "SELECT season, " .
			"arn, arnr, inventory, othercurrentassets, currentassets, fixedassets, assets, " .
			"currentliabilities, othernoncurrentliabilities, noncurrentliabilities, liabilities, stock, noncontrol, equity, " .
			"revenue, costs, profit, income, nopbt, nopat, nopatc, eps, eps2, " .
			"interestexpense, cashoa, cashia, " .
			"publish " .
			"FROM xbrldata WHERE id = " . $id . " AND period = 'current' AND (season = " . $season_list[0];

	for ($ii=1;$ii<$len;$ii++)
		$sql = $sql . " OR season = " . $season_list[$ii];

	$sql = $sql . ") ORDER BY season DESC";

	$result = mysqli_query($conn, $sql) or die('MySQL query error' . $sql);

	$jj = 0;
	while($row = mysqli_fetch_array($result)){
		$season = $row[$jj++];
		$xbrl = array();
		$xbrl['current'] = new xbrlData();
		$xbrl['corresp'] = new xbrlData();
		$xbrl['current']->season = $season;

		$xbrl['current']->arn = $row[$jj++];
		$xbrl['current']->arnr = $row[$jj++];
		$xbrl['current']->inventory = $row[$jj++];
		$xbrl['current']->othercurrentassets = $row[$jj++];
		$xbrl['current']->currentassets = $row[$jj++];
		$xbrl['current']->fixedassets = $row[$jj++];
		$xbrl['current']->assets = $row[$jj++];
		$xbrl['current']->currentliabilities = $row[$jj++];
		$xbrl['current']->othernoncurrentliabilities = $row[$jj++];
		$xbrl['current']->noncurrentliabilities = $row[$jj++];
		$xbrl['current']->liabilities = $row[$jj++];
		$xbrl['current']->stock = $row[$jj++];
		$xbrl['current']->noncontrol = $row[$jj++];
		$xbrl['current']->equity = $row[$jj++];

		$xbrl['current']->revenue = $row[$jj++];
		$xbrl['current']->costs = $row[$jj++];
		$xbrl['current']->profit = $row[$jj++];
		$xbrl['current']->income = $row[$jj++];
		$xbrl['current']->nopbt = $row[$jj++];
		$xbrl['current']->nopat = $row[$jj++];
		$xbrl['current']->nopatc = $row[$jj++];
		$xbrl['current']->eps = $row[$jj++];
		$xbrl['current']->eps2 = $row[$jj++];

		$xbrl['current']->interestexpense = $row[$jj++];
		$xbrl['current']->cashoa = $row[$jj++];
		$xbrl['current']->cashia = $row[$jj++];

		$xbrl['current']->publish = $row[$jj++];

		$jj = 0;
		array_push($xbrls, $xbrl);
		//$xbrls[$season] = $xbrl;
	}

	// get 'corresponding' xbrl for assigned year x season
	$sql = "SELECT season, " .
			"arn, arnr, inventory, othercurrentassets, currentassets, fixedassets, assets, " .
			"currentliabilities, othernoncurrentliabilities, noncurrentliabilities, liabilities, stock, noncontrol, equity, " .
			"revenue, costs, profit, income, nopbt, nopat, nopatc, eps, eps2, " .
			"interestexpense, cashoa, cashia, " .
			"publish " .
			"FROM xbrldata WHERE id = " . $id . " AND period = 'corresp' AND (season = " . $season_list[0];

	for ($ii=1;$ii<$len;$ii++)
		$sql = $sql . " OR season = " . $season_list[$ii];

	$sql = $sql . ") ORDER BY season DESC";

	$result = mysqli_query($conn, $sql) or die('MySQL query error' . $sql);

	$jj = 0;
	$ii = 0;
	while($row = mysqli_fetch_array($result)){
		$season = $row[$jj++];
		//$xbrl = $xbrls[$season];
		$xbrl = $xbrls[$ii++];
		$xbrl['corresp']->season = $season;

		$xbrl['corresp']->arn = $row[$jj++];
		$xbrl['corresp']->arnr = $row[$jj++];
		$xbrl['corresp']->inventory = $row[$jj++];
		$xbrl['corresp']->othercurrentassets = $row[$jj++];
		$xbrl['corresp']->currentassets = $row[$jj++];
		$xbrl['corresp']->fixedassets = $row[$jj++];
		$xbrl['corresp']->assets = $row[$jj++];
		$xbrl['corresp']->currentliabilities = $row[$jj++];
		$xbrl['corresp']->othernoncurrentliabilities = $row[$jj++];
		$xbrl['corresp']->noncurrentliabilities = $row[$jj++];
		$xbrl['corresp']->liabilities = $row[$jj++];
		$xbrl['corresp']->stock = $row[$jj++];
		$xbrl['corresp']->noncontrol = $row[$jj++];
		$xbrl['corresp']->equity = $row[$jj++];

		$xbrl['corresp']->revenue = $row[$jj++];
		$xbrl['corresp']->costs = $row[$jj++];
		$xbrl['corresp']->profit = $row[$jj++];
		$xbrl['corresp']->income = $row[$jj++];
		$xbrl['corresp']->nopbt = $row[$jj++];
		$xbrl['corresp']->nopat = $row[$jj++];
		$xbrl['corresp']->nopatc = $row[$jj++];
		$xbrl['corresp']->eps = $row[$jj++];
		$xbrl['corresp']->eps2 = $row[$jj++];

		$xbrl['corresp']->interestexpense = $row[$jj++];
		$xbrl['corresp']->cashoa = $row[$jj++];
		$xbrl['corresp']->cashia = $row[$jj++];

		$xbrl['corresp']->publish = $row[$jj++];

		$jj = 0;
	}
	
	$count = count($xbrls);
	for ($ii=0;$ii<$count;$ii++)
	{
		$season = $xbrls[$ii]['current']->season;

		if ((int)substr($season, 0, 4) >= 2013) // ifrs
		{
			if ((substr($season, 4, 2) == '04' and $count - $ii >1) or
				(substr($season, 4, 2) == '03' and $count - $ii >1) or
				(substr($season, 4, 2) == '02' and $count - $ii >1))
			{
				$xbrls[$ii]['current']->interestexpense -= $xbrls[$ii+1]['current']->interestexpense;
				$xbrls[$ii]['corresp']->interestexpense -= $xbrls[$ii+1]['corresp']->interestexpense;
			}

			if (substr($season, 4, 2) == '04' and $count - $ii >=4)
			{
				$xbrls[$ii]['current']->revenue -= $xbrls[$ii+1]['current']->revenue;
				$xbrls[$ii]['current']->revenue -= $xbrls[$ii+2]['current']->revenue;
				$xbrls[$ii]['current']->revenue -= $xbrls[$ii+3]['current']->revenue;
				$xbrls[$ii]['current']->costs -= $xbrls[$ii+1]['current']->costs;
				$xbrls[$ii]['current']->costs -= $xbrls[$ii+2]['current']->costs;
				$xbrls[$ii]['current']->costs -= $xbrls[$ii+3]['current']->costs;
				$xbrls[$ii]['current']->profit -= $xbrls[$ii+1]['current']->profit;
				$xbrls[$ii]['current']->profit -= $xbrls[$ii+2]['current']->profit;
				$xbrls[$ii]['current']->profit -= $xbrls[$ii+3]['current']->profit;
				$xbrls[$ii]['current']->income -= $xbrls[$ii+1]['current']->income;
				$xbrls[$ii]['current']->income -= $xbrls[$ii+2]['current']->income;
				$xbrls[$ii]['current']->income -= $xbrls[$ii+3]['current']->income;
				$xbrls[$ii]['current']->nopbt -= $xbrls[$ii+1]['current']->nopbt;
				$xbrls[$ii]['current']->nopbt -= $xbrls[$ii+2]['current']->nopbt;
				$xbrls[$ii]['current']->nopbt -= $xbrls[$ii+3]['current']->nopbt;
				$xbrls[$ii]['current']->nopat -= $xbrls[$ii+1]['current']->nopat;
				$xbrls[$ii]['current']->nopat -= $xbrls[$ii+2]['current']->nopat;
				$xbrls[$ii]['current']->nopat -= $xbrls[$ii+3]['current']->nopat;
				$xbrls[$ii]['current']->nopatc -= $xbrls[$ii+1]['current']->nopatc;
				$xbrls[$ii]['current']->nopatc -= $xbrls[$ii+2]['current']->nopatc;
				$xbrls[$ii]['current']->nopatc -= $xbrls[$ii+3]['current']->nopatc;
				$xbrls[$ii]['current']->eps -= $xbrls[$ii+1]['current']->eps;
				$xbrls[$ii]['current']->eps -= $xbrls[$ii+2]['current']->eps;
				$xbrls[$ii]['current']->eps -= $xbrls[$ii+3]['current']->eps;
				$xbrls[$ii]['current']->eps2 -= $xbrls[$ii+1]['current']->eps2;
				$xbrls[$ii]['current']->eps2 -= $xbrls[$ii+2]['current']->eps2;
				$xbrls[$ii]['current']->eps2 -= $xbrls[$ii+3]['current']->eps2;

				$xbrls[$ii]['corresp']->revenue -= $xbrls[$ii+1]['corresp']->revenue;
				$xbrls[$ii]['corresp']->revenue -= $xbrls[$ii+2]['corresp']->revenue;
				$xbrls[$ii]['corresp']->revenue -= $xbrls[$ii+3]['corresp']->revenue;
				$xbrls[$ii]['corresp']->costs -= $xbrls[$ii+1]['corresp']->costs;
				$xbrls[$ii]['corresp']->costs -= $xbrls[$ii+2]['corresp']->costs;
				$xbrls[$ii]['corresp']->costs -= $xbrls[$ii+3]['corresp']->costs;
				$xbrls[$ii]['corresp']->profit -= $xbrls[$ii+1]['corresp']->profit;
				$xbrls[$ii]['corresp']->profit -= $xbrls[$ii+2]['corresp']->profit;
				$xbrls[$ii]['corresp']->profit -= $xbrls[$ii+3]['corresp']->profit;
				$xbrls[$ii]['corresp']->income -= $xbrls[$ii+1]['corresp']->income;
				$xbrls[$ii]['corresp']->income -= $xbrls[$ii+2]['corresp']->income;
				$xbrls[$ii]['corresp']->income -= $xbrls[$ii+3]['corresp']->income;
				$xbrls[$ii]['corresp']->nopbt -= $xbrls[$ii+1]['corresp']->nopbt;
				$xbrls[$ii]['corresp']->nopbt -= $xbrls[$ii+2]['corresp']->nopbt;
				$xbrls[$ii]['corresp']->nopbt -= $xbrls[$ii+3]['corresp']->nopbt;
				$xbrls[$ii]['corresp']->nopat -= $xbrls[$ii+1]['corresp']->nopat;
				$xbrls[$ii]['corresp']->nopat -= $xbrls[$ii+2]['corresp']->nopat;
				$xbrls[$ii]['corresp']->nopat -= $xbrls[$ii+3]['corresp']->nopat;
				$xbrls[$ii]['corresp']->nopatc -= $xbrls[$ii+1]['corresp']->nopatc;
				$xbrls[$ii]['corresp']->nopatc -= $xbrls[$ii+2]['corresp']->nopatc;
				$xbrls[$ii]['corresp']->nopatc -= $xbrls[$ii+3]['corresp']->nopatc;
				$xbrls[$ii]['corresp']->eps -= $xbrls[$ii+1]['corresp']->eps;
				$xbrls[$ii]['corresp']->eps -= $xbrls[$ii+2]['corresp']->eps;
				$xbrls[$ii]['corresp']->eps -= $xbrls[$ii+3]['corresp']->eps;
				$xbrls[$ii]['corresp']->eps2 -= $xbrls[$ii+1]['corresp']->eps2;
				$xbrls[$ii]['corresp']->eps2 -= $xbrls[$ii+2]['corresp']->eps2;
				$xbrls[$ii]['corresp']->eps2 -= $xbrls[$ii+3]['corresp']->eps2;
			}
		}
		else // gaap
		{
			if ((substr($season, 4, 2) == '04' and $count - $ii >1) or
				(substr($season, 4, 2) == '03' and $count - $ii >1) or
				(substr($season, 4, 2) == '02' and $count - $ii >1))
			{
				$xbrls[$ii]['current']->revenue -= $xbrls[$ii+1]['current']->revenue;
				$xbrls[$ii]['current']->costs -= $xbrls[$ii+1]['current']->costs;
				$xbrls[$ii]['current']->profit -= $xbrls[$ii+1]['current']->profit;
				$xbrls[$ii]['current']->income -= $xbrls[$ii+1]['current']->income;
				$xbrls[$ii]['current']->interestexpense -= $xbrls[$ii+1]['current']->interestexpense;
				$xbrls[$ii]['current']->nopbt -= $xbrls[$ii+1]['current']->nopbt;
				$xbrls[$ii]['current']->nopat -= $xbrls[$ii+1]['current']->nopat;
				$xbrls[$ii]['current']->nopatc -= $xbrls[$ii+1]['current']->nopatc;
				$xbrls[$ii]['current']->eps -= $xbrls[$ii+1]['current']->eps;
				$xbrls[$ii]['current']->eps2 -= $xbrls[$ii+1]['current']->eps2;

				$xbrls[$ii]['corresp']->revenue -= $xbrls[$ii+1]['corresp']->revenue;
				$xbrls[$ii]['corresp']->costs -= $xbrls[$ii+1]['corresp']->costs;
				$xbrls[$ii]['corresp']->profit -= $xbrls[$ii+1]['corresp']->profit;
				$xbrls[$ii]['corresp']->income -= $xbrls[$ii+1]['corresp']->income;
				$xbrls[$ii]['corresp']->interestexpense -= $xbrls[$ii+1]['corresp']->interestexpense;
				$xbrls[$ii]['corresp']->nopbt -= $xbrls[$ii+1]['corresp']->nopbt;
				$xbrls[$ii]['corresp']->nopat -= $xbrls[$ii+1]['corresp']->nopat;
				$xbrls[$ii]['corresp']->nopatc -= $xbrls[$ii+1]['corresp']->nopatc;
				$xbrls[$ii]['corresp']->eps -= $xbrls[$ii+1]['corresp']->eps;
				$xbrls[$ii]['corresp']->eps2 -= $xbrls[$ii+1]['corresp']->eps2;
			}
		}
	}

	return $xbrls;
}

function load_yearly_xbrl($id, $num)
{
	global $season_enum;
	global $conn;

	$latest_season = get_latest_xbrldata_season($id);

	$start = array_search($latest_season, $season_enum);
	// # of seasons in current year + 8 seasons + $num seasons,
	// which is for the need of calculating finantial indexes
	$len = 8 + (int)substr($latest_season, 4, 2) + $num*4;

	// $start 指向 xbrldata 目前有資料的最新季報
	// $len 為在這個routine當中要load的季報筆數, 目前設定為今年到現在的財報跟過去八季的財報

	$season_list = array();
	for ($ii=$start;$ii<min(count($season_enum),$start+$len);$ii++)
		array_push($season_list, $season_enum[$ii]);

	$xbrly = array();

	// get 'current' xbrl for assigned year x season
	$sql = "SELECT season, " .
			"arn, arnr, inventory, othercurrentassets, currentassets, fixedassets, assets, " .
			"currentliabilities, othernoncurrentliabilities, noncurrentliabilities, liabilities, stock, noncontrol, equity, " .
			"revenue, costs, profit, income, nopbt, nopat, nopatc, eps, eps2, " .
			"interestexpense, cashoa, cashia, " .
			"publish " .
			"FROM xbrldata WHERE id = " . $id . " AND period = 'current' AND (season = " . $season_list[0];

	for ($ii=1;$ii<$len;$ii++)
		$sql = $sql . " OR season = " . $season_list[$ii];

	$sql = $sql . ") ORDER BY season DESC";

	$result = mysqli_query($conn, $sql) or die('MySQL query error' . $sql);

	$jj = 0;
	while($row = mysqli_fetch_array($result)){
		$season = $row[$jj++];
		if (substr($season, 4, 2) != '04')
		{
			$jj = 0;
			continue;
		}
		$xbrl = new xbrlData();
		$xbrl->season = $season;

		$xbrl->arn = $row[$jj++];
		$xbrl->arnr = $row[$jj++];
		$xbrl->inventory = $row[$jj++];
		$xbrl->othercurrentassets = $row[$jj++];
		$xbrl->currentassets = $row[$jj++];
		$xbrl->fixedassets = $row[$jj++];
		$xbrl->assets = $row[$jj++];
		$xbrl->currentliabilities = $row[$jj++];
		$xbrl->othernoncurrentliabilities = $row[$jj++];
		$xbrl->noncurrentliabilities = $row[$jj++];
		$xbrl->liabilities = $row[$jj++];
		$xbrl->stock = $row[$jj++];
		$xbrl->noncontrol = $row[$jj++];
		$xbrl->equity = $row[$jj++];

		$xbrl->revenue = $row[$jj++];
		$xbrl->costs = $row[$jj++];
		$xbrl->profit = $row[$jj++];
		$xbrl->income = $row[$jj++];
		$xbrl->nopbt = $row[$jj++];
		$xbrl->nopat = $row[$jj++];
		$xbrl->nopatc = $row[$jj++];
		$xbrl->eps = $row[$jj++];
		$xbrl->eps2 = $row[$jj++];

		$xbrl->interestexpense = $row[$jj++];
		$xbrl->cashoa = $row[$jj++];
		$xbrl->cashia = $row[$jj++];

		$xbrl->publish = $row[$jj++];

		$jj = 0;
		array_push($xbrly, $xbrl);
	}

	return $xbrly;
}

?>