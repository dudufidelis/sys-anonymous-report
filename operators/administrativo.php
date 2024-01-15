<?php
session_start();

include 'connection.php';


$resultado = $conn->query("SELECT * FROM denuncias");

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Área Administrativa</title>
</head>
<body>
    <div class="container">
        <h1>Área Administrativa</h1>
        <h2>Denúncias:</h2>

        <?php
        // Exibir denúncias
        while ($row = $resultado->fetch_assoc()) {
            echo "<p><strong>Data/Hora:</strong> {$row['data_hora']}<br>";
            echo "<strong>Nome:</strong> {$row['nome']}<br>";
            echo "<strong>Denúncia:</strong> {$row['mensagem']}</p>";
            echo "<hr>";
        }
        ?>

        <p><a href="logout.php">Sair</a></p>
    </div>
</body>
</html>

<?php
$conn->close();
?>
