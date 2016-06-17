<?php

/*
Filename:
	stockIDQuery.php

Usage:
-) query_id_data() returns all entries
-) query_id_data_sii() returns sii entries
-) query_id_data_otc() returns otc entries
-) query_id_data_by_id($id) return zero/one entry
-) stockIDQueryTest() --> for regression testing

Descriptions:
	This file exports functions to generate SQL script files to insert iddata
table for sii/otc stocks. Following proper sequence of operations:
0) backup previous version of iddata_sii.sql and iddata_otc.sql
1) gen_id_data_sii_sql() generates iddata_sii.sql
2) gen_id_data_otc_sql() generates iddata_otc.sql
3) DROP TABLE iddata
4) CREATE TABLE iddata ( id VARCHAR(10) NOT NULL,
						 name VARCHAR(20) CHARACTER SET utf8,
						 type VARCHAR(20) CHARACTER SET utf8,
						 industry VARCHAR(20) CHARACTER SET utf8,
						 ondate DATE,
						 offdate DATE )
5) run two sql scripts
6) use URL "http://localhost/scripts/stockDB/mysqlTest" to check iddata
7) make sure iddata is mostly ok, next step is daydata

	Here two samples are offered for reference:
1) INSERT INTO iddata (id,name,type,industry,ondate,offdate) VALUES
	('1101', '台泥', '上市', '水泥工業', '1962/02/09', NULL);
2) INSERT INTO iddata (id,name,type,industry,ondate,offdate) VALUES
	('1256', 'F-鮮活', '上櫃', '食品工業', '2012/09/05', NULL);

*/

include_once("LIB_log.php");
include_once("LIB_mysql.php");

class idData
{
    // property declaration
    public $id = '';
	public $name = '';
	public $industry = '';
	public $market = '';
	public $ondate = '';
    public $onyyyy = '';
    public $onmm = '';
}

function query_id_data()
{
	$iddata = array();

	$query = "SELECT * FROM iddata";
	$result = mysql_query($query) or die('MySQL query error');

	while($row = mysql_fetch_array($result)){
		$id = $row['id'];
		$iddata[$id] = new idData();
		$iddata[$id]->id = $id;
		$iddata[$id]->market = $row['market'];
		$iddata[$id]->onyyyy = substr($row['ondate'], 0, 4);
		$iddata[$id]->onmm = substr($row['ondate'], 5, 2);
	}

	echo_v(DEBUG_VERBOSE, "[query_id_data] There are " . count($iddata) . " stocks in table iddata.");
	return $iddata;
}

function query_id_data_sii()
{
	$iddata = array();

	$query = "SELECT * FROM iddata WHERE market = 'sii'";
	$result = mysql_query($query) or die('MySQL query error');

	while($row = mysql_fetch_array($result)){
		$id = $row['id'];
		$iddata[$id] = new idData();
		$iddata[$id]->id = $id;
		$iddata[$id]->market = $row['market'];
		$iddata[$id]->onyyyy = substr($row['ondate'], 0, 4);
		$iddata[$id]->onmm = substr($row['ondate'], 5, 2);
	}

	echo_v(DEBUG_VERBOSE, "[query_id_data_sii] There are " . count($iddata) . " sii stocks in table iddata.");
	return $iddata;
}

function query_id_data_otc()
{
	$iddata = array();

	$query = "SELECT * FROM iddata WHERE market = 'otc'";
	$result = mysql_query($query) or die('MySQL query error');

	while($row = mysql_fetch_array($result)){
		$id = $row['id'];
		$iddata[$id] = new idData();
		$iddata[$id]->id = $id;
		$iddata[$id]->market = $row['market'];
		$iddata[$id]->onyyyy = substr($row['ondate'], 0, 4);
		$iddata[$id]->onmm = substr($row['ondate'], 5, 2);
	}

	echo_v(DEBUG_VERBOSE, "[query_id_data_otc] There are " . count($iddata) . " otc stocks in table iddata.");
	return $iddata;
}

function query_id_data_by_ys($year, $season)
{
	$iddata = array();

	$query = "SELECT * FROM iddata";
	$result = mysql_query($query) or die('MySQL query error');

	while($row = mysql_fetch_array($result)){
		$onyyyy = substr($row['ondate'], 0, 4);
		$onmm = substr($row['ondate'], 5, 2);

		if (((int)$year > (int)$onyyyy) or (((int)$year == (int)$onyyyy)) and ((int)$onmm <= 3*(int)$season))
		{
			$id = $row['id'];
			$iddata[$id] = new idData();
			$iddata[$id]->id = $id;
			$iddata[$id]->market = $row['market'];
			$iddata[$id]->onyyyy = $onyyyy;
			$iddata[$id]->onmm = $onmm;
		}
	}

	echo_v(DEBUG_VERBOSE, "[query_id_data_by_ys] There are " . count($iddata) . " active stocks in " . $year . $season);
	return $iddata;
}

