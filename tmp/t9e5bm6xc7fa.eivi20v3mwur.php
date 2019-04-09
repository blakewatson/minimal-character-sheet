<?php echo $this->render('templates/header.html',NULL,get_defined_vars(),0); ?>

    <div class="container container-center-center">
        <div class="inner">
            <h1>Register</h1>
    
            <?php if (isset( $success_message )): ?>
                
                    <p><?= ($success_message) ?></p>
                
    
                <?php else: ?>
                    <?php if (isset( $error_message )): ?>
                        <p class="danger"><?= ($error_message) ?></p>
                    <?php endif; ?>
                    
                    <form action="/register" method="post">
                        <p>
                            <label for="user" class="label block">Username</label>
                            <input type="text" id="user" name="user" class="field field-visible">
                        </p>
    
                        <p>
                            <label for="email" class="label block">Email</label>
                            <input type="email" id="email" name="email" class="field field-visible">
                        </p>
    
                        <p>
                            <label for="pw1" class="label block">Password</label>
                            <input type="text" id="pw1" name="pw1" class="field field-visible">
                        </p>
    
                        <p>
                            <label for="pw2" class="label block">Confirm Password</label>
                            <input type="text" id="pw2" name="pw2" class="field field-visible">
                        </p>
    
                        <input type="hidden" name="phone">
                        <input type="hidden" name="csrf" value="<?= ($CSRF) ?>">
    
                        <p><button type="submit" class="button-primary">Create User</button></p>
                    </form>
                
            <?php endif; ?>
        </div>
    </div>

<?php echo $this->render('templates/footer.html',NULL,get_defined_vars(),0); ?>