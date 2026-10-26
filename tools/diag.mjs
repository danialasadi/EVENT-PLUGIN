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

const state = async (label) => {
  const s = await page.evaluate(() => {
    const se = document.scrollingElement;
    const cs = [...document.querySelectorAll('canvas')].map((c) => {
      const r = c.getBoundingClientRect();
      return {
        w: c.width,
        h: c.height,
        rect: [Math.round(r.x), Math.round(r.y), Math.round(r.width), Math.round(r.height)],
        parentOverflow: getComputedStyle(c.parentElement).overflow,
      };
    });
    const z = [...document.querySelectorAll('div,span,button')]
      .map((e) => (e.childElementCount === 0 ? (e.textContent || '').trim() : ''))
      .filter((t) => /^\d+(\.\d+)?%$/.test(t));
    return {
      scroll: [se.scrollLeft, se.scrollTop, se.scrollWidth, se.clientWidth, se.scrollHeight, se.clientHeight],
      canvases: cs,
      zoom: z,
      bodyOverflow: getComputedStyle(document.body).overflow,
    };
  });
  console.log(label, JSON.stringify(s));
};

await state('BEFORE');
await page.screenshot({ path: 'design/diag_before.png' });

// small pan, one chunk
await page.mouse.move(800, 500);
await page.mouse.down();
await page.mouse.move(1050, 500, { steps: 1 });
await page.mouse.up();
await page.waitForTimeout(2000);
await state('AFTER_PAN_250');
await page.screenshot({ path: 'design/diag_after.png' });

// wheel pan instead
await page.mouse.move(800, 500);
await page.mouse.wheel(0, 300);
await page.waitForTimeout(2000);
await state('AFTER_WHEEL_300');
await page.screenshot({ path: 'design/diag_wheel.png' });

await browser.close();
