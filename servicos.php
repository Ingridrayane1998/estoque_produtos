<?php require_once  'function.php';
?>

<section id="servicos">
    <div class="row">
        <div class="col-sm-12" style="padding: 3% 3% ">
            <h2 class="font-tittle text-center">
            Serviços Disponiveis
            </h2>
        </div>
    </div>
    <div class="row">
        <?php 
              $listar = ListarServicos();
              if($listar){
                foreach($listar as $l){

                  
        ?>
        <div class="col-sm-4">
        <div class="card text-center mx-auto">
        <img src="img/servico/<?php echo $l['url_imagem_servico'];?>" alt="" class="card-img">
            <div class="card-body">
            <h3 class= "font-weight-bolder">
                    <?php echo $l['nm_servico']; ?>
                    </h3>
                    <strong>Descrição:</strong>
                    <?php echo $l['ds_servico']; ?>
                    <br>
                    <br>
                <a href="#" class="btn btn-outline-success">ver mais</a>
            </div>
        </div>
        </div>


        
    <?php
           }     
        }
    ?>
    </div>
</section>