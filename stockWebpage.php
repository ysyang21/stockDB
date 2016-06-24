<?php

/*
Filename:
	stockWebPage.php

Usage:


Descriptions:
	This file is used to implement UI for both backend and frontend of stock price evaluation system.
*/

// 股票簡介及近況
function show_stock_brief($stock, $price_rank, $price, $yoy_rank, $yoy)
{
	echo_n('  <table>');
	echo_n('    <caption>股票簡介</caption>');
	$thead = '    <thead><th>代號<th>名稱<th>行業別<th>上市櫃別<th>上市櫃時間';
	if ($price_rank != -1)
		$thead = $thead . '<th>股價<th>股價排名';
	if ($yoy_rank != -1)
		$thead = $thead . '<th>月營收年增率<th>月營收年增率排名';
	$thead = $thead . '</thead>';
	echo_n($thead);
	echo '    <tbody><tr>';
	echo '<td>' . '<a href="case.php?stockid=' . $stock->id . '">' . $stock->id . '</a>';
	echo '<td>' . $stock->name;
	echo '<td>' . $stock->industry;
	echo '<td>' . ($stock->market=='sii'?'上市':'上櫃');
	echo '<td>' . $stock->onyyyy . $stock->onmm;

	if ($price_rank == -1)
	{
		//echo '<td>';
		//echo '<td>';
	}
	else if ($price_rank < 100)
	{
		echo "<td class='good'>" . $price;
		echo "<td class='good'>" . ($price_rank+1);
	}
	else
	{
		echo '<td>' . $price;
		echo '<td>' . ($price_rank+1);
	}

	if ($yoy_rank == -1)
	{
		//echo '<td>';
		//echo '<td>';
	}
	else if ($yoy_rank < 100)
	{
		echo "<td class = 'good'>" . percent($yoy);
		echo "<td class = 'good'>" . ($yoy_rank+1);
	}
	else
	{
		echo '<td>' . percent($yoy);
		echo '<td>' . ($yoy_rank+1);
	}

	echo_n('</tbody>');
	echo_n('  </table><br>');
}

function show_stock_brief_case($stock)
{
	echo_n('  <table>');
	echo_n('    <caption>股票簡介</caption>');
	$thead = '    <thead><th>代號<th>名稱<th>行業別<th>上市櫃別<th>上市櫃時間';
	$thead = $thead . '</thead>';
	echo_n($thead);
	echo '    <tbody><tr>';
	echo '<td>' . '<a href="index.php?stockid=' . $stock->id . '">' . $stock->id . '</a>';
	echo '<td>' . $stock->name;
	echo '<td>' . $stock->industry;
	echo '<td>' . ($stock->market=='sii'?'上市':'上櫃');
	echo '<td>' . $stock->onyyyy . $stock->onmm;

	echo_n('</tbody>');
	echo_n('  </table><br>');
}

// 最近四年還原股價與本益比
function show_idr_per($pepos)
{
	$pepo_latest = count($pepos)-1;

	// 四年還原股價高低檔 / 四年EPS / 四年本益比高低檔
	echo_n('  <table>');
	echo_n('    <caption>歷史資料(*為今年推估值)</caption>');
	echo '    <thead><th>年度';
	echo '<th>' . substr(date('Y'), 0, 4) . "*";
	foreach ($pepos[$pepo_latest]->xdr as $year => $xdr)
		echo '<th>' . $year;
	echo_n('</thead>');
	echo_n('    <tbody>');

	echo '      <tr>';
	echo '<td>還原股價高檔';
	echo '<td>' . decimal2($pepos[$pepo_latest]->xdr_estimated->high);
	foreach ($pepos[$pepo_latest]->xdr as $year => $xdr)
		echo '<td>' . decimal2($xdr->high);
	echo_n('');

	echo '      <tr>';
	echo '<td>還原股價低檔';
	echo '<td>' . decimal2($pepos[$pepo_latest]->xdr_estimated->low);
	foreach ($pepos[$pepo_latest]->xdr as $year => $xdr)
		echo '<td>' . decimal2($xdr->low);
	echo_n('');

	echo '      <tr>';
	echo '<td>基本每股盈餘';
	echo '<td>' . decimal2($pepos[$pepo_latest]->eps_estimated);
	foreach ($pepos[$pepo_latest]->eps as $year => $eps)
		echo '<td>' . decimal2($eps);
	echo_n('');

	echo '      <tr>';
	echo '<td>稀釋每股盈餘';
	echo '<td>' . "N/A";
	foreach ($pepos[$pepo_latest]->eps2 as $year => $eps2)
		echo '<td>' . decimal2($eps2);
	echo_n('');

	echo '      <tr>';
	echo '<td>本益比高檔';
	echo '<td>' . decimal2($pepos[$pepo_latest]->per_estimated->high);
	foreach ($pepos[$pepo_latest]->per as $year => $per)
		echo '<td>' . decimal2($per->high);
	echo_n('');

	echo '      <tr>';
	echo '<td>本益比低檔';
	echo '<td>' . decimal2($pepos[$pepo_latest]->per_estimated->low);
	foreach ($pepos[$pepo_latest]->per as $year => $per)
		echo '<td>' . decimal2($per->low);
	echo_n('');

	echo_n('    </tbody>');
	echo_n('  </table><br>');
}

// 最近至少八季財務報表

function show_xbrl($xbrls)
{
	echo_n('  <table>');
	echo_n('    <caption>財務報表</caption>');

	echo '    <thead><th>季度';
	for ($ii = 0; $ii<count($xbrls)-1;$ii++)
		echo '<th>' . $xbrls[$ii]['current']->season;
	echo_n('</thead>');
	echo_n('    <tbody>');

	echo '      <tr>';
	echo '<td>股本(億)';
	for ($ii = 0; $ii<count($xbrls)-1;$ii++)
		echo '<td>' . decimal2($xbrls[$ii]['current']->stock/100000000);
	echo_n('');

	echo '      <tr>';
	echo '<td>營收(億)';
	for ($ii = 0; $ii<count($xbrls)-1;$ii++)
		echo '<td>' . decimal2($xbrls[$ii]['current']->revenue/100000000);
	echo_n('');

	echo '      <tr>';
	echo '<td>稅後淨利(億)';
	for ($ii = 0; $ii<count($xbrls)-1;$ii++)
		echo '<td>' . decimal2($xbrls[$ii]['current']->nopat/100000000);
	echo_n('');

	echo '      <tr>';
	echo '<td>EPS';
	for ($ii = 0; $ii<count($xbrls)-1;$ii++)
		echo '<td>' . decimal2($xbrls[$ii]['current']->eps);
	echo_n('');

	echo '      <tr>';
	echo '<td>存貨(億)';
	for ($ii = 0; $ii<count($xbrls)-1;$ii++)
		echo '<td>' . decimal2($xbrls[$ii]['current']->inventory/100000000);
	echo_n('');

	echo '      <tr>';
	echo '<td>存貨營收比';
	for ($ii = 0; $ii<count($xbrls)-1;$ii++)
		echo '<td>' . percent($xbrls[$ii]['current']->inventory/$xbrls[$ii]['current']->revenue);
	echo_n('');

	echo '      <tr>';
	echo '<td>財報公布日';
	for ($ii = 0; $ii<count($xbrls)-1;$ii++)
		echo '<td>' . $xbrls[$ii]['current']->publish;
	echo_n('');

	echo_n('    </tbody>');
	echo_n('  </table><br>');
}

class verdictData
{
	public $season = "";	// 季度

	public $每股盈餘為正 = TRUE;
	public $每股盈餘成長 = TRUE;
	public $營收成長 = TRUE;
	public $營業利益成長 = TRUE;
	public $稅後淨利成長 = TRUE;
	public $營業利益率穩定 = TRUE;
	public $月營收年增率遞增 = TRUE;
	public $累計現金流量正遞增 = TRUE;
	public $存貨週轉率沒下降 = TRUE;
}

