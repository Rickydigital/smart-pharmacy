const fs = require("fs");
const path = require("path");
const {
  Presentation,
  PresentationFile,
  row,
  column,
  grid,
  layers,
  panel,
  text,
  image,
  shape,
  rule,
  fill,
  fixed,
  hug,
  wrap,
  grow,
  fr,
  auto,
} = require("@oai/artifact-tool");
const { stroke } = require("@oai/artifact-tool/presentation-jsx");

const ROOT = path.resolve(__dirname, "..");
const OUT = path.join(ROOT, "output");
const PREVIEW = path.join(ROOT, "qa", "ppt_previews");
const RAW = path.join(ROOT, "screenshots", "raw");
fs.mkdirSync(OUT, { recursive: true });
fs.mkdirSync(PREVIEW, { recursive: true });

const W = 1920;
const H = 1080;
const BLUE = "#2563EB";
const TEAL = "#14B8A6";
const ORANGE = "#F97316";
const NAVY = "#07111F";
const INK = "#0F172A";
const MUTED = "#64748B";
const PAPER = "#F8FBFF";

function shot(name) {
  return path.join(RAW, name);
}

function tx(value, size, color = INK, bold = false, width = fill, extra = {}) {
  return text(value, {
    width,
    height: hug,
    style: {
      fontFace: "Aptos Display",
      fontSize: size,
      bold,
      color,
      ...extra,
    },
  });
}

function body(value, color = "#334155", width = wrap(760)) {
  return tx(value, 29, color, false, width, { fontFace: "Aptos" });
}

function eyebrow(value, color = BLUE) {
  return tx(value.toUpperCase(), 18, color, true, fill, { letterSpacing: 1.2 });
}

function bullet(value, color = "#334155") {
  return row({ width: fill, height: hug, gap: 14, align: "start" }, [
    shape({ width: fixed(12), height: fixed(12), fill: BLUE, geometry: "ellipse" }),
    tx(value, 25, color, false, fill, { fontFace: "Aptos" }),
  ]);
}

function imagePanel(name, imgPath, opts = {}) {
  return panel(
    {
      width: opts.width ?? fill,
      height: opts.height ?? fill,
      padding: opts.padding ?? 12,
      fill: opts.fill ?? "#FFFFFF",
      line: stroke(opts.line ?? "#D7E3F8", 1),
      borderRadius: "rounded-xl",
      shadow: "lg",
    },
    image({
      name,
      path: imgPath,
      width: fill,
      height: fill,
      fit: opts.fit ?? "cover",
      alt: opts.alt ?? name,
      rotation: opts.rotation,
    }),
  );
}

function darkBg() {
  return layers({ width: fill, height: fill }, [
    shape({ width: fill, height: fill, fill: NAVY }),
    shape({ width: fixed(720), height: fixed(1080), fill: "#0B3B6B" }),
    shape({ width: fixed(520), height: fixed(1080), fill: "#0F766E" }),
  ]);
}

function slideFrame(slide, content, bg = PAPER) {
  slide.compose(
    layers({ name: "slide-root", width: fill, height: fill }, [
      typeof bg === "string" ? shape({ width: fill, height: fill, fill: bg }) : bg,
      content,
    ]),
    { frame: { left: 0, top: 0, width: W, height: H }, baseUnit: 8 },
  );
}

function titleStack(kicker, title, subtitle, color = INK, subColor = "#475569") {
  return column({ width: fill, height: hug, gap: 18 }, [
    eyebrow(kicker),
    tx(title, 68, color, true, wrap(1120)),
    body(subtitle, subColor, wrap(980)),
  ]);
}

