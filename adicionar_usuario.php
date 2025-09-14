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

// Processar o formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = isset($_POST['nome']) ? trim($_POST['nome']) : '';
    $sobrenome = isset($_POST['sobrenome']) ? trim($_POST['sobrenome']) : '';
    $cargo_id = isset($_POST['cargo']) ? intval($_POST['cargo']) : 0;
    $senha = isset($_POST['senha']) ? $_POST['senha'] : '';
    $confirmar_senha = isset($_POST['confirmar_senha']) ? $_POST['confirmar_senha'] : '';
    
    // Gerar o nome completo e o login no formato nome.sobrenome
    $nome_completo = $nome . ' ' . $sobrenome;
    $login = strtolower(removerAcentos($nome) . '.' . removerAcentos($sobrenome));
    
    // Validar os dados
    $erros = [];
    
    if (empty($nome) || empty($sobrenome)) {
        $erros[] = "Nome e sobrenome são obrigatórios.";
    }
    
    if ($cargo_id <= 0) {
        $erros[] = "Selecione um cargo válido.";
    }
    
    if (empty($senha)) {
        $erros[] = "A senha é obrigatória.";
    } elseif ($senha != $confirmar_senha) {
        $erros[] = "As senhas não coincidem.";
    } elseif (strlen($senha) < 6) {
        $erros[] = "A senha deve ter pelo menos 6 caracteres.";
    }
    
    // Verificar se o login já existe
    $sql = "SELECT cd_usuario FROM tb_usuario WHERE nm_email = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param('s', $login);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $erros[] = "Já existe um usuário com este login ($login). Tente outro nome ou sobrenome.";
    }
    
    // Se não houver erros, adicionar o usuário
    if (empty($erros)) {
        $senha_hash = hash('sha256', $senha);
        
        $sql = "INSERT INTO tb_usuario (nm_usuario, nm_email, cd_senha, id_cargo, dt_registro) 
                VALUES (?, ?, ?, ?, NOW())";
        $stmt = $con->prepare($sql);
        $stmt->bind_param('sssi', $nome_completo, $login, $senha_hash, $cargo_id);
        
        if ($stmt->execute()) {
            $mensagem = "Usuário cadastrado com sucesso! Login: " . $login;
            $tipoMensagem = "success";
            
            // Limpar os campos do formulário
            $nome = $sobrenome = $senha = $confirmar_senha = '';
            $cargo_id = 0;
        } else {
            $mensagem = "Erro ao cadastrar usuário: " . $stmt->error;
            $tipoMensagem = "danger";
        }
    }
}

// Função para remover acentos e caracteres especiais
function removerAcentos($string) {
    return preg_replace(
        array('/[áàãâä]/u', '/[éèêë]/u', '/[íìîï]/u', '/[óòõôö]/u', '/[úùûü]/u', '/[ç]/u', '/[^a-zA-Z0-9]/'),
        array('a', 'e', 'i', 'o', 'u', 'c', ''),
        strtolower($string)
    );
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Usuário - Controle de Estoque</title>
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
    <?php require_once 'nav.php' ?>
    
    <div class="container mt-5 pt-5">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="usuarios.php">Usuários</a></li>
                <li class="breadcrumb-item active" aria-current="page">Adicionar Usuário</li>
            </ol>
        </nav>
        
        <div class="card">
            <div class="card-header">
                <h4>Adicionar Novo Usuário</h4>
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
                
                <form method="post" action="adicionar_usuario.php">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nome">Nome</label>
                                <input type="text" class="form-control" id="nome" name="nome" value="<?php echo isset($nome) ? htmlspecialchars($nome) : ''; ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="sobrenome">Sobrenome</label>
                                <input type="text" class="form-control" id="sobrenome" name="sobrenome" value="<?php echo isset($sobrenome) ? htmlspecialchars($sobrenome) : ''; ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="cargo">Cargo</label>
                        <select class="form-control" id="cargo" name="cargo" required>
                            <option value="">Selecione um cargo</option>
                            <?php
                            $sql = "SELECT cd_cargo, nm_cargo FROM tb_cargo ORDER BY nm_cargo";
                            $result = $con->query($sql);
                            
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $selected = isset($cargo_id) && $cargo_id == $row['cd_cargo'] ? 'selected' : '';
                                    echo '<option value="' . $row['cd_cargo'] . '" ' . $selected . '>' . $row['nm_cargo'] . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Login</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Automático:</span>
                            </div>
                            <input type="text" class="form-control" id="login_preview" readonly>
                        </div>
                        <small class="form-text text-muted">O login será gerado automaticamente no formato nome.sobrenome</small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="senha">Senha</label>
                                <input type="password" class="form-control" id="senha" name="senha" required>
                                <small class="form-text text-muted">A senha deve ter pelo menos 6 caracteres</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="confirmar_senha">Confirmar Senha</label>
                                <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group text-right">
                        <a href="usuarios.php" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Cadastrar Usuário</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
    // Script para gerar preview do login
    document.addEventListener('DOMContentLoaded', function() {
        const nomeInput = document.getElementById('nome');
        const sobrenomeInput = document.getElementById('sobrenome');
        const loginPreview = document.getElementById('login_preview');
        
        function updateLoginPreview() {
            const nome = nomeInput.value.trim().toLowerCase();
            const sobrenome = sobrenomeInput.value.trim().toLowerCase();
            
            if (nome && sobrenome) {
                // Função simplificada para remover acentos no cliente
                const removeAcentos = (str) => {
                    return str.normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                              .replace(/[^a-z0-9]/g, '');
                };
                
                const nomeProcessado = removeAcentos(nome);
                const sobrenomeProcessado = removeAcentos(sobrenome);
                
                loginPreview.value = nomeProcessado + '.' + sobrenomeProcessado;
            } else {
                loginPreview.value = '';
            }
        }
        
        nomeInput.addEventListener('input', updateLoginPreview);
        sobrenomeInput.addEventListener('input', updateLoginPreview);
        
        // Inicializar o preview
        updateLoginPreview();
    });
    </script>
    
    <?php require_once 'footer.php'; ?>
</body>
</html>
