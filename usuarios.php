<?php
require_once 'header.php';

// Verificar se o usuário está logado
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

require_once 'function.php';

// Verificar se o usuário é administrador
$sql = "SELECT c.nm_cargo FROM tb_usuario u 
        INNER JOIN tb_cargo c ON u.id_cargo = c.cd_cargo 
        WHERE u.cd_usuario = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param('i', $_SESSION['id']);
$stmt->execute();
$result = $stmt->get_result();
$cargo = $result->fetch_assoc()['nm_cargo'];

if ($cargo != 'ADMINISTRADOR') {
    // Redirecionar para a página inicial com mensagem de erro
    header("Location: index.php?erro=permissao");
    exit;
}

// Processar exclusão de usuário
if (isset($_GET['desativar']) && !empty($_GET['desativar'])) {
    $id_usuario = intval($_GET['desativar']);
    
    // Não permitir desativar o próprio usuário
    if ($id_usuario != $_SESSION['id']) {
        $sql = "UPDATE tb_usuario SET st_usuario = '0' WHERE cd_usuario = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param('i', $id_usuario);
        
        if ($stmt->execute()) {
            $mensagem = "Usuário desativado com sucesso!";
            $tipoMensagem = "success";
        } else {
            $mensagem = "Erro ao desativar usuário.";
            $tipoMensagem = "danger";
        }
    } else {
        $mensagem = "Você não pode desativar seu próprio usuário.";
        $tipoMensagem = "warning";
    }
}

// Processar ativação de usuário
if (isset($_GET['ativar']) && !empty($_GET['ativar'])) {
    $id_usuario = intval($_GET['ativar']);
    
    $sql = "UPDATE tb_usuario SET st_usuario = '1' WHERE cd_usuario = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param('i', $id_usuario);
    
    if ($stmt->execute()) {
        $mensagem = "Usuário ativado com sucesso!";
        $tipoMensagem = "success";
    } else {
        $mensagem = "Erro ao ativar usuário.";
        $tipoMensagem = "danger";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuários - Controle de Estoque</title>
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
    <?php require_once 'nav.php' ?>
    
    <div class="container mt-5 pt-5">
        <div class="row mb-4">
            <div class="col-md-8">
                <h2>Gerenciamento de Usuários</h2>
            </div>
            <div class="col-md-4 text-right">
                <a href="adicionar_usuario.php" class="btn btn-success">
                    <i class="bi bi-plus-circle"></i> Adicionar Usuário
                </a>
            </div>
        </div>
        
        <?php if (isset($mensagem)): ?>
        <div class="alert alert-<?php echo $tipoMensagem; ?> alert-dismissible fade show" role="alert">
            <?php echo $mensagem; ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <?php endif; ?>
        
        <!-- Tabela de Usuários -->
        <div class="card">
            <div class="card-header">
                <h5>Lista de Usuários</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Login</th>
                                <th>Cargo</th>
                                <th>Data de Registro</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Modificar a consulta para incluir o status do usuário
                            $sql = "SELECT 
                                    u.cd_usuario,
                                    u.nm_usuario,
                                    u.nm_email,
                                    c.nm_cargo,
                                    u.dt_registro,
                                    u.st_usuario
                                FROM 
                                    tb_usuario u
                                INNER JOIN 
                                    tb_cargo c ON u.id_cargo = c.cd_cargo
                                ORDER BY 
                                    u.nm_usuario";
                            
                            $result = $con->query($sql);
                            
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . $row['cd_usuario'] . "</td>";
                                    echo "<td>" . $row['nm_usuario'] . "</td>";
                                    echo "<td>" . $row['nm_email'] . "</td>";
                                    echo "<td>" . $row['nm_cargo'] . "</td>";
                                    echo "<td>" . date('d/m/Y', strtotime($row['dt_registro'])) . "</td>";
                                    echo "<td>" . ($row['st_usuario'] == '1' ? 
                                            '<span class="badge badge-success">Ativo</span>' : 
                                            '<span class="badge badge-danger">Inativo</span>') . "</td>";
                                    echo "<td>";
                                    
                                    // Não mostrar opções de editar/desativar para o próprio usuário
                                    if ($row['cd_usuario'] != $_SESSION['id']) {
                                        echo '<a href="editar_usuario.php?id=' . $row['cd_usuario'] . '" class="btn btn-sm btn-warning mr-1"><i class="bi bi-pencil"></i></a>';
                                        
                                        if ($row['st_usuario'] == '1') {
                                            echo '<a href="usuarios.php?desativar=' . $row['cd_usuario'] . '" class="btn btn-sm btn-danger mr-1" onclick="return confirm(\'Tem certeza que deseja desativar este usuário?\')"><i class="bi bi-trash"></i></a>';
                                        } else {
                                            echo '<a href="usuarios.php?ativar=' . $row['cd_usuario'] . '" class="btn btn-sm btn-success mr-1" onclick="return confirm(\'Tem certeza que deseja ativar este usuário?\')"><i class="bi bi-check-circle"></i></a>';
                                        }
                                    } else {
                                        echo '<span class="text-muted">Usuário atual</span>';
                                    }
                                    
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='7' class='text-center'>Nenhum usuário cadastrado</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <?php require_once 'footer.php'; ?>
</body>
</html>
