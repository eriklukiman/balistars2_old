<?php
$dbHost = "localhost";
$dbUser = "bintangbali";
$dbPassword = "b@l!b3Rsin4r";
$dbName = "balistars";

try {
	$db = new PDO("mysql:dbhost=$dbHost; dbname=$dbName", "$dbUser", "$dbPassword");
} catch (PDOException $e) {
	// echo $e->getMessage();
}
