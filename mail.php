<?php

include_once("LIB_log.php");

include_once("stockWebpage.php");

$hrf = "http://iwork.apc.gov.tw/HRF_WEB";
$hrb = "http://iwork.apc.gov.tw/HRB_WEB";
$job = "http://iwork.apc.gov.tw/JOB_COUNSELORS_WEB";

function send_notify_mail($url)
{
	$to = "ysyang21@gmail.com"; //收件者
	$subject = "APC website notification"; //信件標題
	$msg = "$url id down";//信件內容
	$headers = "From: ysyang@tgic.org.tw"; //寄件者

	if(mail("$to", "$subject", "$msg", "$headers"))
		echo "Mail is sent ok!\n";//寄信成功就會顯示的提示訊息
	else
		echo "Mail is sent fail!\n";//寄信失敗顯示的錯誤訊息
}

// 網頁頭
$t1 = show_webpage_header('APC Monitor');

// 網頁內容
send_notify_mail($hrf);

// 網頁尾
show_webpage_tail($t1);


?>