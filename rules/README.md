# Critical Workspace Rules, PRD & AI Context

> **IMPORTANT MANDATE FOR AI ASSISTANTS & AGENTS (Antigravity, Gemini, Claude, Cursor, Windsurf, etc.):**
> Before executing tasks, implementing features, or writing code in this repository, you **MUST** read and adhere to all specification, PRD, and rule documents stored in this `rules/` directory.

---

## Folder Structure & Document Index

| File | Purpose & Description |
|---|---|
| [`01_PRD.md`](file:///C:/xampp/htdocs/LULU/rules/01_PRD.md) | **Product Requirements Document (PRD)** — System architecture, Storefront & Admin requirements, Telegram integration, entity schemas, payment workflows, and mobile-first design goals. |
| [`02_AI_ENGINEERING_RULES.md`](file:///C:/xampp/htdocs/LULU/rules/02_AI_ENGINEERING_RULES.md) | **Non-Negotiable AI Engineering Rules** — Tech stack enforcement (Laravel 11, PHP 8.3+, Inertia+React/Tailwind admin, Blade+Alpine storefront, Redis), architecture rules, zero full-page reloads, database transaction & atomic stock requirements, and security rules. |
| [`03_BUILD_PROMPTS.md`](file:///C:/xampp/htdocs/LULU/rules/03_BUILD_PROMPTS.md) | **Step-by-step build prompts** and milestone guidance for constructing the storefront, admin console, and Telegram integration. |

---

## Instructions for Adding New Context Files

Whenever you (the user or AI) add new `.md` files to this `rules/` folder (such as extra architectural diagrams, API contracts, design tokens, or feature specs):
1. Place the `.md` file inside `rules/`.
2. Reference the document here in `rules/README.md`.
3. AI assistants will automatically read and enforce all guidelines contained within `rules/*.md`.
