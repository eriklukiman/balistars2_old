<?php
$dbHost = "localhost";
$dbUser = "rx";
$dbPassword = "a";
$dbName = "balistars";

try {
	$db = new PDO("mysql:dbhost=$dbHost; dbname=$dbName", "$dbUser", "$dbPassword");
} catch (PDOException $e) {
	// echo $e->getMessage();
}
