# LCC Calendar Data Endpoints

Two Modeltroller API endpoints power the Schedule and Inventory calendars. Both support date range querying and optional filters.

Base route pattern: /{section}/models/{Model}/{action}

- Schedule endpoint: /default/models/schedule/fetch
- Inventory endpoint: /default/models/inventory/fetch

Request (GET)
- from: YYYY-MM-DD (start of visible range)
- to:   YYYY-MM-DD (end of visible range)
- categories[]: Optional list of categories to include.
  - Schedule: Shows, Sales (unknown: CheckIn, CheckOut → currently empty)
  - Inventory: Inventory, Orders, Purchases (Purchases currently a placeholder)
- breakers[]: Optional list of breaker handles (e.g., alice, bob). If omitted, empty, or all selected → no filter.

Response (application/json)
{
  "ok": true,
  "events": [
    {
      "id": "string",
      "title": "string",
      "start": "2025-10-18T10:00:00Z",
      "end": "2025-10-18T18:00:00Z | null",
      "category": "Shows|Sales|Inventory|Orders|Purchases",
      "breaker": "alice|bob|null",
      "subtype": "optional string",
      "url": "/optional/link",
      "color": "#optional",
      "meta": {"placeholder": true} // optional
    }
  ]
}

Server-side behavior
- Date bounds are enforced inclusively against the relevant timestamp column.
- A max span of 1 year is enforced (returns ok=false otherwise).
- Event cap (5000) is enforced (ok=false if exceeded).
- breakers[] is sanitized to a-z 0-9 _ - . and compared case-insensitively to the host/handle when available.
- Unknown/undefined categories (e.g., CheckIn/CheckOut for Schedule) currently return no events.

Data sources (canonical tables)
- Schedule::Shows → lcc_breaks (scheduled_at, host, title)
- Schedule::Sales → lcc_orders (placed_at), optionally joined to lcc_break_orders → lcc_breaks to attribute a breaker. Returned as daily aggregates per breaker when possible.
- Inventory::Inventory → lcc_inventory (created_at used as received/created timestamp)
- Inventory::Orders → lcc_orders (placed_at), optionally attributed to breaker via lcc_break_orders → lcc_breaks (daily aggregates)
- Inventory::Purchases → placeholder event at the midpoint of the requested range (meta.placeholder = true)

Frontend wiring (both pages)
- On calendar init and whenever the visible month changes or filters toggle, compute:
  - from = first visible day (YYYY-MM-DD)
  - to   = last visible day (YYYY-MM-DD)
- Collect selected top-level categories (excluding the Breakers category itself) and selected breaker subcheckboxes.
  - If no breaker is selected or all breakers are selected → omit breakers[] entirely.
- Fetch with: fetch(`/default/models/{schedule|inventory}/fetch?from=...&to=...&categories[]=...&breakers[]=...`)
- Debounce rapid toggles (approx 250ms) to avoid unnecessary network traffic.
- Show a small loading overlay during fetch and a non-blocking toast on error.

Notes
- If table or column names differ in your environment, update the Modeltroller to match. This implementation assumes canonical table names with the lcc_ prefix: lcc_breaks, lcc_orders, lcc_break_orders, lcc_inventory.
- Times are emitted in ISO 8601 with a trailing Z (UTC). If your DB stores local times, the conversion is naive; adjust as needed if timezone fidelity is required.
