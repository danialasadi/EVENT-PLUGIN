import { chromium } from 'playwright';
import fs from 'fs';

const URL =
  'https://www.figma.com/design/PvQ98dwLwUkLy8Spkt4fUY/Shaparak-Web-%E2%80%93-Full-Design?node-id=59-1043&t=x28fwhnzYMWeYEpm-0';

// geometry (design px). Anchor A = screen (0,70) CSS at 50% zoom == design (-600,-600)
const ROWS = 8;
const COLS = 3;
const STEP_X = 1550; // CSS px  (3100 design px)
const STEP_Y = 780; // CSS px  (1560 design px)
const INIT_X = 1486; // CSS px
const INIT_Y = 2442; // CSS px

const outDir = 'design/tiles';
fs.mkdirSync(outDir, { recursive: true });
const SKIP = new Set((process.env.SKIP_TILES || '').split(',').filter(Boolean));

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
const t0 = Date.now();
await page.goto(URL, { waitUntil: 'domcontentloaded', timeout: 90000 });
try {
  await page.waitForSelector('canvas', { timeout: 60000 });
} catch {
  console.log('NO CANVAS');
}
await page.waitForTimeout(20000);

const zoom = async () =>
  page.evaluate(() => {
    const m = [...document.querySelectorAll('div,span,button')]
      .map((e) => (e.childElementCount === 0 ? (e.textContent || '').trim() : ''))
      .filter((t) => /^\d+(\.\d+)?%$/.test(t));
    return m;
  });

async function pan(dx, dy) {
  let rx = dx;
  let ry = dy;
  let guard = 0;
  while ((Math.abs(rx) > 0.5 || Math.abs(ry) > 0.5) && guard++ < 400) {
    const cx = Math.max(-490, Math.min(490, rx));
    const cy = Math.max(-490, Math.min(490, ry));
    await page.mouse.move(800, 500);
    await page.mouse.down();
    await page.mouse.move(800 + cx, 500 + cy, { steps: 1 });
    await page.mouse.up();
    rx -= cx;
    ry -= cy;
  }
  await page.waitForTimeout(1500); // let WebGL repaint before we shoot
}

await page.mouse.move(800, 500);
await page.keyboard.press('0');
await page.waitForTimeout(1200);
console.log('after 0 ->', JSON.stringify(await zoom()), Date.now() - t0);

await page.mouse.move(800, 500);
await page.keyboard.press('-');
await page.waitForTimeout(1200);
console.log('after - ->', JSON.stringify(await zoom()), Date.now() - t0);

await pan(INIT_X, INIT_Y);
await page.waitForTimeout(1500);

for (let j = 0; j < ROWS; j++) {
  for (let k = 0; k < COLS; k++) {
    const f = `${outDir}/t${j}_${k}.png`;
    if (SKIP.has(`t${j}_${k}`)) {
      console.log('skip existing', f, Date.now() - t0);
    } else {
      await page.screenshot({ path: f });
      console.log('shot', f, Date.now() - t0);
    }
    // pan the VIEW right by one column (content moves left)
    if (k < COLS - 1) await pan(-STEP_X, 0);
  }
  // back to column 0 and one row down
  if (j < ROWS - 1) await pan(STEP_X * (COLS - 1), -STEP_Y);
}

console.log('DONE', JSON.stringify(await zoom()), Date.now() - t0);
await browser.close();