function query_id_data_by_ym($year, $month)
{
	$iddata = array();

	$query = "SELECT * FROM iddata";
	$result = mysql_query($query) or die('MySQL query error');

	while($row = mysql_fetch_array($result)){
		$onyyyy = substr($row['ondate'], 0, 4);
		$onmm = substr($row['ondate'], 5, 2);

		if (((int)$year > (int)$onyyyy) or (((int)$year == (int)$onyyyy)) and ((int)$onmm <= (int)$month))
		{
			$id = $row['id'];
			$iddata[$id] = new idData();
			$iddata[$id]->id = $id;
			$iddata[$id]->market = $row['market'];
			$iddata[$id]->onyyyy = $onyyyy;
			$iddata[$id]->onmm = $onmm;
		}
	}

	echo_v(DEBUG_VERBOSE, "[query_id_data_by_ym] There are " . count($iddata) . " active stocks in " . $year . $month);

	return $iddata;
}

function query_id_data_by_id($id)
{
	$query = "SELECT * FROM iddata WHERE id = " . $id;
	stopwatch_inter();
	$result = mysql_query($query) or die('MySQL query error');

	while($row = mysql_fetch_array($result)){
		if ($id == $row['id'])
		{
			$stock = new idData();
			$stock->id = $id;
			$stock->name = $row['name'];
			$stock->industry = $row['industry'];
			$stock->market = $row['market'];
			$stock->ondate = $row['ondate'];
			$stock->onyyyy = substr($row['ondate'], 0, 4);
			$stock->onmm = substr($row['ondate'], 5, 2);
			echo_v(LOG_VERBOSE, stopwatch_inter() . " ms to ". formatstr($query) . "[" . __FUNCTION__ . "]");
			return $stock;
		}
	}
	echo_v(LOG_VERBOSE, stopwatch_inter() . " ms to ". formatstr($query) . "[" . __FUNCTION__ . "]");
	return null;
}

function query_name_by_id($id)
{
	$name = '';
	$query = "SELECT name FROM iddata WHERE id = " . $id;
	$result = mysql_query($query) or die('MySQL query error');
	while($row = mysql_fetch_array($result)){
		$name = $row['name'];
	}

	if($name == '')
		echo_v(ERROR_VERBOSE, "[query_name_by_id] No stock matched for id " . $id);
	return $name;
}

function query_id_by_name($name)
{
	$id = '';
	$query = "SELECT id FROM iddata WHERE name = '" . $name . "'";
	$result = mysql_query($query) or die('MySQL query error');
	while($row = mysql_fetch_array($result)){
		$id = $row['id'];
	}

	if($id == '')
		echo_v(ERROR_VERBOSE, "[query_id_by_name] No stock matched for name " . $name);
	return $id;
}

/******************** Entry Function ************************/

function stockIDQueryTest()
{
	echo_v(LOG_VERBOSE, "");
	echo_v(LOG_VERBOSE, "**********************************************************************");
	echo_v(LOG_VERBOSE, "**** stockIDQueryTest ************************************************");
	echo_v(LOG_VERBOSE, "**********************************************************************");
	echo_v(LOG_VERBOSE, "");

	$iddata = query_id_data();
	echo_v(LOG_VERBOSE, "Number of all entries: " . count($iddata) . ".");
	$iddata = query_id_data_sii();
	echo_v(LOG_VERBOSE, "Number of sii entries: " . count($iddata) . ".");
	$iddata = query_id_data_otc();
	echo_v(LOG_VERBOSE, "Number of otc entries: " . count($iddata) . ".");

	$id_array = array("1101", "2330", "2333");
	foreach($id_array as $id)
	{
		$stock = query_id_data_by_id($id);

		/* leave if stock not found */
		if ($stock == null)
		{
			echo_v(ERROR_VERBOSE, "[stockIDQueryTest] stock id " . $id . " is not found!");
			return;
		}
		echo_v(DEBUG_VERBOSE, "[stockIDQueryTest] stock id " . $id . " is found!");
		echo_v(DEBUG_VERBOSE, "[stockIDQueryTest] Market of " . $id . " is " . $stock->market);
		echo_v(DEBUG_VERBOSE, "[stockIDQueryTest] 上市年月 of " . $id . " is " . $stock->onyyyy . $stock->onmm);
	}
	
	// 1264 德麥 ondate = 2015/4/9
	// 3416 融程電訊 ondate = 2015/1/23
	// 3266 昇陽建設 ondate = 2014/12/24
	// 8443 阿瘦 ondate = 2014/9/15
	// 2330 聯發科 ondate = 1998
	// 2333 no such id
	// Test what is in query_id_data_by_ys('2015', '01'); // should be 3416, 3266 and 8443 + 2330
	// Test what is in query_id_data_by_ys('2014', '04'); // should be 3266 and 8443 + 2330
	// Test what is in query_id_data_by_ys('2014', '03'); // should be 8443 + 2330
	// Test what is in query_id_data_by_ys('2014', '02'); // should be only 2330

	$id_array = array("1264", "3416", "3266", "8443", "2330", "2333");

	$year_season_list = array(
	'201501',
	'201404', '201403', '201402', '201401',
	'201304', '201303', '201302', '201301');

	foreach($year_season_list as $year_season)
	{
		$year = substr($year_season, 0, 4);
		$season = substr($year_season, 4, 2);
		
		$iddata = query_id_data_by_ys($year, $season);
		foreach ($id_array as $id)
		{
			foreach ($iddata as $stock)
			{
				if ($id == $stock->id)
					echo_v(LOG_VERBOSE, $id . " is found in " . $year_season);
			}
		}
	}
}

?>