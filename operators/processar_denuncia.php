<?php
// Endpoint sempre JSON para envio de denúncias.
header('Content-Type: application/json; charset=utf-8');
include 'connection.php';

function out($ok, $msg, $code = 200, $extra = []) {
    http_response_code($ok ? 200 : $code);
    echo json_encode(array_merge(['success' => (bool)$ok, 'message' => $msg], $extra));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    out(false, 'Método inválido.', 405);
}

$nome = isset($_POST['nome']) ? trim($_POST['nome']) : '';
$mensagem = isset($_POST['mensagem']) ? trim($_POST['mensagem']) : '';
$minLen = 150; $maxLen = 3000;
if ($mensagem === '') {
    out(false, 'O campo relato é obrigatório.', 422);
}
// Comprimento seguro multibyte
$len = function_exists('mb_strlen') ? mb_strlen($mensagem, 'UTF-8') : strlen($mensagem);
if ($len < $minLen) {
    out(false, 'Relato muito curto. Mínimo de ' . $minLen . ' caracteres.', 422);
}
if ($len > $maxLen) {
    out(false, 'Relato muito longo. Máximo de ' . $maxLen . ' caracteres.', 422);
}

$desejaIdentificar = isset($_POST['deseja_identificar']);
$telefone = null; $cpf = null;
if ($desejaIdentificar) {
    if ($nome === '') out(false, 'Nome obrigatório.', 422);
    $telefoneDigits = preg_replace('/[^0-9]/', '', $_POST['telefone'] ?? '');
    if (!preg_match('/^\d{10,11}$/', $telefoneDigits) || preg_match('/^(\d)\1+$/', $telefoneDigits)) {
        out(false, 'Telefone inválido.', 422);
    }
    $telefone = $telefoneDigits;
    $cpfDigits = preg_replace('/[^0-9]/', '', $_POST['cpf'] ?? '');
    if (!preg_match('/^\d{11}$/', $cpfDigits) || preg_match('/^(\d)\1+$/', $cpfDigits)) {
        out(false, 'CPF inválido.', 422);
    }
    $cpf = $cpfDigits;
} else {
    $nome = null;
}

$ip = $_SERVER['REMOTE_ADDR'] ?? null;

$stmt = $conn->prepare('INSERT INTO denuncias (nome, telefone, cpf, mensagem, ip) VALUES (?, ?, ?, ?, ?)');
if (!$stmt) {
    error_log('Erro prepare: ' . $conn->error . "\n", 3, '../logs/errors.log');
    out(false, 'Falha interna (prep).', 500);
}
if (!$stmt->bind_param('sssss', $nome, $telefone, $cpf, $mensagem, $ip)) {
    error_log('Erro bind: ' . $stmt->error . "\n", 3, '../logs/errors.log');
    out(false, 'Falha interna (bind).', 500);
}
if (!$stmt->execute()) {
    error_log('Erro exec: ' . $stmt->error . "\n", 3, '../logs/errors.log');
    out(false, 'Erro ao salvar. Tente novamente.', 500);
}
out(true, 'Relato enviado com sucesso.');