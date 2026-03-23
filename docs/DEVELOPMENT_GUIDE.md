# Atom Suit Backend - Developer & Contribution Guide

## System Requirements
- PHP 8.2+
- Composer
- Node.js & npm
- A supported database (like MySQL, PostgreSQL, or SQLite)
- **For Production:**
    - **Supervisor:** To ensure the queue worker process remains active.
    - **Cron:** For running scheduled tasks.

## Key Developer Features
- Modular Transaction Workflow (Purchase/Sale)
- FIFO Inventory Management
- Role & Permission Management (Spatie)
- OAuth2 Authentication (Laravel Passport)
- API-First Clean Architecture

## Installation

```bash
# Clone and setup environment
cp .env.example .env
composer install

# Generate Keys
php artisan key:generate
php artisan passport:keys --force
php artisan passport:client --personal

# Configure Stripe
# IMPORTANT: Before setting up Stripe locally or on a server, ensure the stripe webhook is configured correctly in your .env.

# Run Migrations & Seeders
php artisan migrate --seed
php artisan db:seed --class=UsersSeeder # (Optional) Create default test users
php artisan db:seed --class=RolesAndPermissionsSeeder # (Optional) Sync core permissions

# Start queue workers for asynchronous tasks
php artisan queue:listen
```

---

## Contribution Guidelines

To maintain code quality and consistency across the ERP ecosystem, please adhere to the following strict guidelines when contributing.

### General Architectural Principles
- **Keep it DRY:** Avoid duplicating code. Utilize existing Actions, Services, and Enum helpers where possible.
- **Thin Controllers:** Controllers must exclusively handle routing, receiving requests, and returning API resources. Math and Data transformations belong elsewhere.
- **Form Requests:** All validation and authorization logic must be strictly handled within dedicated `FormRequest` classes.
- **Business Logic:** Complex business operations (like document financial generation or ledger posting) must be encapsulated within `Action` classes (e.g., `CreatePurchaseInvoice.php`).
- **Permissions over Roles:** When checking for authorization, use granular permissions (`$user->can('create_purchase_invoice')`) instead of role-checking (`$user->hasRole('admin')`).

### Git Workflow & Commit Guidelines
Follow [Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/) for a clear commit history.

#### Commit Types Matrix
| **Type**    | **Usage**                                          | **Example Commit Message**                                  |
|-------------|----------------------------------------------------|-------------------------------------------------------------|
| **feat**    | A new feature                                      | `feat(user): add user export API endpoint`                  |
| **fix**     | A bug fix                                          | `fix(order): correct invalid status code on approval`       |
| **docs**    | Documentation only changes                         | `docs(contributing): add guidelines for new contributors`   |
| **style**   | Code style changes (formatting, spacing, etc.)     | `style: apply Pint fixes to inventory module`               |
| **refactor**| Code refactoring (no bug fix or new feature)       | `refactor(batch): optimize FIFO stock retrieval logic`      |
| **perf**    | Performance improvements                           | `perf(sale): improve sale item lookup performance`          |
| **test**    | Adding or fixing tests                             | `test(item): add unit tests for stockOnHand calculation`    |
| **build**   | Build system or dependency changes                 | `build: update npm dependencies`                            |
| **ci**      | CI/CD pipeline or automation related changes       | `ci(github): add CI workflow for PR validation`             |
| **chore**   | Routine tasks, maintenance (non-code affecting)    | `chore: clean up unused services`                           |

#### Branch Naming Conventions
```bash
git checkout -b feature/vendor-payment-gateway
git checkout -b bug/fix-tax-rounding-issue
git checkout -b refactor/extract-stock-movement-action
```

### Coding Standards
- **Static Analysis:** Run PHPStan before pushing code. Zero errors are required.
  ```bash
  ./vendor/bin/phpstan analyse
  ```
- **Code Formatting:** Run Laravel Pint to enforce standard styling conventions.
  ```bash
  ./vendor/bin/pint
  ```

### Database Migrations & Seeders
- **Immutability:** Never modify legacy, pushed migrations. Always draft a new migration for schema permutations.
- Update relevant seeders if you alter core structural data requirements.
