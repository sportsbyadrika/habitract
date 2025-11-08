<?php
require_once __DIR__ . '/../includes/functions.php';

start_session();
unset($_SESSION['user']);
session_destroy();

header('Location: /index.php');
exit;
