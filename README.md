# PharmacyManager API Backend

This repository contains the API-only backend for the **PharmacyManager** platform. It is built using the Laravel framework (v12).

## System Requirements

- PHP 8.2+
- Composer
- Node.js & npm
- A supported database (like MySQL, PostgreSQL, or SQLite)
- **For Production:**
    - **Supervisor:** To ensure the queue worker process remains active.
    - **Cron:** For running scheduled tasks.

## Key Features


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
- **Keep it DRY:** Don't repeat yourself. Utilize existing services, actions, and helpers where possible.
- **Thin Controllers:** Controllers should only be responsible for receiving requests and returning responses.
- **Use Request Classes:** All request validation and authorization logic must be handled within dedicated `Request` classes.
- **Business Logic:** Complex business logic should be encapsulated within `Service` or `Action` classes.
- **Permissions over Roles:** Whenever checking for authorization, prefer using specific permissions (`$user->can('do_something')`) instead of checking for roles directly (`$user->hasRole('admin')`). This makes the system more flexible.

### Git Workflow
1.  **Create a Feature/Bug Branch:** All new work should be done on a feature/bug branch as required.
    ```bash
    # Example:
    git checkout -b feature/user-export-endpoint
    git checkout -b bug/invalid-status-code
    ```
2.  **Write Clear Commit Messages:** Write a concise, imperative-style subject line (e.g., "Add user export functionality"). Add more details in the body if necessary.
3.  **Submit a Merge Request:** Once your feature is complete and tested, push your branch and create a Merge Request against the `main` or `staging` branch.

### Coding Standards
- **Laravel Pint:** The project uses Laravel Pint to enforce PSR-12 coding standards. Run it before committing to ensure your code is formatted correctly.
  ```bash
  ./vendor/bin/pint
  ```
- **Naming Conventions:** Follow Laravel's standard naming conventions for models, controllers, migrations, etc.

### Database
- **Migrations:** Never modify a migration that has already been merged into a shared branch. If you need to alter a table, create a new migration file.
- **Seeders:** If you add data required for the application to function correctly, update the relevant database seeders.





TODO: 
move the dashboard repo methods to their corect service dashboardService or model repo
if any service class is only calling the action class, then move the logic to action class and keep either serivce or action. unlesss complex acion exists
change Exceptions to HttpException


## transaction workflow

Action Type	Proposed Model	Role / Use Case	Editable?	Voidable?
Direct Purchase	Purchase	Immediate purchase → GRN + Invoice generated in one go (e.g. pharmacy buys stock from vendor at counter)	❌ After payment	✅ If unpaid
Quoted Purchase	PurchaseOrder	Proposal to vendor. Can be approved → GRN + Invoice generated	✅ Until converted	✅ Before conversion
Received Goods	GoodsReceivedNote	Confirms goods actually received. Can differ from order. Triggers stock-in.	❌ Immutable	⚠️ Only if no invoice tied
Vendor Billing	PurchaseInvoice	The vendor's bill. Needed for accounting. Final step in the purchase pipeline.	❌ Immutable	✅ If unpaid and no journal posted

Action Type	Proposed Model	Role / Use Case	Editable?	Voidable?
Direct Sale	Sale	POS sale. Invoice + Delivery Note auto-created.	❌ After payment	✅ If unpaid
Sales Proposal	SaleOrder	Customer proposal/quote. Can be approved and converted into invoice + delivery note.	✅ Until converted	✅ Before conversion
Goods Out	DeliveryNote	Proof that the goods were physically handed over. Triggers stock-out.	❌ Immutable	⚠️ Only if no invoice tied
Customer Billing	SaleInvoice	Final bill for the customer. May be auto-created for direct sales or from order.	❌ Immutable	✅ If unpaid and no journal posted




🛒 Purchasing Flow
## PurchaseOrder

🧠 Use case: Draft/proposal sent to vendor. Not financially binding.

✍️ Editable: ✅ Yes

🔁 Convert To: PurchaseInvoice + GoodsReceivedNote

🧯 Can void: ✅ Yes (before converted)

🔒 Locked After: Converted to invoice or GRN

## Purchase (Direct Purchase)

🧠 Use case: Immediate invoice + GRN (e.g., buying stock for a pharmacy).

✍️ Editable: ⚠️ Only before payment or stock consumption

🔁 Creates: PurchaseInvoice, GoodsReceivedNote

🧯 Can void: ✅ Only if stock not yet consumed

🔒 Locked After: Payment done or stock used

## PurchaseInvoice

🧠 Use case: Financial document used in accounting

✍️ Editable: ❌ No

🧯 Can void: ✅ Only before payment/approval

🔒 Locked After: Payment or accounting approval

## GoodsReceivedNote

🧠 Use case: Acknowledges stock received; triggers inventory update

✍️ Editable: ❌ No

🧯 Can void: ⚠️ Yes, by reversing stock if not consumed

🔒 Locked After: Stock used in sales/consumption

💸 Sales Flow
## SaleOrder

🧠 Use case: Quote sent to customer, not binding

✍️ Editable: ✅ Yes

🔁 Convert To: SaleInvoice + DeliveryNote

🧯 Can void: ✅ Yes

🔒 Locked After: Converted

## Sale (Direct Sale)

🧠 Use case: Walk-in sale (pharmacy, POS)

✍️ Editable: ⚠️ Only before payment or delivery

🔁 Creates: SaleInvoice, DeliveryNote

🧯 Can void: ✅ Only if stock not delivered/used

🔒 Locked After: Payment or delivery confirmed

## SaleInvoice

🧠 Use case: Bill sent to customer, financial doc

✍️ Editable: ❌ No

🧯 Can void: ✅ With reversal of delivery/stock movement

🔒 Locked After: Paid / accounted

## DeliveryNote

🧠 Use case: Goods given to customer, updates inventory

✍️ Editable: ❌ No

🧯 Can void: ✅ Only if goods not consumed

🔒 Locked After: Goods confirmed delivered