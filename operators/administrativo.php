<?php
session_start();

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit();
}

include 'connection.php';

// Definir a quantidade de denúncias a serem exibidas por página
$denunciasPorPagina = 5;

// Determinar a página atual (padrão é 1 se não estiver definida)
$paginaAtual = isset($_GET['pagina']) ? $_GET['pagina'] : 1;

// Calcular o offset para a consulta SQL
$offset = ($paginaAtual - 1) * $denunciasPorPagina;

// Consulta para obter as denúncias paginadas (ordenadas pela data_hora de forma decrescente)
$query = "SELECT * FROM denuncias ORDER BY data_hora DESC LIMIT $denunciasPorPagina OFFSET $offset";
$resultado = $conn->query($query);

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <link rel="shortcut icon" href="../assets/favicon.ico" type="image/x-icon">
    <title>Área Administrativa</title>
</head>
<body>
    <div class="container">
        <h1>Área Administrativa</h1>
        <h2>Denúncias:</h2>

        <?php
        // Exibir denúncias com IP
        while ($row = $resultado->fetch_assoc()) {
            // Formatando a data no formato dd/mm/yyyy
            $dataFormatada = date('d/m/Y', strtotime($row['data_hora']));

            echo "<p><strong>Data/Hora:</strong> $dataFormatada<br>";
            echo "<strong>Nome:</strong> {$row['nome']}<br>";
            echo "<strong>Denúncia:</strong> {$row['mensagem']}<br>";
            echo "<strong>IP:</strong> {$row['ip']}</p>";
            echo "<hr>";
        }
        ?>

        <div class="paginacao">
            <?php
            // Adicionar controles de navegação
            $queryTotal = "SELECT COUNT(*) as total FROM denuncias";
            $resultadoTotal = $conn->query($queryTotal);
            $totalDenuncias = $resultadoTotal->fetch_assoc()['total'];
            $totalPaginas = ceil($totalDenuncias / $denunciasPorPagina);

            // Definir o número máximo de links exibidos
            $maxLinks = 5;

            // Calcular o intervalo de páginas a serem exibidas
            $intervalo = floor($maxLinks / 5);

            // Calcular as páginas inicial e final a serem exibidas
            $paginaInicial = max(1, $paginaAtual - $intervalo);
            $paginaFinal = min($totalPaginas, $paginaInicial + $maxLinks - 1);

            // Exibir botão "Anterior"
            $paginaAnterior = max(1, $paginaAtual - 1);
            echo "<a href='administrativo.php?pagina=$paginaAnterior'>&lt; Anterior</a> ";

            // Exibir links para páginas dentro do intervalo calculado
            for ($i = $paginaInicial; $i <= $paginaFinal; $i++) {
                echo "<a href='administrativo.php?pagina=$i'>$i</a>";
            }

            // Exibir botão "Próxima"
            $paginaProxima = min($totalPaginas, $paginaAtual + 1);
            echo " <a href='administrativo.php?pagina=$paginaProxima'>Próxima &gt;</a>";

            ?>
        </div>
    </div>
    <a href="logout.php">Sair</a>
</body>
</html>

<?php
$conn->close();
?>
