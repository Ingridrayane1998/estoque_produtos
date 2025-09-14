<?php
  require_once 'conect.php';
  require_once 'header.php';
    if($_POST){
      ValidarLogin($_POST['email'], $_POST['senha']);
    }
    function ValidarLogin ($email, $senha){
      $sql = 'select
              cd_usuario, nm_usuario
              from tb_usuario
              where
              nm_email = "'.$email.'" and
              cd_senha = sha2("'.$senha.'",256)';
      $stmt = $GLOBALS['con']->query($sql);

      if($stmt->num_rows > 0){
          session_start();
          $r =$stmt->fetch_array();

          $_SESSION['id'] =$r['cd_usuario'];
          $_SESSION['usuario'] =$r['nm_usuario'];
          Confirma("Bem Vindo " .$_SESSION['usuario'], "./painel/index.php");
      }
      else{
        Erro("Ops! Usuário e/ou senha inválidos!","login.php");
      }  
    }

    function Confirma($msg, $pagina){
       print'
            <div class="modal fade" id="myModal" data-backdrop="static">
                <div class="modal-dialog modal-md">
                    <div class="modal-content">
                        <div class="modal-body text-center font-weight-bolder text-success">
                        <i class="bi bi-check2-circle"></i>
                        <br>
                        <h3>'.$msg.'<h3>
                        </div>
                        <div class="modal-footer">
                          <button class="btn btn-success btn-block mx-auto"
                            onclick="redirecionar()">OK</button>
                        </div>
                    </div>
                </div>
            </div>
            <script>
              function redirecionar(){
                location.href = "'.$pagina.'";
              } 
            </script>
            ';
    }

    function Erro($msg){
      print'
           <div class="modal fade" id="myModal" data-backdrop="static">
               <div class="modal-dialog modal-md">
                   <div class="modal-content">
                       <div class="modal-body text-center font-weight-bolder text-danger">
                       <i class="bi bi-x-circle"></i>
                       <br>
                       <h3>'.$msg.'<h3>
                       </div>
                       <div class="modal-footer">
                         <button class="btn btn-danger btn-block mx-auto"
                           onclick="redirecionar()">OK</button>
                       </div>
                   </div>
               </div>
           </div>
           <script>
             function redirecionar(){
               history.go(-1);
              }
           </script>
           ';
   }
   require_once 'footer.php';
?>
<style>
  .myModal .modal-body{height:370px;}
  i{font-size: 44p  t;}
</style>






<script>
  $(document).ready(function(){
    $('#myModal').modal('show');
  });

   $('#myModal').on('shown.bs.modal', function(){
    $('#myInput').trigger('focus');
   });
   </script>