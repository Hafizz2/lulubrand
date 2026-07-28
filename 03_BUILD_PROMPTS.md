# Build Prompts — feed these to Antigravity one at a time, in order

How to use this file: paste `01_PRD.md` and `02_AI_ENGINEERING_RULES.md` into the AI's project/system context first (or as the very first message, telling it "treat this as permanent context for the whole project"). Then run the phases below **one at a time**, waiting for each to finish and reviewing before moving to the next. Don't paste all phases at once — that's how you get a messy, half-finished monolith.

---

### Phase 0 — Project Setup
```
Set up a new Laravel 11 project for this build. Configure:
- MySQL connection, Redis for cache/session/queue
- Inertia.js + React + Tailwind CSS scaffolding for the admin panel (route prefix /admin)
- Blade + Alpine.js + Tailwind for the public storefront
- A clean folder structure separating admin (Inertia/React pages under resources/js/Admin)
  from storefront (Blade views under resources/views/storefront)
- Base layout files for both (empty shell is fine for now — no components yet)
- .env.example with placeholders for DB, Redis, Telegram bot token, Telegram webhook secret,
  app URL
Confirm the project boots and both an empty /admin route and an empty / storefront route
render successfully before moving on. Follow 02_AI_ENGINEERING_RULES.md for everything.
```

### Phase 1 — Data Model & Migrations
```
Based on section 4 of 01_PRD.md, generate all Eloquent models, migrations, and factories for:
users (with role), categories (nested), products, product_images, attributes,
attribute_values, product_variants (+ pivot for variant attribute values), carts, cart_items,
orders, order_items, discounts, stock_movements, telegram_links, notifications_log.
Money fields as integers (minor units). Add appropriate indexes and foreign keys.
Write a seeder that creates: 5 categories (nested at least one level), 15 demo products each
with 2-3 variants (size x colour), and 1 admin user. Run migrations + seed and confirm it works.
```

### Phase 2 — Auth & Roles
```
Implement authentication for two contexts:
1. Customer auth (register/login/logout, password reset) for the storefront, standard Laravel auth.
2. Admin/staff auth for the Inertia admin panel, with a role column (owner/staff) enforced via
   middleware + Laravel Policies. Only 'owner' can manage staff and Telegram settings.
Guest checkout must remain possible without requiring account creation — auth is optional for
customers, required for admin.
```

### Phase 3 — Storefront Catalog (public, read-only first)
```
Build the public storefront catalog per PRD section 3.1:
- Category/listing page with faceted filters (category, size, colour swatches, fabric,
  price range, in-stock only) and sort (newest, price asc/desc). Filtering and pagination
  ("Load more") must happen via AJAX/Alpine, no full page reload, and the URL query string
  must stay in sync so filtered views are shareable/bookmarkable.
- Product grid cards with hover/tap image swap (primary/secondary image) and inline colour
  swatch thumbnails that swap the card's image/price without navigating away.
- Product detail page with gallery, variant selector (size+colour resolving to a specific
  SKU/stock/price), quantity input, size guide modal, related products.
- Ensure everything is server-rendered for SEO (proper meta tags, slugs) with Alpine
  enhancing interactivity on top.
Reference feel: app.houseofcb.com's category and product pages (facet sidebar, swatch-driven
variant switching, minimal generous-whitespace grid) — match the *quality bar*, not the
literal design.
```

### Phase 4 — Cart & Checkout
```
Build cart + checkout per PRD section 3.1:
- AJAX add-to-cart with mini-cart drawer and live cart count, no reload.
- Cart page: editable quantities (AJAX), remove item, coupon code field, totals.
- Checkout: guest checkout by default (name, phone, address, city), Cash on Delivery and
  manual mobile-money confirmation as payment methods, behind a PaymentMethod interface so a
  real gateway can be added later without refactoring checkout.
- Order placement must run in a DB transaction, atomically decrement variant stock
  (never oversell under concurrent 
  requests), write a stock_movements row, and dispatch an
  OrderPlaced event.
- Confirmation page with order number; order lookup page (order number + phone) with a status
  timeline (Pending → Confirmed → Packed → Shipped → Delivered → Cancelled/Refunded).
Write a feature test that places two concurrent orders against the last unit of stock and
confirms only one succeeds.
```

