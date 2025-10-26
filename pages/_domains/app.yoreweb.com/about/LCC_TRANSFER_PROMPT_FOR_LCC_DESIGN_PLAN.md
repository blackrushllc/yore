# Prompt for ChatGPT — Transfer Context for Junie Setup

You are ChatGPT (GPT‑5). I’m opening this new chat to continue the Luxe Card Club work in the thread that already contains extensive Yore framework and Modeltroller context.

Please read the following as a **handover summary** of everything completed in the other Luxe Card Club chat so far, and use it to compose the *next prompt* that will instruct Junie Ultimate to continue from Section 3: Workflows and User Stories.

---

# Luxe Card Club Status Summary (for your understanding)

Erik has provided:

* The full MySQL schema (`lcc_` tables + `_csv` staging tables) and sample data.
* Two Excel spreadsheets mapping business and inventory workflows.
* A Project Brief markdown document.

You and Erik have:

* Designed and normalized the schema, creating canonical tables prefixed `lcc_`.
* Defined detailed SQL migrations, foreign keys, and importer views.
* Completed the **Importer Specification** mapping `_csv` staging tables to canonical entities.

All database work and importer logic design are complete. Erik has stopped at **SECTION 3: DATA IMPORTS**. The next phase involves designing user‑story‑based workflows and translating them into Yore Modeltrollers and routable web/api endpoints.

---

# Your Task in This New Chat

Create a concise **prompt for Junie Ultimate** that:

1. Summarizes the completed work above.
2. Directs Junie to begin Phase 3 — implementing Workflows and User Stories for Luxe Card Club.
3. Specifies that Yore now supports **Modeltrollers**, i.e., Model classes that also act as Controllers with `web_` and `api_` routable methods.
4. Instructs Junie to use the canonical schema and importer design as the foundation for generating:

    * Operational workflows (Inventory, Breaks, Finance, Shipping).
    * UI and API routes.
    * Example Modeltroller classes and controller logic.
    * Module‑level build roadmap for `lcc_orders`, `lcc_breaks`, `lcc_finance`, `lcc_ship`.

Your output should be a **finalized prompt** ready to paste into Junie Ultimate, incorporating everything done so far and directing it to continue from Section 3.

--- — Luxe Card Club Context Summary

You are ChatGPT (GPT‑5) continuing work on the Luxe Card Club project inside the Yore PHP framework.

## Summary of progress so far

Erik has uploaded and reviewed all source materials: the MySQL schema (with sample data), two Excel spreadsheets (business and inventory workflows), and a Project Brief markdown file. We analyzed these and produced:

* A **complete normalized data model**, with canonical tables prefixed `lcc_`.
* Detailed **SQL migrations** and **foreign keys**, which Erik has already implemented in the live database.
* A full **Importer Specification** showing how `_csv` staging tables map into canonical Yore entities.

All schema refactoring and import logic design are now finished. Erik has stopped at **SECTION 3: DATA IMPORTS**, which is the next phase of work.

## Next tasks for Junie (Phase 3)

You should now:

1. Read and understand the current canonical schema (the `lcc_` tables) and importer relationships.
2. Continue from **SECTION 3: DATA IMPORTS**, generating:

    * Operational workflows derived from the Excel business sheets (inventory, breaks, finance, scheduling).
    * User‑story‑driven UI and API routes inside Yore.
    * Example `Modeltrollers` (a Yore‑specific construct that merges Model + Controller) implementing routable `web_` and `api_` methods.
3. Produce module‑level design plans for `lcc_orders`, `lcc_breaks`, `lcc_finance`, and `lcc_ship`, consistent with Yore conventions and the new Modeltroller pattern.
4. Ensure every route and workflow maps clearly to its corresponding schema entities and CRUD actions.

## Context for Yore

Yore is a Laravel‑like PHP framework that now supports **Modeltrollers** — hybrid Model classes containing both controller logic and routable `web_` / `api_` methods. These automatically become URL‑routable endpoints.

You are to generate the continuing roadmap, implementation sketches, and example PHP stubs using this architecture.

---

Turn your existing assets (MySQL schema + sample data, 2 Google Sheets, a Markdown brief) into a crisp product definition and a pragmatic build roadmap for a Yore-based web app.

---

## How to share everything with AI (fastest path)

1. **Database**

