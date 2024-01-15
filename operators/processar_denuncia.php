<?php

include 'connection.php';

$nome = isset($_POST['nome']) ? $_POST['nome'] : null;
$mensagem = $_POST['mensagem'];

$ip = $_SERVER['REMOTE_ADDR'];

$stmt = $conn->prepare("INSERT INTO denuncias (nome, mensagem, ip) VALUES (?, ?, ?)");

if (!$stmt) {
    $erro = "Erro de preparação da consulta: " . $conn->error . "\n";
    error_log($erro, 3, "../logs/errors.log");
    header("Location: ../redirect/error.html");
    exit();
}

$stmt->bind_param("sss", $nome, $mensagem, $ip);

if ($stmt->execute()) {
    header("Location: ../redirect/success.html");
    exit();
} else {
    $erro = "Erro ao enviar a denúncia: " . $stmt->error . "\n";
    error_log($erro, 3, "../logs/errors.log");
    header("Location: ../redirect/error.html");
    exit();
}

?>
