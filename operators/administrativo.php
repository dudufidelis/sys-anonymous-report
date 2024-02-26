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

// Processar a data de filtro, se fornecida
$filtroData = isset($_GET['data_filtro']) ? $_GET['data_filtro'] : '';
$condicaoData = '';

if ($filtroData !== '') {
    // Validar a data de filtro (você pode precisar ajustar isso dependendo do formato da data)
    if (strtotime($filtroData) !== false) {
        $filtroData = date('Y-m-d', strtotime($filtroData));
        $condicaoData = " AND DATE(data_hora) = '$filtroData'";
    } else {
        echo "Data de filtro inválida.";
        exit();
    }
}

// Consulta para obter as denúncias paginadas (ordenadas pela data_hora de forma decrescente)
$query = "SELECT * FROM denuncias WHERE 1 $condicaoData ORDER BY data_hora DESC LIMIT $denunciasPorPagina OFFSET $offset";
$resultado = $conn->query($query);

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/admin-area.css">
    <link rel="shortcut icon" href="../assets/favicon.ico" type="image/x-icon">
    <title>Área Administrativa</title>
</head>

<body>
    <div class="container">
        <header>
            <img src="../assets/saine-logo.png" alt="Logo Saine Health Complex">
            <h1>Área Administrativa</h1>
            <a href="logout.php">Sair</a>
        </header>

        <div class="navigation-row">
            <form action="administrativo.php" method="GET">
                <label for="data_filtro">Data:</label>
                <input type="date" id="data_filtro" name="data_filtro" value="<?php echo $filtroData; ?>">
                <button type="submit">Filtrar</button>
            </form>
    
            <div class="navigation">
                <?php
                $queryTotal = "SELECT COUNT(*) as total FROM denuncias WHERE 1 $condicaoData";
                $resultadoTotal = $conn->query($queryTotal);
                $totalDenuncias = $resultadoTotal->fetch_assoc()['total'];
                $totalPaginas = ceil($totalDenuncias / $denunciasPorPagina);
    
                $paginaAnterior = max(1, $paginaAtual - 1);
                echo "<a href='administrativo.php?pagina=$paginaAnterior&data_filtro=$filtroData'>Anterior</a> ";
                echo "-";
                $paginaProxima = min($totalPaginas, $paginaAtual + 1);
                echo " <a href='administrativo.php?pagina=$paginaProxima&data_filtro=$filtroData'>Próxima</a>";
                ?>
            </div>
        </div>

        <section>
            <?php
                while ($row = $resultado->fetch_assoc()) {
                    $dataFormatada = date('d/m/Y', strtotime($row['data_hora']));
                    echo "<div class='reports'>";
                    echo "<div class='report-info'>";
                    echo "<p>Nome: {$row['nome']}</p>";
                    echo "<p>Data: $dataFormatada</p>";
                    echo "</div>";
                    echo "<div class='bar'></div>";
                    echo "<div class='report'>{$row['mensagem']}</div>";
                    echo "</div>";
                }
            ?>
        </section>
    </div>
</body>
</html>

<?php
$conn->close();
?>