import path from 'path';
import sharp from 'sharp';
import {
  watch
} from 'chokidar';

export default function convertImages(options = {
  format: 'avif', // デフォルトをAVIFに変更（WebPより高圧縮率）
  quality: 80,
  sizes: [1, 2], // 1x, 2xサイズを生成
  maxWidth: 1920, // 最大幅
  maxHeight: 1080 // 最大高さ
}) {
  return {
    name: 'convert-images',
    enforce: 'pre',

    buildStart() {
      const watcher = watch('src/public/images/**/*.{png,jpg,jpeg}', {
        persistent: true
      });

      watcher.on('add', async (filePath) => {
        if (/\.(png|jpe?g)$/.test(filePath)) {
          await processImage(filePath, options);
        }
      });

      watcher.on('change', async (filePath) => {
        if (/\.(png|jpe?g)$/.test(filePath)) {
          await processImage(filePath, options);
        }
      });
    },

    async transform(src, id) {
      if (/\.(png|jpe?g)$/.test(id)) {
        await processImage(id, options);
        return null;
      }
    },
  };
}

/**
 * 画像を処理して最適化された形式に変換
 * @param {string} filePath - 元画像のパス
 * @param {Object} options - 変換オプション
 */
async function processImage(filePath, options) {
  try {
    const dir = path.dirname(filePath);
    const base = path.basename(filePath, path.extname(filePath));
    const ext = path.extname(filePath);

    // 元画像のメタデータを取得
    const metadata = await sharp(filePath).metadata();

    // リサイズが必要かチェック
    const needsResize = metadata.width > options.maxWidth || metadata.height > options.maxHeight;

    // ベース画像を作成（リサイズ済み）
    let baseImage = sharp(filePath);
    if (needsResize) {
      baseImage = baseImage.resize(options.maxWidth, options.maxHeight, {
        fit: 'inside',
        withoutEnlargement: true
      });
    }

    // AVIF形式で変換（優先）
    if (options.format === 'avif' || options.format === 'both') {
      const avifPath = path.resolve(dir, `${base}.avif`);
      await baseImage
        .avif({
          quality: options.quality
        })
        .toFile(avifPath);
      console.log(`✅ AVIF generated: ${avifPath}`);
    }

    // WebP形式で変換
    if (options.format === 'webp' || options.format === 'both') {
      const webpPath = path.resolve(dir, `${base}.webp`);
      await baseImage
        .webp({
          quality: options.quality
        })
        .toFile(webpPath);
      console.log(`✅ WebP generated: ${webpPath}`);
    }

    // 複数サイズを生成
    if (options.sizes && options.sizes.length > 0) {
      for (const scale of options.sizes) {
        if (scale === 1) continue; // 1xは既に生成済み

        const scaledWidth = Math.round((metadata.width || options.maxWidth) * scale);
        const scaledHeight = Math.round((metadata.height || options.maxHeight) * scale);

        const scaledImage = sharp(filePath).resize(scaledWidth, scaledHeight, {
          fit: 'inside',
          withoutEnlargement: true
        });

        // スケール付きファイル名
        const scaledBase = `${base}@${scale}x`;

        if (options.format === 'avif' || options.format === 'both') {
          const scaledAvifPath = path.resolve(dir, `${scaledBase}.avif`);
          await scaledImage
            .avif({
              quality: options.quality
            })
            .toFile(scaledAvifPath);
          console.log(`✅ AVIF ${scale}x generated: ${scaledAvifPath}`);
        }

        if (options.format === 'webp' || options.format === 'both') {
          const scaledWebpPath = path.resolve(dir, `${scaledBase}.webp`);
          await scaledImage
            .webp({
              quality: options.quality
            })
            .toFile(scaledWebpPath);
          console.log(`✅ WebP ${scale}x generated: ${scaledWebpPath}`);
        }
      }
    }

    // 元画像も最適化（JPEG/PNGの品質向上）
    if (ext.toLowerCase() === '.jpg' || ext.toLowerCase() === '.jpeg') {
      const optimizedJpegPath = path.resolve(dir, `${base}_optimized.jpg`);
      await baseImage
        .jpeg({
          quality: options.quality,
          progressive: true
        })
        .toFile(optimizedJpegPath);
      console.log(`✅ Optimized JPEG generated: ${optimizedJpegPath}`);
    } else if (ext.toLowerCase() === '.png') {
      const optimizedPngPath = path.resolve(dir, `${base}_optimized.png`);
      await baseImage
        .png({
          quality: options.quality,
          progressive: true
        })
        .toFile(optimizedPngPath);
      console.log(`✅ Optimized PNG generated: ${optimizedPngPath}`);
    }

  } catch (err) {
    console.error('❌ Image conversion error:', err);
  }
}