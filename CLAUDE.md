# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What This Is

A ProcessWire module that manages migration files for database-affecting changes (creating fields, templates, modules, access rules) across environments. Migration files use the ProcessWire API — these are **not** schema migrations like Laravel/Rails. They recreate what would normally be done via the PW admin UI.

Two PW modules ship together:
- **Migrations** (`Migrations.module`) — core module that tracks migration state in a `migrations` DB table and runs `update()`/`downgrade()` on migration classes
- **ProcessMigrations** (`ProcessMigrations.module`) — admin UI under Setup > Migrations

## Commands

### CLI (Symfony Console)
```bash
bin/migrate run [what] [-l|--latest]   # Run migrations
bin/migrate rollback [what]            # Rollback migrations
bin/migrate create                     # Create default migration
bin/migrate create:field               # Create field migration
bin/migrate create:template            # Create template migration
bin/migrate create:module              # Create module migration
bin/migrate create:access              # Create access migration
bin/migrate create:custom              # Create from custom template
bin/migrate show                       # Show migration status
bin/migrate install                    # Install the Migrations module
```

The CLI entry point (`bin/migrate`) bootstraps ProcessWire via `index.php` four levels up, then delegates to `ProcessWire\Migrations\CLI::run()`.

### Tests
```bash
# Uses Kahlan test framework with conveyor.yml for CI setup
# Tests require a running ProcessWire instance
vendor/bin/kahlan
```

Test specs are in `spec/` and use ProcessWire's wire API directly. The test setup (`conveyor.yml`) copies migration files into `site/migrations/` of a PW install.

## Architecture

### Namespace split
- `ProcessWire\` namespace — PW module files and helper classes in `classes/` (loaded via `include_once`, not autoloaded)
- `ProcessWire\Migrations\` namespace — CLI layer in `src/` (PSR-4 autoloaded via Composer)

### Migration class hierarchy (`classes/`)
- `Migration` — abstract base with `update()` and `downgrade()` methods, plus helpers (`insertIntoTemplate`, `removeFromTemplate`, `editInTemplateContext`, `eachPageUncache`)
- `FieldMigration`, `TemplateMigration`, `ModuleMigration`, `AccessMigration` — typed subclasses

### Migration file conventions
- Stored in `site/migrations/` (PW site directory)
- Filename format: `YYYY-MM-DD_HH-mm-ss.php` (timestamp-based)
- Class name derived from filename: `Migration_YYYY_MM_DD_HH_mm_ss` (via `Migrationfile::filenameToClassname`)
- Each file has a static `$description` property and extends a Migration subclass
- Templates for new files live in `templates/*.php.inc`; custom templates can override from `site/migrations/templates/`

### CLI commands (`src/Command/`)
Commands use two traits:
- `MigrationsModule` — provides `setMigrations()` to inject the Migrations module instance
- `MigrationsTable` — renders migration status tables with `CliStyles`

### Migration state tracking
The `migrations` DB table stores classnames of migrated files. `Migrationfile::$migrated` is set by comparing filesystem files against this table. Both namespaced (`\ProcessWire\ClassName`) and non-namespaced class resolution is supported when running migrations.
