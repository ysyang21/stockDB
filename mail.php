<?php

$hrf = "http://iwork.apc.gov.tw/HRF_WEB";
$hrb = "http://iwork.apc.gov.tw/HRB_WEB";
$job = "http://iwork.apc.gov.tw/JOB_COUNSELORS_WEB";

$netktv = "ysyang@netktv.com";

$ys = "ysyang@tgic.org.tw";		// engineer 1
$lj = "9300lj@tgic.org.tw";		// engineer 2
$yg = "ygdry7331@tgic.org.tw";	// pm 1
$wsc = "wsc@tgic.org.tw";		// pm 2
$wh = "wenhan@tgic.org.tw";		// artist
$east = "east0122@tgic.org.tw";	// director, don't send to him

$art = "Dear Team APCJob,\n\n" .
		"    I'm sorry to inform you that website in the title is down. Please help to fix it asap, thanks a lot!\n\n" .
		"BR,\n" .
		"Guardian\n";


function send_notify_mail($to, $url, $msg, $from)
{
	if(mail("$to", "[APCJob] $url is down!!", "$msg", "From: $from"))
		echo "Mail is sent ok!<br>";
	else
		echo "Mail is sent fail!<br>";
}

send_notify_mail($ys, $job, $art, $netktv);

?>