/**
 * バニラJavaScriptでのドロワーメニュー実装
 * jQuery依存を排除し、モダンなJavaScriptで記述
 */

// === 定数定義 ===
const BREAKPOINTS = {
    TABLET: 768 // タブレット表示の境界値（px）
};

const ANIMATION = {
    DURATION: 500 // デフォルトアニメーション時間（ミリ秒）
};

const activeAnimations = new WeakMap();

// === ユーティリティ関数 ===

/**
 * 要素が非表示状態かどうかを判定
 * @param {HTMLElement} element - 判定対象の要素
 * @returns {boolean} 非表示の場合true
 */
function isElementHidden(element) {
    return window.getComputedStyle(element).display === 'none';
}

/**
 * 汎用アニメーション関数
 * @param {Function} updateFunction - 各フレームで実行する更新処理
 * @param {Function} completeFunction - アニメーション完了時の処理
 * @param {number} duration - アニメーション時間（ミリ秒）
 */
function animate(element, updateFunction, completeFunction = () => {}, duration = ANIMATION.DURATION) {
    const prevState = activeAnimations.get(element);
    if (prevState) {
        prevState.cancelled = true;
        cancelAnimationFrame(prevState.rafId);
    }

    let start = null;
    const state = { cancelled: false, rafId: 0 };

    function frame(timestamp) {
        if (state.cancelled) return;
        if (!start) start = timestamp;
        const progress = timestamp - start;
        const ratio = duration === 0 ? 1 : Math.min(progress / duration, 1);

        updateFunction(ratio, progress);

        if (ratio < 1) {
            state.rafId = requestAnimationFrame(frame);
        } else {
            activeAnimations.delete(element);
            completeFunction();
        }
    }

    state.rafId = requestAnimationFrame(frame);
    activeAnimations.set(element, state);
}

/**
 * リサイズイベントのスロットリング用
 */
function throttledResize(callback, delay = 100) {
    let timeoutId;
    return function() {
        if (timeoutId) {
            clearTimeout(timeoutId);
        }
        timeoutId = setTimeout(() => {
            timeoutId = null;
            callback();
        }, delay);
    };
}

function rememberDisplay(element) {
    if (!element.dataset.originalDisplay) {
        const computedDisplay = window.getComputedStyle(element).display;
        if (computedDisplay && computedDisplay !== 'none') {
            element.dataset.originalDisplay = computedDisplay;
        }
    }
}

function showElement(element) {
    rememberDisplay(element);
    const targetDisplay = element.dataset.originalDisplay || 'block';
    element.style.display = targetDisplay;
}
document.addEventListener('DOMContentLoaded', function() {
    // === ハンバーガーメニュー処理 ===

    // ハンバーガーボタンとドロワーメニューの要素を取得
    const hamburger = document.querySelector('.js-hamburger');
    const drawer = document.querySelector('.js-drawer');

    // 両方の要素が存在する場合のみ処理を実行
    if (hamburger && drawer) {
        // ハンバーガーボタンクリック時の処理
        hamburger.addEventListener('click', function() {
            // ハンバーガーボタンの開閉状態をトグル（is-openクラスの付け外し）
            this.classList.toggle('is-open');
            // ドロワーメニューをフェード表示/非表示
            fadeToggle(drawer);
        });

        // ドロワー内のリンクをクリックした時の処理
        const drawerLinks = drawer.querySelectorAll('a[href]'); // href属性を持つaタグを全取得
        drawerLinks.forEach(link => {
            link.addEventListener('click', function() {
                // リンククリック時はメニューを閉じる
                hamburger.classList.remove('is-open'); // ハンバーガーボタンの開状態を解除
                fadeOut(drawer); // ドロワーメニューをフェードアウト
            });
        });

        // ウィンドウリサイズ時の処理（スロットリング適用）
        window.addEventListener('resize', throttledResize(function() {
            // 画面幅がタブレット以上の場合（PC表示時）
            if (window.matchMedia(`(min-width: ${BREAKPOINTS.TABLET}px)`).matches) {
                // モバイルメニューを強制的に閉じる
                hamburger.classList.remove('is-open');
                fadeOut(drawer);
            }
        }));
    }

    // === アコーディオン処理 ===

    // アコーディオンの見出し要素を全取得
    const accordionItems = document.querySelectorAll('.js-drawer-accordion');
    accordionItems.forEach(item => {
        // 各アコーディオン見出しにクリックイベントを設定
        item.addEventListener('click', function() {
            // 次の兄弟要素（アコーディオンの中身）を取得
            const nextElement = this.nextElementSibling;
            if (nextElement) {
                // アコーディオンの開閉をスライドアニメーションで実行
                slideToggle(nextElement);
                // 見出しの開閉状態をトグル（is-openクラスの付け外し）
                this.classList.toggle('is-open');
            }
        });
    });
});

