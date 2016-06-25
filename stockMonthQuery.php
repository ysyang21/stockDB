<?php

/*
Filename:
	stockMonthQuery.php

Usage:

Descriptions:
	Exports functions to query monthdata database.
*/

include_once("LIB_log.php");
include_once("LIB_mysql.php");

$id_monthly_revenue = [];

function prepare_id_monthly_revenue($id)
{
	global $id_monthly_revenue;

	$query = "SELECT month, thisMonth, yearAgo FROM monthdata WHERE id = " . $id;
	stopwatch_inter();
	$result = mysql_query($query) or die('MySQL query error');
	$id_monthly_revenue = [];
	while($row = mysql_fetch_array($result)){
		$id_monthly_revenue[$row[0]] = array((int)$row[1], (int)$row[2]);
	}

	echo_v(LOG_VERBOSE, stopwatch_inter() . " ms to ". formatstr($query) . "[" . __FUNCTION__ . "]");
}

function query_est_monthly_revenue_yoy($pepo, $year, $month, $day)
{
	global $yearmonth_enum;
	global $id_monthly_revenue;
	$yoy1 = 0;
	$yoy2 = 0;
	$id = $pepo->id;

	$ii = (array_key_exists($yearmonth_enum[array_search($year.$month, $yearmonth_enum) + 1], $id_monthly_revenue) ? 1 : 0);

	if ($ii!=0)
		$start = array_search($year.$month, $yearmonth_enum) + 1;
	else
		$start = array_search($year.$month, $yearmonth_enum) + 2;

	// Do we need last 6 or 7 months data? It depends what do we have.
	// The possibility that Janurary is a complete month is very high since CNY usually take place on Febrary.
	// If Feburary is among last 6 month data, we must combine it with Janurary to evaluate as one month data.
	//
	// 12月10日後: 11 10 09 08 07 06 / 10日前 10 09 08 07 06 05
	// 11月10日後: 10 09 08 07 06 05 / 10日前 09 08 07 06 05 04
	// 10月10日後: 09 08 07 06 05 04 / 10日前 08 07 06 05 04 03
	// 09月10日後: 08 07 06 05 04 03 / 10日前 07 06 05 04 03 (02 01)
	// 08月10日後: 07 06 05 04 03 (02 01) / 10日前 06 05 04 03 (02 01) 12
	// 07月10日後: 06 05 04 03 (02 01) 12 / 10日前 05 04 03 (02 01) 12 11
	// 06月10日後: 05 04 03 (02 01) 12 11 / 10日前 04 03 (02 01) 12 11 10
	// 05月10日後: 04 03 (02 01) 12 11 10 / 10日前 03 (02 01) 12 11 10 09
	// 04月10日後: 03 (02 01) 12 11 10 09 / 10日前 (02 01) 12 11 10 09 08
	// 03月10日後: (02 01) 12 11 10 09 08 / 10日前 01 12 11 10 09 08
	// 02月10日後: 01 12 11 10 09 08 / 10日前 12 11 10 09 08 07
	// 01月10日後: 12 11 10 09 08 07 / 10日前 11 10 09 08 07 06

	$month_list = array();
	$month_start = substr($yearmonth_enum[$start], 4, 2);
	if ((int)$month_start>=8 or (int)$month_start<=1) // get 6 month revenue yoy
	{
		for ($ii=$start;$ii<min(count($yearmonth_enum), $start+6);$ii++)
		{
			array_push($month_list, $yearmonth_enum[$ii]);
		}
	}
	else // get 6 or 7 month revenue yoy, depending on whether flag JAN_FEB_MERGE is set or not
	{
		for ($ii=$start;$ii<min(count($yearmonth_enum), $start+6+JAN_FEB_MERGE);$ii++)
		{
			array_push($month_list, $yearmonth_enum[$ii]);
		}
	}

	if (count($month_list)==6)
	{
		$query = "SELECT month, ((thisMonth/yearAgo)-1) FROM monthdata WHERE id = " . $id .
			" AND (month = " . $month_list[0] .
			" OR month = " . $month_list[1] .
			" OR month = " . $month_list[2] .
			" OR month = " . $month_list[3] .
			" OR month = " . $month_list[4] .
			" OR month = " . $month_list[5] . ")";
		$result = mysql_query($query) or die('MySQL query error');
		while($row = mysql_fetch_array($result)){
			$pepo->monthly_revenue_yoy[$row[0]]=$row[1];
		}
		echo_v(LOG_VERBOSE, stopwatch_inter() . " ms to ". formatstr($query) . "[" . __FUNCTION__ . "]");
		
		$yoy1 = array_sum($pepo->monthly_revenue_yoy)/6;
		$yoy2 = $pepo->monthly_revenue_yoy[$month_list[0]];
		
		echo_v(DEBUG_VERBOSE, "[query_est_monthly_revenue_yoy] avg = " . percent($yoy1) . ", latest = " . percent($yoy2));
		return min($yoy1, $yoy2);
	}
	else // count = 7, a little complicated
	{
		$query = "SELECT month, ((thisMonth/yearAgo)-1) FROM monthdata WHERE id = " . $id .
			" AND (month = " . $month_list[0] .
			" OR month = " . $month_list[1] .
			" OR month = " . $month_list[2] .
			" OR month = " . $month_list[3] .
			" OR month = " . $month_list[4] .
			" OR month = " . $month_list[5] .
			" OR month = " . $month_list[6] . ")";
		$result = mysql_query($query) or die('MySQL query error');
		while($row = mysql_fetch_array($result)){
			$pepo->monthly_revenue_yoy[$row[0]]=$row[1];
		}
		echo_v(LOG_VERBOSE, stopwatch_inter() . " ms to ". formatstr($query) . "[" . __FUNCTION__ . "]");
		$B = array_sum($pepo->monthly_revenue_yoy)/7; // B
		$yoy_jan = $pepo->monthly_revenue_yoy[$year."01"];
		$yoy_feb = $pepo->monthly_revenue_yoy[$year."02"];
		
		$query = "SELECT month, thisMonth FROM monthdata WHERE id = " . $id .
			" AND (month = " . (string)((int)$year-1) . "02" .
			" OR month = " . (string)((int)$year-1) . "01)";
		$result = mysql_query($query) or die('MySQL query error');
		$revenue = array();
		while($row = mysql_fetch_array($result)){
			$revenue[$row[0]] = $row[1];
		}
		echo_v(LOG_VERBOSE, stopwatch_inter() . " ms to ". formatstr($query) . "[" . __FUNCTION__ . "]");
		$mom_lastfeb = $revenue[(string)((int)$year-1) . "02"] / $revenue[(string)((int)$year-1) . "01"];
		$X = $yoy_feb/(1+$mom_lastfeb) + $yoy_jan/(1+1/$mom_lastfeb); // X
		// A = (7B-X) / 6
		$yoy1 = ($B * 7 - $X) / 6;
		$yoy2 = $pepo->monthly_revenue_yoy[$month_list[0]];
		
		echo_v(DEBUG_VERBOSE, "[query_est_monthly_revenue_yoy] avg = " . percent($yoy1) . ", latest = " . percent($yoy2));
		return min($yoy1, $yoy2); 
	}
}

