# create-system — DDL-first WinterCMS plugin scaffolding

`create-system` introspects a live PostgreSQL schema and generates a complete WinterCMS plugin skeleton: models, migrations, backend controllers, list/form field definitions, and language files. The schema is the single source of truth — annotate your DDL with YAML comments to control every aspect of the generated output.

## What it does

1. Connects to a PostgreSQL database and reads the schema (tables, columns, foreign keys, constraints, triggers).
2. Classifies each table by structural pattern: `ContentTable`, `PivotTable`, `SemiPivotTable`, `CentralTable`, `ReportTable`.
3. Infers ORM relation types from FK structure (BelongsTo, HasMany, BelongsToMany, etc.).
4. Generates a full WinterCMS plugin directory under `plugins/<author>/<name>/`.
5. Optionally commits and pushes the generated plugin via git.

## Quick start

```bash
# From your WinterCMS root, with the schema already migrated:
/path/to/acorn-create-system myplugin

# Generate all plugins detected in the schema:
/path/to/acorn-create-system all

# Generate and push to git:
/path/to/acorn-create-system myplugin push
```

## Installation

```bash
git clone https://github.com/anewholm/create-system /var/www/scripts/acorn-create-system-dir
composer install -d /var/www/scripts/acorn-create-system-dir
```

Or clone the [scripts](https://github.com/anewholm/scripts) repo which includes this tool alongside the broader WinterCMS setup scripts.

## Prerequisites

- PHP 8.1+
- PostgreSQL 12+ (must be running and accessible)
- Composer
- WinterCMS installation (the generated output targets WinterCMS 1.2+)

## YAML comment vocabulary

Table, column, and FK comments in PostgreSQL can contain YAML fragments to control generation. See [PATTERNS.md](PATTERNS.md) for the full catalogue of patterns and their YAML keys.

### Common examples

```sql
-- table comment
COMMENT ON TABLE acorn_calendar_events IS 'table-type: content
label: Event
label_plural: Events';

-- column comment  
COMMENT ON COLUMN acorn_calendar_events.name IS 'field-type: text
tab: details';

-- FK comment
COMMENT ON CONSTRAINT event_location_id_fkey ON acorn_calendar_events IS 'type: Xto1
read-only: true';
```

## Output structure

For a table `acorn_myapp_widgets` the generator produces:

```
plugins/acorn/myapp/
  models/
    Widget.php
    widget/
      columns.yaml
      fields.yaml
  controllers/
    Widgets.php
    widgets/
      list.htm
      update.htm
      create.htm
  lang/
    en/
      lang.php
  updates/
    create_acorn_myapp_widgets_table.php
    version.yaml
  Plugin.php
```

## IS Pattern catalogue

[PATTERNS.md](PATTERNS.md) documents every pattern the tool recognises — table classification, relation type inference, field rendering, OLAP support, and YAML override keys. This is the design reference for extending or modifying the generator.

## Compatibility

| PHP  | PostgreSQL | WinterCMS output target |
|------|------------|------------------------|
| 8.1+ | 12+        | 1.2+ (Laravel 9/10/11) |

## Related

- [anewholm/scripts](https://github.com/anewholm/scripts) — setup scripts that install WinterCMS environments for generated plugins
- [anewholm/acorn](https://github.com/anewholm/acorn) — base module that generated plugins extend

## License

MIT
