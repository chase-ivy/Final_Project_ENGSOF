<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('dbhost', 'localhost');
define('dbuser', 'root');
define('dbpass', '');
define('dbname', 'rsync_db');
define('dbport', '3306'); // change if needed

try {
    $connect = new PDO(
        "mysql:host=" . dbhost . ";port=" . dbport . ";dbname=" . dbname,
        dbuser,
        dbpass
    );

    $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}