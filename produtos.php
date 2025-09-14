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
$isAdmin = ($cargo == 'ADMINISTRADOR');

// Verificar se foi solicitada uma categoria específica
$categoria = isset($_GET['categoria']) ? intval($_GET['categoria']) : null;

// Verificar se há mensagem de erro
$erro = isset($_GET['erro']) ? $_GET['erro'] : '';
if ($erro == 'permissao') {
    $mensagem = "Você não tem permissão para acessar essa funcionalidade.";
    $tipoMensagem = "danger";
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produtos - Controle de Estoque</title>
    <link rel="stylesheet" href="./css/style.css">
    <style>
        .produto-card {
            transition: transform 0.3s;
            margin-bottom: 20px;
        }
        .produto-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .produto-img {
            height: 200px;
            object-fit: cover;
        }
        .badge-estoque {
            position: absolute;
            top: 10px;
            right: 10px;
        }
        .filtro-categorias {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php require_once 'nav.php' ?>
    
    <div class="container mt-5 pt-5">
        <div class="row mb-4">
            <div class="col-md-8">
                <h2>Gerenciamento de Produtos</h2>
            </div>
            <?php if ($isAdmin): ?>
            <div class="col-md-4 text-right">
                <a href="adicionar_produto.php" class="btn btn-success">
                    <i class="bi bi-plus-circle"></i> Adicionar Produto
                </a>
            </div>
            <?php endif; ?>
        </div>
        
        <?php if (isset($mensagem)): ?>
        <div class="alert alert-<?php echo $tipoMensagem; ?> alert-dismissible fade show" role="alert">
            <?php echo $mensagem; ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <?php endif; ?>
        
        <!-- Filtro de categorias -->
        <div class="row filtro-categorias">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Filtrar por Categoria</h5>
                        <div class="d-flex flex-wrap">
                            <a href="produtos.php" class="btn btn-outline-primary m-1 <?php echo !$categoria ? 'active' : ''; ?>">
                                Todos
                            </a>
                            <?php
                            $categorias = ListarCategorias();
                            if ($categorias) {
                                while ($cat = $categorias->fetch_assoc()) {
                                    $active = $categoria == $cat['cd_categoria'] ? 'active' : '';
                                    echo '<a href="produtos.php?categoria=' . $cat['cd_categoria'] . '" class="btn btn-outline-primary m-1 ' . $active . '">';
                                    echo $cat['nm_categoria'];
                                    echo '</a>';
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Lista de produtos -->
        <div class="row">
            <?php
            $produtos = ListarProdutos($categoria);
            if ($produtos && $produtos->num_rows > 0) {
                while ($produto = $produtos->fetch_assoc()) {
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
                    
                    // Verificar se o produto está ativo
                    $cardClass = $produto['st_produto'] == '1' ? '' : 'bg-light';
                    
                    // Verificar se a imagem existe, caso contrário usar uma imagem padrão
                    $imagem = !empty($produto['url_imagem_produto']) && file_exists('img/produtos/' . $produto['url_imagem_produto']) 
                              ? 'img/produtos/' . $produto['url_imagem_produto'] 
                              : 'img/produto_default.jpg';
            ?>
            <div class="col-md-4">
                <div class="card produto-card <?php echo $cardClass; ?>">
                    <span class="badge badge-pill badge-<?php echo $badgeClass; ?> badge-estoque">
                        <?php echo $badgeText; ?>
                    </span>
                    <img src="<?php echo $imagem; ?>" class="card-img-top produto-img" alt="<?php echo $produto['nm_produto']; ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $produto['nm_produto']; ?></h5>
                        <p class="card-text">
                            <small class="text-muted"><?php echo $produto['nm_categoria']; ?></small>
                        </p>
                        <p class="card-text"><?php echo substr($produto['ds_produto'], 0, 100) . (strlen($produto['ds_produto']) > 100 ? '...' : ''); ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>R$ <?php echo number_format($produto['vl_produto'], 2, ',', '.'); ?></strong>
                            </div>
                            <div>
                                <span class="badge badge-info">
                                    Estoque: <?php echo $produto['qt_estoque']; ?>
                                </span>
                            </div>
                        </div>
                        <div class="mt-3">
                            <a href="ver_produto.php?id=<?php echo $produto['cd_produto']; ?>" class="btn btn-primary btn-sm">
                                <i class="bi bi-eye"></i> Detalhes
                            </a>
                            <?php if ($isAdmin): ?>
                            <a href="editar_produto.php?id=<?php echo $produto['cd_produto']; ?>" class="btn btn-warning btn-sm">
                                <i class="bi bi-pencil"></i> Editar
                            </a>
                            <?php endif; ?>
                            <a href="movimentar_produto.php?id=<?php echo $produto['cd_produto']; ?>" class="btn btn-success btn-sm">
                                <i class="bi bi-arrow-left-right"></i> Movimentar
                            </a>
                        </div>
                    </div>
                    <div class="card-footer text-muted">
                        <?php 
                        if (!empty($produto['dt_validade'])) {
                            $validade = new DateTime($produto['dt_validade']);
                            $hoje = new DateTime();
                            $diff = $hoje->diff($validade);
                            
                            if ($validade < $hoje) {
                                echo '<span class="text-danger">Vencido há ' . $diff->days . ' dias</span>';
                            } else {
                                echo 'Validade: ' . date('d/m/Y', strtotime($produto['dt_validade']));
                            }
                        } else {
                            echo 'Sem data de validade';
                        }
                        ?>
                    </div>
                </div>
            </div>
            <?php
                }
            } else {
            ?>
            <div class="col-12">
                <div class="alert alert-info">
                    Nenhum produto encontrado.
                    <?php if ($isAdmin): ?>
                    <a href="adicionar_produto.php" class="alert-link">Adicionar um novo produto</a>.
                    <?php endif; ?>
                </div>
            </div>
            <?php
            }
            ?>
        </div>
    </div>
    
    <?php require_once 'footer.php'; ?>
</body>
</html>
