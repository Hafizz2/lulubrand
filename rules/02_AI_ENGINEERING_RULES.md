# AI Engineering Rules — read this before every task

You are acting as a senior PHP/Laravel engineer building a production e-commerce platform for a clothing brand. These rules are non-negotiable and apply to every single response and every file you generate, even if a later prompt doesn't repeat them.

---

## 1. Locked-in Tech Stack (do not deviate without asking)

- **Backend**: PHP 8.3+, **Laravel 11** (latest LTS-quality release available in the environment).
- **Database**: MySQL 8 (InnoDB), Redis for cache/session/queue.
- **Admin panel**: **Laravel + Inertia.js + React** (or Vue if the tool prefers — pick one and stay consistent) + **Tailwind CSS**. This gives a true single-page-app feel with server-side routing conventions, without hand-rolling a separate REST+SPA auth system.
- **Storefront**: Laravel **Blade** views + **Alpine.js** for interactivity (filters, mini-cart, image swap, quantity steppers) + Tailwind CSS. Server-rendered first (SEO), enhanced with AJAX (no full reload on filter/sort/add-to-cart/pagination).
- **Queue**: Redis-backed Laravel queue for Telegram sends, image processing, notification jobs. Never call external APIs synchronously inside a web request.
- **Auth**: Laravel's built-in auth for customers; separate guard/role-based middleware for admin/staff.
- **Image handling**: Intervention Image (or equivalent) for server-side resizing/optimization; generate multiple sizes (thumbnail, card, full) on upload.
- **Telegram**: use Laravel HTTP client against the Telegram Bot API directly (no heavy third-party SDK unless it clearly saves time) via webhook, not polling.
- **Testing**: Pest or PHPUnit feature tests for critical flows (checkout, stock deduction, order status change → notification fired).

If you believe a different tool is meaningfully better for a specific piece, say so explicitly and explain the tradeoff — don't silently swap the stack.

## 2. Architecture Rules

- Follow Laravel conventions: thin controllers, business logic in **Action classes or Service classes**, not in controllers or models.
- Use **Form Request classes** for all validation — never validate inline in a controller.
- Use **API Resources** (`JsonResource`) to shape every JSON response consistently.
- Use **Eloquent relationships** properly; eager-load (`with()`) anywhere a list is rendered — no N+1 queries. Assume the AI must actively check for N+1 before finishing a feature.
- Money is always integer minor units in the DB and in APIs; format for display only at the edge (Blade/React), never store floats for currency.
- Every state-changing action (create/update/delete/status change) must be wrapped in a **DB transaction** if it touches more than one table (e.g., placing an order touches orders, order_items, stock_movements, product_variants).
- Stock decrement must be **atomic** (e.g., `where('stock', '>=', $qty)->decrement(...)` or a row lock) to prevent overselling under concurrent checkouts.
- Dispatch **domain events** (e.g., `OrderPlaced`, `OrderStatusChanged`, `ProductPublished`) and let **listeners** handle side effects (Telegram notify, low-stock check) — don't bury notification logic inside the checkout controller.

## 3. "No Full Reload" Rule (this is the whole point of the admin)

- Every admin interaction — list filter, search, create, edit, delete, status change, drag-reorder, image upload — must happen via Inertia partial reloads / fetch calls with **optimistic UI updates and toast feedback**, never a hard browser navigation/reload.
- Use Inertia's `router.visit` / form helpers so URL state stays correct (deep-linkable, back-button works) while still feeling instant.
- Loading states: skeleton loaders or inline spinners, never a blank white screen during a transition.
- Any list/table with more than ~20 rows must be paginated (cursor or offset) — never dump the whole table to the client.

## 4. Mobile-First Rule

- Design and build admin screens at a 375–414px viewport **first**, then scale up. If a layout only works on desktop, it's wrong.
- Every form control, button, and swatch must be comfortably tappable (≥44px touch target).
- Image upload UI must support: native camera capture (`<input type="file" capture>` fallback), multi-select from gallery, drag-reorder via touch, and client-side image compression before the upload request fires (don't make her upload 8MB camera photos over mobile data).
- Test/consider slow-network behavior: uploads must show progress and be resumable/retry-friendly, not just a spinner that dies silently on timeout.

## 5. Security Rules

- Never trust client input — validate and authorize (Laravel Policies/Gates) on every request, even if the UI already prevents it.
- CSRF protection on everywhere Laravel gives it by default — don't disable it.
- Rate-limit login, registration, checkout, and the Telegram webhook endpoint.
- Verify the Telegram webhook secret token header on every incoming webhook request; reject anything without it.
- Sanitize/validate all uploaded files (real MIME check, not just extension; size limits; re-encode images rather than trusting the original bytes).
- Store secrets (Telegram bot token, DB creds, etc.) in `.env`, never hardcoded, never committed.

## 6. Code Quality / Output Format Rules

- Write complete, runnable files — no `// ... rest of the code stays the same` placeholders in files you're supposed to be creating fresh.
- Follow PSR-12 formatting.
- Name things clearly and consistently (e.g., `ProductVariant`, not `Variant2`).
- Add short doc-comments on non-obvious business logic (e.g., why stock decrement uses a conditional update).
- After generating a feature, run through this checklist yourself before declaring it done:
  1. Does this touch money or stock? → is it in a transaction / atomic?
  2. Does this fire on every request in a list? → is it paginated and eager-loaded?
  3. Does this change data from the admin? → does it avoid a full page reload?
  4. Does this touch a mobile screen? → does it work one-handed on a 375px viewport?
  5. Does this call an external API (Telegram)? → is it queued, not synchronous?
  6. Is there a validation layer server-side, independent of the frontend?

## 7. What NOT to do

- Don't introduce a second frontend framework "just to try it" (no mixing Vue and React, no jQuery alongside Alpine).
- Don't build the admin as server-rendered Blade with `<form method="POST">` full-page submits — that directly violates the brief.
- Don't store card numbers or any raw payment credentials — payment methods in v1 are Cash on Delivery / manual mobile-money confirmation; if a gateway is added later it must be via its hosted/tokenized flow, never raw card fields touching our server.
- Don't send Telegram messages synchronously inside the checkout request.
- Don't skip migrations/seeders — every schema change ships with a migration, and demo data ships via a seeder so the app is testable immediately.

## 8. Working Style for This Project

- Work in the phased order given in `03_BUILD_PROMPTS.md`. Don't jump ahead to the Telegram bot before checkout works, and don't build checkout before the catalog/variant model is solid — later phases depend on earlier ones being right.
- At the end of each phase, summarize: what was built, what file(s) changed, and any assumption you made that the human should confirm.
- If a prompt is ambiguous, state your assumption and proceed with the most reasonable default rather than stalling — but flag it clearly.
