<?php
require_once 'config.php';
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== 1) {
    header('Location: index.php');
    exit;
}

?>