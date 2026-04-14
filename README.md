# create-system — DDL-first WinterCMS plugin scaffolding

> CI development in **progress**. Includes a full test system installation and create-system run on a real DB. Only on Ubuntu server.

![Human made content](human-made-content.png "Human made content")
[![CI](https://github.com/anewholm/create-system/actions/workflows/ci.yml/badge.svg)](https://github.com/anewholm/create-system/actions/workflows/ci.yml)
[![Security Scan](https://github.com/anewholm/create-system/actions/workflows/semgrep.yml/badge.svg)](https://github.com/anewholm/create-system/actions/workflows/semgrep.yml)

Information management systems have administration interfaces. University admin, legal system admin, and so on. The normal database administration UI, like PGAdmin or PocketBase, is almost all the UI you need for the project after you have built the DB, but not quite friendly enough. So why should I then have to spend a month repeating all the MVC from a ERD / DDL that already describes everything that is needed... with a few YAML comments to point the way?

`create-system` introspects a live PostgreSQL schema and generates a complete, functional and ready, WinterCMS plugin: models, migrations, backend controllers, list/form field definitions, language files, etc. It makes use of the in-built `artisan create:model|controller` commands and then adds a _lot_ of flavor. The schema is the single source of truth — with optional YAML comments — to control every aspect of the generated output.

## What it does

1. Connects to a PostgreSQL database and reads the schema (tables, columns, foreign keys, constraints, triggers).
2. Classifies each table by structural pattern: `ContentTable`, `PivotTable`, `SemiPivotTable`, `CentralTable`, `ReportTable`.
3. [Infers ORM relation](PATTERNS.md) types from FK structure (BelongsTo, HasMany, BelongsToMany, etc.).
4. Generates a full WinterCMS plugin directory under `plugins/<author>/<name>/`.
5. Optionally commits and pushes the generated plugin via git.
6. Optionally generates functional [Pentaho Mondrian](https://mondrian.pentaho.com/documentation/olap.php) OLAP reporting cubes from DB views.

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

## Prerequisites

- [PHP](https://www.php.net/downloads.php) v8.1+
- [PostgreSQL](https://www.postgresql.org/download/) v16+ (must be running and accessible)
- [Composer](https://getcomposer.org/download/) v2
- [WinterCMS](https://wintercms.com/install) installation (the generated output targets WinterCMS v1.2+)

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

| OS (LTS)   | PHP  | PostgreSQL | WinterCMS               |
|------------|------|------------|-------------------------|
| Ubuntu 22+ | 8.1+ | 15+        | 1.2+ (Laravel 9/10/11)  |

## Related

- [anewholm/scripts](https://github.com/anewholm/scripts) — setup scripts that install WinterCMS environments for generated plugins
- [anewholm/acorn](https://github.com/anewholm/acorn) — base module that generated plugins extend

## License

MIT
