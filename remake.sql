DROP DATABASE IF EXISTS db_controle_estoque;
CREATE DATABASE db_controle_estoque;
USE db_controle_estoque;
SET SQL_SAFE_UPDATES = 1;

-- Tabela de usuários (mantida com pequenas alterações)
CREATE TABLE tb_usuario (
  cd_usuario INT PRIMARY KEY AUTO_INCREMENT,
  nm_usuario VARCHAR(80) NOT NULL,
  nm_email VARCHAR(80) NOT NULL UNIQUE,
  cd_senha VARCHAR(128) NOT NULL,
  dt_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO tb_usuario SET
  nm_usuario = 'admin',
  nm_email = 'admin@estoque.com',
  cd_senha = SHA2('123', 256);

-- Tabela de categorias (substitui tb_cidade)
CREATE TABLE tb_categoria (
  cd_categoria INT AUTO_INCREMENT PRIMARY KEY,
  nm_categoria VARCHAR(80) NOT NULL
);

INSERT INTO tb_categoria SET cd_categoria = '1', nm_categoria = 'Doces';
INSERT INTO tb_categoria SET cd_categoria = '2', nm_categoria = 'Bebidas';
INSERT INTO tb_categoria SET cd_categoria = '3', nm_categoria = 'Salgadinhos';
INSERT INTO tb_categoria SET cd_categoria = '4', nm_categoria = 'Outros';

-- Tabela de produtos (substitui tb_pacote)
CREATE TABLE tb_produto (
  cd_produto INT AUTO_INCREMENT PRIMARY KEY,
  id_categoria INT NOT NULL,
  nm_produto VARCHAR(100) NOT NULL,
  ds_produto LONGTEXT NOT NULL,
  vl_produto DECIMAL(8,2) NOT NULL,
  qt_estoque INT NOT NULL DEFAULT 0,
  dt_validade DATE,
  url_imagem_produto VARCHAR(80),
  st_produto CHAR(1) NOT NULL DEFAULT "1",
  FOREIGN KEY (id_categoria) REFERENCES tb_categoria(cd_categoria)
);

-- Tabela de fornecedores (substitui tb_hospedagem)
CREATE TABLE tb_fornecedor (
  cd_fornecedor INT AUTO_INCREMENT PRIMARY KEY,
  nm_fornecedor VARCHAR(100) NOT NULL,
  nr_avaliacao INT NOT NULL DEFAULT 0,
  ds_endereco VARCHAR(100) NOT NULL,
  nr_telefone VARCHAR(20) NOT NULL,
  ds_email VARCHAR(80) NOT NULL,
  st_fornecedor CHAR(1) NOT NULL DEFAULT "1"
);

-- Tabela de movimentações (nova)
CREATE TABLE tb_movimentacao (
  cd_movimentacao INT AUTO_INCREMENT PRIMARY KEY,
  id_produto INT NOT NULL,
  tp_movimentacao ENUM('entrada', 'saida') NOT NULL,
  qt_movimentacao INT NOT NULL,
  dt_movimentacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  id_usuario INT NOT NULL,
  ds_observacao TEXT,
  FOREIGN KEY (id_produto) REFERENCES tb_produto(cd_produto),
  FOREIGN KEY (id_usuario) REFERENCES tb_usuario(cd_usuario)
);

-- Tabela de compras (nova)
CREATE TABLE tb_compra (
  cd_compra INT AUTO_INCREMENT PRIMARY KEY,
  id_fornecedor INT NOT NULL,
  dt_compra DATE NOT NULL,
  vl_total DECIMAL(10,2) NOT NULL,
  st_compra ENUM('pendente', 'finalizada', 'cancelada') NOT NULL DEFAULT 'pendente',
  FOREIGN KEY (id_fornecedor) REFERENCES tb_fornecedor(cd_fornecedor)
);

-- Tabela de itens da compra (nova)
CREATE TABLE tb_item_compra (
  cd_item INT AUTO_INCREMENT PRIMARY KEY,
  id_compra INT NOT NULL,
  id_produto INT NOT NULL,
  qt_item INT NOT NULL,
  vl_unitario DECIMAL(8,2) NOT NULL,
  FOREIGN KEY (id_compra) REFERENCES tb_compra(cd_compra),
  FOREIGN KEY (id_produto) REFERENCES tb_produto(cd_produto)
);
