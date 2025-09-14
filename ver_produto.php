<?php
require_once 'header.php';

// Verificar se o usuário está logado
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

require_once 'function.php';

// Verificar se o ID do produto foi fornecido
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: produtos.php");
    exit;
}

$id_produto = intval($_GET['id']);
$produto = DetalhesProduto($id_produto);

// Se o produto não existir, redirecionar
if (!$produto) {
    header("Location: produtos.php");
    exit;
}

// Buscar as últimas movimentações deste produto
$sql = "SELECT 
            m.tp_movimentacao, 
            m.qt_movimentacao, 
            m.dt_movimentacao, 
            u.nm_usuario,
            m.ds_observacao
        FROM 
            tb_movimentacao m
        INNER JOIN 
            tb_usuario u ON m.id_usuario = u.cd_usuario
        WHERE 
            m.id_produto = ?
        ORDER BY 
            m.dt_movimentacao DESC
        LIMIT 10";

$stmt = $con->prepare($sql);
$stmt->bind_param('i', $id_produto);
$stmt->execute();
$movimentacoes = $stmt->get_result();

// Verificar o cargo do usuário
$sql = "SELECT c.nm_cargo FROM tb_usuario u 
        INNER JOIN tb_cargo c ON u.id_cargo = c.cd_cargo 
        WHERE u.cd_usuario = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param('i', $_SESSION['id']);
$stmt->execute();
$result = $stmt->get_result();
$cargo = $result->fetch_assoc()['nm_cargo'];
$isAdmin = ($cargo == 'ADMINISTRADOR');
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $produto['nm_produto']; ?> - Controle de Estoque</title>
    <link rel="stylesheet" href="./css/style.css">
    <style>
        .produto-imagem {
            max-height: 400px;
            object-fit: contain;
        }
        .badge-estoque {
            font-size: 1rem;
        }
    </style>
</head>
<body>
    <?php require_once 'nav.php' ?>
    
    <div class="container mt-5 pt-5">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="produtos.php">Produtos</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo $produto['nm_produto']; ?></li>
            </ol>
        </nav>
        
        <div class="row">
            <div class="col-md-5">
                <?php
                // Verificar se a imagem existe, caso contrário usar uma imagem padrão
                $imagem = !empty($produto['url_imagem_produto']) && file_exists('img/produtos/' . $produto['url_imagem_produto']) 
                          ? 'img/produtos/' . $produto['url_imagem_produto'] 
                          : 'img/produto_default.jpg';
                ?>
                <img src="<?php echo $imagem; ?>" class="img-fluid produto-imagem" alt="<?php echo $produto['nm_produto']; ?>">
            </div>
            
            <div class="col-md-7">
                <h1><?php echo $produto['nm_produto']; ?></h1>
                <p class="lead"><?php echo $produto['nm_categoria']; ?></p>
                
                <div class="my-4">
                    <h3>R$ <?php echo number_format($produto['vl_produto'], 2, ',', '.'); ?></h3>
                    
                    <?php
                    // Definir classe de badge baseada no estoque
                    $badgeClass = 'badge-success';
                    $badgeText = 'Em estoque';
                    
                    if ($produto['qt_estoque'] <= 0) {
                        $badgeClass = 'badge-danger';
                        $badgeText = 'Sem estoque';
                    } elseif ($produto['qt_estoque'] < 10) {
                        $badgeClass = 'badge-warning';
                        $badgeText = 'Baixo estoque';
                    }
                    ?>
                    
                    <span class="badge badge-<?php echo $badgeClass; ?> badge-estoque">
                        <?php echo $badgeText; ?> - <?php echo $produto['qt_estoque']; ?> unidades
                    </span>
                </div>
                
                <div class="my-4">
                    <h5>Descrição</h5>
                    <p><?php echo nl2br($produto['ds_produto']); ?></p>
                </div>
                
                <div class="my-4">
                    <h5>Informações Adicionais</h5>
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Status
                            <span class="badge badge-<?php echo $produto['st_produto'] == '1' ? 'success' : 'secondary'; ?> badge-pill">
                                <?php echo $produto['st_produto'] == '1' ? 'Ativo' : 'Inativo'; ?>
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Validade
                            <span>
                                <?php 
                                if (!empty($produto['dt_validade'])) {
                                    $validade = new DateTime($produto['dt_validade']);
                                    $hoje = new DateTime();
                                    
                                    if ($validade < $hoje) {
                                        echo '<span class="text-danger">Vencido - ' . date('d/m/Y', strtotime($produto['dt_validade'])) . '</span>';
                                    } else {
                                        echo date('d/m/Y', strtotime($produto['dt_validade']));
                                    }
                                } else {
                                    echo 'Não informada';
                                }
                                ?>
                            </span>
                        </li>
                    </ul>
                </div>
                
                <div class="my-4">
                    <?php if ($isAdmin): ?>
                    <a href="editar_produto.php?id=<?php echo $produto['cd_produto']; ?>" class="btn btn-warning">
                        <i class="bi bi-pencil"></i> Editar Produto
                    </a>
                    <?php endif; ?>
                    <a href="movimentar_produto.php?id=<?php echo $produto['cd_produto']; ?>" class="btn btn-success">
                        <i class="bi bi-arrow-left-right"></i> Movimentar Estoque
                    </a>
                    <a href="produtos.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Voltar
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Histórico de movimentações -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Últimas Movimentações</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Data/Hora</th>
                                        <th>Tipo</th>
                                        <th>Quantidade</th>
                                        <th>Usuário</th>
                                        <th>Observação</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($movimentacoes->num_rows > 0) {
                                        while ($mov = $movimentacoes->fetch_assoc()) {
                                            echo "<tr>";
                                            echo "<td>" . date('d/m/Y H:i', strtotime($mov['dt_movimentacao'])) . "</td>";
                                            echo "<td>" . ($mov['tp_movimentacao'] == 'entrada' ? 
                                                    '<span class="badge badge-success">Entrada</span>' : 
                                                    '<span class="badge badge-danger">Saída</span>') . "</td>";
                                            echo "<td>" . $mov['qt_movimentacao'] . "</td>";
                                            echo "<td>" . $mov['nm_usuario'] . "</td>";
                                            echo "<td>" . $mov['ds_observacao'] . "</td>";
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
    </div>
    
    <?php require_once 'footer.php'; ?>
</body>
</html>
