<?php

$hrf = "http://iwork.apc.gov.tw/HRF_WEB";
$hrb = "http://iwork.apc.gov.tw/HRB_WEB";
$job = "http://iwork.apc.gov.tw/JOB_COUNSELORS_WEB";

$netktv = "ysyang@netktv.com";

$ys = "ysyang";		// engineer 1
$lj = "9300lj";		// engineer 2
$yg = "ygdry7331";	// pm 1
$wsc = "wsc";		// pm 2
$wh = "wenhan";		// artist
$east = "east0122";	// director, don't send to him

function send_notify_mail($to, $url, $from)
{
	$msg = "Dear Team APCJob,\n\n" .
			"    I'm sorry to inform you that website in the title is down. Please help to fix it asap, thanks a lot!\n\n" .
			"BR,\n" .
			"Guardian\n";

	if(mail("$to@tgic.org.tw", "[APCJob] $url is down!!", "$msg", "From: $from"))
		echo "Mail is sent ok!<br>";
	else
		echo "Mail is sent fail!<br>";
}

function send_notify_mail2($to, $url, $from)
{
	$msg = "Dear Team APCJob,\n\n" .
			"    I'm sorry to inform you that $url is down. Please help to fix it asap, thanks a lot!\n\n" .
			"BR,\n" .
			"Guardian\n";

	if(mail("$to@tgic.org.tw", "[APCJob] $url is down!!", "$msg", "From: $from"))
		echo "Mail is sent ok!<br>";
	else
		echo "Mail is sent fail!<br>";
}

send_notify_mail($ys, $job, $netktv);				// this will be ok
send_notify_mail($ys, $job, "ysyang@tgic.org.tw");	// to and from can't be in the same domain, or it will be regarded as a fraud mail
send_notify_mail2($ys, $job, $netktv);				// $url can't be in the message, or it will be regarded as a fraud mail

?>