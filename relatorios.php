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
$tipo_relatorio = isset($_GET['tipo_relatorio']) ? $_GET['tipo_relatorio'] : '';
$data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : date('Y-m-01'); // Primeiro dia do mês atual
$data_fim = isset($_GET['data_fim']) ? $_GET['data_fim'] : date('Y-m-t'); // Último dia do mês atual
$categoria = isset($_GET['categoria']) ? intval($_GET['categoria']) : 0;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios - Controle de Estoque</title>
    <link rel="stylesheet" href="./css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php require_once 'nav.php' ?>
    
    <div class="container mt-5 pt-5">
        <div class="row mb-4">
            <div class="col-12">
                <h2>Relatórios</h2>
            </div>
        </div>
        
        <!-- Filtros -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>Filtros</h5>
            </div>
            <div class="card-body">
                <form method="get" action="relatorios.php">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="tipo_relatorio">Tipo de Relatório</label>
                                <select class="form-control" id="tipo_relatorio" name="tipo_relatorio">
                                    <option value="">Selecione</option>
                                    <option value="movimentacoes" <?php echo $tipo_relatorio == 'movimentacoes' ? 'selected' : ''; ?>>Movimentações</option>
                                    <option value="estoque" <?php echo $tipo_relatorio == 'estoque' ? 'selected' : ''; ?>>Estoque Atual</option>
                                    <option value="validade" <?php echo $tipo_relatorio == 'validade' ? 'selected' : ''; ?>>Produtos a Vencer</option>
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
                                <label for="categoria">Categoria</label>
                                <select class="form-control" id="categoria" name="categoria">
                                    <option value="0">Todas</option>
                                    <?php
                                    $categorias = ListarCategorias();
                                    if ($categorias) {
                                        while ($cat = $categorias->fetch_assoc()) {
                                            $selected = $categoria == $cat['cd_categoria'] ? 'selected' : '';
                                            echo '<option value="' . $cat['cd_categoria'] . '" ' . $selected . '>' . $cat['nm_categoria'] . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 text-right">
                            <button type="submit" class="btn btn-primary">Gerar Relatório</button>
                            <a href="relatorios.php" class="btn btn-secondary">Limpar Filtros</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <?php if ($tipo_relatorio): ?>
        <!-- Resultados do Relatório -->
        <div class="card">
            <div class="card-header">
                <h5>
                    <?php 
                    if ($tipo_relatorio == 'movimentacoes') {
                        echo 'Relatório de Movimentações';
                    } elseif ($tipo_relatorio == 'estoque') {
                        echo 'Relatório de Estoque Atual';
                    } elseif ($tipo_relatorio == 'validade') {
                        echo 'Relatório de Produtos a Vencer';
                    }
                    ?>
                </h5>
            </div>
            <div class="card-body">
                <?php if ($tipo_relatorio == 'movimentacoes'): ?>
                    <!-- Gráfico de Movimentações -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <canvas id="graficoMovimentacoes"></canvas>
                        </div>
                        <div class="col-md-6">
                            <canvas id="graficoTipoMovimentacoes"></canvas>
                        </div>
                    </div>
                    
                    <!-- Tabela de Movimentações -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Produto</th>
                                    <th>Tipo</th>
                                    <th>Quantidade</th>
                                    <th>Usuário</th>
                                    <th>Observação</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Construir a consulta SQL com os filtros
                                $sql = "SELECT 
                                        DATE(m.dt_movimentacao) as data,
                                        p.nm_produto,
                                        m.tp_movimentacao,
                                        SUM(m.qt_movimentacao) as total_quantidade,
                                        u.nm_usuario,
                                        m.ds_observacao
                                    FROM 
                                        tb_movimentacao m
                                    INNER JOIN 
                                        tb_produto p ON m.id_produto = p.cd_produto
                                    INNER JOIN 
                                        tb_usuario u ON m.id_usuario = u.cd_usuario
                                    WHERE 
                                        DATE(m.dt_movimentacao) BETWEEN ? AND ?";
                                
                                $params = [$data_inicio, $data_fim];
                                $types = 'ss';
                                
                                if ($categoria > 0) {
                                    $sql .= " AND p.id_categoria = ?";
                                    $params[] = $categoria;
                                    $types .= 'i';
                                }
                                
                                $sql .= " GROUP BY DATE(m.dt_movimentacao), p.nm_produto, m.tp_movimentacao, u.nm_usuario, m.ds_observacao
                                          ORDER BY m.dt_movimentacao DESC";
                                
                                $stmt = $con->prepare($sql);
                                $stmt->bind_param($types, ...$params);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                
                                // Dados para o gráfico
                                $datas = [];
                                $entradasPorData = [];
                                $saidasPorData = [];
                                
                                $totalEntradas = 0;
                                $totalSaidas = 0;
                                
                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . date('d/m/Y', strtotime($row['data'])) . "</td>";
                                        echo "<td>" . $row['nm_produto'] . "</td>";
                                        echo "<td>" . ($row['tp_movimentacao'] == 'entrada' ? 
                                                '<span class="badge badge-success">Entrada</span>' : 
                                                '<span class="badge badge-danger">Saída</span>') . "</td>";
                                        echo "<td>" . $row['total_quantidade'] . "</td>";
                                        echo "<td>" . $row['nm_usuario'] . "</td>";
                                        echo "<td>" . $row['ds_observacao'] . "</td>";
                                        echo "</tr>";
                                        
                                        // Coletar dados para o gráfico
                                        $dataFormatada = date('d/m', strtotime($row['data']));
                                        if (!in_array($dataFormatada, $datas)) {
                                            $datas[] = $dataFormatada;
                                            $entradasPorData[$dataFormatada] = 0;
                                            $saidasPorData[$dataFormatada] = 0;
                                        }
                                        
                                        if ($row['tp_movimentacao'] == 'entrada') {
                                            $entradasPorData[$dataFormatada] += $row['total_quantidade'];
                                            $totalEntradas += $row['total_quantidade'];
                                        } else {
                                            $saidasPorData[$dataFormatada] += $row['total_quantidade'];
                                            $totalSaidas += $row['total_quantidade'];
                                        }
                                    }
                                } else {
                                    echo "<tr><td colspan='6' class='text-center'>Nenhuma movimentação encontrada</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Script para gerar os gráficos -->
                    <script>
                        // Gráfico de movimentações por data
                        var ctxMovimentacoes = document.getElementById('graficoMovimentacoes').getContext('2d');
                        var graficoMovimentacoes = new Chart(ctxMovimentacoes, {
                            type: 'bar',
                            data: {
                                labels: <?php echo json_encode(array_values($datas)); ?>,
                                datasets: [
                                    {
                                        label: 'Entradas',
                                        data: <?php echo json_encode(array_values($entradasPorData)); ?>,
                                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                        borderColor: 'rgba(75, 192, 192, 1)',
                                        borderWidth: 1
                                    },
                                    {
                                        label: 'Saídas',
                                        data: <?php echo json_encode(array_values($saidasPorData)); ?>,
                                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                                        borderColor: 'rgba(255, 99, 132, 1)',
                                        borderWidth: 1
                                    }
                                ]
                            },
                            options: {
                                scales: {
                                    y: {
                                        beginAtZero: true
                                    }
                                },
                                responsive: true,
                                plugins: {
                                    title: {
                                        display: true,
                                        text: 'Movimentações por Data'
                                    }
                                }
                            }
                        });
                        
                        // Gráfico de tipo de movimentações
                        var ctxTipoMovimentacoes = document.getElementById('graficoTipoMovimentacoes').getContext('2d');
                        var graficoTipoMovimentacoes = new Chart(ctxTipoMovimentacoes, {
                            type: 'pie',
                            data: {
                                labels: ['Entradas', 'Saídas'],
                                datasets: [{
                                    data: [<?php echo $totalEntradas; ?>, <?php echo $totalSaidas; ?>],
                                    backgroundColor: [
                                        'rgba(75, 192, 192, 0.2)',
                                        'rgba(255, 99, 132, 0.2)'
                                    ],
                                    borderColor: [
                                        'rgba(75, 192, 192, 1)',
                                        'rgba(255, 99, 132, 1)'
                                    ],
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    title: {
                                        display: true,
                                        text: 'Proporção de Entradas e Saídas'
                                    }
                                }
                            }
                        });
                    </script>
                <?php elseif ($tipo_relatorio == 'estoque'): ?>
                    <!-- Gráfico de Estoque por Categoria -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <canvas id="graficoEstoqueCategoria"></canvas>
                        </div>
                        <div class="col-md-6">
                            <canvas id="graficoValorEstoque"></canvas>
                        </div>
                    </div>
                    
                    <!-- Tabela de Estoque -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th>Categoria</th>
                                    <th>Quantidade</th>
                                    <th>Valor Unitário</th>
                                    <th>Valor Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Construir a consulta SQL com os filtros
                                $sql = "SELECT 
                                        p.nm_produto,
                                        c.nm_categoria,
                                        p.qt_estoque,
                                        p.vl_produto,
                                        (p.qt_estoque * p.vl_produto) as valor_total,
                                        p.st_produto
                                    FROM 
                                        tb_produto p
                                    INNER JOIN 
                                        tb_categoria c ON p.id_categoria = c.cd_categoria
                                    WHERE 1=1";
                                
                                $params = [];
                                $types = '';
                                
                                if ($categoria > 0) {
                                    $sql .= " AND p.id_categoria = ?";
                                    $params[] = $categoria;
                                    $types .= 'i';
                                }
                                
                                $sql .= " ORDER BY c.nm_categoria, p.nm_produto";
                                
                                $stmt = $con->prepare($sql);
                                
