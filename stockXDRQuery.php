<?php

/*
Filename:
	stockXDRQuery.php

Usage:


Descriptions:
	This file exports functions to query XDR data
*/

include_once("LIB_log.php");
include_once("LIB_mysql.php");

include_once("stockDayQuery.php");

class xdrData
{
    // property declaration
	public $date = '';
	public $market = '';
	public $value = 0.0;
	public $xdr = '';
	public $xd = 0.0;
	public $xr = 0.0;
	public $xr2 = 0.0;
	public $xr2p = 0.0;
}

class xdrHL
{
    // property declaration
	public $high = 0.0;
    public $low = 0.0;
}

class perData
{
    // property declaration
	public $high = 0.0;
    public $low = 0.0;
}

/******************** Query Utilities ************************/

function query_xdr_data_by_id($id)
{
	global $conn;
	$xdrdata = array();

	// Does it matter to set ORDER DESC or ORDER ASC?
	// if DESC, a little different from moneyDJ values
	$query = "SELECT * FROM xdrdata WHERE id = '" . $id . "' ORDER BY date";
	stopwatch_inter();
	$result = mysqli_query($conn, $query) or die('MySQL query error');

	$ii = 0;
	while($row = mysqli_fetch_array($result)){
		$xdrdata[$ii] = new xdrData();
		$xdrdata[$ii]->date = $row['date'];
		$xdrdata[$ii]->market = $row['market'];
		$xdrdata[$ii]->value = $row['value'];
		$xdrdata[$ii]->xdr = $row['xdr'];
		$xdrdata[$ii]->xd = $row['xd'];
		$xdrdata[$ii]->xr = $row['xr'];
		$xdrdata[$ii]->xr2 = $row['xr2'];
		$xdrdata[$ii]->xr2p = $row['xr2p'];

		echo_v(DEBUG_VERBOSE, "[query_xdr_data_by_id] stock " . $id . " xdr " . $row['value'] . " on " . $row['date']);
		$ii++;
	}

	echo_v(LOG_VERBOSE, stopwatch_inter() . " ms to ". formatstr($query) . "[" . __FUNCTION__ . "]");
	echo_v(DEBUG_VERBOSE, "[query_xdr_data_by_id] There are " . (($ii==0)?"no":$ii) . " xdr for id " . $id);

	return $xdrdata;
}

function query_xdr_data_by_id_y($id, $year)
{
	global $conn;
	$xdrdata = array();

	// Does it matter to set ORDER DESC or ORDER ASC?
	// if DESC, a little different from moneyDJ values
	$query = "SELECT * FROM xdrdata WHERE id = '" . $id . "' AND Year(date) = '" . $year . "' ORDER BY date";
	stopwatch_inter();
	$result = mysqli_query($conn, $query) or die('MySQL query error');

	$ii = 0;
	while($row = mysqli_fetch_array($result)){
		$xdrdata[$ii] = new xdrData();
		$xdrdata[$ii]->date = $row['date'];
		$xdrdata[$ii]->market = $row['market'];
		$xdrdata[$ii]->value = $row['value'];
		$xdrdata[$ii]->xdr = $row['xdr'];
		$xdrdata[$ii]->xd = $row['xd'];
		$xdrdata[$ii]->xr = $row['xr'];
		$xdrdata[$ii]->xr2 = $row['xr2'];
		$xdrdata[$ii]->xr2p = $row['xr2p'];

		echo_v(DEBUG_VERBOSE, "[query_xdr_data_by_id] stock " . $id . " xdr " . $row['value'] . " on " . $row['date']);
		$ii++;
	}

	echo_v(LOG_VERBOSE, stopwatch_inter() . " ms to ". formatstr($query) . "[" . __FUNCTION__ . "]");
	echo_v(DEBUG_VERBOSE, "[query_xdr_data_by_id_y] There are " . (($ii==0)?"no":$ii) . " xdr for id " . $id);

	return $xdrdata;
}