function query_est_monthly_revenue_yoy_v0($pepo, $year, $month, $day)
{
	global $yearmonth_enum;
	$yoy1 = 0;
	$yoy2 = 0;
	$id = $pepo->id;

	$query = "SELECT month FROM monthdata WHERE id = " . $id . " AND month = " . $yearmonth_enum[array_search($year.$month, $yearmonth_enum) + 1];
	stopwatch_inter();
	$result = mysql_query($query) or die('MySQL query error');
	$ii=0;
	while($row = mysql_fetch_array($result)){
		$ii++;
	}
	echo_v(LOG_VERBOSE, stopwatch_inter() . " ms to ". formatstr($query) . "[" . __FUNCTION__ . "]");

	if ($ii!=0)
		$start = array_search($year.$month, $yearmonth_enum) + 1;
	else
		$start = array_search($year.$month, $yearmonth_enum) + 2;

	// Do we need last 6 or 7 months data? It depends what do we have.
	// The possibility that Janurary is a complete month is very high since CNY usually take place on Febrary.
	// If Feburary is among last 6 month data, we must combine it with Janurary to evaluate as one month data.
	//
	// 12月10日後: 11 10 09 08 07 06 / 10日前 10 09 08 07 06 05
	// 11月10日後: 10 09 08 07 06 05 / 10日前 09 08 07 06 05 04
	// 10月10日後: 09 08 07 06 05 04 / 10日前 08 07 06 05 04 03
	// 09月10日後: 08 07 06 05 04 03 / 10日前 07 06 05 04 03 (02 01)
	// 08月10日後: 07 06 05 04 03 (02 01) / 10日前 06 05 04 03 (02 01) 12
	// 07月10日後: 06 05 04 03 (02 01) 12 / 10日前 05 04 03 (02 01) 12 11
	// 06月10日後: 05 04 03 (02 01) 12 11 / 10日前 04 03 (02 01) 12 11 10
	// 05月10日後: 04 03 (02 01) 12 11 10 / 10日前 03 (02 01) 12 11 10 09
	// 04月10日後: 03 (02 01) 12 11 10 09 / 10日前 (02 01) 12 11 10 09 08
	// 03月10日後: (02 01) 12 11 10 09 08 / 10日前 01 12 11 10 09 08
	// 02月10日後: 01 12 11 10 09 08 / 10日前 12 11 10 09 08 07
	// 01月10日後: 12 11 10 09 08 07 / 10日前 11 10 09 08 07 06

	$month_list = array();
	$month_start = substr($yearmonth_enum[$start], 4, 2);
	if ((int)$month_start>=8 or (int)$month_start<=1) // get 6 month revenue yoy
	{
		for ($ii=$start;$ii<min(count($yearmonth_enum), $start+6);$ii++)
		{
			array_push($month_list, $yearmonth_enum[$ii]);
		}
	}
	else // get 6 or 7 month revenue yoy, depending on whether flag JAN_FEB_MERGE is set or not
	{
		for ($ii=$start;$ii<min(count($yearmonth_enum), $start+6+JAN_FEB_MERGE);$ii++)
		{
			array_push($month_list, $yearmonth_enum[$ii]);
		}
	}

	if (count($month_list)==6)
	{
		$query = "SELECT month, ((thisMonth/yearAgo)-1) FROM monthdata WHERE id = " . $id .
			" AND (month = " . $month_list[0] .
			" OR month = " . $month_list[1] .
			" OR month = " . $month_list[2] .
			" OR month = " . $month_list[3] .
			" OR month = " . $month_list[4] .
			" OR month = " . $month_list[5] . ")";
		$result = mysql_query($query) or die('MySQL query error');
		while($row = mysql_fetch_array($result)){
			$pepo->monthly_revenue_yoy[$row[0]]=$row[1];
		}
		echo_v(LOG_VERBOSE, stopwatch_inter() . " ms to ". formatstr($query) . "[" . __FUNCTION__ . "]");
		
		$yoy1 = array_sum($pepo->monthly_revenue_yoy)/6;
		$yoy2 = $pepo->monthly_revenue_yoy[$month_list[0]];
		
		echo_v(DEBUG_VERBOSE, "[query_est_monthly_revenue_yoy] avg = " . percent($yoy1) . ", latest = " . percent($yoy2));
		return min($yoy1, $yoy2);
	}
	else // count = 7, a little complicated
	{
		$query = "SELECT month, ((thisMonth/yearAgo)-1) FROM monthdata WHERE id = " . $id .
			" AND (month = " . $month_list[0] .
			" OR month = " . $month_list[1] .
			" OR month = " . $month_list[2] .
			" OR month = " . $month_list[3] .
			" OR month = " . $month_list[4] .
			" OR month = " . $month_list[5] .
			" OR month = " . $month_list[6] . ")";
		$result = mysql_query($query) or die('MySQL query error');
		while($row = mysql_fetch_array($result)){
			$pepo->monthly_revenue_yoy[$row[0]]=$row[1];
		}
		echo_v(LOG_VERBOSE, stopwatch_inter() . " ms to ". formatstr($query) . "[" . __FUNCTION__ . "]");
		$B = array_sum($pepo->monthly_revenue_yoy)/7; // B
		$yoy_jan = $pepo->monthly_revenue_yoy[$year."01"];
		$yoy_feb = $pepo->monthly_revenue_yoy[$year."02"];
		
		$query = "SELECT month, thisMonth FROM monthdata WHERE id = " . $id .
			" AND (month = " . (string)((int)$year-1) . "02" .
			" OR month = " . (string)((int)$year-1) . "01)";
		$result = mysql_query($query) or die('MySQL query error');
		$revenue = array();
		while($row = mysql_fetch_array($result)){
			$revenue[$row[0]] = $row[1];
		}
		echo_v(LOG_VERBOSE, stopwatch_inter() . " ms to ". formatstr($query) . "[" . __FUNCTION__ . "]");
		$mom_lastfeb = $revenue[(string)((int)$year-1) . "02"] / $revenue[(string)((int)$year-1) . "01"];
		$X = $yoy_feb/(1+$mom_lastfeb) + $yoy_jan/(1+1/$mom_lastfeb); // X
		// A = (7B-X) / 6
		$yoy1 = ($B * 7 - $X) / 6;
		$yoy2 = $pepo->monthly_revenue_yoy[$month_list[0]];
		
		echo_v(DEBUG_VERBOSE, "[query_est_monthly_revenue_yoy] avg = " . percent($yoy1) . ", latest = " . percent($yoy2));
		return min($yoy1, $yoy2); 
	}
}

