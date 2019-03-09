<?php echo $this->render('templates/header.html',NULL,get_defined_vars(),0); ?>

    <div class="container">
        <h1>Login</h1>

        <?php if ($error_message): ?>
            <p><?= ($error_message) ?></p>
        <?php endif; ?>

        <form action="/login" method="post">
            <p>
                <label for="email" class="label block">Email</label>
                <input type="email" id="email" name="email" class="field field-visible">
            </p>

            <p>
                <label for="pw" class="label block">Password</label>
                <input type="text" id="pw" name="pw" class="field field-visible">
            </p>

            <input type="hidden" name="username">
            <input type="hidden" name="csrf" value="<?= ($CSRF) ?>">

            <p><button type="submit">Log In</button></p>
        </form>

        <p><a href="/register">Create an account</a></p>
    </div>

<?php echo $this->render('templates/footer.html',NULL,get_defined_vars(),0); ?>