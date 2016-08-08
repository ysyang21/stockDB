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

function stockIndicators($id, $level)
{
	$stock = query_id_data_by_id($id);
	
	if ($stock == null)
	{
		echo_v(ERROR_VERBOSE, "[stockIndicators] stock id " . $id . " is not found!");
		return;
	}

	echo_n ("<div class='container'>");

	// 最近(n+2)財務年表
	$xbrly = load_yearly_xbrl($id, 4);
	if (count($xbrly) < 2)
	{
		echo_v (ERROR_VERBOSE, "[stockIndicators] " . $id . " is not on my radar since ipo less then two years.");
		return;
	}

	// 最近(m+8+8)季財務報表
	$xbrls = load_seasonly_xbrl($id, 8);
	$verdicts = calculate_verdicts($xbrls);

	// 最近18個月月營收報表
	$months = load_monthly_revenue($id, 18);
	$verdictm = calculate_verdictm($months);

	$verdict = calculate_verdict($verdicts, $verdictm);

	// 股票簡介及近況
	echo_n ("<div class='profile'>");
	show_stock_brief_case($stock, $verdict);
	echo_n ("</div>"); // end of profile

	$since_date = $xbrls[0]['current']->publish;
	$prices = query_day_price_lochs_by_id_since($id, $since_date);
	echo_n ("<div class='xbrls'>");
	show_stock_candlestick_chart($id, $prices);
	show_stock_bar_chart($id, $prices);
	echo_n ("</div>"); // end of xbrls

	if ($level == 'expert' or $level == 'normal' or $level == 'rookie')
	{
		echo_n ("<div class='xbrls'>");
		show_xbrl_core($xbrls, $verdicts);
		echo_n ("</div>"); // end of xbrls
		echo_n ("<div class='monthly'>");
		show_monthly_revenue($months, $verdictm);
		echo_n ("</div>"); // end of monthly
	}

	if ($level == 'expert' or $level == 'normal')
	{
		echo_n ("<div class='xbrly'>");
		show_yearly_xbrl($xbrly);
		echo_n ("</div>"); // end of xbrly
		echo_n ("<div class='xbrls'>");
		show_seasonly_xbrl($xbrls);
		echo_n ("</div>"); // end of xbrls
	}

	if ($level == 'expert')
	{
		echo_n ("<div class='xbrls'>");
		show_xbrl_group_a($xbrls);
		echo_n ("</div>"); // end of xbrls
		echo_n ("<div class='xbrls'>");
		show_xbrl_group_b($xbrls);
		echo_n ("</div>"); // end of xbrls
		echo_n ("<div class='xbrls'>");
		show_xbrl_group_c($xbrls);
		echo_n ("</div>"); // end of xbrls
		echo_n ("<div class='xbrls'>");
		show_xbrl_group_d($xbrls);
		echo_n ("</div>"); // end of xbrls
		echo_n ("<div class='xbrls'>");
		show_xbrl_group_e($xbrls);
		echo_n ("</div>"); // end of xbrls
	}

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
	$verdicts = calculate_verdicts($xbrls);

	// 最近18個月月營收報表
	$months = load_monthly_revenue($id, 18);
	$verdictm = calculate_verdictm($months);

	$verdict = calculate_verdict($verdicts, $verdictm);

	return $verdict;
}

