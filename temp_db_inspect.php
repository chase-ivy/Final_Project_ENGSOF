<?php
try {
    $db = new PDO('mysql:host=localhost;port=3306;dbname=rsync_db','root','');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    foreach ($db->query('SHOW TABLES') as $row) {
        echo $row[0] . PHP_EOL;
    }
} catch (PDOException $e) {
    echo 'ERROR: ' . $e->getMessage();
}
