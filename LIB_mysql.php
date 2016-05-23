<?php

// Connect to DB
$dbhost = '127.0.0.1';
$dbuser = 'root';
$dbpass = 'mouton88';
$dbname = 'stockDB';
$conn = mysql_connect($dbhost, $dbuser, $dbpass) or die('Error with MySQL connection');
mysql_query("SET NAMES 'utf8'");
mysql_select_db($dbname);

?>
