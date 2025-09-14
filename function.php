<?php
require_once 'conect.php';

function ListarProdutos($categoria = null) {
    $sql = 'SELECT 
        p.cd_produto,
        p.nm_produto,
        p.ds_produto,
        p.vl_produto,
        p.qt_estoque,
        p.dt_validade,
        p.url_imagem_produto,
        p.st_produto,
        c.nm_categoria
    FROM 
        tb_produto p
    INNER JOIN 
        tb_categoria c ON p.id_categoria = c.cd_categoria';
    
    if ($categoria) {
        $sql .= ' WHERE c.cd_categoria = ' . $categoria;
    }
    
    $res = $GLOBALS['con']->query($sql);
    if ($res->num_rows > 0) {
        return $res;
    }
    return false;
}

function ListarCategorias() {
    $sql = 'SELECT 
        cd_categoria,
        nm_categoria
    FROM 
        tb_categoria
    ORDER BY 
        nm_categoria';
    
    $res = $GLOBALS['con']->query($sql);
    if ($res->num_rows > 0) {
        return $res;
    }
    return false;
}

function ListarCargos() {
    $sql = 'SELECT 
        cd_cargo,
        nm_cargo
    FROM 
        tb_cargo
    ORDER BY 
        nm_cargo';
    
    $res = $GLOBALS['con']->query($sql);
    if ($res->num_rows > 0) {
        return $res;
    }
    return false;
}

function ListarUsuarios() {
    $sql = 'SELECT 
        u.cd_usuario,
        u.nm_usuario,
        u.nm_email,
        c.nm_cargo,
        u.dt_registro
    FROM 
        tb_usuario u
    INNER JOIN 
        tb_cargo c ON u.id_cargo = c.cd_cargo
    ORDER BY 
        u.nm_usuario';
    
    $res = $GLOBALS['con']->query($sql);
    if ($res->num_rows > 0) {
        return $res;
    }
    return false;
}

function ListarFornecedores() {
    $sql = 'SELECT 
        cd_fornecedor,
        nm_fornecedor,
        nr_avaliacao,
        ds_endereco,
        nr_telefone,
        ds_email,
        st_fornecedor
    FROM 
        tb_fornecedor
    ORDER BY
        nm_fornecedor';
    
    $res = $GLOBALS['con']->query($sql);
    if ($res->num_rows > 0) {
        return $res;
    }
    return false;
}

function ListarMovimentacoes() {
    $sql = 'SELECT 
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
    ORDER BY 
        m.dt_movimentacao DESC';
    
    $res = $GLOBALS['con']->query($sql);
    if ($res->num_rows > 0) {
        return $res;
    }
    return false;
}

function ListarRequisicoes() {
    $sql = 'SELECT 
        r.cd_requisicao,
        u.nm_usuario,
        r.dt_requisicao,
        r.st_requisicao,
        r.ds_observacao
    FROM 
        tb_requisicao r
    INNER JOIN 
        tb_usuario u ON r.id_usuario = u.cd_usuario
    ORDER BY 
        r.dt_requisicao DESC';
    
    $res = $GLOBALS['con']->query($sql);
    if ($res->num_rows > 0) {
        return $res;
    }
    return false;
}

function DetalhesProduto($produto) {
    $sql = 'SELECT 
        p.cd_produto,
        p.nm_produto,
        p.ds_produto,
        p.vl_produto,
        p.qt_estoque,
        p.dt_validade,
        p.url_imagem_produto,
        p.st_produto,
        c.nm_categoria,
        c.cd_categoria
    FROM 
        tb_produto p
    INNER JOIN 
        tb_categoria c ON p.id_categoria = c.cd_categoria
    WHERE 
        p.cd_produto = ?';
    
    $stmt = $GLOBALS['con']->prepare($sql);
    $stmt->bind_param('i', $produto);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}

