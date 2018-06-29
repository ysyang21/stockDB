<?php

// Connect to DB
$dbhost = '127.0.0.1';
$dbuser = 'root';
$dbpass = 'mouton88';
$dbname = 'stockDB';
$conn = mysqli_connect($dbhost, $dbuser, $dbpass) or die('Error with MySQL connection');
mysqli_query($conn, "SET NAMES 'utf8'");
mysqli_select_db($conn, $dbname);

function exist_by($query)
{
	global $conn;
	$result = mysqli_query($conn, $query) or die('MySQL query error');
	$exist=false;
	while( $row = mysqli_fetch_array($result)){
		$exist=true;
	}
	return $exist;
}

?>
