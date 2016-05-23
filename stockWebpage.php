﻿<?php

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
	echo_n('    <thead><th>代號<th>名稱<th>行業別<th>上市櫃別<th>財務報表<th>上市櫃時間'.
			'<th>股價<th>股價排名<th>月營收年增率<th>月營收年增率排名</thead>');
	echo '    <tbody><tr>';
	echo '<td>' . $stock->id;
	echo '<td>' . $stock->name;
	echo '<td>' . $stock->industry;
	echo '<td>' . ($stock->market=='sii'?'上市':'上櫃');
	echo '<td>' . ($stock->report=='cr'?'合併':'個別');
	echo '<td>' . $stock->onyyyy . $stock->onmm;

	if ($price_rank == -1)
	{
		echo '<td>';
		echo '<td>';
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
		echo '<td>';
		echo '<td>';
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

// 最近四年還原股價與本益比
function show_idr_per($pepos)
{
	$pepo_latest = count($pepos)-1;

	// 四年還原股價高低檔 / 四年EPS / 四年本益比高低檔
	echo_n('  <table>');
	echo_n('    <caption>歷史資料(*為今年推估值)</caption>');
	echo '    <thead><th>年度';
	echo '<th>' . substr(date('Y'), 0, 4) . "*";
	foreach ($pepos[$pepo_latest]->idr as $year => $idr)
		echo '<th>' . $year;
	echo_n('</thead>');
	echo_n('    <tbody>');

	echo '      <tr>';
	echo '<td>還原股價高檔';
	echo '<td>' . decimal2($pepos[$pepo_latest]->idr_estimated->high);
	foreach ($pepos[$pepo_latest]->idr as $year => $idr)
		echo '<td>' . decimal2($idr->high);
	echo_n('');

	echo '      <tr>';
	echo '<td>還原股價低檔';
	echo '<td>' . decimal2($pepos[$pepo_latest]->idr_estimated->low);
	foreach ($pepos[$pepo_latest]->idr as $year => $idr)
		echo '<td>' . decimal2($idr->low);
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
	foreach ($xbrls as $xbrl)
		echo '<th>' . $xbrl->season;
	echo_n('</thead>');
	echo_n('    <tbody>');

	echo '      <tr>';
	echo '<td>股本(億)';
	foreach ($xbrls as $xbrl)
		echo '<td>' . decimal2($xbrl->stock/100000000);
	echo_n('');

	echo '      <tr>';
	echo '<td>營收(億)';
	foreach ($xbrls as $xbrl)
		echo '<td>' . decimal2($xbrl->revenue/100000000);
	echo_n('');

	echo '      <tr>';
	echo '<td>稅後淨利(億)';
	foreach ($xbrls as $xbrl)
		echo '<td>' . decimal2($xbrl->nopat/100000000);
	echo_n('');

	echo '      <tr>';
	echo '<td>EPS';
	foreach ($xbrls as $xbrl)
		echo '<td>' . decimal2($xbrl->eps);
	echo_n('');

	echo '      <tr>';
	echo '<td>存貨(億)';
	foreach ($xbrls as $xbrl)
		echo '<td>' . decimal2($xbrl->inventory/100000000);
	echo_n('');

	echo '      <tr>';
	echo '<td>存貨營收比';
	foreach ($xbrls as $xbrl)
		echo '<td>' . percent($xbrl->inventory/$xbrl->revenue);
	echo_n('');

	echo '      <tr>';
	echo '<td>財報公布日';
	foreach ($xbrls as $xbrl)
		echo '<td>' . $xbrl->publish;
	echo_n('');

	echo_n('    </tbody>');
	echo_n('  </table><br>');
}

// 最近八月月營收
function show_monthly_revenue($monthly_revenue)
{
	echo_n('  <table>');
	echo_n('    <caption>月營收</caption>');
	echo '    <thead><th>月份';
	foreach ($monthly_revenue->thisMonth as $month => $revenue)
		echo '<th>' . $month;
	echo_n('</thead>');
	echo_n('    <tbody>');
	
	echo '      <tr>';
	echo '<td>今年本月營收(億)';
	$yoys = array();
	foreach ($monthly_revenue->thisMonth as $month => $revenue1)
	{
		echo '<td>' . decimal2($revenue1/100000000);
		array_push($yoys, $revenue1);
	}
	echo_n('');

	echo '      <tr>';
	echo '<td>去年同月營收(億)';
	$ii=0;
	foreach ($monthly_revenue->yearAgo as $month => $revenue2)
	{
		echo '<td>' . decimal2($revenue2/100000000);
		if ($revenue2>0) // avoid devide by zero
		{
			$yoys[$ii] /= $revenue2;
		}
		else
		{
			$yoys[$ii] = 0;
		}
		$yoys[$ii] -= 1;
		$ii++;
	}
	echo_n('');

	echo '      <tr>';
	echo '<td>營收年增率';
	foreach ($yoys as $yoy)
		echo '<td>' . percent($yoy);
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
	    '<th>股價高低點<th>當日收盤<th>上下檔風險*<th>投資建議*</thead>');
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
		echo '<td>' . decimal2($pepo->idr_estimated->high) . "/" . decimal2($pepo->idr_estimated->low);
		echo '<td>' . decimal2($pepo->evaluate_price);
		echo '<td>' . $pepo->potential;
		if ($pepo->verdict == "buy buy buy")
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
	echo_n('    <caption>選股小幫手</caption>');
	echo_n('    <tbody>');

	echo_n('      <tr>');
	echo_n('        <td>' . '高價股族群');
	echo_n('        <td>' . '<input type=button value=" 前十檔 " onClick="self.location=' . "'" . $my_name . "?do=highpriceCheck&begin=0'" . '">');
	echo_n('        <td>' . '<input type=button value=" 11-20檔" onClick="self.location=' . "'" . $my_name . "?do=highpriceCheck&begin=10'" . '">');
	echo_n('        <td>' . '<input type=button value=" 21-30檔" onClick="self.location=' . "'" . $my_name . "?do=highpriceCheck&begin=20'" . '">');
	echo_n('        <td>' . '<input type=button value=" 31-40檔" onClick="self.location=' . "'" . $my_name . "?do=highpriceCheck&begin=30'" . '">');
	echo_n('        <td>' . '<input type=button value=" 41-50檔" onClick="self.location=' . "'" . $my_name . "?do=highpriceCheck&begin=40'" . '">');
	echo_n('        <td>' . '<input type=button value=" 51-60檔" onClick="self.location=' . "'" . $my_name . "?do=highpriceCheck&begin=50'" . '">');
	echo_n('        <td>' . '<input type=button value=" 61-70檔" onClick="self.location=' . "'" . $my_name . "?do=highpriceCheck&begin=60'" . '">');
	echo_n('        <td>' . '<input type=button value=" 71-80檔" onClick="self.location=' . "'" . $my_name . "?do=highpriceCheck&begin=70'" . '">');
	echo_n('        <td>' . '<input type=button value=" 81-90檔" onClick="self.location=' . "'" . $my_name . "?do=highpriceCheck&begin=80'" . '">');
	echo_n('        <td>' . '<input type=button value=" 91-100檔" onClick="self.location=' . "'" . $my_name . "?do=highpriceCheck&begin=90'" . '">');

	echo_n('      <tr>');
	echo_n('        <td>' . '月營收年增率');
	echo_n('        <td>' . '<input type=button value=" 前十檔 " onClick="self.location=' . "'" . $my_name . "?do=monthyoyCheck&begin=0'" . '">');
	echo_n('        <td>' . '<input type=button value=" 11-20檔" onClick="self.location=' . "'" . $my_name . "?do=monthyoyCheck&begin=10'" . '">');
	echo_n('        <td>' . '<input type=button value=" 21-30檔" onClick="self.location=' . "'" . $my_name . "?do=monthyoyCheck&begin=20'" . '">');
	echo_n('        <td>' . '<input type=button value=" 31-40檔" onClick="self.location=' . "'" . $my_name . "?do=monthyoyCheck&begin=30'" . '">');
	echo_n('        <td>' . '<input type=button value=" 41-50檔" onClick="self.location=' . "'" . $my_name . "?do=monthyoyCheck&begin=40'" . '">');
	echo_n('        <td>' . '<input type=button value=" 51-60檔" onClick="self.location=' . "'" . $my_name . "?do=monthyoyCheck&begin=50'" . '">');
	echo_n('        <td>' . '<input type=button value=" 61-70檔" onClick="self.location=' . "'" . $my_name . "?do=monthyoyCheck&begin=60'" . '">');
	echo_n('        <td>' . '<input type=button value=" 71-80檔" onClick="self.location=' . "'" . $my_name . "?do=monthyoyCheck&begin=70'" . '">');
	echo_n('        <td>' . '<input type=button value=" 81-90檔" onClick="self.location=' . "'" . $my_name . "?do=monthyoyCheck&begin=80'" . '">');
	echo_n('        <td>' . '<input type=button value=" 91-100檔" onClick="self.location=' . "'" . $my_name . "?do=monthyoyCheck&begin=90'" . '">');

	echo_n('    </tbody>');
	echo_n('  </table><br>');

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

	show_ma5();
	show_ma20();

	echo_n('      var view = new google.visualization.DataView(data);');
	echo_n('      view.setColumns([0, 1, 2, 3, 4, ma5, ma20]);');
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

	show_ma5();
	show_ma20();

	echo_n('      var view = new google.visualization.DataView(data);');
	echo_n('      view.setColumns([0, 1, 2, 3, 4, ma5, ma20]);');
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
				$high = str_replace(",", "", decimal2($pepo ->idr_estimated->high));
				$low = str_replace(",", "", decimal2($pepo ->idr_estimated->low));
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