* Export a **schema-only SQL** dump and a **small data sample** (e.g., 200–1,000 rows per key table).
* If easier, export each table to **CSV** with headers.
* Include ERD images if you have them (optional).

2. **Google Sheets**

* In each Sheet: **File → Download → CSV**.
* Name files clearly, e.g., `orders_reference.csv`, `workflows_notes.csv`.
* If there are multiple tabs, export each tab separately.

3. **Docs**

* Provide the Markdown file as-is (`ProjectBrief.md`).
* If there are embedded links, paste them inline at the bottom of the doc.

4. **Upload here**

* Drag/drop the SQL/CSV/MD files into this chat.
* If something can’t be exported, paste the content inline (wrap long SQL in a code block).

> Once you upload, I’ll parse, profile, and produce a concise spec + roadmap immediately.

---

## What I’ll produce from your uploads

1. **Domain Model** (entities, relationships, cardinalities).
2. **Glossary** of terms (e.g., Breaks, Listings, Orders, Shipments, Fees).
3. **Data Contract** (column-level dictionary per table + integrity rules).
4. **Oper. Workflows** (from Sheets → real-world processes mapped to screens).
5. **MVP Scope** (weeks 1–4) + **Phase 2/3** backlog.
6. **Yore Build Plan** (modules, routes, views, CLI jobs, DB migrations).
7. **Risk & Gaps** (what’s missing/ambiguous and proposed defaults).

---

## Quick intake checklist (so nothing’s missed)

* [ ] Schema-only SQL dump (DDL).
* [ ] Sample data (CSV or limited SQL INSERTs) for core tables: **Orders, Listings, Breaks, Customers/Buyers, Inventory, Shipments, Fees/Commissions, Payouts**.
* [ ] The 2 Google Sheets as CSV (all tabs).
* [ ] Original Markdown brief.
* [ ] Any WhatNot CSV exports you already have (e.g., **Livestream**, **Orders**, **Payouts**).
* [ ] Any **status codes** or enum meanings (e.g., `transaction_type`, `sale_type`).
* [ ] Any **identifiers** that join systems (e.g., WhatNot `order_id` ↔ internal `order_id`).

---

## Likely domain for Luxe Card Club (starter template)

**Core entities**

* **Listing** (id, title, description, category, sku, price, buy_format, sale_type, status).
* **Break** (id, livestream_id, title, scheduled_at, host, format, team/random/slot schema).
* **Order** (order_id, buyer_id, listing_id, quantity, price, coupon, fees, tax, currency, timestamps).
* **Buyer** (id, name, state, country, contact handles).
* **Inventory Item** (sku, product, condition, location, cost).
* **Shipment** (shipment_id, carrier, tracking, shipped_at, cost, status).
* **Fees** (commission, processing, tax on fees), **Payout** (net to seller).
* **User/Staff** (roles: admin, seller, shipper, accountant).

**Key relationships**

* Listing 1—* Orders.
* Break 1—* Listings (optional) and/or Break 1—* Orders.
* Buyer 1—* Orders; Order 1—1 Shipment (often) or 1—* if split.
* Orders → Fees/Adjustments; Payout aggregates by period.

---

## MVP (suggested 4–6 weeks)

**Week 1 — Data & Foundations**

* Normalize schema in MySQL (migrations under Yore’s Orm).
* Build **Importer** for WhatNot CSVs (Orders / Livestream / Payouts).
* Data validation & dedupe (buyers, SKUs).
* Seed minimal admin user and role model.

**Week 2 — Core Views**

* **Orders** index (filter by date, status; pluck/export CSV).
* **Buyers** index + detail (orders, spend, shipping history).
* **Listings** index (active, sold, profit estimate).

**Week 3 — Operations**

* **Shipments** workflow: pick/pack list, create shipment, record cost/tracking; status sync.
* **Breaks** dashboard: attach orders/listings to a break; slot/team assignment notes.

**Week 4 — Finance**

* Fees & Payouts reconciliation (commission, processing, taxes on fees).
* Profit per order/listing/break; period P&L.
* Export to bookkeeping CSV.

**Week 5–6 — Quality & Extras**

* Role-based permissions; activity log.
* Advanced search (by buyer handle, SKU, livestream).
* Scheduled importer (cron) + email/Slack summaries.

---

## Yore build plan (concrete)

**Modules**

