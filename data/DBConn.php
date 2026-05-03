<?php
// DBConn.php
//declear variable fopr connections 
$server = (string) "localhost";
$username = (string) "root";
$password = (string) "";
$database = (string) "ClothingStore";
$port = (int) 3306;

$conn = mysqli_connect($server, $username, $password, $database ,$port);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>

