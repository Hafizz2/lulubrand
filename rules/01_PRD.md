# PRD — LULU'S Ordering Platform
Version 1.0 · Prepared for AI-assisted build (Antigravity / Claude)

---

## 1. Context & Goal

The client runs a women's clothing brand with a **large, frequently-changing catalog** (many categories, colourways, sizes, fabrics). She is **heavily mobile-first** — she will add new arrivals, edit stock, and manage orders almost entirely from her phone. The reference site is **House of CB** (app.houseofcb.com): clean editorial imagery, faceted filtering (style / size / colour / cup / length / fabric), hover-swap product images, colour-variant swatches inline on the grid, "New" style drops, and a fast, uncluttered checkout.

We are **not** cloning House of CB's code — we are matching its *quality bar*: fast, minimal, confident typography, generous imagery, and zero jank. The admin side must feel like a native app, not a PHP CRUD scaffold, the client's desire design and her answers are on file answer and on sample photos folder.

**Two products, one system:**
1. **Storefront** — customers browse, filter, and order , see size guidelines.
2. **Admin Console** — brand owner manages products/variants/stock/orders from mobile or desktop, with zero full-page reloads.
3. **Telegram Bot** — connective tissue: new-arrival broadcasts to customers, order-status pushes to customers, and instant new-order/low-stock alerts to the owner, 

---

## 2. Users & Devices

