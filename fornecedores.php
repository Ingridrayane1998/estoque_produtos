<?php
require_once 'header.php';

// Verificar se o usuário está logado
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

require_once 'function.php';

// Processar exclusão de fornecedor
if (isset($_GET['excluir']) && !empty($_GET['excluir'])) {
    $id_fornecedor = intval($_GET['excluir']);
    
    $sql = "UPDATE tb_fornecedor SET st_fornecedor = '0' WHERE cd_fornecedor = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param('i', $id_fornecedor);
    
    if ($stmt->execute()) {
        $mensagem = "Fornecedor desativado com sucesso!";
        $tipoMensagem = "success";
    } else {
        $mensagem = "Erro ao desativar fornecedor.";
        $tipoMensagem = "danger";
    }
}

// Processar ativação de fornecedor
if (isset($_GET['ativar']) && !empty($_GET['ativar'])) {
    $id_fornecedor = intval($_GET['ativar']);
    
    $sql = "UPDATE tb_fornecedor SET st_fornecedor = '1' WHERE cd_fornecedor = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param('i', $id_fornecedor);
    
    if ($stmt->execute()) {
        $mensagem = "Fornecedor ativado com sucesso!";
        $tipoMensagem = "success";
    } else {
        $mensagem = "Erro ao ativar fornecedor.";
        $tipoMensagem = "danger";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fornecedores - Controle de Estoque</title>
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
    <?php require_once 'nav.php' ?>
    
    <div class="container mt-5 pt-5">
        <div class="row mb-4">
            <div class="col-md-8">
                <h2>Gerenciamento de Fornecedores</h2>
            </div>
            <div class="col-md-4 text-right">
                <a href="adicionar_fornecedor.php" class="btn btn-success">
                    <i class="bi bi-plus-circle"></i> Adicionar Fornecedor
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
        
        <!-- Tabela de Fornecedores -->
        <div class="card">
            <div class="card-header">
                <h5>Lista de Fornecedores</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>Avaliação</th>
                                <th>Endereço</th>
                                <th>Telefone</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $fornecedores = ListarFornecedores();
                            if ($fornecedores && $fornecedores->num_rows > 0) {
                                while ($fornecedor = $fornecedores->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . $fornecedor['cd_fornecedor'] . "</td>";
                                    echo "<td>" . $fornecedor['nm_fornecedor'] . "</td>";
                                    echo "<td>";
                                    
                                    // Exibir estrelas para avaliação
                                    for ($i = 1; $i <= 5; $i++) {
                                        if ($i <= $fornecedor['nr_avaliacao']) {
                                            echo '<i class="bi bi-star-fill text-warning"></i>';
                                        } else {
                                            echo '<i class="bi bi-star text-secondary"></i>';
                                        }
                                    }
                                    
                                    echo "</td>";
                                    echo "<td>" . $fornecedor['ds_endereco'] . "</td>";
                                    echo "<td>" . $fornecedor['nr_telefone'] . "</td>";
                                    echo "<td>" . $fornecedor['ds_email'] . "</td>";
                                    echo "<td>" . ($fornecedor['st_fornecedor'] == '1' ? 
                                            '<span class="badge badge-success">Ativo</span>' : 
                                            '<span class="badge badge-danger">Inativo</span>') . "</td>";
                                    echo "<td>";
                                    echo '<a href="editar_fornecedor.php?id=' . $fornecedor['cd_fornecedor'] . '" class="btn btn-sm btn-warning mr-1"><i class="bi bi-pencil"></i></a>';
                                    
                                    if ($fornecedor['st_fornecedor'] == '1') {
                                        echo '<a href="fornecedores.php?excluir=' . $fornecedor['cd_fornecedor'] . '" class="btn btn-sm btn-danger mr-1" onclick="return confirm(\'Tem certeza que deseja desativar este fornecedor?\')"><i class="bi bi-trash"></i></a>';
                                    } else {
                                        echo '<a href="fornecedores.php?ativar=' . $fornecedor['cd_fornecedor'] . '" class="btn btn-sm btn-success mr-1" onclick="return confirm(\'Tem certeza que deseja ativar este fornecedor?\')"><i class="bi bi-check-circle"></i></a>';
                                    }
                                    
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='8' class='text-center'>Nenhum fornecedor cadastrado</td></tr>";
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
