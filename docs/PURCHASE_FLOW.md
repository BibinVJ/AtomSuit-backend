# Purchase Transactions & Financial Flow Architecture

This document describes the lifecycle, snapshotting architecture, and mathematical processing engine for Purchase transactions within the Atom Suit ERP backend.

## 1. Transaction Lifecycle
Atom Suit supports flexible purchasing workflows:

- **Purchase Order (PO):** The initial proposal to purchase goods from a Vendor.
- **Goods Received Note (GRN):** The physical reception of goods at the warehouse, triggering a stock increment.
- **Purchase Invoice (PI):** The vendor's bill for accounting purposes, converting the received value into Accounts Payable.
- **Debit Note (DN):** A return or adjustment that reverses accounts payable and (optionally) returns stock to the vendor.

Transactions can be chained (e.g., PO → GRN → PI) or created directly (e.g., Direct PI without a PO).

## 2. The Snapshot Principle (Immutability)
To comply with strict accounting and auditing standards, Atom Suit employs **Hard Snapshots** during document generation.

When an item is added to a transaction, the `price`, `item_meta`, and `tax_meta` are physically copied from the master tables into the transaction item JSON structures. 
If an administrator changes the definition of "Laptop" or raises the state Tax Rate from 18% to 20% tomorrow, **historical invoices are not affected.**

If a transaction relies on a parent document (e.g., generating a PI from a GRN), the PI simply inherits the exact snapshot structure recorded on the parent document.

## 3. Tax Determination & Storage
Taxes in Atom Suit support complex, nested, multi-jurisdiction layouts. 
A `TaxGroup` contains multiple underlying `TaxRates` (e.g., "India Inter-State" contains both "CGST 9%" and "SGST 9%").

**Precedence Rules:**
1. If a Vendor has a default Tax Group, it overrides the Item's Tax Group.
2. If no Vendor Tax Group exists, the Item's default Tax Group is used.

**Snapshot Storage:**
Taxes are stored recursively inside the `tax_meta` column as an array matrix. Both Percentage-based and Fixed-amount fees are supported:
```json
{
    "id": 1,
    "name": "Intra-State GST 18%",
    "rates": [
        {"id": 10, "name": "CGST 9%", "rate": 9.00, "type": "percentage"},
        {"id": 11, "name": "Environmental Fee", "rate": 5.00, "type": "fixed"}
    ]
}
```

## 4. Global Mathematical Engine
Calculation logic is strictly forbidden from lying inside frontend payloads or model observers. Instead, the backend acts as the single source of truth via the `RecalculatePurchaseDocumentTotalsAction` engine.

When any document is generated or modified, this global engine:
1. Loops through all line items.
2. Identifies the operational quantity (`accepted_quantity` for GRNs, `quantity` for others).
3. Applies line-level `discount_amount` logic (Percentage vs Fixed value calculations).
4. Loops dynamically through the complex `tax_meta['rates']` array, evaluating each nested tax rate and aggregating the `tax_amount`.
5. Caches the exact financial figures onto the Line Item (`sub_total`, `tax_amount`, `total_amount`).
6. Sums the items upwards and forcefully overwrites the cached document Header columns (`sub_total`, `discount_total`, `tax_total`, `total_amount`).

Because all four transaction types (PO, GRN, PI, DB) deliberately share an identical polymorphic column schema, this single centralized calculator safely governs the entire Purchase domain without duplicating code.
