---
name: wc-api
description: Scaffold an API v1 endpoint group (controller + FormRequest + API Resource + tests).
arguments: resource-name (PascalCase, e.g. Customer)
---

# /wc-api <ResourceName>

Generate an idiomatic WC API endpoint group:

1. `[arka:routing] dev -> Paulo`.
2. Generate controller via `php artisan make:controller Api/V1/<ResourceName>Controller --api --no-interaction`.
3. Generate FormRequests: `Store<ResourceName>Request`, `Update<ResourceName>Request`.
4. Generate API Resource: `<ResourceName>Resource`, `<ResourceName>Collection`.
5. Add route in `routes/api/v1.php` with `Route::apiResource('...', ...)`.
6. Add Scramble attribute annotations for OpenAPI.
7. Generate feature test with full CRUD scenarios (cursor pagination, RFC7807 errors, rate limit, idempotency).
8. Run Pint + PHPStan + Pest. All green then commit.

ARGUMENTS: $1 = PascalCase resource name (e.g. `wc-api Customer`).