// Check if monthData of (year.month) exist on given date
function query_monthly_revenue($id, $month)
{
	$revenue = -1;
	$query = "SELECT thisMonth FROM monthdata WHERE id = " . $id . " AND month = " . $month;
	$result = mysql_query($query) or die('MySQL query error');
	while($row = mysql_fetch_array($result)){
		$revenue = $row[0];
	}
	if($revenue == -1)
		echo_v(DEBUG_VERBOSE, "[query_monthly_revenue] id = " . $id . " has no monthly revenue data on " . $month);
	return $revenue;
}

function query_month_revenue_yoys_sorted_on_month($month)
{
	$yoys = array();

	//$sql = "SELECT id, (thisMonth/yearAgo)-1 FROM monthdata WHERE month = '" . $month . "' ORDER BY (thisMonth/yearAgo) DESC";
	// need to join following condition:
	//$sql = "SELECT * FROM iddata WHERE ondate IS NOT NULL AND offdate IS NULL AND (market = 'sii' OR market = 'otc') AND industry != '存託憑證' AND type = 'ci'";
	//$query = "SELECT m.id, (m.thisMonth/m.yearAgo)-1 FROM monthdata m INNER JOIN iddata i ON (m.id=i.id) WHERE m.month = '" . $month .
	//		"' AND m.yearAgo != 0 AND i.industry != '存託憑證' AND i.type ='ci' ORDER BY (m.thisMonth/m.yearAgo) DESC";
	$query = "SELECT m.id, (m.thisMonth/m.yearAgo)-1 FROM monthdata m INNER JOIN iddata i ON (m.id=i.id) WHERE m.month = '" . $month .
			"' AND m.yearAgo != 0 ORDER BY (m.thisMonth/m.yearAgo) DESC";
	//echo_v(ALARM_VERBOSE, "[query_id_by_month_revenue_yoy_topN_from_start] sql = ". $sql);
	stopwatch_inter();
	$result = mysql_query($query) or die('MySQL query error');

	while($row = mysql_fetch_array($result)){
		//$yoys[$row['id']] = $row['(thisMonth/yearAgo)-1'];
		$yoys[$row[0]] = $row[1];
	}
	echo_v(LOG_VERBOSE, stopwatch_inter() . " ms to ". formatstr($query) . "[" . __FUNCTION__ . "]");
	// There are many zero monthly revenue, divided by zero is hard to handle.
	unset($yoys['4743']);
	unset($yoys['4168']);
	return $yoys;
}