* `modules/orm` – your lightweight ORM layer (already planned).
* `modules/lcc_orders` – Order/Buyer/Listing models + controllers.
* `modules/lcc_ship` – Shipment flows, labels (if integrating later), costs.
* `modules/lcc_finance` – Fees, payouts, reconciliation, reports.
* `modules/lcc_breaks` – Break management, slotting.

**Routes (examples)**

* `GET /orders`, `GET /orders/{id}`
* `GET /buyers`, `GET /buyers/{id}`
* `GET /listings`
* `GET /breaks`, `POST /breaks/{id}/attach-order`
* `GET /finance/payouts`, `GET /reports/pnl`

**Views**

* DataTables for large lists; server-side pagination.
* Common filters: date range, status, sku, buyer, livestream.
* CSV export from every index.

**CLI / Cron**

* `php cli.php lcc:import whatnot:orders <csv>`
* `php cli.php lcc:import whatnot:livestream <csv>`
* `php cli.php lcc:reconcile:payouts <csv>`
* Nightly: re-run reconciliation and email summary.

---

## Prompts you can paste into Junie (Yore-aware)

**1) Project ingest**

> *“Scan the uploaded CSVs (`orders.csv`, `livestream.csv`, `payouts.csv`) and the SQL DDL dump. Build a unified **Data Dictionary** per table (columns, types, nullability, examples) and infer **foreign keys** and **enums**. Output in Markdown under headings: Entities, Relationships, Integrity Rules, Enumerations, and Questions/Gaps.”*

**2) ERD & glossary**

> *“From the Data Dictionary, generate an **ERD description** (text-first) and a **plain-English glossary**. Highlight many-to-many joins (if any) and suggest junction tables.”*

**3) Yore scaffolding**

> *“Create Yore modules `lcc_orders`, `lcc_ship`, `lcc_finance`, `lcc_breaks` with minimal controllers, routes, and views. Implement server-side pagination, filters, and CSV export. Use our Orm conventions: model classes in `/modules/<name>/models`, controllers in `/modules/<name>/controllers`, views in domain-based folders, and `cli.php` commands as described.”*

**4) Importers**

> *“Implement CSV importers for WhatNot exports: map columns to our schema, validate rows, log rejects, and upsert on keys (`order_id`, `livestream_id`, `buyer_name`). Add idempotency using file hash + row hash.”*

**5) Reconciliation**

> *“Implement payout reconciliation: from `payouts.csv` join orders → compute per-order net, fees, taxes on fees. Provide `/reports/pnl` with groupings by day, week, livestream, listing, and break.”*

**6) QA fixtures**

> *“Generate seed fixtures from sample CSVs for local dev, plus 20 synthetic buyers & 50 orders with varied fees and shipping statuses.”*

---

## Prompts you can paste into ChatGPT (this chat)

**Upload-first**

> *“Here are the schema SQL and three CSVs. Please: (1) infer entities & relationships, (2) produce a Data Dictionary, (3) list 10–15 concrete user stories, (4) propose an MVP and Phase 2 plan for Yore modules.”*

**Gaps analysis**

> *“From these files, identify missing fields, ambiguous enums, and risky assumptions. Propose defaults and a decision log I can confirm with the client.”*

**Screen sketches (text-first)**

> *“Propose the exact table columns and filters for `/orders`, `/buyers`, `/listings`, `/breaks`, and `/finance/payouts`. Keep it to the essential operator needs.”*

---

## Decision log template (keep this short and living)

* **ID**: `DL-0001`
* **Topic**: e.g., `Order.status` meanings
* **Options**: [ ] A … [ ] B …
* **Decision**: A
* **Rationale**: …
* **Owner**: …
* **Date**: …

---

## Risks & mitigations (likely)

* **WhatNot export drift** → Build importer with column map versioning & schema checks.
* **Duplicate buyers** → Normalize on name+state; later, add email/handle-based merge UI.
* **Break-slot logic variance** → Keep model flexible: `breaks`, `break_slots`, `break_orders`.
* **Performance (large CSVs)** → Chunked import, server-side tables, background jobs.

---

## Domain Model — Draft v0 (from SQL schema)

### Core import/staging entities

