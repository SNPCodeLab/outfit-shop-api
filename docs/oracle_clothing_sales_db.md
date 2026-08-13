# Oracle Clothing Sales Management System — Database Reference

Comprehensive guide and rules for designing, reviewing, improving, and generating Oracle Database schemas for Clothing Sales Management Systems.

## Registered Skill Paths
- **Workspace**: [`.agents/skills/oracle-clothing-sales-db/SKILL.md`](file:///Users/Apple16/Desktop/SS_MIS/.agents/skills/oracle-clothing-sales-db/SKILL.md)
- **Global**: [`~/.gemini/config/skills/oracle-clothing-sales-db/SKILL.md`](file:///Users/Apple16/.gemini/config/skills/oracle-clothing-sales-db/SKILL.md)

## Key Guidelines

1. **Target Database**: Oracle Database (`NUMBER(10)`, `VARCHAR2(n)`, `NUMBER(12,2)`, `TIMESTAMP`, `CLOB`).
2. **Core Pattern**: `PRODUCT 1 -> N PRODUCT_VARIANT (SIZE, COLOR)`.
3. **Transaction Split**: Header / Detail (`PURCHASE_HEADER`/`DETAIL`, `SALE_HEADER`/`DETAIL`).
4. **Historical Price Preservation**: Always freeze `unit_price` / `cost_price` at transaction execution time.
5. **Constraints**: Mandatory `CHECK`, `NOT NULL`, `UNIQUE(product_id, size_id, color_id)`, `UNIQUE(sku)`, `UNIQUE(username)`.
