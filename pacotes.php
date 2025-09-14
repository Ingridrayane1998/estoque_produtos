<?php
require_once 'function.php';
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

<section id="pacotes">
    <div class="row">
        <div class="col-sm-12" style="padding: 3% 3% " >
            <h2 class="font-tittle text-center">
                Pacotes de Viagem
            </h2>
        </div>
    </div>
    <div class="row">
    <?php
            $listar = ListarPacotes();
            if($listar){
                foreach($listar as $l){
                    $st = '';
                    if($l['st_pacote'] == "0"){
                        // $st = 'hide'; // Aplica a classe CSS hide e retire o continue
                        continue; // a instrução continue faz com que o loop foreach pule para a próxima iteração, ignorando todo o código que renderiza o card.
                    }
            ?>
    <div class="col-sm-3 <?php echo $st;?>">
        <div class="card text-center mx-auto">
            <img src="img/pacotes/<?php echo $l['url_imagem_pacote'];?>" alt="" class="card-img">
            <div class="card-body">
                <h3 class= "font-weight-bolder">
                    <?php echo $l['nm_cidade']; ?>
                    </h3>
                    <strong>Periodo:</strong>
                    <?php echo $l['ds_periodo']; ?>
                    <br>
                    <strong>Acomodação:</strong>
                    <?php echo $l['ds_acomodacao']; ?>
                    <br>
                    <strong style="font-size: 25pt;"> R$ <?php echo $l['vl_pacote']; ?> </strong>(dia/pessoa)<br>
                    <strong>Parcele em até
                    <?php echo $l['qt_parcela_pacote']; ?>x
                    sem juros
                    </strong>
                    <br>
                    <br>
                    <a href="ver_pacote.php?pacote=<?php echo $l['cd_pacote']; ?>" class="btn btn-outline-success">ver mais</a>
            </div>    
        </div>
    </div>
    <?php
            }
        }
    ?>
   
        
  </div>
</div>
        
    </div>
    
</section>






<!-- Write your comments here 
<div class="card" style="width: 18rem;">
            <img src="" class="card-img-top" alt="...">
        <div class="card-body">
            <h5 class="card-title">Card title</h5>
        <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
        <a href="#" class="btn btn-primary">Go somewhere</a>
        -->