# Fix HTTP 500 RuntimeException in POST /auth/login

The `POST /auth/login` endpoint is returning a 500 error with no details. Analysis suggests the error is occurring outside the main `try-catch` block in `AuthController::login`, likely due to a `Cache` failure (since `database` driver is used) or an unhandled exception in the global middleware/exception handler when running in the production-like environment of Vercel.

## Proposed Changes

### 1. [MODIFY] [AuthController.php](file:///Users/Apple16/Desktop/OutfitShop MIS and POS API/app/Http/Controllers/Api/V1/AuthController.php)
- Wrap the entire `login` method in a `try-catch` block to ensure all exceptions (including those from `Cache` at the start and end of the method) are caught.
- Ensure all database interactions and token creation are within this block.

### 2. [MODIFY] [config/auth.php](file:///Users/Apple16/Desktop/OutfitShop MIS and POS API/config/auth.php)
- Register the `sanctum` guard explicitly in the `guards` array to prevent potential `RuntimeException` if `auth('sanctum')` is called directly (e.g., in `AuditLogService`).

### 3. [MODIFY] [api/index.php](file:///Users/Apple16/Desktop/OutfitShop MIS and POS API/api/index.php)
- Temporarily set `APP_DEBUG` to `true` to allow the user to see the stack trace if the error persists.
- This will be reverted after verification.

### 4. Maintenance
- Run `php artisan config:clear` and `php artisan cache:clear` to ensure no stale configuration is used.
- Run `php artisan optimize:clear` for a full cleanup.

## Verification Plan
- Simulate the login request using `php artisan tinker`.
- Request the user to re-test the endpoint and provide the new response if it still fails (it should now contain details if debug mode is enabled).
- Once confirmed working, revert `APP_DEBUG` in `api/index.php`.