$monthly_revenue_offset = 2;

// 最近八月月營收
function show_monthly_revenue($months)
{
	global $monthly_revenue_offset;

	$verdict = new verdictData();

	echo_n('  <table>');
	echo_n('    <caption>月營收</caption>');

	echo '    <thead><th>月份';
	for ($ii=0;$ii<count($months)-$monthly_revenue_offset;$ii++)
	{
		echo '<th>' . $months[$ii]->month;
	}
	echo_n('</thead>');
	echo_n('    <tbody>');
	
	echo '      <tr>';
	echo '<td>本月營收(億)';
	for ($ii=0;$ii<count($months)-$monthly_revenue_offset;$ii++)
	{
		echo '<td>' . decimal2($months[$ii]->current/100000000);
	}
	echo_n('');

	echo '      <tr>';
	echo '<td>去年同月營收(億)';
	for ($ii=0;$ii<count($months)-$monthly_revenue_offset;$ii++)
	{
		echo '<td>' . decimal2($months[$ii]->corresp/100000000);
	}
	echo_n('');

	echo '      <tr>';
	echo '<td>營收年增率(%)';
	for ($ii=0;$ii<count($months)-$monthly_revenue_offset;$ii++)
	{
		for ($jj=$ii;$jj<$ii+2;$jj++)
		{
			if ($months[$jj]->current < 0)
			{
				$verdict->月營收年增率遞增 = FALSE;
				break;
			}
			if (($months[$jj]->corresp==0) or ($months[$jj]->corresp==0))
			{
				$verdict->月營收年增率遞增 = FALSE;
				break;
			}
			if ($months[$jj]->current/$months[$jj]->corresp < $months[$jj+1]->current/$months[$jj+1]->corresp)
			{
				$verdict->月營收年增率遞增 = FALSE;
				break;
			}
		}

		if ($months[$ii]->corresp != 0) // avoid devide by zero
		{
			echo ($verdict->月營收年增率遞增?'<td bgcolor="red">':'<td>')  . percent(($months[$ii]->current / $months[$ii]->corresp) - 1);
		}
		else
		{
			echo '<td>' . 'DIV/0';
		}
		$verdict->月營收年增率遞增 = TRUE;
	}
	echo_n('');

	echo_n('    </tbody>');
	echo_n('  </table><br>');
}

function show_xbrl_core($xbrls)
{
	$verdict = new verdictData();

	echo_n('  <table>');
	echo_n('    <caption>核心財務指標</caption>');

	echo '    <thead><th>季度';
	for ($ii=0;$ii<count($xbrls)-4;$ii++)
		echo '<th>' . $xbrls[$ii]['current']->season;
	echo '<th>公式';
	echo_n('</thead>');
	echo_n('    <tbody>');

	echo '      <tr>';
	echo '<td>近四季eps>0';
	for ($ii=0;$ii<count($xbrls)-4;$ii++)
	{
		for ($jj=$ii;$jj<$ii+4;$jj++)
		{
			if ($xbrls[$jj]['current']->eps < 0) {
				$verdict->每股盈餘為正 = FALSE;
				break;
			}
		}
		echo ($verdict->每股盈餘為正?'<td bgcolor="red">':'<td>') . decimal2($xbrls[$ii]['current']->eps);
		$verdict->每股盈餘為正 = TRUE;
	}
	// for ($ii=count($xbrls)-4;$ii<count($xbrls)-1;$ii++)
	// {
	// 	echo '<td>' . decimal2($xbrls[$ii]['current']->eps);
	// }
	echo '<td>1.b';
	echo_n('');

	echo '      <tr>';
	echo '<td>近三季yoy(eps)>0';
	for ($ii=0;$ii<count($xbrls)-4;$ii++)
	{
		for ($jj=$ii;$jj<$ii+3;$jj++)
		{
			if (($xbrls[$jj]['current']->eps < $xbrls[$jj]['corresp']->eps)) {
				$verdict->每股盈餘成長 = FALSE;
				break;
			}
		}
		echo ($verdict->每股盈餘成長?'<td bgcolor="red">':'<td>') . percent(($xbrls[$ii]['current']->eps/$xbrls[$ii]['corresp']->eps)-1);
		$verdict->每股盈餘成長 = TRUE;
	}
	// for ($ii=count($xbrls)-3;$ii<count($xbrls)-1;$ii++)
	// {
	// 	echo '<td>' . percent(($xbrls[$ii]['current']->eps/$xbrls[$ii]['corresp']->eps)-1);
	// }
	echo '<td>(每股盈餘EPS[i] / 每股盈餘EPS[i+4])*100%-1';
	echo_n('');

	echo '      <tr>';
	echo '<td>近三季yoy(營收)>0';
	for ($ii=0;$ii<count($xbrls)-4;$ii++)
	{
		for ($jj=$ii;$jj<$ii+3;$jj++)
		{
			if (($xbrls[$jj]['current']->revenue < $xbrls[$jj]['corresp']->revenue)) {
				$verdict->營收成長 = FALSE;
				break;
			}
		}
		echo ($verdict->營收成長?'<td bgcolor="red">':'<td>') . percent(($xbrls[$ii]['current']->revenue/$xbrls[$ii]['corresp']->revenue)-1);
		$verdict->營收成長 = TRUE;
	}
	// for ($ii=count($xbrls)-3;$ii<count($xbrls)-1;$ii++)
	// {
	// 	echo '<td>' . percent(($xbrls[$ii]['current']->revenue/$xbrls[$ii]['corresp']->revenue)-1);
	// }
	echo '<td>2.1';
	echo_n('');

	echo '      <tr>';
	echo '<td>近三季yoy(營業利益)>0';
	for ($ii=0;$ii<count($xbrls)-4;$ii++)
	{
		for ($jj=$ii;$jj<$ii+3;$jj++)
		{
			if (($xbrls[$jj]['current']->income < $xbrls[$jj]['corresp']->income)) {
				$verdict->營業利益成長 = FALSE;
				break;
			}
		}
		echo ($verdict->營業利益成長?'<td bgcolor="red">':'<td>') . percent(($xbrls[$ii]['current']->income/$xbrls[$ii]['corresp']->income)-1);
		$verdict->營業利益成長 = TRUE;
	}
	// for ($ii=count($xbrls)-3;$ii<count($xbrls)-1;$ii++)
	// {
	// 	echo '<td>' . percent(($xbrls[$ii]['current']->income/$xbrls[$ii]['corresp']->income)-1);
	// }
	echo '<td>2.2';
	echo_n('');

	echo '      <tr>';
	echo '<td>近三季yoy(稅後淨利)>0';
	for ($ii=0;$ii<count($xbrls)-4;$ii++)
	{
		for ($jj=$ii;$jj<$ii+3;$jj++)
		{
			if (($xbrls[$jj]['current']->nopat < $xbrls[$jj]['corresp']->nopat)) {
				$verdict->稅後淨利成長 = FALSE;
				break;
			}
		}
		echo ($verdict->稅後淨利成長?'<td bgcolor="red">':'<td>') . percent(($xbrls[$ii]['current']->nopat/$xbrls[$ii]['corresp']->nopat)-1);
		$verdict->稅後淨利成長 = TRUE;
	}
	// for ($ii=count($xbrls)-3;$ii<count($xbrls)-1;$ii++)
	// {
	// 	echo '<td>' . percent(($xbrls[$ii]['current']->nopat/$xbrls[$ii]['corresp']->nopat)-1);
	// }
	echo '<td>2.4';
	echo_n('');

	echo '      <tr>';
	echo '<td>近三季營業利益率穩定';
	for ($ii=0;$ii<count($xbrls)-4;$ii++)
	{
		for ($jj=$ii;$jj<$ii+3;$jj++)
		{
			$current = $xbrls[$jj]['current']->income/$xbrls[$jj]['current']->revenue;
			$earlier = $xbrls[$jj+1]['current']->income/$xbrls[$jj+1]['current']->revenue;

			if ($current < 0) {
				$verdict->營業利益率穩定 = FALSE;
				break;
			}

			if (($current / $earlier) < 0.85) {
				$verdict->營業利益率穩定 = FALSE;
				break;
			}
		}
		echo ($verdict->營業利益率穩定?'<td bgcolor="red">':'<td>') . percent($xbrls[$ii]['current']->income/$xbrls[$ii]['current']->revenue);
		$verdict->營業利益率穩定 = TRUE;
	}
	// for ($ii=count($xbrls)-3;$ii<count($xbrls)-1;$ii++)
	// {
	// 	echo '<td>' . percent($xbrls[$ii]['current']->income/$xbrls[$ii]['current']->revenue);
	// }
	echo '<td>1.2';
	echo_n('');

	$start_season = $xbrls[0]['current']->season;
	if ('04' == substr($start_season, 4, 2))
	{
		if ($start->cashoa+$start->cashia < 0)
		{
			$verdict->累計現金流量正遞增 = FALSE;
		}
		else
		{
			for ($ii=0;$ii<3;$ii++)
			{
				$current = $xbrls[$ii]['current'];
				$earlier = $xbrls[$ii+1]['current'];
				if ( ($current->cashoa+$current->cashia) < ($earlier->cashoa+$earlier->cashia) )
				{
					$verdict->累計現金流量正遞增 = FALSE;
					break;
				}
			}
		}
	}
	else if ('03' == substr($start_season, 4, 2))
	{
		for ($ii=0;$ii<2;$ii++)
		{
			$current = $xbrls[$ii]['current'];
			$earlier = $xbrls[$ii+1]['current'];
			if ( ($current->cashoa+$current->cashia) < ($earlier->cashoa+$earlier->cashia) )
			{
				$verdict->累計現金流量正遞增 = FALSE;
				break;
			}
		}
	}
	else if ('02' == substr($start_season, 4, 2))
	{
		$current = $xbrls[$ii]['current'];
		$earlier = $xbrls[$ii+1]['current'];
		if ( ($current->cashoa+$current->cashia) < ($earlier->cashoa+$earlier->cashia) )
		{
			$verdict->累計現金流量正遞增 = FALSE;
			break;
		}
	}
	else if ('01' == substr($start_season, 4, 2))
	{
		for ($ii=1;$ii<4;$ii++)
		{
			$current = $xbrls[$ii]['current'];
			$earlier = $xbrls[$ii+1]['current'];
			if ( ($current->cashoa+$current->cashia) < ($earlier->cashoa+$earlier->cashia) )
			{
				$verdict->累計現金流量正遞增 = FALSE;
				break;
			}
		}
	}

	echo '      <tr>';
	echo ($verdict->累計現金流量正遞增?'<td bgcolor="red">':'<td>') . '累計自由現金流量正遞增';
	for ($ii=0;$ii<count($xbrls)-4;$ii++)
	{
		$xbrl = $xbrls[$ii]['current'];
		echo '<td>' . decimal2(($xbrl->cashoa+$xbrl->cashia)/100000000);
	}
	//echo '<td>營業活動之淨現金流入流出CashFlowFromUsedInOperatingActivities + 投資活動之淨現金流入流出CashFlowFromUsedInInvestingActivities';
	echo '<td>自由現金流量 = 營業活動現金流量 + 投資活動現金流量';
	echo_n('');

	echo '      <tr>';
	echo '<td>近四季存貨週轉率穩定';
	for ($ii=0;$ii<count($xbrls)-4;$ii++)
	{
		for ($jj=$ii;$jj<$ii+4;$jj++)
		{
			$current = $xbrls[$ii]['current'];
			$earlier = $xbrls[$ii+1]['current'];
			$garlier = $xbrls[$ii+2]['current'];
			$current_turnover = $current->costs*2/($current->inventory+$earlier->inventory);
			$earlier_turnover = $earlier->costs*2/($earlier->inventory+$garlier->inventory);
			if (($current_turnover / $earlier_turnover) < 0.85) {
				$verdict->存貨週轉率沒下降 = FALSE;
				break;
			}
		}
		echo ($verdict->存貨週轉率沒下降?'<td bgcolor="red">':'<td>') . decimal2($xbrls[$ii]['current']->costs*2/($xbrls[$ii]['current']->inventory+$xbrls[$ii+1]['current']->inventory));
		$verdict->存貨週轉率沒下降 = TRUE;
	}
	// for ($ii=count($xbrls)-4;$ii<count($xbrls)-1;$ii++)
	// {
	// 	echo '<td>' . decimal2($xbrls[$ii]['current']->costs*2/($xbrls[$ii]['current']->inventory+$xbrls[$ii+1]['current']->inventory));
	// }
	echo '<td>4.2';
	echo_n('');

	echo_n('    </tbody>');
	echo_n('  </table><br>');
}