* **lcc_uploaded_files** — registry of files you’ve imported; parent to all `*_csv` tables.
* **lcc_livestream_csv** — per‑order records exported from WhatNot livestreams (order ids, buyer handle, product, tracking, coupon, costs, timestamps). Parent key: `order_id` (string) + `placed_at` + `buyer` (unique). FK: `uploaded_file_id → lcc_uploaded_files.id`.
* **lcc_ledger_csv** — financial events (sales earnings, giveaways/charges, adjustments) keyed by (`listing_id`,`order_id`,`status`,`transaction_type`,`date`). FK: `uploaded_file_id → lcc_uploaded_files.id`. Candidate joins: `order_id ↔ lcc_livestream_csv.order_numeric_id` (normalize types) and `listing_id ↔ internal Listings`.
* **lcc_breaks_csv** — rows tying break format/title/product to buyer username, order number, and price. Unique (`order_no`,`username`). FK: `uploaded_file_id → lcc_uploaded_files.id`.
* **lcc_weekly_orders_csv** — (staging) weekly rollups of orders (structure similar to `livestream_csv`, used for ingestion checks).

### Operational/entities (app-owned)

* **lcc_inventory** — current inventory plus vendor/grade metadata. Several id columns are `varchar` placeholders; should be normalized.
* **lcc_expenses**, **lcc_expense_types** — transactional expenses + type lookup.
* **lcc_revenue**, **lcc_revenue_types** — non‑WhatNot revenue entries + type lookup (e.g., shows/LGS/private sales).
* **lcc_grading_comps** — lookup (PSA, BGS, SGC, …).
* **lcc_schedule** — calendar rows for shows/breaks/staffing.
* **lcc_singles** — catalog for individual cards (early draft).
* **lcc_users** — Yore users/staff.

### High‑level relationships (conceptual)

* `lcc_uploaded_files 1—* {lcc_livestream_csv, lcc_ledger_csv, lcc_breaks_csv, lcc_weekly_orders_csv}` (actual FKs present).
* `lcc_livestream_csv 1—* lcc_ledger_csv` via `order_id` (needs type alignment) and optionally via `listing_id`.
* `lcc_expenses *—1 lcc_expense_types` (proposed FK).
* `lcc_inventory *—1 lcc_grading_comps` (proposed FK), and `lcc_expenses *—1 lcc_inventory` (optional if itemized).
* Future: introduce **Buyers**, **Listings**, **Orders**, **Shipments** as app‑owned tables, then map WhatNot CSVs into them.

---

## Data Dictionary (key tables)

### lcc_livestream_csv (staging)

* **Keys**: `UNIQUE(order_id, placed_at, buyer)`; `uploaded_file_id` FK.
* **Notable columns**: `order_id` (string id), `order_numeric_id` (string number), `buyer` (handle), `product_name/description`, `product_quantity`, `sold_price` (⚠︎ stored as `varchar` incl. `$`), `tracking`, `shipment_id`, `shipping_address`, `postal_code`, `placed_at` (string timestamp), `coupon_code/price`, `cost_per_item`, `total_cost`, `sku`.
* **Purpose**: raw WhatNot order feed per sale; source for Orders, Buyers, Shipments.

### lcc_ledger_csv (staging)

* **Keys**: `UNIQUE(listing_id, order_id, status, transaction_type, date)`; `uploaded_file_id` FK.
* **Notable columns**: `amount` (⚠︎ `varchar` with currency symbol), `message`, `status` (e.g., `completed`, `processing`), `transaction_type` (e.g., `SALES`, `ADJUSTMENT`).
* **Purpose**: financial line items (earnings, fees, giveaways) per order/listing.

### lcc_breaks_csv (staging)

* **Keys**: `PRIMARY(id)`, `UNIQUE(order_no, username)`; `uploaded_file_id` FK.
* **Notable columns**: `format` (e.g., Break), `title` (show label), `product` (team/slot), `price` (⚠︎ `varchar` with `$`).
* **Purpose**: team/slot sales by break; helps link orders to specific break configurations.

### lcc_inventory (app‑owned)

* **Keys**: `PRIMARY(id)`.
* **Notable columns**: `price`, `value` (⚠︎ `varchar`); `grading_comp_id` (⚠︎ `varchar`), `grade`, `customer_id` (⚠︎ `varchar`), `date_of_order/received` (⚠︎ `varchar`), product/case/box counts and costs.
* **Purpose**: product catalog + purchase costing; target for normalization and joins to sales.

### lcc_expenses / lcc_expense_types (app‑owned)

