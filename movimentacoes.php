<?php
require_once 'header.php';

// Verificar se o usuário está logado
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

require_once 'function.php';

// Parâmetros de filtro
$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';
$data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : '';
$data_fim = isset($_GET['data_fim']) ? $_GET['data_fim'] : '';
$produto = isset($_GET['produto']) ? intval($_GET['produto']) : 0;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movimentações - Controle de Estoque</title>
    <link rel="stylesheet" href="./css/style.css">
</head>
<body>
    <?php require_once 'nav.php' ?>
    
    <div class="container mt-5 pt-5">
        <div class="row mb-4">
            <div class="col-md-8">
                <h2>Histórico de Movimentações</h2>
            </div>
            <div class="col-md-4 text-right">
                <a href="nova_movimentacao.php" class="btn btn-success">
                    <i class="bi bi-plus-circle"></i> Nova Movimentação
                </a>
            </div>
        </div>
        
        <!-- Filtros -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>Filtros</h5>
            </div>
            <div class="card-body">
                <form method="get" action="movimentacoes.php">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="tipo">Tipo</label>
                                <select class="form-control" id="tipo" name="tipo">
                                    <option value="">Todos</option>
                                    <option value="entrada" <?php echo $tipo == 'entrada' ? 'selected' : ''; ?>>Entrada</option>
                                    <option value="saida" <?php echo $tipo == 'saida' ? 'selected' : ''; ?>>Saída</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="data_inicio">Data Início</label>
                                <input type="date" class="form-control" id="data_inicio" name="data_inicio" value="<?php echo $data_inicio; ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="data_fim">Data Fim</label>
                                <input type="date" class="form-control" id="data_fim" name="data_fim" value="<?php echo $data_fim; ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="produto">Produto</label>
                                <select class="form-control" id="produto" name="produto">
                                    <option value="0">Todos</option>
                                    <?php
                                    $produtos = ListarProdutos();
                                    if ($produtos) {
                                        while ($p = $produtos->fetch_assoc()) {
                                            $selected = $produto == $p['cd_produto'] ? 'selected' : '';
                                            echo '<option value="' . $p['cd_produto'] . '" ' . $selected . '>' . $p['nm_produto'] . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 text-right">
                            <button type="submit" class="btn btn-primary">Filtrar</button>
                            <a href="movimentacoes.php" class="btn btn-secondary">Limpar Filtros</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Tabela de Movimentações -->
        <div class="card">
            <div class="card-header">
                <h5>Movimentações de Estoque</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Produto</th>
                                <th>Tipo</th>
                                <th>Quantidade</th>
                                <th>Data/Hora</th>
                                <th>Usuário</th>
                                <th>Observação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Construir a consulta SQL com os filtros
                            $sql = "SELECT 
                                    m.cd_movimentacao,
                                    p.nm_produto,
                                    m.tp_movimentacao,
                                    m.qt_movimentacao,
                                    m.dt_movimentacao,
                                    u.nm_usuario,
                                    m.ds_observacao
                                FROM 
                                    tb_movimentacao m
                                INNER JOIN 
                                    tb_produto p ON m.id_produto = p.cd_produto
                                INNER JOIN 
                                    tb_usuario u ON m.id_usuario = u.cd_usuario
                                WHERE 1=1";
                            
                            $params = [];
                            $types = '';
                            
                            if (!empty($tipo)) {
                                $sql .= " AND m.tp_movimentacao = ?";
                                $params[] = $tipo;
                                $types .= 's';
                            }
                            
                            if (!empty($data_inicio)) {
                                $sql .= " AND DATE(m.dt_movimentacao) >= ?";
                                $params[] = $data_inicio;
                                $types .= 's';
                            }
                            
                            if (!empty($data_fim)) {
                                $sql .= " AND DATE(m.dt_movimentacao) <= ?";
                                $params[] = $data_fim;
                                $types .= 's';
                            }
                            
                            if ($produto > 0) {
                                $sql .= " AND m.id_produto = ?";
                                $params[] = $produto;
                                $types .= 'i';
                            }
                            
                            $sql .= " ORDER BY m.dt_movimentacao DESC";
                            
                            $stmt = $con->prepare($sql);
                            
                            if (!empty($params)) {
                                $stmt->bind_param($types, ...$params);
                            }
                            
                            $stmt->execute();
                            $result = $stmt->get_result();
                            
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . $row['cd_movimentacao'] . "</td>";
                                    echo "<td>" . $row['nm_produto'] . "</td>";
                                    echo "<td>" . ($row['tp_movimentacao'] == 'entrada' ? 
                                            '<span class="badge badge-success">Entrada</span>' : 
                                            '<span class="badge badge-danger">Saída</span>') . "</td>";
                                    echo "<td>" . $row['qt_movimentacao'] . "</td>";
                                    echo "<td>" . date('d/m/Y H:i', strtotime($row['dt_movimentacao'])) . "</td>";
                                    echo "<td>" . $row['nm_usuario'] . "</td>";
                                    echo "<td>" . $row['ds_observacao'] . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='7' class='text-center'>Nenhuma movimentação encontrada</td></tr>";
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
