<?php echo $this->render('templates/header.html',NULL,get_defined_vars(),0); ?>

<div class="container container-center-center">
    <div class="inner">
        <?php if (isset( $success_message )): ?>
            
                <p><?= ($success_message) ?></p>
            
    
            <?php else: ?>
                <h1>Send confirmation email</h1>
    
                <form action="/register/confirm/send" method="post">
                    <p>
                        <label for="email" class="label block">Email</label>
                        <input type="email" id="email" name="email" class="field field-visible">
                    </p>
    
                    <input type="hidden" name="username">
                    <input type="hidden" name="csrf" value="<?= ($CSRF) ?>">
    
                    <p><button type="submit" class="button-primary">Log In</button></p>
                </form>
            
        <?php endif; ?>
    </div>
</div>

<?php echo $this->render('templates/footer.html',NULL,get_defined_vars(),0); ?>