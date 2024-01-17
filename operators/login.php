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
    <link rel="stylesheet" href="../css/login.css">
    <title>Login</title>
</head>
<body>
    <div class="container">
        <header>
            <img src="../assets/logo.png" alt="Logo Saine Health Complex">
            <h2>Area Administrativa</h2>
        </header>

        <form action="login.php" method="post">
            <div class="input">
                <input placeholder="Usuario" type="text" id="nome_usuario" name="nome_usuario" required>
            </div>
            <div class="input">
                <input placeholder="Senha" type="password" id="senha" name="senha" required>
            </div>
            
        <?php if (isset($erro_login)): ?>
            <p style="color: red; padding: 1rem;"><?php echo $erro_login; ?></p>
        <?php endif; ?>

            <button type="submit">Entrar</button>
        </form>
    </div>
</body>
</html>

