<?php

function dbConnect(){

	global $conn;

	$servername = "localhost";
	$username = "root";
	$password = "123";
	$dbName = "lasangha_bhavana_1";

	# Create connection
	$conn = new mysqli($servername, $username, $password, $dbName);

	# Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}
	else{
		//print "Connected";
	}
}

/**
 * I make queries
 */
function dbQuery($q){
	
	global $conn;

	$result = $conn->query($q);

	return $result;

}

