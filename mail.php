<?php

$netktv = "ysyang@netktv.com";

$ys = "ysyang@tgic.org.tw";		// engineer 1
$lj = "9300lj@tgic.org.tw";		// engineer 2
$yg = "ygdry7331@tgic.org.tw";	// pm 1
$wsc = "wsc@tgic.org.tw";		// pm 2
$wh = "wenhan@tgic.org.tw";		// artist
$east = "east0122@tgic.org.tw";	// director, don't send to him

function send_notify_mail($to, $url, $status, $from)
{
	$msg_down = "Dear Team APCJob,\n\n" .
			"    I'm sorry to inform you that website in the title is down. Please help to fix it asap, thanks a lot QQ!\n\n" .
			"BR,\n" .
			"Guardian\n";

	$msg_fine = "Dear Team APCJob,\n\n" .
			"    I'm happy to inform you that website in the title is fine. You can enjoy additional peaceful hour ^^!\n\n" .
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

function send_notify_mail2($to, $url, $from)
{
	$msg = "Dear Team APCJob,\n\n" .
			"    I'm sorry to inform you that $url is down. Please help to fix it asap, thanks a lot!\n\n" .
			"BR,\n" .
			"Guardian\n"; // $url in the mail content, will cause the mail server treat it as dangerous

	if(mail($to, "[APCJob] $url is down!!", $msg, "From: $from"))
		echo "Mail is sent ok!<br>";
	else
		echo "Mail is sent fail!<br>";
}

// to and from can't be in the same domain, or it will be regarded as a spoofing mail and thrown into garbage folder
//send_notify_mail($ys, $job, "ysyang@tgic.org.tw");

// a link can't be in the mail content, or it will be regarded as a dangerous mail and thrown into garbage folder
//send_notify_mail2($ys, $job, $netktv);

send_notify_mail($ys, $argv[1], $argv[2], $netktv);

?>