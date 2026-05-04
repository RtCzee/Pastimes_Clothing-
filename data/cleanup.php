<?php
// Cleanup script to remove items without images from the database
require_once 'DBConn.php';

// Delete items without an image
$result = $conn->query("DELETE FROM tblClothes WHERE image IS NULL OR image = ''");

if ($result) {
    echo "Cleanup complete! Removed items without images from the store.";
    echo "<br><a href='../Shop.php'>Back to Shop</a>";
} else {
    echo "Error: " . $conn->error;
    echo "<br><a href='../Shop.php'>Back to Shop</a>";
}
?>
