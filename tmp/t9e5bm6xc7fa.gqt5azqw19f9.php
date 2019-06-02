<?php echo $this->render('templates/header.html',NULL,get_defined_vars(),0); ?>


<div class="dashboard container container-slim">
    <h1>My Characters</h1>

    <?php if ($sheets): ?>
        <?php else: ?>
            <p>Nothing to see here. Create a character by clicking the link below.</p>
        
    <?php endif; ?>

    <?php foreach (($sheets?:[]) as $sheet): ?>
        <p><a href="/sheet/<?= ($sheet['id']) ?>">
            <?php if ($sheet['name']): ?>
                <?= ($sheet['name']) ?>
                <?php else: ?>Untitled Character
            <?php endif; ?>
        </a></p>
    <?php endforeach; ?>

    <p><a href="/add-sheet" class="button-primary">Add a character sheet</a></p>

    <br><br>

    <h1>Settings</h1>

    <p>
        <label for="settingDarkMode">Dark Mode (experimental)</label>:
        <input id="settingDarkMode" type="checkbox">
    </p>

    <div class="account-actions">
        <p><a href="/logout">Logout</a></p>
    </div>
</div>

<?php echo $this->render('templates/footer.html',NULL,get_defined_vars(),0); ?>