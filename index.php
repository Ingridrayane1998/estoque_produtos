<?php
require_once 'header.php';

// Verificar se o usuário está logado
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Controle de Estoque</title>
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
    <?php require_once 'nav.php' ?>
    
    <!-- Mensagem de boas-vindas -->
    <?php if (isset($_SESSION['mostrar_boas_vindas']) && $_SESSION['mostrar_boas_vindas']): ?>
<div class="container mt-5 pt-2">
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>Seja bem-vindo, <?php echo $_SESSION['usuario']; ?>!</strong> Você está conectado ao sistema de Controle de Estoque.
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
</div>
<?php 
// Resetar a flag para não mostrar novamente
$_SESSION['mostrar_boas_vindas'] = false;
endif; 
?>

    
    <div class="container mt-3">
        <div class="jumbotron">
            <h1 class="display-4">Sistema de Controle de Estoque</h1>
            <p class="lead">Gerencie seu estoque de doces, bebidas, salgadinhos e outros produtos de forma eficiente.</p>
            <hr class="my-4">
            <p>Utilize o menu para navegar entre as diferentes funcionalidades do sistema.</p>
            <a class="btn btn-primary btn-lg" href="produtos.php" role="button">Ver Produtos</a>
        </div>
        
        <!-- Cards com resumo -->
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-header">Total de Produtos</div>
                    <div class="card-body">
                        <h5 class="card-title">
                            <?php
                            $sql = "SELECT COUNT(*) as total FROM tb_produto";
                            $result = $con->query($sql);
                            $row = $result->fetch_assoc();
                            echo $row['total'] ? $row['total'] : 0;
                            ?>
                        </h5>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card text-white bg-success mb-3">
                    <div class="card-header">Produtos em Estoque</div>
                    <div class="card-body">
                        <h5 class="card-title">
                            <?php
                            $sql = "SELECT SUM(qt_estoque) as total FROM tb_produto";
                            $result = $con->query($sql);
                            $row = $result->fetch_assoc();
                            echo $row['total'] ? $row['total'] : 0;
                            ?>
                        </h5>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card text-white bg-warning mb-3">
                    <div class="card-header">Produtos com Baixo Estoque</div>
                    <div class="card-body">
                        <h5 class="card-title">
                            <?php
                            $sql = "SELECT COUNT(*) as total FROM tb_produto WHERE qt_estoque < 10";
                            $result = $con->query($sql);
                            $row = $result->fetch_assoc();
                            echo $row['total'] ? $row['total'] : 0;
                            ?>
                        </h5>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card text-white bg-danger mb-3">
                    <div class="card-header">Produtos Vencidos</div>
                    <div class="card-body">
                        <h5 class="card-title">
                            <?php
                            $sql = "SELECT COUNT(*) as total FROM tb_produto WHERE dt_validade < CURDATE()";
                            $result = $con->query($sql);
                            $row = $result->fetch_assoc();
                            echo $row['total'] ? $row['total'] : 0;
                            ?>
                        </h5>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Últimas movimentações -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Últimas Movimentações</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th>Tipo</th>
                                    <th>Quantidade</th>
                                    <th>Data</th>
                                    <th>Usuário</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT 
                                            p.nm_produto, 
                                            m.tp_movimentacao, 
                                            m.qt_movimentacao, 
                                            m.dt_movimentacao, 
                                            u.nm_usuario
                                        FROM 
                                            tb_movimentacao m
                                        INNER JOIN 
                                            tb_produto p ON m.id_produto = p.cd_produto
                                        INNER JOIN 
                                            tb_usuario u ON m.id_usuario = u.cd_usuario
                                        ORDER BY 
                                            m.dt_movimentacao DESC
                                        LIMIT 5";
                                $result = $con->query($sql);
                                
                                if ($result && $result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . $row['nm_produto'] . "</td>";
                                        echo "<td>" . ($row['tp_movimentacao'] == 'entrada' ? 
                                                '<span class="badge badge-success">Entrada</span>' : 
                                                '<span class="badge badge-danger">Saída</span>') . "</td>";
                                        echo "<td>" . $row['qt_movimentacao'] . "</td>";
                                        echo "<td>" . date('d/m/Y H:i', strtotime($row['dt_movimentacao'])) . "</td>";
                                        echo "<td>" . $row['nm_usuario'] . "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='5' class='text-center'>Nenhuma movimentação registrada</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php require_once 'footer.php'; ?>
</body>
</html>
