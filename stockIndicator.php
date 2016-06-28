<?php

/*
Filename:
	stockIndicators.php

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
		echo_v(ERROR_VERBOSE, "[stockIndicators] stock id " . $id . " is not found!");
		return;
	}

	echo '  <table>' . "\n";
	echo '    <tbody>' . "\n";
	echo '      <tr>' . "\n";
	echo '        <td>' . "\n";

	// 股票簡介及近況
	show_stock_brief_case($stock);

	// 最近(m+8+8)季財務報表
	$xbrls = load_seasonly_xbrl($id, 8);

	// 最近幾年財務年表
	$xbrly = load_yearly_xbrl($id, 8);

	// 最近18個月月營收報表
	$months = load_monthly_revenue($id, 18);

	show_yearly_xbrl($xbrly);
	show_seasonly_xbrl($xbrls);
	show_monthly_revenue($months);
	show_xbrl_core($xbrls);
	show_xbrl_group_a($xbrls);
	show_xbrl_group_b($xbrls);
	show_xbrl_group_c($xbrls);
	show_xbrl_group_d($xbrls);
	show_xbrl_group_e($xbrls);

	echo '        </td>' . "\n";
	echo '      </tr>' . "\n";
	echo '    </tbody>' . "\n";
	echo '  </table><br>' . "\n";
}

?>