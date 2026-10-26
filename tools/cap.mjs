import { chromium } from 'playwright';

// Usage: node tools/cap.mjs '<url>' '<json actions>' <out.png> [scale] [width] [height]
const url = process.argv[2];
const actions = JSON.parse(process.argv[3] || '[]');
const out = process.argv[4] || 'design/cap.png';
const scale = parseInt(process.argv[5] || '2', 10);
const width = parseInt(process.argv[6] || '1600', 10);
const height = parseInt(process.argv[7] || '1000', 10);
const bootWait = parseInt(process.env.BOOT_WAIT || '20000', 10);

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
  deviceScaleFactor: scale,
  locale: 'en-US',
  userAgent:
    'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',
});
const page = await ctx.newPage();
await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 90000 });
try {
  await page.waitForSelector('canvas', { timeout: 60000 });
} catch {
  console.log('no canvas');
}
await page.waitForTimeout(bootWait);
console.log('url:', page.url());

const readZoom = async () => {
  const z = await page.evaluate(() => {
    const els = [...document.querySelectorAll('div,span,button')];
    const m = els
      .map((e) => (e.childElementCount === 0 ? (e.textContent || '').trim() : ''))
      .filter((t) => /^\d+(\.\d+)?%$/.test(t));
    return m;
  });
  return z;
};
console.log('zoom:', JSON.stringify(await readZoom()));

for (const a of actions) {
  try {
    if (a.click) {
      await page.mouse.click(a.click[0], a.click[1]);
    } else if (a.move) {
      await page.mouse.move(a.move[0], a.move[1]);
    } else if (a.down) {
      await page.mouse.move(a.down[0], a.down[1]);
      await page.mouse.down();
    } else if (a.up) {
      await page.mouse.move(a.up[0], a.up[1], { steps: 25 });
      await page.mouse.up();
    } else if (a.wheel) {
      await page.mouse.wheel(a.wheel[0], a.wheel[1]);
    } else if (a.key) {
      await page.keyboard.press(a.key);
    } else if (a.type) {
      await page.keyboard.type(a.type);
    } else if (a.clicksel) {
      await page.locator(a.clicksel).first().click({ timeout: 8000 });
    } else if (a.shot) {
      await page.screenshot({ path: a.shot });
      console.log('shot', a.shot);
    } else if (a.logzoom) {
      console.log('zoom:', JSON.stringify(await readZoom()));
    } else if (a.wait) {
      await page.waitForTimeout(a.wait);
    }
  } catch (e) {
    console.log('ACTION FAILED', JSON.stringify(a), String(e).slice(0, 200));
  }
}

await page.waitForTimeout(2500);
await page.screenshot({ path: out });
console.log('final', out, 'zoom:', JSON.stringify(await readZoom()));
await browser.close();
