# Copilot instructions for iot-backend

## Project snapshot
- This repository is currently a **Laravel 10 API/backend scaffold** with minimal domain code (mostly framework defaults).
- PHP backend is primary; frontend assets are Vite-based but only default bootstrap files are present.
- Authentication baseline is Sanctum token-capable via `User` model + personal access tokens migration.

## Architecture and request flow
- HTTP entrypoint: `public/index.php` -> Laravel app bootstrap in `bootstrap/app.php`.
- Route registration is centralized in `app/Providers/RouteServiceProvider.php`:
  - API routes: `routes/api.php` with `api` middleware + `/api` prefix.
  - Web routes: `routes/web.php` with `web` middleware.
- Middleware behavior lives in `app/Http/Kernel.php`:
  - `api` group uses throttling alias `throttle:api` and bindings.
  - `web` group has sessions + CSRF enabled.
- Current API surface is intentionally minimal (`GET /api/user` protected by `auth:sanctum`).

## Data and auth conventions
- Default DB connection is MySQL (`config/database.php`, `.env.example` values).
- Existing schema is default Laravel tables only (`users`, `password_reset_tokens`, `personal_access_tokens`, `failed_jobs`).
- `app/Models/User.php` uses `HasApiTokens`, so token auth should be implemented with Sanctum patterns.
- Password casting uses `'password' => 'hashed'`; do not manually hash twice when using mass assignment.

## Developer workflows (use these first)
- Install deps: `composer install` and `npm install`.
- App bootstrap: copy `.env.example` to `.env`, then `php artisan key:generate`.
- Run backend locally: `php artisan serve`.
- Apply schema: `php artisan migrate`.
- Run tests: `php artisan test` (PHPUnit 10 configured by `phpunit.xml`).
- Frontend dev/build (if touching assets): `npm run dev` / `npm run build`.

## Codebase-specific guidance for AI agents
- Treat this as a **starting template**: when adding features, create explicit Controllers/Requests/Models instead of putting logic in route closures.
- Keep route organization split by transport (`routes/api.php` vs `routes/web.php`) and preserve middleware intent.
- For authenticated API endpoints, prefer `auth:sanctum` middleware to stay aligned with existing setup.
- Add DB changes through migrations in `database/migrations`; do not edit existing migration history unless asked.
- If tests are added for API work, place them in `tests/Feature` and follow `ExampleTest` style (`$this->get(...)`, status assertions).
- Keep `AppServiceProvider`/`EventServiceProvider` clean unless introducing container bindings or app-wide boot hooks.

## Integration points and external dependencies
- Core backend stack: `laravel/framework`, `laravel/sanctum`, `guzzlehttp/guzzle` (`composer.json`).
- Frontend toolchain: `vite`, `laravel-vite-plugin`, `axios` (`package.json`, `resources/js/bootstrap.js`).
- Broadcasting/Echo scaffolding exists but is commented out in `resources/js/bootstrap.js`; enable only when real-time features are required.
