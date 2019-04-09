<?php echo $this->render('templates/header.html',NULL,get_defined_vars(),0); ?>


<div class="container container-slim">
    <h1>My Characters</h1>


    <?php foreach (($sheets?:[]) as $sheet): ?>
        <p><a href="/sheet/<?= ($sheet['id']) ?>"><?= ($sheet['name']) ?></a></p>
    <?php endforeach; ?>

    <p><a href="/add-sheet" class="button-primary">Add a character sheet</a></p>

    <div class="account-actions">
        <p><a href="/logout">Logout</a></p>
    </div>
</div>

<?php echo $this->render('templates/footer.html',NULL,get_defined_vars(),0); ?>