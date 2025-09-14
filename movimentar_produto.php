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

// Processar o formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo = isset($_POST['tipo']) ? $_POST['tipo'] : '';
    $quantidade = isset($_POST['quantidade']) ? intval($_POST['quantidade']) : 0;
    $observacao = isset($_POST['observacao']) ? $_POST['observacao'] : '';
    
    // Validar os dados
    $erros = [];
    
    if ($tipo != 'entrada' && $tipo != 'saida') {
        $erros[] = "Selecione um tipo de movimentação válido.";
    }
    
    if ($quantidade <= 0) {
        $erros[] = "A quantidade deve ser maior que zero.";
    }
    
    // Se for uma saída, verificar se há estoque suficiente
    if ($tipo == 'saida' && $quantidade > $produto['qt_estoque']) {
        $erros[] = "Estoque insuficiente. Disponível: " . $produto['qt_estoque'];
    }
    
    // Se não houver erros, adicionar a movimentação
    if (empty($erros)) {
        if (AdicionarMovimentacao($id_produto, $tipo, $quantidade, $_SESSION['id'], $observacao)) {
            $mensagem = "Movimentação registrada com sucesso!";
            $tipoMensagem = "success";
            
            // Atualizar os dados do produto após a movimentação
            $produto = DetalhesProduto($id_produto);
            
            // Redirecionar após 2 segundos
            header("refresh:2;url=ver_produto.php?id=" . $id_produto);
        } else {
            $mensagem = "Erro ao registrar movimentação.";
            $tipoMensagem = "danger";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movimentar Estoque - <?php echo $produto['nm_produto']; ?></title>
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
    <?php require_once 'nav.php' ?>
    
    <div class="container mt-5 pt-5">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="produtos.php">Produtos</a></li>
                <li class="breadcrumb-item"><a href="ver_produto.php?id=<?php echo $id_produto; ?>"><?php echo $produto['nm_produto']; ?></a></li>
                <li class="breadcrumb-item active" aria-current="page">Movimentar Estoque</li>
            </ol>
        </nav>
        
        <div class="row">
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Informações do Produto</h5>
                    </div>
                    <div class="card-body">
                        <h4><?php echo $produto['nm_produto']; ?></h4>
                        <p><strong>Categoria:</strong> <?php echo $produto['nm_categoria']; ?></p>
                        <p><strong>Estoque Atual:</strong> <?php echo $produto['qt_estoque']; ?> unidades</p>
                        <p><strong>Valor Unitário:</strong> R$ <?php echo number_format($produto['vl_produto'], 2, ',', '.'); ?></p>
                        
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
                        
                        <div class="mt-3">
                            <span class="badge badge-<?php echo $badgeClass; ?>">
                                <?php echo $badgeText; ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5>Registrar Movimentação</h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($mensagem)): ?>
                        <div class="alert alert-<?php echo $tipoMensagem; ?> alert-dismissible fade show" role="alert">
                            <?php echo $mensagem; ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (isset($erros) && !empty($erros)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($erros as $erro): ?>
                                <li><?php echo $erro; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>
                        
                        <form method="post" action="movimentar_produto.php?id=<?php echo $id_produto; ?>">
                            <div class="form-group">
                                <label>Tipo de Movimentação</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="tipo" id="tipo_entrada" value="entrada" <?php echo (isset($_POST['tipo']) && $_POST['tipo'] == 'entrada') ? 'checked' : ''; ?> required>
                                    <label class="form-check-label" for="tipo_entrada">
                                        Entrada
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="tipo" id="tipo_saida" value="saida" <?php echo (isset($_POST['tipo']) && $_POST['tipo'] == 'saida') ? 'checked' : ''; ?> required>
                                    <label class="form-check-label" for="tipo_saida">
                                        Saída
                                    </label>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="quantidade">Quantidade</label>
                                <input type="number" class="form-control" id="quantidade" name="quantidade" min="1" value="<?php echo isset($_POST['quantidade']) ? $_POST['quantidade'] : '1'; ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="observacao">Observação</label>
                                <textarea class="form-control" id="observacao" name="observacao" rows="3"><?php echo isset($_POST['observacao']) ? $_POST['observacao'] : ''; ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label>Usuário Responsável</label>
                                <input type="text" class="form-control" value="<?php echo $_SESSION['usuario']; ?>" readonly>
                                <small class="form-text text-muted">Esta movimentação será registrada com o seu usuário.</small>
                            </div>
                            
                            <div class="form-group text-right">
                                <a href="ver_produto.php?id=<?php echo $id_produto; ?>" class="btn btn-secondary">Cancelar</a>
                                <button type="submit" class="btn btn-primary">Registrar Movimentação</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php require_once 'footer.php'; ?>
</body>
</html>
