import { defineConfig } from "vite";
import { resolve, relative, extname } from "path";
import { globSync } from "glob";
import { fileURLToPath } from "node:url";
import autoprefixer from "autoprefixer";
import { ViteImageOptimizer } from "vite-plugin-image-optimizer";
import convertImagesToAvif from './bin/vite-plugin-convert-avif.js';

// サイトのルートを決定
const root = resolve(__dirname, "src");

// WordPress用ビルドのinput設定。WordPress用にはhtmlファイルは不要なため、scssとjsのみをビルド対象にする
const inputsForWordPress = {
  style: resolve(root, "/assets/style/style.scss"),
  // 動的にファイルを取得する @see https://rollupjs.org/configuration-options/#input
  ...Object.fromEntries(
    globSync("src/assets/js/*.js").map((file) => [
      relative(
        "src/assets/js",
        file.slice(0, file.length - extname(file).length),
      ),
      resolve(__dirname, file),
    ]),
  ),

};

// 静的開発用のinput設定。静的資材用にはhtmlファイルを経由してscss,jsなどをビルドする
const inputsForStatic = {
  style: resolve(root, "/assets/style/style.scss"),
  ...Object.fromEntries(
    globSync("src/**/*.html").map((file) => [
      relative("src", file.slice(0, file.length - extname(file).length)),
      resolve(__dirname, file),
    ]),
  ),
};

export default defineConfig(({
  mode
}) => ({
  root,
  base: "./",
  server: {
    port: 5173,
    origin: mode == "wp" ? undefined : "http://localhost:5173",
  },
  build: {
    minify: false,
    outDir: mode === "wp" ?
      resolve(__dirname, "wordpress/themes/TEMPLATE_NAME/") : resolve(__dirname, "dist"),
    rollupOptions: {
      input: mode === "wp" ? inputsForWordPress : inputsForStatic,
      output: {
        entryFileNames: "assets/js/[name].js",
        chunkFileNames: "assets/js/[name].js",
        assetFileNames: (assetsInfo) => {
          if (assetsInfo.name.endsWith(".css")) {
            return "assets/style/[name].[ext]";
          } else {
            return "assets/[name].[ext]";
          }
        },
      },
    },
    css: {
      // devSourcemap: true, // SCSSのソースマップを生成（ビルド時には自動的に無効になる）
      postcss: {
        plugins: [autoprefixer()]
      },
    },
  },
  plugins: [
    // 画像最適化
    ViteImageOptimizer({
      include: '**/*.{png,jpg,jpeg,webp,avif}', // 最適化する画像の形式を指定
      png: {
        quality: 80,
      },
      jpeg: {
        quality: 80,
      },
      jpg: {
        quality: 80,
      },
      webp: {
        quality: 80,
      },
      avif: {
        quality: 80,
      },
    }),
    convertImagesToAvif(),
  ],
  resolve: {
    alias: {
      "@": resolve(__dirname, "src/assets/style"),
      "@js": resolve(__dirname, "src/assets/js"),
    },
  },
}));