    

    <?php if ($app): ?>
        <script src="//cdn.quilljs.com/1.3.6/quill.min.js"></script>
        <script>window.sheet = "<?= ($this->raw($sheet)) ?>";</script>
        <script src="<?= ($BASE) ?>/dist/app.js"></script>
    <?php endif; ?>

</body>

</html>