function query_xr_data_by_id($id)
{
	global $conn;
	$xr = 0.0;

	$query = "SELECT xr FROM xdrdata WHERE id = '" . $id . "' AND market = 'sii' AND Year(date) = '" . date("Y") . "'";
	stopwatch_inter();
	$result = mysqli_query($conn, $query) or die('MySQL query error');

	$kk = 0;
	while($row = mysqli_fetch_array($result)){
		$xr = $row['xr'];
		echo_v(DEBUG_VERBOSE, "[query_xr_data_by_id] stock " . $id . ", xr= " . $xr);
		$kk++;
		break;
	}

	if ($kk != 0) {
		echo_v(LOG_VERBOSE, stopwatch_inter() . " ms to ". formatstr($query) . "[" . __FUNCTION__ . "]");
		echo_v(DEBUG_VERBOSE, "[query_xr_data_by_id] There are " . $kk . " xr for id " . $id);
		return $xr;
	}

	$query = "SELECT xr FROM xdrdata WHERE id = '" . $id . "' AND market = 'otc' AND Year(date) = '" . date("Y") . "'";
	stopwatch_inter();
	$result = mysqli_query($conn, $query) or die('MySQL query error');

	$jj = 0;
	while($row = mysqli_fetch_array($result)){
		$xr = $row['xr'];
		echo_v(DEBUG_VERBOSE, "[query_xr_data_by_id] stock " . $id . ", xr= " . $xr);
		$jj++;
		break;
	}

	if ($jj != 0) {
		echo_v(LOG_VERBOSE, stopwatch_inter() . " ms to ". formatstr($query) . "[" . __FUNCTION__ . "]");
		echo_v(DEBUG_VERBOSE, "[query_xr_data_by_id] There are " . $jj . " xr for id " . $id);
		return $xr;
	}

	$query = "SELECT xr FROM xdrdata WHERE id = '" . $id . "' AND market = 'bod' AND Year(date) = '" . date("Y") . "'";
	stopwatch_inter();
	$result = mysqli_query($conn, $query) or die('MySQL query error');

	$ii = 0;
	while($row = mysqli_fetch_array($result)){
		$xr = $row['xr'];
		echo_v(DEBUG_VERBOSE, "[query_xr_data_by_id] stock " . $id . ", xr= " . $xr);
		$ii++;
		break;
	}

	echo_v(LOG_VERBOSE, stopwatch_inter() . " ms to ". formatstr($query) . "[" . __FUNCTION__ . "]");
	echo_v(DEBUG_VERBOSE, "[query_xr_data_by_id] There are " . (($ii==0)?"no":$ii) . " xr for id " . $id);

	return $xr;
}

// moneyDJ algorithm
// $id_kline is [ $kline_date => $kline_lochs (low, open, close, high, stock) ]*
function convert_idr_to_xdr($id, $id_kline)
{
	echo_v (DEBUG_VERBOSE, "[convert_idr_to_xdr] id " . $id);

	$xdr_kline = array();
	foreach($id_kline as $kline_date => $kline_lochs)
		$xdr_kline[$kline_date] = $kline_lochs;

	if (count($xdr_kline) > 0)
	{
		// adjust the day data according to xdr date and value
		$xdr_data = query_xdr_data_by_id($id);
		echo_v (LOG_VERBOSE, "[convert_idr_to_xdr] " . count($xdr_data) . " xdr(s) are found!");

		foreach($xdr_data as $xdr_entry)
		{
			// Don't apply this xdr to id_kline if xdr is 'bod'
			if ($xdr_entry->market == 'bod')
			{
				echo_v (LOG_VERBOSE, "[convert_idr_to_xdr] market is bod.");
				continue;
			}

			// Don't apply this xdr to id_kline if xdr date is not within dates of id_kline
			if (!array_key_exists($xdr_entry->date, $xdr_kline))
			{
				echo_v (LOG_VERBOSE, "[convert_idr_to_xdr] " . $xdr_entry->date . " not within.");
				continue;
			}

			// Get the last trade day before XD/XDR date
			$date = date("Y-m-d", (strtotime($xdr_entry->date) - 86400));
			$dates = array_keys($id_kline);
			while (false == array_search($date, $dates))
	    		$date = date("Y-m-d", (strtotime($date) - 86400));
	    	echo_v (LOG_VERBOSE, "[convert_idr_to_xdr] date moves to " . $date);

	    	if (($xdr_entry->xdr=='xd') or ($xdr_entry->xdr=='xdr'))
	    	{
	    		$before_xdr = $xdr_kline[$date][2];
	    		$ratio = ($before_xdr-$xdr_entry->xd) / $before_xdr;

	    		foreach($xdr_kline as $kline_date => $kline_lochs)
	    		{
	    			if ($kline_date < $xdr_entry->date)
					{
						$xdr_kline[$kline_date][0] *= $ratio; // low
						$xdr_kline[$kline_date][1] *= $ratio; // open
						$xdr_kline[$kline_date][2] *= $ratio; // close
						$xdr_kline[$kline_date][3] *= $ratio; // high
					}
	    		}
	    	}
		}
	}

	return $xdr_kline;
}

