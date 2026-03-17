# Backend Agent Guide

This repository is the Laravel API backend for Atom Suit.

For broader backend context, also review [README.md](/home/bibin/websites/atomsuit/backend/README.md).

## Stack

- Laravel 12
- Laravel Passport
- Spatie Permission
- Stancl Tenancy
- Cashier/Stripe

## Required Backend Flow

Follow the established backend architecture:

`controller -> request -> service/action -> repository -> resource`

Do not collapse new features into controller-only code when the surrounding module already uses these layers.

## Multi-Tenant Rules

- This is a subdomain-based multi-tenant backend with separate tenant databases.
- Central and tenant contexts both exist in the same app.
- Tenant-aware behavior is handled through tenancy middleware and context-aware auth/model usage.
- Never assume central records and tenant records are interchangeable.
- Before adding a migration, decide whether it belongs in:
  - `database/migrations`
  - `database/migrations/tenant`

## File Placement

For most CRUD or domain features, review whether you need:

- model in `app/Models`
- request in `app/Http/Requests`
- controller in `app/Http/Controllers`
- service in `app/Services`
- action in `app/Actions` for focused operations
- repository in `app/Repositories`
- resource in `app/Http/Resources`
- route changes in `routes/api.php` or `routes/web.php`
- enum/seeder updates for permissions or defaults
- tests in `tests`

## Working Conventions

- Keep controllers thin.
- Put validation in request classes.
- Keep business logic in services/actions.
- Reuse repository patterns, especially the shared CRUD behavior already used across modules.
- Use `ApiResponse` patterns for consistent API responses.
- Prefer permission checks over direct role checks.
- Match existing naming and module structure before introducing a new abstraction.

## Validation

Run relevant checks before finishing backend work:

- `composer test`
- `./vendor/bin/phpstan analyse`
- `./vendor/bin/pint`