* **Keys**: `PRIMARY(id)`.
* **Notable columns**: `expense_type_id` (int), `amount` (`decimal(9,2)`), optional links (`breaker_id`, `inventory_id`, `customer_id`, …).
* **Purpose**: P&L costs; types include Cards/Boxes/Supplies/Travel/General.

### lcc_grading_comps (lookup)

* **Values**: PSA, BGS, SGC, HGA, CGC, TAG, Arena Club, ISA, Rare Edition, FCG.

### lcc_schedule, lcc_singles, lcc_revenue, lcc_revenue_types, lcc_users

* Early scaffolds; keep simple keys and tighten types during normalization.

---

## Gaps & Normalization Plan

1. **Monetary & numeric fields as strings**

    * Convert `amount`, `sold_price`, `price`, `value`, `coupon_price`, `cost_per_item`, `total_cost` to `DECIMAL(10,2)`; strip `$` on import.
    * Convert counts/ids stored as `varchar` to proper types (`INT`/`BIGINT`).

2. **Time & dates as strings**

    * Convert `placed_at`, `date`, and order/shipping timestamps to `DATETIME` with timezone/UTC policy.

3. **Missing normalized entities**

    * Create **buyers** (handle, name/address snapshot, canonical contact), **listings** (title, sku, format), **orders** (includes quantity, price, coupon, fees), **shipments** (carrier, tracking, cost).
    * Map WhatNot → internal ids; maintain raw → canonical mapping tables.

4. **Foreign keys not declared**

    * Add FKs: `lcc_expenses.expense_type_id → lcc_expense_types.id`;
      `lcc_inventory.grading_comp_id → lcc_grading_comps.id` (change to `INT UNSIGNED`).

5. **Breaks linkage**

    * Introduce `breaks` + `break_slots` + `break_orders` (junction) to link `lcc_breaks_csv` and `orders` robustly.

6. **Integrity rules**

    * Unique **order** by WhatNot `order_id`; ensure `ledger` rows roll up to exactly one order; enforce 1—* relation between order and ledger lines.

---

## ERD (text‑first – target state)

```
uploaded_files (id)
    └── livestream_csv (uploaded_file_id FK)
    └── ledger_csv (uploaded_file_id FK)
    └── breaks_csv (uploaded_file_id FK)

buyers (id) ←— (buyer handle) — livestream_csv
listings (id) ←— (listing_id/sku) — {livestream_csv, ledger_csv}
orders (id, whatnot_order_id, listing_id, buyer_id, qty, amounts...)
    ├─< ledger_lines (id, order_id, transaction_type, amount)
    └─< shipments (id, order_id, carrier, tracking, cost)

breaks (id)
    ├─< break_slots (id, break_id, team/slot)
    └─< break_orders (break_id, order_id, slot_id)

inventory (id, product, cost...)  ──< expenses (id, expense_type_id, inventory_id?)
expense_types (id)
grading_comps (id)
```

---

## Suggested Migrations (safe sequence)

1. Add canonical tables: `buyers`, `listings`, `orders`, `order_items`, `shipments`, `ledger_lines`, `breaks`, `break_slots`, `break_orders`.
2. Add FKs in existing tables (`expenses`, `inventory`).
3. Create **import views** to parse currency → decimal & timestamps → datetime.

    * e.g., `vw_livestream_orders_clean` projecting parsed columns.
4. Backfill canonical tables from `*_csv` staging (idempotent scripts).
5. Enforce constraints (unique order id, non‑null foreign keys).
6. Add indexes for `order_id`, `listing_id`, `buyer`, `placed_at` on staging; for `buyer_id`, `listing_id`, `created_at` on canon tables.

---

## Yore Module Mapping (from data model)

* **lcc_orders**: buyers, listings, orders, order_items, shipments, ledger_lines; Orders/Buyers/Listings views + CSV export.
* **lcc_breaks**: breaks, break_slots, break_orders; attach orders to slot/team.
* **lcc_finance**: expenses, revenue, reconciliation to ledger_lines; P&L and payouts.
* **lcc_ship**: shipping workflow, pick/pack, tracking updates.

---

## Migrations — DDL draft (idempotent)

> Safe to run on MySQL 8+. Namespaced with `lcc_` prefix where appropriate. Use your Yore migrations to split into files if preferred.

### 001_create_canonical.sql

