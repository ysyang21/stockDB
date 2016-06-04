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

class xbrlData
{
	public $season = "";
	public $stock = 0;
	public $revenue = 0;
	public $nopat = 0;
	public $eps = 0;
	public $eps2 = 0;
	public $publish = "";
	public $inventory = 0;
	public $income = 0;
	public $cashoa = 0;
	public $cashia = 0;
}

$id_seasonly_xbrl = [];

function prepare_id_seasonly_xbrl($id)
{
	global $id_seasonly_xbrl;

	$query = "SELECT season, revenue, stock, eps, eps2, publish FROM xbrldata WHERE id = " . $id;
	stopwatch_inter();
	$result = mysql_query($query) or die('MySQL query error');
	$id_seasonly_xbrl = [];
	while($row = mysql_fetch_array($result)){
		$id_seasonly_xbrl[$row[0]] = array((int)$row[1], (int)$row[2], (float)$row[3], (float)$row[4], $row[5]);
	}

	echo_v(LOG_VERBOSE, stopwatch_inter() . " ms to ". formatstr($query) . "[" . __FUNCTION__ . "]");
}

function query_all_inventory($id)
{
	$query = "SELECT season, inventory FROM xbrldata WHERE id = " . $id;
	stopwatch_inter();
	$result = mysql_query($query) or die('MySQL query error');
	$inventory = [];
	while($row = mysql_fetch_array($result)){
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
		echo_v(DEBUG_VERBOSE, "[query_seasonly_revenue] id = " . $id . " has no seasonly publish data on " . $season);
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
		echo_v(ERROR_VERBOSE, "[query_seasonly_revenue] id = " . $id . " has no stock data on " . $year);
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
		echo_v(ERROR_VERBOSE, "[query_year_eps] id = " . $id . " has no yearly eps2 data on " . $year);
	return $eps2;
}

function query_est_profitax_over_revenue($id, $date)
{
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
	$result = mysql_query($query) or die('MySQL query error');
	while($row = mysql_fetch_array($result)){
		$ratio = $row[0];
	}
	echo_v(LOG_VERBOSE, stopwatch_inter() . " ms to ". formatstr($query) . "[" . __FUNCTION__ . "]");
	return $ratio;
}

// if given xbrl is published in given date
function query_xbrl_on_date($id, $year, $season, $date)
{
	$publish = "";
	$query = "SELECT publish FROM xbrldata WHERE id = " . $id . " AND season = " . $year . $season . " AND report = 'ci-cr'";
	stopwatch_inter();
	$result = mysql_query($query) or die('MySQL query error');
	while($row = mysql_fetch_array($result)){
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
		$result = mysql_query($query) or die('MySQL query error');
		while($row = mysql_fetch_array($result)){
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
	$date = $since_date;
	$evaluate_dates = array();

	$query = "SELECT publish FROM xbrldata WHERE id = " . $id;
	stopwatch_inter();
	$result = mysql_query($query) or die('MySQL query error');

	while($row = mysql_fetch_array($result)){
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

	$sql = "SELECT season FROM xbrldata WHERE id = " . $id . " AND season = " . $might_have_been_published_season;
	$result = mysql_query($sql) or die('MySQL query error');
	$ii=0;
	while($row = mysql_fetch_array($result)){
		$ii++;
	}

	$latest_season = (($ii!=0)?$might_have_been_published_season:$latest_scheduled_season);

	return $latest_season;
}

function load_seasonly_xbrl($id)
{
	global $season_enum;

	$latest_season = get_latest_xbrldata_season($id);

	$start = array_search($latest_season, $season_enum);
	$len = 8 + (int)substr($latest_season, 4, 2);

	// $start 指向 xbrldata 目前有資料的最新季報
	// $len 為在這個routine當中要load的季報筆數, 目前設定為今年到現在的財報跟過去八季的財報

	$season_list = array();
	for ($ii=$start;$ii<min(count($season_enum),$start+$len);$ii++)
		array_push($season_list, $season_enum[$ii]);

	$sql = "SELECT season, stock, revenue, nopat, eps, eps2, publish, inventory, income, cashoa, cashia FROM xbrldata WHERE id = " . $id .
		" AND (season = " . $season_list[0];

	for ($ii=1;$ii<$len;$ii++)
		$sql = $sql . " OR season = " . $season_list[$ii];

	$sql = $sql . ") ORDER BY season DESC";

	$result = mysql_query($sql) or die('MySQL query error');

	$xbrls = array();

	while($row = mysql_fetch_array($result)){
		$xbrl = new xbrlData();
		$xbrl->season = $row[0];
		$xbrl->stock = $row[1];
		$xbrl->revenue = $row[2];
		$xbrl->nopat = $row[3];
		$xbrl->eps = $row[4];
		$xbrl->eps2 = $row[5];
		$xbrl->publish = $row[6];
		$xbrl->inventory = $row[7];
		$xbrl->income = $row[8];
		$xbrl->cashoa = $row[9];
		$xbrl->cashia = $row[10];

		array_push($xbrls, $xbrl);
	}

	//echo '<pre>';
	//print_r($xbrls);
	//echo '</pre>';
	
	$count = count($xbrls);
	for ($ii=0;$ii<$count;$ii++)
	{
		if ((int)substr($xbrls[$ii]->season, 0, 4) >= 2013) // ifrs
		{
			if (substr($xbrls[$ii]->season, 4, 2) == '04' and $count - $ii >=4)
			{
				if (substr($xbrls[$ii+1]->season, 4, 2) == '03')
				{
					$xbrls[$ii]->revenue -= $xbrls[$ii+1]->revenue;
					$xbrls[$ii]->nopat -= $xbrls[$ii+1]->nopat;
					$xbrls[$ii]->eps -= $xbrls[$ii+1]->eps;
				}
				if (substr($xbrls[$ii+2]->season, 4, 2) == '02')
				{
					$xbrls[$ii]->revenue -= $xbrls[$ii+2]->revenue;
					$xbrls[$ii]->nopat -= $xbrls[$ii+2]->nopat;
					$xbrls[$ii]->eps -= $xbrls[$ii+2]->eps;
				}
				if (substr($xbrls[$ii+3]->season, 4, 2) == '01')
				{
					$xbrls[$ii]->revenue -= $xbrls[$ii+3]->revenue;
					$xbrls[$ii]->nopat -= $xbrls[$ii+3]->nopat;
					$xbrls[$ii]->eps -= $xbrls[$ii+3]->eps;
				}
			}	
		}
		else // gaap
		{
			if (substr($xbrls[$ii]->season, 4, 2) == '04' and $count - $ii >=1)
			{
				$xbrls[$ii]->revenue -= $xbrls[$ii+1]->revenue;
				$xbrls[$ii]->nopat -= $xbrls[$ii+1]->nopat;
				$xbrls[$ii]->eps -= $xbrls[$ii+1]->eps;
			}
			if (substr($xbrls[$ii]->season, 4, 2) == '03' and $count - $ii >=1)
			{
				$xbrls[$ii]->revenue -= $xbrls[$ii+1]->revenue;
				$xbrls[$ii]->nopat -= $xbrls[$ii+1]->nopat;
				$xbrls[$ii]->eps -= $xbrls[$ii+1]->eps;
			}
			if (substr($xbrls[$ii]->season, 4, 2) == '02' and $count - $ii >=1)
			{
				$xbrls[$ii]->revenue -= $xbrls[$ii+1]->revenue;
				$xbrls[$ii]->nopat -= $xbrls[$ii+1]->nopat;
				$xbrls[$ii]->eps -= $xbrls[$ii+1]->eps;
			}
		}
	}

	//echo '<pre>';
	//print_r($xbrls);
	//echo '</pre>';

	return $xbrls;
}

?>