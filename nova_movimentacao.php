<?php
require_once 'header.php';

// Verificar se o usuário está logado
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

require_once 'function.php';

// Processar o formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $produto = isset($_POST['produto']) ? intval($_POST['produto']) : 0;
    $tipo = isset($_POST['tipo']) ? $_POST['tipo'] : '';
    $quantidade = isset($_POST['quantidade']) ? intval($_POST['quantidade']) : 0;
    $observacao = isset($_POST['observacao']) ? $_POST['observacao'] : '';
    
    // Validar os dados
    $erros = [];
    
    if ($produto <= 0) {
        $erros[] = "Selecione um produto válido.";
    }
    
    if ($tipo != 'entrada' && $tipo != 'saida') {
        $erros[] = "Selecione um tipo de movimentação válido.";
    }
    
    if ($quantidade <= 0) {
        $erros[] = "A quantidade deve ser maior que zero.";
    }
    
    // Se for uma saída, verificar se há estoque suficiente
    if ($tipo == 'saida') {
        $detalhesProduto = DetalhesProduto($produto);
        if ($detalhesProduto && $detalhesProduto['qt_estoque'] < $quantidade) {
            $erros[] = "Estoque insuficiente. Disponível: " . $detalhesProduto['qt_estoque'];
        }
    }
    
    // Se não houver erros, adicionar a movimentação
    if (empty($erros)) {
        // Usar o ID do usuário logado para registrar quem fez a movimentação
        $id_usuario = $_SESSION['id'];
        
        if (AdicionarMovimentacao($produto, $tipo, $quantidade, $id_usuario, $observacao)) {
            $mensagem = "Movimentação registrada com sucesso!";
            $tipoMensagem = "success";
            
            // Redirecionar para a página de movimentações após 2 segundos
            header("refresh:2;url=movimentacoes.php");
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
    <title>Nova Movimentação - Controle de Estoque</title>
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
    <?php require_once 'nav.php' ?>
    
    <div class="container mt-5 pt-5">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="movimentacoes.php">Movimentações</a></li>
                <li class="breadcrumb-item active" aria-current="page">Nova Movimentação</li>
            </ol>
        </nav>
        
        <div class="card">
            <div class="card-header">
                <h4>Nova Movimentação de Estoque</h4>
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
                
                <form method="post" action="nova_movimentacao.php">
                    <div class="form-group">
                        <label for="produto">Produto</label>
                        <select class="form-control" id="produto" name="produto" required>
                            <option value="">Selecione um produto</option>
                            <?php
                            $produtos = ListarProdutos();
                            if ($produtos) {
                                while ($p = $produtos->fetch_assoc()) {
                                    $selected = isset($_POST['produto']) && $_POST['produto'] == $p['cd_produto'] ? 'selected' : '';
                                    echo '<option value="' . $p['cd_produto'] . '" ' . $selected . '>' . $p['nm_produto'] . ' (Estoque: ' . $p['qt_estoque'] . ')</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    
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
                        <a href="movimentacoes.php" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Concluir Movimentação</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <?php require_once 'footer.php'; ?>
</body>
</html>