function twoCol(slide, kicker, title, subtitle, leftChildren, rightNode, bg = PAPER) {
  slideFrame(
    slide,
    grid(
      {
        width: fill,
        height: fill,
        padding: { x: 86, y: 66 },
        rows: [auto, fr(1), auto],
        columns: [fr(0.88), fr(1.12)],
        columnGap: 62,
        rowGap: 32,
      },
      [
        column({ columnSpan: 2, width: fill, height: hug, gap: 16 }, [
          eyebrow(kicker),
          tx(title, 60, INK, true, wrap(1320)),
          body(subtitle, "#475569", wrap(1160)),
        ]),
        column({ width: fill, height: fill, gap: 18, justify: "center" }, leftChildren),
        rightNode,
        tx("Smart Pharmacy | POS + Inventory + Finance + Alerts + Reports", 16, "#64748B", false, fill),
      ],
    ),
    bg,
  );
}

function statChip(label, value, color = BLUE) {
  return panel(
    { width: fill, height: hug, padding: { x: 20, y: 16 }, fill: "#FFFFFF", line: stroke("#D7E3F8", 1), borderRadius: "rounded-xl" },
    column({ width: fill, gap: 4 }, [
      tx(label.toUpperCase(), 15, color, true, fill, { letterSpacing: 1.1 }),
      tx(value, 28, INK, true, fill),
    ]),
  );
}

const presentation = Presentation.create({ slideSize: { width: W, height: H } });

// 1. Cover
{
  const slide = presentation.slides.add();
  slideFrame(
    slide,
    grid(
      {
        width: fill,
        height: fill,
        padding: { x: 90, y: 70 },
        columns: [fr(0.9), fr(1.1)],
        rows: [fr(1), auto],
        columnGap: 54,
      },
      [
        column({ width: fill, height: fill, justify: "center", gap: 26 }, [
          eyebrow("Executive presentation", TEAL),
          tx("Smart Pharmacy", 92, "#FFFFFF", true, wrap(780)),
          tx("Faster sales, safer stock, and stronger owner visibility.", 34, "#D5F3ED", false, wrap(740)),
          row({ width: fill, gap: 18 }, [
            statChip("Core", "POS + Stock", TEAL),
            statChip("Control", "Approvals", ORANGE),
          ]),
        ]),
        row({ width: fill, height: fill, gap: 20, align: "center" }, [
          imagePanel("dashboard-cover", shot("02-dashboard.png"), { height: fixed(650), rotation: -3 }),
          column({ width: fixed(260), height: fill, gap: 22, justify: "center" }, [
            imagePanel("mobile-cover", shot("23-mobile-dashboard.png"), { height: fixed(520), fit: "cover" }),
            imagePanel("pos-mini-cover", shot("03-smart-pos.png"), { height: fixed(170), fit: "cover" }),
          ]),
        ]),
        tx("Proposal deck | May 2026", 18, "#B9EDE3", false, fill, { columnSpan: 2 }),
      ],
    ),
    darkBg(),
  );
}

// 2. Problem
twoCol(
  presentation.slides.add(),
  "Problem description",
  "Pharmacies move fast, but control often moves slowly.",
  "When POS, stock, expiry, returns, cash, and branch decisions are separated, the owner loses the full operating picture.",
  [
    bullet("Stock can look available even when it is expired, damaged, or sold."),
    bullet("Cash closing becomes difficult when expenses and refunds sit elsewhere."),
    bullet("Approvals are delayed when sensitive actions depend on informal messages."),
    bullet("Managers see the problem late, after losses or shortages have already happened."),
  ],
  column({ width: fill, height: fill, gap: 22, justify: "center" }, [
    imagePanel("inventory-risk", shot("08-inventory.png"), { height: fixed(350) }),
    row({ width: fill, height: fixed(180), gap: 20 }, [
      imagePanel("daily-closing-risk", shot("16-daily-closing.png")),
      imagePanel("sales-return-risk", shot("14-sales-returns.png")),
    ]),
  ]),
);

