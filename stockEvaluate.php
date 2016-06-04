<?php

/*
Filename:
	stockEvaluate.php

Usage:


Descriptions:
	This file is used to implement stock price evaluation process.
*/

include_once("LIB_log.php");

include_once("stockIDRQuery.php");
include_once("xbrlQuery.php");
include_once("stockMonthQuery.php");
include_once("stockWebpage.php");

class pepoData
{
    // property declaration
	public $id = "";
	public $onyyyy = "";
	public $revenue_lastyear = 0;
	public $revenue_yoy_estimated = 0; // 月營收年增率
	public $revenue_estimated = 0;
	public $nopat_over_revenue_estimated = 0; // 稅前淨利率
	public $nopat_estimated = 0;
	public $stock_now = 0;
	public $eps = array();
	public $eps_estimated = 0;
	public $xdr = array();
	public $xdr_estimated = null;
	public $per = array();
	public $per_estimated = null;
	public $evaluate_price = 0;
	public $evaluate_date = "";
	public $stock_position = "";
	public $potential = "";
	public $verdict = 0.0;
	public $monthly_revenue_yoy = array();
}

function query_revenue_estimated($pepo, $date)
{
	// XBRL update deadline: Q1-5/15, Q2-8/14, Q3-11/14, Q4+Y-3/31
	// Available revenue data in place:
	// ******************** Month data ************************** XBRL data *************************
	// 01/01 ~ 01/10:  year_1.(11,10...)            checkQ(year_1.'04') + year_1.(Q3,Q2,Q1)
	// 01/11 ~ 02/10:  year_1.(*).......            checkQ(year_1.'04') + year_1.(Q3,Q2,Q1)
	// 02/10 ~ 03/10:  year.(1    ) + year_1.(*)    checkQ(year_1.'04') + year_1.(Q3,Q2,Q1)
	// 03/11 ~ 03/31:  year.(2,1  ) + year_1.(*)    checkQ(year_1.'04') + year_1.(Q3,Q2,Q1)
	// 04/01 ~ 04/10:  year.(2,1  ) + year_1.(*)    checkQ(year.'01')   + year_1.(*)
	// 04/11 ~ 05/10:  year.(3,2,1) + year_1.(*)    checkQ(year.'01') + year_1.(*)
	// 05/11 ~ 05/15:  year.(4...1) + year_1.(*)    checkQ(year.'01') + year_1.(*)
	// 05/16 ~ 06/10:  year.(4...1) + year_1.(*)                      + year.(Q1) + year_1.(*)
	// 06/11 ~ 06/30:  year.(5...1) + year_1.(*)                      + year.(Q1) + year_1.(*)
	// 07/01 ~ 07/10:  year.(5...1) + year_1.(*)    checkQ(year.'02') + year.(Q1) + year_1.(*)
	// 07/11 ~ 08/10:  year.(6...1) + year_1.(*)    checkQ(year.'02') + year.(Q1) + year_1.(*)
	// 08/11 ~ 08/14:  year.(7...1) + year_1.(*)    checkQ(year.'02') + year.(Q1) + year_1.(*)
	// 08/15 ~ 09/10:  year.(7...1) + year_1.(*)                      + year.(Q2,Q1) + year_1.(*)
	// 09/11 ~ 09/30:  year.(8...1) + year_1.(*)                      + year.(Q2,Q1) + year_1.(*)
	// 10/01 ~ 10/10:  year.(8...1) + year_1.(*)    checkQ(year.'03') + year.(Q2,Q1) + year_1.(*)
	// 10/11 ~ 11/10:  year.(9...1) + year_1.(*)    checkQ(year.'03') + year.(Q2,Q1) + year_1.(*)
	// 11/11 ~ 11/14:  year.(10..1) + year_1.(*)    checkQ(year.'03') + year.(Q2,Q1) + year_1.(*)
	// 11/15 ~ 12/10:  year.(10..1) + year_1.(*)                      + year.(Q3,Q2,Q1) + year_1.(*)
	// 12/11 ~ 12/31:  year.(11..1) + year_1.(*)                      + year.(Q3,Q2,Q1) + year_1.(*)
	// **********************************************************************************************
	// Best Effort
	//
	// 01/01 ~ 01/10 checkIfQ4exist(year_1)
	//    (0 ~ 9)    False -> return (M11+M10+Q3+Q2+Q1+(M12)) * yoy           // case 1
	//               True  -> return (Q4+Q3+Q2+Q1) * yoy                      // case 2, P(Q4)~0%
	// 01/11 ~ 02/10 checkIfQ4exist(year_1)
	//   (10 ~ 40)   False -> return (M12+M11+M10+Q3+Q2+Q1) * yoy             // case 3
	//               True  -> return (Q4+Q3+Q2+Q1) * yoy                      // case 2, P(Q4)~0%
	// 02/11 ~ 03/10 checkIfQ4exist(year_1) // *check leap year from now on*
	//   (41 ~ 68*)  False -> return M01 + (M12+M11+M10+Q3+Q2+M03+M02) * yoy  // case 4
	//               True  -> return M01 + (Q4+Q3+Q2+M03+M02) * yoy           // case 5, P(Q4)~10%
	// 03/11 ~ 03/31 checkIfQ4exist(year_1)
	//   (69*~ 89*)  False -> return M01+M02 + (M12+M11+M10+Q3+Q2+M03) * yoy  // case 6
	//               True  -> return M01+M02 + (Q4+Q3+Q2+M03) * yoy           // case 7, P(Q4)~100%
	// 04/01 ~ 04/10 checkIfQ1exist(year)
	//   (90*~ 99*)  False -> return M01+M02 + (Q4+Q3+Q2+M03) * yoy           // case 7
	//               True  -> return Q1 + (Q4-Q1) * yoy                       // case 8, P(Q1)~0%
	// 04/11 ~ 05/10 checkIfQ1exist(year)
	//  (100*~ 129*) False -> return M01+M02+M03 + (Q4+Q3+Q2) * yoy           // case 9
	//               True  -> return Q1 + (Q4+Q3+Q2) * yoy                    // case 8, P(Q1)~10%
	// 05/11 ~ 05/15 checkIfQ1exist(year)
	//  (130*~ 134*) False -> return M01+M02+M03+M04 + (Q4+Q3+M06+M05) * yoy  // case 10
	//               True  -> return M04+Q1 + (Q4+Q3+M06+M05) * yoy           // case 11, P(Q1)~100%
	// 05/16 ~ 06/10
	//  (135*~ 160*)       -> return M04+Q1 + (Q4+Q3+M06+M05) * yoy           // case 11
	// 06/11 ~ 06/30
	//  (161*~ 180*)       -> return M05+M04+Q1 + (Q4+Q3+M06) * yoy           // case 12
	// 07/01 ~ 07/10 checkIfQ2exist(year)
	//  (181*~ 190*) False -> return M05+M04+Q1 + (Q4+Q3+M06) * yoy           // case 12
	//               True  -> return Q2+Q1 + (Q4+Q3) * yoy                    // case 13, P(Q2)~0%
	// 07/11 ~ 08/10 checkIfQ2exist(year)
	//  (191*~ 221*) False -> return M06+M05+M04+Q1 + (Q4+Q3) * yoy           // case 14
	//               True  -> return Q2+Q1 + (Q4+Q3) * yoy                    // case 13, P(Q2)~10%
	// 08/11 ~ 08/14 checkIfQ2exist(year)
	//  (222*~ 225*) False -> return M07+M06+M05+M04+Q1 + (Q4+M09+M08) * yoy  // case 15
	//               True  -> return M07+Q2+Q1 + (Q4+M09+M08) * yoy           // case 16, P(Q2)~100%
	// 08/15 ~ 09/10
	//  (226*~ 252*)       -> return M07+Q2+Q1 + (Q4+M09+M08) * yoy           // case 16
	// 09/11 ~ 09/30
	//  (253*~ 272*)       -> return M08+M07+Q2+Q1 + (Q4+M09) * yoy           // case 17
	// 10/01 ~ 10/10 checkIfQ3exist(year)
	//  (273*~ 282*) False -> return M08+M07+Q2+Q1 + (Q4+M09) * yoy           // case 17
	//               True  -> return Q3+Q2+Q1 + (Q4) * yoy                    // case 18, P(Q3)~0%
	// 10/11 ~ 11/10 checkIfQ3exist(year)
	//  (283*~ 313*) False -> return M09+M08+M07+Q2+Q1 + (Q4) * yoy           // case 19
	//               True  -> return Q3+Q2+Q1 + (Q4) * yoy                    // case 18, P(Q3)~10%
	// 11/11 ~ 11/14 checkIfQ3exist(year)
	//  (314*~ 317*) False -> return M10+M09+M08+M07+Q2+Q1 + (M12+M11) * yoy  // case 20
	//               True  -> return M10+Q3+Q2+Q1 + (M12+M11) * yoy           // case 21, P(Q3)~100%
	// 11/15 ~ 12/10
	//  (318*~ 343*)       -> return M10+Q3+Q2+Q1 + (M12+M11) * yoy           // case 21
	// 12/11 ~ 12/31
	//  (344*~ 364*)       -> return M11+M10+Q3+Q2+Q1 + (M12) * yoy           // case 22
	// **********************************************************************************************

	$revenue_restimated = 0;
	$year = substr($date, 0, 4);
	$year_1 = (string)((int)$year - 1);
	$year_2 = (string)((int)$year - 2);

	//$dayOfYear1 = array(0, 10, 41, 69, 90, 100, 130, 135, 161, 181, 191, 222, 226, 253, 273, 283, 314, 318, 344, 365);
	//$dayOfYear2 = array(0, 10, 41, 70, 91, 101, 131, 136, 162, 182, 192, 223, 227, 254, 274, 284, 315, 319, 345, 366);
	//$dayOfYearList = (date('L', strtotime($date))?$dayOfYear2:$dayOfYear1);

	$dayOfYear = date( 'z', strtotime($date));
	// treating 60th day in a leap year (3/1) the same as 59th day in a non-leap year (3/1)
	// this implies treating 59th day in a leap year (2/29) also the same as 59th day in a non-leap year (3/1)
	if (1==date('L', strtotime($date)) and $dayOfYear > 59)
		$dayOfYear--;

	switch ($dayOfYear)
	{
		case 0: case 2: case 3: case 4: case 5: case 6: case 7: case 8: case 9:
			if ((int)$year_2 >= 2010)
			{
				if (defined("USE_MONTHDATA_TO_APPROXIMATE"))
				{
					if (false == query_xbrl_on_date($pepo->id, $year_1, '04', $date)) // case 1
					{
						$revenue_restimated+=query_monthly_revenue($pepo->id, $year_1 . "11");
						$revenue_restimated+=query_monthly_revenue($pepo->id, $year_1 . "10");
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year_1 . "03");
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year_1 . "02");
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year_1 . "01");
						$revenue_restimated+=query_monthly_revenue($pepo->id, $year_2 . "12");
						$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
					}
					else // case 2, P(Q4)~0
					{
						$revenue_restimated+=query_yearly_revenue($pepo->id, $year_1);
						$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
					}
				}
				else
				{
					if (false == query_xbrl_on_date($pepo->id, $year_1, '04', $date)) // case 1'
					{
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year_2 . "04");
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_2 . "03");
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_2 . "02");
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_2 . "01");
						$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year_1 . "03");
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year_1 . "02");
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year_1 . "01");
						$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
					}
					else // case 2, P(Q4)~0
					{
						$revenue_restimated+=query_yearly_revenue($pepo->id, $year_1);
						$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
					}
				}
			}
			break;
		case 10: case 11: case 12: case 13: case 14: case 15: case 16: case 17: case 18: case 19:
		case 20: case 21: case 22: case 23: case 24: case 25: case 26: case 27: case 28: case 29:
		case 30: case 31: case 32: case 33: case 34: case 35: case 36: case 37: case 38: case 39:
		case 40:
			if ((int)$year_2 >= 2010)
			{
				if (defined("USE_MONTHDATA_TO_APPROXIMATE"))
				{
					if (false == query_xbrl_on_date($pepo->id, $year_1, '04', $date)) // case 3
					{
						$revenue_restimated+=query_monthly_revenue($pepo->id, $year_1 . "12");
						$revenue_restimated+=query_monthly_revenue($pepo->id, $year_1 . "11");
						$revenue_restimated+=query_monthly_revenue($pepo->id, $year_1 . "10");
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year_1 . "03");
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year_1 . "02");
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year_1 . "01");
						$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
					}
					else // case 2, P(Q4)~0
					{
						$revenue_restimated+=query_yearly_revenue($pepo->id, $year_1);
						$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
					}
				}
				else
				{
					if (false == query_xbrl_on_date($pepo->id, $year_1, '04', $date)) // case 3'
					{
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year_2 . "04");
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_2 . "03");
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_2 . "02");
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_2 . "01");
						$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year_1 . "03");
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year_1 . "02");
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year_1 . "01");
						$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
					}
					else // case 2, P(Q4)~0
					{
						$revenue_restimated+=query_yearly_revenue($pepo->id, $year_1);
						$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
					}
				}
			}
			break;
		case 41: case 42: case 43: case 44: case 45: case 46: case 47: case 48: case 49:
		case 50: case 51: case 52: case 53: case 54: case 55: case 56: case 57: case 58: case 59:
		case 60: case 61: case 62: case 63: case 64: case 65: case 66: case 67: case 68:
			if ((int)$year_2 >= 2010)
			{
				if (defined("USE_MONTHDATA_TO_APPROXIMATE"))
				{
					if (false == query_xbrl_on_date($pepo->id, $year_1, '04', $date)) // case 4
					{
						$revenue_restimated+=query_monthly_revenue($pepo->id, $year_1 . "12");
						$revenue_restimated+=query_monthly_revenue($pepo->id, $year_1 . "11");
						$revenue_restimated+=query_monthly_revenue($pepo->id, $year_1 . "10");
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year_1 . "03");
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year_1 . "02");
						$revenue_restimated+=query_monthly_revenue($pepo->id, $year_1 . "03");
						$revenue_restimated+=query_monthly_revenue($pepo->id, $year_1 . "02");
						$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
						$revenue_restimated+=query_monthly_revenue($pepo->id, $year . "01");
					}
					else // case 5, P(Q4)~10
					{
						$revenue_restimated+=query_yearly_revenue($pepo->id, $year_1);
						$revenue_restimated-=query_monthly_revenue($pepo->id, $year_1 . "01");
						$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
						$revenue_restimated+=query_monthly_revenue($pepo->id, $year . "01");
					}
				}
				else
				{
					if (false == query_xbrl_on_date($pepo->id, $year_1, '04', $date)) // case 4'
					{
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year_2 . "04");
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_2 . "03");
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_2 . "02");
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_2 . "01");
						$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year_1 . "03");
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year_1 . "02");
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year_1 . "01");
						$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
					}
					else // case 5', P(Q4)~10
					{
						$revenue_restimated+=query_yearly_revenue($pepo->id, $year_1);
						$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
					}
				}
			}
			break;
		case 69:
		case 70: case 71: case 72: case 73: case 74: case 75: case 76: case 77: case 78: case 79:
		case 80: case 81: case 82: case 83: case 84: case 85: case 86: case 87: case 88: case 89:
			if ((int)$year_2 >= 2010)
			{
				if (defined("USE_MONTHDATA_TO_APPROXIMATE"))
				{
					if (false == query_xbrl_on_date($pepo->id, $year_1, '04', $date)) // case 6
					{
						$revenue_restimated+=query_monthly_revenue($pepo->id, $year_1 . "12");
						$revenue_restimated+=query_monthly_revenue($pepo->id, $year_1 . "11");
						$revenue_restimated+=query_monthly_revenue($pepo->id, $year_1 . "10");
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year_1 . "03");
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year_1 . "02");
						$revenue_restimated+=query_monthly_revenue($pepo->id, $year_1 . "03");
						$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
						$revenue_restimated+=query_monthly_revenue($pepo->id, $year . "01");
						$revenue_restimated+=query_monthly_revenue($pepo->id, $year . "02");
					}
					else // case 7, P(Q4)~100
					{
						$revenue_restimated+=query_yearly_revenue($pepo->id, $year_1);
						$revenue_restimated-=query_monthly_revenue($pepo->id, $year_1 . "02");
						$revenue_restimated-=query_monthly_revenue($pepo->id, $year_1 . "01");
						$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
						$revenue_restimated+=query_monthly_revenue($pepo->id, $year . "01");
						$revenue_restimated+=query_monthly_revenue($pepo->id, $year . "02");
					}
				}
				else
				{
					if (false == query_xbrl_on_date($pepo->id, $year_1, '04', $date)) // case 6
					{
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year_2 . "04");
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_2 . "03");
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_2 . "02");
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_2 . "01");
						$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year_1 . "03");
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year_1 . "02");
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year_1 . "01");
						$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
					}
					else // case 7, P(Q4)~100
					{
						$revenue_restimated+=query_yearly_revenue($pepo->id, $year_1);
						$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
					}
				}
			}
			break;
		case 90: case 91: case 92: case 93: case 94: case 95: case 96: case 97: case 98: case 99:
			if ((int)$year_2 >= 2010)
			{
				if (defined("USE_MONTHDATA_TO_APPROXIMATE"))
				{
					if (false == query_xbrl_on_date($pepo->id, $year, '01', $date)) // case 7
					{
						$revenue_restimated+=query_yearly_revenue($pepo->id, $year_1);
						$revenue_restimated-=query_monthly_revenue($pepo->id, $year_1 . "02");
						$revenue_restimated-=query_monthly_revenue($pepo->id, $year_1 . "01");
						$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
						$revenue_restimated+=query_monthly_revenue($pepo->id, $year . "01");
						$revenue_restimated+=query_monthly_revenue($pepo->id, $year . "02");
					}
					else // case 8, P(Q1)~0
					{
						$revenue_restimated+=query_yearly_revenue($pepo->id, $year_1);
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "01");
						$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "01");
					}
				}
				else
				{
					if (false == query_xbrl_on_date($pepo->id, $year, '01', $date)) // case 7
					{
						$revenue_restimated+=query_yearly_revenue($pepo->id, $year_1);
						$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
					}
					else // case 8, P(Q1)~0
					{
						$revenue_restimated+=query_yearly_revenue($pepo->id, $year_1);
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "01");
						$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "01");
					}
				}
			}
			break;
		case 100: case 101: case 102: case 103: case 104: case 105: case 106: case 107: case 108: case 109:
		case 110: case 111: case 112: case 113: case 114: case 115: case 116: case 117: case 118: case 119:
		case 120: case 121: case 122: case 123: case 124: case 125: case 126: case 127: case 128: case 129:
			if ((int)$year_2 >= 2010)
			{
				if (defined("USE_MONTHDATA_TO_APPROXIMATE"))
				{
					if (false == query_xbrl_on_date($pepo->id, $year, '01', $date)) // case 9
					{
						$revenue_restimated+=query_yearly_revenue($pepo->id, $year_1);
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "01");
						$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
						$revenue_restimated+=query_monthly_revenue($pepo->id, $year . "01");
						$revenue_restimated+=query_monthly_revenue($pepo->id, $year . "02");
						$revenue_restimated+=query_monthly_revenue($pepo->id, $year . "03");
					}
					else // case 8, P(Q1)~10
					{
						$revenue_restimated+=query_yearly_revenue($pepo->id, $year_1);
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "01");
						$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "01");
					}
				}
				else
				{
					if (false == query_xbrl_on_date($pepo->id, $year, '01', $date)) // case 9'
					{
						$revenue_restimated+=query_yearly_revenue($pepo->id, $year_1);
						$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);

					}
					else // case 8, P(Q1)~10
					{
						$revenue_restimated+=query_yearly_revenue($pepo->id, $year_1);
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "01");
						$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "01");
					}
				}
			}
			break;
		case 130: case 131: case 132: case 133: case 134:
			if ((int)$year_2 >= 2010)
			{
				if (defined("USE_MONTHDATA_TO_APPROXIMATE"))
				{
					if (false == query_xbrl_on_date($pepo->id, $year, '01', $date)) // case 10
					{
						$revenue_restimated+=query_yearly_revenue($pepo->id, $year_1);
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "01");
						$revenue_restimated-=query_monthly_revenue($pepo->id, $year_1 . "04");
						$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
						$revenue_restimated+=query_monthly_revenue($pepo->id, $year . "01");
						$revenue_restimated+=query_monthly_revenue($pepo->id, $year . "02");
						$revenue_restimated+=query_monthly_revenue($pepo->id, $year . "03");
						$revenue_restimated+=query_monthly_revenue($pepo->id, $year . "04");
					}
					else // case 11, P(Q1)~100
					{
						$revenue_restimated+=query_yearly_revenue($pepo->id, $year_1);
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "01");
						$revenue_restimated-=query_monthly_revenue($pepo->id, $year_1 . "04");
						$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "01");
						$revenue_restimated+=query_monthly_revenue($pepo->id, $year . "04");
					}
				}
				else
				{
					if (false == query_xbrl_on_date($pepo->id, $year, '01', $date)) // case 10
					{
						$revenue_restimated+=query_yearly_revenue($pepo->id, $year_1);
						$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
					}
					else // case 11, P(Q1)~100
					{
						$revenue_restimated+=query_yearly_revenue($pepo->id, $year_1);
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "01");
						$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "01");
					}
				}
			}
			break;
		case 135: case 135: case 136: case 137: case 138: case 139: case 140:
		case 141: case 142: case 143: case 144: case 145: case 146: case 147: case 148: case 149: case 150:
		case 151: case 152: case 153: case 154: case 155: case 156: case 157: case 158: case 159: case 160:
			if ((int)$year_2 >= 2010)
			{
				if (defined("USE_MONTHDATA_TO_APPROXIMATE")) // case 11
				{
					$revenue_restimated+=query_yearly_revenue($pepo->id, $year_1);
					$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "01");
					$revenue_restimated-=query_monthly_revenue($pepo->id, $year_1 . "04");
					$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
					$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "01");
					$revenue_restimated+=query_monthly_revenue($pepo->id, $year . "04");
				}
				else // case 11'
				{
					$revenue_restimated+=query_yearly_revenue($pepo->id, $year_1);
					$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "01");
					$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
					$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "01");
				}
			}
			break;
		case 161: case 162: case 163: case 164: case 165: case 166: case 167: case 168: case 169: case 170:
		case 171: case 172: case 173: case 174: case 175: case 176: case 177: case 178: case 179: case 180:
			if ((int)$year_2 >= 2010)
			{
				if (defined("USE_MONTHDATA_TO_APPROXIMATE")) // case 12
				{
					$revenue_restimated+=query_yearly_revenue($pepo->id, $year_1);
					$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "01");
					$revenue_restimated-=query_monthly_revenue($pepo->id, $year_1 . "04");
					$revenue_restimated-=query_monthly_revenue($pepo->id, $year_1 . "05");
					$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
					$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "01");
					$revenue_restimated+=query_monthly_revenue($pepo->id, $year . "04");
					$revenue_restimated+=query_monthly_revenue($pepo->id, $year . "05");
				}
				else // case 12'
				{
					$revenue_restimated+=query_yearly_revenue($pepo->id, $year_1);
					$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "01");
					$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
					$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "01");
				}
			}
			break;
		case 181: case 182: case 183: case 184: case 185: case 186: case 187: case 188: case 189: case 190:
			if ((int)$year_2 >= 2010)
			{
				if (defined("USE_MONTHDATA_TO_APPROXIMATE")) // case 12
				{
					if (false == query_xbrl_on_date($pepo->id, $year, '02', $date)) // case 12
					{
						$revenue_restimated+=query_yearly_revenue($pepo->id, $year_1);
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "01");
						$revenue_restimated-=query_monthly_revenue($pepo->id, $year_1 . "04");
						$revenue_restimated-=query_monthly_revenue($pepo->id, $year_1 . "05");
						$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "01");
						$revenue_restimated+=query_monthly_revenue($pepo->id, $year . "04");
						$revenue_restimated+=query_monthly_revenue($pepo->id, $year . "05");
					}
					else // case 13, P(Q2)~0
					{
						$revenue_restimated+=query_yearly_revenue($pepo->id, $year_1);
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "01");
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "02");
						$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "01");
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "02");
					}
				}
				else
				{
					if (false == query_xbrl_on_date($pepo->id, $year, '02', $date)) // case 12'
					{
						$revenue_restimated+=query_yearly_revenue($pepo->id, $year_1);
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "01");
						$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "01");
					}
					else // case 13, P(Q2)~0
					{
						$revenue_restimated+=query_yearly_revenue($pepo->id, $year_1);
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "01");
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "02");
						$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "01");
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "02");
					}
				}
			}
			break;
		case 191: case 192: case 193: case 194: case 195: case 196: case 197: case 198: case 199: case 200:
		case 201: case 202: case 203: case 204: case 205: case 206: case 207: case 208: case 209: case 210:
		case 211: case 212: case 213: case 214: case 215: case 216: case 217: case 218: case 219: case 220:
		case 221:
			if ((int)$year_2 >= 2010)
			{
				if (defined("USE_MONTHDATA_TO_APPROXIMATE")) // case 12
				{
					if (false == query_xbrl_on_date($pepo->id, $year, '02', $date)) // case 14
					{
						$revenue_restimated+=query_yearly_revenue($pepo->id, $year_1);
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "01");
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "02");
						$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "01");
						$revenue_restimated+=query_monthly_revenue($pepo->id, $year . "04");
						$revenue_restimated+=query_monthly_revenue($pepo->id, $year . "05");
						$revenue_restimated+=query_monthly_revenue($pepo->id, $year . "06");
					}
					else // case 13, P(Q2)~10
					{
						$revenue_restimated+=query_yearly_revenue($pepo->id, $year_1);
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "01");
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "02");
						$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "01");
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "02");
					}
				}
				else
				{
					if (false == query_xbrl_on_date($pepo->id, $year, '02', $date)) // case 14'
					{
						$revenue_restimated+=query_yearly_revenue($pepo->id, $year_1);
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "01");
						$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "01");
					}
					else // case 13, P(Q2)~10
					{
						$revenue_restimated+=query_yearly_revenue($pepo->id, $year_1);
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "01");
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "02");
						$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "01");
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "02");
					}
				}
			}
			break;
		case 222: case 223: case 224: case 225:
			if ((int)$year_2 >= 2010)
			{
				if (defined("USE_MONTHDATA_TO_APPROXIMATE")) // case 12
				{
					if (false == query_xbrl_on_date($pepo->id, $year, '02', $date)) // case 15
					{
						$revenue_restimated+=query_yearly_revenue($pepo->id, $year_1);
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "01");
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "02");
						$revenue_restimated-=query_monthly_revenue($pepo->id, $year_1 . "07");
						$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "01");
						$revenue_restimated+=query_monthly_revenue($pepo->id, $year . "04");
						$revenue_restimated+=query_monthly_revenue($pepo->id, $year . "05");
						$revenue_restimated+=query_monthly_revenue($pepo->id, $year . "06");
						$revenue_restimated+=query_monthly_revenue($pepo->id, $year . "07");
					}
					else // case 16, P(Q2)~100
					{
						$revenue_restimated+=query_yearly_revenue($pepo->id, $year_1);
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "01");
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "02");
						$revenue_restimated-=query_monthly_revenue($pepo->id, $year_1 . "07");
						$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "01");
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "02");
						$revenue_restimated+=query_monthly_revenue($pepo->id, $year . "07");
					}
				}
				else
				{
					if (false == query_xbrl_on_date($pepo->id, $year, '02', $date)) // case 15
					{
						$revenue_restimated+=query_yearly_revenue($pepo->id, $year_1);
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "01");
						$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "01");
					}
					else // case 16, P(Q2)~100
					{
						$revenue_restimated+=query_yearly_revenue($pepo->id, $year_1);
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "01");
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "02");
						$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "01");
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "02");
					}
				}
			}
			break;
		case 226: case 227: case 228: case 229: case 230:
		case 231: case 232: case 233: case 234: case 235: case 236: case 237: case 238: case 239: case 240:
		case 241: case 242: case 243: case 244: case 245: case 246: case 247: case 248: case 249: case 250:
		case 251: case 252: case 253:
			if ((int)$year_2 >= 2010)
			{
				if (defined("USE_MONTHDATA_TO_APPROXIMATE")) // case 16
				{
					$revenue_restimated+=query_yearly_revenue($pepo->id, $year_1);
					$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "01");
					$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "02");
					$revenue_restimated-=query_monthly_revenue($pepo->id, $year_1 . "07");
					$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
					$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "01");
					$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "02");
					$revenue_restimated+=query_monthly_revenue($pepo->id, $year . "07");
				}
				else // case 16
				{
					$revenue_restimated+=query_yearly_revenue($pepo->id, $year_1);
					$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "01");
					$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "02");
					$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
					$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "01");
					$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "02");
				}
			}
			break;
		case 253: case 254: case 255: case 256: case 257: case 258: case 259: case 260:
		case 261: case 262: case 263: case 264: case 265: case 266: case 267: case 268: case 269: case 270:
		case 271: case 272:
			if ((int)$year_2 >= 2010)
			{
				if (defined("USE_MONTHDATA_TO_APPROXIMATE")) // case 17
				{
					$revenue_restimated+=query_yearly_revenue($pepo->id, $year_1);
					$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "01");
					$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "02");
					$revenue_restimated-=query_monthly_revenue($pepo->id, $year_1 . "07");
					$revenue_restimated-=query_monthly_revenue($pepo->id, $year_1 . "08");
					$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
					$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "01");
					$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "02");
					$revenue_restimated+=query_monthly_revenue($pepo->id, $year . "07");
					$revenue_restimated+=query_monthly_revenue($pepo->id, $year . "08");
				}
				else // case 17'
				{
					$revenue_restimated+=query_yearly_revenue($pepo->id, $year_1);
					$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "01");
					$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "02");
					$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
					$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "01");
					$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "02");
				}
			}
			break;
		case 273: case 274: case 275: case 276: case 277: case 278: case 279: case 280: case 281: case 282:
			if ((int)$year_2 >= 2010)
			{
				if (defined("USE_MONTHDATA_TO_APPROXIMATE"))
				{
					if (false == query_xbrl_on_date($pepo->id, $year, '03', $date)) // case 17
					{
						$revenue_restimated+=query_yearly_revenue($pepo->id, $year_1);
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "01");
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "02");
						$revenue_restimated-=query_monthly_revenue($pepo->id, $year_1 . "07");
						$revenue_restimated-=query_monthly_revenue($pepo->id, $year_1 . "08");
						$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "01");
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "02");
						$revenue_restimated+=query_monthly_revenue($pepo->id, $year . "07");
						$revenue_restimated+=query_monthly_revenue($pepo->id, $year . "08");
					}
					else // case 18, P(Q3)~0
					{
						$revenue_restimated+=query_yearly_revenue($pepo->id, $year_1);
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "01");
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "02");
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "03");
						$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "01");
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "02");
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "03");
					}
				}
				else
				{
					if (false == query_xbrl_on_date($pepo->id, $year, '03', $date)) // case 17'
					{
						$revenue_restimated+=query_yearly_revenue($pepo->id, $year_1);
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "01");
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "02");
						$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "01");
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "02");
					}
					else // case 18, P(Q3)~0
					{
						$revenue_restimated+=query_yearly_revenue($pepo->id, $year_1);
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "01");
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "02");
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "03");
						$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "01");
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "02");
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "03");
					}
				}
			}
			break;
		case 283: case 284: case 285: case 286: case 287: case 288: case 289: case 290:
		case 291: case 292: case 293: case 294: case 295: case 296: case 297: case 298: case 299: case 300:
		case 301: case 302: case 303: case 304: case 305: case 306: case 307: case 308: case 309: case 310:
		case 311: case 312: case 313:
			if ((int)$year_2 >= 2010)
			{
				if (defined("USE_MONTHDATA_TO_APPROXIMATE"))
				{
					if (false == query_xbrl_on_date($pepo->id, $year, '03', $date)) // case 19
					{
						$revenue_restimated+=query_yearly_revenue($pepo->id, $year_1);
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "01");
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "02");
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "03");
						$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "01");
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "02");
						$revenue_restimated+=query_monthly_revenue($pepo->id, $year . "07");
						$revenue_restimated+=query_monthly_revenue($pepo->id, $year . "08");
						$revenue_restimated+=query_monthly_revenue($pepo->id, $year . "09");
					}
					else // case 18, P(Q3)~10
					{
						$revenue_restimated+=query_yearly_revenue($pepo->id, $year_1);
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "01");
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "02");
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "03");
						$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "01");
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "02");
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "03");
					}
				}
				else
				{
					if (false == query_xbrl_on_date($pepo->id, $year, '03', $date)) // case 19'
					{
						$revenue_restimated+=query_yearly_revenue($pepo->id, $year_1);
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "01");
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "02");
						$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "01");
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "02");
					}
					else // case 18, P(Q3)~10
					{
						$revenue_restimated+=query_yearly_revenue($pepo->id, $year_1);
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "01");
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "02");
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "03");
						$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "01");
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "02");
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "03");
					}
				}
			}
			break;
		case 314: case 315: case 316: case 317:
			if ((int)$year_2 >= 2010)
			{
				if (defined("USE_MONTHDATA_TO_APPROXIMATE"))
				{
					if (false == query_xbrl_on_date($pepo->id, $year, '03', $date)) // case 20
					{
						$revenue_restimated+=query_monthly_revenue($pepo->id, $year_1 . "12");
						$revenue_restimated+=query_monthly_revenue($pepo->id, $year_1 . "11");
						$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "01");
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "02");
						$revenue_restimated+=query_monthly_revenue($pepo->id, $year . "07");
						$revenue_restimated+=query_monthly_revenue($pepo->id, $year . "08");
						$revenue_restimated+=query_monthly_revenue($pepo->id, $year . "09");
						$revenue_restimated+=query_monthly_revenue($pepo->id, $year . "10");
					}
					else // case 21, P(Q3)~100
					{
						$revenue_restimated+=query_monthly_revenue($pepo->id, $year_1 . "12");
						$revenue_restimated+=query_monthly_revenue($pepo->id, $year_1 . "11");
						$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "01");
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "02");
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "03");
						$revenue_restimated+=query_monthly_revenue($pepo->id, $year . "10");
					}
				}
				else
				{
					if (false == query_xbrl_on_date($pepo->id, $year, '03', $date)) // case 20'
					{
						$revenue_restimated+=query_yearly_revenue($pepo->id, $year_1);
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "01");
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "02");
						$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "01");
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "02");
					}
					else // case 21', P(Q3)~100
					{
						$revenue_restimated+=query_yearly_revenue($pepo->id, $year_1);
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "01");
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "02");
						$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "03");
						$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "01");
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "02");
						$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "03");
					}
				}
			}
			break;
		case 318: case 319:
		case 320: case 321: case 322: case 323: case 324: case 325: case 326: case 327: case 328: case 329:
		case 330: case 331: case 332: case 333: case 334: case 335: case 336: case 337: case 338: case 339:
		case 340: case 341: case 342: case 343:
			if ((int)$year_2 >= 2010)
			{
				if (defined("USE_MONTHDATA_TO_APPROXIMATE")) // case 21
				{
					$revenue_restimated+=query_monthly_revenue($pepo->id, $year_1 . "12");
					$revenue_restimated+=query_monthly_revenue($pepo->id, $year_1 . "11");
					$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
					$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "01");
					$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "02");
					$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "03");
					$revenue_restimated+=query_monthly_revenue($pepo->id, $year . "10");
				}
				else // case 21'
				{
					$revenue_restimated+=query_yearly_revenue($pepo->id, $year_1);
					$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "01");
					$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "02");
					$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "03");
					$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
					$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "01");
					$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "02");
					$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "03");
				}
			}
			break;
		case 344: case 345: case 346: case 347: case 348: case 349:
		case 350: case 351: case 352: case 353: case 354: case 355: case 356: case 357: case 358: case 359:
		case 360: case 361: case 362: case 363: case 364:
			if ((int)$year_2 >= 2010)
			{
				if (defined("USE_MONTHDATA_TO_APPROXIMATE")) // case 22
				{
					$revenue_restimated+=query_monthly_revenue($pepo->id, $year_1 . "12");
					$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
					$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "01");
					$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "02");
					$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "03");
					$revenue_restimated+=query_monthly_revenue($pepo->id, $year . "10");
					$revenue_restimated+=query_monthly_revenue($pepo->id, $year . "11");
				}
				else // case 22'
				{
					$revenue_restimated+=query_yearly_revenue($pepo->id, $year_1);
					$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "01");
					$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "02");
					$revenue_restimated-=query_seasonly_revenue($pepo->id, $year_1 . "03");
					$revenue_restimated*=(1+$pepo->revenue_yoy_estimated);
					$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "01");
					$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "02");
					$revenue_restimated+=query_seasonly_revenue($pepo->id, $year . "03");
				}
			}
			break;
		default:
			echo_v (ERROR_VERBOSE, "invalid day of year!!");
			break;
	}
	return $revenue_restimated;
}

