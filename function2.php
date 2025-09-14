<?php
require_once 'conect.php';

function ListarPacotes(){
    $sql = 'SELECT 
  p.cd_pacote, 
  p.ds_periodo, 
  p.ds_acomodacao, 
  p.vl_pacote, 
  p.qt_parcela_pacote, 
  p.url_imagem_pacote, 
  p.st_pacote, 
  p.ic_active,
  c.nm_cidade -- Nome da cidade a partir da tabela tb_cidade
FROM 
  tb_pacote p
INNER JOIN 
  tb_cidade c 
ON 
  p.id_cidade = c.cd_cidade;';
    $res = $GLOBALS['con']->query($sql);
    if($res->num_rows > 0){
        return $res;
    }
  }

  function ListarServicos(){
    $sql = 'select 
    cd_servico,
    nm_servico,
    ds_servico,
    url_imagem_servico
    from 
    tb_servico';
    $res = $GLOBALS['con']->query($sql);
    if($res->num_rows > 0){
      return $res;
    }
  }

  function ListarCarousel(){
    $sql = 'select 
    url_imagem_carousel,
    st_carousel,
    ic_active
    from tb_carousel';
    $res =$GLOBALS['con']->query($sql);
    if($res->num_rows > 0){
      return $res;
    }
  }
?>