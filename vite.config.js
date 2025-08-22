import {
  defineConfig
} from 'vite';
import {
  resolve,
  relative,
  extname
} from 'path';
import {
  globSync
} from 'glob';
import {
  fileURLToPath
} from 'node:url';
import autoprefixer from 'autoprefixer';
import handlebars from 'vite-plugin-handlebars';
import {
  ViteImageOptimizer
} from 'vite-plugin-image-optimizer';
import convertImages from './bin/vite-plugin-convert-images.js';

// サイトのルートを決定
const root = resolve(__dirname, 'src');

// 環境変数を取得
const isDev = process.env.NODE_ENV === 'development';
const isWpMode =
  process.env.NODE_ENV === 'wp' || process.argv.includes('--mode=wp');
const isWpDevMode = process.argv.includes('--mode=wp-dev');
const isWpProdMode = process.argv.includes('--mode=wp-prod');

// WordPress用ビルドのinput設定
const inputsForWordPress = {
  style: resolve(root, 'assets', 'style', 'style.scss'),
  // 動的にファイルを取得する @see https://rollupjs.org/configuration-options/#input
  ...Object.fromEntries(
    globSync('src/assets/js/*.js').map(file => [
      relative(
        'src/assets/js',
        file.slice(0, file.length - extname(file).length)
      ),
      resolve(__dirname, file),
    ])
  ),
};

// 静的開発用のinput設定
const inputsForStatic = {
  style: resolve(root, 'assets', 'style', 'style.scss'),
  ...Object.fromEntries(
    globSync('src/**/*.html').map(file => [
      relative('src', file.slice(0, file.length - extname(file).length)),
      resolve(__dirname, file),
    ])
  ),
};

// 環境別設定
const getBuildConfig = () => {
  if (isWpProdMode) {
    return {
      minify: true,
      sourcemap: false,
      outDir: resolve(__dirname, 'wordpress/themes/TEMPLATE_NAME/'),
      rollupOptions: {
        input: inputsForWordPress,
        output: {
          entryFileNames: 'assets/js/[name].min.js',
          chunkFileNames: 'assets/js/[name].min.js',
          assetFileNames: assetsInfo => {
            if (assetsInfo.name.endsWith('.css')) {
              return 'assets/style/[name].min.[ext]';
            } else {
              return 'assets/[name].[ext]';
            }
          },
        },
      },
    };
  } else if (isWpDevMode) {
    return {
      minify: false,
      sourcemap: true,
      outDir: resolve(__dirname, 'wordpress/themes/TEMPLATE_NAME/'),
      rollupOptions: {
        input: inputsForWordPress,
        output: {
          entryFileNames: 'assets/js/[name].js',
          chunkFileNames: 'assets/js/[name].js',
          assetFileNames: assetsInfo => {
            if (assetsInfo.name.endsWith('.css')) {
              return 'assets/style/[name].[ext]';
            } else {
              return 'assets/[name].[ext]';
            }
          },
        },
      },
    };
  } else {
    return {
      minify: false,
      sourcemap: true,
      outDir: resolve(__dirname, 'dist'),
      rollupOptions: {
        input: inputsForStatic,
        output: {
          entryFileNames: 'assets/js/[name].js',
          chunkFileNames: 'assets/js/[name].js',
          assetFileNames: assetsInfo => {
            if (assetsInfo.name.endsWith('.css')) {
              return 'assets/style/[name].[ext]';
            } else {
              return 'assets/[name].[ext]';
            }
          },
        },
      },
    };
  }
};

export default defineConfig(({
  mode
}) => {
  const buildConfig = getBuildConfig();

  return {
    root,
    base: './',
    server: {
      port: 5173,
      origin: mode === 'wp' ? undefined : 'http://localhost:5173',
      host: true, // ネットワークアクセス許可
      hmr: {
        overlay: true, // エラーオーバーレイ表示
        // WordPress環境でのホットリロード改善
        port: 5173,
        host: 'localhost',
      },
      // CORS設定（WordPress環境との連携）
      cors: true,
      // プロキシ設定（必要に応じて）
      proxy: {
        // WordPress APIへのプロキシ設定例
        '/wp-json': {
          target: 'http://localhost:8888',
          changeOrigin: true,
          secure: false,
        },
      },
    },
    build: {
      ...buildConfig,
      css: {
        devSourcemap: true,
        postcss: {
          plugins: [
            autoprefixer(),
            // 本番ビルド時のみCSS圧縮
            ...(isWpProdMode ? [] : []),
          ],
        },
      },
      // ビルド効率化
      chunkSizeWarningLimit: 1000,
      rollupOptions: {
        ...buildConfig.rollupOptions,
        // 外部依存関係の最適化
        external: isWpMode ? [] : [],
        output: {
          ...buildConfig.rollupOptions.output,
          // コード分割の最適化
          manualChunks: isWpMode ?
            undefined :
            {
              vendor: ['@splidejs/splide'],
            },
        },
      },
    },
    plugins: [
      // 画像最適化（強化版）
      ViteImageOptimizer({
        include: '**/*.{png,jpg,jpeg,webp,avif}',
        png: {
          quality: 80,
          progressive: true,
        },
        jpeg: {
          quality: 80,
          progressive: true,
        },
        jpg: {
          quality: 80,
          progressive: true,
        },
        webp: {
          quality: 80,
        },
        avif: {
          quality: 80,
        },
      }),

      // 開発環境では画像をAVIFに変換（強化版）
      isDev ?
      convertImages({
        format: 'avif',
        quality: 80,
        sizes: [1, 2],
        maxWidth: 1920,
        maxHeight: 1080,
      }) :
      null,

      // コンポーネントのディレクトリを読み込む
      handlebars({
        partialDirectory: resolve(__dirname, 'includes'),
        helpers: {
          br: contents => {
            return contents ? contents.replace(/\r?\n/g, '<br>') : '';
          },
          // 環境別の条件分岐ヘルパー
          isDev: () => isDev,
          isWp: () => isWpMode,
          isWpDev: () => isWpDevMode,
          isWpProd: () => isWpProdMode,
        },
        context: pagePath => ({
          brTxt: 'これはテスト文章です。\nこれはテスト文章です。',
          buildTime: new Date().toISOString(),
          environment: isWpProdMode ?
            'production' :
            isWpDevMode ?
            'development' :
            'static',
        }),
      }),
    ],
    resolve: {
      alias: {
        '@': resolve(__dirname, 'src/assets/style'),
        '@js': resolve(__dirname, 'src/assets/js'),
        '@images': resolve(__dirname, 'src/public/images'),
        '@components': resolve(__dirname, 'includes'),
      },
    },
    // 開発サーバーの最適化
    optimizeDeps: {
      include: ['@splidejs/splide'],
    },
    // ビルド時の警告抑制
    logLevel: isWpProdMode ? 'error' : 'info',
  };
});