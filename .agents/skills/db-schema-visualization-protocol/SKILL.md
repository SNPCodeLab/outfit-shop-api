---
name: db-schema-visualization-protocol
description: Protocol for generating complete, high-fidelity Mermaid.js ER diagrams directly from a PostgreSQL database. Captures all tables, columns, PK/FK/UK markers, data types, nullability, defaults, and cardinality with 100% schema coverage.
---

# Database Schema Visualization Protocol

Use this protocol to generate a professional-grade ER diagram for the entire database.

## 1. Schema Extraction
Query the PostgreSQL system catalogs (`information_schema`) to gather the complete structural metadata.

### Core Components to Capture:
- **Tables**: Every base table in the `public` schema.
- **Columns**: Name, clean data type, nullability (`NOT NULL`), and default values.
- **Keys**: Primary Keys (PK), Foreign Keys (FK), and Unique Constraints (UK).
- **Relationships**: Parent-child links derived from `referential_constraints`.

## 2. Cardinality Mapping
Determine the relationship type based on the presence of Unique Constraints on Foreign Key columns.

- **1:N (||--o{)**: Standard Foreign Key without a Unique constraint on the child column.
- **1:1 (||--||)**: Foreign Key column that also has a `UNIQUE` constraint.

## 3. Mermaid Syntax Standards
To ensure compatibility with the Mermaid Live Editor:
- **Type Sanitization**: Replace spaces, parentheses, and commas in data types with underscores (e.g., `character_varying`).
- **Comment Cleanliness**: Place constraints like `NOT NULL` and `DEFAULT` within double quotes after the column name.
- **Cardinality Labels**: Label every relationship line with the name of the join column for maximum clarity.

## 4. Execution Workflow
1.  Run the internal `php artisan tinker` script to output the `erDiagram` block.
2.  Paste the output into [Mermaid Live](https://mermaid.live).
3.  Analyze the resulting graph for architectural bottlenecks or missing indices.