// 3. Discovery
{
  const slide = presentation.slides.add();
  slideFrame(
    slide,
    grid(
      {
        width: fill,
        height: fill,
        padding: { x: 86, y: 66 },
        rows: [auto, fr(1), auto],
        columns: [fr(1), fr(1)],
        columnGap: 50,
        rowGap: 30,
      },
      [
        titleStack("Discovery", "The system is needed where every movement touches another record.", "A sale touches stock. A return touches revenue and batch quantity. An expense touches closing. A transfer touches two branches.", "#FFFFFF", "#D5F3ED"),
        imagePanel("quick-search-discovery", shot("22-quick-search.png"), { height: fixed(560) }),
        grid({ width: fill, height: hug, columns: [fr(1), fr(1)], columnGap: 16, rowGap: 16 }, [
          statChip("Movement", "Recorded once"),
          statChip("Permission", "Controlled action", ORANGE),
          statChip("Report", "Visible outcome", TEAL),
          statChip("Audit", "Traceable user", BLUE),
        ]),
        tx("Discovery outcome: the pharmacy does not need another isolated screen. It needs one connected operating layer.", 22, "#B9EDE3", false, fill, { columnSpan: 2 }),
      ],
    ),
    darkBg(),
  );
}

// 4. Solution
twoCol(
  presentation.slides.add(),
  "Solution",
  "Smart Pharmacy brings the operating layer into one responsive system.",
  "The same platform connects products, suppliers, purchases, batches, sales, returns, expenses, closing, reports, alerts, and audit logs.",
  [
    bullet("One role-based shell for all daily modules."),
    bullet("Inventory updates from real business movements."),
    bullet("Topbar alerts and system messages keep action visible."),
    bullet("Reports and dashboards turn records into owner decisions."),
  ],
  imagePanel("dashboard-solution", shot("02-dashboard.png"), { height: fixed(620) }),
);

// 5. POS
twoCol(
  presentation.slides.add(),
  "Fast sales",
  "The POS is designed for speed without losing control.",
  "Search, select, discount, payment, customer details, receipt, and today sales stay in one work surface.",
  [
    bullet("Search medicine by name, category, type, code, or barcode."),
    bullet("Retail and wholesale modes support different selling contexts."),
    bullet("Inventory decreases immediately after sale completion."),
    bullet("Cashier can record POS expenses without leaving the sales flow."),
  ],
  imagePanel("pos-detail", shot("03-smart-pos.png"), { height: fixed(650) }),
);

// 6. Inventory
twoCol(
  presentation.slides.add(),
  "Stock intelligence",
  "Inventory is tracked by branch, product, batch, expiry, quantity, cost, and status.",
  "That structure protects the business from silent stock loss and protects customers from expired products.",
  [
    bullet("Batch and expiry records make risk visible."),
    bullet("Low stock and expiring items become action points."),
    bullet("Movements show before and after balances."),
    bullet("Automatic expiry write-off can remove unsafe stock from sale."),
  ],
  imagePanel("inventory-detail", shot("08-inventory.png"), { height: fixed(650) }),
);

// 7. Approvals
twoCol(
  presentation.slides.add(),
  "Controlled approvals",
  "Sensitive stock and finance actions are separated from normal entry.",
  "Users can create requests, while trusted roles approve, reject, verify, dispatch, receive, or recalculate.",
  [
    bullet("Sales returns require review before stock and finance are affected."),
    bullet("Stock adjustments protect against unapproved shrinkage or found stock changes."),
    bullet("Transfers separate request, approval, dispatch, and receiving."),
    bullet("Daily closing verification keeps cash accountability clean."),
  ],
  column({ width: fill, height: fill, gap: 20, justify: "center" }, [
    imagePanel("transfer-approval", shot("12-stock-transfers.png"), { height: fixed(295) }),
    imagePanel("adjustment-approval", shot("11-stock-adjustments.png"), { height: fixed(245) }),
  ]),
);

