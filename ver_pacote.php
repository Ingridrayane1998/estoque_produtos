<?php
require_once 'header.php';
$pacote = $_GET['pacote']; 

if (empty($pacote)) {
    die('Erro: O pacote não foi passado.');
}
?>

<body>
<?php require_once 'nav.php'; ?>

<div class="container-fluid mt-20">
    <div class="row">
        <div class="cols-sm-12" id="fundos">
        </div>
    </div>
</div>

<?php
$listar = DetalhesPacote($pacote);
if (!is_array($listar)) {
    die('Erro: DetalhesPacote não retornou um array.');
}

$nmCidade = $listar['nmCidade'] ?? null; // Defina nmCidade aqui

if ($nmCidade) {
?>
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-sm-12">
                <h4 class="font-weight-bolder">
                    Hospedagens no <?php echo htmlspecialchars($nmCidade); ?>
                </h4>
            </div>
        </div>
    </div>
    <style>
        .mt-20 {
            margin-top: 4.5rem;
        }
        .mt-30 {
            margin-top: 225px;
        }
        #fundos {
            background-image: url("./img/pacotes/<?php echo $listar['urlImagem']; ?>");
            background-size: 100% 100%;
            background-position: center; 
            background-repeat: no-repeat;
            height: 65vh; 
            width: 100%; 
        }
        .hide {
            position: absolute; 
            width: 0;           
            height: 0;         
            overflow: hidden;  
            opacity: 0;      
        }
        .card-img-h {
            height: 400px;
            background-size: 100%;
            background-position: center; 
            background-repeat: no-repeat;
            width: 100%; 
            border-radius: 10px;
        }
        .mx-10 {
            padding-left: 350px;
            padding-top: 10px;
            padding-right: 175px;
            padding-bottom: 0px;   
        }
        .card {
            margin-bottom: 15%;
            border: none;
            width: 85%;
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
            border-radius: 10px;
        }
        .card:hover {
            transition: 0.7s;
            scale: 1.05;
        }
        .inside-box {
            border: 1px solid #ccc;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin: 20px auto;
        }
        .border {
            border-right: 1px solid #ccc; 
        }
        .fs-2 {
            font-size: 14px;
        }
        .estrela {
            color: #ccc;
            font-size: 18px; 
        }
        .estrela.ativa {
            color: #f39c12; 
        }
    </style>
<?php
} 
?>

<?php
$listarH = DetalhesHospedagem($nmCidade); // Chame a função de hospedagem