function gradedStocks($showgrade)
{
	$ids = array_keys(query_id_data());

	$gradings = array();
	$statics = array();
	$jj = 1;
	foreach($ids as $id)
	{
		if ($id == '')
			continue;

		$verdict = stockIndicatorsVerdict($id);

		$latest_verdict_of_this_id = $verdict;
		$gradings[$id] = $latest_verdict_of_this_id;
		if (array_key_exists($latest_verdict_of_this_id, $statics))
			$statics[$latest_verdict_of_this_id]++;
		else
			$statics[$latest_verdict_of_this_id]=1;

		// if ($jj>=10)
		// 	break;
		$jj++;
	}

	arsort($gradings);

	$first = array_values($gradings)[0];
	$last = end($gradings);

	for ($ii=$first;$ii>=$last;$ii--)
	{
		if ($ii == (int)$showgrade)
		{
			if (array_key_exists($ii, $statics))
				echo_n ("<div class='stock$ii'><a>$ii 分(共" . $statics[$ii] . "個)(按我可重複收合或展開)</a></div>");
		}
	}

	$jj = 1;
	foreach($gradings as $id => $grading)
	{
		if ($grading == $showgrade)
		{
			echo_n ("<div class='stock" . $gradings[$id] . "'>");
			stockIndicators($id, 'rookie'); // rookie, normal, expert
			echo_n ("</div>"); // end of stockx

			// if ($jj>=10)
			// 	break;
			$jj++;
		}
	}
}

function gradedNewStocks($tag = '')
{
	$ids = array();

	if ($tag == 'newmoon')
		$ids = array_keys(query_id_data_new_moon());
	else if ($tag == 'newseason')
		$ids = array_keys(query_id_data_new_season());
	else
		echo_v(ERROR_VERBOSE, "[gradedNewStocks] invalid tag!");

	if (count($ids) == 0)
		return;

	$gradings = array();
	$statics = array();
	$jj = 1;
	foreach($ids as $id)
	{
		if ($id == '')
			continue;

		$verdict = stockIndicatorsVerdict($id);

		$latest_verdict_of_this_id = $verdict;
		$gradings[$id] = $latest_verdict_of_this_id;
		if (array_key_exists($latest_verdict_of_this_id, $statics))
			$statics[$latest_verdict_of_this_id]++;
		else
			$statics[$latest_verdict_of_this_id]=1;

		// if ($jj>=10)
		// 	break;
		$jj++;
	}

	arsort($gradings);

	$first = array_values($gradings)[0];
	$last = end($gradings);

	for ($ii=$first;$ii>=$last;$ii--)
	{
		if (array_key_exists($ii, $statics))
			echo_n ("<div class='stock$ii'><a>$ii 分(共" . $statics[$ii] . "個)(按我可重複收合或展開)</a></div>");
	}

	$jj = 1;
	foreach($gradings as $id => $grading)
	{
		echo_n ("<div class='stock" . $gradings[$id] . "'>");
		stockIndicators($id, 'rookie'); // rookie, normal, expert
		echo_n ("</div>"); // end of stockx

		// if ($jj>=10)
		// 	break;
		$jj++;
	}
}

function backTesting()
{
	$ids = array_keys(query_id_data());

	$gradings = array();
	$statics = array();
	$jj = 1;
	foreach($ids as $id)
	{
		if ($id == '')
			continue;

		$verdict = stockIndicatorsVerdict($id);

		$latest_verdict_of_this_id = $verdict;
		$gradings[$id] = $latest_verdict_of_this_id;
		if (array_key_exists($latest_verdict_of_this_id, $statics))
			$statics[$latest_verdict_of_this_id]++;
		else
			$statics[$latest_verdict_of_this_id]=1;

		if ($jj>=10)
			break;
		$jj++;
	}

	arsort($gradings);

	$first = array_values($gradings)[0];
	$last = end($gradings);

	for ($ii=$first;$ii>=$last;$ii--)
	{
		// if ($ii == (int)$showgrade)
		// {
			if (array_key_exists($ii, $statics))
				echo_n ("<div class='stock$ii'><a>$ii 分(共" . $statics[$ii] . "個)(按我可重複收合或展開)</a></div>");
		// }
	}

	$jj = 1;
	foreach($gradings as $id => $grading)
	{
		// if ($grading == $showgrade)
		// {
			echo_n ("<div class='stock" . $gradings[$id] . "'>");
			stockIndicators($id, 'rookie'); // rookie, normal, expert
			echo_n ("</div>"); // end of stockx

			if ($jj>=10)
				break;
			$jj++;
		// }
	}
}

?>