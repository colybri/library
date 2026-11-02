// Opens each library dashboard in Kibana with a headless Chromium, waits for
// every panel to render and fails if one shows an error (a Lens panel with a
// missing field, a broken saved search...). Screenshots go to $SCREENSHOTS.
// Usage: ELASTIC_PASSWORD=... node kibana-dashboards.mjs library-api library-domain ...
import { chromium } from 'playwright';

const kibana = process.env.KIBANA_URL ?? 'https://localhost:5601';
const screenshots = process.env.SCREENSHOTS ?? 'kibana-screenshots';
const auth = Buffer.from(`elastic:${process.env.ELASTIC_PASSWORD}`).toString('base64');
const errorSelectors = [
  '[data-test-subj="embeddableError"]',
  '[data-test-subj="embeddableStackError"]',
  '[data-test-subj="errorMessageMarkdown"]',
  '[data-test-subj="embeddable-lens-failure"]',
];

const browser = await chromium.launch();
const context = await browser.newContext({
  ignoreHTTPSErrors: true,
  viewport: { width: 1600, height: 1200 },
  extraHTTPHeaders: { Authorization: `Basic ${auth}` },
});
const page = await context.newPage();
let failed = false;

for (const id of process.argv.slice(2)) {
  await page.goto(`${kibana}/app/dashboards#/view/${id}?_g=(time:(from:now-1h,to:now))`);
  await page.waitForSelector('[data-test-subj="dashboardPanel"], [data-test-subj^="dashboardPanel"]', { timeout: 120_000 });
  // Panels set data-render-complete="true" once drawn (with or without data)
  try {
    await page.waitForFunction(() => {
      const panels = document.querySelectorAll('[data-render-complete]');
      return panels.length > 0 && [...panels].every((p) => p.getAttribute('data-render-complete') === 'true');
    }, null, { timeout: 90_000 });
  } catch {
    console.log(`${id}: some panels did not finish rendering in 90 s`);
  }
  await page.waitForTimeout(3_000);
  await page.screenshot({ path: `${screenshots}/${id}.png`, fullPage: true });

  const panels = await page.locator('[data-test-subj="dashboardPanel"]').count();
  const errors = [];
  for (const selector of errorSelectors) {
    for (const element of await page.locator(selector).all()) {
      errors.push((await element.innerText()).replace(/\s+/g, ' ').trim());
    }
  }
  // Lens lists its own problems (missing fields...) behind this button
  const lensMessages = await page.locator('[data-test-subj="lens-message-list-trigger"]').count();
  console.log(`${id}: ${panels} panels, ${errors.length} errors, ${lensMessages} Lens message buttons`);
  for (const error of errors) console.log(`  error: ${error}`);
  if (panels === 0 || errors.length > 0) failed = true;
  if (lensMessages > 0) {
    for (const trigger of await page.locator('[data-test-subj="lens-message-list-trigger"]').all()) {
      await trigger.click();
      const text = await page.locator('[data-test-subj="lens-message-list-panel"], .euiPopover__panel').last().innerText().catch(() => '');
      console.log(`  Lens message: ${text.replace(/\s+/g, ' ').trim()}`);
      if (/error/i.test(text)) failed = true;
      await page.keyboard.press('Escape');
    }
  }
}

await browser.close();
process.exit(failed ? 1 : 0);
