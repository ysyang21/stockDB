<?php

$hrf = "http://iwork.apc.gov.tw/HRF_WEB";
$hrb = "http://iwork.apc.gov.tw/HRB_WEB";
$job = "http://iwork.apc.gov.tw/JOB_COUNSELORS_WEB";

$gmail = "ysyang21@gmail.com";

$ys = "ysyang@tgic.org.tw";		// engineer 1
$lj = "9300lj@tgic.org.tw";		// engineer 2
$yg = "ygdry7331@tgic.org.tw";	// pm 1
$wsc = "wsc@tgic.org.tw";		// pm 2
$wh = "wenhan@tgic.org.tw";		// artist
$east = "east0122@tgic.org.tw";	// director, don't send to him

function send_notify_mail($from, $to, $url)
{
	$subject = "[APCJob] $url is down!!";
	$msg = "Dear Team APCJob,\n\n" .
			"    I'm sorry to inform you that $url is down. Please help to fix it asap, thanks a lot!\n\n" .
			"BR,\n" .
			"Guardian\n";
	$headers = "From: $from";

	if(mail("$to", "$subject", "$msg", "$headers"))
		echo "Mail is sent ok!\n";
	else
		echo "Mail is sent fail!\n";
}

//send_notify_mail($ys, $gmail, $hrf);
send_notify_mail($ys, $yg, $job);

?>