// class xbrlData
// {
// 	public $season = "";	// 季度
// 	public $stock = 0;		// 股本
// 	public $revenue = 0;	// 營業收入 (Operating Revenue)
// 	public $profit = 0;		// 營業毛利 (Gross Profit)
// 	public $income = 0;		// 營業利益 (Operating Income)
// 	public $nopbt = 0;		// 稅前淨利 (Net Operating Profit Before Tax)
// 	public $nopat = 0;		// 稅後淨利 (Net Operating Profit After Tax)
// 	public $eps = 0;		// 每股盈餘 (Earning Per Share)
// 	public $eps2 = 0;		// 稀釋每股盈餘
// 	public $inventory = 0;	// 存貨
// 	public $cashoa = 0;		// 營運活動現金流量
// 	public $cashia = 0;		// 投資活動現金流量
// 	public $publish = "";	// 財報公佈日
// }

// -獲利能力 (Return on investment analysis)
// 1.1 營業毛利率 = 營業毛利(營業收入－營業成本) / 營業收入
// 1.2 營業利益率 = 營業利益(營業收入－營業成本－營業費用) / 營業收入
// 1.3 稅前淨利率 = 稅前淨利 / 營業收入
// 1.4 稅後淨利率 = 稅後淨利 / 營業收入
// 1.5 每股淨值 = 股東權益總額 / 期末股本
// 1.6 每股營業額 = 營業收入 / 期末股本
// 1.7 每股營業利益 = 營業利益 / 期末股本 
// 		Operation income to capital per share
// 1.8 每股稅前淨利 = 稅前淨利 / 期末股本 
// 		Pre-tax income to capital per share
// 1.9 股東權益報酬率 = 稅後淨利 / 股東權益
// 		(ROE) Return on total stockholders' equalty
// 1.a 資產報酬率 = 稅後淨利 / 總資產
// 		(ROA) Return on total assets
// 1.b 每股盈餘 = 稅後淨利 / 期末股本
// 		(EPS) Earning Per Share
$xbrl_group_offset = 4;

