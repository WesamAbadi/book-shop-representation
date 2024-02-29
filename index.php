<?php

$servername = "localhost";
$username = "username";
$password = "";
$dbname = "bookshop";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->close();
?>
