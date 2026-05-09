from __future__ import annotations

import math
import textwrap
from pathlib import Path

from PIL import Image, ImageDraw, ImageFont


ROOT = Path(__file__).resolve().parents[1]
RAW = ROOT / "screenshots" / "raw"
OUT = ROOT / "screenshots" / "annotated"
OUT.mkdir(parents=True, exist_ok=True)

FONT = Path(r"C:\Windows\Fonts\arial.ttf")
BOLD = Path(r"C:\Windows\Fonts\arialbd.ttf")


def font(size: int, bold: bool = False) -> ImageFont.FreeTypeFont:
    path = BOLD if bold and BOLD.exists() else FONT
    return ImageFont.truetype(str(path), size=size) if path.exists() else ImageFont.load_default()


def arrow(draw: ImageDraw.ImageDraw, start: tuple[int, int], end: tuple[int, int], color: str) -> None:
    draw.line([start, end], fill=color, width=5)
    angle = math.atan2(end[1] - start[1], end[0] - start[0])
    head_len = 18
    head_ang = math.pi / 7
    p1 = (
        end[0] - head_len * math.cos(angle - head_ang),
        end[1] - head_len * math.sin(angle - head_ang),
    )
    p2 = (
        end[0] - head_len * math.cos(angle + head_ang),
        end[1] - head_len * math.sin(angle + head_ang),
    )
    draw.polygon([end, p1, p2], fill=color)


