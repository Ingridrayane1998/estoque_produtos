<?php
// Verificar se o usuário está logado
if (!isset($_SESSION)) {
    session_start();
}

// Verificar se o usuário é administrador
$isAdmin = false;
if (isset($_SESSION['id'])) {
    require_once 'conect.php';
    $sql = "SELECT c.nm_cargo FROM tb_usuario u 
            INNER JOIN tb_cargo c ON u.id_cargo = c.cd_cargo 
            WHERE u.cd_usuario = ?";
    $stmt = $GLOBALS['con']->prepare($sql);
    $stmt->bind_param('i', $_SESSION['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $cargo = $result->fetch_assoc()['nm_cargo'];
    $isAdmin = ($cargo == 'ADMINISTRADOR');
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Controle de Estoque</title>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top nav-shadow p-3 font-tittle">
        <a class="navbar-brand nav-hover" href="index.php">Controle de Estoque</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNavDropdown">
            <ul class="navbar-nav">
                <?php if (isset($_SESSION['id'])) { ?>
                    <li class="nav-item nav-hover">
                        <a class="navbar-brand" href="index.php">Dashboard</a>
                    </li>
                    <li class="nav-item nav-hover">
                        <a class="navbar-brand" href="produtos.php">Produtos</a>
                    </li>
                    <li class="nav-item nav-hover">
                        <a class="navbar-brand" href="movimentacoes.php">Movimentações</a>
                    </li>
                    <?php if ($isAdmin) { ?>
                    <li class="nav-item nav-hover">
                        <a class="navbar-brand" href="fornecedores.php">Fornecedores</a>
                    </li>
                    <li class="nav-item nav-hover">
                        <a class="navbar-brand" href="usuarios.php">Usuários</a>
                    </li>
                    <?php } ?>
                    <li class="nav-item nav-hover">
                        <a class="navbar-brand" href="logout.php">Sair</a>
                    </li>
                <?php } else { ?>
                    <li class="nav-item nav-hover">
                        <a class="navbar-brand" href="login.php">Entrar</a>
                    </li>
                <?php } ?>
            </ul>
        </div>
    </nav>
</body>
</html>
