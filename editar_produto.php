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
    // Redirecionar para a página de produtos com mensagem de erro
    header("Location: produtos.php?erro=permissao");
    exit;
}

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
    $nome = isset($_POST['nome']) ? $_POST['nome'] : '';
    $descricao = isset($_POST['descricao']) ? $_POST['descricao'] : '';
    $categoria = isset($_POST['categoria']) ? intval($_POST['categoria']) : 0;
    $valor = isset($_POST['valor']) ? str_replace(',', '.', $_POST['valor']) : 0;
    $estoque = isset($_POST['estoque']) ? intval($_POST['estoque']) : 0;
    $validade = isset($_POST['validade']) && !empty($_POST['validade']) ? $_POST['validade'] : null;
    $status = isset($_POST['status']) ? $_POST['status'] : '1';
    
    // Manter a imagem atual se não for enviada uma nova
    $imagem = $produto['url_imagem_produto'];
    
    // Processar upload de nova imagem, se houver
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] == 0) {
        $ext = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
        $novo_nome = md5(time()) . '.' . $ext;
        $diretorio = "img/produtos/";
        
        if (move_uploaded_file($_FILES['imagem']['tmp_name'], $diretorio . $novo_nome)) {
            $imagem = $novo_nome;
        }
    }
    
    // Atualizar o produto
    if (AtualizarProduto($id_produto, $nome, $descricao, $categoria, $valor, $estoque, $validade, $imagem, $status)) {
        $mensagem = "Produto atualizado com sucesso!";
        $tipoMensagem = "success";
        
        // Atualizar os dados do produto após a edição
        $produto = DetalhesProduto($id_produto);
    } else {
        $mensagem = "Erro ao atualizar produto.";
        $tipoMensagem = "danger";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Produto - Controle de Estoque</title>
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
    <?php require_once 'nav.php' ?>
    
    <div class="container mt-5 pt-5">
        <div class="card">
            <div class="card-header">
                <h4>Editar Produto</h4>
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
                
                <form method="post" action="editar_produto.php?id=<?php echo $id_produto; ?>" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="nome">Nome do Produto</label>
                        <input type="text" class="form-control" id="nome" name="nome" value="<?php echo htmlspecialchars($produto['nm_produto']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="descricao">Descrição</label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="3" required><?php echo htmlspecialchars($produto['ds_produto']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="categoria">Categoria</label>
                        <select class="form-control" id="categoria" name="categoria" required>
                            <?php
                            $categorias = ListarCategorias();
                            if ($categorias) {
                                while ($cat = $categorias->fetch_assoc()) {
                                    $selected = $cat['cd_categoria'] == $produto['cd_categoria'] ? 'selected' : '';
                                    echo '<option value="' . $cat['cd_categoria'] . '" ' . $selected . '>' . $cat['nm_categoria'] . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="valor">Valor Unitário (R$)</label>
                        <input type="text" class="form-control" id="valor" name="valor" value="<?php echo number_format($produto['vl_produto'], 2, ',', '.'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="estoque">Quantidade em Estoque</label>
                        <input type="number" class="form-control" id="estoque" name="estoque" value="<?php echo $produto['qt_estoque']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="validade">Data de Validade</label>
                        <input type="date" class="form-control" id="validade" name="validade" value="<?php echo $produto['dt_validade'] ? date('Y-m-d', strtotime($produto['dt_validade'])) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="1" <?php echo $produto['st_produto'] == '1' ? 'selected' : ''; ?>>Ativo</option>
                            <option value="0" <?php echo $produto['st_produto'] == '0' ? 'selected' : ''; ?>>Inativo</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="imagem">Imagem do Produto</label>
                        <input type="file" class="form-control-file" id="imagem" name="imagem">
                        <small class="form-text text-muted">Deixe em branco para manter a imagem atual.</small>
                    </div>
                    <div class="form-group text-right">
                        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                        <a href="ver_produto.php?id=<?php echo $id_produto; ?>" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <?php require_once 'footer.php'; ?>
</body>
</html>