// 8. Visibility
twoCol(
  presentation.slides.add(),
  "Owner visibility",
  "The owner can see performance, risk, and exceptions without waiting for manual summaries.",
  "Dashboards and reports convert daily transactions into sales, stock, profit, expense, and accountability evidence.",
  [
    bullet("Revenue, gross profit, net profit, and cost sold are visible together."),
    bullet("Top products and trends support restocking and pricing decisions."),
    bullet("Report center centralizes sales, stock, purchase, profit, expense, and prescription reporting."),
    bullet("Activity logs preserve who did what and when."),
  ],
  column({ width: fill, height: fill, gap: 20, justify: "center" }, [
    imagePanel("profit-report", shot("18-profit-report.png"), { height: fixed(310) }),
    imagePanel("report-center", shot("17-report-center.png"), { height: fixed(235) }),
  ]),
);

// 9. Responsive
{
  const slide = presentation.slides.add();
  slideFrame(
    slide,
    grid(
      {
        width: fill,
        height: fill,
        padding: { x: 86, y: 66 },
        rows: [auto, fr(1), auto],
        columns: [fr(0.9), fr(1.1)],
        columnGap: 58,
        rowGap: 28,
      },
      [
        column({ columnSpan: 2, width: fill, gap: 14 }, [
          eyebrow("Responsive online access", TEAL),
          tx("One system, from counter screen to phone screen.", 62, "#FFFFFF", true, wrap(1240)),
          tx("Smart Pharmacy is web-based and responsive, so managers and authorized users can monitor operations across desktop, tablet, and mobile views.", 29, "#D5F3ED", false, wrap(1160)),
        ]),
        column({ width: fill, height: fill, gap: 18, justify: "center" }, [
          bullet("No separate desktop-only reporting habit.", "#D5F3ED"),
          bullet("Mobile dashboards keep owner visibility alive away from the branch.", "#D5F3ED"),
          bullet("Counter POS and smaller operational views share the same data source.", "#D5F3ED"),
        ]),
        row({ width: fill, height: fill, gap: 28, align: "center", justify: "center" }, [
          imagePanel("mobile-dashboard", shot("23-mobile-dashboard.png"), { width: fixed(220), height: fixed(560), fit: "cover" }),
          imagePanel("mobile-pos", shot("24-mobile-pos.png"), { width: fixed(220), height: fixed(560), fit: "cover" }),
          imagePanel("desktop-dashboard-small", shot("02-dashboard.png"), { width: fixed(430), height: fixed(300), fit: "cover" }),
        ]),
        tx("Responsive first: one login, one data source, multiple screen sizes.", 19, "#B9EDE3", false, fill, { columnSpan: 2 }),
      ],
    ),
    darkBg(),
  );
}

// 10. Why choose
{
  const slide = presentation.slides.add();
  const rows = [
    ["Ordinary POS", "Smart Pharmacy"],
    ["Sales screen only", "Sales + inventory + finance + reports"],
    ["Manual expiry checking", "Expiry alerts and write-off readiness"],
    ["Informal approvals", "Permission-based approval workflow"],
    ["Computer-bound view", "Responsive online access"],
    ["Hard to extend", "AI and communication-ready roadmap"],
  ];
  slideFrame(
    slide,
    grid(
      {
        width: fill,
        height: fill,
        padding: { x: 86, y: 66 },
        rows: [auto, fr(1), auto],
        columns: [fr(1), fr(1)],
        columnGap: 52,
        rowGap: 28,
      },
      [
        column({ columnSpan: 2, width: fill, gap: 12 }, [
          eyebrow("Why this system"),
          tx("It is advised because it controls the full pharmacy workflow, not only the receipt.", 58, INK, true, wrap(1390)),
        ]),
        column({ width: fill, height: fill, gap: 10 }, rows.map((r, i) =>
          grid(
            { width: fill, height: i === 0 ? fixed(58) : fixed(76), columns: [fr(1), fr(1)], columnGap: 10 },
            [
              panel({ fill: i === 0 ? "#E2E8F0" : "#FFFFFF", line: stroke("#D7E3F8", 1), padding: 14 }, tx(r[0], i === 0 ? 22 : 20, i === 0 ? INK : "#475569", i === 0, fill)),
              panel({ fill: i === 0 ? BLUE : "#EFF6FF", line: stroke("#BBD2FF", 1), padding: 14 }, tx(r[1], i === 0 ? 22 : 20, i === 0 ? "#FFFFFF" : INK, true, fill)),
            ],
          )
        )),
        imagePanel("product-setup-why", shot("05-product-setup.png"), { height: fixed(570) }),
        tx("Decision read: choose the system that connects daily speed with management control.", 19, MUTED, false, fill, { columnSpan: 2 }),
      ],
    ),
  );
}

