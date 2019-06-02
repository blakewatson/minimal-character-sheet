(function() {
    // init dark mode
    var darkMode = localStorage.getItem('setting-dark-mode') === 'true';
    console.log(darkMode);
    setDarkMode(darkMode);

    // if on dashboard, respond to settings controls
    if(document.querySelector('.dashboard')) {
        var inputDarkMode = document.getElementById('settingDarkMode');
        inputDarkMode.checked = darkMode;
        inputDarkMode.addEventListener('input', event => {
            setDarkMode(event.target.checked);
        });
    }

    function setDarkMode(val) {
        document.querySelector('html').classList.toggle('dark-mode', val);
        localStorage.setItem('setting-dark-mode', val);
    }
})();