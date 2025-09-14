<?php
$host = 'localhost';
$usuario = 'root';  // Usuário padrão do XAMPP
$senha = '';        // Senha padrão do XAMPP (vazia)
$banco = 'db_controle_estoque';  // Nome do seu banco de dados

$con = new mysqli($host, $usuario, $senha, $banco);

if ($con->connect_error) {
    die("Falha na conexão: " . $con->connect_error);
}
?>