def label(
    base: Image.Image,
    text: str,
    box_xy: tuple[int, int],
    target: tuple[int, int],
    color: str = "#2563EB",
    max_width: int = 290,
) -> None:
    overlay = Image.new("RGBA", base.size, (255, 255, 255, 0))
    draw = ImageDraw.Draw(overlay)
    title_font = font(20, True)
    body_font = font(16)

    words = text.split(":", 1)
    title = words[0].strip()
    body = words[1].strip() if len(words) > 1 else ""
    lines = [title]
    if body:
        lines += textwrap.wrap(body, width=34)

    line_heights = [title_font.getbbox(lines[0])[3] - title_font.getbbox(lines[0])[1] + 4]
    for line in lines[1:]:
        bbox = body_font.getbbox(line)
        line_heights.append(bbox[3] - bbox[1] + 4)

    box_w = max_width
    box_h = 22 + sum(line_heights) + 18
    x, y = box_xy

    draw.rounded_rectangle(
        [x + 5, y + 7, x + box_w + 5, y + box_h + 7],
        radius=18,
        fill=(15, 23, 42, 38),
    )
    draw.rounded_rectangle(
        [x, y, x + box_w, y + box_h],
        radius=18,
        fill=(255, 255, 255, 236),
        outline=color,
        width=3,
    )

    draw.ellipse([x + 17, y + 17, x + 35, y + 35], fill=color)
    draw.text((x + 48, y + 14), title, font=title_font, fill="#0F172A")
    yy = y + 43
    for line in lines[1:]:
        draw.text((x + 22, yy), line, font=body_font, fill="#334155")
        yy += 22

    start = (x + box_w // 2, y + box_h)
    if target[1] < y:
        start = (x + box_w // 2, y)
    elif target[0] < x:
        start = (x, y + box_h // 2)
    elif target[0] > x + box_w:
        start = (x + box_w, y + box_h // 2)

    arrow(draw, start, target, color)
    base.alpha_composite(overlay)


module_callouts = {
    "01-login": [
        ("Secure access: Users sign in with email or username.", (930, 135), (820, 365), "#2563EB"),
        ("Authentication: Password field protects role-based access.", (120, 640), (720, 485), "#14B8A6"),
    ],
    "02-dashboard": [
        ("Business overview: Revenue, profit, activity, and top products are visible immediately.", (810, 118), (620, 455), "#2563EB"),
        ("Filters: Branch and date controls refine executive performance views.", (585, 675), (530, 293), "#F97316"),
    ],
    "03-smart-pos": [
        ("Fast product search: Cashier finds medicine by name, code, category, or barcode.", (90, 610), (255, 158), "#2563EB"),
        ("Current sale: Cart, discount, payment method, and checkout stay on one screen.", (940, 605), (1215, 492), "#14B8A6"),
    ],
    "04-pharmacy-setup": [
        ("Profile and branches: Maintain pharmacy identity, main branch, and branch details.", (780, 120), (915, 330), "#2563EB"),
        ("Settings: Currency, expiry warnings, receipt footer, and selling rules are configured here.", (150, 640), (640, 525), "#F97316"),
    ],
    "05-product-setup": [
        ("Product catalog: Products, types, categories, units, structures, and prices live together.", (780, 118), (665, 270), "#2563EB"),
        ("Action controls: Import, export, or add a new product from the same workspace.", (130, 655), (1250, 153), "#14B8A6"),
    ],
    "06-suppliers": [
        ("Supplier records: Supplier contacts and active status support controlled purchasing.", (790, 120), (610, 590), "#2563EB"),
        ("Management action: Add or update suppliers before purchase receiving.", (130, 650), (1230, 155), "#F97316"),
    ],
    "07-purchases": [
        ("Purchase receiving: Create purchase orders and receive stock into inventory batches.", (780, 110), (680, 525), "#2563EB"),
        ("Traceability: Supplier, branch, status, cost, and item data remain auditable.", (120, 645), (900, 595), "#14B8A6"),
    ],
    "08-inventory": [
        ("Batch stock control: Inventory is tracked by branch, batch, expiry, quantity, and cost.", (760, 115), (865, 595), "#2563EB"),
        ("Risk filters: Low stock, expiring, expired, and blocked stock can be isolated quickly.", (120, 650), (540, 470), "#F97316"),
    ],
    "09-inventory-movements": [
        ("Movement history: Every stock increase, sale, return, adjustment, and transfer is traceable.", (750, 115), (810, 560), "#2563EB"),
        ("Audit view: Before and after balances show the effect of each transaction.", (100, 650), (1025, 590), "#14B8A6"),
    ],
    "10-inventory-alerts": [
        ("Alert center: Low stock, out-of-stock, expiring, and expired risks are centralized.", (755, 116), (635, 575), "#2563EB"),
        ("Resolution workflow: Alerts can be read, resolved, ignored, or generated manually.", (95, 650), (1210, 155), "#F97316"),
    ],
    "11-stock-adjustments": [
        ("Controlled correction: Adjust damaged, expired, lost, found, or counted stock through approvals.", (730, 115), (640, 585), "#2563EB"),
        ("Status visibility: Draft, approved, rejected, and cancelled actions remain visible.", (105, 650), (920, 580), "#14B8A6"),
    ],
    "12-stock-transfers": [
        ("Branch movement: Transfer stock from source branch to destination branch in approved stages.", (735, 115), (650, 585), "#2563EB"),
        ("Dispatch and receive: Workflow separates approval, dispatch, and receiving accountability.", (105, 650), (915, 580), "#F97316"),
    ],
    "13-sales-history": [
        ("Sales history: Receipts, customers, totals, profit, and payment method are searchable.", (770, 118), (750, 585), "#2563EB"),
        ("Receipt control: Authorized users can review, cancel, or reprint receipts.", (105, 650), (1235, 585), "#14B8A6"),
    ],
    "14-sales-returns": [
        ("Return requests: Returned items are captured with condition, refund, and approval status.", (750, 115), (700, 585), "#2563EB"),
        ("Stock impact: Only sellable approved returns restore inventory.", (105, 650), (1080, 580), "#F97316"),
    ],
    "15-expenses": [
        ("Expense recording: Operational costs are categorized by branch, date, and payment method.", (750, 115), (685, 590), "#2563EB"),
        ("Closing impact: Cash expenses feed the daily expected-cash calculation.", (105, 650), (920, 455), "#14B8A6"),
    ],
    "16-daily-closing": [
        ("Cash accountability: Expected cash, counted cash, and differences are calculated per day.", (745, 115), (635, 595), "#2563EB"),
        ("Verification: Draft, submitted, verified, rejected, and recalculation states guide review.", (105, 650), (940, 590), "#F97316"),
    ],
    "17-report-center": [
        ("Report center: Sales, stock, purchases, profit, expenses, and prescriptions are organized here.", (750, 115), (695, 500), "#2563EB"),
        ("Export-ready insights: Managers can filter and export operational reports.", (105, 650), (1240, 160), "#14B8A6"),
    ],
    "18-profit-report": [
        ("Profit view: Net sales, cost sold, gross profit, expenses, and net profit are analyzed together.", (745, 115), (640, 475), "#2563EB"),
        ("Decision support: Date and branch filters turn transaction data into management insight.", (105, 650), (530, 295), "#F97316"),
    ],
    "19-users": [
        ("User administration: Staff accounts are tied to roles, branches, status, and permissions.", (720, 115), (710, 585), "#2563EB"),
        ("Access control: Add, edit, activate, deactivate, and reset credentials from one page.", (105, 650), (1220, 150), "#14B8A6"),
    ],
    "20-roles": [
        ("Role design: Permissions define what each role can see and do.", (740, 115), (655, 585), "#2563EB"),
        ("Least privilege: Approval rights are separated from normal operational entry.", (105, 650), (1115, 590), "#F97316"),
    ],
    "21-activity-logs": [
        ("Audit trail: Important actions are recorded with user, event, time, and details.", (755, 115), (785, 585), "#2563EB"),
        ("Investigation tools: Filters help owners review activity by log type and period.", (105, 650), (520, 465), "#14B8A6"),
    ],
    "22-quick-search": [
        ("Global search: Products, receipts, batches, transfers, expenses, and records can be found quickly.", (770, 130), (790, 50), "#2563EB"),
        ("Permission-aware results: Users only see records their role is allowed to open.", (115, 650), (745, 245), "#F97316"),
    ],
    "23-mobile-dashboard": [
        ("Responsive dashboard: Core metrics and filters adapt to narrow screens.", (45, 665), (190, 165), "#2563EB"),
    ],
    "24-mobile-pos": [
        ("Mobile POS: Search, cart, and payment remain usable on a phone-sized screen.", (42, 650), (205, 150), "#14B8A6"),
    ],
}

fallback = [
    ("Primary workspace: Use filters, actions, and table controls to manage the module.", (770, 115), (710, 590), "#2563EB"),
    ("Navigation: Sidebar keeps each workflow reachable from the same system shell.", (95, 650), (95, 455), "#14B8A6"),
]


for src in sorted(RAW.glob("*.png")):
    img = Image.open(src).convert("RGBA")
    callouts = module_callouts.get(src.stem, fallback)
    for text, pos, target, color in callouts:
        label(img, text, pos, target, color=color)
    out = OUT / src.name
    img.convert("RGB").save(out, quality=92)
    print(out)
