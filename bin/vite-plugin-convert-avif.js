import path from 'path';
import sharp from 'sharp';
import { watch } from 'chokidar';

export default function convertImagesToAvif() {
  return {
    name: 'convert-images-to-avif', // プラグイン名
    enforce: 'pre',
    buildStart() {
      const watcher = watch('src/public/images/**/*.{png,jpg,jpeg}', { persistent: true });
      watcher.on('add', async (filePath) => { // `path` を `filePath` に変更
        if (/\.(png|jpe?g)$/.test(filePath)) {
          const dir = path.dirname(filePath); // `path` モジュールを正しく参照
          const base = path.basename(filePath, path.extname(filePath));
          const avifPath = path.resolve(dir, `${base}.avif`);

          // 画像をAVIF形式に変換
          await sharp(filePath)
            .avif()
            .toFile(avifPath)
            .catch(err => console.error('AVIF conversion error:', err));
        }
      });
    },
    async transform(src, id) {
      if (/\.(png|jpe?g)$/.test(id)) {
        const dir = path.dirname(id);
        const base = path.basename(id, path.extname(id));
        const avifPath = path.resolve(dir, `${base}.avif`);

        // 画像をAVIF形式に変換
        await sharp(id)
          .avif()
          .toFile(avifPath)
          .catch(err => console.error('AVIF conversion error:', err));

        return null; // 元の画像ファイルの処理は行わない
      }
    },
  };
}