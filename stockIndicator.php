<?php

/*
Filename:
	stockEvaluate.php

Usage:


Descriptions:
	This file is used to implement stock price evaluation process.
*/

include_once("LIB_log.php");

include_once("xbrlQuery.php");
include_once("stockMonthQuery.php");
include_once("stockWebpage.php");

function stockIndicators($id) //, $since_date, $id_prices, $id_yoys, $sii_kline)
{
	$stock = query_id_data_by_id($id);
	
	if ($stock == null)
	{
		echo_v(ERROR_VERBOSE, "[stockEvaluateTest] stock id " . $id . " is not found!");
		return;
	}

	echo '  <table>' . "\n";
	echo '    <tbody>' . "\n";
	echo '      <tr>' . "\n";
	echo '        <td>' . "\n";

	// 股票簡介及近況
	show_stock_brief_case($stock);

	// 最近至少八季財務報表
	$xbrls = load_seasonly_xbrl($id);
	show_xbrl_bonddealer($xbrls);

	// 最近十二個月月營收
	$month = load_monthly_revenue($id);
	show_monthly_revenue($month);

	echo '        </td>' . "\n";
	echo '      </tr>' . "\n";
	echo '    </tbody>' . "\n";
	echo '  </table><br>' . "\n";
}

?>