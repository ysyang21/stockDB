<?php

/*
Filename:
	stockWebPage.php

Usage:


Descriptions:
	This file is used to implement UI for both backend and frontend of stock price evaluation system.
*/

include_once("stockVerdict.php");

// 股票簡介及近況
function show_stock_brief($stock, $price_rank, $price, $yoy_rank, $yoy)
{
	echo_n('  <table class="t1">');
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
	echo '<td>' . '<a href="index.php?stockid=' . $stock->id . '">' . $stock->name . '</a>';
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

function show_stock_brief_case($stock, $verdict = 0)
{
	echo_n('  <table class="t1">');
	echo_n('    <caption>股票簡介</caption>');
	$thead = '    <thead><th>代號<th>名稱<th>行業別<th>上市櫃別<th>上市櫃時間<th>分數';
	$thead = $thead . '</thead>';
	echo_n($thead);
	echo '    <tbody><tr>';
	echo '<td>' . '<a href="index.php?stockid=' . $stock->id . '">' . $stock->id . '</a>';
	echo '<td>' . '<a href="case.php?stockid=' . $stock->id . '">' . $stock->name . '</a>';
	echo '<td>' . $stock->industry;
	echo '<td>' . ($stock->market=='sii'?'上市':'上櫃');
	echo '<td>' . $stock->onyyyy . $stock->onmm;
	echo '<td>' . $verdict;

	echo_n('</tbody>');
	echo_n('  </table><br>');
}

// 最近四年還原股價與本益比
function show_idr_per($pepos)
{
	$pepo_latest = count($pepos)-1;

	// 四年還原股價高低檔 / 四年EPS / 四年本益比高低檔
	echo_n('  <table class="t1">');
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

$xbrl_offset = 4;

// 最近至少兩年財務報表
function show_yearly_xbrl($xbrly)
{
	echo_n('  <table class="t1">');
	echo_n('    <caption>財務報表(合併年報或個別年報)</caption>');

	echo '    <thead><th>年度';
	for ($ii = 0; $ii<count($xbrly);$ii++)
		echo '<th>' . substr($xbrly[$ii]->season, 0, 4);
	echo_n('</thead>');
	echo_n('    <tbody>');

	echo '      <tr>';
	echo '<td>營收(億)';
	for ($ii = 0; $ii<count($xbrly);$ii++)
		echo '<td>' . decimal2($xbrly[$ii]->revenue/100000000);
	echo_n('');

	echo '      <tr>';
	echo '<td>營業利益(億)';
	for ($ii = 0; $ii<count($xbrly);$ii++)
		echo '<td>' . decimal2($xbrly[$ii]->income/100000000);
	echo_n('');

	echo '      <tr>';
	echo '<td>稅後淨利(億)';
	for ($ii = 0; $ii<count($xbrly);$ii++)
		echo '<td>' . decimal2($xbrly[$ii]->nopat/100000000);
	echo_n('');

	echo '      <tr>';
	echo '<td>EPS';
	for ($ii = 0; $ii<count($xbrly);$ii++)
		echo '<td>' . decimal2($xbrly[$ii]->eps);
	echo_n('');

	echo '      <tr>';
	echo '<td>存貨(億)';
	for ($ii = 0; $ii<count($xbrly);$ii++)
		echo '<td>' . decimal2($xbrly[$ii]->inventory/100000000);
	echo_n('');

	echo '      <tr>';
	echo '<td>存貨營收比';
	for ($ii = 0; $ii<count($xbrly);$ii++)
		echo '<td>' . percent($xbrly[$ii]->inventory/$xbrly[$ii]->revenue);
	echo_n('');

	echo '      <tr>';
	echo '<td>期末股本(億)';
	for ($ii = 0; $ii<count($xbrly);$ii++)
		echo '<td>' . decimal2($xbrly[$ii]->stock/100000000);
	echo_n('');

	echo '      <tr>';
	echo '<td>財報公布日';
	for ($ii = 0; $ii<count($xbrly);$ii++)
		echo '<td>' . $xbrly[$ii]->publish;
	echo_n('');

	echo_n('    </tbody>');
	echo_n('  </table><br>');
}

// 最近至少八季財務報表
function show_seasonly_xbrl($xbrls)
{
	global $xbrl_offset;

	echo_n('  <table class="t1">');
	echo_n('    <caption>財務報表(單季合併或單季個別)</caption>');

	echo '    <thead><th>季度';
	for ($ii = 0; $ii<count($xbrls)-$xbrl_offset;$ii++)
		echo '<th>' . $xbrls[$ii]['current']->season;
	echo_n('</thead>');
	echo_n('    <tbody>');

	echo '      <tr>';
	echo '<td>營收(億)';
	for ($ii = 0; $ii<count($xbrls)-$xbrl_offset;$ii++)
		echo '<td>' . decimal2($xbrls[$ii]['current']->revenue/100000000);
	echo_n('');

	echo '      <tr>';
	echo '<td>營業利益(億)';
	for ($ii = 0; $ii<count($xbrls)-$xbrl_offset;$ii++)
		echo '<td>' . decimal2($xbrls[$ii]['current']->income/100000000);
	echo_n('');

	echo '      <tr>';
	echo '<td>稅後淨利(億)';
	for ($ii = 0; $ii<count($xbrls)-$xbrl_offset;$ii++)
		echo '<td>' . decimal2($xbrls[$ii]['current']->nopat/100000000);
	echo_n('');

	echo '      <tr>';
	echo '<td>EPS';
	for ($ii = 0; $ii<count($xbrls)-$xbrl_offset;$ii++)
		echo '<td>' . decimal2($xbrls[$ii]['current']->eps);
	echo_n('');

	echo '      <tr>';
	echo '<td>存貨(億)';
	for ($ii = 0; $ii<count($xbrls)-$xbrl_offset;$ii++)
		echo '<td>' . decimal2($xbrls[$ii]['current']->inventory/100000000);
	echo_n('');

	echo '      <tr>';
	echo '<td>存貨營收比';
	for ($ii = 0; $ii<count($xbrls)-$xbrl_offset;$ii++)
		echo '<td>' . percent($xbrls[$ii]['current']->inventory/$xbrls[$ii]['current']->revenue);
	echo_n('');

	echo '      <tr>';
	echo '<td>期末股本(億)';
	for ($ii = 0; $ii<count($xbrls)-$xbrl_offset;$ii++)
		echo '<td>' . decimal2($xbrls[$ii]['current']->stock/100000000);
	echo_n('');

	echo '      <tr>';
	echo '<td>財報公布日';
	for ($ii = 0; $ii<count($xbrls)-$xbrl_offset;$ii++)
		echo '<td>' . $xbrls[$ii]['current']->publish;
	echo_n('');

	echo_n('    </tbody>');
	echo_n('  </table><br>');
}

$monthly_revenue_offset = 2;

// 最近八月月營收
function show_monthly_revenue($months, $verdictm = null)
{
	global $monthly_revenue_offset;

	echo_n('  <table class="t1">');
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
	echo '<td>月營收年增率遞增';
	for ($ii=0;$ii<count($months)-$monthly_revenue_offset;$ii++)
	{
		if ($months[$ii]->corresp > 0) // avoid devide by zero or negative/negative case
		{
			if ($verdictm != null)
				echo ($verdictm[$ii]->月營收年增率遞增?'<td bgcolor="red">':'<td>')  . percent(($months[$ii]->current / $months[$ii]->corresp) - 1);
			else
				echo '<td>' . percent(($months[$ii]->current / $months[$ii]->corresp) - 1);
		}
		else if ($months[$ii]->corresp == 0)
		{
			echo '<td>' . 'DIV/0';
		}
		else // < 0
		{
			echo '<td>' . 'DIV/-';
		}
	}
	echo_n('');

	echo_n('    </tbody>');
	echo_n('  </table><br>');
}

function show_xbrl_core($xbrls, $verdicts)
{
	echo_n('  <table class="t1">');
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
		echo ($verdicts[$ii]->每股盈餘為正?'<td bgcolor="red">':'<td>') . decimal2($xbrls[$ii]['current']->eps);
	}
	echo '<td>1.b';
	echo_n('');

	echo '      <tr>';
	echo '<td>近三季yoy(eps)>0';
	for ($ii=0;$ii<count($xbrls)-4;$ii++)
	{
		if ($xbrls[$ii]['corresp']->eps > 0) // avoid devide by zero or negative/negative case
		{
			echo ($verdicts[$ii]->每股盈餘成長?'<td bgcolor="red">':'<td>') . percent(($xbrls[$ii]['current']->eps/$xbrls[$ii]['corresp']->eps)-1);
		}
		else if ($xbrls[$ii]['corresp']->eps == 0)
		{
			echo '<td>' . 'DIV/0';
		}
		else // < 0
		{
			echo '<td>' . 'DIV/-';
		}
	}
	echo '<td>(每股盈餘EPS[i] / 每股盈餘EPS[i+4])*100%-1';
	echo_n('');

	echo '      <tr>';
	echo '<td>近三季yoy(營收)>0';
	for ($ii=0;$ii<count($xbrls)-4;$ii++)
	{
		if ($xbrls[$ii]['corresp']->revenue > 0) // avoid devide by zero or negative/negative case
		{
			echo ($verdicts[$ii]->營收成長?'<td bgcolor="red">':'<td>') . percent(($xbrls[$ii]['current']->revenue/$xbrls[$ii]['corresp']->revenue)-1);
		}
		else if ($xbrls[$ii]['corresp']->revenue == 0)
		{
			echo '<td>' . 'DIV/0';
		}
		else // < 0
		{
			echo '<td>' . 'DIV/-';
		}
	}
	echo '<td>2.1';
	echo_n('');

	echo '      <tr>';
	echo '<td>近三季yoy(營業利益)>0';
	for ($ii=0;$ii<count($xbrls)-4;$ii++)
	{
		if ($xbrls[$ii]['corresp']->income > 0) // avoid devide by zero or negative/negative case
		{
			echo ($verdicts[$ii]->營業利益成長?'<td bgcolor="red">':'<td>') . percent(($xbrls[$ii]['current']->income/$xbrls[$ii]['corresp']->income)-1);
		}
		else if ($xbrls[$ii]['corresp']->income == 0)
		{
			echo '<td>' . 'DIV/0';
		}
		else // < 0
		{
			echo '<td>' . 'DIV/-';
		}
	}
	echo '<td>2.2';
	echo_n('');

	echo '      <tr>';
	echo '<td>近三季yoy(稅後淨利)>0';
	for ($ii=0;$ii<count($xbrls)-4;$ii++)
	{
		if ($xbrls[$ii]['corresp']->nopat > 0) // avoid devide by zero or negative/negative case
		{
			echo ($verdicts[$ii]->稅後淨利成長?'<td bgcolor="red">':'<td>') . percent(($xbrls[$ii]['current']->nopat/$xbrls[$ii]['corresp']->nopat)-1);
		}
		else if ($xbrls[$ii]['corresp']->nopat == 0)
		{
			echo '<td>' . 'DIV/0';
		}
		else // < 0
		{
			echo '<td>' . 'DIV/-';
		}
	}
	echo '<td>2.4';
	echo_n('');

	echo '      <tr>';
	echo '<td>近三季營業利益率穩定';
	for ($ii=0;$ii<count($xbrls)-4;$ii++)
	{
		if ($xbrls[$ii]['current']->revenue > 0) // avoid devide by zero or negative/negative case
		{
			echo ($verdicts[$ii]->營業利益率穩定?'<td bgcolor="red">':'<td>') . percent($xbrls[$ii]['current']->income/$xbrls[$ii]['current']->revenue);
		}
			else if ($xbrls[$ii]['current']->revenue == 0)
		{
			echo '<td>' . 'DIV/0';
		}
		else // < 0
		{
			echo '<td>' . 'DIV/-';
		}
	}
	echo '<td>1.2';
	echo_n('');

	echo '      <tr>';
	echo ($verdicts[0]->累計現金流量正遞增?'<td bgcolor="red">':'<td>') . '累計自由現金流量正遞增';
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
		$current_inventory = ($xbrls[$ii]['current']->inventory+$xbrls[$ii+1]['current']->inventory)/2;
		if ($current_inventory > 0)
		{
			echo ($verdicts[$ii]->存貨週轉率沒下降?'<td bgcolor="red">':'<td>') . decimal2($xbrls[$ii]['current']->costs/$current_inventory);
		}
		else if ($current_inventory == 0)
		{
			echo '<td>' . 'DIV/0';
		}
		else // < 0
		{
			echo '<td>' . 'DIV/-';
		}
	}
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

function show_xbrl_group_a($xbrls)
{
	global $xbrl_offset;

	echo_n('  <table class="t1">');
	echo_n('    <caption>獲利能力</caption>');

	echo '    <thead><th>季度';
	for ($ii=0;$ii<count($xbrls)-$xbrl_offset;$ii++)
		echo '<th>' . $xbrls[$ii]['current']->season;
	echo '<th>公式';
	echo_n('</thead>');
	echo_n('    <tbody>');

	echo '      <tr>';
	echo '<td>1.1 營業毛利率(%)';
	for ($ii=0;$ii<count($xbrls)-$xbrl_offset;$ii++)
	{
		$xbrl = $xbrls[$ii]['current'];
		if ($xbrl->revenue > 0)
			echo '<td>' . percent($xbrl->profit/$xbrl->revenue);
		else if ($xbrl->revenue == 0)
			echo '<td>DIV/0';
		else
			echo '<td>DIV/-';
	}
	echo '<td>營業毛利Profit / 營業收入Revenue';
	echo_n('');

	echo '      <tr>';
	echo '<td>1.2 營業利益率(%)';
	for ($ii=0;$ii<count($xbrls)-$xbrl_offset;$ii++)
	{
		$xbrl = $xbrls[$ii]['current'];
		if ($xbrl->revenue > 0)
			echo '<td>' . percent($xbrl->income/$xbrl->revenue);
		else if ($xbrl->revenue == 0)
			echo '<td>DIV/0';
		else
			echo '<td>DIV/-';
	}
	echo '<td>營業利益Income / 營業收入Revenue';
	echo_n('');

	echo '      <tr>';
	echo '<td>1.3 稅前淨利率(%)';
	for ($ii=0;$ii<count($xbrls)-$xbrl_offset;$ii++)
	{
		$xbrl = $xbrls[$ii]['current'];
		if ($xbrl->revenue > 0)
			echo '<td>' . percent($xbrl->nopbt/$xbrl->revenue);
		else if ($xbrl->revenue == 0)
			echo '<td>DIV/0';
		else
			echo '<td>DIV/-';
	}
	echo '<td>稅前淨利Nopbt / 營業收入Revenue';
	echo_n('');

	echo '      <tr>';
	echo '<td>1.4 稅後淨利率(%)';
	for ($ii=0;$ii<count($xbrls)-$xbrl_offset;$ii++)
	{
		$xbrl = $xbrls[$ii]['current'];
		if ($xbrl->revenue > 0)
			echo '<td>' . percent($xbrl->nopat/$xbrl->revenue);
		else if ($xbrl->revenue == 0)
			echo '<td>DIV/0';
		else
			echo '<td>DIV/-';
	}
	echo '<td>稅後淨利Nopat / 營業收入Revenue';
	echo_n('');

	echo '      <tr>';
	echo '<td>1.5 每股淨值';
	for ($ii=0;$ii<count($xbrls)-$xbrl_offset;$ii++)
	{
		$xbrl = $xbrls[$ii]['current'];
		echo '<td>' . decimal2(($xbrl->equity-$xbrl->noncontrol)*10/$xbrl->stock);
	}
	echo '<td>股東權益(Equity-Noncontrol) / 期末股本(Stock/10)';
	echo_n('');

	echo '      <tr>';
	echo '<td>1.6 每股營業額';
	for ($ii=0;$ii<count($xbrls)-$xbrl_offset;$ii++)
	{
		$xbrl = $xbrls[$ii]['current'];
		echo '<td>' . decimal2($xbrl->revenue*10/$xbrl->stock);
	}
	echo '<td>營業收入Revenue / 期末股本(Stock/10)';
	echo_n('');

	echo '      <tr>';
	echo '<td>1.7 每股營業利益';
	for ($ii=0;$ii<count($xbrls)-$xbrl_offset;$ii++)
	{
		$xbrl = $xbrls[$ii]['current'];
		echo '<td>' . decimal2($xbrl->income*10/$xbrl->stock);
	}
	echo '<td>營業利益Income / 期末股本(Stock/10)';
	echo_n('');

	echo '      <tr>';
	echo '<td>1.8 每股稅前淨利';
	for ($ii=0;$ii<count($xbrls)-$xbrl_offset;$ii++)
	{
		$xbrl = $xbrls[$ii]['current'];
		echo '<td>' . decimal2($xbrl->nopbt*10/$xbrl->stock);
	}
	echo '<td>稅前淨利Nopbt / 期末股本(Stock/10)';
	echo_n('');

	echo '      <tr>';
	echo '<td>1.9 股東權益報酬率(%)';
	for ($ii=0;$ii<count($xbrls)-$xbrl_offset;$ii++)
	{
		$xbrl = $xbrls[$ii]['current'];
		$xbrl_1 = $xbrls[$ii+1]['current'];
	 	echo '<td>' . percent($xbrl->nopat*2/($xbrl->equity+$xbrl_1->equity));
	}
	echo '<td>稅後淨利Nopat / 期初期末平均股東權益Equity[i,i+1]/2';
	echo_n('');

	echo '      <tr>';
	echo '<td>1.a 資產報酬率(%)';
	for ($ii=0;$ii<count($xbrls)-$xbrl_offset;$ii++)
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
	for ($ii=0;$ii<count($xbrls)-$xbrl_offset;$ii++)
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
	global $xbrl_offset;

	echo_n('  <table class="t1">');
	echo_n('    <caption>經營績效</caption>');

	echo '    <thead><th>季度';
	for ($ii=0;$ii<count($xbrls)-$xbrl_offset;$ii++)
	{
		$xbrl = $xbrls[$ii]['current'];
		echo '<th>' . $xbrl->season;
	}
	echo '<th>公式';
	echo_n('</thead>');
	echo_n('    <tbody>');

	echo '      <tr>';
	echo '<td>2.1 營收年增率(%)';
	for ($ii=0;$ii<count($xbrls)-$xbrl_offset;$ii++)
	{
		$xbrl = $xbrls[$ii];
		if ($xbrl['corresp']->revenue > 0)
			echo '<td>' . percent(($xbrl['current']->revenue/$xbrl['corresp']->revenue)-1);
		else if ($xbrl['corresp']->revenue == 0)
			echo '<td>DIV/0';
		else
			echo '<td>DIV/-';
	}
	echo '<td>(營業收入Revenue[i] / 去年同期營業收入Revenue[i+4])*100%-1';
	echo_n('');

	echo '      <tr>';
	echo '<td>2.2 營業利益年增率(%)';
	for ($ii=0;$ii<count($xbrls)-$xbrl_offset;$ii++)
	{
		$xbrl = $xbrls[$ii];
		if ($xbrl['corresp']->income > 0)
			echo '<td>' . percent(($xbrl['current']->income/$xbrl['corresp']->income)-1);
		else if ($xbrl['corresp']->income == 0)
			echo '<td>DIV/0';
		else
			echo '<td>DIV/-';
	}
	echo '<td>(營業利益Income[i] / 去年同期營業利益Income[i+4])*100%-1';
	echo_n('');

	echo '      <tr>';
	echo '<td>2.3 稅前淨利年增率(%)';
	for ($ii=0;$ii<count($xbrls)-$xbrl_offset;$ii++)
	{
		$xbrl = $xbrls[$ii];
		if ($xbrl['corresp']->nopbt > 0)
			echo '<td>' . percent(($xbrl['current']->nopbt/$xbrl['corresp']->nopbt)-1);
		else if ($xbrl['corresp']->nopbt == 0)
			echo '<td>DIV/0';
		else
			echo '<td>DIV/-';
	}
	echo '<td>(稅前淨利Nopbt[i] / 去年同期稅前淨利Nopbt[i+4])*100%-1';
	echo_n('');

	echo '      <tr>';
	echo '<td>2.4 稅後淨利年增率(%)';
	for ($ii=0;$ii<count($xbrls)-$xbrl_offset;$ii++)
	{
		$xbrl = $xbrls[$ii];
		if ($xbrl['corresp']->nopat > 0)
			echo '<td>' . percent(($xbrl['current']->nopat/$xbrl['corresp']->nopat)-1);
		else if ($xbrl['corresp']->nopat == 0)
			echo '<td>DIV/0';
		else
			echo '<td>DIV/-';
	}
	echo '<td>(稅後淨利Nopat[i] / 去年同期稅後淨利Nopat[i+4])*100%-1';
	echo_n('');

	echo '      <tr>';
	echo '<td>2.5 總資產年增率(%)';
	for ($ii=0;$ii<count($xbrls)-$xbrl_offset;$ii++)
	{
		$xbrl = $xbrls[$ii];
		if ($xbrl['corresp']->assets > 0)
			echo '<td>' . percent(($xbrl['current']->assets/$xbrl['corresp']->assets)-1);
		else if ($xbrl['corresp']->assets == 0)
			echo '<td>DIV/0';
		else
			echo '<td>DIV/-';
	}
	echo '<td>(總資產Assets[i] / 去年同期總資產Assets[i+4])*100%-1';
	echo_n('');

	echo '      <tr>';
	echo '<td>2.6 淨值年增率(%)';
	for ($ii=0;$ii<count($xbrls)-$xbrl_offset;$ii++)
	{
		$xbrl = $xbrls[$ii];
		if ($xbrl['corresp']->equity > 0)
			echo '<td>' . percent(($xbrl['current']->equity/$xbrl['corresp']->equity)-1);
		else if ($xbrl['corresp']->equity == 0)
			echo '<td>DIV/0';
		else
			echo '<td>DIV/-';
	}
	echo '<td bgcolor="yellow">(股東權益Equity[i] / 去年同期股東權益Equity[i+4])*100%-1';
	echo_n('');

	echo '      <tr>';
	echo '<td>2.7 固定資產年增率(%)';
	for ($ii=0;$ii<count($xbrls)-$xbrl_offset;$ii++)
	{
		$xbrl = $xbrls[$ii];
		if ($xbrl['corresp']->fixedassets > 0)
			echo '<td>' . percent(($xbrl['current']->fixedassets/$xbrl['corresp']->fixedassets)-1);
		else if ($xbrl['corresp']->fixedassets == 0)
			echo '<td>DIV/0';
		else
			echo '<td>DIV/-';
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
	global $xbrl_offset;

	echo_n('  <table class="t1">');
	echo_n('    <caption>償債能力</caption>');

	echo '    <thead><th>季度';
	for ($ii=0;$ii<count($xbrls)-$xbrl_offset;$ii++)
	{
		$xbrl = $xbrls[$ii]['current'];
		echo '<th>' . $xbrl->season;
	}
	echo '<th>公式';
	echo_n('</thead>');
	echo_n('    <tbody>');

	echo '      <tr>';
	echo '<td>3.1 流動比率(%)';
	for ($ii=0;$ii<count($xbrls)-$xbrl_offset;$ii++)
	{
		$xbrl = $xbrls[$ii]['current'];
		echo '<td>' . percent($xbrl->currentassets/$xbrl->currentliabilities);
	}
	echo '<td>流動資產CurrentAssets / 流動負債CurrentLiabilities';
	echo_n('');

	echo '      <tr>';
	echo '<td>3.2 速動比率(%)';
	for ($ii=0;$ii<count($xbrls)-$xbrl_offset;$ii++)
	{
		$xbrl = $xbrls[$ii]['current'];
		echo '<td>' . percent(($xbrl->currentassets-$xbrl->inventory-$xbrl->othercurrentassets)/$xbrl->currentliabilities);
	}
	echo '<td>速動資產(CurrentAssets-Inventory-OtherCurrentAssets) / 流動負債CurrentLiabilities';
	echo_n('');

	echo '      <tr>';
	echo '<td>3.3 負債比率(%)';
	for ($ii=0;$ii<count($xbrls)-$xbrl_offset;$ii++)
	{
		$xbrl = $xbrls[$ii]['current'];
		echo '<td>' . percent($xbrl->liabilities/$xbrl->assets);
	}
	echo '<td>負債總額Liabilities / 資產總額Assets';
	echo_n('');

	echo '      <tr>';
	echo '<td>3.4 利息保障倍數';
	for ($ii=0;$ii<count($xbrls)-$xbrl_offset;$ii++)
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
	global $xbrl_offset;

	echo_n('  <table class="t1">');
	echo_n('    <caption>經營能力</caption>');

	echo '    <thead><th>季度';
	for ($ii=0;$ii<count($xbrls)-$xbrl_offset;$ii++)
	{
		$xbrl = $xbrls[$ii]['current'];
		echo '<th>' . $xbrl->season;
	}
	echo '<th>公式';
	echo_n('</thead>');
	echo_n('    <tbody>');

	echo '      <tr>';
	echo '<td>4.1 應收帳款週轉率(次)';
	for ($ii=0;$ii<count($xbrls)-$xbrl_offset;$ii++)
	{
		$xbrl = $xbrls[$ii]['current'];
		$xbrl_1 = $xbrls[$ii+1]['current'];
		$accountsreceivable = ($xbrl->arn+$xbrl->arnr+$xbrl_1->arn+$xbrl_1->arnr)/2;
		if ($accountsreceivable > 0)
			echo '<td>' . decimal2($xbrl->revenue/$accountsreceivable);
		else // == 0
			echo '<td>DIV/0';
	}
	echo '<td>營業收入(reveune) / 期初期末平均應收帳款(AccountsReceivableNet[i,i+1] + AccountReceivableRelatedPartiesNet[i,i+1])/2';
	echo_n('');

	echo '      <tr>';
	echo '<td>4.2 存貨週轉率(次)';
	for ($ii=0;$ii<count($xbrls)-$xbrl_offset;$ii++)
	{
		$xbrl = $xbrls[$ii]['current'];
		$xbrl_1 = $xbrls[$ii+1]['current'];
		$turnover = ($xbrl->inventory+$xbrl_1->inventory)/2;
		if ($turnover > 0)
			echo '<td>' . decimal2($xbrl->costs/$turnover);
		else // == 0
			echo '<td>DIV/0';
	}
	echo '<td>銷貨成本(Costs) / 期初期末平均存貨(Inventory[i,i+1])/2';
	echo_n('');

	echo '      <tr>';
	echo '<td>4.3 固定資產週轉率(次)';
	for ($ii=0;$ii<count($xbrls)-$xbrl_offset;$ii++)
	{
		$xbrl = $xbrls[$ii]['current'];
		if ($xbrl->fixedassets > 0)
			echo '<td>' . decimal2($xbrl->revenue/$xbrl->fixedassets);
		else // == 0
			echo '<td>DIV/0';
	}
	echo '<td>營業收入Revenue / 固定資產FixedAssets';
	echo_n('');

	echo '      <tr>';
	echo '<td>4.4 總資產週轉率(次)';
	for ($ii=0;$ii<count($xbrls)-$xbrl_offset;$ii++)
	{
		$xbrl = $xbrls[$ii]['current'];
		echo '<td>' . decimal2($xbrl->revenue/$xbrl->assets);
	}
	echo '<td>營業收入Revenue / 總資產Assets';
	echo_n('');

	echo '      <tr>';
	echo '<td>4.6 淨值週轉率(次)';
	for ($ii=0;$ii<count($xbrls)-$xbrl_offset;$ii++)
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
	global $xbrl_offset;

	echo_n('  <table class="t1">');
	echo_n('    <caption>資本結構</caption>');

	echo '    <thead><th>季度';
	for ($ii=0;$ii<count($xbrls)-$xbrl_offset;$ii++)
	{
		$xbrl = $xbrls[$ii]['current'];
		echo '<th>' . $xbrl->season;
	}
	echo '<th>公式';
	echo_n('</thead>');
	echo_n('    <tbody>');

	echo '      <tr>';
	echo '<td>5.1 負債對淨值比率(%)';
	for ($ii=0;$ii<count($xbrls)-$xbrl_offset;$ii++)
	{
		$xbrl = $xbrls[$ii]['current'];
		echo '<td>' . percent($xbrl->liabilities/$xbrl->equity);
	}
	echo '<td>負債總額Liabilities / 股東權益Equity';
	echo_n('');

	echo '      <tr>';
	echo '<td>5.2 長期資金適合率(%)';
	for ($ii=0;$ii<count($xbrls)-$xbrl_offset;$ii++)
	{
		$xbrl = $xbrls[$ii]['current'];
		if ($xbrl->fixedassets > 0)
			echo '<td>' . percent(($xbrl->equity+$xbrl->noncurrentliabilities-$xbrl->othernoncurrentliabilities)/$xbrl->fixedassets);
		else // == 0
			echo '<td>DIV/0';
	}
	echo '<td bgcolor="yellow">長期資金(Equity-NonCurrentLiabilities-OtherNonCurrentLiabilities) / 固定資產FixedAssets';
	echo_n('');

	echo_n('    </tbody>');
	echo_n('  </table><br>');
}

// 股價評估過程
function show_stock_evaluation($pepos)
{
	echo_n('  <table class="t1">');
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

// 網頁內容 (Frontend), used in index.php
function show_frontend_updater($my_name)
{
	global $observed_stocks;

	echo_n('  <table class="t1">');
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

	// index.php --> show_frontend_updater
	// ?stockid=3008
	// ?stockname=聯發科

	if(isset($_GET['stockid']))
		stockIDCheck($_GET['stockid']);
	else if(isset($_GET['stockname']))
		nameCheck($_GET['stockname']);
}

$a = array(8, 1, 1, 1, 1, 2, 2, 2, 2);

// 網頁內容 (Case Study), used in case.php
function show_casestudy_updater($my_name)
{
	global $observed_stocks;
	global $a;

	echo_n('  <table class="t1">');
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

	echo_n('  <table class="t1">');
	echo_n('    <caption style="color:red">各財務指標的權值分配</caption>');
	echo_n('    <tbody>');
	echo_n('      <tr>');
	echo_n('        <td>' . '核心財務指標');
	echo_n('        <td>' . '分數');
	echo_n('      <tr>');
	echo_n('        <td>' . '近四季每股盈餘為正');
	echo_n('        <td>' . $a[0]);
	echo_n('      <tr>');
	echo_n('        <td>' . '近三季每股盈餘成長');
	echo_n('        <td>' . $a[1]);
	echo_n('      <tr>');
	echo_n('        <td>' . '近三季營收成長');
	echo_n('        <td>' . $a[2]);
	echo_n('      <tr>');
	echo_n('        <td>' . '近三季營業利益成長');
	echo_n('        <td>' . $a[3]);
	echo_n('      <tr>');
	echo_n('        <td>' . '近三季稅後淨利成長');
	echo_n('        <td>' . $a[4]);
	echo_n('      <tr>');
	echo_n('        <td>' . '近三季營業利益率穩定');
	echo_n('        <td>' . $a[5]);
	echo_n('      <tr>');
	echo_n('        <td>' . '近四季存貨週轉率沒下降');
	echo_n('        <td>' . $a[6]);
	echo_n('      <tr>');
	echo_n('        <td>' . '累計現金流量正遞增');
	echo_n('        <td>' . $a[7]);
	echo_n('      <tr>');
	echo_n('        <td>' . '月營收年增率遞增');
	echo_n('        <td>' . $a[8]);
	echo_n('      <tr>');
	echo_n('        <td>' . '滿分');
	echo_n('        <td>' . array_sum($a));

	echo_n('    </tbody>');
	echo_n('  </table>');
	echo_n('  <br>');

	echo_n('  <table class="t1">');
	echo_n('    <caption style="color:red">電腦挑土豆(按一下要等兩分半鐘左右才會算出來, 請耐心等待)</caption>');
	echo_n('    <tbody>');
	echo_n('      <tr>');
	echo_n('        <td>' . '已公佈財報');
	echo_n('        <td>' . '<input type=button value="已公佈財報" onClick="self.location=' . "'" . $my_name . "?do=gradeStocks'" . '">');
	for ($ii=array_sum($a);$ii>=0;$ii--)
		echo_n('        <td>' . '<input type=button value="' . $ii . '分" onClick="self.location=' . "'" . $my_name . "?do=gradeStocks&grade=" . $ii . "'" . '">');
	echo_n('    </tbody>');
	echo_n('  </table>');
	echo_n('  <br>');

	if (date('d')<=10)
	{
		echo_n('  <table class="t1">');
		echo_n('    <caption style="color:red">電腦挑新月份土豆(當月十日之前已公布月營收的股票)</caption>');
		echo_n('    <tbody>');
		echo_n('      <tr>');
		echo_n('        <td>' . '新月份營收');
		echo_n('        <td>' . '<input type=button value="新月份營收" onClick="self.location=' . "'" . $my_name . "?do=gradeNewMonthStocks'" . '">');
		for ($ii=array_sum($a);$ii>=0;$ii--)
			echo_n('        <td>' . '<input type=button value="' . $ii . '分" onClick="self.location=' . "'" . $my_name . "?do=gradeNewMonthStocks&grade=" . $ii . "'" . '">');
		echo_n('    </tbody>');
		echo_n('  </table>');
		echo_n('  <br>');
	}

	$date = today();
	$dayOfYear = date( 'z', strtotime($date));
	// treating 60th day in a leap year (3/1) the same as 59th day in a non-leap year (3/1)
	// this implies treating 59th day in a leap year (2/29) also the same as 59th day in a non-leap year (3/1)
	if (1==date('L', strtotime($date)) and $dayOfYear > 59)
		$dayOfYear--;

	echo_n($date . " is the " . $dayOfYear . "th day of the year.");
	echo_n('  <br>');

	// comments in stockEvaluate.php line 48, 3/31(89), 5/15(134), 8/14(225), 11/15(317)
	if ((69<=$dayOfYear and $dayOfYear<=89) or
		(114<=$dayOfYear and $dayOfYear<=134) or
		(205<=$dayOfYear and $dayOfYear<=225) or
		(297<=$dayOfYear and $dayOfYear<=317))
	{
		echo_n('  <table class="t1">');
		echo_n('    <caption style="color:red">電腦挑新一季土豆(截止日前二十天已公布財報的股票)</caption>');
		echo_n('    <tbody>');
		echo_n('      <tr>');
		echo_n('        <td>' . '新一季財報');
		echo_n('        <td>' . '<input type=button value="新一季財報" onClick="self.location=' . "'" . $my_name . "?do=gradeNewSeasonStocks'" . '">');
		for ($ii=array_sum($a);$ii>=0;$ii--)
			echo_n('        <td>' . '<input type=button value="' . $ii . '分" onClick="self.location=' . "'" . $my_name . "?do=gradeNewSeasonStocks&grade=" . $ii . "'" . '">');
		echo_n('    </tbody>');
		echo_n('  </table>');
		echo_n('  <br>');
	}

	// case.php --> show_casestudy_updater
	// ?stockid=3008
	// ?stockname=聯發科
	// ?do=gradeStocks[&grade=20]
	// ?do=gradeNewSeasonStocks[&grade=20]
	// ?do=gradeNewMonthStocks[&grade=20]

	if(isset($_GET['do']) && isset($_GET['grade']) && function_exists($_GET['do']))
		call_user_func($_GET['do'], $_GET['grade']);
	else if(isset($_GET['do']) && isset($_GET['tag']) && function_exists($_GET['do']))
		call_user_func($_GET['do'], $_GET['tag']);
	else if(isset($_GET['do']) && function_exists($_GET['do']))
		call_user_func($_GET['do']);
	else if(isset($_GET['stockid']))
		stockIDCheck($_GET['stockid']);
	else if(isset($_GET['stockname']))
		nameCheck($_GET['stockname']);
}

// 網頁頭
function show_webpage_header($stage)
{
	global $a;

	echo_n('');
	echo_n('');
	echo_n('<!doctype html>');
	echo_n('<html>');
	echo_n('  <head>');
	echo_n('    <title>Pepo Project ' . $stage . '</title>');
	echo_n('    <meta content="text/html; charset=utf-8" http-equiv="Content-Type">');
	echo_n('      <style type="text/css">');
	echo_n('      .t1{border-collapse: collapse; border: inset;}');
	echo_n('      #header {margin:0 auto;}');
	for ($ii=array_sum($a);$ii>=0;$ii--)
		echo_n("      .stock$ii {clear:both; margin:0 auto;}");
	echo_n('      .container {position:relative; display:inline}');
	echo_n('      .highlight {background:#00FF00;}');
	echo_n('      tbody {border: solid outset;}');
	echo_n('      th {}');
	echo_n('      td {border: solid thin; text-align: left; padding: 2;}');
	echo_n('      .good {color:#FF0000;}');
	echo_n('      input {}');
	echo_n('      .long {color:#0000FF;}');
	echo_n('      form {margin: 1 1; padding: 0;}');
	echo_n('      .profile {float:left; margin:10px 10px 0 10px;}');
	echo_n('      .xbrls {float:left; margin:10px 10px 0 10px;}');
	echo_n('      .xbrly {float:left; margin:10px 10px 0 10px;}');
	echo_n('      .monthly {float:left; margin:10px 10px 0 10px;}');
	echo_n('        #footer {clear:both; margin:0 auto;}');
	echo_n('      </style>');
	echo_n('    <script type="text/javascript" src="https://www.google.com/jsapi"></script>');
	echo_n('    <script type="text/javascript" src="./jquery-1.3.1.js"></script>');

	echo_n('    <script type="text/javascript">');
	for ($ii=array_sum($a);$ii>=0;$ii--)
	{
		echo_n('        $(document).ready(function(){');
		echo_n('	        $(".' . "stock$ii" . '").children().hide().end().children("a").show();');
		echo_n('	        $(".' . "stock$ii" . '").click(function(){');
		echo_n('                ($(".' . "stock$ii" . '").hasClass("highlight")) ?');
		echo_n('        	        ($(".' . "stock$ii" . '").removeClass("highlight").children().hide().end().children("a").show()) :');
		echo_n('        	        ($(".' . "stock$ii" . '").addClass("highlight").children().show());');
		echo_n('	        });');
		echo_n('        });');	
	}
	echo_n('    </script>');

	// echo_n('    <script>');
	// echo_n('		function writeMessage(canvas, message) {');
	// echo_n('			var context = canvas.getContext("2d");');
	// echo_n('			context.clearRect(0, 0, canvas.width, canvas.height);');
	// echo_n('			context.font = "18pt Calibri";');
	// echo_n('			context.fillStyle = "black";');
	// echo_n('			context.fillText(message, 10, 25);');
	// echo_n('		}');
	// echo_n('		function getMousePos(canvas, evt) {');
	// echo_n('			var rect = canvas.getBoundingClientRect();');
	// echo_n('			return {');
	// echo_n('				x: evt.clientX - rect.left,');
	// echo_n('				y: evt.clientY - rect.top');
	// echo_n('			};');
	// echo_n('		}');
	// echo_n('    </script>');

	echo_n('  </head>');
	echo_n('  <body>');

	echo_n('  <input type=button value="Frontend" onClick="self.location=' . "'index.php'" . '">');
	echo_n('  <input type=button value="CaseStudy" onClick="self.location=' . "'case.php'" . '">');

	echo_n ("<div id='header'>");
	date_default_timezone_set ("Asia/Taipei");
	if (isset($_SERVER['HTTP_USER_AGENT'])) echo "<pre>";
	echo_v(NO_VERBOSE, "Start time: " . date("Y-m-d") . " " . date("h:i:sa"));
	if (isset($_SERVER['HTTP_USER_AGENT'])) echo "</pre>";
	$t1 = round(microtime(true) * 1000);
	echo_n ("</div>");

	return $t1;
}

// 網頁尾
function show_webpage_tail($t1)
{
	echo_n ("<div id='footer'>");
	if (isset($_SERVER['HTTP_USER_AGENT'])) echo "<pre>";
	$t2 = round(microtime(true) * 1000);
	echo_v(NO_VERBOSE, "End time: " . date("Y-m-d") . " " . date("h:i:sa"));
	echo_v(NO_VERBOSE, "Duration: " . ($t2 - $t1) . "ms");
	if (isset($_SERVER['HTTP_USER_AGENT'])) echo "</pre>";
	echo_n ("</div>");

	echo_n('  <input type=button value="Frontend" onClick="self.location=' . "'index.php'" . '">');
	echo_n('  <input type=button value="CaseStudy" onClick="self.location=' . "'case.php'" . '">');

	echo_n('  </body>');
	echo_n('</html>');
	echo_n('');
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
	// echo_n('        height: 500,');
	// echo_n('        width: 1000,');
	echo_n("        height: " . (count($prices)>300 ? count($prices) : 300) . ",");
	echo_n("        width: " . (count($prices)>300 ? count($prices)*2 : 600) . ",");
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
	// echo_n('        height: 500,');
	// echo_n('        width: 1000,');
	echo_n("        height: " . (count($prices)>300 ? count($prices) : 300) . ",");
	echo_n("        width: " . (count($prices)>300 ? count($prices)*2 : 600) . ",");
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
	// echo_n('        height: 500,');
	// echo_n('        width: 1000,');
	echo_n("        height: " . (count($prices)>300 ? count($prices) : 300) . ",");
	echo_n("        width: " . (count($prices)>300 ? count($prices)*2 : 600) . ",");
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
	// echo_n('        height: 200,');
	// echo_n('        width: 1000,');
	echo_n("        height: 80,");
	echo_n("        width: " . (count($prices)>300 ? count($prices)*2 : 600) . ",");
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

?>