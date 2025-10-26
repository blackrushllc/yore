# Models (ORM)

This project uses a minimal Active Record-style base class at Modules\\Orm\\Core\\Model.

New public API (aliases and helpers):
- static store(array $attrs): static — alias of create().
- static firstOrNew(array $match, array $values = []): static — hydrate or create (not persisted).
- static firstOrCreate(array $match, array $values = []): static — hydrate or create+save.
- static updateOrCreate(array $match, array $values): static — update existing or create.
- static updateWhere(array $values, array $where): int — bulk update.
- static insertMany(array $rows): int — naive multi-insert loop.
- static upsert(array $rows, array $uniqueBy, array $updateCols): int — stub for now.
- static findBy(string $column, mixed $value): ?static — first by column.
- static whereIn(string $column, array $values): QueryBuilder — IN helper.
- static lastInsertId(): ?string — DB last insert id.
- updateAttributes(array $values): void — fill + save.
- refresh(): void — reload from DB by PK.

DataTables-ready index methods:
- static index(array $params, array $options = []): array
- static indexFromRequest(?array $options = null): array

Options (whitelists):
- columns: list of allowed columns
- searchable: list of columns used for LIKE searches
- orderable: list of sortable columns
- as: 'array'|'models' (default 'array')
- maxLength: cap for DataTables length (default 1000)

index() interprets either a simple search map (e.g., ['find' => 'smith'] or ['username' => 'erik']) or raw DataTables params (search, columns[x][search][value], order, start/length). Security: Only whitelisted columns are referenced; unknowns are ignored.

Routable methods on models:
- web_*: return HTML body that will be themed by Controller.
- api_*: return raw output (arrays/objects are JSON-encoded).

Signature: function web_action(Controller $controller, array $request, ...$args)
