<?php

include 'connection.php';

$nome = isset($_POST['nome']) ? $_POST['nome'] : null;
$mensagem = $_POST['mensagem'];


$stmt = $conn->prepare("INSERT INTO denunciass (nome, mensagem) VALUES (?, ?)");

if (!$stmt) {
    $erro = "Erro de preparação da consulta: " . $conn->error . "\n";
    error_log($erro, 3, "../logs/errors.log");
    header("Location: error.html");
    exit();
}

$stmt->bind_param("ss", $nome, $mensagem);




if ($stmt->execute()) {
    header("Location: ../success.html");
    exit();
} else {
    $erro = "Erro ao enviar a denúncia: " . $stmt->error . "\n";
    error_log($erro, 3, "../logs/errors.log");
    header("Location: error.html");
    exit();
}

?>
