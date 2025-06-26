🔁 OVERVIEW
Your system has two types of flows:

Direct Flow — for walk-in transactions (e.g. pharmacy sales)

Planned Flow — for structured B2B/B2G procurement or wholesale

🛒 PURCHASE FLOW
1. Purchase Order (PO)
Model: PurchaseOrder

Purpose: Proposal to procure items.

Editable: ✅ Until converted

Voidable: ✅ If not yet converted

Fields:
vendor_id

order_date

items [item_id, qty, unit_cost]

status: Draft / Approved / Converted / Voided

2. Convert PO to Invoice + GRN
Action: "Convert to Purchase"

Effect: Creates PurchaseInvoice and GoodsReceivedNote

PO status: Converted

Editable: ❌

Stock: Not yet updated until GRN

3. Direct Purchase
Model: Purchase

Purpose: Invoice + GRN in one go (for direct vendor billing)

Editable: ✅ until paid

Voidable: ✅ if unpaid or untouched stock

Fields:
vendor_id

invoice_number

items [item_id, qty, unit_cost]

payment_status: Pending / Paid

4. Goods Received Note (GRN)
Model: GoodsReceivedNote

Purpose: Tracks stock received per invoice

Editable: ❌

Voidable: ❌

Fields:
linked to Purchase / PurchaseInvoice

transaction_date

items [batch_id, expiry_date, quantity]

💰 SALES FLOW
1. Sales Order (SO)
Model: SaleOrder

Purpose: Customer proposal

Editable: ✅ Until converted

Voidable: ✅ If not yet converted

Fields:
customer_id

order_date

items [item_id, qty, price]

2. Convert SO to Invoice + Delivery
Action: "Convert to Sale"

Effect: Creates SaleInvoice and DeliveryNote

SO status: Converted

Editable: ❌

3. Direct Sale
Model: Sale

Purpose: Invoice + Delivery in one go (e.g. pharmacy walk-in)

Editable: ✅ until paid

Voidable: ✅ if unpaid or untouched stock

4. Delivery Note
Model: DeliveryNote

Purpose: Stock movement to customer

Editable: ❌

Voidable: ❌

