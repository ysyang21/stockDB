﻿<?php

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
	global $conn;
	$query = "SELECT * FROM iddata WHERE report = 'ci-cr' OR report = 'ci-ir'";
	$result = mysqli_query($conn, $query) or die('MySQL query error');

	$iddata = array();
	while($row = mysqli_fetch_array($result)){
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

function query_id_data_new_month()
{
	global $conn;
	// 輸入日期, 按照財報死線推算肯定已經在monthdata的最新月營收月份季度
	$latest_month = get_latest_scheduled_month(today());

	// 例如說今天是某月9日, 雖然還沒到公布上月月營收的死線, 但是我們推論它很可能已經被更新到 monthdata當中了
	$one_month_before_latest_month = forward_month($latest_month);

	$query = "SELECT * FROM iddata WHERE (report = 'ci-cr' OR report = 'ci-ir') AND id in (SELECT id FROM monthdata WHERE month = " . $one_month_before_latest_month . ")";

	$result = mysqli_query($conn, $query) or die('MySQL query error');

	$iddata = array();
	while($row = mysqli_fetch_array($result)){
		$id = $row['id'];
		$iddata[$id] = new idData();
		$iddata[$id]->id = $id;
		$iddata[$id]->market = $row['market'];
		$iddata[$id]->onyyyy = substr($row['ondate'], 0, 4);
		$iddata[$id]->onmm = substr($row['ondate'], 5, 2);
	}

	echo_v(DEBUG_VERBOSE, "[query_id_data_new_month] There are " . count($iddata) . " stocks in table iddata.");
	return $iddata;
}

// For example, it's 2016/8/16, IFRS 2Q reports shall be there for all sii/otc but always some companies postpone
// This query is here to sieve those ids. You can use gradeStocks to do experiment on this query
function query_id_data_lack_latest_season()
{
	global $conn;
	$latest_season = get_latest_scheduled_season(today());
	$one_season_before_latest_season = backward_season($latest_season);

	$query = "SELECT * FROM iddata WHERE (report = 'ci-cr' OR report = 'ci-ir') " .
		"AND id in (SELECT id FROM xbrldata WHERE season = '" . $one_season_before_latest_season . "') " .
		"AND id not in (SELECT id FROM xbrldata WHERE season = '" . $latest_season . "')";
	echo $query . "<br>\n";

	$result = mysqli_query($conn, $query) or die('MySQL query error');

	$iddata = array();
	while($row = mysqli_fetch_array($result)){
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

function query_id_data_by_season($season)
{
	global $conn;
	$query = "SELECT * FROM iddata WHERE (report = 'ci-cr' OR report = 'ci-ir') AND id in (SELECT id FROM xbrldata WHERE season = " . $season . ")";

	$result = mysqli_query($query) or die('MySQL query error');

	$iddata = array();
	while($row = mysqli_fetch_array($result)){
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

function query_id_data_latest_season()
{
	$latest_season = get_latest_scheduled_season(today());

	$query = "SELECT * FROM iddata WHERE (report = 'ci-cr' OR report = 'ci-ir') AND id in (SELECT id FROM xbrldata WHERE season = " . $latest_season . ")";

	$result = mysqli_query($conn, $query) or die('MySQL query error');

	$iddata = array();
	while($row = mysqli_fetch_array($result)){
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

function query_id_data_new_season()
{
	global $conn;
	$latest_season = get_latest_scheduled_season(today());
	$might_have_been_published_season = forward_season($latest_season);

	$query = "SELECT * FROM iddata WHERE (report = 'ci-cr' OR report = 'ci-ir') AND id in (SELECT id FROM xbrldata WHERE season = " . $might_have_been_published_season . ")";

	$result = mysqli_query($conn, $query) or die('MySQL query error');

	$iddata = array();
	while($row = mysqli_fetch_array($result)){
		$id = $row['id'];
		$iddata[$id] = new idData();
		$iddata[$id]->id = $id;
		$iddata[$id]->market = $row['market'];
		$iddata[$id]->onyyyy = substr($row['ondate'], 0, 4);
		$iddata[$id]->onmm = substr($row['ondate'], 5, 2);
	}

	echo_v(DEBUG_VERBOSE, "[query_id_data_new_season] There are " . count($iddata) . " stocks in table iddata.");
	return $iddata;
}

function query_id_data_sii()
{
	global $conn;
	$iddata = array();

	$query = "SELECT * FROM iddata WHERE market = 'sii'";
	$result = mysqli_query($conn, $query) or die('MySQL query error');

	while($row = mysqli_fetch_array($result)){
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
	global $conn;
	$iddata = array();

	$query = "SELECT * FROM iddata WHERE market = 'otc'";
	$result = mysqli_query($conn, $query) or die('MySQL query error');

	while($row = mysqli_fetch_array($result)){
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
	global $conn;
	$iddata = array();

	$query = "SELECT * FROM iddata";
	$result = mysqli_query($conn, $query) or die('MySQL query error');

	while($row = mysqli_fetch_array($result)){
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
	global $conn;
	$iddata = array();

	$query = "SELECT * FROM iddata";
	$result = mysqli_query($conn, $query) or die('MySQL query error');

	while($row = mysqli_fetch_array($result)){
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
	global $conn;
	$query = "SELECT * FROM iddata WHERE id = " . $id;
	stopwatch_inter();
	$result = mysqli_query($conn, $query) or die('MySQL query error');

	while($row = mysqli_fetch_array($result)){
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
	global $conn;
	$name = '';
	$query = "SELECT name FROM iddata WHERE id = " . $id;
	$result = mysqli_query($conn, $query) or die('MySQL query error');
	while($row = mysqli_fetch_array($result)){
		$name = $row['name'];
	}

	if($name == '')
		echo_v(ERROR_VERBOSE, "[query_name_by_id] No stock matched for id " . $id);
	return $name;
}

function query_id_by_name($name)
{
	global $conn;
	$id = '';
	$query = "SELECT id FROM iddata WHERE name = '" . $name . "'";
	$result = mysqli_query($conn, $query) or die('MySQL query error');
	while($row = mysqli_fetch_array($result)){
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