// 11. Communication and AI
twoCol(
  presentation.slides.add(),
  "Future improvement",
  "AI and communication make the system proactive.",
  "The next layer can turn system events into the right message, sent to the right person, through the right channel.",
  [
    bullet("AI assistant for stock risk, report summaries, product lookup, and management questions."),
    bullet("Internal communication for approvals, tasks, branch notes, and issue follow-up."),
    bullet("SMS, email, and WhatsApp for alerts, receipts, customer reminders, and owner summaries."),
    bullet("Permission-aware AI so assistance never bypasses system access rules."),
  ],
  column({ width: fill, height: fill, gap: 20, justify: "center" }, [
    imagePanel("quick-search-ai", shot("22-quick-search.png"), { height: fixed(330) }),
    imagePanel("activity-ai", shot("21-activity-logs.png"), { height: fixed(220) }),
  ]),
);

// 12. Roadmap and recommendation
{
  const slide = presentation.slides.add();
  slideFrame(
    slide,
    grid(
      {
        width: fill,
        height: fill,
        padding: { x: 86, y: 66 },
        rows: [auto, fr(1), auto],
        columns: [fr(1.05), fr(0.95)],
        columnGap: 58,
        rowGap: 32,
      },
      [
        column({ columnSpan: 2, width: fill, gap: 14 }, [
          eyebrow("Recommendation", TEAL),
          tx("Move forward with Smart Pharmacy as the operating foundation.", 64, "#FFFFFF", true, wrap(1330)),
          tx("Start with the MVP core, then expand into AI, internal communication, SMS, email, WhatsApp, integrations, and owner mobile workflows.", 30, "#D5F3ED", false, wrap(1240)),
        ]),
        column({ width: fill, height: fill, gap: 18, justify: "center" }, [
          statChip("Phase 1", "Setup + Product + Users", TEAL),
          statChip("Phase 2", "POS + Purchases + Inventory", BLUE),
          statChip("Phase 3", "Approvals + Alerts + Reports", ORANGE),
          statChip("Phase 4", "AI + Communication + Integrations", TEAL),
        ]),
        imagePanel("closing-dashboard", shot("02-dashboard.png"), { height: fixed(610) }),
        tx("Best MVP value: accurate stock, fast sales, controlled approvals, expiry safety, owner visibility, and branch accountability.", 20, "#B9EDE3", false, fill, { columnSpan: 2 }),
      ],
    ),
    darkBg(),
  );
}

(async () => {
  const pending = presentation.getPendingImageHydrationRequests();
  presentation.hydrateImageAssets(
    pending
      .filter((req) => req.uri && fs.existsSync(req.uri))
      .map((req) => ({
        assetId: req.assetId,
        contentType: req.contentType,
        data: fs.readFileSync(req.uri),
      })),
  );

  for (let i = 0; i < presentation.slides.count; i += 1) {
    const slide = presentation.slides.getItem(i);
    const png = await slide.export({ format: "png" });
    fs.writeFileSync(
      path.join(PREVIEW, `slide-${String(i + 1).padStart(2, "0")}.png`),
      Buffer.from(await png.arrayBuffer()),
    );
  }
  const pptx = await PresentationFile.exportPptx(presentation);
  await pptx.save(path.join(OUT, "Smart_Pharmacy_3D_Executive_Presentation.pptx"));
  console.log(`presentation built: ${path.join(OUT, "Smart_Pharmacy_3D_Executive_Presentation.pptx")}`);
})();