```sql
-- BUYERS
CREATE TABLE IF NOT EXISTS buyers (
  id              BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  handle          VARCHAR(255) NOT NULL,
  name            VARCHAR(255) NULL,
  email           VARCHAR(255) NULL,
  phone           VARCHAR(50)  NULL,
  country         VARCHAR(2)   NULL,
  state           VARCHAR(64)  NULL,
  city            VARCHAR(128) NULL,
  postal_code     VARCHAR(32)  NULL,
  address_line1   VARCHAR(255) NULL,
  address_line2   VARCHAR(255) NULL,
  created_at      DATETIME DEFAULT (NOW()),
  updated_at      DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_buyers_handle (handle)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- LISTINGS
CREATE TABLE IF NOT EXISTS listings (
  id              BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  external_listing_id VARCHAR(255) NULL,  -- WhatNot listing_id when available
  sku             VARCHAR(255) NULL,
  title           VARCHAR(500) NULL,
  description     TEXT NULL,
  format          VARCHAR(50) NULL,    -- auction/BIN/break-slot
  status          VARCHAR(50) NULL,    -- active/sold/cancelled
  created_at      DATETIME DEFAULT (NOW()),
  updated_at      DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_listings_ext (external_listing_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ORDERS
CREATE TABLE IF NOT EXISTS orders (
  id                  BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  whatnot_order_id    VARCHAR(255) NOT NULL,
  whatnot_order_num   BIGINT NULL, -- numeric variant when provided
  buyer_id            BIGINT UNSIGNED NOT NULL,
  listing_id          BIGINT UNSIGNED NULL,
  quantity            INT UNSIGNED NOT NULL DEFAULT 1,
  sold_price          DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  coupon_code         VARCHAR(255) NULL,
  coupon_amount       DECIMAL(10,2) NULL,
  cost_per_item       DECIMAL(10,2) NULL,
  total_cost          DECIMAL(10,2) NULL,
  placed_at           DATETIME(6) NULL,
  created_at          DATETIME DEFAULT (NOW()),
  updated_at          DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_orders_wn (whatnot_order_id),
  KEY idx_orders_buyer (buyer_id),
  KEY idx_orders_listing (listing_id),
  CONSTRAINT fk_orders_buyer   FOREIGN KEY (buyer_id)  REFERENCES buyers(id),
  CONSTRAINT fk_orders_listing FOREIGN KEY (listing_id) REFERENCES listings(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ORDER ITEMS (future-proof, even if most orders are 1 item)
CREATE TABLE IF NOT EXISTS order_items (
  id          BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  order_id    BIGINT UNSIGNED NOT NULL,
  sku         VARCHAR(255) NULL,
  title       VARCHAR(500) NULL,
  quantity    INT UNSIGNED NOT NULL DEFAULT 1,
  unit_price  DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  created_at  DATETIME DEFAULT (NOW()),
  updated_at  DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_items_order (order_id),
  CONSTRAINT fk_items_order FOREIGN KEY (order_id) REFERENCES orders(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SHIPMENTS
CREATE TABLE IF NOT EXISTS shipments (
  id            BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  order_id      BIGINT UNSIGNED NOT NULL,
  carrier       VARCHAR(50) NULL,
  tracking      VARCHAR(255) NULL,
  label_url     TEXT NULL,
  shipped_at    DATETIME NULL,
  shipping_cost DECIMAL(10,2) NULL,
  status        VARCHAR(50) NULL,
  created_at    DATETIME DEFAULT (NOW()),
  updated_at    DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_shipments_tracking (tracking),
  KEY idx_shipments_order (order_id),
  CONSTRAINT fk_ship_order FOREIGN KEY (order_id) REFERENCES orders(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- LEDGER LINES (normalized from lcc_ledger_csv)
CREATE TABLE IF NOT EXISTS ledger_lines (
  id               BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  order_id         BIGINT UNSIGNED NULL,     -- linked after import
  listing_id       BIGINT UNSIGNED NULL,
  whatnot_listing_id VARCHAR(255) NULL,
  whatnot_order_id   VARCHAR(255) NULL,
  occurred_at      DATETIME NULL,
  transaction_type VARCHAR(50) NOT NULL,     -- SALES / ADJUSTMENT / FEE ...
  status           VARCHAR(50) NULL,         -- processing/completed
  amount           DECIMAL(10,2) NOT NULL,
  message          VARCHAR(500) NULL,
  created_at       DATETIME DEFAULT (NOW()),
  updated_at       DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_ledger_order (order_id),
  KEY idx_ledger_type (transaction_type),
  KEY idx_ledger_time (occurred_at),
  CONSTRAINT fk_ledger_order FOREIGN KEY (order_id) REFERENCES orders(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- BREAKS
CREATE TABLE IF NOT EXISTS breaks (
  id           BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  title        VARCHAR(500) NULL,
  scheduled_at DATETIME NULL,
  host         VARCHAR(255) NULL,
  format       VARCHAR(100) NULL,
  created_at   DATETIME DEFAULT (NOW()),
  updated_at   DATETIME NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- BREAK SLOTS (team/random/weight)
CREATE TABLE IF NOT EXISTS break_slots (
  id        BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  break_id  BIGINT UNSIGNED NOT NULL,
  label     VARCHAR(255) NOT NULL,  -- e.g., "Arizona Cardinals" or "Slot #7"
  created_at DATETIME DEFAULT (NOW()),
  updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_slots_break (break_id),
  CONSTRAINT fk_slots_break FOREIGN KEY (break_id) REFERENCES breaks(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- BREAK ↔ ORDERS (who bought which slot)
CREATE TABLE IF NOT EXISTS break_orders (
  id        BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  break_id  BIGINT UNSIGNED NOT NULL,
  slot_id   BIGINT UNSIGNED NULL,
  order_id  BIGINT UNSIGNED NOT NULL,
  price     DECIMAL(10,2) NULL,
  created_at DATETIME DEFAULT (NOW()),
  updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_bo_break (break_id),
  KEY idx_bo_order (order_id),
  CONSTRAINT fk_bo_break FOREIGN KEY (break_id) REFERENCES breaks(id),
  CONSTRAINT fk_bo_slot  FOREIGN KEY (slot_id)  REFERENCES break_slots(id),
  CONSTRAINT fk_bo_order FOREIGN KEY (order_id) REFERENCES orders(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 002_alter_existing.sql

```sql
-- Tighten existing tables with real FKs and types
ALTER TABLE lcc_expenses
  ADD COLUMN IF NOT EXISTS expense_type_id INT UNSIGNED NOT NULL AFTER location_id,
  ADD CONSTRAINT IF NOT EXISTS fk_expense_type FOREIGN KEY (expense_type_id) REFERENCES lcc_expense_types(id);