class monthData
{
	public $month = ''; // '201201' .. '201912'
	public $current = 0; // monthly revenue of current month
	public $corresp = 0; // monthly revenue of correspoinding month exactly 12 months ago
}

// find 12 latest monthly revenue data in the database monthdata and print out
// this routine can be used to show the last update time
function load_monthly_revenue($id, $mon_num)
{
	global $yearmonth_enum;
	$year = date('Y');
	$month = date('m');

	// Using a while loop querying monthdata mysql until one of following conditions is satisfied:
	// 1. monthdata for some month exists,
	// 2. yearmonth_enum exhausts
	// probe if monthdata for last month is available
	$found = FALSE;
	$start = array_search($year.$month, $yearmonth_enum);
	do
	{
		$possible_yearmonth = $yearmonth_enum[$start];
		$query = "SELECT month FROM monthdata WHERE id = " . $id . " AND month = " . $possible_yearmonth;
		$result = mysql_query($query) or die('MySQL query error');
		while($row = mysql_fetch_array($result)){
			$found = TRUE;
		}
		$start++;
	}
	while (!$found and $start!=count($yearmonth_enum));

	if ($start==count($yearmonth_enum))
		return null;

	// notice here $start points to second existing monthdata, need to rewind back for one step
	$start--;

	$month_list = array();
	for ($ii=$start;$ii<min(count($yearmonth_enum), $start+($mon_num+3));$ii++)
	{
		array_push($month_list, $yearmonth_enum[$ii]);
	}

	$query = "SELECT month, thisMonth, yearAgo FROM monthdata WHERE id = " . $id . " AND (month = " . $month_list[0];
	for ($ii=1;$ii<count($month_list);$ii++)
		$query = $query . " OR month = " . $month_list[$ii];
	$query = $query . ") ORDER BY month DESC";
	$result = mysql_query($query) or die('MySQL query error');

	$months = array();
	$ii=0;
	while($row = mysql_fetch_array($result)){
		$months[$ii] = new monthData();
		$months[$ii]->month = $row[0];
		$months[$ii]->current = $row[1];
		$months[$ii]->corresp = $row[2];
		$ii++;
	}

	return $months;
}