function DetalhesRequisicao($requisicao) {
    $sql = 'SELECT 
        r.cd_requisicao,
        u.nm_usuario,
        r.dt_requisicao,
        r.st_requisicao,
        r.ds_observacao
    FROM 
        tb_requisicao r
    INNER JOIN 
        tb_usuario u ON r.id_usuario = u.cd_usuario
    WHERE 
        r.cd_requisicao = ?';
    
    $stmt = $GLOBALS['con']->prepare($sql);
    $stmt->bind_param('i', $requisicao);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $req = $result->fetch_assoc();
        
        // Buscar os itens da requisição
        $sql = 'SELECT 
            i.cd_item,
            p.nm_produto,
            i.qt_item,
            p.cd_produto
        FROM 
            tb_item_requisicao i
        INNER JOIN 
            tb_produto p ON i.id_produto = p.cd_produto
        WHERE 
            i.id_requisicao = ?';
        
        $stmt = $GLOBALS['con']->prepare($sql);
        $stmt->bind_param('i', $requisicao);
        $stmt->execute();
        $itens = $stmt->get_result();
        
        $req['itens'] = [];
        while ($item = $itens->fetch_assoc()) {
            $req['itens'][] = $item;
        }
        
        return $req;
    }
    
    return null;
}

function AdicionarMovimentacao($produto, $tipo, $quantidade, $usuario, $observacao = '') {
    $sql = 'INSERT INTO tb_movimentacao (
        id_produto, 
        tp_movimentacao, 
        qt_movimentacao, 
        id_usuario, 
        ds_observacao
    ) VALUES (?, ?, ?, ?, ?)';
    
    $stmt = $GLOBALS['con']->prepare($sql);
    $stmt->bind_param('isiss', $produto, $tipo, $quantidade, $usuario, $observacao);
    
    if ($stmt->execute()) {
        // Atualiza o estoque do produto
        if ($tipo == 'entrada') {
            $sqlUpdate = 'UPDATE tb_produto SET qt_estoque = qt_estoque + ? WHERE cd_produto = ?';
        } else {
            $sqlUpdate = 'UPDATE tb_produto SET qt_estoque = qt_estoque - ? WHERE cd_produto = ?';
        }
        
        $stmtUpdate = $GLOBALS['con']->prepare($sqlUpdate);
        $stmtUpdate->bind_param('ii', $quantidade, $produto);
        return $stmtUpdate->execute();
    }
    
    return false;
}

function AdicionarProduto($nome, $descricao, $categoria, $valor, $estoque, $validade, $imagem) {
    $sql = 'INSERT INTO tb_produto (
        nm_produto,
        ds_produto,
        id_categoria,
        vl_produto,
        qt_estoque,
        dt_validade,
        url_imagem_produto,
        st_produto
    ) VALUES (?, ?, ?, ?, ?, ?, ?, "1")';
    
    $stmt = $GLOBALS['con']->prepare($sql);
    $stmt->bind_param('ssidiss', $nome, $descricao, $categoria, $valor, $estoque, $validade, $imagem);
    
    return $stmt->execute();
}

function AtualizarProduto($id, $nome, $descricao, $categoria, $valor, $estoque, $validade, $imagem, $status) {
    $sql = 'UPDATE tb_produto SET
        nm_produto = ?,
        ds_produto = ?,
        id_categoria = ?,
        vl_produto = ?,
        qt_estoque = ?,
        dt_validade = ?,
        url_imagem_produto = ?,
        st_produto = ?
    WHERE cd_produto = ?';
    
    $stmt = $GLOBALS['con']->prepare($sql);
    $stmt->bind_param('ssidisssi', $nome, $descricao, $categoria, $valor, $estoque, $validade, $imagem, $status, $id);
    
    return $stmt->execute();
}

function CriarRequisicao($usuario, $observacao, $itens) {
    // Iniciar transação
    $GLOBALS['con']->begin_transaction();
    
    try {
        // Inserir a requisição
        $sql = 'INSERT INTO tb_requisicao (
            id_usuario,
            ds_observacao,
            st_requisicao
        ) VALUES (?, ?, "pendente")';
        
        $stmt = $GLOBALS['con']->prepare($sql);
        $stmt->bind_param('is', $usuario, $observacao);
        $stmt->execute();
        
        $idRequisicao = $GLOBALS['con']->insert_id;
        
        // Inserir os itens da requisição
        $sql = 'INSERT INTO tb_item_requisicao (
            id_requisicao,
            id_produto,
            qt_item
        ) VALUES (?, ?, ?)';
        
        $stmt = $GLOBALS['con']->prepare($sql);
        
        foreach ($itens as $item) {
            $stmt->bind_param('iii', $idRequisicao, $item['produto'], $item['quantidade']);
            $stmt->execute();
        }
        
        // Confirmar a transação
        $GLOBALS['con']->commit();
        return true;
    } catch (Exception $e) {
        // Reverter a transação em caso de erro
        $GLOBALS['con']->rollback();
        return false;
    }
}
