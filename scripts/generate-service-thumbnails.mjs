#!/usr/bin/env node
/**
 * Membuat thumbnail SVG untuk setiap layanan katalog homecare.
 *
 * Sumber data : database/data/sk_tarif_homecare.json  (82 baris SK tarif)
 * Ikon garis  : resources/views/components/icon.blade.php (satu sumber yang sama)
 * Keluaran    : public/images/services/{KODE}.svg
 *
 * Jalankan: node scripts/generate-service-thumbnails.mjs
 */
import { readFileSync, writeFileSync, mkdirSync } from 'node:fs';
import { dirname, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const data = JSON.parse(readFileSync(join(root, 'database/data/sk_tarif_homecare.json'), 'utf8'));
const iconBlade = readFileSync(join(root, 'resources/views/components/icon.blade.php'), 'utf8');

const icons = new Map();
for (const [, name, body] of iconBlade.matchAll(/^\s*'([a-z0-9-]+)' => '([^']*)',\s*$/gm)) {
    icons.set(name, body);
}

const outDir = join(root, 'public/images/services');
mkdirSync(outDir, { recursive: true });

const esc = (value) => String(value).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');

const W = 400;
const H = 260;
const ICON_BOX = 132;
const ICON_SCALE = ICON_BOX / 24;
const ICON_X = (W - ICON_BOX) / 2;
const ICON_Y = 44;

let written = 0;
const missing = [];

for (const service of data.services) {
    const category = data.categories.find((item) => item.key === service.category);
    const [from, to] = category?.colors ?? ['#0f766e', '#14b8a6'];
    const label = (category?.label ?? service.category).toUpperCase();
    const body = icons.get(service.icon);

    if (!body) {
        missing.push(`${service.code}: ikon "${service.icon}" tidak ada di icon.blade.php`);
    }

    const svg = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${W} ${H}" width="${W}" height="${H}" role="img" aria-label="${esc(service.name)}">
  <title>${esc(service.name)}</title>
  <defs>
    <linearGradient id="g" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="${from}"/>
      <stop offset="100%" stop-color="${to}"/>
    </linearGradient>
    <linearGradient id="sheen" x1="0" y1="0" x2="0" y2="1">
      <stop offset="0%" stop-color="#ffffff" stop-opacity=".22"/>
      <stop offset="60%" stop-color="#ffffff" stop-opacity="0"/>
    </linearGradient>
  </defs>
  <rect width="${W}" height="${H}" fill="url(#g)"/>
  <rect width="${W}" height="${H}" fill="url(#sheen)"/>
  <circle cx="348" cy="34" r="104" fill="#ffffff" fill-opacity=".12"/>
  <circle cx="286" cy="228" r="66" fill="#ffffff" fill-opacity=".09"/>
  <circle cx="64" cy="236" r="92" fill="#000000" fill-opacity=".06"/>
  <g transform="translate(${ICON_X} ${ICON_Y}) scale(${ICON_SCALE.toFixed(4)})" fill="none" stroke="#ffffff" stroke-width="${(4.6 / ICON_SCALE).toFixed(3)}" stroke-linecap="round" stroke-linejoin="round">
    ${body ?? ''}
  </g>
  <text x="24" y="234" fill="#ffffff" fill-opacity=".92" font-family="Inter, 'Segoe UI', system-ui, sans-serif" font-size="15" font-weight="700" letter-spacing="1.1">${esc(label)}</text>
</svg>
`;

    writeFileSync(join(outDir, `${service.code}.svg`), svg, 'utf8');
    written += 1;
}

console.log(`Thumbnail dibuat: ${written} file di public/images/services/`);
if (missing.length) {
    console.warn('Peringatan:');
    for (const line of missing) console.warn(`  - ${line}`);
}