function query_id_by_month_revenue_yoy_topN_from_start($month, $start, $topN)
{
	$yoys = array();

	$query = "SELECT id, (thisMonth/yearAgo)-1 FROM monthdata WHERE month = '" . $month . "' ORDER BY (thisMonth/yearAgo) DESC";
	//echo_v(ALARM_VERBOSE, "[query_id_by_month_revenue_yoy_topN_from_start] sql = ". $sql);
	$result = mysql_query($query) or die('MySQL query error');

	while($row = mysql_fetch_array($result)){
		//$yoys[$row['id']] = $row['(thisMonth/yearAgo)-1'];
		$yoys[$row[0]] = $row[1];
	}

	if(($start + $topN) > count($yoys))
	{
		echo_v(ERROR_VERBOSE, "[query_id_by_month_revenue_yoy_topN_from_start] N is larger than number of stocks!");
		return null;
	}

	return array_slice($yoys, $start, $topN, true);
}

function get_latest_monthdata_month($id)
{
	global $yearmonth_enum;

	// 輸入日期, 按照財報死線推算肯定已經在monthdata的最新月營收月份季度
	$latest_scheduled_month = get_latest_scheduled_month(today());

	// 例如說今天是某月9日, 雖然還沒到公布上月月營收的死線, 但是我們推論它很可能已經被更新到 monthdata當中了
	$might_have_been_published_month = $yearmonth_enum[array_search($latest_scheduled_month, $yearmonth_enum) - 1];

	$query = "SELECT month FROM monthdata WHERE id = " . $id . " AND month = " . $might_have_been_published_month;
	$result = mysql_query($query) or die('MySQL query error');
	$ii=0;
	while($row = mysql_fetch_array($result)){
		$ii++;
	}

	$latest_month = (($ii!=0)?$might_have_been_published_month:$latest_scheduled_month);

	return $latest_month;
}

?>