-- Convert grading_comp_id to INT and add FK
ALTER TABLE lcc_inventory
  MODIFY COLUMN grading_comp_id INT UNSIGNED NULL,
  ADD CONSTRAINT IF NOT EXISTS fk_inventory_grader FOREIGN KEY (grading_comp_id) REFERENCES lcc_grading_comps(id);

-- Helpful indexes on staging tables
CREATE INDEX IF NOT EXISTS idx_ls_order ON lcc_livestream_csv (order_id);
CREATE INDEX IF NOT EXISTS idx_ls_buyer ON lcc_livestream_csv (buyer);
CREATE INDEX IF NOT EXISTS idx_ledger_ord ON lcc_ledger_csv (order_id);
CREATE INDEX IF NOT EXISTS idx_ledger_list ON lcc_ledger_csv (listing_id);
```

### 003_views_and_clean_parsers.sql

```sql
-- Currency → DECIMAL helper via VIEWs (no data loss in staging)
CREATE OR REPLACE VIEW vw_livestream_clean AS
SELECT
  id,
  uploaded_file_id,
  order_id,
  CAST(NULLIF(order_numeric_id, '') AS UNSIGNED) AS order_numeric_id,
  buyer,
  product_name,
  product_description,
  CAST(NULLIF(product_quantity,'') AS UNSIGNED) AS product_quantity,
  CAST(REPLACE(REPLACE(NULLIF(sold_price,''), '$',''), ',', '') AS DECIMAL(10,2)) AS sold_price,
  cancelled_or_failed,
  bundled_in_show,
  tracking,
  shipment_id,
  shipping_address,
  postal_code,
  CASE
    WHEN placed_at REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2}' THEN CAST(placed_at AS DATETIME(6))
    ELSE STR_TO_DATE(placed_at, '%b %e, %Y, %h:%i:%s %p')
  END AS placed_at,
  coupon_code,
  CAST(REPLACE(REPLACE(NULLIF(coupon_price,''), '$',''), ',', '') AS DECIMAL(10,2)) AS coupon_price,
  CAST(REPLACE(REPLACE(NULLIF(cost_per_item,''), '$',''), ',', '') AS DECIMAL(10,2)) AS cost_per_item,
  CAST(REPLACE(REPLACE(NULLIF(total_cost,''), '$',''), ',', '') AS DECIMAL(10,2)) AS total_cost,
  sku,
  created_at,
  updated_at,
  deleted_at