// this is a special case, not able to deal with common case
function initialize_stock_data($stock, $date)
{
	echo_v(DEBUG_VERBOSE, "**** initialize_stock_data ************************************************");

	$pepo = new pepoData();
	$pepo->id = $stock->id;
	$pepo->onyyyy = $stock->onyyyy;

	$year = substr($date, 0, 4);
	$month = substr($date, 5, 2);
	$day = substr($date, 8, 2);

	$diff = (int)$year - (int)$pepo->onyyyy;
	if ($diff < 2)
	{
		echo_v (ERROR_VERBOSE, "[initialize_stock_data] It's not on my radar");
		return null;
	}

	$year_1 = (string)((int)$year - 1);

	// This is a aggregation of database access plus utility invokation to
	// offer insight of one single question: At the time 2015/4/10, is 2330
	// worthy of buying? The answer to the question is broken into steps.
	//
	// A. Get stock revenue at year 2014
	$pepo->revenue_lastyear = query_yearly_revenue($pepo->id, $year_1);
	echo_v(DEBUG_VERBOSE, "[evaluate_stock] A: id " . $pepo->id . " revenue (" . $year_1 . ") = " . $pepo->revenue_lastyear);

	// B. Estimate revenue yoy (year 2015 over year 2014)
	$pepo->revenue_yoy_estimated = query_est_monthly_revenue_yoy($pepo, $year, $month, $day);
	echo_v(DEBUG_VERBOSE, "[evaluate_stock] B: id " . $pepo->id .
		" estimated revenue yoy (" . $year . "/" . $year_1 . ") = " . percent($pepo->revenue_yoy_estimated));

	// C. Estimate revenue 2015 by A * (1 + B)
	//$pepo->revenue_estimated = $pepo->revenue_lastyear * (1 + $pepo->revenue_yoy_estimated);
	//echo_v(LOG_VERBOSE, "[evaluate_stock] C: id " . $pepo->id . " estimated revenue (" . $year . ") = " . $pepo->revenue_estimated);
	$pepo->revenue_estimated = query_revenue_estimated($pepo, $date);
	echo_v(DEBUG_VERBOSE, "[evaluate_stock] C: id " . $pepo->id . " estimated revenue (" . $year . ") = " . $pepo->revenue_estimated);
	
	return $pepo;
}

