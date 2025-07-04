# Pharmacy Manager backend api
This is an **API-only** backend for **Pharmacy Manager**.


## Requirements
- PHP 8.1+
- MySQL
- Composer
- **Supervisor** (for managing queue workers in production)


yml commands to run
php artisan optimize:clear
composer install --no-interaction --prefer-dist --optimize-autoloader
php artisan migrate --force
php artisan optimize
php artisan queue:restart


## Developer Setup (Local)
```bash
- cp .env.example .env
- composer install

- php artisan key:generate

- php artisan migrate --seed
- php artisan db:seed --class=UsersSeeder # (Optional) Create default users for each role if needed

- php artisan passport:keys --force  # Generates Passport keys
- php artisan passport:client --personal  # Generates a personal access client
- php artisan vendor:publish --tag=laravel-mail # To publish mail package

- php artisan queue:listen
```


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