| User | Primary device | Needs |
|---|---|---|
| Shopper | Mobile browser (70-90% of traffic expected) | Fast browsing, easy filtering, frictionless checkout, order tracking without needing an account if possible |`
| Brand owner / admin | Mobile phone (primary), desktop (secondary) | Upload products + variants fast, from camera roll or camera, manage orders, manage users , see stock at a glance, get pinged on new orders , see finances , sales graph of day/week/month  |
| Staff | Mobile/desktop | Limited-role access to fulfil orders , add stock and manage stock , do most of the admin work but dont see finances , telegram account management, site settings etc|

---

## 3. Core Functional Requirements

### 3.1 Storefront
- Home page: hero/editorial banners (owner-editable), new arrivals rail, featured categories.
- Category / listing page:
  - Faceted filters: category, size, colour (swatch UI), fabric, price range, "in stock only".
  - Sort: newest, price asc/desc, best selling.
  - Grid with **hover/tap image swap** (primary + secondary product image) and **inline colour-variant thumbnails** (tapping a swatch swaps the whole card to that variant, no page reload).
  - Infinite scroll or "Load more" via AJAX (no full reload), URL-syncable filters (shareable/bookmarkable links, back-button correct).
- Product detail page:
  - Image gallery/zoom, variant selector (size + colour → resolves to exact SKU + stock), quantity, add to cart via AJAX with instant cart-count update and mini-cart drawer.
  - Size guide modal, related products, low-stock / sold-out badges.
- Cart & Checkout:
  - Cart page/drawer, editable quantities via AJAX, coupon/discount code field.
  - Guest checkout by default (name, phone, address, city) — this is common for East-African/regional clothing brands where card-based checkout isn't the norm; support **Cash on Delivery** and **bank/mobile-money manual confirmation** as first-class payment methods, with an extensible payment-method interface so a real gateway (Stripe/Chapa/Telebirr/etc.) can be plugged in later.
  - Order confirmation page + confirmation pushed to the customer's Telegram if they opt in (see §5).
- Order tracking: lookup by order number + phone (no login required), OR authenticated account order history. Status timeline: Pending → Confirmed → Packed → Shipped → Delivered → Cancelled/Refunded.
- Wishlist - basic account (register/login, saved addresses, order history).
- Fully responsive, mobile-first breakpoints; Lighthouse mobile performance target ≥ 90.

### 3.2 Admin Console (the part that must impress)
This must feel like a modern SaaS dashboard (think Shopify admin / Linear), **not** server-rendered PHP forms with submit buttons.
- **Dashboard**: today's orders, revenue snapshot, low-stock alerts, pending orders needing action — all as live-feeling widgets.
- **Products**:
  - List with search, filters, bulk actions (publish/unpublish, delete), inline quick-edit (price/stock) without opening a full page.
  - Create/Edit product: title, description, category, base price, multiple images with drag-to-reorder, **variant matrix builder** (size × colour × fabric → auto-generates SKUs with individual stock + optional price override).
  - **Mobile image upload**: direct camera capture or gallery picker, client-side compression before upload (critical — she'll be on mobile data), drag-to-reorder on touch, progress indicator, background upload so she can keep working.
- **Categories**: nested categories (e.g., Dresses → Maxi Dresses), drag-to-reorder, cover image.
- **Orders**: kanban-style or list with status filter, tap-to-update status (fires Telegram notification to customer automatically), order detail drawer (not a new page), print/export invoice.
- **Inventory**: stock adjustment log, low-stock threshold alerts.
- **Customers**: list, order history per customer.
- **Discounts/Coupons**: create % or fixed discounts, usage limits, expiry.
- **Telegram settings**: connect/disconnect broadcast channel, preview a "new arrival" broadcast before sending, toggle which events notify the owner.
- **Content**: manage homepage banners/hero images without a developer.
- Everything above is driven by an internal JSON API and rendered as an **SPA** — actions (save, delete, status change, stock edit) happen via fetch/XHR with optimistic UI + toasts, never a full page reload. Route transitions are client-side (no white-flash reload) but URLs still work as deep links.
- manage users - add user , delete user , give user privildges password, phone number , role and etc..

### 3.3 Telegram Bot (this is a first-class feature, not a plugin)
Two audiences, two behaviours:

**A. Customer-facing bot** (public bot the shopper can start):
- `/start` — welcome + link account by phone number or order number.
- New arrival broadcasts to subscribed customers (owner triggers from admin, or automatic when a product is published as "New").
- Order status push notifications ("Your order #1234 has shipped 🚚") sent automatically when admin changes order status.
- `/track <order_number>` command to check status inline in Telegram, no need to open the site.
- Optional: `/orders` to see recent order history if the Telegram account is linked to their account.

**B. Owner/admin-facing bot** (private chat/channel, restricted by chat ID):
- Instant push: "🛒 New order #1234 — 149 USD — 3 items" the second an order is placed.
- Low-stock alerts.
- Daily summary : orders count + revenue.

Architecture note: the bot runs via **webhook** (not long-polling) hitting a dedicated controller endpoint; outbound messages are dispatched through a **queued job**, never sent synchronously inside the request that creates the order (so a slow Telegram API call never slows down checkout).

---

## 4. Data Model (high level — AI should generate full migrations from this)

- `users` (customers + admin/staff, role column)
- `categories` (self-referencing parent_id for nesting)
- `products` (belongs to category; title, slug, description, base_price, status, is_new flag, published_at)
- `product_images` (product_id, url, sort_order, is_primary)
- `attributes` (e.g., Size, Colour, Fabric) + `attribute_values` (e.g., S/M/L, Red/Blue, Cotton/Silk)
- `product_variants` (product_id, sku, price_override, stock_quantity, image_id nullable) — variant is a specific combination of attribute values, modelled via a `variant_attribute_value` pivot
- `carts` / `cart_items` (session or user based)
- `orders` (order_number, customer info snapshot, status, payment_method, totals, telegram_chat_id nullable)
- `order_items` (snapshot of product/variant/price at time of order — never a live foreign lookup, so historical orders stay accurate even if price changes later)
- `discounts`
- `stock_movements` (audit log: reason, delta, resulting quantity, actor)
- `telegram_links` (user_id/order lookup ↔ telegram chat_id)
- `notifications_log` (what was sent, to whom, when, delivery status)

Money stored as integer minor units (cents), never floats.

---

## 5. Non-Functional Requirements

- **Performance**: storefront pages server-rendered/cached for SEO + speed; product images served via CDN/optimized (WebP, responsive `srcset`); admin API responses < 300ms p95 on typical endpoints.
- **Mobile-first**: every admin screen usable one-handed on a phone; touch targets ≥ 44px; upload flow tested on 3G-equivalent throttling.
- **No full page reloads** for any admin action (create/edit/delete/status-change/search/filter) — all via fetch/AJAX/SPA routing.
- **Scalability**: catalog should comfortably handle 10,000+ SKUs without slow queries — proper indexing, pagination everywhere, no `SELECT *` in hot paths, eager-loading to avoid N+1.
- **Security**: RBAC (owner vs staff), CSRF protection on all state-changing requests, rate limiting on auth/checkout endpoints, input validation server-side always (never trust client), image upload MIME/type validation, Telegram webhook secret-token verification.
- **Reliability**: queued jobs for anything slow/external (Telegram sends, image processing, emails) so user-facing requests stay fast; failed jobs retried with backoff and logged.
- **SEO**: clean slugs, meta tags, OpenGraph, sitemap.xml, server-rendered product/category HTML (not client-only rendering) for crawlability.
- **Observability**: error logging (e.g., Sentry-compatible), basic admin activity log (who changed what).

---

## 6. Explicit Non-Goals (v1)

To keep scope sane and shippable: multi-vendor marketplace, multi-currency, multi-language, native mobile app (PWA-installable web admin is enough for v1), real-time chat support. These can be phase-2+ if she asks.

---

## 7. Success Criteria

- Owner can add a new product with 3 colour variants and photos from her phone in under 3 minutes, with zero page reloads.
- A customer can filter to "Maxi Dresses, size M, colour red" and check out in under 2 minutes on mobile.
- The moment an order is placed, the owner's Telegram gets a push within a few seconds.
- Admin feels indistinguishable in responsiveness from a native app.
