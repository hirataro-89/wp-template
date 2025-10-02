#!/usr/bin/env node
 // Watch SCSS directories and bump mtime of style.scss on add/remove
// to make Vite re-expand globs without modifying file contents.

import {
    watch
} from 'chokidar';
import {
    promises as fs
} from 'fs';
import {
    resolve,
    relative
} from 'path';

const ROOT = resolve(process.cwd());
const STYLE_ENTRY = resolve(ROOT, 'src/assets/style/style.scss');
const TARGET_DIRS = [
    resolve(ROOT, 'src/assets/style/object/project'),
    resolve(ROOT, 'src/assets/style/object/component'),
    resolve(ROOT, 'src/assets/style/layouts'),
    resolve(ROOT, 'src/assets/style/utils'),
];

async function touchStyle(reason = '') {
    try {
        // Ensure file exists, then update atime/mtime to now
        await fs.access(STYLE_ENTRY);
        const now = new Date();
        await fs.utimes(STYLE_ENTRY, now, now);
        console.log(`[watch-scss] style.scss touched${reason ? ` (${reason})` : ''}`);
    } catch (e) {
        console.error('[watch-scss] Touch failed:', e);
    }
}

async function main() {
    // Initial touch to ensure Vite expands current globs
    await touchStyle('startup');

    const watcher = watch(
        TARGET_DIRS.map((dir) => `${dir}/**/*.scss`), {
            ignoreInitial: true
        }
    );

    const onAdd = async (path) => {
        console.log(`[watch-scss] File added: ${relative(ROOT, path)}`);
        await touchStyle('add');
    };
    const onUnlink = async (path) => {
        console.log(`[watch-scss] File removed: ${relative(ROOT, path)}`);
        await touchStyle('unlink');
    };

    watcher.on('add', onAdd);
    watcher.on('unlink', onUnlink);
}

main();