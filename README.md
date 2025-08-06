# Inventory Manager API Backend
This repository contains the API-only backend for the **Inventory Manager** platform. It is built using the Laravel framework (v12).


## System Requirements
- PHP 8.2+
- Composer
- Node.js & npm
- A supported database (like MySQL, PostgreSQL, or SQLite)
- **For Production:**
    - **Supervisor:** To ensure the queue worker process remains active.
    - **Cron:** For running scheduled tasks.


## Key Features
- Modular Transaction Workflow (Purchase/Sale)
- FIFO Inventory Management
- Role & Permission Management (Spatie)
- OAuth2 Authentication (Laravel Passport)
- API-First Clean Architecture


## Developer Setup (Local)
```bash
- cp .env.example .env
- composer install

- php artisan key:generate

- php artisan migrate --seed
- php artisan db:seed --class=UsersSeeder # (Optional) Create default users for each role if needed
- php artisan db:seed --class=RolesAndPermissionsSeeder # (Optional) to sync the newly added roles and permission

- php artisan passport:keys --force  # Generates Passport keys
- php artisan passport:client --personal  # Generates a personal access client

- php artisan queue:listen
```

## Contribution Guidelines
To maintain code quality and consistency, please adhere to the following guidelines when contributing to the project.

### General Principles
- **Keep it DRY:** Avoid duplicating code. Utilize existing services, actions, and helpers where possible.
- **Thin Controllers:** Controllers should only be responsible for receiving requests and returning responses.
- **Use Request Classes:** All request validation and authorization logic must be handled within dedicated `Request` classes.
- **Business Logic:** Complex business logic should be encapsulated within `Service` or `Action` classes.
- **Permissions over Roles:** Whenever checking for authorization, prefer using specific permissions (`$user->can('do_something')`) instead of checking for roles directly (`$user->hasRole('role')`). This makes the system more flexible.

### Git Workflow & Commit Guidelines
Follow [Conventional Commits](https://www.conventionalcommits.org/en/v1.0.0/) for clear commit history.

#### Commit Types Examples:
| Type       | Example Commit Message                                   |
|------------|----------------------------------------------------------|
| feat       | feat(user): add user export API endpoint                  |
| fix        | fix(order): correct invalid status code on approval       |
| docs       | docs(contributing): add guidelines for new contributors   |
| style      | style: apply Pint fixes to inventory module               |
| refactor   | refactor(batch): optimize FIFO stock retrieval logic      |
| perf       | perf(sale): improve sale item lookup performance          |
| test       | test(item): add unit tests for stockOnHand calculation    |
| build      | build: update npm dependencies                           |
| ci         | ci(github): add CI workflow for PR validation            |
| chore      | chore: clean up unused services                          |
| revert     | revert: revert 'feat(user): add user export API endpoint' |

#### Branch Naming Conventions
```bash
git checkout -b feature/user-export-endpoint
git checkout -b bug/fix-status-code
git checkout -b enhancement/optimize-export-performance
```

### Coding Standards
- **Static Analysis:** Run PHPStan before pushing code:
  ```bash
  ./vendor/bin/phpstan analyse
  ```
- **Code Formatting:** Run Laravel Pint to fix styling:
  ```bash
  ./vendor/bin/pint
  ```
- **Naming Conventions:** Follow Laravel’s standard conventions.


### Database Migrations & Seeders
- Never modify merged migrations. Create a new migration for schema changes.
- Update relevant seeders if you add essential application data.

---

# Transaction Workflow

## Purchasing Flow
| Action Type        | Model               | Editable? | Voidable? | Notes                                              |
|--------------------|--------------------|-----------|-----------|----------------------------------------------------|
| Direct Purchase     | Purchase            | ❌ After payment | ✅ If unpaid | Immediate purchase (Invoice + GRN in one step)     |
| Quoted Purchase     | PurchaseOrder       | ✅ Until converted | ✅ Before conversion | Proposal to vendor, converts to Invoice & GRN      |
| Goods Received      | GoodsReceivedNote   | ❌ Immutable | ⚠️ If no invoice tied | Confirms actual goods received, triggers stock-in  |
| Vendor Billing      | PurchaseInvoice     | ❌ Immutable | ✅ If unpaid/no journal | Vendor's bill for accounting purposes              |

## Sales Flow
| Action Type         | Model               | Editable? | Voidable? | Notes                                              |
|---------------------|--------------------|-----------|-----------|----------------------------------------------------|
| Direct Sale          | Sale                | ❌ After payment | ✅ If unpaid | POS sale, Invoice + Delivery Note auto-created     |
| Sales Proposal       | SaleOrder           | ✅ Until converted | ✅ Before conversion | Customer proposal, converts to Invoice & Delivery  |
| Goods Out            | DeliveryNote        | ❌ Immutable | ⚠️ If no invoice tied | Goods handed over, triggers stock-out              |
| Customer Billing     | SaleInvoice         | ❌ Immutable | ✅ If unpaid/no journal | Customer's final bill                              |

---

# Transaction Flow Matrix

| Flow                          | Allowed? | When to Use                                              |
|-------------------------------|----------|----------------------------------------------------------|
| PO → GRN → Invoice             | Yes      | Default, strict procurement                             |
| PO → Invoice → GRN             | Yes      | Vendor invoices before delivery                          |
| Direct Invoice → No GRN        | Yes      | Services/non-stock items                                 |
| Direct Invoice → GRN           | Yes      | When user confirms delivery with warehouse               |
| Direct GRN → Invoice           | Yes      | Order received first, invoice to follow                  |
| POS Purchase → Invoice → GRN   | Yes      | For POS sales with automated flows                      |

Same principles apply for Sales as well.

# Manual Transaction Creation
- **No auto-created records.** All transactions (Invoices, GRNs, Delivery Notes) must be explicitly created by the user through the frontend interface.
- This may evolve based on user feedback for streamlining bulk operations.

---