/**
 * フェードトグル関数（jQueryのfadeToggle相当）
 * 要素の表示状態に応じてフェードイン/フェードアウトを切り替える
 * @param {HTMLElement} element - 対象の要素
 * @param {number} duration - アニメーション時間（ミリ秒）
 */
function fadeToggle(element, duration = ANIMATION.DURATION) {
    // 要素の表示状態に応じて処理を分岐
    if (isElementHidden(element)) {
        fadeIn(element, duration); // フェードイン実行
    } else {
        fadeOut(element, duration); // フェードアウト実行
    }
}

/**
 * フェードイン関数
 * 要素を透明度0から1へ徐々に変化させて表示する
 * @param {HTMLElement} element - 対象の要素
 * @param {number} duration - アニメーション時間（ミリ秒）
 */
function fadeIn(element, duration = ANIMATION.DURATION) {
    showElement(element); // 要素を表示状態にする
    const computedOpacity = parseFloat(window.getComputedStyle(element).opacity);
    const startOpacity = Number.isNaN(computedOpacity) ? 0 : computedOpacity;
    element.style.opacity = startOpacity.toString();

    // 汎用アニメーション関数を使用
    animate(
        element,
        (ratio) => {
            const nextOpacity = startOpacity + (1 - startOpacity) * ratio;
            element.style.opacity = nextOpacity.toString();
        },
        () => {
            element.style.opacity = '1';
        },
        duration
    );
}

/**
 * フェードアウト関数
 * 要素を現在の透明度から0へ徐々に変化させて非表示にする
 * @param {HTMLElement} element - 対象の要素
 * @param {number} duration - アニメーション時間（ミリ秒）
 */
function fadeOut(element, duration = ANIMATION.DURATION) {
    rememberDisplay(element);
    const computedOpacity = parseFloat(window.getComputedStyle(element).opacity);
    const startOpacity = Number.isNaN(computedOpacity) ? 1 : computedOpacity;

    // 汎用アニメーション関数を使用
    animate(
        element,
        (ratio) => {
            const nextOpacity = startOpacity * (1 - ratio);
            element.style.opacity = nextOpacity.toString();
        },
        () => {
            element.style.display = 'none'; // アニメーション完了時に要素を非表示
            element.style.opacity = '0';
        },
        duration
    );
}

/**
 * スライドトグル関数（jQueryのslideToggle相当）
 * 要素の表示状態に応じてスライドダウン/スライドアップを切り替える
 * @param {HTMLElement} element - 対象の要素
 * @param {number} duration - アニメーション時間（ミリ秒）
 */
function slideToggle(element, duration = ANIMATION.DURATION) {
    // 要素の表示状態に応じて処理を分岐
    if (isElementHidden(element)) {
        slideDown(element, duration); // スライドダウン実行
    } else {
        slideUp(element, duration); // スライドアップ実行
    }
}

/**
 * スライドダウン関数
 * 要素を高さ0から本来の高さまで徐々に拡張して表示する
 * @param {HTMLElement} element - 対象の要素
 * @param {number} duration - アニメーション時間（ミリ秒）
 */
function slideDown(element, duration = ANIMATION.DURATION) {
    showElement(element); // 要素を表示状態にする
    const height = element.scrollHeight; // 要素の本来の高さを取得
    element.style.height = '0px'; // 高さを0に設定（完全に縮んだ状態）
    element.style.overflow = 'hidden'; // はみ出た内容を非表示にする

    // 汎用アニメーション関数を使用
    animate(
        element,
        (ratio) => {
            // 高さを0から本来の高さまで変化
            element.style.height = (height * ratio) + 'px';
        },
        () => {
            // アニメーション完了時の処理
            element.style.height = ''; // 高さの制限を解除（自動サイズに戻す）
            element.style.overflow = ''; // overflowの制限を解除
        },
        duration
    );
}

/**
 * スライドアップ関数
 * 要素を現在の高さから0まで徐々に縮小して非表示にする
 * @param {HTMLElement} element - 対象の要素
 * @param {number} duration - アニメーション時間（ミリ秒）
 */
function slideUp(element, duration = ANIMATION.DURATION) {
    rememberDisplay(element);
    const height = element.scrollHeight; // 要素の現在の高さを取得
    element.style.height = height + 'px'; // 高さを明示的に設定
    element.style.overflow = 'hidden'; // はみ出た内容を非表示にする

    // 汎用アニメーション関数を使用
    animate(
        element,
        (ratio) => {
            // 高さを本来の高さから0まで変化
            element.style.height = (height * (1 - ratio)) + 'px';
        },
        () => {
            // アニメーション完了時の処理
            element.style.display = 'none'; // 要素を非表示にする
            element.style.height = ''; // 高さの制限を解除
            element.style.overflow = ''; // overflowの制限を解除
        },
        duration
    );
}
