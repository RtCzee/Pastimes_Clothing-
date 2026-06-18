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

function ensure_table_exists($conn, $tableName, $createSql)
{
    $safeName = preg_replace('/[^a-zA-Z0-9_]/', '', $tableName);
    if ($safeName === '') {
        return false;
    }

    $check = $conn->query("SHOW TABLES LIKE '" . $conn->real_escape_string($safeName) . "'");
    if ($check && $check->num_rows > 0) {
        return true;
    }

    return (bool) $conn->query($createSql);
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

    ensure_table_exists($conn, 'tblCheckout', "
        CREATE TABLE tblCheckout (
            checkoutID INT AUTO_INCREMENT PRIMARY KEY,
            checkoutReference VARCHAR(40) UNIQUE,
            userID INT,
            itemCount INT,
            subtotal DECIMAL(10,2),
            shipping DECIMAL(10,2),
            total DECIMAL(10,2),
            cartSnapshot LONGTEXT,
            status VARCHAR(20) DEFAULT 'completed',
            createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (userID) REFERENCES tblUser(userID)
        )
    ");

    ensure_table_exists($conn, 'tblAorder', "
        CREATE TABLE tblAorder (
            orderID INT AUTO_INCREMENT PRIMARY KEY,
            checkoutReference VARCHAR(40),
            userID INT,
            itemID INT,
            quantity INT,
            orderDate DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (userID) REFERENCES tblUser(userID),
            FOREIGN KEY (itemID) REFERENCES tblClothes(itemID)
        )
    ");

    ensure_table_exists($conn, 'tblReview', "
        CREATE TABLE tblReview (
            reviewID INT AUTO_INCREMENT PRIMARY KEY,
            productId VARCHAR(40),
            userID INT,
            username VARCHAR(50),
            rating INT,
            comment TEXT,
            createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (userID) REFERENCES tblUser(userID)
        )
    ");

    $tableCheck = $conn->query("SHOW TABLES LIKE 'tblMessage'");
    if ($tableCheck && $tableCheck->num_rows === 0) {
        $conn->query("
            CREATE TABLE tblMessage (
                messageID INT AUTO_INCREMENT PRIMARY KEY,
                senderUserID INT NULL,
                senderAdminID INT NULL,
                recipientType ENUM('seller','admin','buyer') NOT NULL,
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
    } else {
        $senderAdminCheck = $conn->query("SHOW COLUMNS FROM tblMessage LIKE 'senderAdminID'");
        if ($senderAdminCheck && $senderAdminCheck->num_rows === 0) {
            $conn->query('ALTER TABLE tblMessage ADD COLUMN senderAdminID INT NULL AFTER senderUserID');
        }
        $conn->query("ALTER TABLE tblMessage MODIFY senderUserID INT NULL");
        $conn->query("ALTER TABLE tblMessage MODIFY recipientType ENUM('seller','admin','buyer') NOT NULL");
    }

    return true;
}