function update_stock_data_nopat_over_revenue_diff2($pepo, $date, $xdr_kline)
{
	echo_v(DEBUG_VERBOSE, "**** update_stock_data_nopat_over_revenue_diff2 ************************************************");

	$year = substr($date, 0, 4);
	$year_1 = (string)((int)$year - 1);

	// H. Get stock prices High and Low at year 2014-2011, including excluded dividends and excluded rights
	$pepo->xdr[$year_1] = query_xdr_highlow_by_year($year_1, $xdr_kline);
	echo_v(DEBUG_VERBOSE, "[evaluate_stock] H: id " . $pepo->id . " idr high/low(year) = " .
		$pepo->xdr[$year_1]->high . "/" . $pepo->xdr[$year_1]->low . "(" . $year_1 . ")");

	// I. Get EPS  and EPS2 at year 2014-2011
	$pepo->eps[$year_1] = query_year_eps($pepo->id, $year_1);
	echo_v(DEBUG_VERBOSE, "[evaluate_stock] I: id " . $pepo->id . " eps(year) = " .
		$pepo->eps[$year_1] . "(" . $year_1 . ")");

	$pepo->eps2[$year_1] = query_year_eps2($pepo->id, $year_1);
	echo_v(DEBUG_VERBOSE, "[evaluate_stock] I: id " . $pepo->id . " eps2(year) = " .
		$pepo->eps2[$year_1] . "(" . $year_1 . ")");

	// J. Compute 2014-2011 PER (P/E Ratio) High and Low by H / I

	$pepo->per[$year_1] = new perData();
	$pepo->per[$year_1]->high = $pepo->xdr[$year_1]->high / $pepo->eps[$year_1];
	$pepo->per[$year_1]->low = $pepo->xdr[$year_1]->low / $pepo->eps[$year_1];

	echo_v(DEBUG_VERBOSE, "[evaluate_stock] J: id " . $pepo->id . " per high/low(year) = " .
	decimal2($pepo->per[$year_1]->high) .	"/" . decimal2($pepo->per[$year_1]->low) . "(" . $year_1 . ")");

	$pepo->per_estimated = new perData();
	$pepo->per_estimated->high = $pepo->per[$year_1]->high;
	$pepo->per_estimated->low = $pepo->per[$year_1]->low;

	echo_v(DEBUG_VERBOSE, "[evaluate_stock] J: id " . $pepo->id . " estimated per high/low(year) = " .
		decimal2($pepo->per_estimated->high) .	"/" . decimal2($pepo->per_estimated->low) . "(" . $year . ")");
}

