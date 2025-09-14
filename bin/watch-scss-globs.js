#!/usr/bin/env node
// Watch object/project SCSS files and generate a forward aggregator
// so that new files are picked up without restarting Vite.

import { watch } from 'chokidar';
import { promises as fs } from 'fs';
import { resolve, relative, dirname, basename } from 'path';

const ROOT = resolve(process.cwd());
const TARGET_DIR = resolve(ROOT, 'src/assets/style/object/project');
const OUTPUT_FILE = resolve(TARGET_DIR, '_all.scss');

async function generate() {
  try {
    const files = await listScssFiles(TARGET_DIR);
    const lines = [];
    lines.push('// Auto-generated. Do not edit directly.');
    lines.push('// This file aggregates SCSS in object/project for instant HMR.');
    lines.push('');
    for (const file of files) {
      const rel = relative(TARGET_DIR, file).replace(/\\/g, '/');
      if (basename(file) === '_all.scss') continue;
      if (basename(file) === '_index.scss') continue;
      const noExt = rel.replace(/\.scss$/, '');
      const parts = noExt.split('/');
      const last = parts.pop();
      const normalizedLast = last.replace(/^_/, '');
      parts.push(normalizedLast);
      const fwdPath = parts.join('/');
      // Use @forward so callers can @use as *
      lines.push(`@forward "./${fwdPath}";`);
    }
    lines.push('');
    await fs.mkdir(dirname(OUTPUT_FILE), { recursive: true });
    await fs.writeFile(OUTPUT_FILE, lines.join('\n'));
    console.log(`[watch-scss] Regenerated: ${relative(ROOT, OUTPUT_FILE)} with ${files.length} files`);
  } catch (e) {
    console.error('[watch-scss] Generate failed:', e);
  }
}

async function listScssFiles(dir) {
  // recursive listing without external deps
  const out = [];
  async function walk(d) {
    const entries = await fs.readdir(d, { withFileTypes: true });
    for (const ent of entries) {
      const p = resolve(d, ent.name);
      if (ent.isDirectory()) {
        await walk(p);
      } else if (ent.isFile() && p.endsWith('.scss')) {
        out.push(p);
      }
    }
  }
  await walk(dir);
  return out.sort();
}

async function main() {
  await generate();
  const watcher = watch(`${TARGET_DIR}/**/*.scss`, {
    ignoreInitial: true,
  });
  const onChange = async (path) => {
    console.log(`[watch-scss] Change detected: ${relative(ROOT, path)}`);
    await generate();
  };
  watcher.on('add', onChange);
  watcher.on('unlink', onChange);
  watcher.on('change', onChange);
}

main();
