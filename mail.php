<?php

$hrf = "http://iwork.apc.gov.tw/HRF_WEB";
$hrb = "http://iwork.apc.gov.tw/HRB_WEB";
$job = "http://iwork.apc.gov.tw/JOB_COUNSELORS_WEB";

$gmail = "ysyang21@gmail.com";

$ys = "ysyang@tgic.org.tw";
$lj = "9300lj@tgic.org.tw";
$yg = "ygdry7331@tgic.org.tw";
$wsc = "wsc@tgic.org.tw";
$wh = "wenhan@tgic.org.tw";
$east = "east0122@tgic.org.tw";

function send_notify_mail($from, $to, $url)
{
	$subject = "[APCJob] $url is down!!";
	$msg = "Dear APCJob team, \n\n" .
			"    I'm sorry to inform you that $url is down. Please help to fix it asap, thanks a lot! \n\n" .
			"BR,\n" .
			"Site Monitor\n";
	$headers = "From: $from";

	if(mail("$to", "$subject", "$msg", "$headers"))
		echo "Mail is sent ok!\n";
	else
		echo "Mail is sent fail!\n";
}

send_notify_mail($ys, $gmail, $hrf);

?>