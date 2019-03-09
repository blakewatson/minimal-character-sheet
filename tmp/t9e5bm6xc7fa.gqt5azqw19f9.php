<?php echo $this->render('templates/header.html',NULL,get_defined_vars(),0); ?>

<h1>Special Content</h1>

<div class="container">
    <p><a href="/logout">Logout</a></p>

    <?php foreach (($sheets?:[]) as $sheet): ?>
        <p><a href="/sheet/<?= ($sheet['id']) ?>"><?= ($sheet['name']) ?></a></p>
    <?php endforeach; ?>

    <p><a href="/add-sheet">Add a character sheet</a></p>
</div>

<?php echo $this->render('templates/footer.html',NULL,get_defined_vars(),0); ?>