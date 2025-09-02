<?php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
require __DIR__ . '/../config/connection.php'; // caso use logs futuros
session_destroy();
header('Location: login.php');
exit();