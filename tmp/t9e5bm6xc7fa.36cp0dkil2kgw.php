<?php echo $this->render('templates/header.html',NULL,get_defined_vars(),0); ?>

<form action="" method="POST">
    <label for="sheet-name" class="label block">What&rsquo;s your character&rsquo;s name?</label>
    <input type="text" class="field field-visible" id="sheet-name" name="sheet_name">
    <button type="submit">Create Sheet</button>
    <input type="hidden" name="csrf" value="<?= ($CSRF) ?>">
</form>

<?php echo $this->render('templates/footer.html',NULL,get_defined_vars(),0); ?>