if (is_array($listarH) && count($listarH) > 0) { 
    // Ordena o array de hospedagem pela quantidade de estrelas (ntHotel)
    usort($listarH, function($a, $b) {
        return $b['ntHotel'] <=> $a['ntHotel']; // Ordena em ordem decrescente
    });

    ?>
    <div class="row">
        <?php foreach ($listarH as $hospedagem) { 
            $st = ($hospedagem['stHospedagem'] == "0") ? 'hide' : '';
        ?>
            <div class="col-sm-12 mx-10 <?php echo $st; ?>">
                <div class="card mb-3">
                    <div class="row no-gutters">
                        <div class="col-md-4 text-center">
                            <img src="./img/hospedagem/<?php echo htmlspecialchars($hospedagem['urlImagemHospedagem']); ?>" alt="Imagem do Hotel" class="card-img-h">
                        </div>
                        <div class="col-md-4 border">
                            <div class="card-body">
                                <h6 class="card-title font-weight-bolder fs-2">Nome do Hotel: <?php echo htmlspecialchars($hospedagem['nmHotel']); ?></h6>
                                <div class="avaliacao mb-2">
                                    <?php
                                    // Defina o número máximo de estrelas
                                    $maxEstrelas = 5;

                                    // Loop para exibir as estrelas
                                    for ($i = 1; $i <= $maxEstrelas; $i++) {
                                        if ($i <= $hospedagem['ntHotel']) {
                                            echo '<span class="estrela ativa">&#9733;</span>'; // Estrela acesa
                                        } else {
                                            echo '<span class="estrela">&#9734;</span>'; // Estrela apagada
                                        }
                                    }
                                    ?>
                                </div>
                                <p class="card-text font-weight-bolder fs-2">Rua: <?php echo htmlspecialchars($hospedagem['ruaHospedagem']); ?></p>
                                <p class="card-text"><small class="text-muted">Detalhes da Hospedagem</small></p>
                                <div class="inside-box p-2">
                                    <p class="card-text"><small class="text-muted"><?php echo htmlspecialchars($hospedagem['tpHospedagem']); ?></small></p>
                                    <p class="card-text"><small class="text-muted"><strong>Serviços inclusos:</strong></small></p>
                                    <div class="d-flex col">
                                    <?php if (!empty($hospedagem['servicos'])) { ?>
                                    <?php foreach ($hospedagem['servicos'] as $servico) { ?>
                                        <p class="card-text mr-1 mb-1"><small class="text-muted"><?php echo htmlspecialchars($servico['nmServico']);?>.</small></p>
                                    <?php } ?>
                                    <?php } else { ?>
                                    <p class="card-text"><small class="text-muted">Nenhum serviço incluído</small></p>
                                    <?php } ?>
                                    </div>
                                    <p class="card-text ml-3"><small class="text-muted"><?php echo htmlspecialchars($servico['dsServico']);?></small></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card-body mt-30">
                                <div class="d-flex col justify-content-between">
                                    <p class="card-text">Total do pacote:</p>
                                    <strong>R$ <?php echo number_format($hospedagem['vlHospedagem'], 2, ',', '.'); ?></strong>
                                </div>
                                <div class="d-flex row justify-content-center mb-2">
                                    <a href="" class="btn btn-outline-warning">Selecionar Hotel</a>
                                </div>
                                <div class="d-flex row justify-content-center">
                                    <p class="card-text">Taxas inclusas em até: <strong><?php echo $hospedagem['qtParcelaHospedagem']; ?>X</strong></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
    <?php
} else {
    echo 'Nenhuma hospedagem encontrada para esta cidade.';
}

require_once 'footer.php';

// Função que busca os detalhes do pacote
function DetalhesPacote($pacote) {
    global $con; // Certifique-se de que a conexão está disponível globalmente
    
    $sql = 'SELECT 
                p.cd_pacote, 
                p.ds_periodo, 
                p.ds_acomodacao, 
                p.vl_pacote, 
                p.qt_parcela_pacote, 
                p.url_imagem_pacote, 
                p.st_pacote, 
                c.nm_cidade -- Nome da cidade a partir da tabela tb_cidade
            FROM 
                tb_pacote p
            INNER JOIN 
                tb_cidade c 
            ON 
                p.id_cidade = c.cd_cidade
            WHERE 
                p.cd_pacote = ?';
    $res = $con->prepare($sql);
    
    if ($res === false) {
        die('Erro ao preparar a consulta: ' . $con->error);
    }

    // Vincula o parâmetro da consulta
    if (!$res->bind_param('i', $pacote)) {
        die('Erro ao vincular parâmetros: ' . $res->error);
    }

    // Executa a consulta
    if (!$res->execute()) {
        die('Erro ao executar consulta: ' . $res->error);
    }

    // Liga as colunas do resultado às variáveis
    $res->bind_result($cdPacote, $dsPeriodo, $dsAcomodacao, $vlPacote, $qtParcela, $urlImagem, $stPacote, $nmCidade);

    // Verifica se há resultados e retorna como array
    if ($res->fetch()) {
        return [
            'cdPacote' => $cdPacote,
            'dsPeriodo' => $dsPeriodo,
            'dsAcomodacao' => $dsAcomodacao,
            'vlPacote' => $vlPacote,
            'qtParcela' => $qtParcela,
            'urlImagem' => $urlImagem,
            'stPacote' => $stPacote,
            'nmCidade' => $nmCidade
        ];
    }

    $res->close();
    
    return null;
}

function DetalhesHospedagem($nmCidade) {
    global $con; // Certifique-se de que a conexão está disponível globalmente
    
    $sql = 'SELECT 
                h.cd_hospedagem, 
                h.nm_hotel,
                h.nt_hotel,
                h.rua_hospedagem, 
                h.tp_hospedagem,
                h.vl_hospedagem, 
                h.qt_parcela_hospedagem,
                h.url_imagem_hospedagem, 
                h.st_hospedagem, 
                s.cd_servico,
                s.nm_servico,
                s.ds_servico
            FROM 
                tb_hospedagem h
            INNER JOIN 
                tb_cidade c ON h.id_cidade = c.cd_cidade
            LEFT JOIN 
                tb_hospedagem_servico hs ON h.cd_hospedagem = hs.cd_hospedagem
            LEFT JOIN 
                tb_servico s ON hs.cd_servico = s.cd_servico
            WHERE 
                c.nm_cidade = ?'; // Filtra pela cidade

    $res = $con->prepare($sql);
    
    if ($res === false) {
        die('Erro ao preparar a consulta: ' . $con->error);
    }

    // Vincula o parâmetro da consulta
    if (!$res->bind_param('s', $nmCidade)) {
        die('Erro ao vincular parâmetros: ' . $res->error);
    }

    // Executa a consulta
    if (!$res->execute()) {
        die('Erro ao executar consulta: ' . $res->error);
    }

    // Liga as colunas do resultado às variáveis
    $res->bind_result(
        $cdHospedagem, 
        $nmHotel,
        $ntHotel,
        $ruaHospedagem, 
        $tpHospedagem, 
        $vlHospedagem, 
        $qtParcelaHospedagem, 
        $urlImagemHospedagem, 
        $stHospedagem, 
        $cdServico,
        $nmServico,
        $dsServico
    );

    $resultados = []; // Inicializa um array para armazenar os resultados

    // Verifica se há resultados e armazena todos em um array
    while ($res->fetch()) {
        $hospedagemKey = $cdHospedagem; // Usar o ID da hospedagem como chave

        if (!isset($resultados[$hospedagemKey])) {
            // Se a hospedagem ainda não foi adicionada, inicialize-a
            $resultados[$hospedagemKey] = [
                'nmHotel' => $nmHotel,
                'ntHotel'=>$ntHotel,
                'ruaHospedagem' => $ruaHospedagem,
                'tpHospedagem' => $tpHospedagem,
                'vlHospedagem' => $vlHospedagem,
                'qtParcelaHospedagem' => $qtParcelaHospedagem,
                'urlImagemHospedagem' => $urlImagemHospedagem,
                'stHospedagem' => $stHospedagem,
                'servicos' => [] // Inicializa o array de serviços
            ];
        }

        // Adiciona o serviço à hospedagem
        $resultados[$hospedagemKey]['servicos'][] = [
            'cdServico' => $cdServico,
            'nmServico' => $nmServico,
            'dsServico' => $dsServico
        ];
    }

    $res->close();
    
    return array_values($resultados); // Retorna todos os resultados
}
?>
</body>