function query_xdr_highlow_by_year($year, $xdr_kline)
{
	if ((int)$year > (int)date("Y"))
	{
		echo_v (ERROR_VERBOSE, "[query_xdr_highlow_by_year] invalid future year " . $year);
		return null;
	}

	$dates = array_keys($xdr_kline);
	$date = $dates[count($dates)-1];
	if ((int)$year < (int)substr($date, 0, 4))
	{
		echo_v (ERROR_VERBOSE, "[query_xdr_highlow_by_year] invalid past year " . $year);
		return null;
	}

	$highs = array();
	$lows = array();

	foreach ($xdr_kline as $kline_date => $kline_lochs)
	{
		if ($year == substr($kline_date, 0, 4))
		{
			$highs[$kline_date] = $kline_lochs[3];
			$lows[$kline_date] = $kline_lochs[0];
		}
	}

	rsort($highs);
	//print_r($highs);
	rsort($lows);
	//print_r($lows);

	$xdr = new xdrHL();
	$xdr->high = decimal2((float)$highs[0]);
	$xdr->low = decimal2((float)$lows[count($lows)-1]);

	return $xdr;
}

/******************** Test Utilities ************************/

function stockXDRQueryTest()
{
	echo_v(LOG_VERBOSE, "");
	echo_v(LOG_VERBOSE, "**********************************************************************");
	echo_v(LOG_VERBOSE, "**** stockXDRQueryTest ***********************************************");
	echo_v(LOG_VERBOSE, "**********************************************************************");
	echo_v(LOG_VERBOSE, "");

	// sii.1338 since 2012/12/19
	// sii.1338 on 2014(current-1) -> 3.97 on 2014/6/12
	// otc.3265 since 2008 (2005/08/02, but tpex only offer since 2008)
	// otc.3265 on 2015(current) -> 2.26 (right=0, dividend=2.26) on 2015/05/04
	// otc.5227 since 2013/12/09
	// otc.5227 on 2015(current) -> 1.08 on 2015/01/21
	//$id_array = array('1338', '3265', '5227', '2727', '2867');

	// given stock id and year, get high and low idr value.
	// 2867 has 2 xr on year 2014
	// 2727 has 1 xd and 1 xr on year 2014
	//$id_array = array('2454', '1338', '3265', '5227', '2867', '2727');
	$id_array = array();

	foreach($id_array as $id)
	{
		$nowyyyy = date("Y");
		$stock = query_id_data_by_id($id);

		/* leave if stock not found */
		if ($stock == null)
		{
			echo_v(ERROR_VERBOSE, "[stockIDRQueryTest] stock id " . $id . " is not found!");
			return;
		}
		echo_v(DEBUG_VERBOSE, "[stockIDRQueryTest] stock id " . $id . " is found!");

		$onyyyy = $stock->onyyyy;

		if ($stock->market == 'sii')
		{
			if ((int)$stock->onyyyy < 2003)
				$onyyyy = '2003';
		}
		else if ($stock->market == 'otc')
		{
			if ((int)$stock->onyyyy < 2008)
				$onyyyy = '2008';
		}

		$id_kline = query_day_price_lochs_by_id_since($id, '2010-01-01');
		$xdr_kline = convert_idr_to_xdr($id, $id_kline);

		// "future year" expected
		$hl = query_xdr_highlow_by_year((string)((int)$nowyyyy + 1), $xdr_kline);
		print_r($hl);

		// "current year" expected
		$hl = query_xdr_highlow_by_year($nowyyyy, $xdr_kline);
		print_r($hl);

		// "full year" expected
		$hl = query_xdr_highlow_by_year((string)((int)$nowyyyy - 1), $xdr_kline);
		print_r($hl);

		// "full year" expected
		$hl = query_xdr_highlow_by_year((string)((int)$onyyyy + 1), $xdr_kline);
		print_r($hl);

		// "IPO year" expected
		$hl = query_xdr_highlow_by_year($onyyyy, $id_kline);
		print_r($hl);

		// "before IPO year" expected
		$hl = query_xdr_highlow_by_year((string)((int)$onyyyy - 1), $xdr_kline);
		print_r($hl);
	}

	$id_kline = query_day_price_lochs_by_id_since('3126', '2014-01-01');
	$xdr_kline = convert_idr_to_xdr('3126', $id_kline);
	$hl = query_xdr_highlow_by_year('2014', $xdr_kline);
	print_r($hl);
}

?>