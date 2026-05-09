const fs = require("fs");
const path = require("path");
const { chromium } = require("playwright");

const ROOT = path.resolve(__dirname, "..");
const OUT = path.join(ROOT, "screenshots", "raw");
fs.mkdirSync(OUT, { recursive: true });

const baseUrl = "http://127.0.0.1:8000";
const chromeCandidates = [
  "C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe",
  "C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe",
  "C:\\Program Files\\Microsoft\\Edge\\Application\\msedge.exe",
  "C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe",
];
const executablePath = chromeCandidates.find((candidate) => fs.existsSync(candidate));

const pages = [
  { slug: "01-login", title: "Login", url: "/login", auth: false },
  { slug: "02-dashboard", title: "Dashboard", url: "/dashboard" },
  { slug: "03-smart-pos", title: "Smart POS", url: "/pos" },
  { slug: "04-pharmacy-setup", title: "Pharmacy Setup", url: "/setup" },
  { slug: "05-product-setup", title: "Product Setup", url: "/product-setup" },
  { slug: "06-suppliers", title: "Suppliers", url: "/suppliers" },
  { slug: "07-purchases", title: "Purchases", url: "/purchases" },
  { slug: "08-inventory", title: "Current Inventory", url: "/inventory" },
  { slug: "09-inventory-movements", title: "Inventory Movements", url: "/inventory-movements" },
  { slug: "10-inventory-alerts", title: "Inventory Alerts", url: "/inventory-alerts" },
  { slug: "11-stock-adjustments", title: "Stock Adjustments", url: "/stock-adjustments" },
  { slug: "12-stock-transfers", title: "Stock Transfers", url: "/stock-transfers" },
  { slug: "13-sales-history", title: "Sales History", url: "/sales" },
  { slug: "14-sales-returns", title: "Sales Returns", url: "/sales-returns" },
  { slug: "15-expenses", title: "Expenses", url: "/expenses" },
  { slug: "16-daily-closing", title: "Daily Closing", url: "/daily-closings" },
  { slug: "17-report-center", title: "Report Center", url: "/reports" },
  { slug: "18-profit-report", title: "Profit Report", url: "/reports/profit" },
  { slug: "19-users", title: "Users", url: "/users" },
  { slug: "20-roles", title: "Roles and Permissions", url: "/roles" },
  { slug: "21-activity-logs", title: "Activity Logs", url: "/activity-logs" },
];

async function settle(page) {
  await page.waitForLoadState("domcontentloaded");
  await page.waitForTimeout(1200);
  await page.evaluate(() => {
    document.querySelectorAll(".modal-backdrop").forEach((el) => el.remove());
    document.body.classList.remove("modal-open");
  }).catch(() => {});
}

async function screenshot(page, name, title, opts = {}) {
  await settle(page);
  const file = path.join(OUT, `${name}.png`);
  await page.screenshot({ path: file, fullPage: opts.fullPage ?? false });
  console.log(`${title}: ${file}`);
}

(async () => {
  const browser = await chromium.launch({
    headless: true,
    executablePath,
  });
  const context = await browser.newContext({
    viewport: { width: 1440, height: 900 },
    deviceScaleFactor: 1,
  });
  const page = await context.newPage();

  for (const p of pages) {
    await page.goto(`${baseUrl}${p.url}`, { waitUntil: "domcontentloaded" });
    if (p.auth === false) {
      await screenshot(page, p.slug, p.title);
      continue;
    }

    if (page.url().includes("/login")) {
      await page.fill("#login", "admin");
      await page.fill("#password", "password");
      await Promise.all([
        page.waitForNavigation({ waitUntil: "domcontentloaded" }),
        page.click('button[type="submit"]'),
      ]);
    }

    await page.goto(`${baseUrl}${p.url}`, { waitUntil: "domcontentloaded" });
    await screenshot(page, p.slug, p.title);
  }

  await page.goto(`${baseUrl}/dashboard`, { waitUntil: "domcontentloaded" });
  await settle(page);
  const search = page.locator(".js-quick-search-input").first();
  if (await search.count()) {
    await search.fill("para");
    await page.waitForTimeout(1000);
    await screenshot(page, "22-quick-search", "Quick Search");
  }

  await page.setViewportSize({ width: 390, height: 844 });
  await page.goto(`${baseUrl}/dashboard`, { waitUntil: "domcontentloaded" });
  await screenshot(page, "23-mobile-dashboard", "Mobile Dashboard");

  await page.goto(`${baseUrl}/pos`, { waitUntil: "domcontentloaded" });
  await screenshot(page, "24-mobile-pos", "Mobile POS");

  await context.close();
  await browser.close();
  process.exit(0);
})();
