    <script src="//cdn.quilljs.com/1.3.6/quill.min.js"></script>

    <?php if ($sheet): ?>
        <script>window.sheet = "<?= ($sheet) ?>";</script>
        <script src="<?= ($BASE) ?>/dist/app.js"></script>
    <?php endif; ?>

</body>

</html>
