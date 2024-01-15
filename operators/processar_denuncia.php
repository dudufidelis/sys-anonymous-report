<?php

include 'connection.php';

if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}


$nome = isset($_POST['nome']) ? $_POST['nome'] : null;
$mensagem = $_POST['mensagem'];


$stmt = $conn->prepare("INSERT INTO denuncias (nome, mensagem) VALUES (?, ?)");
$stmt->bind_param("ss", $nome, $mensagem);


if ($stmt->execute()) {
    echo "Denúncia enviada com sucesso!";
} else {
    echo "Erro ao enviar a denúncia: " . $stmt->error;
}


$stmt->close();
$conn->close();
?>