function show_xbrl_group_a($xbrls)
{
	global $xbrl_group_offset;

	echo_n('  <table>');
	echo_n('    <caption>獲利能力</caption>');

	echo '    <thead><th>季度';
	for ($ii=0;$ii<count($xbrls)-$xbrl_group_offset;$ii++)
		echo '<th>' . $xbrls[$ii]['current']->season;
	echo '<th>公式';
	echo_n('</thead>');
	echo_n('    <tbody>');

	echo '      <tr>';
	echo '<td>1.1 營業毛利率(%)';
	for ($ii=0;$ii<count($xbrls)-$xbrl_group_offset;$ii++)
	{
		$xbrl = $xbrls[$ii]['current'];
		echo '<td>' . percent($xbrl->profit/$xbrl->revenue);
	}
	echo '<td>營業毛利Profit / 營業收入Revenue';
	echo_n('');

	echo '      <tr>';
	echo '<td>1.2 營業利益率(%)';
	for ($ii=0;$ii<count($xbrls)-$xbrl_group_offset;$ii++)
	{
		$xbrl = $xbrls[$ii]['current'];
		echo '<td>' . percent($xbrl->income/$xbrl->revenue);
	}
	echo '<td>營業利益Income / 營業收入Revenue';
	echo_n('');

	echo '      <tr>';
	echo '<td>1.3 稅前淨利率(%)';
	for ($ii=0;$ii<count($xbrls)-$xbrl_group_offset;$ii++)
	{
		$xbrl = $xbrls[$ii]['current'];
		echo '<td>' . percent($xbrl->nopbt/$xbrl->revenue);
	}
	echo '<td>稅前淨利Nopbt / 營業收入Revenue';
	echo_n('');

	echo '      <tr>';
	echo '<td>1.4 稅後淨利率(%)';
	for ($ii=0;$ii<count($xbrls)-$xbrl_group_offset;$ii++)
	{
		$xbrl = $xbrls[$ii]['current'];
		echo '<td>' . percent($xbrl->nopat/$xbrl->revenue);
	}
	echo '<td>稅後淨利Nopat / 營業收入Revenue';
	echo_n('');

	echo '      <tr>';
	echo '<td>1.5 每股淨值';
	for ($ii=0;$ii<count($xbrls)-$xbrl_group_offset;$ii++)
	{
		$xbrl = $xbrls[$ii]['current'];
		echo '<td>' . decimal2(($xbrl->equity-$xbrl->noncontrol)*10/$xbrl->stock);
	}
	echo '<td>股東權益(Equity-Noncontrol) / 期末股本(Stock/10)';
	echo_n('');

	echo '      <tr>';
	echo '<td>1.6 每股營業額';
	for ($ii=0;$ii<count($xbrls)-$xbrl_group_offset;$ii++)
	{
		$xbrl = $xbrls[$ii]['current'];
		echo '<td>' . decimal2($xbrl->revenue*10/$xbrl->stock);
	}
	echo '<td>營業收入Revenue / 期末股本(Stock/10)';
	echo_n('');

	echo '      <tr>';
	echo '<td>1.7 每股營業利益';
	for ($ii=0;$ii<count($xbrls)-$xbrl_group_offset;$ii++)
	{
		$xbrl = $xbrls[$ii]['current'];
		echo '<td>' . decimal2($xbrl->income*10/$xbrl->stock);
	}
	echo '<td>營業利益Income / 期末股本(Stock/10)';
	echo_n('');

	echo '      <tr>';
	echo '<td>1.8 每股稅前淨利';
	for ($ii=0;$ii<count($xbrls)-$xbrl_group_offset;$ii++)
	{
		$xbrl = $xbrls[$ii]['current'];
		echo '<td>' . decimal2($xbrl->nopbt*10/$xbrl->stock);
	}
	echo '<td>稅前淨利Nopbt / 期末股本(Stock/10)';
	echo_n('');

	echo '      <tr>';
	echo '<td>1.9 股東權益報酬率(%)';
	for ($ii=0;$ii<count($xbrls)-$xbrl_group_offset;$ii++)
	{
		$xbrl = $xbrls[$ii]['current'];
		$xbrl_1 = $xbrls[$ii+1]['current'];
	 	echo '<td>' . percent($xbrl->nopat*2/($xbrl->equity+$xbrl_1->equity));
	}
	echo '<td>稅後淨利Nopat / 期初期末平均股東權益Equity[i,i+1]/2';
	echo_n('');

	echo '      <tr>';
	echo '<td>1.a 資產報酬率(%)';
	for ($ii=0;$ii<count($xbrls)-$xbrl_group_offset;$ii++)
	{
		$xbrl = $xbrls[$ii]['current'];
		$xbrl_1 = $xbrls[$ii+1]['current'];
		$taxrate = ($xbrl->nopbt - $xbrl->nopat) / $xbrl->nopbt;
	 	echo '<td>' . percent(($xbrl->nopat+$xbrl->interestexpense*(1-$taxrate))*2/($xbrl->assets+$xbrl_1->assets));
	}
	echo '<td>稅後息前淨利Nopat-InterestsExpense*(1-(Nopbt-Nopat)/Nopbt) / 期初期末平均資產Assets[i,i+1]/2';
	echo_n('');

	echo '      <tr>';
	echo '<td>1.b 每股盈餘';
	for ($ii=0;$ii<count($xbrls)-$xbrl_group_offset;$ii++)
	{
		$xbrl = $xbrls[$ii]['current'];
		echo '<td>' . decimal2($xbrl->eps);
	}
	echo '<td>每股盈餘EPS';
	echo_n('');

	echo_n('    </tbody>');
	echo_n('  </table><br>');
}

/*
-經營績效
2.1 營收成長率
2.2 營業利益成長率
2.3 稅前淨利成長率
2.4 稅後淨利成長率
2.5 總資產成長率
2.6 淨值成長率
2.7 固定資產成長率
*/

function show_xbrl_group_b($xbrls)
{
	global $xbrl_group_offset;

	echo_n('  <table>');
	echo_n('    <caption>經營績效</caption>');

	echo '    <thead><th>季度';
	for ($ii=0;$ii<count($xbrls)-$xbrl_group_offset;$ii++)
	{
		$xbrl = $xbrls[$ii]['current'];
		echo '<th>' . $xbrl->season;
	}
	echo '<th>公式';
	echo_n('</thead>');
	echo_n('    <tbody>');

	echo '      <tr>';
	echo '<td>2.1 營收年增率(%)';
	for ($ii=0;$ii<count($xbrls)-$xbrl_group_offset;$ii++)
	{
		$xbrl = $xbrls[$ii];
		echo '<td>' . percent(($xbrl['current']->revenue/$xbrl['corresp']->revenue)-1);
	}
	echo '<td>(營業收入Revenue[i] / 去年同期營業收入Revenue[i+4])*100%-1';
	echo_n('');

	echo '      <tr>';
	echo '<td>2.2 營業利益年增率(%)';
	for ($ii=0;$ii<count($xbrls)-$xbrl_group_offset;$ii++)
	{
		$xbrl = $xbrls[$ii];
		echo '<td>' . percent(($xbrl['current']->income/$xbrl['corresp']->income)-1);
	}
	echo '<td>(營業利益Income[i] / 去年同期營業利益Income[i+4])*100%-1';
	echo_n('');

	echo '      <tr>';
	echo '<td>2.3 稅前淨利年增率(%)';
	for ($ii=0;$ii<count($xbrls)-$xbrl_group_offset;$ii++)
	{
		$xbrl = $xbrls[$ii];
		echo '<td>' . percent(($xbrl['current']->nopbt/$xbrl['corresp']->nopbt)-1);
	}
	echo '<td>(稅前淨利Nopbt[i] / 去年同期稅前淨利Nopbt[i+4])*100%-1';
	echo_n('');

	echo '      <tr>';
	echo '<td>2.4 稅後淨利年增率(%)';
	for ($ii=0;$ii<count($xbrls)-$xbrl_group_offset;$ii++)
	{
		$xbrl = $xbrls[$ii];
		echo '<td>' . percent(($xbrl['current']->nopat/$xbrl['corresp']->nopat)-1);
	}
	echo '<td>(稅後淨利Nopat[i] / 去年同期稅後淨利Nopat[i+4])*100%-1';
	echo_n('');

	echo '      <tr>';
	echo '<td>2.5 總資產年增率(%)';
	for ($ii=0;$ii<count($xbrls)-$xbrl_group_offset;$ii++)
	{
		$xbrl = $xbrls[$ii];
		echo '<td>' . percent(($xbrl['current']->assets/$xbrl['corresp']->assets)-1);
	}
	echo '<td>(總資產Assets[i] / 去年同期總資產Assets[i+4])*100%-1';
	echo_n('');

	echo '      <tr>';
	echo '<td>2.6 淨值年增率(%)';
	for ($ii=0;$ii<count($xbrls)-$xbrl_group_offset;$ii++)
	{
		$xbrl = $xbrls[$ii];
		echo '<td>' . percent(($xbrl['current']->equity/$xbrl['corresp']->equity)-1);
	}
	echo '<td bgcolor="yellow">(股東權益Equity[i] / 去年同期股東權益Equity[i+4])*100%-1';
	echo_n('');

	echo '      <tr>';
	echo '<td>2.7 固定資產年增率(%)';
	for ($ii=0;$ii<count($xbrls)-$xbrl_group_offset;$ii++)
	{
		$xbrl = $xbrls[$ii];
		echo '<td>' . percent(($xbrl['current']->fixedassets/$xbrl['corresp']->fixedassets)-1);
	}
	echo '<td bgcolor="yellow">(固定資產FixedAssets[i] / 去年同期固定資產FixedAssets[i+4])*100%-1';
	echo_n('');

	echo_n('    </tbody>');
	echo_n('  </table><br>');
}

