<?php echo $this->render('templates/header.html',NULL,get_defined_vars(),0); ?>

<div class="container">
    <?php if ($success): ?>
        <h1>Account confirmed</h1>
        <p>You can now <a href="/login">log in to your account</a>.</p>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <h1><?= ($error_message) ?></h1>
    <?php endif; ?>
</div>

<?php echo $this->render('templates/footer.html',NULL,get_defined_vars(),0); ?>