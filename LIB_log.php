<?php

/* Define End of line const */
define("EOL", (isset($_SERVER['HTTP_USER_AGENT'])?("<br>\n"):("\n")));
define("PRE", (isset($_SERVER['HTTP_USER_AGENT'])?("<pre>"):("")));
define("SPRE", (isset($_SERVER['HTTP_USER_AGENT'])?("</pre>"):("")));

/* verbose level setting */
// 100: full verbose, print everything
// 80: print debug message, detail message, alarm message and error message
// 60: print detail message, alarm message and error message
// 40: print alarm message and error message
// 20: print only error message
// 0: no verbose, print nothing
define("FULL_VERBOSE", 100);
define("DEBUG_VERBOSE", 80);
define("LOG_VERBOSE", 60);
define("ALARM_VERBOSE", 40);
define("ERROR_VERBOSE", 20);
define("NO_VERBOSE", 0);
define("Day1", '2015-01-01');

$verbose = ALARM_VERBOSE;

function echo_v($verbose_level, $verbose_string)
{
	global $verbose;

	switch ($verbose_level)
	{
		case FULL_VERBOSE:
			if ($verbose >= FULL_VERBOSE) echo "[_FULL] ", $verbose_string, EOL;
			break;
		case DEBUG_VERBOSE:
			if ($verbose >= DEBUG_VERBOSE) echo "[DEBUG] ", $verbose_string, EOL;
			break;
		case LOG_VERBOSE:
			if ($verbose >= LOG_VERBOSE) echo "[_LOG_] ", $verbose_string, EOL;
			break;
		case ALARM_VERBOSE:
			if ($verbose >= ALARM_VERBOSE) echo "[ALARM] ", $verbose_string, EOL;
			break;
		case ERROR_VERBOSE:
			if ($verbose >= ERROR_VERBOSE) echo "[ERROR] ", $verbose_string, EOL;
	        break;
		case NO_VERBOSE:
			if ($verbose >= NO_VERBOSE) echo "[-----] ", $verbose_string, EOL;
	        break;
	}
}

function echo_n($str)
{
	echo $str . "\n";
}

function today()
{
	return date("Y")."-".date("m")."-".date("d");
}

function sleeping($sec)
{
	for($ii=0;$ii<$sec;$ii++)
	{
		sleep(1);
		echo_v (LOG_VERBOSE, $ii);
	}
}

$years = array(
			'2030', '2029', '2028', '2027', '2026', '2025', '2024', '2023', '2022', '2021',
			'2020', '2019', '2018', '2017', '2016', '2015', '2014', '2013', '2012', '2011',
			'2010', '2009', '2008', '2007', '2006', '2005', '2004', '2003', '2002', '2001',
			'2000', '1999', '1998', '1997', '1996', '1995', '1994', '1993', '1991', '1990'
			);

$months = array('12', '11', '10', '09', '08', '07', '06', '05', '04', '03', '02', '01');

$season_date = array
	(
	"201501" => "2015/05/15",
	"201404" => "2015/03/31",
	"201403" => "2014/11/14",
	"201402" => "2014/08/14",
	"201401" => "2014/05/15",
	"201304" => "2014/03/31",
	"201303" => "2013/11/14",
	"201302" => "2013/08/14",
	"201301" => "2013/05/15",
	"201204" => "2013/03/31",
	"201203" => "2012/10/31",
	"201202" => "2012/08/31",
	"201201" => "2012/04/30",
	"201104" => "2012/03/31",
	"201103" => "2011/10/31",
	"201102" => "2011/08/31",
	"201101" => "2011/04/30",
	"201004" => "2011/03/31",
	"201003" => "2010/10/31",
	"201002" => "2010/08/31",
	"201001" => "2010/04/30"
	);

// monthdata
$yearmonth_enum = array(
    '201912', '201911', '201910', '201909', '201908', '201907', '201906', '201905', '201904', '201903', '201902', '201901',
    '201812', '201811', '201810', '201809', '201808', '201807', '201806', '201805', '201804', '201803', '201802', '201801',
    '201712', '201711', '201710', '201709', '201708', '201707', '201706', '201705', '201704', '201703', '201702', '201701',
    '201612', '201611', '201610', '201609', '201608', '201607', '201606', '201605', '201604', '201603', '201602', '201601',
    '201512', '201511', '201510', '201509', '201508', '201507', '201506', '201505', '201504', '201503', '201502', '201501',
    '201412', '201411', '201410', '201409', '201408', '201407', '201406', '201405', '201404', '201403', '201402', '201401',
    '201312', '201311', '201310', '201309', '201308', '201307', '201306', '201305', '201304', '201303', '201302', '201301',
    '201212', '201211', '201210', '201209', '201208', '201207', '201206', '201205', '201204', '201203', '201202', '201201',
    '201112', '201111', '201110', '201109', '201108', '201107', '201106', '201105', '201104', '201103', '201102', '201101',
    '201012', '201011', '201010', '201009', '201008', '201007', '201006', '201005', '201004', '201003', '201002', '201001',
    );

