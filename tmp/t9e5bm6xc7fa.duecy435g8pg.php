<?php echo $this->render('templates/header.html',NULL,get_defined_vars(),0); ?>

    <div id="sheet"></div>

    <input type="hidden" name="sheet_id" id="sheet-id" value="<?= ($sheet_id) ?>">
    <input type="hidden" name="csrf" id="csrf" value="<?= ($CSRF) ?>">

<?php echo $this->render('templates/footer.html',NULL,get_defined_vars(),0); ?>