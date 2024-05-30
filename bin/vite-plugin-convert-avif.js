import path from 'path';
import sharp from 'sharp';

export default function convertImagesToAvif() {
  return {
    name: 'convert-images-to-avif', // プラグイン名
    enforce: 'pre',
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