function update_stock_data_nopat_over_revenue_diff3($pepo, $date, $xdr_kline)
{
	echo_v(DEBUG_VERBOSE, "**** update_stock_data_nopat_over_revenue_diff3 ************************************************");

	$year = substr($date, 0, 4);
	$year_1 = (string)((int)$year - 1);
	$year_2 = (string)((int)$year - 2);

	// H. Get stock prices High and Low at year 2014-2011, including excluded dividends and excluded rights
	$pepo->xdr[$year_1] = query_xdr_highlow_by_year($year_1, $xdr_kline);
	$pepo->xdr[$year_2] = query_xdr_highlow_by_year($year_2, $xdr_kline);
	echo_v(DEBUG_VERBOSE, "[evaluate_stock] H: id " . $pepo->id . " idr high/low(year) = " .
		$pepo->xdr[$year_1]->high . "/" . $pepo->xdr[$year_1]->low . "(" . $year_1 . "), " .
		$pepo->xdr[$year_2]->high . "/" . $pepo->xdr[$year_2]->low . "(" . $year_2 . ")");

	// I. Get EPS at year 2014-2011
	$pepo->eps[$year_1] = query_year_eps($pepo->id, $year_1);
	$pepo->eps[$year_2] = query_year_eps($pepo->id, $year_2);
	echo_v(DEBUG_VERBOSE, "[evaluate_stock] I: id " . $pepo->id . " eps(year) = " .
		$pepo->eps[$year_1] . "(" . $year_1 . "), " .
		$pepo->eps[$year_2] . "(" . $year_2 . ")");

	$pepo->eps2[$year_1] = query_year_eps2($pepo->id, $year_1);
	$pepo->eps2[$year_2] = query_year_eps2($pepo->id, $year_2);
	echo_v(DEBUG_VERBOSE, "[evaluate_stock] I: id " . $pepo->id . " eps2(year) = " .
		$pepo->eps2[$year_1] . "(" . $year_1 . "), " .
		$pepo->eps2[$year_2] . "(" . $year_2 . ")");

	// J. Compute 2014-2011 PER (P/E Ratio) High and Low by H / I

	$pepo->per[$year_1] = new perData();
	$pepo->per[$year_1]->high = $pepo->xdr[$year_1]->high / $pepo->eps[$year_1];
	$pepo->per[$year_1]->low = $pepo->xdr[$year_1]->low / $pepo->eps[$year_1];

	$pepo->per[$year_2] = new perData();
	$pepo->per[$year_2]->high = $pepo->xdr[$year_2]->high / $pepo->eps[$year_2];
	$pepo->per[$year_2]->low = $pepo->xdr[$year_2]->low / $pepo->eps[$year_2];

	echo_v(DEBUG_VERBOSE, "[evaluate_stock] J: id " . $pepo->id . " per high/low(year) = " .
	decimal2($pepo->per[$year_1]->high) .	"/" . decimal2($pepo->per[$year_1]->low) . "(" . $year_1 . "), " .
	decimal2($pepo->per[$year_2]->high) .	"/" . decimal2($pepo->per[$year_2]->low) . "(" . $year_2 . ")");

	$pepo->per_estimated = new perData();
	$pepo->per_estimated->high = ($pepo->per[$year_1]->high+$pepo->per[$year_2]->high)/2;
	$pepo->per_estimated->low = ($pepo->per[$year_1]->low+$pepo->per[$year_2]->low)/2;

	echo_v(DEBUG_VERBOSE, "[evaluate_stock] J: id " . $pepo->id . " estimated per high/low(year) = " .
		decimal2($pepo->per_estimated->high) .	"/" . decimal2($pepo->per_estimated->low) . "(" . $year . ")");
}

