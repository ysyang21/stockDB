<?php

/*
Filename:
	stockVerdict.php

Usage:


Descriptions:
	Calculate verdict according to business report and month revenue report
*/

class verdictmData
{
	public $month = "";	// 月份

	public $月營收年增率遞增 = TRUE;
}

function calculate_verdictm($months)
{
	$verdictm = array();

	for ($ii=0;$ii<count($months);$ii++)
	{
		$verdictm[$ii] = new verdictmData();
		$verdictm[$ii]->month = $months[$ii]->month;
	}

	for ($ii=0;$ii<count($months)-2;$ii++)
	{
		for ($jj=$ii;$jj<$ii+2;$jj++)
		{
			if (($months[$jj]->corresp==0) or ($months[$jj]->corresp==0))
			{
				$verdictm[$ii]->月營收年增率遞增 = FALSE;
				break;
			}
			if (($months[$jj]->current<0) or ($months[$jj]->corresp<0))
			{
				$verdictm[$ii]->月營收年增率遞增 = FALSE;
				break;
			}
			if (($months[$jj+1]->corresp==0) or ($months[$jj+1]->corresp==0))
			{
				$verdictm[$ii]->月營收年增率遞增 = FALSE;
				break;
			}
			if (($months[$jj+1]->current<0) or ($months[$jj+1]->corresp<0))
			{
				$verdictm[$ii]->月營收年增率遞增 = FALSE;
				break;
			}
			if ($months[$jj]->current/$months[$jj]->corresp < $months[$jj+1]->current/$months[$jj+1]->corresp)
			{
				$verdictm[$ii]->月營收年增率遞增 = FALSE;
				break;
			}
		}
	}

	return $verdictm;
}

class verdictsData
{
	public $season = "";	// 季度

	public $每股盈餘為正 = TRUE;
	public $每股盈餘成長 = TRUE;
	public $營收成長 = TRUE;
	public $營業利益成長 = TRUE;
	public $稅後淨利成長 = TRUE;
	public $營業利益率穩定 = TRUE;
	public $累計現金流量正遞增 = TRUE;
	public $存貨週轉率沒下降 = TRUE;
}