/*
-償債能力 (Liquidity analysis)
3.1 流動比率 = 流動資產 / 流動負債
		Current Ratio
3.2 速動比率 = 速動資產 / 流動負債 = (流動資產－存貨-預付款項) / 流動負債
		Quick Ratio
3.3 負債比率 = 負債總額 / 資產總額
3.4 利息保障倍數(累計) = EBIT ( 稅前息前純益 ) / 利息費用 經營獲利
*/

function show_xbrl_group_c($xbrls)
{
	global $xbrl_group_offset;

	echo_n('  <table>');
	echo_n('    <caption>償債能力</caption>');

	echo '    <thead><th>季度';
	for ($ii=0;$ii<count($xbrls)-$xbrl_group_offset;$ii++)
	{
		$xbrl = $xbrls[$ii]['current'];
		echo '<th>' . $xbrl->season;
	}
	echo '<th>公式';
	echo_n('</thead>');
	echo_n('    <tbody>');

	echo '      <tr>';
	echo '<td>3.1 流動比率(%)';
	for ($ii=0;$ii<count($xbrls)-$xbrl_group_offset;$ii++)
	{
		$xbrl = $xbrls[$ii]['current'];
		echo '<td>' . percent($xbrl->currentassets/$xbrl->currentliabilities);
	}
	echo '<td>流動資產CurrentAssets / 流動負債CurrentLiabilities';
	echo_n('');

	echo '      <tr>';
	echo '<td>3.2 速動比率(%)';
	for ($ii=0;$ii<count($xbrls)-$xbrl_group_offset;$ii++)
	{
		$xbrl = $xbrls[$ii]['current'];
		echo '<td>' . percent(($xbrl->currentassets-$xbrl->inventory-$xbrl->othercurrentassets)/$xbrl->currentliabilities);
	}
	echo '<td>速動資產(CurrentAssets-Inventory-OtherCurrentAssets) / 流動負債CurrentLiabilities';
	echo_n('');

	echo '      <tr>';
	echo '<td>3.3 負債比率(%)';
	for ($ii=0;$ii<count($xbrls)-$xbrl_group_offset;$ii++)
	{
		$xbrl = $xbrls[$ii]['current'];
		echo '<td>' . percent($xbrl->liabilities/$xbrl->assets);
	}
	echo '<td>負債總額Liabilities / 資產總額Assets';
	echo_n('');

	echo '      <tr>';
	echo '<td>3.4 利息保障倍數';
	for ($ii=0;$ii<count($xbrls)-$xbrl_group_offset;$ii++)
	{
		$xbrl = $xbrls[$ii]['current'];
		if ($xbrl->interestexpense != 0)
			echo '<td>' . decimal2(($xbrl->nopbt+$xbrl->interestexpense)/$xbrl->interestexpense);
		else
			echo '<td>DIV/0';
	}
	echo '<td>稅前息前淨利(Nopbt+InterestsExpense) / 利息費用InterestExpense';
	echo_n('');

	echo_n('    </tbody>');
	echo_n('  </table><br>');
}

/*
-經營能力 (Operating performance analysis)
4.1 應收帳款週轉率 = 賒銷(銷貨收入) / 平均應收帳款
		Average collection turnover
4.2 存貨週轉率 = 銷售成本 / 平均存貨
		Average inventory turnover
4.3 固定資產週轉率 = 銷貨金額 / 固定資產
		Fixed aassets turnover
4.4 總資產週轉率 = 銷貨金額 / 總資產(自有資本)
		Total assets turnover
4.5 員工平均營業額 = 營業收入 / 員工人數 ()
4.6 淨值週轉率 = 營業收入 / 淨值
*/

function show_xbrl_group_d($xbrls)
{
	global $xbrl_group_offset;

	echo_n('  <table>');
	echo_n('    <caption>經營能力</caption>');

	echo '    <thead><th>季度';
	for ($ii=0;$ii<count($xbrls)-$xbrl_group_offset;$ii++)
	{
		$xbrl = $xbrls[$ii]['current'];
		echo '<th>' . $xbrl->season;
	}
	echo '<th>公式';
	echo_n('</thead>');
	echo_n('    <tbody>');

	echo '      <tr>';
	echo '<td>4.1 應收帳款週轉率(次)';
	for ($ii=0;$ii<count($xbrls)-$xbrl_group_offset;$ii++)
	{
		$xbrl = $xbrls[$ii]['current'];
		$xbrl_1 = $xbrls[$ii+1]['current'];
		echo '<td>' . decimal2($xbrl->revenue*2/($xbrl->arn+$xbrl->arnr+$xbrl_1->arn+$xbrl_1->arnr));
	}
	echo '<td>營業收入(reveune) / 期初期末平均應收帳款(AccountsReceivableNet[i,i+1] + AccountReceivableRelatedPartiesNet[i,i+1])/2';
	echo_n('');

	echo '      <tr>';
	echo '<td>4.2 存貨週轉率(次)';
	for ($ii=0;$ii<count($xbrls)-$xbrl_group_offset;$ii++)
	{
		$xbrl = $xbrls[$ii]['current'];
		$xbrl_1 = $xbrls[$ii+1]['current'];
		echo '<td>' . decimal2($xbrl->costs*2/($xbrl->inventory+$xbrl_1->inventory));
	}
	echo '<td>銷貨成本(Costs) / 期初期末平均存貨(Inventory[i,i+1])/2';
	echo_n('');

	echo '      <tr>';
	echo '<td>4.3 固定資產週轉率(次)';
	for ($ii=0;$ii<count($xbrls)-$xbrl_group_offset;$ii++)
	{
		$xbrl = $xbrls[$ii]['current'];
		echo '<td>' . decimal2($xbrl->revenue/$xbrl->fixedassets);
	}
	echo '<td>營業收入Revenue / 固定資產FixedAssets';
	echo_n('');

	echo '      <tr>';
	echo '<td>4.4 總資產週轉率(次)';
	for ($ii=0;$ii<count($xbrls)-$xbrl_group_offset;$ii++)
	{
		$xbrl = $xbrls[$ii]['current'];
		echo '<td>' . decimal2($xbrl->revenue/$xbrl->assets);
	}
	echo '<td>營業收入Revenue / 總資產Assets';
	echo_n('');

	echo '      <tr>';
	echo '<td>4.6 淨值週轉率(次)';
	for ($ii=0;$ii<count($xbrls)-$xbrl_group_offset;$ii++)
	{
		$xbrl = $xbrls[$ii]['current'];
		echo '<td>' . decimal2($xbrl->revenue/$xbrl->equity);
	}
	echo '<td>營業收入Revenue / 股東權益Equity';
	echo_n('');

	echo_n('    </tbody>');
	echo_n('  </table><br>');
}