function per3_high($pers, $date)
{
	$year = substr($date, 0, 4);
	$year_1 = (string)((int)$year - 1);
	$year_2 = (string)((int)$year - 2);
	$year_3 = (string)((int)$year - 3);

	if ($pers[$year_1]->high >= $pers[$year_2]->high and $pers[$year_2]->high >= $pers[$year_3]->high) // 3 2 1
	{
		if ($pers[$year_1]->high / $pers[$year_2]->high >= 1.2)
		{
			return $pers[$year_1]->high*1.1;
		}
		else
		{
			return $pers[$year_1]->high;
		}
	}
	else if ($pers[$year_1]->high <= $pers[$year_2]->high and $pers[$year_2]->high <= $pers[$year_3]->high) // 1 2 3
	{
		return $pers[$year_1]->high*0.9;
	}
	else // 1 2 1 and 2 1 2
	{
		return ($pers[$year_1]->high+$pers[$year_2]->high+$pers[$year_3]->high)/3;
	}
}

function per3_low($pers, $date)
{
	$year = substr($date, 0, 4);
	$year_1 = (string)((int)$year - 1);
	$year_2 = (string)((int)$year - 2);
	$year_3 = (string)((int)$year - 3);

	if ($pers[$year_1]->low >= $pers[$year_2]->low and $pers[$year_2]->low >= $pers[$year_3]->low) // 3 2 1
	{
		if ($pers[$year_1]->low / $pers[$year_2]->low >= 1.2)
		{
			return $pers[$year_1]->low*1.1;
		}
		else
		{
			return $pers[$year_1]->low;
		}
	}
	else if ($pers[$year_1]->low <= $pers[$year_2]->low and $pers[$year_2]->low <= $pers[$year_3]->low) // 1 2 3
	{
		return $pers[$year_1]->low*0.9;
	}
	else // 1 2 1 and 2 1 2
	{
		return ($pers[$year_1]->low+$pers[$year_2]->low+$pers[$year_3]->low)/3;
	}
}

function update_stock_data_nopat_over_revenue_diff4($pepo, $date, $xdr_kline)
{
	echo_v(DEBUG_VERBOSE, "**** update_stock_data_nopat_over_revenue_diff4 ************************************************");

	$year = substr($date, 0, 4);
	$year_1 = (string)((int)$year - 1);
	$year_2 = (string)((int)$year - 2);
	$year_3 = (string)((int)$year - 3);

	// H. Get stock prices High and Low at year 2014-2011, including excluded dividends and excluded rights
	$pepo->xdr[$year_1] = query_xdr_highlow_by_year($year_1, $xdr_kline);
	$pepo->xdr[$year_2] = query_xdr_highlow_by_year($year_2, $xdr_kline);
	$pepo->xdr[$year_3] = query_xdr_highlow_by_year($year_3, $xdr_kline);
	echo_v(DEBUG_VERBOSE, "[evaluate_stock] H: id " . $pepo->id . " idr high/low(year) = " .
		$pepo->idr[$year_1]->high . "/" . $pepo->xdr[$year_1]->low . "(" . $year_1 . "), " .
		$pepo->idr[$year_2]->high . "/" . $pepo->xdr[$year_2]->low . "(" . $year_2 . "), " .
		$pepo->idr[$year_3]->high . "/" . $pepo->xdr[$year_3]->low . "(" . $year_3 . ")");

	// I. Get EPS at year 2014-2011
	$pepo->eps[$year_1] = query_year_eps($pepo->id, $year_1);
	$pepo->eps[$year_2] = query_year_eps($pepo->id, $year_2);
	$pepo->eps[$year_3] = query_year_eps($pepo->id, $year_3);
	echo_v(DEBUG_VERBOSE, "[evaluate_stock] I: id " . $pepo->id . " eps(year) = " .
		$pepo->eps[$year_1] . "(" . $year_1 . "), " .
		$pepo->eps[$year_2] . "(" . $year_2 . "), " .
		$pepo->eps[$year_3] . "(" . $year_3 . ")");

	$pepo->eps2[$year_1] = query_year_eps2($pepo->id, $year_1);
	$pepo->eps2[$year_2] = query_year_eps2($pepo->id, $year_2);
	$pepo->eps2[$year_3] = query_year_eps2($pepo->id, $year_3);
	echo_v(DEBUG_VERBOSE, "[evaluate_stock] I: id " . $pepo->id . " eps2(year) = " .
		$pepo->eps2[$year_1] . "(" . $year_1 . "), " .
		$pepo->eps2[$year_2] . "(" . $year_2 . "), " .
		$pepo->eps2[$year_3] . "(" . $year_3 . ")");

	// J. Compute 2014-2011 PER (P/E Ratio) High and Low by H / I

	$pepo->per[$year_1] = new perData();
	$pepo->per[$year_1]->high = $pepo->xdr[$year_1]->high / $pepo->eps[$year_1];
	$pepo->per[$year_1]->low = $pepo->xdr[$year_1]->low / $pepo->eps[$year_1];

	$pepo->per[$year_2] = new perData();
	$pepo->per[$year_2]->high = $pepo->xdr[$year_2]->high / $pepo->eps[$year_2];
	$pepo->per[$year_2]->low = $pepo->xdr[$year_2]->low / $pepo->eps[$year_2];

	$pepo->per[$year_3] = new perData();
	$pepo->per[$year_3]->high = $pepo->xdr[$year_3]->high / $pepo->eps[$year_3];
	$pepo->per[$year_3]->low = $pepo->xdr[$year_3]->low / $pepo->eps[$year_3];

	echo_v(DEBUG_VERBOSE, "[evaluate_stock] J: id " . $pepo->id . " per high/low(year) = " .
	decimal2($pepo->per[$year_1]->high) .	"/" . decimal2($pepo->per[$year_1]->low) . "(" . $year_1 . "), " .
	decimal2($pepo->per[$year_2]->high) .	"/" . decimal2($pepo->per[$year_2]->low) . "(" . $year_2 . "), " .
	decimal2($pepo->per[$year_3]->high) .	"/" . decimal2($pepo->per[$year_3]->low) . "(" . $year_3 . ")");

	$pepo->per_estimated = new perData();
	$pepo->per_estimated->high = per3_high($pepo->per, $date);
	$pepo->per_estimated->low = per3_low($pepo->per, $date);
	//$pepo->per_estimated->high = ($pepo->per[$year_1]->high+$pepo->per[$year_2]->high+$pepo->per[$year_3]->high)/3;
	//$pepo->per_estimated->low = ($pepo->per[$year_1]->low+$pepo->per[$year_2]->low+$pepo->per[$year_3]->low)/3;

	echo_v(DEBUG_VERBOSE, "[evaluate_stock] J: id " . $pepo->id . " estimated per high/low(year) = " .
		decimal2($pepo->per_estimated->high) .	"/" . decimal2($pepo->per_estimated->low) . "(" . $year . ")");
}