function calculate_verdicts($xbrls)
{
	if (count($xbrls)==0)
		return null;

	$verdicts = array();

	for ($ii=0;$ii<count($xbrls);$ii++)
	{
		$verdicts[$ii] = new verdictsData();
		$verdicts[$ii]->season = $xbrls[$ii]['current']->season;
	}

	for ($ii=0;$ii<count($xbrls)-4;$ii++)
	{
		for ($jj=$ii;$jj<$ii+4;$jj++)
		{
			if ($xbrls[$jj]['current']->eps < 0) {
				$verdicts[$ii]->每股盈餘為正 = FALSE;
				break;
			}
		}

		for ($jj=$ii;$jj<$ii+3;$jj++)
		{
			if (($xbrls[$jj]['current']->eps==0) or ($xbrls[$jj]['corresp']->eps==0))
			{
				$verdicts[$ii]->每股盈餘成長 = FALSE;
				break;
			}
			if (($xbrls[$jj]['current']->eps<0) or ($xbrls[$jj]['corresp']->eps<0))
			{
				$verdicts[$ii]->每股盈餘成長 = FALSE;
				break;
			}
			if (($xbrls[$jj]['current']->eps < $xbrls[$jj]['corresp']->eps)) {
				$verdicts[$ii]->每股盈餘成長 = FALSE;
				break;
			}
		}

		for ($jj=$ii;$jj<$ii+3;$jj++)
		{
			if (($xbrls[$jj]['current']->revenue==0) or ($xbrls[$jj]['corresp']->revenue==0))
			{
				$verdicts[$ii]->營收成長 = FALSE;
				break;
			}
			if (($xbrls[$jj]['current']->revenue<0) or ($xbrls[$jj]['corresp']->revenue<0))
			{
				$verdicts[$ii]->營收成長 = FALSE;
				break;
			}
			if (($xbrls[$jj]['current']->revenue < $xbrls[$jj]['corresp']->revenue)) {
				$verdicts[$ii]->營收成長 = FALSE;
				break;
			}
		}

		for ($jj=$ii;$jj<$ii+3;$jj++)
		{
			if (($xbrls[$jj]['current']->income==0) or ($xbrls[$jj]['corresp']->income==0))
			{
				$verdicts[$ii]->營業利益成長 = FALSE;
				break;
			}
			if (($xbrls[$jj]['current']->income<0) or ($xbrls[$jj]['corresp']->income<0))
			{
				$verdicts[$ii]->營業利益成長 = FALSE;
				break;
			}
			if (($xbrls[$jj]['current']->income < $xbrls[$jj]['corresp']->income)) {
				$verdicts[$ii]->營業利益成長 = FALSE;
				break;
			}
		}

		for ($jj=$ii;$jj<$ii+3;$jj++)
		{
			if (($xbrls[$jj]['current']->nopat==0) or ($xbrls[$jj]['corresp']->nopat==0))
			{
				$verdicts[$ii]->稅後淨利成長 = FALSE;
				break;
			}
			if (($xbrls[$jj]['current']->nopat<0) or ($xbrls[$jj]['corresp']->nopat<0))
			{
				$verdicts[$ii]->稅後淨利成長 = FALSE;
				break;
			}
			if (($xbrls[$jj]['current']->nopat < $xbrls[$jj]['corresp']->nopat)) {
				$verdicts[$ii]->稅後淨利成長 = FALSE;
				break;
			}
		}

		for ($jj=$ii;$jj<$ii+3;$jj++)
		{
			$current_income = $xbrls[$jj]['current']->income;
			$current_revenue = $xbrls[$jj]['current']->revenue;

			if ($current_income == 0 or $current_revenue == 0) {
				$verdicts[$ii]->營業利益率穩定 = FALSE;
				break;
			}

			if ($current_income < 0 or $current_revenue < 0) {
				$verdicts[$ii]->營業利益率穩定 = FALSE;
				break;
			}

			$earlier_income = $xbrls[$jj+1]['current']->income;
			$earlier_revenue = $xbrls[$jj+1]['current']->revenue;

			if ($earlier_income == 0 or $earlier_revenue == 0) {
				$verdicts[$ii]->營業利益率穩定 = FALSE;
				break;
			}

			if ($earlier_income < 0 or $earlier_revenue < 0) {
				$verdicts[$ii]->營業利益率穩定 = FALSE;
				break;
			}

			$current_rate = $current_income/$current_revenue;
			$earlier_rate = $earlier_income/$earlier_revenue;

			if ($current_rate == 0 or $earlier_rate == 0) {
				$verdicts[$ii]->營業利益率穩定 = FALSE;
				break;
			}

			if ($current_rate < 0 or $earlier_rate < 0) {
				$verdicts[$ii]->營業利益率穩定 = FALSE;
				break;
			}

			if (($current_rate / $earlier_rate) < 0.85) {
				$verdicts[$ii]->營業利益率穩定 = FALSE;
				break;
			}
		}

		for ($jj=$ii;$jj<$ii+4;$jj++)
		{
			$current = $xbrls[$ii]['current'];
			$earlier = $xbrls[$ii+1]['current'];
			$earlierer = $xbrls[$ii+2]['current'];

			$current_inventories = ($current->inventory+$earlier->inventory)/2;
			$earlier_inventories = ($earlier->inventory+$earlierer->inventory)/2;

			if ($current->costs == 0 or $current_inventories == 0) {
				$verdicts[$ii]->存貨週轉率沒下降 = FALSE;
				break;
			}

			if ($current->costs < 0 or $current_inventories < 0) {
				$verdicts[$ii]->存貨週轉率沒下降 = FALSE;
				break;
			}

			if ($earlier->costs == 0 or $earlier_inventories == 0) {
				$verdicts[$ii]->存貨週轉率沒下降 = FALSE;
				break;
			}

			if ($earlier->costs < 0 or $earlier_inventories < 0) {
				$verdicts[$ii]->存貨週轉率沒下降 = FALSE;
				break;
			}

			$current_turnover = $current->costs/$current_inventories;
			$earlier_turnover = $earlier->costs/$earlier_inventories;

			if ($current_turnover == 0 or $earlier_turnover == 0) {
				$verdicts[$ii]->存貨週轉率沒下降 = FALSE;
				break;
			}

			if ($current_turnover < 0 or $earlier_turnover < 0) {
				$verdicts[$ii]->存貨週轉率沒下降 = FALSE;
				break;
			}

			if (($current_turnover / $earlier_turnover) < 0.85) {
				$verdicts[$ii]->存貨週轉率沒下降 = FALSE;
				break;
			}
		}
	}

	$start = $xbrls[0]['current'];
	$start_season = $start->season;
	if ('04' == substr($start_season, 4, 2))
	{
		if ($start->cashoa+$start->cashia < 0)
		{
			$verdicts[0]->累計現金流量正遞增 = FALSE;
		}
		else
		{
			if (count($xbrls)<4) // at least 4 items
			{
				$verdicts[0]->累計現金流量正遞增 = FALSE;
			}
			else
			{
				for ($ii=0;$ii<3;$ii++)
				{
					$current = $xbrls[$ii]['current'];
					$earlier = $xbrls[$ii+1]['current'];
					if ( ($current->cashoa+$current->cashia) < ($earlier->cashoa+$earlier->cashia) )
					{
						$verdicts[0]->累計現金流量正遞增 = FALSE;
					}
				}
			}
		}
	}
	else if ('03' == substr($start_season, 4, 2))
	{
		if (count($xbrls)<3) // at least 3 items
		{
			$verdicts[0]->累計現金流量正遞增 = FALSE;
		}
		else
		{
			for ($ii=0;$ii<2;$ii++)
			{
				$current = $xbrls[$ii]['current'];
				$earlier = $xbrls[$ii+1]['current'];
				if ( ($current->cashoa+$current->cashia) < ($earlier->cashoa+$earlier->cashia) )
				{
					$verdicts[0]->累計現金流量正遞增 = FALSE;
					break;
				}
			}
		}
	}
	else if ('02' == substr($start_season, 4, 2))
	{
		if (count($xbrls)<2) // at least 2 items
		{
			$verdicts[0]->累計現金流量正遞增 = FALSE;
		}
		else
		{
			$current = $xbrls[0]['current'];
			$earlier = $xbrls[1]['current'];
			if ( ($current->cashoa+$current->cashia) < ($earlier->cashoa+$earlier->cashia) )
			{
				$verdicts[0]->累計現金流量正遞增 = FALSE;
			}
		}
	}
	else if ('01' == substr($start_season, 4, 2))
	{
		if (count($xbrls)<5) // at least 5 items
		{
			$verdicts[0]->累計現金流量正遞增 = FALSE;
		}
		else
		{
			for ($ii=1;$ii<4;$ii++)
			{
				$current = $xbrls[$ii]['current'];
				$earlier = $xbrls[$ii+1]['current'];
				if ( ($current->cashoa+$current->cashia) < ($earlier->cashoa+$earlier->cashia) )
				{
					$verdicts[0]->累計現金流量正遞增 = FALSE;
					break;
				}
			}
		}
	}

	return $verdicts;
}

function calculate_verdict($verdicts, $verdictm)
{
	if ($verdicts == null or $verdictm == null or count($verdicts)==0 or count($verdictm)==0)
		return 0;

	$verdict = 0;

	$verdict += ($verdicts[0]->每股盈餘為正?8:0);
	$verdict += ($verdicts[0]->每股盈餘成長?1:0);
	$verdict += ($verdicts[0]->營收成長?1:0);
	$verdict += ($verdicts[0]->營業利益成長?1:0);
	$verdict += ($verdicts[0]->稅後淨利成長?1:0);
	$verdict += ($verdicts[0]->營業利益率穩定?2:0);
	$verdict += ($verdicts[0]->存貨週轉率沒下降?2:0);
	$verdict += ($verdicts[0]->累計現金流量正遞增?2:0);

	$verdict += ($verdictm[0]->月營收年增率遞增?2:0);

	return $verdict;
}

?>