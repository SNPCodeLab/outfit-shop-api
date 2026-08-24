# Implementation Plan - Master Backend API & Role Permission Synchronisation

This plan outlines the steps to achieve 100% synchronisation between the backend API and the Next.js frontend, following the "MASTER BACKEND ENGINEERING PROMPT".

## User Review Required

> [!IMPORTANT]
> The project uses `SaleHeader` for orders and `system_broadcast_alerts` for alerts. The proposed code in the prompt used `Order` and `broadcast_alerts`. I will adapt the code to use the existing project models while ensuring the functionality matches the requirements.

> [!WARNING]
> I will be updating `routes/api.php` significantly to match the hierarchical structure and permission gates requested.

## Proposed Changes

### 1. Database Schema Alignment

#### [NEW] [2026_08_24_220000_complete_outfit_schema_alignment.php](file:///Users/Apple16/Desktop/BACKEND/database/migrations/2026_08_24_220000_complete_outfit_schema_alignment.php)
- Add soft deletes to `products`, `customers`, `clothing_sizes`, `colors`. (Already in `employees`).
- Add `pantone` to `colors`.
- Add `is_active` to `promotions`.
- Ensure `broadcast_alerts` exists (mapping to the requirements).

### 2. Form Requests & Validation

#### [NEW] [UpdateSizeRequest.php](file:///Users/Apple16/Desktop/BACKEND/app/Http/Requests/UpdateSizeRequest.php)
#### [NEW] [UpdateColorRequest.php](file:///Users/Apple16/Desktop/BACKEND/app/Http/Requests/UpdateColorRequest.php)
#### [NEW] [AdminResetPasswordRequest.php](file:///Users/Apple16/Desktop/BACKEND/app/Http/Requests/AdminResetPasswordRequest.php)

### 3. Controllers Business Logic

#### [MODIFY] [ClothingSizeController.php](file:///Users/Apple16/Desktop/BACKEND/app/Http/Controllers/Api/V1/ClothingSizeController.php)
- Update to include `size_code` and standard response envelope.
#### [MODIFY] [ColorController.php](file:///Users/Apple16/Desktop/BACKEND/app/Http/Controllers/Api/V1/ColorController.php)
- Update to include `pantone` and standard response envelope.
#### [MODIFY] [OrderController.php](file:///Users/Apple16/Desktop/BACKEND/app/Http/Controllers/Api/V1/OrderController.php)
- Add `voidOrder` method with stock restoration logic.
#### [MODIFY] [AiIntelligenceController.php](file:///Users/Apple16/Desktop/BACKEND/app/Http/Controllers/Api/V1/AiIntelligenceController.php)
- Align with the specific AI prediction methods requested.
#### [MODIFY] [AdminMasterController.php](file:///Users/Apple16/Desktop/BACKEND/app/Http/Controllers/Api/V1/AdminMasterController.php)
- Ensure monitoring endpoints like `masterPulse`, `performance`, `apiAnalytics` and `broadcastAlert` are present.

### 4. Route Synchronization

#### [MODIFY] [api.php](file:///Users/Apple16/Desktop/BACKEND/routes/api.php)
- Reorganize routes into the 4 Tiers: PUBLIC, AUTHENTICATED (STAFF/CASHIER), MANAGER, and ADMIN.
- Apply correct `role` and `ability` middleware.

## Verification Plan

### Automated Tests
- Run `php artisan test` to ensure no regressions.
- Run `./vendor/bin/pint --test` for code style.
- Manually test key new endpoints using `curl` or a test script.

### Manual Verification
- Verify the standard response envelope across different controllers.
- Check RBAC gates by attempting access with different role-mocked tokens.
