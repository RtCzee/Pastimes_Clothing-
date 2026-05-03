<?php
// loadClothingStore.php

$server = "localhost";
$username = "root";
$password = "";

// connect WITHOUT selecting DB first
$conn = mysqli_connect($server, $username, $password);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// CREATE DATABASE
$sql = "CREATE DATABASE IF NOT EXISTS ClothingStore";
mysqli_query($conn, $sql);

// select database
mysqli_select_db($conn, "ClothingStore");

// include table script
include("createTable.php");

echo "Database and tables created successfully!";
?>