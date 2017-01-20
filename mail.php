<?php

$ys = "ysyang@tgic.org.tw";		// engineer 1
$lj = "9300lj@tgic.org.tw";		// engineer 2
$yg = "ygdry7331@tgic.org.tw";	// pm 1
$wsc = "wsc@tgic.org.tw";		// pm 2
$wh = "wenhan@tgic.org.tw";		// artist
$east = "east0122@tgic.org.tw";	// director, don't send to him

function send_notify_mail($to, $url, $status, $from)
{
	$msg_down = "Dear Team APCJob,\n\n" .
			"    I'm sorry to inform you that the host/website is down. Please help to fix it asap, thanks a lot! Q_Q\n\n" .
			"BR,\n" .
			"Guardian\n";

	$msg_fine = "Dear Team APCJob,\n\n" .
			"    I'm happy to inform you that the host/website is fine. Enjoy your time! Y^^Y\n\n" .
			"BR,\n" .
			"Guardian\n";

	if ($status=='down')
	{
		mail($to, "[APCJob] $url is down!!", $msg_down, "From: $from");
	}
	else if ($status=='fine')
	{
		mail($to, "[APCJob] $url is fine!!", $msg_fine, "From: $from");
	}
	else
	{
		//mail($to, "[APCJob] $url is else!!", $msg_else, "From: $from");
	}
}

// to and from can't be in the same domain, or it will be regarded as a spoofing mail and thrown into garbage folder
// a link can't be in the mail content, or it will be regarded as a dangerous mail and thrown into garbage folder
//send_notify_mail($ys, $job, "ysyang@tgic.org.tw");

send_notify_mail($ys, $argv[1], $argv[2], "ysyang@netktv.com");
send_notify_mail($yg, $argv[1], $argv[2], "ysyang@netktv.com");

?>