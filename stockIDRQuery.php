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

class idrData
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

function query_idr_data_by_id_y($id, $year, $id_kline)
{
	echo_v (DEBUG_VERBOSE, "[query_idr_data_by_id_y] id " . $id . " on year " . $year);

	// get day price of the year
	$dayprices = query_day_price_by_id_y($id, $year, $id_kline);
	//print_r($dayprices);
	if (count($dayprices) > 0)
	{
		// adjust the day data according to xdr date and value
		$xdr = query_xdr_data_by_id_y($id, $year);
		echo_v (DEBUG_VERBOSE, "[query_idr_data_by_id_y] " . count($xdr) . " xdr is found!");
		foreach($xdr as $xdr_entry)
		{
			foreach($dayprices as $key => $value)
			{
				if ($key >= $xdr_entry->date)
				{
					if ($xdr_entry->xdr=='xd')
					{
						$dayprices[$key] += $xdr_entry->xd;
					}
					else if ($xdr_entry->xdr=='xr')
					{
						$dayprices[$key] *= (1+$xdr_entry->xr+$xdr_entry->xr2);
						$dayprices[$key] -= ($xdr_entry->xr2p*$xdr_entry->xr2);
					}
					else if ($xdr_entry->xdr=='xdr')
					{
						$dayprices[$key] *= (1+$xdr_entry->xr+$xdr_entry->xr2);
						$dayprices[$key] -= ($xdr_entry->xr2p*$xdr_entry->xr2);
						$dayprices[$key] += $xdr_entry->xd;
					}
				}
			}
		}
		foreach($dayprices as $key => $value)
		{
			$dayprices[$key] = decimal2($dayprices[$key]);
		}
		//print_r($dayprices);
		// prepare idr object
		rsort($dayprices);
		//print_r($dayprices);
		$idr = new idrData();
		$idr->high = (float)$dayprices[0];
		$idr->low = (float)$dayprices[count($dayprices)-1];

		return $idr;
	}

	return null;
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
		$idr = query_idr_data_by_id_y($id, (string)((int)$nowyyyy + 1), $id_kline);
		print_r($idr);
		// "current year" expected
		$idr = query_idr_data_by_id_y($id, $nowyyyy, $id_kline);
		print_r($idr);
		// "full year" expected
		$idr = query_idr_data_by_id_y($id, (string)((int)$nowyyyy - 1), $id_kline);
		print_r($idr);
		// "full year" expected
		$idr = query_idr_data_by_id_y($id, (string)((int)$onyyyy + 1), $id_kline);
		print_r($idr);
		// "IPO year" expected
		$idr = query_idr_data_by_id_y($id, $onyyyy, $id_kline);
		print_r($idr);
		// "before IPO year" expected
		$idr = query_idr_data_by_id_y($id, (string)((int)$onyyyy - 1), $id_kline);
		print_r($idr);
	}

	$id_kline = query_day_price_by_id_since('3126', '2014-01-01');
	$idr = query_idr_data_by_id_y('3126', '2014', $id_kline);
	print_r($idr);
}

?>