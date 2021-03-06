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
		echo_v (ERROR_VERBOSE, "[stockIndicators] " . $id . " is not on my radar since ipo less then two years.</div>");
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

function gradeStocks($showgrade = -1)
{
	$ids = array_keys(query_id_data_latest_season());

	if ($ids == null or count($ids) == 0)
	{
		echo_v(ERROR_VERBOSE, "[gradeStocks] no new reports in latest season!");
		return;
	}

	$grades = array();
	$statics = array();
	$jj = 1;
	foreach($ids as $id)
	{
		if ($id == '')
			continue;

		$verdict = stockIndicatorsVerdict($id);

		$grades[$id] = $verdict;
		if (array_key_exists($verdict, $statics))
			$statics[$verdict]++;
		else
			$statics[$verdict]=1;

		// if ($jj>=10)
		// 	break;
		$jj++;
	}

	arsort($grades);

	$first = array_values($grades)[0];
	$last = end($grades);

	for ($grade=$first;$grade>=$last;$grade--)
	{
		if ($grade == (int)$showgrade or (int)$showgrade == -1)
		{
			if (array_key_exists($grade, $statics))
				echo_n ("<div class='stock$grade'><a>$grade 分(共" . $statics[$grade] . "個)(按我可重複收合或展開)</a></div>");
		}
	}

	$jj = 1;
	foreach($grades as $id => $grade)
	{
		if ($grade == (int)$showgrade or (int)$showgrade == -1)
		{
			echo_n ("<div class='stock$grade'>");
			stockIndicators($id, 'rookie'); // rookie, normal, expert
			echo_n ("</div>"); // end of stockx

			// if ($jj>=10)
			// 	break;
			$jj++;
		}
	}
}

function gradeNewSeasonStocks($showgrade = -1)
{
	$ids = array_keys(query_id_data_new_season());

	if ($ids == null or count($ids) == 0)
	{
		echo_v(ERROR_VERBOSE, "[gradeNewSeasonStocks] no new reports in new season!");
		return;
	}

	$grades = array();
	$statics = array();
	$jj = 1;
	foreach($ids as $id)
	{
		if ($id == '')
			continue;

		$verdict = stockIndicatorsVerdict($id);

		$grades[$id] = $verdict;
		if (array_key_exists($verdict, $statics))
			$statics[$verdict]++;
		else
			$statics[$verdict]=1;

		// if ($jj>=10)
		// 	break;
		$jj++;
	}

	arsort($grades);

	$first = array_values($grades)[0];
	$last = end($grades);

	for ($grade=$first;$grade>=$last;$grade--)
	{
		if ($grade == (int)$showgrade or (int)$showgrade == -1)
		{
			if (array_key_exists($grade, $statics))
				echo_n ("<div class='stock$grade'><a>$grade 分(共" . $statics[$grade] . "個)(按我可重複收合或展開)</a></div>");
		}
	}

	$jj = 1;
	foreach($grades as $id => $grade)
	{
		if ($grade == (int)$showgrade or (int)$showgrade == -1)
		{
			echo_n ("<div class='stock" . $grades[$id] . "'>");
			stockIndicators($id, 'rookie'); // rookie, normal, expert
			echo_n ("</div>"); // end of stockx

			// if ($jj>=10)
			// 	break;
			$jj++;
		}
	}
}

function gradeNewMonthStocks($showgrade = -1)
{
	$ids = array_keys(query_id_data_new_month());

	if ($ids == null or count($ids) == 0)
	{
		echo_v(ERROR_VERBOSE, "[gradeNewMonthStocks] no new reports in new month!");
		return;
	}

	$grades = array();
	$statics = array();
	$jj = 1;
	foreach($ids as $id)
	{
		if ($id == '')
			continue;

		$verdict = stockIndicatorsVerdict($id);

		$grades[$id] = $verdict;
		if (array_key_exists($verdict, $statics))
			$statics[$verdict]++;
		else
			$statics[$verdict]=1;

		// if ($jj>=10)
		// 	break;
		$jj++;
	}

	arsort($grades);

	$first = array_values($grades)[0];
	$last = end($grades);

	for ($grade=$first;$grade>=$last;$grade--)
	{
		if ($grade == (int)$showgrade or (int)$showgrade == -1)
		{
		if (array_key_exists($grade, $statics))
			echo_n ("<div class='stock$grade'><a>$grade 分(共" . $statics[$grade] . "個)(按我可重複收合或展開)</a></div>");
		}
	}

	$jj = 1;
	foreach($grades as $id => $grade)
	{
		if ($grade == (int)$showgrade or (int)$showgrade == -1)
		{
			echo_n ("<div class='stock" . $grades[$id] . "'>");
			stockIndicators($id, 'rookie'); // rookie, normal, expert
			echo_n ("</div>"); // end of stockx

			// if ($jj>=10)
			// 	break;
			$jj++;
		}
	}
}

function backTesting()
{
	// point season to latest scheduled season
	$season = get_latest_scheduled_season(today());

	// from 201602 to 201001, there are 27 seasons to analyse
	$ids = array();
	for ($ii=0;$ii<1;$ii++)
	{
		$ids = array_keys(query_id_data_by_season($season));
		echo "The season $season has " . count($ids) . " xbrl reports, oh yeah!<br>\n";

		$season = backward_season($season);
	}

	if ($ids == null or count($ids) == 0)
		return;

	$grades = array();
	$statics = array();
	$jj = 1;
	foreach($ids as $id)
	{
		if ($id == '')
			continue;

		$verdict = stockIndicatorsVerdict($id);

		$grades[$id] = $verdict;
		if (array_key_exists($verdict, $statics))
			$statics[$verdict]++;
		else
			$statics[$verdict]=1;

		// if ($jj>=10)
		// 	break;
		$jj++;
	}

	arsort($grades);

	$first = array_values($grades)[0];
	$last = end($grades);

	print_r($grades);

/*
	for ($ii=$first;$ii>=$last;$ii--)
	{
		// if ($ii == (int)$showgrade)
		// {
			if (array_key_exists($ii, $statics))
				echo_n ("<div class='stock$ii'><a>$ii 分(共" . $statics[$ii] . "個)(按我可重複收合或展開)</a></div>");
		// }
	}

	$jj = 1;
	foreach($grades as $id => $grading)
	{
		// if ($grading == $showgrade)
		// {
			echo_n ("<div class='stock" . $grades[$id] . "'>");
			stockIndicators($id, 'rookie'); // rookie, normal, expert
			echo_n ("</div>"); // end of stockx

			if ($jj>=10)
				break;
			$jj++;
		// }
	}
*/
}

?>