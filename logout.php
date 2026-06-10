<?php
require_once 'includes/auth.php';
session_destroy();
header('Location: ' . SITE_URL . '/login.php');
exit;
