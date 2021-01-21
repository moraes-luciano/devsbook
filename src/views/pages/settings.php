
<?=$render('header',['loggedUser'=> $loggedUser]);?> 

    
    <section class="container main">
       
        <?=$render('sidebar',['activeMenu'=>'config']);?>

        <div class='configOptions'>

 
        <form method='Post' action="<?=$base;?>/config">
            
            <h3>Configurações</h3>
            
            <div class="option file">
                <label>Nova foto do perfil</label>
                <input type="file">
            </div>

            <div class="option file">
                <label>Nova foto da capa</label>
                <input type="file">
            </div>
      
            <hr>

            <div class="option data">
                <label>Nome</label>
                <input type="text" name="name" placeholder="<?=$loggedUser->name;?>">
            </div>

            <div class="option data">
                <label>E-mail</label>
                <input type="email" name="email" placeholder="<?=$loggedUser->email;?>">
               
            </div>

            <div class="option data">
                <label>Data de nascimento
                    <?php if(!empty($flash['flash_birthdate'])): ?>
                        
                        <span class="flash"><?php echo $flash['flash_birthdate']; ?></span>
    
                    <?php endif; ?>
                </label>
                <input type="text" id="birthdate" name="birthdate" placeholder="<?=date('d/m/Y', strtotime($loggedUser->birthdate));?>">
            </div>

            <div class="option data">
                <label>Cidade</label>
                <?php
                    if(empty($loggedUser->city)){
                        $loggedUser->city='Pendente';
                    }
                ?>

                <input type="text" name="city" placeholder="<?=$loggedUser->city;?>">
            </div>


            <div class="option data">
                <label>Trabalho</label>
                <?php
                    if(empty($loggedUser->work)){
                        $loggedUser->work='Pendente';
                    }
                ?>
                <input type="text" name="work" placeholder="<?=$loggedUser->work;?>">
            </div>

            <hr>
            <h4>Mudar senha?</h4>

            <div class="option data">
                <label>Nova senha</label>
                <input type="text" name="newPassword">
            </div>

            <div class="option data">
                
                <label>Senha atual para confirmar
                    
                    <?php if(!empty($flash['flash_password'])): ?>
                                
                        <span class="flash"><?php echo $flash['flash_password']; ?></span>
    
                    <?php endif; ?>

                </label>
                <input type="text" name="password">
  
            </div>

            <hr>
            <br>

            <button class="button" type="submit">Apply changes</button>
       
       </form>
        
    </section>

    <script src="https://unpkg.com/imask"></script>
    
    <script>
        IMask(       
            document.getElementById('birthdate'),
            {
                mask:'00/00/0000'
            }
        );
    </script>
       