function per4_high($pers, $date)
{
	$year = substr($date, 0, 4);
	$year_1 = (string)((int)$year - 1);
	$year_2 = (string)((int)$year - 2);
	$year_3 = (string)((int)$year - 3);
	$year_4 = (string)((int)$year - 4);

	if ($pers[$year_1]->high >= $pers[$year_2]->high and $pers[$year_2]->high >= $pers[$year_3]->high and $pers[$year_3]->high >= $pers[$year_4]->high) // 4 3 2 1
	{
		if ($pers[$year_1]->high / $pers[$year_2]->high >= 1.2)
		{
			return $pers[$year_1]->high*1.1;
		}
		else
		{
			return $pers[$year_1]->high;
		}
	}
	else if ($pers[$year_1]->high >= $pers[$year_2]->high and $pers[$year_2]->high >= $pers[$year_3]->high and $pers[$year_3]->high <= $pers[$year_4]->high) // 3 2 1 2
	{
		return per3_high($pers, $date);
	}
	else if ($pers[$year_1]->high <= $pers[$year_2]->high and $pers[$year_2]->high <= $pers[$year_3]->high and $pers[$year_3]->high <= $pers[$year_4]->high) // 1 2 3 4
	{
		return $pers[$year_1]->high*0.9;
	}
	else if ($pers[$year_1]->high <= $pers[$year_2]->high and $pers[$year_2]->high <= $pers[$year_3]->high and $pers[$year_3]->high >= $pers[$year_4]->high) // 1 2 3 2
	{
		return $pers[$year_1]->high;
	}
	else // 2 1 2 3 and 2 3 2 1
	{
		return ($pers[$year_1]->high+$pers[$year_2]->high+$pers[$year_3]->high+$pers[$year_4]->high)/4;
	}
}

function per4_low($pers, $date)
{
	$year = substr($date, 0, 4);
	$year_1 = (string)((int)$year - 1);
	$year_2 = (string)((int)$year - 2);
	$year_3 = (string)((int)$year - 3);
	$year_4 = (string)((int)$year - 4);

	if ($pers[$year_1]->low >= $pers[$year_2]->low and $pers[$year_2]->low >= $pers[$year_3]->low and $pers[$year_3]->low >= $pers[$year_4]->low) // 4 3 2 1
	{
		if ($pers[$year_1]->low / $pers[$year_2]->low >= 1.2)
		{
			return $pers[$year_1]->low*1.1;
		}
		else
		{
			return $pers[$year_1]->low;
		}
	}
	else if ($pers[$year_1]->low >= $pers[$year_2]->low and $pers[$year_2]->low >= $pers[$year_3]->low and $pers[$year_3]->low <= $pers[$year_4]->low) // 3 2 1 2
	{
		return $pers[$year_1]->low;
	}
	else if ($pers[$year_1]->low <= $pers[$year_2]->low and $pers[$year_2]->low <= $pers[$year_3]->low and $pers[$year_3]->low <= $pers[$year_4]->low) // 1 2 3 4
	{
		return $pers[$year_1]->low*0.9;
	}
	else if ($pers[$year_1]->low <= $pers[$year_2]->low and $pers[$year_2]->low <= $pers[$year_3]->low and $pers[$year_3]->low >= $pers[$year_4]->low) // 1 2 3 2
	{
		return $pers[$year_1]->low;
	}
	else // 2 1 2 3 and 2 3 2 1
	{
		return ($pers[$year_1]->low+$pers[$year_2]->low+$pers[$year_3]->low+$pers[$year_4]->low)/4;
	}
}

function update_stock_data_nopat_over_revenue_diff5($pepo, $date, $xdr_kline)
{
	echo_v(DEBUG_VERBOSE, "**** update_stock_data_nopat_over_revenue_diff5 ************************************************");

	$year = substr($date, 0, 4);
	$year_1 = (string)((int)$year - 1);
	$year_2 = (string)((int)$year - 2);
	$year_3 = (string)((int)$year - 3);
	$year_4 = (string)((int)$year - 4);

$t1 = round(microtime(true) * 1000);

	// H. Get stock prices High and Low at year 2014-2011, including excluded dividends and excluded rights
	$pepo->xdr[$year_1] = query_xdr_highlow_by_year($year_1, $xdr_kline);
	$pepo->xdr[$year_2] = query_xdr_highlow_by_year($year_2, $xdr_kline);
	$pepo->xdr[$year_3] = query_xdr_highlow_by_year($year_3, $xdr_kline);
	$pepo->xdr[$year_4] = query_xdr_highlow_by_year($year_4, $xdr_kline);
	echo_v(DEBUG_VERBOSE, "[evaluate_stock] H: id " . $pepo->id . " xdr high/low(year) = " .
		$pepo->xdr[$year_1]->high . "/" . $pepo->xdr[$year_1]->low . "(" . $year_1 . "), " .
		$pepo->xdr[$year_2]->high . "/" . $pepo->xdr[$year_2]->low . "(" . $year_2 . "), " .
		$pepo->xdr[$year_3]->high . "/" . $pepo->xdr[$year_3]->low . "(" . $year_3 . "), " .
		$pepo->xdr[$year_4]->high . "/" . $pepo->xdr[$year_4]->low . "(" . $year_4 . ")");

$t2 = round(microtime(true) * 1000);
echo_v(LOG_VERBOSE, ($t2-$t1) . " ms to " . '<span style="color:#FF0000">' . "Step H" . '</span>' . "[" . __FUNCTION__ . "]");

	// I. Get EPS at year 2014-2011
	$pepo->eps[$year_1] = query_year_eps($pepo->id, $year_1);
	$pepo->eps[$year_2] = query_year_eps($pepo->id, $year_2);
	$pepo->eps[$year_3] = query_year_eps($pepo->id, $year_3);
	$pepo->eps[$year_4] = query_year_eps($pepo->id, $year_4);
	echo_v(DEBUG_VERBOSE, "[evaluate_stock] I: id " . $pepo->id . " eps(year) = " .
		$pepo->eps[$year_1] . "(" . $year_1 . "), " .
		$pepo->eps[$year_2] . "(" . $year_2 . "), " .
		$pepo->eps[$year_3] . "(" . $year_3 . "), " .
		$pepo->eps[$year_4] . "(" . $year_4 . ")");

	$pepo->eps2[$year_1] = query_year_eps2($pepo->id, $year_1);
	$pepo->eps2[$year_2] = query_year_eps2($pepo->id, $year_2);
	$pepo->eps2[$year_3] = query_year_eps2($pepo->id, $year_3);
	$pepo->eps2[$year_4] = query_year_eps2($pepo->id, $year_4);
	echo_v(DEBUG_VERBOSE, "[evaluate_stock] I: id " . $pepo->id . " eps2(year) = " .
		$pepo->eps2[$year_1] . "(" . $year_1 . "), " .
		$pepo->eps2[$year_2] . "(" . $year_2 . "), " .
		$pepo->eps2[$year_3] . "(" . $year_3 . "), " .
		$pepo->eps2[$year_4] . "(" . $year_4 . ")");

$t3 = round(microtime(true) * 1000);
echo_v(LOG_VERBOSE, ($t3-$t2) . " ms to " . '<span style="color:#FF0000">' . "Step I" . '</span>' . "[" . __FUNCTION__ . "]");

	// J. Compute 2014-2011 PER (P/E Ratio) High and Low by H / I

	$pepo->per[$year_1] = new perData();
	$pepo->per[$year_1]->high = $pepo->xdr[$year_1]->high / $pepo->eps[$year_1];
	$pepo->per[$year_1]->low = $pepo->xdr[$year_1]->low / $pepo->eps[$year_1];

	$pepo->per[$year_2] = new perData();
	$pepo->per[$year_2]->high = $pepo->xdr[$year_2]->high / $pepo->eps[$year_2];
	$pepo->per[$year_2]->low = $pepo->xdr[$year_2]->low / $pepo->eps[$year_2];

	$pepo->per[$year_3] = new perData();
	$pepo->per[$year_3]->high = $pepo->xdr[$year_3]->high / $pepo->eps[$year_3];
	$pepo->per[$year_3]->low = $pepo->xdr[$year_3]->low / $pepo->eps[$year_3];

	$pepo->per[$year_4] = new perData();
	$pepo->per[$year_4]->high = $pepo->xdr[$year_4]->high / $pepo->eps[$year_4];
	$pepo->per[$year_4]->low = $pepo->xdr[$year_4]->low / $pepo->eps[$year_4];

	echo_v(DEBUG_VERBOSE, "[evaluate_stock] J: id " . $pepo->id . " per high/low(year) = " .
	decimal2($pepo->per[$year_1]->high) .	"/" . decimal2($pepo->per[$year_1]->low) . "(" . $year_1 . "), " .
	decimal2($pepo->per[$year_2]->high) .	"/" . decimal2($pepo->per[$year_2]->low) . "(" . $year_2 . "), " .
	decimal2($pepo->per[$year_3]->high) .	"/" . decimal2($pepo->per[$year_3]->low) . "(" . $year_3 . "), " .
	decimal2($pepo->per[$year_4]->high) .	"/" . decimal2($pepo->per[$year_4]->low) . "(" . $year_4 . ")");

	$pepo->per_estimated = new perData();
	$pepo->per_estimated->high = per4_high($pepo->per, $date);
	$pepo->per_estimated->low = per4_low($pepo->per, $date);
	//$pepo->per_estimated->high = ($pepo->per[$year_1]->high+$pepo->per[$year_2]->high+$pepo->per[$year_3]->high+$pepo->per[$year_4]->high)/4;
	//$pepo->per_estimated->low = ($pepo->per[$year_1]->low+$pepo->per[$year_2]->low+$pepo->per[$year_3]->low+$pepo->per[$year_4]->low)/4;

$t4 = round(microtime(true) * 1000);
echo_v(LOG_VERBOSE, ($t4-$t3) . " ms to " . '<span style="color:#FF0000">' . "Step J" . '</span>' . "[" . __FUNCTION__ . "]");

	echo_v(DEBUG_VERBOSE, "[evaluate_stock] J: id " . $pepo->id . " estimated per high/low(year) = " .
		decimal2($pepo->per_estimated->high) .	"/" . decimal2($pepo->per_estimated->low) . "(" . $year . ")");
}

