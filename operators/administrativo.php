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
$busca = isset($_GET['q']) ? trim($_GET['q']) : '';
$condicaoData = '';
// Condição de busca
$condicaoBusca = '';

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

// Estatísticas agregadas mínimas (Total, Identificadas, Anônimas)
$sqlStats = "SELECT 
    COUNT(*) AS total,
    SUM(CASE WHEN nome IS NOT NULL AND nome <> '' THEN 1 ELSE 0 END) AS identificadas
    FROM denuncias";
$statsRow = $conn->query($sqlStats)->fetch_assoc();
$total = $statsRow['total'] ?? 0;
$ident = $statsRow['identificadas'] ?? 0;
$anon = $total - $ident;

if ($busca !== '') {
    $safe = $conn->real_escape_string($busca);
    $condicaoBusca = " AND (mensagem LIKE '%$safe%' OR nome LIKE '%$safe%' OR telefone LIKE '%$safe%' OR cpf LIKE '%$safe%')";
}

// Consulta principal com filtros
$queryBase = "FROM denuncias WHERE 1 $condicaoData $condicaoBusca";

$query = "SELECT id, nome, telefone, cpf, mensagem, data_hora $queryBase ORDER BY data_hora DESC LIMIT $denunciasPorPagina OFFSET $offset";
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
        <header class="admin-header">
            <div class="branding">
                <img src="../assets/saine-logo.png" alt="Logo Saine Health Complex">
                <div class="titulo">
                    <h1>Administrativo</h1>
                    <small>Gestão de relatos</small>
                </div>
            </div>
            <a class="btn-sair" href="logout.php">Sair</a>
        </header>

        <div class="navigation-row">
            <form class="filtro" action="administrativo.php" method="GET">
                <div class="campo-inline">
                    <label for="data_filtro">Data</label>
                    <input type="date" id="data_filtro" name="data_filtro" value="<?php echo htmlspecialchars($filtroData); ?>">
                </div>
                <div class="campo-inline">
                    <label for="q">Busca</label>
                    <input type="text" id="q" name="q" placeholder="Palavra-chave" value="<?php echo htmlspecialchars($busca); ?>" maxlength="60">
                </div>
                <button type="submit" class="btn-filtrar">Aplicar</button>
                <a href="administrativo.php" class="btn-reset" title="Limpar filtros">Limpar</a>
            </form>
        </div>

        <div class="stats-cards slim">
            <div class="stat-card focus">
                <div class="stat-top"><h3>Total</h3></div>
                <p class="valor"><?php echo $total; ?></p>
                <small>Denúncias registradas</small>
            </div>
            <div class="stat-card ident">
                <div class="stat-top"><h3>Identificadas</h3></div>
                <p class="valor"><?php echo $ident; ?></p>
                <small>Com dados pessoais</small>
            </div>
            <div class="stat-card anon">
                <div class="stat-top"><h3>Anônimas</h3></div>
                <p class="valor"><?php echo $anon; ?></p>
                <small>Sem identificação</small>
            </div>
        </div>

    <section>
            <?php
                while ($row = $resultado->fetch_assoc()) {
                    $dataFormatada = date('d/m/Y H:i', strtotime($row['data_hora']));
                    $nome = $row['nome'];
                    $exibirNome = ($nome === null || $nome === '') ? 'Anônimo' : htmlspecialchars($nome);
                    $telefone = ($row['telefone'] ?? '') !== '' ? htmlspecialchars($row['telefone']) : null;
                    $cpf = ($row['cpf'] ?? '') !== '' ? htmlspecialchars($row['cpf']) : null;
                    echo "<article class='report-card'>";
                        echo "<header class='rc-head'>";
                            echo "<div class='rc-ident'>";
                                echo "<span class='rc-nome" . ($exibirNome==='Anônimo' ? " anon" : "") . "'>$exibirNome";
                                if ($telefone) { echo " <span class='rc-meta'>Tel: $telefone</span>"; }
                                if ($cpf) { echo " <span class='rc-meta'>CPF: $cpf</span>"; }
                                echo "</span>";
                            echo "</div>";
                            echo "<time class='rc-data'>$dataFormatada</time>";
                        echo "</header>";
                        echo "<div class='rc-body'>" . nl2br(htmlspecialchars($row['mensagem'])) . "</div>";
                    echo "</article>";
                }
            ?>
        </section>
        <?php
            // Paginação inferior centralizada
            $queryTotal = "SELECT COUNT(*) as total $queryBase";
            $resultadoTotal = $conn->query($queryTotal);
            $totalDenuncias = $resultadoTotal->fetch_assoc()['total'] ?? 0;
            $totalPaginas = max(1, ceil($totalDenuncias / $denunciasPorPagina));
            $paginaAtual = max(1, min($paginaAtual, $totalPaginas));
            $first = 1; $last = $totalPaginas;
            $prev = max($first, $paginaAtual - 1);
            $next = min($last, $paginaAtual + 1);
        ?>
        <div class="paginacao pag-bottom">
            <a class="page-btn" href="administrativo.php?pagina=<?php echo $first; ?>&data_filtro=<?php echo urlencode($filtroData); ?>&q=<?php echo urlencode($busca); ?>" title="Primeira">«</a>
            <a class="page-btn" href="administrativo.php?pagina=<?php echo $prev; ?>&data_filtro=<?php echo urlencode($filtroData); ?>&q=<?php echo urlencode($busca); ?>" title="Anterior">‹</a>
            <span class="page-status">Página <?php echo $paginaAtual; ?> / <?php echo $totalPaginas; ?></span>
            <a class="page-btn" href="administrativo.php?pagina=<?php echo $next; ?>&data_filtro=<?php echo urlencode($filtroData); ?>&q=<?php echo urlencode($busca); ?>" title="Próxima">›</a>
            <a class="page-btn" href="administrativo.php?pagina=<?php echo $last; ?>&data_filtro=<?php echo urlencode($filtroData); ?>&q=<?php echo urlencode($busca); ?>" title="Última">»</a>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>