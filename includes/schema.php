<?php

function listing_category_options()
{
    return array('Tops', 'Bottoms', 'Dresses', 'Outerwear', 'Accessories');
}

function listing_size_options()
{
    return array('XS', 'S', 'M', 'L', 'XL');
}

function listing_condition_options()
{
    return array('Excellent', 'Good', 'Fair');
}

function validate_listing_category($value)
{
    return in_array($value, listing_category_options(), true) ? $value : 'Tops';
}

function validate_listing_size($value)
{
    return in_array($value, listing_size_options(), true) ? $value : 'M';
}

function validate_listing_condition($value)
{
    return in_array($value, listing_condition_options(), true) ? $value : 'Good';
}

function ensure_clothes_column($conn, $column, $definition)
{
    $safeColumn = preg_replace('/[^a-zA-Z0-9_]/', '', $column);
    if ($safeColumn === '') {
        return false;
    }

    $check = $conn->query("SHOW COLUMNS FROM tblClothes LIKE '" . $conn->real_escape_string($safeColumn) . "'");
    if (!$check) {
        return false;
    }

    if ($check->num_rows === 0) {
        return (bool) $conn->query("ALTER TABLE tblClothes ADD COLUMN `$safeColumn` $definition");
    }

    return true;
}

function ensure_pastimes_schema($conn)
{
    if (!$conn) {
        return false;
    }

    ensure_clothes_column($conn, 'sellerID', 'INT NULL');
    ensure_clothes_column($conn, 'category', "VARCHAR(50) DEFAULT 'Tops'");
    ensure_clothes_column($conn, 'size', "VARCHAR(20) DEFAULT 'M'");
    ensure_clothes_column($conn, 'condition', "VARCHAR(20) DEFAULT 'Good'");

    $tableCheck = $conn->query("SHOW TABLES LIKE 'tblMessage'");
    if ($tableCheck && $tableCheck->num_rows === 0) {
        $conn->query("
            CREATE TABLE tblMessage (
                messageID INT AUTO_INCREMENT PRIMARY KEY,
                senderUserID INT NOT NULL,
                recipientType ENUM('seller','admin') NOT NULL,
                recipientUserID INT NULL,
                itemID INT NULL,
                subject VARCHAR(150) NOT NULL,
                body TEXT NOT NULL,
                isRead TINYINT(1) DEFAULT 0,
                createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (senderUserID) REFERENCES tblUser(userID),
                FOREIGN KEY (recipientUserID) REFERENCES tblUser(userID),
                FOREIGN KEY (itemID) REFERENCES tblClothes(itemID)
            )
        ");
    }

    return true;
}
