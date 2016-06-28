<?php

/*
Filename:
	stockIndicators.php

Usage:


Descriptions:
	This file is used to implement stock price indicators calculation process.
*/

include_once("LIB_log.php");

include_once("xbrlQuery.php");
include_once("stockMonthQuery.php");
include_once("stockWebpage.php");
include_once("stockVerdict.php");

function stockIndicators($id)
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

	// 最近(n+2)財務年表
	$xbrly = load_yearly_xbrl($id, 4);

	// 最近(m+8+8)季財務報表
	$xbrls = load_seasonly_xbrl($id, 8);
	$verdicts = calculate_verdicts($xbrls);

	// 最近18個月月營收報表
	$months = load_monthly_revenue($id, 18);
	$verdictm = calculate_verdictm($months);

	show_yearly_xbrl($xbrly);
	show_seasonly_xbrl($xbrls);
	show_monthly_revenue($months, $verdictm);
	show_xbrl_core($xbrls, $verdicts);
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

function stockIndicatorsVerdict($id)
{
	$stock = query_id_data_by_id($id);
	
	if ($stock == null)
	{
		echo_v(ERROR_VERBOSE, "[stockIndicatorsVerdict] stock id " . $id . " is not found!");
		return;
	}

	// 最近(m+8+8)季財務報表
	$xbrls = load_seasonly_xbrl($id, 8);
	// if (count($xbrls)==1)
	// 	echo $id . "<br>";
	$verdicts = calculate_verdicts($xbrls);

	return $verdicts;
}

?>