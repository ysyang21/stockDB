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

include_once("stockIDQuery.php");

class xdrData
{
    // property declaration
	public $date = '';
	public $value = 0.0;
	public $xdr = '';
	public $xd = 0.0;
	public $xr = 0.0;
	public $xr2 = 0.0;
	public $xr2p = 0.0;
}

/******************** Query Utilities ************************/

function query_xdr_data_by_id_y($id, $year)
{
	$xbrdata = array();

	$query = "SELECT * FROM xdrdata WHERE id = '" . $id . "' and Year(date) = '" . $year . "' ORDER BY date";
	stopwatch_inter();
	$result = mysql_query($query) or die('MySQL query error');

	$ii = 0;
	while($row = mysql_fetch_array($result)){
		$xbrdata[$ii] = new xdrData();
		$xbrdata[$ii]->date = $row['date'];
		$xbrdata[$ii]->value = $row['value'];
		$xbrdata[$ii]->xdr = $row['xdr'];
		$xbrdata[$ii]->xd = $row['xd'];
		$xbrdata[$ii]->xr = $row['xr'];
		$xbrdata[$ii]->xr2 = $row['xr2'];
		$xbrdata[$ii]->xr2p = $row['xr2p'];

		echo_v(DEBUG_VERBOSE, "[query_xdr_data_by_id_y] stock " . $id . " xdr " . $row['value'] . " on " . $row['date']);
		$ii++;
	}
	echo_v(LOG_VERBOSE, stopwatch_inter() . " ms to ". formatstr($query) . "[" . __FUNCTION__ . "]");

	if ($ii==0)
		echo_v(DEBUG_VERBOSE, "[query_xdr_data_by_id_y] There are " . "no" . " xdr in year " . $year . " for id " . $id);
	else
		echo_v(DEBUG_VERBOSE, "[query_xdr_data_by_id_y] There are " . $ii . " xdr in year " . $year . " for id " . $id);

	return $xbrdata;
}

/******************** Test Utilities ************************/

function query_xdr_data()
{
	echo_v(LOG_VERBOSE, "**** query_xdr_data *********************************************");

	// sii.1338 since 2012/12/19
	// sii.1338 on 2014(current-1) -> 3.97 on 2014/6/12

	// otc.3265 since 2008 (2005/08/02, but tpex only offer since 2008)
	// otc.3265 on 2015(current) -> 2.26 (right=0, dividend=2.26) on 2015/05/04

	// otc.5227 since 2013/12/09
	// otc.5227 on 2015(current) -> 1.08 on 2015/01/21

	//$id_array = array('1338', '3265', '5227', '2727', '2867');
	$id_array = array();

	foreach($id_array as $id)
	{
		$nowyyyy = date("Y");
		$stock = query_id_data_by_id($id);

		/* leave if stock not found */
		if ($stock == null)
		{
			echo_v(ERROR_VERBOSE, "[query_xdr_data] stock id " . $id . " is not found!");
			return;
		}
		echo_v(DEBUG_VERBOSE, "[query_xdr_data] stock id " . $id . " is found!");

		$onyyyy = $stock->onyyyy;

		if ($stock->market == 'sii')
		{
			if ((int)$stock->onyyyy < 2003)
				$onyyyy = '2010';
		}
		else if ($stock->market == 'otc')
		{
			if ((int)$stock->onyyyy < 2008)
				$onyyyy = '2010';
		}

		// "future year" expected
		$xdr = query_xdr_data_by_id_y($id, (string)((int)$nowyyyy + 1));

		// "current year" expected
		$xdr = query_xdr_data_by_id_y($id, $nowyyyy);

		// "full year" expected
		$xdr = query_xdr_data_by_id_y($id, (string)((int)$nowyyyy - 1));
		print_r($xdr);

		// "full year" expected
		$xdr = query_xdr_data_by_id_y($id, (string)((int)$onyyyy + 1));
		print_r($xdr);

		// "IPO year" expected
		$xdr = query_xdr_data_by_id_y($id, $onyyyy);

		// "before IPO year" expected
		$xdr = query_xdr_data_by_id_y($id, (string)((int)$onyyyy - 1));
	}
	
	$xdr = query_xdr_data_by_id_y('3376', '2014');
	print_r($xdr);
}

/******************** Entry Function ************************/

function stockXDRQueryTest()
{
	echo_v(LOG_VERBOSE, "");
	echo_v(LOG_VERBOSE, "**********************************************************************");
	echo_v(LOG_VERBOSE, "**** stockXDRQueryTest ***********************************************");
	echo_v(LOG_VERBOSE, "**********************************************************************");
	echo_v(LOG_VERBOSE, "");

	query_xdr_data();
}

?>