/*
-資本結構
5.1 負債對淨值比率
5.2 長期資金適合率 = (股東權益淨額 + 長期負債) / 固定資產淨額
*/

function show_xbrl_group_e($xbrls)
{
	global $xbrl_group_offset;

	echo_n('  <table>');
	echo_n('    <caption>資本結構</caption>');

	echo '    <thead><th>季度';
	for ($ii=0;$ii<count($xbrls)-$xbrl_group_offset;$ii++)
	{
		$xbrl = $xbrls[$ii]['current'];
		echo '<th>' . $xbrl->season;
	}
	echo '<th>公式';
	echo_n('</thead>');
	echo_n('    <tbody>');

	echo '      <tr>';
	echo '<td>5.1 負債對淨值比率(%)';
	for ($ii=0;$ii<count($xbrls)-$xbrl_group_offset;$ii++)
	{
		$xbrl = $xbrls[$ii]['current'];
		echo '<td>' . percent($xbrl->liabilities/$xbrl->equity);
	}
	echo '<td>負債總額Liabilities / 股東權益Equity';
	echo_n('');

	echo '      <tr>';
	echo '<td>5.2 長期資金適合率(%)';
	for ($ii=0;$ii<count($xbrls)-$xbrl_group_offset;$ii++)
	{
		$xbrl = $xbrls[$ii]['current'];
		echo '<td>' . percent(($xbrl->equity+$xbrl->noncurrentliabilities-$xbrl->othernoncurrentliabilities)/$xbrl->fixedassets);
	}
	echo '<td bgcolor="yellow">長期資金(Equity-NonCurrentLiabilities-OtherNonCurrentLiabilities) / 固定資產FixedAssets';
	echo_n('');

	echo_n('    </tbody>');
	echo_n('  </table><br>');
}

// 股價評估過程
function show_stock_evaluation($pepos)
{
	echo_n('  <table>');
	echo_n('    <caption>股價評估過程(*為今年推估值)</caption>');
	echo_n('    <thead><th>評估日期<th>去年營收(億)<th>營收年增率*<th>今年營收(億)*<th>稅後淨利率*<th>稅後淨利(億)*<th>股本(億)*<th>EPS*' .
	    '<th>股價高低點<th>當日收盤<th>上下檔風險*<th>溫度計*</thead>');
	echo_n('    <tbody>');
	foreach ($pepos as $pepo) {
		echo '      <tr>';
		echo '<td>' . $pepo->evaluate_date;
		echo '<td>' . decimal2($pepo->revenue_lastyear/100000000);
		echo '<td>' . percent($pepo->revenue_yoy_estimated);
		echo '<td>' . decimal2($pepo->revenue_estimated/100000000);
		echo '<td>' . percent($pepo->nopat_over_revenue_estimated);
		echo '<td>' . decimal2($pepo->nopat_estimated/100000000);
		echo '<td>' . decimal2($pepo->stock_now/100000000);
		echo '<td>' . decimal2($pepo->eps_estimated);
		//echo '<td>' . decimal2($pepo->per_estimated->high) . "/" . decimal2($pepo->per_estimated->low);
		echo '<td>' . decimal2($pepo->xdr_estimated->high) . "/" . decimal2($pepo->xdr_estimated->low);
		echo '<td>' . decimal2($pepo->evaluate_price);
		echo '<td>' . $pepo->potential;
		if ($pepo->verdict <= 0.0)
			echo "<td class = 'good'>" . $pepo->verdict;
		else
			echo '<td>' . $pepo->verdict;
		echo_n('');
	} 

	echo_n('    </tbody>');
	echo_n('  </table><br>');
}

class etaData
{
	// property declaration
	public $monthETA = '';
	public $indexETA = '';
	public $dayETA = '';
	public $idETA = '';
	public $xdrETA = '';
	public $ifrsETA = '';
}

// 網頁內容 (Frontend)
function show_frontend_updater($my_name)
{
	global $observed_stocks;

	echo_n('  <table>');
	echo_n('    <caption>個股調查</caption>');
	echo_n('    <tbody>');

	echo_n('      <tr>');
	echo_n('        <td>' . '請輸入股票代號');
	echo_n('        <td>');
	echo_n('          <form action="' . $my_name . '" method="get">');
	echo_n('　          <input size=4 type=text name="stockid" value="3008">');
	echo_n('            <input type="submit" value="Submit">');
	echo_n('          </form>');

	echo_n('      <tr>');
	echo_n('        <td>' . '請輸入股票名稱');
	echo_n('        <td>');
	echo_n('          <form action="' . $my_name . '" method="get">');
	echo_n('　          <input size=4 type=text name="stockname" value="聯發科">');
	echo_n('            <input type="submit" value="Submit">');
	echo_n('          </form>');

	echo_n('      <tr>');
	echo_n('        <td>' . '請選擇股票名稱');
	echo_n('        <td>');
	echo_n('          <form action="' . $my_name . '" method="get">');
	echo_n('            <select name="stockname">');
	foreach($observed_stocks as $stock)
	{
		echo_n('              <option value="' . $stock . '">' . $stock . '</option>');
	}
	echo_n('            </select>');
	echo_n('            <input type="submit" value="Submit">');
	echo_n('          </form>');

	echo_n('    </tbody>');
	echo_n('  </table>');
	echo_n('  <br>');
	echo_n('  <input type=button value="Frontend" onClick="self.location=' . "'index.php'" . '">');
	echo_n('  <input type=button value="CaseStudy" onClick="self.location=' . "'case.php'" . '">');
	echo_n('  <br>');
	echo_n('  <br>');
}

// 網頁內容 (Case Study)
function show_casestudy_updater($my_name)
{
	global $observed_stocks;

	echo_n('  <table>');
	echo_n('    <caption>個案研究</caption>');
	echo_n('    <tbody>');

	echo_n('      <tr>');
	echo_n('        <td>' . '請輸入股票代號');
	echo_n('        <td>');
	echo_n('          <form action="' . $my_name . '" method="get">');
	echo_n('　          <input size=4 type=text name="stockid" value="3008">');
	echo_n('            <input type="submit" value="Submit">');
	echo_n('          </form>');

	echo_n('      <tr>');
	echo_n('        <td>' . '請輸入股票名稱');
	echo_n('        <td>');
	echo_n('          <form action="' . $my_name . '" method="get">');
	echo_n('　          <input size=4 type=text name="stockname" value="聯發科">');
	echo_n('            <input type="submit" value="Submit">');
	echo_n('          </form>');

	echo_n('      <tr>');
	echo_n('        <td>' . '請選擇股票名稱');
	echo_n('        <td>');
	echo_n('          <form action="' . $my_name . '" method="get">');
	echo_n('            <select name="stockname">');
	foreach($observed_stocks as $stock)
	{
		echo_n('              <option value="' . $stock . '">' . $stock . '</option>');
	}
	echo_n('            </select>');
	echo_n('            <input type="submit" value="Submit">');
	echo_n('          </form>');

	echo_n('    </tbody>');
	echo_n('  </table>');
	echo_n('  <br>');
	echo_n('  <input type=button value="Frontend" onClick="self.location=' . "'index.php'" . '">');
	echo_n('  <input type=button value="CaseStudy" onClick="self.location=' . "'case.php'" . '">');
	echo_n('  <br>');
	echo_n('  <br>');
}

