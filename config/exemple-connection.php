<?php
$host = "substitua_pelo_seu_host";
$usuario = "substitua_pelo_seu_usuario";
$senha = "substitua_pela_sua_senha";
$banco = "substitua_pelo_seu_banco";

$conn = new mysqli($host, $usuario, $senha, $banco);

if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

?>
