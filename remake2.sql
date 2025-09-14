drop database if exists db_remake_travel;
create database db_remake_travel;

use db_remake_travel;

SET SQL_SAFE_UPDATES=1;

create table tb_usuario (
cd_usuario int primary key auto_increment,
nm_usuario varchar(80) not null,
nm_email varchar(80) not null unique,
cd_senha varchar(128) not null,
dt_registro datetime not null default current_timestamp
);

insert into tb_usuario set
nm_usuario = 'ricardo',
nm_email= 'ricardo@gmail.com',
cd_senha = sha2('123',256);

create table tb_carousel (
cd_carousel int auto_increment primary key,
url_imagem_carousel varchar(100) not null,
ds_carousel varchar(120),
ic_active char(1) ,
st_carousel char(1) not null
);

insert into tb_carousel set
cd_carousel = '1',
ds_carousel = 'Ubatuba',
url_imagem_carousel = 'ubatuba.jpg',
st_carousel = '1';

insert into tb_carousel set
cd_carousel = '2',
ds_carousel = 'Caraguatatuba',
url_imagem_carousel = 'caragua.jpg',
st_carousel = '1';

insert into tb_carousel set
cd_carousel = '3',
ds_carousel = 'São Sebastião',
url_imagem_carousel = 'sao-sebastiao.jpg',
st_carousel = '1';

insert into tb_carousel set
cd_carousel = '4',
ds_carousel = 'Ilhabela',
url_imagem_carousel = 'ilhabela.jpg',
st_carousel = '1';

create table tb_cidade(
	cd_cidade int auto_increment primary key,
	nm_cidade varchar(80) not null
);	

insert into tb_cidade set
cd_cidade = '1',
nm_cidade = 'Ubatuba';

insert into tb_cidade set
cd_cidade = '2',
nm_cidade = 'Caraguatatuba';

insert into tb_cidade set
cd_cidade = '3',
nm_cidade = 'São Sebatião';

insert into tb_cidade set
cd_cidade = '4',
nm_cidade = 'Ilhabela';

create table tb_pacote (
  cd_pacote int auto_increment primary key,
  id_cidade int not null,
  ds_periodo varchar(80) not null,
  ds_acomodacao longtext not null,
  vl_pacote decimal(8,2) not null,
  qt_parcela_pacote int not null,
  url_imagem_pacote varchar(80) not null,
  st_pacote char(1) not null default "1",
  ic_active char(1),
  foreign key (id_cidade) references tb_cidade(cd_cidade)
);

insert into tb_pacote set
cd_pacote = '1',
id_cidade = '1',
ds_periodo = '7 Dias e 6 Noites',
ds_acomodacao = 'hotel 5 estrelas, pensão completa',
vl_pacote = '250',
qt_parcela_pacote = '12',
url_imagem_pacote = '0006ef1069b42e48e4216faae865cd04.jpg',
st_pacote = '1';

insert into tb_pacote set
cd_pacote = '2',
id_cidade = '2',
ds_periodo = '2 Dias e 1 Noites',
ds_acomodacao = 'hotel 2 estrelas',
vl_pacote = '100',
qt_parcela_pacote = '18',
url_imagem_pacote = 'a0d1c6b2a861126335d26b56f89deaa8.jpg',
st_pacote = '1';

insert into tb_pacote set
cd_pacote = '3',
id_cidade = '3',
ds_periodo = '4 Dias e 3 Noites',
ds_acomodacao = 'hotel 5 estrelas, pensão completa',
vl_pacote = '200',
qt_parcela_pacote = '12',
url_imagem_pacote = 'a096abfaa21eb2964dbd67b7203e25f2.jpg',
st_pacote = '1';

insert into tb_pacote set
cd_pacote = '4',
id_cidade = '4',
ds_periodo = '3 Dias e 2 Noites',
ds_acomodacao = 'hotel 3 estrelas, pensão completa',
vl_pacote = '200',
qt_parcela_pacote = '12',
url_imagem_pacote = 'c9c8c08fd8b499b11a6b7acfd5f0517a.jpg',
st_pacote = '1';

