import { chromium } from 'playwright';

const URL =
  'https://www.figma.com/design/PvQ98dwLwUkLy8Spkt4fUY/Shaparak-Web-%E2%80%93-Full-Design?node-id=59-1043&t=x28fwhnzYMWeYEpm-0';

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
  deviceScaleFactor: 2,
  userAgent:
    'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',
});
const page = await ctx.newPage();
await page.goto(URL, { waitUntil: 'domcontentloaded', timeout: 90000 });
await page.waitForSelector('canvas', { timeout: 60000 }).catch(() => {});
await page.waitForTimeout(20000);

const zoom = () =>
  page.evaluate(() =>
    [...document.querySelectorAll('div,span,button')]
      .map((e) => (e.childElementCount === 0 ? (e.textContent || '').trim() : ''))
      .filter((t) => /^\d+(\.\d+)?%$/.test(t))
  );

const shot = async (name, waitMs) => {
  if (waitMs) await page.waitForTimeout(waitMs);
  await page.screenshot({ path: `design/${name}.png` });
  console.log('shot', name, JSON.stringify(await zoom()));
};

async function pan(dx, dy) {
  let rx = dx, ry = dy, g = 0;
  while ((Math.abs(rx) > 0.5 || Math.abs(ry) > 0.5) && g++ < 400) {
    const cx = Math.max(-490, Math.min(490, rx));
    const cy = Math.max(-490, Math.min(490, ry));
    await page.mouse.move(800, 500);
    await page.mouse.down();
    await page.mouse.move(800 + cx, 500 + cy, { steps: 1 });
    await page.mouse.up();
    rx -= cx; ry -= cy;
  }
}

await page.mouse.move(800, 500);
await page.keyboard.press('0');
await page.waitForTimeout(1500);
await page.mouse.move(800, 500);
await page.keyboard.press('-');
await shot('z50_a', 3000);

await pan(1550, 0);
await shot('z50_b', 2000);
await shot('z50_c', 10000);

// nudge to force a repaint
await page.mouse.move(800, 500);
await page.mouse.wheel(0, 1);
await page.waitForTimeout(300);
await page.mouse.wheel(0, -1);
await shot('z50_d', 5000);

console.log('DONE');
await browser.close();