$season_enum = array(
	'201904', '201903', '201902', '201901', 
	'201804', '201803', '201802', '201801', 
	'201704', '201703', '201702', '201701', 
	'201604', '201603', '201602', '201601', 
	'201504', '201503', '201502', '201501', 
	'201404', '201403', '201402', '201401', 
	'201304', '201303', '201302', '201301', 
	'201204', '201203', '201202', '201201', 
	'201104', '201103', '201102', '201101', 
	'201004', '201003', '201002', '201001', 
	);

//define("USE_MONTHDATA_TO_APPROXIMATE", true);

function get_yearmonth()
{
	return date("Y") . sprintf("%02d", (int)date("m"));
}

function get_latest_scheduled_month($date)
{
	$year = substr($date, 0, 4); //date("Y");
	$month = substr($date, 5, 2); //date("m");
	$day = substr($date, 8, 2); //date("d");

	// Get latest monthData entry
	$toyyyy = $year;
	$tomm = "";
	if ($day >= 10)
	{
		$tomm = sprintf("%02d", (string)(((int)$month - 1) % 12));
		if ((int)($month) < 1)
			$toyyyy = (string)((int)$year - 1);
	}
	else
	{
		$tomm = sprintf("%02d", (string)(((int)$month - 2) % 12));
		if ((int)($month) < 2)
			$toyyyy = (string)((int)$year - 1);
	}
	return $toyyyy . $tomm;
}

// invoked by stockIDGenSql:gen_id_data_sii_sql/gen_id_data_sii_sql
// invoked by xbrlIFRSData:download_ifrs_data_now/xbrlIFRSDataTest
// invoked by xbrlQuery:load_seasonly_xbrl
// 輸入日期, 按照財報死線推算肯定已經在xbrldata的最新財報季度
// 例如 '2015/11/14'=>'201503', '2015/11/13'=>'201502'
function get_latest_scheduled_season($date)
{
	// Get latest xbrlData entry
	$toyyyy = substr($date, 0, 4);
	$toqq = "";
	$seq = date( 'z', strtotime($date)) + 1;
	if ($seq >= 318) // 11/14
	{
		$toqq = '03';
	}
	else if ($seq >= 226) // 8/14
	{
		$toqq = '02';
	}
	else if ($seq > 135) // 5/15
	{
		$toqq = '01';
	}
	else
	{
		$toqq = '04';
		$toyyyy = (string)((int)$toyyyy - 1);
	}

	return $toyyyy . $toqq;
}

function percent($value)
{
	if (is_numeric($value))
		return number_format($value*100, 2) . "%";
	else
		return $value;
}

function million($value)
{
	if (is_numeric($value))
		return number_format($value/1000000, 0);
	else
		return $value;
}

function decimal2($value)
{
	if (is_numeric($value))
		return number_format($value, 2, '.', '');
	else
		return $value;
}

// assign 1 if want to merge JAN/FEB monthly revenue, assign 0 elsewise
define("JAN_FEB_MERGE", 1);

// if only companies IPO more than 2 years are to selected, assign 2
define("OLDER_THAN", 2);

$observed_stocks = array(
	'統一超',
	'新日興',
	'可寧衛',
	'台郡科技',
	// 'F-貿聯',

	// '可成科技',
	// '上緯企業',
	// '誠品生',
	// '利勤實業',
	// '智基科技',
	// '一零四',
	// '樂陞科技',
	// '東隆興業',

	'台積電',
	'聯發科',
	//'研華',
	'鴻海',
	'宏達電',
	//'大立光',
	'漢微科',
	'碩禾',
	'儒鴻',
	//'為升',
	//'晶華酒店',
	//'神準',
	//'瓦城泰統',
	//'璟德電子',
	//'上銀科技',
	//'耕興',
	//'弘塑科技',
	//'其陽',
	//'宏捷科技',
	//'鈺緯',
	//'大豐電',
	//'翔名科技',
);

function year_to_twyear($year)
{
	return (string)((int)$year - 1911);
}

function twyear_to_year($twyear)
{
	return (string)((int)$twyear + 1911);
}

function date_to_twdate($date)
{
	return (string)((int)substr($date,0,4)-1911) . substr($date, 4, 6);
}

function twdate_to_date($twdate)
{
	return (string)((int)substr($twdate, 0, strlen($twdate)-strpos($twdate, '/'))+1911) . "-" .
				substr($twdate, strpos($twdate, '/')+1, 2) . "-" .
				substr($twdate, strpos($twdate, '/')+4, 2);
}

$startT = 0;
$interT = 0;

function stopwatch_start()
{
	global $startT;
	global $interT;
	$startT = round(microtime(true) * 1000);
	$interT = $startT;
}

// period in ms
function stopwatch_inter()
{
	global $interT;
	$now =  round(microtime(true) * 1000);
	$between = $now - $interT;
	$interT = $now;
	return $between;
}

// period in ms
function stopwatch_stop()
{
	global $startT;
	global $interT;
	$now = round(microtime(true) * 1000);
	$between = $now - $startT;
	$startT = 0;
	$interT = 0;
	return $between;
}

function formatstr($str)
{
	$show_len = 0; //48

	if ($show_len==0)
		return $str;

	$strlen = strlen($str);
	if ( $strlen >= $show_len )
	{
		$strret = substr($str, 0, $show_len-3);
		$strret = $strret . "...";
	}
	else
	{
		$strret = $str;
		for($i=$show_len;$i>$strlen-3;$i--)
			$strret = $strret . ".";
	}

	return $strret;
}

?>
