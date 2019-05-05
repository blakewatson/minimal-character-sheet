<?php echo $this->render('templates/header.html',NULL,get_defined_vars(),0); ?>

    <div class="container container-center-center">
        <div class="inner">
            <h1>Login</h1>
    
            <?php if ($error_message): ?>
                <p><?= ($error_message) ?></p>
            <?php endif; ?>

            <?php if ($failed_confirmation): ?>
                <p>You must confirm your account before you can log in. Please check your email. Never received the email? <a href="/register/confirm/send">Click here to send a new confirmation email</a>.</p>
            <?php endif; ?>
    
            <form action="/login" method="post">
                <p>
                    <label for="email" class="label block">Email</label>
                    <input type="email" id="email" name="email" class="field field-visible">
                </p>
    
                <p>
                    <label for="pw" class="label block">Password</label>
                    <input type="password" id="pw" name="pw" class="field field-visible">
                </p>
    
                <input type="hidden" name="username">
                <input type="hidden" name="csrf" value="<?= ($CSRF) ?>">
    
                <p><button type="submit" class="button-primary">Log In</button></p>
            </form>
    
            <p><a href="/register">Create an account</a></p>
        </div>
    </div>

<?php echo $this->render('templates/footer.html',NULL,get_defined_vars(),0); ?>