<?php
include(__DIR__ . "/DBConn.php");


  // DROP TABLES (IMPORTANT ORDER)

mysqli_query($conn, "DROP TABLE IF EXISTS tblAorder");
mysqli_query($conn, "DROP TABLE IF EXISTS tblClothes");
mysqli_query($conn, "DROP TABLE IF EXISTS tblAdmin");
mysqli_query($conn, "DROP TABLE IF EXISTS tblUser");



// CREATE tblUser

mysqli_query($conn, "
CREATE TABLE tblUser (
    userID INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE,
    fullName VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    status ENUM('pending','active','declined') DEFAULT 'pending',
    role ENUM('buyer','seller') DEFAULT 'buyer'
)
");



   // CREATE tblAdmin

mysqli_query($conn, "
CREATE TABLE tblAdmin (
    adminID INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50),
    email VARCHAR(100),
    password VARCHAR(255)
)
");


   // CREATE tblClothes

mysqli_query($conn, "
CREATE TABLE tblClothes (
    itemID INT AUTO_INCREMENT PRIMARY KEY,
    sellerID INT,
    itemName VARCHAR(100),
    description VARCHAR(255),
    price DECIMAL(10,2),
    image VARCHAR(200),
    FOREIGN KEY (sellerID) REFERENCES tblUser(userID)
)
");



   // CREATE tblAorder

mysqli_query($conn, "
CREATE TABLE tblAorder (
    orderID INT AUTO_INCREMENT PRIMARY KEY,
    userID INT,
    itemID INT,
    quantity INT,
    orderDate DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (userID) REFERENCES tblUser(userID),
    FOREIGN KEY (itemID) REFERENCES tblClothes(itemID)
)
");


// INSERTING DATA IN tblAdmin

$hashed = password_hash("admin123", PASSWORD_DEFAULT);
if (mysqli_query($conn, "
INSERT INTO tblAdmin (username, email, password)
VALUES ('admin', 'admin@store.com', '$hashed')
")) {
    echo "Admin inserted successfully<br>";
} else {
    echo "Error: " . mysqli_error($conn);
}



   // LOAD USER DATA FROM FILE

// Path to file
$filePath = __DIR__ . "/userData.txt";

// Check if file exists BEFORE opening
if (!file_exists($filePath)) {
    die("userData.txt not found!");
}

// Open file safely
$file = fopen($filePath, "r");

// Extra safety check
if (!$file) {
    die("Could not open userData.txt");
}

// Read file line by line
while (($line = fgets($file)) !== false) {

    // Split line into parts
    $data = explode(",", trim($line));

    $username = $data[0];
    $fullName = $data[1];
    $email    = $data[2];
    $password = $data[3];

    // Hash password (IMPORTANT)
    $hashed = password_hash($password, PASSWORD_DEFAULT);

    // Insert into database
    mysqli_query($conn, "
        INSERT INTO tblUser (username, fullName, email, password)
        VALUES ('$username', '$fullName', '$email', '$hashed')
    ");
}

// Close file properly
fclose($file);

echo "All tables created and user data loaded successfully!";