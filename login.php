<?php
require_once 'header.php';
require_once 'conect.php';

session_start();

// Verificar se o usuário já está logado
if (isset($_SESSION['id'])) {
    header("Location: index.php");
    exit;
}

// Processar o formulário de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $senha = isset($_POST['senha']) ? $_POST['senha'] : '';
    
    if (empty($email) || empty($senha)) {
        $erro = "Preencha todos os campos.";
    } else {
        // Criptografar a senha
        $senha_hash = hash('sha256', $senha);
        
        // Armazenar informações de depuração apenas em ambiente de desenvolvimento
        // Usar prepared statements para evitar injeção SQL
        $stmt = $con->prepare("SELECT cd_usuario, nm_usuario, id_cargo FROM tb_usuario WHERE nm_email = ? AND cd_senha = ?");
        $stmt->bind_param("ss", $email, $senha_hash);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result === false) {
            $erro = "Erro ao fazer login. Tente novamente mais tarde.";
            // Log do erro real apenas para o servidor, não para o usuário
            error_log("Erro MySQL: " . $con->error);
        } elseif ($result->num_rows > 0) {
            $usuario = $result->fetch_assoc();
            
            // Iniciar a sessão
            $_SESSION['id'] = $usuario['cd_usuario'];
            $_SESSION['usuario'] = $usuario['nm_usuario'];
            $_SESSION['cargo'] = $usuario['id_cargo'];
            
            $_SESSION['mostrar_boas_vindas'] = true;
            
            // Redirecionar para a página inicial
            header("Location: index.php");
            exit;
        } else {
            $erro = "Email ou senha incorretos.";
            // Não exibir detalhes adicionais para o usuário
        }
        
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Controle de Estoque</title>
    <link rel="stylesheet" href="./css/style.css">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 0;
        }
        
        .login-container {
            display: flex;
            width: 900px;
            height: 500px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border-radius: 15px;
            overflow: hidden;
        }
        
        .login-image {
            flex: 1;
            background-color: #0d6efd;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        
        .login-image img {
            width: 80%;
            height: auto;
            object-fit: contain;
            z-index: 2;
            border-radius: 20px; /* Bordas arredondadas para a imagem */
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2); /* Sombra suave para destacar a imagem */
            transition: transform 0.3s ease; /* Efeito suave ao passar o mouse */
        }
        
        .login-image img:hover {
            transform: scale(1.05); /* Efeito de zoom suave ao passar o mouse */
        }
        
        .login-shape {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
        }
        
        .login-form {
            flex: 1;
            padding: 50px;
            background-color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .login-form h1 {
            margin-bottom: 30px;
            font-weight: bold;
            color: #333;
        }
        
        .form-control {
            margin-bottom: 20px;
            border-radius: 5px;
            padding: 12px;
            border: 1px solid #ddd;
        }
        
        .btn-login {
            background-color: #0d6efd;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        
        .btn-login:hover {
            background-color: #0b5ed7;
        }
        
        /* Responsividade para telas menores */
        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
                width: 90%;
                height: auto;
            }
            
            .login-image {
                height: 200px;
            }
            
            .login-form {
                padding: 30px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-image">
            <!-- Adicione sua imagem aqui -->
            <img src="img/inventory_illustration.png" alt="Controle de Estoque">
            <svg class="login-shape" viewBox="0 0 500 500" preserveAspectRatio="none">
                <path d="M0,0 C150,150 350,0 500,150 L500,500 L0,500 Z" fill="#0d6efd"></path>
            </svg>
        </div>
        <div class="login-form">
            <h1>CONTROLE DE ESTOQUE</h1>
            
            <?php if (isset($erro)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $erro; ?>
            </div>
            <?php endif; ?>
            
            <form method="post" action="login.php">
                <div class="form-group">
                    <input type="text" class="form-control" name="email" placeholder="Digite seu usuário" required>
                </div>
                <div class="form-group">
                    <input type="password" class="form-control" name="senha" placeholder="Sua senha" required>
                </div>
                <button type="submit" class="btn btn-login">Entrar</button>
            </form>
        </div>
    </div>
</body>
</html>