function update_stock_data_nopat_over_revenue($pepo, $date, $xdr_kline)
{
	echo_v(DEBUG_VERBOSE, "**** update_stock_data_nopat_over_revenue ************************************************");

	$year = substr($date, 0, 4);
	$year_1 = (string)((int)$year - 1);
$t1 = round(microtime(true) * 1000);

	// D. Estimate ratio of profit after tax over revenue
	$pepo->nopat_over_revenue_estimated = query_est_profitax_over_revenue($pepo->id, $date);
	echo_v(DEBUG_VERBOSE, "[evaluate_stock] D: id " . $pepo->id . " estimated nopat/revenue (" . $year . ") = " . percent($pepo->nopat_over_revenue_estimated));
$t2 = round(microtime(true) * 1000);
echo_v(LOG_VERBOSE, ($t2-$t1) . " ms to " . '<span style="color:#FF0000">' . "Step D" . '</span>' . "[" . __FUNCTION__ . "]");

	// E. Estimate profit after tax by C * D
	$pepo->nopat_estimated = $pepo->revenue_estimated * $pepo->nopat_over_revenue_estimated;
	echo_v(DEBUG_VERBOSE, "[evaluate_stock] E: id " . $pepo->id . " estimated nopat (" . $year . ") = " . $pepo->nopat_estimated);
$t3 = round(microtime(true) * 1000);
echo_v(LOG_VERBOSE, ($t3-$t2) . " ms to " . '<span style="color:#FF0000">' . "Step E" . '</span>' . "[" . __FUNCTION__ . "]");

	// F. Estimate stock by using 2014 stock directly
	$pepo->stock_now = query_year_stock($pepo->id, $year_1);
	echo_v(DEBUG_VERBOSE, "[evaluate_stock] F: id " . $pepo->id . " estimated stock (" . $year . ") = " . $pepo->stock_now);
$t4 = round(microtime(true) * 1000);
echo_v(LOG_VERBOSE, ($t4-$t3) . " ms to " . '<span style="color:#FF0000">' . "Step F" . '</span>' . "[" . __FUNCTION__ . "]");

	// G. Estimate EPS 2015 by E / F * 10
	$pepo->eps_estimated = $pepo->nopat_estimated / $pepo->stock_now * 10;
	echo_v(DEBUG_VERBOSE, "[evaluate_stock] G: id " . $pepo->id . " estimated eps (" . $year . ") = " . decimal2($pepo->eps_estimated));

	$diff = (int)$year - (int)$pepo->onyyyy;
	// e.x. to satisfy a condition in which we have at least one full year data to work with,
	//      we assume the worst case as now: 2015/1/1, since: 2013/12/31
	if ($diff >= 5)
		update_stock_data_nopat_over_revenue_diff5($pepo, $date, $xdr_kline);
	else if ($diff == 4)
		update_stock_data_nopat_over_revenue_diff4($pepo, $date, $xdr_kline);
	else if ($diff == 3)
		update_stock_data_nopat_over_revenue_diff3($pepo, $date, $xdr_kline);
	else if ($diff == 2)
		update_stock_data_nopat_over_revenue_diff2($pepo, $date, $xdr_kline);
	else
		echo_v (ERROR_VERBOSE, "[update_stock_data_nopat_over_revenue] invalid diff value = " . $diff);
	
	// K. Estimate stock prices High and Low by G * J
	$pepo->xdr_estimated = new xdrHL();
	$pepo->xdr_estimated->high = $pepo->eps_estimated * $pepo->per_estimated->high;
	$pepo->xdr_estimated->low = $pepo->eps_estimated * $pepo->per_estimated->low;
	echo_v(DEBUG_VERBOSE, "[evaluate_stock] K: id " . $pepo->id . " estimated price high/low(year) = " .
		decimal2($pepo->xdr_estimated->high) . "/" . decimal2($pepo->xdr_estimated->low) . "(" . $year . ")");

$t5 = round(microtime(true) * 1000);
echo_v(LOG_VERBOSE, ($t5-$t4) . " ms to " . '<span style="color:#FF0000">' . "Step K=HIJ" . '</span>' . "[" . __FUNCTION__ . "]");
}

function evaluate_stock_price($pepo, $date, $xdr_kline)
{
	echo_v(DEBUG_VERBOSE, "**** evaluate_stock_price ************************************************");

	$year = substr($date, 0, 4);

	// L. Identify the latest stock price
	$dayprices = query_day_price_by_id_y($pepo->id, $year, $xdr_kline);

	while (array_key_exists($date, $dayprices) == false)
	{
		$date = date("Y-m-d", (strtotime($date) - 86400));
		if (substr($date, 0, 4) != $year)
		{
			echo_v(ERROR_VERBOSE, "[evaluate_stock] " . $date . " is not among price data." );
			return;
		}
	}
	$pepo->evaluate_date = $date;
	$pepo->evaluate_price = $dayprices[$date];

	echo_v(DEBUG_VERBOSE, "[evaluate_stock] L: id " . $pepo->id . " at day " . $pepo->evaluate_date . ", price = " . $pepo->evaluate_price);

	// M. Compute the potential profit margin and risk margin
	$potential_profit_margin = ($pepo->xdr_estimated->high - $pepo->evaluate_price) / $pepo->evaluate_price;
	$potential_risk_margin = -($pepo->xdr_estimated->low - $pepo->evaluate_price) / $pepo->evaluate_price;
	$pepo->potential = percent($potential_profit_margin) . "/" . percent($potential_risk_margin);
	echo_v(DEBUG_VERBOSE, "[evaluate_stock] M: id " . $pepo->id . " at year " . $year .
		", potential profit margin = " . percent($potential_profit_margin) .
		", potential risk margin = " . percent($potential_risk_margin));

	// N. If potential profit margin is twice as risk margin, it is worthy of buying
	//    case 1:   high<----->now<------->low, profit and risk are both positive -> 
	//    case 2:   now<------>high<------->low, profit is negative and risk is large positive
	//    case 3:   high<----->low<--------->now, profit is large positive and risk is negative

	$pepo->stock_position =
		decimal2($pepo->xdr_estimated->high) . "/" .
		decimal2($pepo->xdr_estimated->low);
	echo_v (DEBUG_VERBOSE, "[evaluate_stock] N: id " . $pepo->id . " at day " . $date . ", " . $pepo->stock_position);

	$centurion = $pepo->xdr_estimated->high - $pepo->xdr_estimated->low;
	$celsius = $pepo->evaluate_price - $pepo->xdr_estimated->low;

	if ($centurion==0)
		$pepo->verdict = 100.0;
	else
		$pepo->verdict = decimal2(($celsius/$centurion)*100);
}

function query_topN_ids_from_start($id_values, $start, $topN)
{
	if(($start + $topN) > count($id_values))
	{
		echo_v(ERROR_VERBOSE, "[query_topN_ids_from_start] N is larger than number of stock ids!");
		return null;
	}

	return array_slice($id_values, $start, $topN, true);	
}