// 網頁頭
function show_webpage_header($stage)
{
	echo_n('');
	echo_n('');
	echo_n('<!doctype html>');
	echo_n('<html>');
	echo_n('  <head>');
	echo_n('    <title>Pepo Project ' . $stage . '</title>');
	echo_n('      <style>');
	echo_n('        table {border-collapse: collapse; border: inset;}');
	echo_n('        tbody {border: solid outset;}');
	echo_n('        th {}');
//	echo_n('        .small {font-size:xx-small;}');
	echo_n('        td {border: solid thin; text-align: left; padding: 2;}');
	echo_n('        .good {color:#FF0000;}');
	echo_n('        input {}');
	echo_n('        .long {color:#0000FF;}');
	echo_n('        form {margin: 1 1; padding: 0;}');
	echo_n('      </style>');
	echo_n('    <script type="text/javascript" src="https://www.google.com/jsapi"></script>');
	echo_n('    <script type="text/javascript" src="./jquery-1.3.1.js"></script>');

	echo_n('    <script>');
	echo_n('		function writeMessage(canvas, message) {');
	echo_n('			var context = canvas.getContext("2d");');
	echo_n('			context.clearRect(0, 0, canvas.width, canvas.height);');
	echo_n('			context.font = "18pt Calibri";');
	echo_n('			context.fillStyle = "black";');
	echo_n('			context.fillText(message, 10, 25);');
	echo_n('		}');
	echo_n('		function getMousePos(canvas, evt) {');
	echo_n('			var rect = canvas.getBoundingClientRect();');
	echo_n('			return {');
	echo_n('				x: evt.clientX - rect.left,');
	echo_n('				y: evt.clientY - rect.top');
	echo_n('			};');
	echo_n('		}');
	echo_n('    </script>');

	echo_n('  </head>');
	echo_n('  <body>');
}


// 周線
function show_ma5()
{
	echo_n('      var days5 = 5;');
	echo_n('      var ma5 = {');
	echo_n("        type: 'number',");
	echo_n("        label: 'MA5',");
	echo_n('        calc: function (dt, row) {');
	echo_n('          // calculate average of closing value for last 5 days,');
	echo_n('          // if we are 5 or more days into the data set');
	echo_n('          if (row >= days5 - 1) {');
	echo_n('            var total = 0;');
	echo_n('            for (var i = 0; i < days5; i++) {');
	echo_n('              total += dt.getValue(row - i, 3);');
	echo_n('            }');
	echo_n('            var avg = total / days5;');
	echo_n('            return {v: avg, f: avg.toFixed(2)};');
	echo_n('          }');
	echo_n('          else {');
	echo_n('            // return null for < x days');
	echo_n('            return null;');
	echo_n('          }');
	echo_n('        }');
	echo_n('      }');
}

// 月線
function show_ma20()
{
	echo_n('      var days20 = 20;');
	echo_n('      var ma20 = {');
	echo_n("        type: 'number',");
	echo_n("        label: 'MA20',");
	echo_n('        calc: function (dt, row) {');
	echo_n('          // calculate average of closing value for last 20 days,');
	echo_n('          // if we are 20 or more days into the data set');
	echo_n('          if (row >= days20 - 1) {');
	echo_n('            var total = 0;');
	echo_n('            for (var i = 0; i < days20; i++) {');
	echo_n('              total += dt.getValue(row - i, 3);');
	echo_n('            }');
	echo_n('            var avg = total / days20;');
	echo_n('            return {v: avg, f: avg.toFixed(2)};');
	echo_n('          }');
	echo_n('          else {');
	echo_n('            // return null for < x days');
	echo_n('            return null;');
	echo_n('          }');
	echo_n('        }');
	echo_n('      }');
}

// 季線
function show_ma60()
{
	echo_n('      var days60 = 60;');
	echo_n('      var ma60 = {');
	echo_n("        type: 'number',");
	echo_n("        label: 'MA60',");
	echo_n('        calc: function (dt, row) {');
	echo_n('          // calculate average of closing value for last 60 days,');
	echo_n('          // if we are 60 or more days into the data set');
	echo_n('          if (row >= days60 - 1) {');
	echo_n('            var total = 0;');
	echo_n('            for (var i = 0; i < days60; i++) {');
	echo_n('              total += dt.getValue(row - i, 3);');
	echo_n('            }');
	echo_n('            var avg = total / days60;');
	echo_n('            return {v: avg, f: avg.toFixed(2)};');
	echo_n('          }');
	echo_n('          else {');
	echo_n('            // return null for < x days');
	echo_n('            return null;');
	echo_n('          }');
	echo_n('        }');
	echo_n('      }');
}

function show_sii_candlestick_chart($prices)
{
	echo_n('  <script type="text/javascript">');
	echo_n('    google.load("visualization", "1", {packages:["corechart"], callback: drawChart1_sii});');
	echo_n('    function drawChart1_sii() {');
	echo_n('      var data = new google.visualization.DataTable();');
	echo_n("      data.addColumn('date', 'Date');");
	echo_n("      data.addColumn('number', 'Low');");
	echo_n("      data.addColumn('number', 'Open');");
	echo_n("      data.addColumn('number', 'Close');");
	echo_n("      data.addColumn('number', 'High');");

	foreach($prices as $date=>$price)
	{
		echo_n("      data.addRow([new Date('" . str_replace('/','-',$date) . "'), " .
			$price[0] . ', ' . $price[1] . ', ' . $price[2] . ', ' . $price[3] . ']);');
	}

//	show_ma5();
//	show_ma20();

	echo_n('      var view = new google.visualization.DataView(data);');
//	echo_n('      view.setColumns([0, 1, 2, 3, 4, ma5, ma20]);');
	echo_n('      view.setColumns([0, 1, 2, 3, 4]);');
	echo_n('      var options = {');
	echo_n('        legend: { position: "none"},');
	//	echo_n('        hAxis: { title: "日期"},');
	//	echo_n('        vAxis: { title: "股價"},');
	echo_n('        height: 500,');
	echo_n('        width: 1000,');
	echo_n('        chartArea: {');
	echo_n('          left: "8%",');
	echo_n('          width: "90%",');
	echo_n('          top: "10%",');
	echo_n('          height: "80%"');
	echo_n('        },');
	echo_n('        bar: {groupWidth: "90%"},');
	echo_n('        candlestick: {');
    echo_n('          risingColor: { strokeWidth: 0, fill: "#a52714" }, // red');
    echo_n('          fallingColor: { strokeWidth: 0, fill: "#0f9d58" } // green');
    echo_n('        },');
	echo_n('        series: {');
	echo_n('          0: {type: "candlesticks"},');
	echo_n('          1: {type: "line"},');
	echo_n('          2: {type: "line"},');
	echo_n('        },');
	echo_n('      };');
	echo_n('      var chart = new google.visualization.ComboChart(document.querySelector("#candlestick_chart_div_sii"));');
	echo_n('      chart.draw(view, options);');
	echo_n('    }');
	echo_n('  </script>');
	echo_n('  <div id="candlestick_chart_div_sii"></div>');
}