create table tb_hospedagem(
   cd_hospedagem int auto_increment primary key,
   id_cidade int not null,
   nm_hotel varchar(100) not null,
   nt_hotel varchar(100) not null,
   rua_hospedagem varchar(100) not null,
   tp_hospedagem varchar(100) not null,
   vl_hospedagem decimal(8,2) not null,
   qt_parcela_hospedagem int not null,
   en_hospedagem date not null,
   sd_hospedagem date not null,
   url_imagem_hospedagem varchar(80) not null,
   st_hospedagem char(1) not null default "1",
   foreign key (id_cidade) references tb_cidade(cd_cidade)
);

 insert into tb_hospedagem set
 cd_hospedagem = '1',
 id_cidade = '1',
 nm_hotel = 'Costa Azul Pousada',
 nt_hotel = '4',
 rua_hospedagem = 'Avenida Praia do Sol',
 tp_hospedagem = 'Suíte com vista para o mar',
 vl_hospedagem = '980',
 qt_parcela_hospedagem = '5',
 en_hospedagem = '2024-11-22',
 sd_hospedagem = '2024-11-25',
 url_imagem_hospedagem = 'd2391b17142da8c5ff9055acb4e0d4d5.jpg',
 st_hospedagem = '1';

 insert into tb_hospedagem set
 cd_hospedagem = '2',
 id_cidade = '2',
 nm_hotel = 'Pousada do Mar Encantado',
 nt_hotel = '3',
 rua_hospedagem = 'Avenida das Palmeiras',
 tp_hospedagem = 'Quarto duplo com vista para o jardim',
 vl_hospedagem = '640',
 qt_parcela_hospedagem = '4',
 en_hospedagem = '2024-11-30',
 sd_hospedagem = '2024-12-02',
 url_imagem_hospedagem = 'd4d1a437ab77ecf1a6f612283ecb90b2.jpg',
 st_hospedagem = '1';
 

 insert into tb_hospedagem set
 cd_hospedagem = '3',
 id_cidade = '3',
 nm_hotel = 'Marina Beach Resort',
 nt_hotel = '5',
 rua_hospedagem = 'Rua dos Coqueiros',
 tp_hospedagem = 'Bangaló deluxe com piscina privada',
 vl_hospedagem = '1450',
 qt_parcela_hospedagem = '6',
 en_hospedagem = '2024-11-26',
 sd_hospedagem = '2024-11-30',
 url_imagem_hospedagem = '4e2ceefad03b0b2861a3668d443dca69.jpg',
 st_hospedagem = '1';

 insert into tb_hospedagem set
 cd_hospedagem = '4',
 id_cidade = '4',
 nm_hotel = 'Ilha Bela Inn',
 nt_hotel = '4',
 rua_hospedagem = 'Praia da Lua Nova',
 tp_hospedagem = 'Suíte master com varanda',
 vl_hospedagem = '870',
 qt_parcela_hospedagem = '8',
 en_hospedagem = '2024-11-23',
 sd_hospedagem = '2024-11-30',
 url_imagem_hospedagem = '2a1591c3d8b54b3312a5abc3a15ffeba.jpg',
 st_hospedagem = '1';
 
create table tb_servico(
  cd_servico int auto_increment primary key,
  nm_servico varchar(60) not null,
  ds_servico longtext not null,
  url_imagem_servico varchar(100) not null
);

insert into tb_servico set
cd_servico = '1',
nm_servico = 'Café da Manhã',
ds_servico = 'Self Service All Incluse, incluso em todas as diárias.',
url_imagem_servico = '47de9dc4443220f0c3ccb0584105df6e.png';

insert into tb_servico set
cd_servico = '2',
nm_servico = 'Almoço',
ds_servico = 'Self Service All Incluse, incluso em todas as diárias.',
url_imagem_servico = '5df86e4a2b24ec3a4299c9a2059b8fb0.webp';

insert into tb_servico set
cd_servico = '3',
nm_servico = 'Jantar',
ds_servico = 'Self Service All Incluse, incluso em todas as diárias.',
url_imagem_servico = 'cc9752223316f10b4fa9810a07a49746.webp';

create table tb_hospedagem_servico(
   cd_hospedagem int not null,
   cd_servico int not null,
   primary key (cd_hospedagem, cd_servico),
   foreign key (cd_hospedagem) references tb_hospedagem(cd_hospedagem),
   foreign key (cd_servico) references tb_servico(cd_servico)
);
INSERT INTO tb_hospedagem_servico (cd_hospedagem, cd_servico)
VALUES (1, 1);

INSERT INTO tb_hospedagem_servico (cd_hospedagem, cd_servico)
VALUES (1, 2);

INSERT INTO tb_hospedagem_servico (cd_hospedagem, cd_servico)
VALUES (1, 3);

INSERT INTO tb_hospedagem_servico (cd_hospedagem, cd_servico)
VALUES (2, 1);

INSERT INTO tb_hospedagem_servico (cd_hospedagem, cd_servico)
VALUES (3, 2);

INSERT INTO tb_hospedagem_servico (cd_hospedagem, cd_servico)
VALUES (4, 3);