function stockEvaluateTest($id, $since_date, $id_prices, $id_yoys, $sii_kline)
{
	$id_kline = query_day_price_lochs_by_id_since($id, '2010-01-01');
	$xdr_kline = convert_idr_to_xdr($id, $id_kline);
	prepare_id_seasonly_xbrl($id);
	prepare_id_monthly_revenue($id);
	$stock = query_id_data_by_id($id);
	
	if ($stock == null)
	{
		echo_v(ERROR_VERBOSE, "[stockEvaluateTest] stock id " . $id . " is not found!");
		return;
	}

	echo '  <table>' . "\n";
	echo '    <tbody>' . "\n";
	echo '      <tr>' . "\n";
	echo '        <td>' . "\n";

	// 股票簡介及近況
	if($id_prices!=null)
		$price_rank = array_search($id, array_keys($id_prices));
	else
		$price_rank = false;
	if($id_yoys!=null)
		$yoy_rank = array_search($id, array_keys($id_yoys));
	else
		$yoy_rank = false;
	if (false !== $price_rank and false !== $yoy_rank)
		show_stock_brief($stock, $price_rank, $id_prices[$id], $yoy_rank, $id_yoys[$id]);
	else if (false !== $price_rank and false === $yoy_rank)
		show_stock_brief($stock, $price_rank, $id_prices[$id], -1, -1);
	else if (false === $price_rank and false !== $yoy_rank)
		show_stock_brief($stock, -1, -1, $yoy_rank, $id_yoys[$id]);
	else if (false === $price_rank and false === $yoy_rank)
		show_stock_brief($stock, -1, -1, -1, -1);

	// 三部曲
	// 1) initialize_stock_data: 做一個初始化動作 --> A) 前一年營收 B) 過去六個月月營收年增率與上個月月營收年增率孰低 C) 預估的今年營收
	// 2) update_stock_data_nopat_over_revenue: 做一個更新的動作 --> D) 過去四季稅後淨利率平均 E) 預估的今年稅後淨利 F) 預估今年股本 G) 預估今年EPS H) 近四年還原股價高低點 I) 近四年EPS J) 近四年 P/E Ratio 高低點  K) 預估今年還原股價高低點
	// 3) evaluate_stock_price: 做一個評估的動作 --> 根據目前股價評估是否值得投資

	$pepos = array();

	$dates = query_evaluate_dates_since($id, $since_date, $sii_kline);

	foreach ($dates as $date)
	{
$t1 = round(microtime(true) * 1000);
		$pepo = initialize_stock_data($stock, $date);
$t2 = round(microtime(true) * 1000);
echo_v(LOG_VERBOSE, ($t2-$t1) . " ms to ". "INITIALIZE_STOCK_DATA" . "[" . __FUNCTION__ . "]");
		if ($pepo == null)
		{
			echo '    </tbody>' . "\n";
			echo '  </table><br>' . "\n";
			return;
		}
		update_stock_data_nopat_over_revenue($pepo, $date, $xdr_kline);
$t3 = round(microtime(true) * 1000);
echo_v(LOG_VERBOSE, ($t3-$t2) . " ms to ". "UPDATE_STOCK_DATA_NOPAT_OVER_REVENUE" . "[" . __FUNCTION__ . "]");

		evaluate_stock_price($pepo, $date, $xdr_kline);
$t4 = round(microtime(true) * 1000);
echo_v(LOG_VERBOSE, ($t4-$t3) . " ms to ". "EVALUATE_STOCK_PRICE" . "[" . __FUNCTION__ . "]");

		array_push($pepos, $pepo);
	}

	//echo '  <table>' . "\n";
	//echo '    <tbody>' . "\n";
	//echo '      <tr>' . "\n";
	//echo '        <td>' . "\n";

	// 最近四年還原股價與本益比
	show_idr_per($pepos);

	//echo '        <td>' . "\n";

	// 最近至少八季財務報表
	echo_v(LOG_VERBOSE, stopwatch_inter() . " ms to ". formatstr("load_seasonly_xbrl") . "[" . __FUNCTION__ . "]");
	$xbrls = load_seasonly_xbrl($id);
	echo_v(LOG_VERBOSE, stopwatch_inter() . " ms to ". formatstr("load_seasonly_xbrl") . "[" . __FUNCTION__ . "]");
	show_xbrl($xbrls);

	//echo '    </tbody>' . "\n";
	//echo '  </table><br>' . "\n";

	// 最近十二個月月營收
	echo_v(LOG_VERBOSE, stopwatch_inter() . " ms to ". formatstr("load_monthly_revenue") . "[" . __FUNCTION__ . "]");
	$month = load_monthly_revenue($id);
	echo_v(LOG_VERBOSE, stopwatch_inter() . " ms to ". formatstr("load_monthly_revenue") . "[" . __FUNCTION__ . "]");
	show_monthly_revenue($month);

	// 股價評估過程
	show_stock_evaluation($pepos);

	echo '        </td>' . "\n";
	echo '        <td>' . "\n";

	echo_v(LOG_VERBOSE, stopwatch_inter() . " ms to ". formatstr("query_day_price_lochs_by_id_since") . "[" . __FUNCTION__ . "]");
	$prices = query_day_price_lochs_by_id_since($id, $since_date);
	echo_v(LOG_VERBOSE, stopwatch_inter() . " ms to ". formatstr("query_day_price_lochs_by_id_since") . "[" . __FUNCTION__ . "]");
	$prices = array_reverse($prices);
	show_stock_candlestick_chart($id, $prices);
	show_stock_bar_chart($id, $prices);

	show_stock_candlestick_chart_with_pepo($id, $prices, $pepos);

	echo_v(LOG_VERBOSE, stopwatch_inter() . " ms to ". formatstr("query_day_price_lochs_by_id_since") . "[" . __FUNCTION__ . "]");
	$prices = query_day_price_lochs_by_id_since('sii', $since_date);
	echo_v(LOG_VERBOSE, stopwatch_inter() . " ms to ". formatstr("query_day_price_lochs_by_id_since") . "[" . __FUNCTION__ . "]");
	$prices = array_reverse($prices);
	show_sii_candlestick_chart($prices);

	echo '        </td>' . "\n";
	echo '      </tr>' . "\n";
	echo '    </tbody>' . "\n";
	echo '  </table><br>' . "\n";
}

function stocksixtyDaysTest($ids, $sii_kline)
{
	$dates = array_keys($sii_kline);
	$start_date = $dates[60];

	echo_n('  <table>');
	echo_n('    <caption>近六十日收盤價</caption>');

	echo '    <thead><th>股票代號<th>股票名稱';
	for($ii=0;$ii<60;$ii++)
		echo_n('<th>' . $dates[59-$ii]);
	echo_n('</thead>');

	echo_n('    <tbody>');

	//$jj = 1;
	foreach($ids as $id)
	{
		$id_kline = query_day_price_by_id_since($id, $start_date);

		echo_n('      <tr>');
		echo '        <td>' . $id . '<td>' . query_name_by_id($id);
		for($ii=0;$ii<60;$ii++)
		{
			$key = $dates[59-$ii];
			if (array_key_exists($key, $id_kline))
				echo '<td>' . $id_kline[$key];
			else
				echo '<td>';
		}
		echo_n('');

		//if ($jj >= 1)
		//	break;
		//$jj++;
	}

	echo_n('    </tbody>');
	echo_n('  </table><br>');
}

function stocksixtyDaysTest2($ids, $sii_kline)
{
	$dates = array_keys($sii_kline);
	$start_date = $dates[60];

	echo '股票代號,股票名稱,';
	for($ii=0;$ii<60;$ii++)
		echo ($dates[59-$ii].',');
	echo_n('');

	$jj = 1;
	foreach($ids as $id)
	{
		$id_kline = query_day_price_by_id_since($id, $start_date);

		echo $id . ',' . query_name_by_id($id) . ',';
		for($ii=0;$ii<60;$ii++)
		{
			$key = $dates[59-$ii];
			if (array_key_exists($key, $id_kline))
				echo $id_kline[$key] . ',';
			else
				echo ',';
		}
		echo_n('');

		//if ($jj >= 5)
		//	break;
		//$jj++;
	}
}

function stockNoDataDaysTest($iddata, $sii_kline)
{
	$ii = 1;
	foreach($iddata as $id => $identry)
	{
		//echo_n( $id . '.' . $identry->onyyyy . $identry->onmm );

		echo_n('    <canvas id="' . $id . '" width="2400", height="1"></canvas>');
		echo_n('    <script type="text/javascript">');
		echo_n('        var element' . $id . ' = document.getElementById("' . $id . '");');
		echo_n('        var context' . $id . ' = element' . $id . '.getContext("2d");');

		echo_n('		element' . $id . '.addEventListener("mousemove", function(evt) {');
		echo_n('			var mousePos = getMousePos(element' . $id . ', evt);');
		echo_n('			var message = "Mouse position: " + mousePos.x + "   ,   " + mousePos.y;');
		echo_n('			writeMessage(element' . $id . ', message);');
		echo_n('		}, false);');

		echo_n('        // read the width and height of the canvas');
		echo_n('        var width = element' . $id . '.width;');
		echo_n('        var height = element' . $id . '.height;');

		echo_n('        // create a new pixels array.');
		echo_n('        var imageData = context' . $id . '.createImageData(width, height);');
		echo_n('        var pos = 0; // index position into imagedata array');

		$birthmonth = (int)($identry->onyyyy . $identry->onmm);

		$id_kline = query_day_price_by_id_since($id, '2010-01-01');
		$jj = 1;
		foreach($sii_kline as $date => $price)
		{
			$yyyy = substr($date, 0, 4);
			$mm = substr($date, 5, 2);
			if (array_key_exists($date, $id_kline) && ((int)($yyyy . $mm) >= $birthmonth))
			{
				$x_pos = (strtotime($date)-strtotime('2010-01-01')) / 86400;
				$pos = $x_pos * 4; 
				echo_n('        pos = ' . $pos . ';');
				echo_n('        imageData.data[pos++] = ' . ((int)$price/256) . ';'); // ((int)$price*16/256)
				echo_n('        imageData.data[pos++] = ' . ((int)$price%256) . ';'); // ((int)$price*16%256)
				echo_n('        imageData.data[pos++] = 255; // blue');
				echo_n('        imageData.data[pos++] = 255; // opaque alpha');
			}

			//if ($jj >= 15)
			//	break;
			$jj++;
		}

		echo_n('        // copy the image data back onto the canvas');
		echo_n('        context' . $id . '.putImageData(imageData, 0, 0); // at coords 0,0');
		echo_n('    </script>');

		//echo_n('    <canvas id="' . $id . '" width="2400", height="1"></canvas>');
		//echo_n('	<div id="status' . $id . '"></div>');

		if ($ii >= 200)
			break;
		$ii++;
	}
	echo_v(NO_VERBOSE, "<br><br>Daydata: " . $ii . " items.");
}

?>