function show_stock_candlestick_chart($id, $prices)
{
	echo_n('  <script type="text/javascript">');
	echo_n('    google.load("visualization", "1", {packages:["corechart"], callback: drawChart1_' . $id . '});');
	echo_n('    function drawChart1_' . $id . '() {');
	echo_n('      var data = new google.visualization.DataTable();');
	echo_n("      data.addColumn('date', 'Date');");
	echo_n("      data.addColumn('number', 'Low');");
	echo_n("      data.addColumn('number', 'Open');");
	echo_n("      data.addColumn('number', 'Close');");
	echo_n("      data.addColumn('number', 'High');");

	foreach($prices as $date=>$price)
	{
		echo_n("      data.addRow([new Date('" . str_replace('/','-',$date) . "'), " .
			$price[0] . ', ' . $price[1] . ', ' . $price[2] . ', ' . $price[3] . ']);');
	}

//	show_ma5();
//	show_ma20();

	echo_n('      var view = new google.visualization.DataView(data);');
//	echo_n('      view.setColumns([0, 1, 2, 3, 4, ma5, ma20]);');
	echo_n('      view.setColumns([0, 1, 2, 3, 4]);');
	echo_n('      var options = {');
	echo_n('        legend: { position: "none"},');
	//	echo_n('        hAxis: { title: "日期"},');
	//	echo_n('        vAxis: { title: "股價"},');
	echo_n("        height: 500,");
	echo_n("        width: 1000,");
	echo_n('        chartArea: {');
	echo_n("          left: '8%',");
	echo_n("          width: '90%',");
	echo_n("          top: '10%',");
	echo_n("          height: '80%'");
	echo_n('        },');
	echo_n("        bar: {groupWidth: '90%'},");
	echo_n("        candlestick: {");
	echo_n("          risingColor: { strokeWidth: 0, fill: '#a52714' }, // red");
	echo_n("          fallingColor: { strokeWidth: 0, fill: '#0f9d58' } // green");
	echo_n("        },");
	echo_n("        series: {");
	echo_n("          0: {type: 'candlesticks'},");
	echo_n("          1: {type: 'line'},");
	echo_n("          2: {type: 'line'},");
	echo_n("        },");
	echo_n('      };');
	echo_n("      var chart = new google.visualization.ComboChart(document.querySelector('#candlestick_chart_div_" . $id . "'));");
	echo_n('      chart.draw(view, options);');
	echo_n('    }');
	echo_n('  </script>');
	echo_n('  <div id="candlestick_chart_div_' . $id . '"></div>');
}

function show_stock_candlestick_chart_with_pepo($id, $prices, $pepos)
{
	echo_n('  <script type="text/javascript">');
	echo_n('    google.load("visualization", "1", {packages:["corechart"], callback: drawChart1_pepo_' . $id . '});');
	echo_n('    function drawChart1_pepo_' . $id . '() {');
	echo_n('      var data = new google.visualization.DataTable();');
	echo_n("      data.addColumn('date', 'Date');");
	echo_n("      data.addColumn('number', 'Low');");
	echo_n("      data.addColumn('number', 'Open');");
	echo_n("      data.addColumn('number', 'Close');");
	echo_n("      data.addColumn('number', 'High');");
	echo_n("      data.addColumn('number', 'expHigh');");
	echo_n("      data.addColumn('number', 'expLow');");

	$high = 0;
	$low = 0;
	$found_first_in_pepo = false;
	foreach($prices as $date=>$price)
	{
		$found_in_pepo = false;
		foreach($pepos as $pepo)
		{
			if ($date == $pepo->evaluate_date)
			{
				$found_in_pepo = true;
				if ($found_first_in_pepo == false)
					$found_first_in_pepo = true;
				$high = str_replace(",", "", decimal2($pepo ->xdr_estimated->high));
				$low = str_replace(",", "", decimal2($pepo ->xdr_estimated->low));
				echo_n("      data.addRow([new Date('" . str_replace('/','-',$date) . "'), " .
					$price[0] . ', ' . $price[1] . ', ' . $price[2] . ', ' . $price[3] . ', ' . $high . ', ' . $low . ']);');
				break;
			}
		}
		if (!$found_in_pepo)
		{
			if (!$found_first_in_pepo)
			{
				$high = $price[3];
				$low = $price[0];
			}
			echo_n("      data.addRow([new Date('" . str_replace('/','-',$date) . "'), " .
				$price[0] . ', ' . $price[1] . ', ' . $price[2] . ', ' . $price[3] . ', ' . $high . ', ' . $low . ']);');
		}
	}

	// 20160420: remove ma5 and ma20 to simplify visualization
	//show_ma5();
	//show_ma20();

	echo_n('      var view = new google.visualization.DataView(data);');
	//echo_n('      view.setColumns([0, 1, 2, 3, 4, ma5, ma20, 5, 6]);');
	echo_n('      view.setColumns([0, 1, 2, 3, 4, 5, 6]);');
	echo_n('      var options = {');
	echo_n('        legend: { position: "none"},');
//	echo_n('        hAxis: { title: "日期"},');
//	echo_n('        vAxis: { title: "股價"},');
	echo_n('        height: 500,');
	echo_n('        width: 1000,');
	echo_n('        chartArea: {');
	echo_n("          left: '8%',");
	echo_n("          width: '90%',");
	echo_n("          top: '10%',");
	echo_n("          height: '80%'");
	echo_n('        },');
	echo_n("        bar: {groupWidth: '90%'},");
	echo_n('        candlestick: {');
	echo_n("          risingColor: { strokeWidth: 0, fill: '#a52714' }, // red");
	echo_n("          fallingColor: { strokeWidth: 0, fill: '#0f9d58' } // green");
	echo_n('        },');
	echo_n('        series: {');
	echo_n("          0: {type: 'candlesticks'},");
	echo_n("          1: {type: 'line'},");
	echo_n("          2: {type: 'line'},");
	echo_n("          3: {type: 'line'},");
	echo_n("          4: {type: 'line'},");
	echo_n('        },');
	echo_n('      };');
	echo_n("      var chart = new google.visualization.ComboChart(document.querySelector('#candlestick_chart_pepo_div_" . $id . "'));");
	echo_n('      chart.draw(view, options);');
	echo_n('    }');
	echo_n('  </script>');
	echo_n('  <div id="candlestick_chart_pepo_div_' . $id . '"></div>');
}

function show_stock_bar_chart($id, $prices)
{
	echo_n('  <script type="text/javascript">');
	echo_n('    google.load("visualization", "1", {packages:["corechart"], callback: drawChart2_' . $id . '});');
	echo_n('    function drawChart2_' . $id . '() {');
	echo_n('      var data = new google.visualization.DataTable();');
	echo_n("      data.addColumn('date', 'Date');");
	echo_n("      data.addColumn('number', 'kstocks');");
	foreach($prices as $date=>$price)
	{
		echo_n("      data.addRow([new Date('" . str_replace('/','-',$date) . "'), " . ($price[4]/1000) . ']);');
	}
	echo_n('      var view = new google.visualization.DataView(data);');
	echo_n('      view.setColumns([0, 1]);');
	echo_n('      var options = {');
	echo_n('        legend: { position: "none"},');
//	echo_n('        hAxis: { title: "日期"},');
//	echo_n('        vAxis: { title: "張數"},');
	echo_n('        height: 200,');
	echo_n('        width: 1000,');
	echo_n('        chartArea: {');
	echo_n("          left: '8%',");
	echo_n("          width: '90%',");
	echo_n("          top: '5%',");
	echo_n("          height: '75%'");
	echo_n('        },');
	echo_n("        series: { 0: {type: 'bars'} }");
	echo_n('      };');
	echo_n("      var chart = new google.visualization.ColumnChart(document.querySelector('#bar_chart_div_" . $id . "'));");
	echo_n('      chart.draw(view, options);');
	echo_n('    }');
	echo_n('  </script>');
	echo_n('  <div id="bar_chart_div_' . $id . '"></div>');
}

// 網頁尾
function show_webpage_tail()
{
	echo_n('  </body>');
	echo_n('</html>');
	echo_n('');
}

?>