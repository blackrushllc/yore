# LCC — SECTION 3: DATA IMPORTS

This document explains the importer system that ingests WhatNot exports (CSV/Excel) into staging tables and transforms them into the canonical Luxe Card Club (LCC) schema. It follows the LCC Design Plan and the Transfer Prompt.

Contents
- Data sources and supported files
- Database objects (staging, views, canonical links)
- Idempotency (file + row)
- CLI commands and usage
- Mapping tables (source → canonical)
- Logging, rejects, and dry-run
- Smoke tests

1) Data sources (Phase 1)
- Livestream Orders feed (per-order rows)
- Ledger/Financial events (fees, adjustments, giveaways)
- Breaks (team/slot rows)
- Weekly orders (optional)

2) Database objects
2.1 Staging tables (raw)
- lcc_uploaded_files: id, sha256, original_name, bytes, rows, imported_at, notes
- lcc_livestream_csv: raw order rows (kept as text). Unique(order_id, placed_at, buyer)
- lcc_ledger_csv: raw ledger rows. Unique(listing_id, order_id, status, transaction_type, date)
- lcc_breaks_csv: raw breaks rows. Unique(order_no, username)
- lcc_weekly_orders_csv: optional
- lcc_import_runs: per-execution metrics and flags
- lcc_import_rejects: per-row rejects with reason and snippet

2.2 Clean views (centralized parsing)
- vw_livestream_clean: currency → DECIMAL(10,2); timestamps → DATETIME; numeric ids → INT; blanks → NULL
- vw_ledger_clean: amount → DECIMAL; date → DATETIME; normalize enums
- vw_breaks_clean: price → DECIMAL

2.3 Canonical tables (targets)
Assumed present per Design Plan (prefixed with lcc_): buyers, listings, orders, order_items (optional), shipments, ledger_lines, breaks, break_slots, break_orders. Helpful unique constraints:
- lcc_orders.whatnot_order_id UNIQUE
- lcc_shipments.tracking UNIQUE (nullable)
- lcc_break_orders (break_id, order_id, slot_id) UNIQUE
- lcc_buyers.handle UNIQUE

3) Idempotency
3.1 File-level
- SHA-256 of uploaded file is recorded in lcc_uploaded_files.sha256. If re-run without --force, importer aborts early.

3.2 Row-level
- Livestream: UNIQUE(order_id, placed_at, buyer) on lcc_livestream_csv
- Ledger: UNIQUE(listing_id, order_id, status, transaction_type, date) on lcc_ledger_csv
- Breaks: UNIQUE(order_no, username) on lcc_breaks_csv

4) Transform & upsert mapping
Canonical writes read from views only.

Livestream → buyers, listings, orders, shipments
- buyers.handle = buyer
- listings.external_listing_id = listing_id; sku; title = product_name
- orders.whatnot_order_id = order_id; whatnot_order_num; buyer_id (by handle); listing_id (by external_listing_id); quantity; sold_price; coupon_code; coupon_amount; cost_per_item; total_cost; placed_at
- shipments upsert by tracking, linked to orders

Ledger → ledger_lines (then linked to orders)
- Insert lcc_ledger_lines(transaction_type, status, amount, message, occurred_at, whatnot_order_id, whatnot_listing_id)
- Reconcile links order_id by joining whatnot_order_id to lcc_orders.whatnot_order_id

Breaks → breaks, break_slots, break_orders
- breaks by title
- break_slots by (break_id, slot_label = product)
- break_orders by (break_id, order_id by whatnot_order_num, slot_id) with price

5) CLI Commands
Use yore/web/cli.php, which now supports LCC commands:

- php cli.php lcc:import livestream <path> [--dry-run] [--force] [--chunk=3000]
- php cli.php lcc:import ledger     <path> [--dry-run] [--force] [--chunk=3000]
- php cli.php lcc:import breaks     <path> [--dry-run] [--force] [--chunk=3000]
- php cli.php lcc:import weekly     <path> [--dry-run] [--force] [--chunk=3000]
- php cli.php lcc:reconcile                  [--from=YYYY-MM-DD] [--to=YYYY-MM-DD]

Behavior:
- File registry SHA-256: file-level idempotency.
- Staging load in chunks (2k–5k) wrapped in transactions.
- Canonical upserts via INSERT ... ON DUPLICATE KEY UPDATE.
- --dry-run: validates and reports counts but performs no writes.
- Summary output printed as JSON with ok, skip, err, total.

6) Mapping headers
Importer normalizes headers to snake_case. Supported header aliases (examples):
- order_id: [order_id, order, orderid]
- order_numeric_id: [order_numeric_id, order_number]
- buyer: [buyer, username]
- product_name: [product_name, title]
- product_quantity: [product_quantity, quantity]
- sold_price: [sold_price, price]
- shipping_address: [shipping_address, address]
- postal_code: [postal_code, zip]
- placed_at: [placed_at, created_at]
- coupon_amount: [coupon_amount, coupon_price]
- breaks.order_no: [order_no, order_number]

7) Logging & rejects
- lcc_import_runs tracks ok_count, skip_count, err_count, started_at, finished_at.
- lcc_import_rejects stores import_run_id, row_num, reason (e.g., missing_key, insert_failed), and row snippet.

8) Smoke tests (suggested)
- Import same file twice → 2nd run reports duplicate and does not reload unless --force.
- Broken currency or missing key → row logged in lcc_import_rejects.
- After imports, run `php cli.php lcc:reconcile` and confirm ledger_lines link to orders (updated order_id).

9) SQL files
- modules/LCC/import/database/sql/001_staging.sql
- modules/LCC/import/database/sql/002_views.sql
- modules/LCC/import/database/sql/003_canonical_links.sql

Notes
- Currency parsing strips $ and commas before casting to DECIMAL(10,2).
- Timestamps parsed with CAST(... AS DATETIME); adjust if source timestamps differ (document headers if needed).
- Imports assume MySQL for ON DUPLICATE KEY and CREATE OR REPLACE VIEW. SQLite tests should focus on staging loads.
