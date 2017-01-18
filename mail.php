<?php
  $to = "ysyang21@gmail.com"; //收件者
  $subject = "APC website notification"; //信件標題
  $msg = "APC1 id down";//信件內容
  $headers = "From: admin@netktv.com"; //寄件者
  
  if(mail("$to", "$subject", "$msg", "$headers")):
   echo "Mail is sent ok!\n";//寄信成功就會顯示的提示訊息
  else:
   echo "Mail is sent fail!\n";//寄信失敗顯示的錯誤訊息
  endif;
?>