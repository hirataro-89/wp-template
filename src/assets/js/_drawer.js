// バニラJavaScriptでのドロワーメニュー実装
document.addEventListener('DOMContentLoaded', function() {
    // ハンバーガーメニュー
    const hamburger = document.querySelector('.js-hamburger');
    const drawer = document.querySelector('.js-drawer');

    if (hamburger && drawer) {
        // ハンバーガーボタンクリック
        hamburger.addEventListener('click', function() {
            this.classList.toggle('is-open');
            fadeToggle(drawer);
        });

        // ドロワーナビのaタグをクリックで閉じる
        const drawerLinks = drawer.querySelectorAll('a[href]');
        drawerLinks.forEach(link => {
            link.addEventListener('click', function() {
                hamburger.classList.remove('is-open');
                fadeOut(drawer);
            });
        });

        // resizeイベント
        window.addEventListener('resize', function() {
            if (window.matchMedia("(min-width: 768px)").matches) {
                hamburger.classList.remove('is-open');
                fadeOut(drawer);
            }
        });
    }

    // アコーディオン
    const accordionItems = document.querySelectorAll('.js-drawer-accordion');
    accordionItems.forEach(item => {
        item.addEventListener('click', function() {
            const nextElement = this.nextElementSibling;
            if (nextElement) {
                slideToggle(nextElement);
                this.classList.toggle('is-open');
            }
        });
    });
});

// フェードトグル関数（jQueryのfadeToggle相当）
function fadeToggle(element, duration = 300) {
    if (element.style.display === 'none' || !element.style.display) {
        fadeIn(element, duration);
    } else {
        fadeOut(element, duration);
    }
}

// フェードイン関数
function fadeIn(element, duration = 300) {
    element.style.display = 'block';
    element.style.opacity = '0';

    let start = null;
    function animate(timestamp) {
        if (!start) start = timestamp;
        const progress = timestamp - start;
        const opacity = Math.min(progress / duration, 1);

        element.style.opacity = opacity;

        if (progress < duration) {
            requestAnimationFrame(animate);
        }
    }
    requestAnimationFrame(animate);
}

// フェードアウト関数
function fadeOut(element, duration = 300) {
    let start = null;
    const initialOpacity = parseFloat(element.style.opacity) || 1;

    function animate(timestamp) {
        if (!start) start = timestamp;
        const progress = timestamp - start;
        const opacity = initialOpacity * (1 - Math.min(progress / duration, 1));

        element.style.opacity = opacity;

        if (progress < duration) {
            requestAnimationFrame(animate);
        } else {
            element.style.display = 'none';
        }
    }
    requestAnimationFrame(animate);
}

// スライドトグル関数（jQueryのslideToggle相当）
function slideToggle(element, duration = 300) {
    if (element.style.display === 'none' || !element.style.display) {
        slideDown(element, duration);
    } else {
        slideUp(element, duration);
    }
}

// スライドダウン関数
function slideDown(element, duration = 300) {
    element.style.display = 'block';
    const height = element.scrollHeight;
    element.style.height = '0px';
    element.style.overflow = 'hidden';

    let start = null;
    function animate(timestamp) {
        if (!start) start = timestamp;
        const progress = timestamp - start;
        const currentHeight = Math.min((progress / duration) * height, height);

        element.style.height = currentHeight + 'px';

        if (progress < duration) {
            requestAnimationFrame(animate);
        } else {
            element.style.height = '';
            element.style.overflow = '';
        }
    }
    requestAnimationFrame(animate);
}

// スライドアップ関数
function slideUp(element, duration = 300) {
    const height = element.scrollHeight;
    element.style.height = height + 'px';
    element.style.overflow = 'hidden';

    let start = null;
    function animate(timestamp) {
        if (!start) start = timestamp;
        const progress = timestamp - start;
        const currentHeight = height * (1 - Math.min(progress / duration, 1));

        element.style.height = currentHeight + 'px';

        if (progress < duration) {
            requestAnimationFrame(animate);
        } else {
            element.style.display = 'none';
            element.style.height = '';
            element.style.overflow = '';
        }
    }
    requestAnimationFrame(animate);
}