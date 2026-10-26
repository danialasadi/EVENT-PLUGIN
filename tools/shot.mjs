import { chromium } from 'playwright';

const url = process.argv[2];
const out = process.argv[3] || 'design/shot.png';
const width = parseInt(process.argv[4] || '1600', 10);
const height = parseInt(process.argv[5] || '1200', 10);
const waitMs = parseInt(process.argv[6] || '25000', 10);

const browser = await chromium.launch({
  headless: true,
  args: [
    '--no-sandbox',
    '--disable-dev-shm-usage',
    '--use-gl=angle',
    '--use-angle=swiftshader',
    '--enable-unsafe-swiftshader',
    '--ignore-gpu-blocklist',
  ],
});
const ctx = await browser.newContext({
  viewport: { width, height },
  deviceScaleFactor: 1,
  locale: 'en-US',
  userAgent:
    'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',
});
const page = await ctx.newPage();
page.on('console', (m) => {
  if (m.type() === 'error') console.log('[console.error]', m.text().slice(0, 300));
});
page.on('requestfailed', (r) => console.log('[reqfail]', r.url().slice(0, 200)));

await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 90000 });
console.log('loaded:', page.url());

// Wait for the Figma canvas to exist, then give it time to paint.
try {
  await page.waitForSelector('canvas', { timeout: 60000 });
  console.log('canvas found');
} catch {
  console.log('no canvas found');
}
await page.waitForTimeout(waitMs);

await page.screenshot({ path: out });
console.log('saved', out);
const title = await page.title();
console.log('title:', title);
await browser.close();
