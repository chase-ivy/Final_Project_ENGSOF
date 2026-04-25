<?php
include 'config.php';
include 'oop.php';

$oop = new oopPHP();

if (!isset($_GET['id']) || !isset($_GET['status'])) {
    header("Location: moderator_dashboard.php");
    exit();
}

$oop->update_report_status($_GET['id'], $_GET['status']);

header("Location: moderator_dashboard.php");
exit();