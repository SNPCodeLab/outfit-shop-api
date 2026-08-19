---
name: postgresql-relationship-audit
description: Comprehensive workflow for auditing and fixing PostgreSQL database relationships, including foreign key validation, missing index detection, orphaned record cleanup, and data type mismatch resolution.
---

# PostgreSQL Relationship Audit & Fix Protocol

This skill provides a strict protocol for maintaining referential integrity and performance in PostgreSQL environments (e.g., NeoDB).

## 1. Discovery Queries

### Detect Missing FK Indexes
```sql
SELECT conname, conrelid::regclass AS table_name, a.attname AS column_name, 'HIGH' AS severity, 'Missing index on FK column' AS issue 
FROM pg_constraint c 
JOIN pg_attribute a ON a.attnum = ANY(c.conkey) AND a.attrelid = c.conrelid 
WHERE contype = 'f' 
AND NOT EXISTS (SELECT 1 FROM pg_index i WHERE i.indrelid = c.conrelid AND a.attnum = ANY(i.indkey));
```

### Detect Orphaned Records
For each foreign key found, run:
```sql
SELECT COUNT(*) FROM [table_name] t 
LEFT JOIN [referenced_table] r ON t.[fk_column] = r.[pk_column] 
WHERE t.[fk_column] IS NOT NULL AND r.[pk_column] IS NULL;
```

### Detect Data Type Mismatches
```sql
SELECT kcu.table_name, kcu.column_name, c1.data_type AS fk_data_type, c2.data_type AS pk_data_type
FROM information_schema.key_column_usage kcu 
JOIN information_schema.constraint_column_usage ccu ON kcu.constraint_name = ccu.constraint_name 
JOIN information_schema.columns c1 ON c1.table_name = kcu.table_name AND c1.column_name = kcu.column_name 
JOIN information_schema.columns c2 ON c2.table_name = ccu.table_name AND c2.column_name = ccu.column_name 
WHERE kcu.constraint_name IN (SELECT constraint_name FROM information_schema.table_constraints WHERE constraint_type = 'FOREIGN KEY') 
AND c1.data_type != c2.data_type;
```

## 2. Fix Implementation

### Zero-Downtime Indexing
Always use `CONCURRENTLY` to avoid locking production tables.
```sql
CREATE INDEX CONCURRENTLY idx_table_column ON table_name(column_name);
```

### Constraint Validation
If a constraint was created `NOT VALID`, use:
```sql
ALTER TABLE table_name VALIDATE CONSTRAINT constraint_name;
```

## 3. Reporting Standards
Reports must categorize issues by:
- **CRITICAL**: Data loss risk, orphans, circular loops.
- **HIGH**: Missing indexes on high-traffic FKs, type mismatches.
- **MEDIUM**: Unvalidated constraints, naming convention violations.
- **LOW**: Performance optimizations on low-traffic tables.
