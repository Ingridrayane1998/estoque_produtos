<?php
require_once 'conect.php';

// Verificar se foi fornecido um ID de produto
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_produto = intval($_GET['id']);
    
    // Buscar a imagem do produto no banco de dados
    $sql = "SELECT img_produto, tipo_img_produto FROM tb_produto WHERE cd_produto = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param('i', $id_produto);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Verificar se a imagem existe
        if ($row['img_produto']) {
            // Definir o cabeçalho para o tipo de imagem
            header("Content-Type: " . $row['tipo_img_produto']);
            
            // Enviar a imagem
            echo $row['img_produto'];
            exit;
        }
    }
}

// Se a imagem não for encontrada ou não existir, exibir uma imagem padrão
$imagem_padrao = 'img/no-image.png';
if (file_exists($imagem_padrao)) {
    $tipo = mime_content_type($imagem_padrao);
    header("Content-Type: " . $tipo);
    readfile($imagem_padrao);
} else {
    // Se nem a imagem padrão existir, enviar um cabeçalho 404
    header("HTTP/1.0 404 Not Found");
    echo "Imagem não encontrada";
}
?>