### Phase 5 — Admin Console: Products & Variants (the mobile-critical part)
```
Build the Inertia/React admin Products section per PRD section 3.2:
- Product list: search, filters, bulk publish/unpublish/delete, inline quick-edit of price
  and stock directly in the table (no full edit page needed for quick changes) — all via
  fetch with optimistic UI + toast on success/failure, no reload.
- Product create/edit screen: title, description, category picker, base price, images
  (multi-upload, drag-to-reorder, works via touch), and a variant matrix builder — pick
  attributes (size/colour/fabric), the UI auto-generates the resulting SKU rows with
  editable stock and optional price override per variant.
- Mobile image upload: support camera capture + gallery multi-select, compress images
  client-side before upload, show per-file upload progress, upload can continue in the
  background while the admin keeps editing other fields.
- Every screen must be usable one-handed at a 375px viewport — check this explicitly.
```

### Phase 6 — Admin Console: Categories, Orders, Inventory, Customers, Discounts, Content
```
Build the remaining admin sections per PRD section 3.2:
- Categories: nested tree UI, drag-to-reorder, cover image.
- Orders: filterable list (status/date), tap-to-change status inline (dispatch an
  OrderStatusChanged event on change), order detail as a slide-over drawer (not a new page),
  printable/exportable invoice.
- Inventory: stock adjustment screen with a movement log/audit trail.
- Customers: list + per-customer order history.
- Discounts: create %/fixed discount codes with usage limits and expiry.
- Content: let the owner edit homepage hero/banner images and copy without a developer.
- Dashboard: today's orders, revenue snapshot, low-stock alerts, pending-action orders, built
  as small independent widgets that load their own data (don't block the whole dashboard on
  one slow query).
```

### Phase 7 — Telegram Bot Integration
```
Implement the Telegram bot per PRD section 5:
- Set up webhook endpoint (not polling) with secret-token verification, rate limited.
- Customer bot: /start (link account via phone or order number), /track <order_number>,
  optional /orders for linked accounts.
- New-arrival broadcast: triggered from the admin Telegram settings screen with a preview
  before sending, goes out as a queued job to all subscribed customer chat_ids (don't block
  the request; batch/throttle sends to respect Telegram rate limits).
- Order notifications: listener on OrderPlaced dispatches a queued job that pushes to the
  owner's admin chat_id immediately; listener on OrderStatusChanged dispatches a queued job
  that pushes to the customer's linked chat_id if one exists.
- Low-stock alert: when a variant's stock crosses below its threshold, queue a notification
  to the owner's chat_id (debounce so it doesn't spam on every unit sold).
- Add the connect/disconnect + preview UI in the Admin Telegram settings screen from PRD 3.2.
Write a test confirming an OrderPlaced event enqueues a Telegram job rather than sending
synchronously.
```

### Phase 8 — Hardening, Performance, and Launch Checklist
```
Do a hardening pass across the whole app:
- Confirm no N+1 queries on any list page (use debugbar/query log to verify).
- Confirm every admin mutation still uses no-reload AJAX/Inertia partial reloads.
- Add server-side validation (Form Requests) to any endpoint that's missing it.
- Add rate limiting to login, checkout, and the Telegram webhook if not already present.
- Add image optimization/responsive srcset to storefront product images.
- Add a sitemap.xml and basic OpenGraph/meta tags to storefront pages.
- Run through the checklist in section 6 of 02_AI_ENGINEERING_RULES.md for every feature
  built so far and report anything that fails it.
- Produce a short README covering: environment variables needed, how to run migrations/seed,
  how to set the Telegram webhook URL, and how to create the first owner admin account.
```

---

## Tips while running this with Antigravity

- If the AI drifts (e.g., starts using full page reloads in admin, or mixes frameworks), stop it immediately and paste the relevant rule from `02_AI_ENGINEERING_RULES.md` back at it — don't let a bad pattern compound across phases.
- Review each phase's diff/output before starting the next phase — catching a schema mistake in Phase 1 is cheap; catching it in Phase 6 is not.
- Keep a running `DECISIONS.md` of any assumption the AI flagged that you confirmed or overrode, so you don't re-litigate it in a later session.
