<?php
session_start();

include 'connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome_usuario = $_POST["nome_usuario"];
    $senha = $_POST["senha"];

    $stmt = $conn->prepare("SELECT id, senha_hash FROM usuarios WHERE nome_usuario = ?");
    $stmt->bind_param("s", $nome_usuario);
    $stmt->execute();
    $stmt->bind_result($id, $senha_hash);
    $stmt->fetch();

    if (password_verify($senha, $senha_hash)) {
        // Login bem-sucedido
        $_SESSION["usuario_id"] = $id;
        header("Location: administrativo.php");
        exit();
    } else {
        $erro_login = "Nome de usuário ou senha incorretos";
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
    <title>Login</title>
</head>
<body>
    <div class="container">
        <h1>Login</h1>
        
        <?php if (isset($erro_login)): ?>
            <p style="color: red;"><?php echo $erro_login; ?></p>
        <?php endif; ?>

        <form action="login.php" method="post">
            <label for="nome_usuario">Nome de Usuário:</label>
            <input type="text" id="nome_usuario" name="nome_usuario" required>

            <label for="senha">Senha:</label>
            <input type="password" id="senha" name="senha" required>

            <button type="submit">Entrar</button>
        </form>
    </div>
</body>
</html>

