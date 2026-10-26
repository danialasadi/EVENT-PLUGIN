import { chromium } from 'playwright';

const url = process.argv[2];
const code = process.argv[3];
const wait = parseInt(process.env.BOOT_WAIT || '20000', 10);

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
  viewport: { width: 1600, height: 1000 },
  deviceScaleFactor: 1,
  userAgent:
    'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',
});
const page = await ctx.newPage();
await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 90000 });
try {
  await page.waitForSelector('canvas', { timeout: 60000 });
} catch {}
await page.waitForTimeout(wait);

const keys = process.env.KEYS;
if (keys) {
  for (const k of keys.split(',')) {
    await page.keyboard.press(k.trim());
    await page.waitForTimeout(700);
  }
}
const expr = code.trim().startsWith('()') || code.trim().startsWith('(') ? `(${code})()` : code;
const res = await page.evaluate(expr);
console.log(typeof res === 'string' ? res : JSON.stringify(res, null, 1));
await browser.close();
