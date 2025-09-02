<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require __DIR__ . '/../config/connection.php';

// Se não autenticado, mostra formulário de login embutido
if (!isset($_SESSION['usuario_id'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nome_usuario = $_POST['nome_usuario'] ?? '';
        $senha = $_POST['senha'] ?? '';
        $stmt = $conn->prepare('SELECT id, senha_hash FROM usuarios WHERE nome_usuario = ?');
        $stmt->bind_param('s', $nome_usuario);
        $stmt->execute();
        $stmt->bind_result($id, $senha_hash);
        $stmt->fetch();
        if ($id && password_verify($senha, $senha_hash)) {
            session_regenerate_id(true);
            $_SESSION['usuario_id'] = $id;
            header('Location: index.php');
            exit();
        } else {
            $erro_login = 'Nome de usuário ou senha incorretos';
        }
        $stmt->close();
    }
?>
    <!DOCTYPE html>
    <html lang="pt-br">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1.0">
        <link rel="stylesheet" href="../css/login.css">
        <title>Login</title>
    </head>

    <body>
        <div class="container">
            <header><img src="../assets/logo.png" alt="Logo Saine Health Complex">
                <h2>Área Administrativa</h2>
            </header>
            <form action="index.php" method="post">
                <div class="input"><input placeholder="Usuário" type="text" name="nome_usuario" required></div>
                <div class="input"><input placeholder="Senha" type="password" name="senha" required></div><?php if (!empty($erro_login)): ?><p style="color:red;padding:1rem;"><?php echo $erro_login; ?></p><?php endif; ?><button type="submit">Entrar</button>
            </form>
        </div>
    </body>

    </html><?php
            if (isset($conn) && $conn instanceof mysqli) {
                $conn->close();
            }
            exit();
        }

        // Dashboard (conteúdo de administrativo.php inline)
        $denunciasPorPagina = 5;
        $paginaAtual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        $offset = ($paginaAtual - 1) * $denunciasPorPagina;
        $filtroData = isset($_GET['data_filtro']) ? $_GET['data_filtro'] : '';
        $busca = isset($_GET['q']) ? trim($_GET['q']) : '';
        $condicaoData = '';
        $condicaoBusca = '';
        if ($filtroData !== '' && strtotime($filtroData) !== false) {
            $filtroData = date('Y-m-d', strtotime($filtroData));
            $condicaoData = " AND DATE(data_hora) = '$filtroData'";
        }
        $sqlStats = "SELECT COUNT(*) AS total, SUM(CASE WHEN nome IS NOT NULL AND nome <> '' THEN 1 ELSE 0 END) AS identificadas FROM denuncias";
        $statsRow = $conn->query($sqlStats)->fetch_assoc();
        $total = $statsRow['total'] ?? 0;
        $ident = $statsRow['identificadas'] ?? 0;
        $anon = $total - $ident;
        if ($busca !== '') {
            $safe = $conn->real_escape_string($busca);
            $condicaoBusca = " AND (mensagem LIKE '%$safe%' OR nome LIKE '%$safe%' OR telefone LIKE '%$safe%' OR cpf LIKE '%$safe%')";
        }
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
    <title>Admin | Relatos Saine</title>
    <link rel="shortcut icon" href="../assets/favicon.ico" type="image/x-icon">
</head>

<body>
    <div class="container">
        <header class="admin-header">
            <div class="branding"><img src="../assets/saine-logo.png" alt="Logo Saine Health Complex">
                <div class="titulo">
                    <h1>Administrativo</h1><small>Gestão de relatos</small>
                </div>
            </div><a class="btn-sair" href="logout.php">Sair</a>
        </header>
        <div class="navigation-row">
            <form class="filtro" action="index.php" method="GET">
                <div class="campo-inline"><label for="data_filtro">Data</label><input type="date" id="data_filtro" name="data_filtro" value="<?php echo htmlspecialchars($filtroData); ?>"></div>
                <div class="campo-inline"><label for="q">Busca</label><input type="text" id="q" name="q" placeholder="Palavra-chave" value="<?php echo htmlspecialchars($busca); ?>" maxlength="60"></div><button type="submit" class="btn-filtrar">Aplicar</button><a href="index.php" class="btn-reset" title="Limpar filtros">Limpar</a>
            </form>
        </div>
        <div class="stats-cards slim">
            <div class="stat-card focus">
                <div class="stat-top">
                    <h3>Total</h3>
                </div>
                <p class="valor"><?php echo $total; ?></p><small>Denúncias registradas</small>
            </div>
            <div class="stat-card ident">
                <div class="stat-top">
                    <h3>Identificadas</h3>
                </div>
                <p class="valor"><?php echo $ident; ?></p><small>Com dados pessoais</small>
            </div>
            <div class="stat-card anon">
                <div class="stat-top">
                    <h3>Anônimas</h3>
                </div>
                <p class="valor"><?php echo $anon; ?></p><small>Sem identificação</small>
            </div>
        </div>
        <section><?php $termoBusca = $busca;
                    $regex = null;
                    if ($termoBusca !== '') {
                        $escaped = preg_quote($termoBusca, '/');
                        $regex = '/' . $escaped . '/iu';
                    }
                    while ($row = $resultado->fetch_assoc()) {
                        $dataFormatada = date('d/m/Y H:i', strtotime($row['data_hora']));
                        $nome = $row['nome'];
                        $exibirNome = ($nome === null || $nome === '') ? 'Anônimo' : htmlspecialchars($nome);
                        $telefone = ($row['telefone'] ?? '') !== '' ? htmlspecialchars($row['telefone']) : null;
                        $cpf = ($row['cpf'] ?? '') !== '' ? htmlspecialchars($row['cpf']) : null;
                        $mensagemBruta = $row['mensagem'];
                        $mensagemEscapada = htmlspecialchars($mensagemBruta);
                        if ($regex) {
                            $mensagemEscapada = preg_replace_callback($regex, function ($m) {
                                return '<span class="hl-term">' . htmlspecialchars($m[0]) . '</span>';
                            }, $mensagemEscapada);
                            if ($exibirNome !== 'Anônimo') {
                                $exibirNome = preg_replace_callback($regex, function ($m) {
                                    return '<span class="hl-term">' . htmlspecialchars($m[0]) . '</span>';
                                }, $exibirNome);
                            }
                            if ($telefone) {
                                $telefone = preg_replace_callback($regex, function ($m) {
                                    return '<span class="hl-term">' . htmlspecialchars($m[0]) . '</span>';
                                }, $telefone);
                            }
                            if ($cpf) {
                                $cpf = preg_replace_callback($regex, function ($m) {
                                    return '<span class="hl-term">' . htmlspecialchars($m[0]) . '</span>';
                                }, $cpf);
                            }
                        }
                        echo "<article class='report-card'>";
                        echo "<header class='rc-head'>";
                        echo "<div class='rc-ident'>";
                        echo "<span class='rc-nome" . ($exibirNome === 'Anônimo' ? " anon" : "") . "'>$exibirNome";
                        if ($telefone) {
                            echo " <span class='rc-meta'>Tel: $telefone</span>";
                        }
                        if ($cpf) {
                            echo " <span class='rc-meta'>CPF: $cpf</span>";
                        }
                        echo "</span>";
                        echo "</div>";
                        echo "<time class='rc-data'>$dataFormatada</time>";
                        echo "</header>";
                        echo "<div class='rc-body'>" . nl2br($mensagemEscapada) . "</div>";
                        echo "</article>";
                    } ?></section><?php $queryTotal = "SELECT COUNT(*) as total $queryBase";
                                    $resultadoTotal = $conn->query($queryTotal);
                                    $totalDenuncias = $resultadoTotal->fetch_assoc()['total'] ?? 0;
                                    $totalPaginas = max(1, ceil($totalDenuncias / $denunciasPorPagina));
                                    $paginaAtual = max(1, min($paginaAtual, $totalPaginas));
                                    $first = 1;
                                    $last = $totalPaginas;
                                    $prev = max($first, $paginaAtual - 1);
                                    $next = min($last, $paginaAtual + 1); ?><div class="paginacao pag-bottom"><a class="page-btn" href="index.php?pagina=<?php echo $first; ?>&data_filtro=<?php echo urlencode($filtroData); ?>&q=<?php echo urlencode($busca); ?>" title="Primeira">«</a><a class="page-btn" href="index.php?pagina=<?php echo $prev; ?>&data_filtro=<?php echo urlencode($filtroData); ?>&q=<?php echo urlencode($busca); ?>" title="Anterior">‹</a><span class="page-status">Página <?php echo $paginaAtual; ?> / <?php echo $totalPaginas; ?></span><a class="page-btn" href="index.php?pagina=<?php echo $next; ?>&data_filtro=<?php echo urlencode($filtroData); ?>&q=<?php echo urlencode($busca); ?>" title="Próxima">›</a><a class="page-btn" href="index.php?pagina=<?php echo $last; ?>&data_filtro=<?php echo urlencode($filtroData); ?>&q=<?php echo urlencode($busca); ?>" title="Última">»</a></div>
    </div>
</body>

</html>

<?php if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
?>