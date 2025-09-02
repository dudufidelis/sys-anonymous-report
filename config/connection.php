<?php
// Conexão única do sistema (admin + operators)
$host    = getenv('DB_HOST') ?: 'localhost';
$usuario = getenv('DB_USER') ?: 'root';
$senha   = getenv('DB_PASS') ?: '';
$banco   = getenv('DB_NAME') ?: 'reports';
$logPath = __DIR__ . '/../logs/errors.log';

// Evita recriar conexão se já existir num include repetido
if (!isset($conn) || !($conn instanceof mysqli)) {
    $conn = @new mysqli($host, $usuario, $senha);
    if ($conn->connect_error) {
        @error_log('Erro conexão inicial: ' . $conn->connect_error . "\n", 3, $logPath);
        die('Falha conexão.');
    }
    $conn->query("CREATE DATABASE IF NOT EXISTS `" . $conn->real_escape_string($banco) . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    if (!$conn->select_db($banco)) {
        @error_log('Falha selecionar banco: ' . $conn->error . "\n", 3, $logPath);
        die('BD indisponível.');
    }
    $conn->set_charset('utf8mb4');
}
