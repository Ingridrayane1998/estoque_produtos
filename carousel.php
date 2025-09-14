<?php require_once 'function.php'
?>
<style>
    .hide {
    position: absolute; 
    width: 0;           
    height: 0;         
    overflow: hidden;  
    opacity: 0;      
}
</style>    
<link rel="stylesheet" href="./css/style.css">
<div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel">
<ol class="carousel-indicators">
    <?php
    $listar = ListarCarousel();
    $first = true; // Variável para controlar o primeiro item
    if ($listar) {
        foreach ($listar as $l) {
            $st = '';
                    if($l['st_carousel'] == "0"){
                        // $st = 'hide'; // Aplica a classe CSS hide e retire o continue
                        continue; // a instrução continue faz com que o loop foreach pule para a próxima iteração, ignorando todo o código que renderiza o card.
                    }
    ?>
    <li data-target="#carouselExampleIndicators" data-slide-to="<?php echo $l['ic_active']; ?>"
        class="<?php echo $first ? 'active' : ''; ?>"></li>
    <?php
        $first = false; // Depois do primeiro item, o restante não terá a classe 'active'
        }
    }
    ?>
</ol>
<div class="carousel-inner">
    <?php 
    $listar = ListarCarousel();
    $first = true; // Resetar a variável para o controle do primeiro item
    if ($listar) {
        foreach ($listar as $l) {
            $st = '';
                    if($l['st_carousel'] == "0"){
                        // $st = 'hide'; // Aplica a classe CSS hide e retire o continue
                        continue; // a instrução continue faz com que o loop foreach pule para a próxima iteração, ignorando todo o código que renderiza o card.
                    }
    ?>
    <div class="carousel-item <?php echo $first ? 'active' : ''; ?>">
        <img src="img/carousel/<?php echo $l['url_imagem_carousel']; ?>" class="d-block w-100" alt="" id="img-carousel">
    </div>
    <?php
        $first = false; // Depois do primeiro item, remover a classe 'active'
        }
    }
    ?>
</div>
  <button class="carousel-control-prev" type="button" data-target="#carouselExampleIndicators" data-slide="prev">
    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    <span class="sr-only">Previous</span>
  </button>
  <button class="carousel-control-next" type="button" data-target="#carouselExampleIndicators" data-slide="next">
    <span class="carousel-control-next-icon" aria-hidden="true"></span>
    <span class="sr-only">Next</span>
  </button>
</div>