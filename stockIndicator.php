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

	echo_n ("<div class='container'>");

	// 股票簡介及近況
	echo_n ("<div class='profile'>");
	show_stock_brief_case($stock);
	echo_n ("</div>"); // end of profile

	// 最近(n+2)財務年表
	$xbrly = load_yearly_xbrl($id, 4);

	// 最近(m+8+8)季財務報表
	$xbrls = load_seasonly_xbrl($id, 8);
	$verdicts = calculate_verdicts($xbrls);

	$since_date = $xbrls[0]['current']->publish;
	$prices = query_day_price_lochs_by_id_since($id, $since_date);
	echo_n ("<div class='xbrls'>");
	show_stock_candlestick_chart($id, $prices);
	show_stock_bar_chart($id, $prices);
	echo_n ("</div>"); // end of xbrls

	// 最近18個月月營收報表
	$months = load_monthly_revenue($id, 18);
	$verdictm = calculate_verdictm($months);

	echo_n ("<div class='xbrls'>");
	show_xbrl_core($xbrls, $verdicts);
	echo_n ("</div>"); // end of xbrls
	echo_n ("<div class='monthly'>");
	show_monthly_revenue($months, $verdictm);
	echo_n ("</div>"); // end of monthly

	// echo_n ("<div class='xbrly'>");
	// show_yearly_xbrl($xbrly);
	// echo_n ("</div>"); // end of xbrly
	// echo_n ("<div class='xbrls'>");
	// show_seasonly_xbrl($xbrls);
	// echo_n ("</div>"); // end of xbrls

	// echo_n ("<div class='xbrls'>");
	// show_xbrl_group_a($xbrls);
	// echo_n ("</div>"); // end of xbrls
	// echo_n ("<div class='xbrls'>");
	// show_xbrl_group_b($xbrls);
	// echo_n ("</div>"); // end of xbrls
	// echo_n ("<div class='xbrls'>");
	// show_xbrl_group_c($xbrls);
	// echo_n ("</div>"); // end of xbrls
	// echo_n ("<div class='xbrls'>");
	// show_xbrl_group_d($xbrls);
	// echo_n ("</div>"); // end of xbrls
	// echo_n ("<div class='xbrls'>");
	// show_xbrl_group_e($xbrls);
	// echo_n ("</div>"); // end of xbrls

	echo_n ("</div>"); // end of container
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

function gradedStocks($showgrade = '-1')
{
	$ids = array_keys(query_id_data());

	$jj = 1;
	$gradings = array();
	$statics = array();
	foreach($ids as $id)
	{
		if ($id == '')
			continue;

		$verdicts = stockIndicatorsVerdict($id);
		// It is possible that $verdicts is a null, so need a check here
		if ($verdicts != null) // and $latest_verdict_of_this_id>8)
		{
			$latest_verdict_of_this_id = $verdicts[0]->verdict;
			$gradings[$id] = $latest_verdict_of_this_id;
			if (array_key_exists($latest_verdict_of_this_id, $statics))
				$statics[$latest_verdict_of_this_id]++;
			else
				$statics[$latest_verdict_of_this_id]=1;
		}

		// if ($jj>=10)
		// 	break;
		$jj++;
	}

	arsort($gradings);

	$first = array_values($gradings)[0];
	$last = end($gradings);

	for ($ii=$first;$ii>=$last;$ii--)
	{
		if ($ii == (int)$showgrade or '-1'== $showgrade)
		{
			if (array_key_exists($ii, $statics))
				echo_n ("<div class='stock$ii'><a>$ii 分(共" . $statics[$ii] . "個)(按我可重複收合或展開)</a></div>");
		}
	}

	$jj = 1;
	foreach($gradings as $id => $grading)
	{
		if ($grading == $showgrade or '-1'== $showgrade)
		{
			echo_n ("<div class='stock" . $gradings[$id] . "'>");
			stockIndicators($id);
			echo_n ("</div>"); // end of stockx

			// if ($jj>=10)
			// 	break;
			$jj++;
		}
	}
}

?>