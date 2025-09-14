<?php 
    require_once 'header.php';
?>
<style>
    .card{border: none;}
    .card{margin-top:50%}
    input[type=email], input[type=password]{
        border-left: none;
        border-right: none;
        border-top: none;
        border-radius: 0px 0px 0px 0px;
    }
    @media(max-width:540px){
        .card{margin-top:-170%}
    }
</style>
<body>
    <div class ="container-fluid">
        <div class="row">
            <div class="col-sm-8" id="fundo"></div>
            <div class="col-sm-4">
                <div class="card">
                    <div class="card-body">
                        <h1 class="font-tittle">Make Travels</h1>
                        <form method="post" action="validar.php" class="form-group">
                            <input type="email" name="email" class="form-control" placeholder="Seu e-mail"
                            required>
                            <br>
                            <input type="password" name="senha" class="form-control" placeholder="Sua senha"
                            required>
                            <br>
                            <input type="submit" name="action" class="btn btn-outline-primary btn-block" 
                            value="Entrar">
                        </form>
                    </div>
                </div>
            </div>
            </div>
</body>
