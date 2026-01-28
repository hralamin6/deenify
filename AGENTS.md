# AGENTS.md

This repository is a Laravel 12 + Livewire 4 + Tailwind 4 app.
These notes are for agentic coding assistants working here.

## Source of Truth
- Follow the existing patterns in neighboring files.
- The Laravel Boost guidelines apply; key rules are summarized below.
- Do not add new base folders or change dependencies without approval.

## Build, Dev, Lint, Test

### Setup
- `composer run setup` (installs deps, copies `.env`, generates key, migrates, builds assets)

### Dev servers
- `composer run dev` (artisan serve + queue + pail + vite via concurrently)
- `npm run dev` (Vite only)

### Build
- `npm run build` (Vite build)

### Lint / Format
- `vendor/bin/pint --dirty` (required before finalizing changes)

### Tests (Pest)
- `php artisan test` (all tests)
- `php artisan test tests/Feature/ProfilePageTest.php` (single file)
- `php artisan test --filter=profile` (single test name)

Notes:
- The repo uses Pest v4 and PHPUnit v12.
- Use the smallest relevant test set first.
- Ask before running the full suite if not required by the change.

## Copilot / Cursor Rules (from `.github/copilot-instructions.md`)

### General
- Use the `search-docs` tool for Laravel ecosystem docs before coding.
- Follow existing conventions; check sibling files for structure and naming.
- Be concise in explanations.
- Only create documentation files when explicitly requested.

### PHP & Laravel
- Always use curly braces for control structures.
- Use explicit return types and parameter type hints.
- Prefer PHPDoc blocks over inline comments; add array shape types when helpful.
- Use constructor property promotion; no empty `__construct()`.
- Use Laravel conventions: Eloquent relationships, factories, Form Requests.
- Avoid `DB::` in favor of model queries (except where already used).
- Use `config()` over `env()` outside config files.
- Use `php artisan make:*` commands and pass `--no-interaction`.

### Laravel 12 structure
- No `app/Console/Kernel.php`; use `bootstrap/app.php` and `routes/console.php`.
- Middleware registration lives in `bootstrap/app.php`.

### Livewire
- Components require a single root element.
- Validate data and run authorization checks in actions.
- Prefer lifecycle hooks (`mount`, `updatedFoo`).

### Tailwind v4
- Use Tailwind v4 syntax; prefer `@import "tailwindcss"`.
- Avoid deprecated utilities; use replacements.
- For lists, use `gap-*` utilities instead of margins.

## Code Style & Conventions

### PHP formatting
- PSR-12 style; 4-space indents; braces on new lines.
- Let Pint enforce formatting; avoid manual alignment.

### Imports
- Keep `use` statements grouped logically; follow existing order in the file.
- Avoid reordering imports unless the file is already being refactored.

### Types & returns
- Always declare return types for methods and functions.
- Type-hint parameters and properties where possible.
- Prefer `casts()` method for model casts when consistent with nearby models.

### Naming
- Classes: `PascalCase`.
- Methods/variables: `camelCase`.
- Booleans: `is*`, `has*`, `can*` prefixes (e.g., `isOnline`).
- Routes: named routes are used; prefer `route()` helpers.

### Error handling
- Use `try/catch` for IO or network calls when failure is expected.
- Prefer Laravel exceptions, validation errors, and abort helpers.
- Avoid swallowing exceptions unless there is a clear fallback path.

### Eloquent & data
- Use relationships and eager loading to avoid N+1 queries.
- Prefer factories for test data and seeding.
- Keep `fillable` arrays explicit; do not use `guarded = []` by default.

### Tests
- Tests are Pest; place in `tests/Feature` or `tests/Unit`.
- Use `php artisan make:test --pest <Name>`.
- Prefer feature tests; use datasets for repeated validation cases.
- Use specific response assertions (`assertForbidden`, `assertNotFound`).

## Frontend Conventions

### Vite
- Entrypoints are `resources/css/app.css` and `resources/js/app.js`.
- If a Vite manifest error occurs, run `npm run build` or `npm run dev`.

### Tailwind / DaisyUI
- Tailwind v4 is active; use its utilities and avoid deprecated ones.
- Reuse existing UI patterns before introducing new ones.
- Use `gap-*` for spacing in lists and flex/grid layouts.

### Livewire routes
- Public routes use `Route::livewire` in `routes/web.php`.
- Auth routes are grouped under `auth` middleware.

## Repository Pointers
- Models in `app/Models`.
- Livewire components likely under `app/Livewire` or similar (check before adding).
- Helpers are in `app/helpers.php` and are autoloaded.

## When in Doubt
- Search for similar patterns in the repo.
- Follow the Laravel Boost rules and prefer framework conventions.
- Run the minimal test set and Pint before finalizing.
