<?php
require_once dirname(__DIR__) . '/config/init.php';
session_destroy();
header('Location: ' . BASE_URL . '/auth/login.php');
exit;
