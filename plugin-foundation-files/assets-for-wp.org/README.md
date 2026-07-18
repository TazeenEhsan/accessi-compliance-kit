# WordPress.org Directory Assets

This folder holds the images uploaded to the WP.org plugin page (they go in the SVN `assets/` directory, **not** inside the plugin zip). Nothing here ships to users.

## Required files & specs

| File | Size | Notes |
|---|---|---|
| `screenshot-1.png` | ~1280×800 (any, keep <1MB) | Numbering must match readme.txt `== Screenshots ==` captions |
| `screenshot-2.png` | 〃 | 〃 |
| `screenshot-3.png` | 〃 | 〃 |
| `screenshot-4.png` | 〃 | 〃 |
| `banner-1544x500.png` | 1544×500 | High-DPI plugin-page header |
| `banner-772x250.png` | 772×250 | Standard banner (required if high-DPI provided) |
| `icon-256x256.png` | 256×256 | High-DPI icon |
| `icon-128x128.png` | 128×128 | Standard icon |

## Shot list (matches readme.txt captions — do not reorder without updating readme.txt)

Prep before shooting: clean admin (no other plugins' notices), Storefront theme, a store with demo products, browser at 1280px+ wide, WP admin color scheme default.

1. **screenshot-1** — Scan tab after scanning the shop page: severity groups (Critical/Serious/Moderate/Minor) visible, one violation row expanded showing the HTML snippet, selector, and help link.
2. **screenshot-2** — Settings tab: all six fix toggles with labels/descriptions/context badges visible, one or two toggled ON to show the interaction, save notice visible if possible.
3. **screenshot-3** — Accessibility Statement card: "not created" state with the **Create statement page** button (or the created state with the edit link — pick whichever reads better; caption says "one-click page creation").
4. **screenshot-4** — WP dashboard with the Accessi Compliance Kit widget: last scan date + per-severity counts + link to the full page.

## Demo video script (~90 seconds — proposal §6 Phase 4)

Record at 1920×1080, trim dead time, no audio needed if captioned; publish to YouTube and embed on the docs page. One take per scene is fine.

1. **(0:00–0:10) Hook.** Front end of a demo store. Caption: "Is your WooCommerce store accessible? The EU Accessibility Act says it has to be."
2. **(0:10–0:30) Scan.** Admin bar → *Scan this page* → Scan tab pre-filled → click **Scan this page** → progress → results appear. Caption: "One click. Runs locally — your data never leaves your server."
3. **(0:30–0:50) Review.** Scroll severity groups, expand one Critical issue: selector, why it fails, how to fix. Caption: "Every detected issue explained: what, where, and how to fix it."
4. **(0:50–1:10) Fix.** Settings tab → toggle **Checkout field labels** ON → save → split/before-after of checkout field markup in devtools (label now present). Caption: "Six one-toggle fixes for WooCommerce's most common issues. All off until you say so."
5. **(1:10–1:25) Statement.** Statement card → **Create statement page** → page opens in editor. Caption: "Generate the accessibility statement the EAA expects — then make it yours."
6. **(1:25–1:30) Close.** Plugin page/logo. Caption: "Accessi Compliance Kit — free on WordPress.org."
