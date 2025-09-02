<?php

require __DIR__ . '/../config/connection.php';

if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome_usuario = $_POST["nome_usuario"];
    $senha = $_POST["senha"];

    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO usuarios (nome_usuario, senha_hash) VALUES (?, ?)");
    $stmt->bind_param("ss", $nome_usuario, $senha_hash);

    if ($stmt->execute()) {
        header("Location: login.php");
        exit();
    } else {
        echo "Erro ao registrar usuário: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Registro</title>
</head>
<body>
    <div class="container">
        <h1>Registro de Usuário</h1>

        <form action="registro.php" method="post">
            <label for="nome_usuario">Nome de Usuário:</label>
            <input type="text" id="nome_usuario" name="nome_usuario" required>

            <label for="senha">Senha:</label>
            <input type="password" id="senha" name="senha" required>

            <button type="submit">Registrar</button>
        </form>

    </div>
</body>
</html>