FROM lcc_livestream_csv;

CREATE OR REPLACE VIEW vw_ledger_clean AS
SELECT
  id,
  uploaded_file_id,
  CASE
    WHEN `date` REGEXP '^[0-9]{4}-' THEN CAST(`date` AS DATETIME)
    ELSE STR_TO_DATE(`date`, '%b %e, %Y, %h:%i:%s %p')
  END AS occurred_at,
  CAST(REPLACE(REPLACE(NULLIF(amount,''), '$',''), ',', '') AS DECIMAL(10,2)) AS amount,
  NULLIF(listing_id,'') AS whatnot_listing_id,
  NULLIF(order_id,'')   AS whatnot_order_id,
  message,
  NULLIF(status,'') AS status,
  NULLIF(transaction_type,'') AS transaction_type,
  created_at,
  updated_at,
  deleted_at
FROM lcc_ledger_csv;
```

---

## Importer Spec — mapping & idempotency

**Source → Canonical**

* From `vw_livestream_clean`:

    * `buyers.handle = buyer` (create if missing; update name/address snapshot when present).
    * `listings.external_listing_id = NULLIF(sku,'') OR NULLIF(product_name,'')` (fallback); `title = product_name`.
    * `orders.whatnot_order_id = order_id`; `whatnot_order_num = order_numeric_id`; `buyer_id = buyers.id`; `listing_id = listings.id` (nullable); quantities/prices from parsed columns; `placed_at` from view.
    * `shipments` upsert by `tracking` if present; link to `orders`.

* From `vw_ledger_clean`:

    * Insert `ledger_lines` per row with `transaction_type`, `status`, `amount`, `message`, `occurred_at`.
    * Post-insert linker: `ledger_lines.whatnot_order_id → orders.whatnot_order_id` to fill `ledger_lines.order_id`.

* From `lcc_breaks_csv` (raw):

    * Create/find `breaks` by `title` (and optional show time if inferable).
    * Ensure `break_slots` exist for each distinct `product` value.
    * Join to `orders` via `order_no ↔ orders.whatnot_order_num` OR `username/handle ↔ buyers.handle` when numeric id missing.
    * Upsert into `break_orders` with `price` parsed to DECIMAL.

**Idempotency keys**

* `orders`: unique on `whatnot_order_id`.
* `shipments`: unique on `tracking`.
* `ledger_lines`: unique compound hash of (whatnot_order_id, transaction_type, occurred_at, amount, message) if you prefer; otherwise allow duplicates and dedupe in reports.
* `break_orders`: unique on (break_id, order_id, slot_id).

**Batching**

* Process imports in chunks (e.g., 2–5k rows). Use a `lcc_import_runs` table to record file hash, row counts, and per-stage success/failure, then attach `uploaded_file_id`.

---

## Data Validation Checklist (per import)

1. **Row parse health**: % of rows with non-null `sold_price`, parsed `placed_at`, and buyer handle.
2. **Orphans**: ledger lines with no matched order after linking.
3. **Duplicates**: count duplicate `whatnot_order_id` in staging vs canonical.
4. **Break coverage**: % of break rows matched to an order and a slot.
5. **Finance sanity**: sum(ledger SALES) ≈ sum(orders.sold_price) for the period; list top discrepancies.

---

## How to run (Yore CLI outline)

* `php cli.php lcc:import livestream <csv|xlsx>` → load into `lcc_livestream_csv`, then upsert into canonical using the views.
* `php cli.php lcc:import ledger <csv|xlsx>` → load `lcc_ledger_csv` → `ledger_lines` + order linking.
* `php cli.php lcc:import breaks <csv|xlsx>` → load `lcc_breaks_csv` → `breaks/*` + `break_orders`.
* `php cli.php lcc:reconcile` → compute per-order net, fees, giveaways; outputs `/reports/pnl` tables.

---

## Next step

If this DDL looks good, I can generate **Yore migration files** (one per block) and stub **importer PHP classes** with the parsing rules from the views baked in.
