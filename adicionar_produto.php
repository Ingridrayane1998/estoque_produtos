<?php
require_once 'header.php';

// Verificar se o usuário está logado
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

require_once 'function.php';
require_once 'conect.php';

// Verificar se o usuário é administrador
$sql = "SELECT c.nm_cargo FROM tb_usuario u 
        INNER JOIN tb_cargo c ON u.id_cargo = c.cd_cargo 
        WHERE u.cd_usuario = ?";
$stmt = $con->prepare($sql);
if ($stmt === false) {
    die("Erro na preparação da consulta: " . $con->error);
}
$stmt->bind_param('i', $_SESSION['id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: produtos.php?erro=usuario");
    exit;
}

$cargo = $result->fetch_assoc()['nm_cargo'];

if ($cargo != 'ADMINISTRADOR') {
    // Redirecionar para a página de produtos com mensagem de erro
    header("Location: produtos.php?erro=permissao");
    exit;
}

// Buscar categorias para o formulário
$sql_categorias = "SELECT cd_categoria, nm_categoria FROM tb_categoria ORDER BY nm_categoria";
$result_categorias = $con->query($sql_categorias);

// Buscar fornecedores para o formulário
$sql_fornecedores = "SELECT cd_fornecedor, nm_fornecedor FROM tb_fornecedor ORDER BY nm_fornecedor";
$result_fornecedores = $con->query($sql_fornecedores);

// Processar o formulário quando enviado
$mensagem = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Receber e validar os dados do formulário
    $nome = isset($_POST['nome']) ? trim($_POST['nome']) : '';
    $descricao = isset($_POST['descricao']) ? trim($_POST['descricao']) : '';
    $preco = isset($_POST['preco']) ? str_replace(',', '.', $_POST['preco']) : 0;
    $quantidade = isset($_POST['quantidade']) ? intval($_POST['quantidade']) : 0;
    $categoria = isset($_POST['categoria']) ? intval($_POST['categoria']) : 0;
    $validade = !empty($_POST['validade']) ? $_POST['validade'] : null;
    
    // Validação básica
    if (empty($nome) || empty($descricao) || $preco <= 0 || $quantidade < 0 || $categoria <= 0) {
        $mensagem = '<div class="alert alert-danger">Preencha todos os campos obrigatórios corretamente.</div>';
    } else {
        // Inserir produto no banco de dados
        if ($validade) {
            $sql_inserir = "INSERT INTO tb_produto (nm_produto, ds_produto, vl_produto, qt_estoque, id_categoria, dt_validade) 
                           VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_inserir = $con->prepare($sql_inserir);
            
            // Verificar se a preparação foi bem-sucedida
            if ($stmt_inserir === false) {
                $mensagem = '<div class="alert alert-danger">Erro na preparação da consulta: ' . $con->error . '</div>';
            } else {
                $stmt_inserir->bind_param('ssdiss', $nome, $descricao, $preco, $quantidade, $categoria, $validade);
                
                if ($stmt_inserir->execute()) {
                    // Redirecionar para a página de produtos com mensagem de sucesso
                    header("Location: produtos.php?sucesso=adicionar");
                    exit;
                } else {
                    $mensagem = '<div class="alert alert-danger">Erro ao adicionar produto: ' . $stmt_inserir->error . '</div>';
                }
            }
        } else {
            $sql_inserir = "INSERT INTO tb_produto (nm_produto, ds_produto, vl_produto, qt_estoque, id_categoria) 
                           VALUES (?, ?, ?, ?, ?)";
            $stmt_inserir = $con->prepare($sql_inserir);
            
            // Verificar se a preparação foi bem-sucedida
            if ($stmt_inserir === false) {
                $mensagem = '<div class="alert alert-danger">Erro na preparação da consulta: ' . $con->error . '</div>';
            } else {
                $stmt_inserir->bind_param('ssdii', $nome, $descricao, $preco, $quantidade, $categoria);
                
                if ($stmt_inserir->execute()) {
                    // Redirecionar para a página de produtos com mensagem de sucesso
                    header("Location: produtos.php?sucesso=adicionar");
                    exit;
                } else {
                    $mensagem = '<div class="alert alert-danger">Erro ao adicionar produto: ' . $stmt_inserir->error . '</div>';
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Produto - Controle de Estoque</title>
    <link rel="stylesheet" href="./css/style.css">
    <style>
        .form-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .form-title {
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .btn-container {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        
        .required-field::after {
            content: " *";
            color: red;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="form-container">
            <h2 class="form-title">Adicionar Novo Produto</h2>
            
            <?php if (!empty($mensagem)) echo $mensagem; ?>
            
            <form method="post" action="adicionar_produto.php">
                <div class="form-group">
                    <label for="nome" class="required-field">Nome do Produto:</label>
                    <input type="text" class="form-control" id="nome" name="nome" required>
                </div>
                
                <div class="form-group">
                    <label for="descricao" class="required-field">Descrição:</label>
                    <textarea class="form-control" id="descricao" name="descricao" rows="3" required></textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="preco" class="required-field">Preço (R$):</label>
                            <input type="text" class="form-control" id="preco" name="preco" required>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="quantidade" class="required-field">Quantidade em Estoque:</label>
                            <input type="number" class="form-control" id="quantidade" name="quantidade" min="0" required>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="categoria" class="required-field">Categoria:</label>
                            <select class="form-control" id="categoria" name="categoria" required>
                                <option value="">Selecione uma categoria</option>
                                <?php
                                if ($result_categorias && $result_categorias->num_rows > 0) {
                                    while ($categoria = $result_categorias->fetch_assoc()) {
                                        echo '<option value="' . $categoria['cd_categoria'] . '">' . $categoria['nm_categoria'] . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="validade">Data de Validade (opcional):</label>
                            <input type="date" class="form-control" id="validade" name="validade">
                        </div>
                    </div>
                </div>
                
                <div class="btn-container">
                    <a href="produtos.php" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Adicionar Produto</button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Script para formatar o campo de preço
        $(document).ready(function() {
            $('#preco').on('input', function() {
                // Remove caracteres não numéricos exceto vírgula e ponto
                let value = $(this).val().replace(/[^0-9.,]/g, '');
                
                // Substitui múltiplos pontos ou vírgulas por um único
                value = value.replace(/[.,]+/g, ',');
                
                // Atualiza o valor do campo
                $(this).val(value);
            });
        });
    </script>
</body>
</html>
