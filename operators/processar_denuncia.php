<?php

include 'connection.php';

$nome = isset($_POST['nome']) ? trim($_POST['nome']) : '';
$mensagem = trim($_POST['mensagem']);

// Controle de identificação
$desejaIdentificar = isset($_POST['deseja_identificar']);
$telefone = null;
$cpf = null;
if ($desejaIdentificar) {
    // Nome obrigatório
    if ($nome === '') {
        header("Location: ../redirect/error.html");
        exit();
    }
    // Telefone: precisa conter 10 ou 11 dígitos
    $telefoneDigits = preg_replace('/[^0-9]/', '', $_POST['telefone'] ?? '');
    if (!preg_match('/^\d{10,11}$/', $telefoneDigits) || preg_match('/^(\d)\1+$/', $telefoneDigits)) {
        header("Location: ../redirect/error.html");
        exit();
    }
    $telefone = $telefoneDigits;
    // CPF: exatamente 11 dígitos
    $cpfDigits = preg_replace('/[^0-9]/', '', $_POST['cpf'] ?? '');
    if (!preg_match('/^\d{11}$/', $cpfDigits) || preg_match('/^(\d)\1+$/', $cpfDigits)) {
        header("Location: ../redirect/error.html");
        exit();
    }
    $cpf = $cpfDigits;
} else {
    // Usuário optou por permanecer anônimo
    $nome = null;
}

$ip = $_SERVER['REMOTE_ADDR'];

$stmt = $conn->prepare("INSERT INTO denuncias (nome, telefone, cpf, mensagem, ip) VALUES (?, ?, ?, ?, ?)");

if (!$stmt) {
    $erro = "Erro de preparação da consulta: " . $conn->error . "\n";
    error_log($erro, 3, "../logs/errors.log");
    header("Location: ../redirect/error.html");
    exit();
}

$stmt->bind_param("sssss", $nome, $telefone, $cpf, $mensagem, $ip);

if ($stmt->execute()) {
    header("Location: ../redirect/success.html");
    exit();
} else {
    $erro = "Erro ao enviar a denúncia: " . $stmt->error . "\n";
    error_log($erro, 3, "../logs/errors.log");
    header("Location: ../redirect/error.html");
    exit();
}