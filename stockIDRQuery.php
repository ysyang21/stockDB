<?php

/*
Filename:
	stockIDRQuery.php

Usage:


Descriptions:
	This file exports functions to query IDR data
*/

include_once("LIB_log.php");

include_once("stockDayQuery.php");
include_once("stockXDRQuery.php");

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

// moneyDJ algorithm
// $id_kline is [ $kline_date => $kline_lochs (low, open, close, high, stock) ]*
function convert_idr_to_xdr($id, $id_kline)
{
	echo_v (DEBUG_VERBOSE, "[convert_idr_to_xdr] id " . $id);

	$xdr_kline = array();
	foreach($id_kline as $kline_date => $kline_lochs)
	{
		$xdr_kline[$kline_date] = $kline_lochs;
	}

	if (count($xdr_kline) > 0)
	{
		// adjust the day data according to xdr date and value
		$xdr = query_xdr_data_by_id($id);
		echo_v (DEBUG_VERBOSE, "[convert_idr_to_xdr] " . count($xdr) . " xdr is found!");

		foreach($xdr as $xdr_entry)
		{
			// Get the last trade day before XD/XDR date
			$date = date("Y-m-d", (strtotime($xdr_entry->date) - 86400));
			$dates = array_keys($id_kline);
			while (false === array_search($date, $dates))
	    		$date = date("Y-m-d", (strtotime($date) - 86400));

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
	rsort($lows);

	$xdr = new xdrHL();
	$xdr->high = decimal2((float)$highs[0]);
	$xdr->low = decimal2((float)$lows[count($lows)-1]);

	return $xdr;
}

/******************** Entry Function ************************/

function stockIDRQueryTest()
{
	echo_v(LOG_VERBOSE, "");
	echo_v(LOG_VERBOSE, "**********************************************************************");
	echo_v(LOG_VERBOSE, "**** stockIDRQueryTest ***********************************************");
	echo_v(LOG_VERBOSE, "**********************************************************************");
	echo_v(LOG_VERBOSE, "");

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

		$id_kline = query_day_price_by_id_since($id, '2010-01-01');

		// "future year" expected
		$idr = query_idr_highlow_by_id_y($id, (string)((int)$nowyyyy + 1), $id_kline);
		print_r($idr);
		// "current year" expected
		$idr = query_idr_highlow_by_id_y($id, $nowyyyy, $id_kline);
		print_r($idr);
		// "full year" expected
		$idr = query_idr_highlow_by_id_y($id, (string)((int)$nowyyyy - 1), $id_kline);
		print_r($idr);
		// "full year" expected
		$idr = query_idr_highlow_by_id_y($id, (string)((int)$onyyyy + 1), $id_kline);
		print_r($idr);
		// "IPO year" expected
		$idr = query_idr_highlow_by_id_y($id, $onyyyy, $id_kline);
		print_r($idr);
		// "before IPO year" expected
		$idr = query_idr_highlow_by_id_y($id, (string)((int)$onyyyy - 1), $id_kline);
		print_r($idr);
	}

	$id_kline = query_day_price_by_id_since('3126', '2014-01-01');
	$idr = query_idr_highlow_by_id_y('3126', '2014', $id_kline);
	print_r($idr);
}

?>