(function() {
    // init dark mode
    var darkMode = localStorage.getItem('setting-dark-mode') === 'true';
    setDarkMode(darkMode);

    // init textarea font
    var textareaFont = localStorage.getItem('setting-textarea-font') || 'default';
    setTextareaFont(textareaFont);

    // if on dashboard, respond to settings controls
    if(document.querySelector('.dashboard')) {
        // dark mode
        var inputDarkMode = document.getElementById('settingDarkMode');
        inputDarkMode.checked = darkMode;
        inputDarkMode.addEventListener('input', event => {
            setDarkMode(event.target.checked);
        });

        // textarea font
        var inputTextareaFont = document.getElementById('settingTextareaFont');
        inputTextareaFont.value = localStorage.getItem('setting-textarea-font') || 'default';
        inputTextareaFont.addEventListener('change', event => {
            setTextareaFont(event.target.value);
        });
    }

    function setDarkMode(val) {
        document.querySelector('html').classList.toggle('dark-mode', val);
        localStorage.setItem('setting-dark-mode', val);
    }

    function setTextareaFont(val) {
        document.querySelector('html').classList.toggle('textarea-font-sans-serif', val === 'sans-serif');
        document.querySelector('html').classList.toggle('textarea-font-serif', val === 'serif');
        localStorage.setItem('setting-textarea-font', val);
    }
})();