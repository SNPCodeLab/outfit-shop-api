# Strict Rule: No Unauthorized Database Seeding

## 1. Absolute Golden Rule
> [!CAUTION]
> **NEVER SEED OR MUTATE DATABASE WITHOUT EXPLICIT USER APPROVAL**
> - The AI Agent **MUST NEVER** execute `php artisan db:seed`, SQL insert scripts, bulk catalog loaders, or database population scripts automatically or unilaterally.
> - Even if preparing a seeder, script, or fixture, the agent **MUST ONLY** write/prepare the code and **EXPLICITLY ASK FOR CONFIRMATION** before running it against the database.

## 2. Scope of Restriction
This rule applies to:
- `php artisan db:seed`
- Direct SQL `INSERT`, `BULK INSERT`, `COPY`, `UNPREPARED` statements
- Custom ingestion scripts (e.g. `BulkProductCatalogSeeder`, `BrandCatalogSeeder`, `KhmeRielCatalogSeeder`)
- Any background task or tinker command that alters database row counts

## 3. Required Workflow
1. When asked to "prepare a seeder" or "build a seeder", write and present the code/script only.
2. Explain what the seeder will do, what tables it touches, and how many records it will create.
3. Stop and wait for the user to explicitly say "run the seeder", "execute seed", or "proceed with seeding" before touching the database.
