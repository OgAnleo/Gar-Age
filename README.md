# Gar-Age

Multi-tenant SaaS for businesses that rent out **garages and storage units**: units, customers,
rental contracts with flexible booking modes, automatic fees and a dashboard. Each business gets
its own space under `/{business-slug}`.

Built with Laravel 13 + Inertia + React 19 + TypeScript, following a **hexagonal architecture**
(ports & adapters) with a strict quality gate.

## Features

- **Units** — garages and storage units with area and a daily rate.
- **Customers** — validated Spanish tax ids (DNI/NIE/CIF), unique per business.
- **Contracts** — weekly, monthly, annual (open-ended or for N periods) or specific days. A unit
  can never be booked twice on the same day; specific-day bookings can interleave.
- **Pricing** — per-business discounts for weekly/monthly/annual bookings. The price is fixed when
  the contract is made; later discount changes do not affect it.
- **Fees** — one per billing period, issued daily by a scheduled command (idempotent), with
  pending / overdue (7 days of grace) / paid status.
- **Dashboard** — today's occupancy, revenue collected and issued this month, the last six months
  of collections, overdue fees and free units.
- **Accounts** — registration creates the business; 2FA and passkeys (Laravel Fortify). An owner
  deletes their business (and all its data) before deleting their account.
- **Team** — owners add employees by email; someone without an account gets one with a temporary
  password shown once (no mail needed). Staff run units, customers, contracts and fees; pricing,
  the team and the business settings stay with owners (`manage-business` gate).
- **Public page** — when the owner publishes it, `/{business}/disponibles` shows anyone the units
  free today with their price per day, week, month and year, and the business's contact details.

## Architecture

Business code lives in `src/` (namespace `GarAge\`), one module per bounded context:
`Tenancy`, `Units`, `Customers`, `Contracts`, `Billing`, `Pricing`, `Dashboard`, `Catalog`, plus
`Shared`.

```
            ┌──────────────────────────── app/ (Laravel adapters) ──────────────────────────┐
 HTTP ───▶  │ Controllers · Form requests · Presenters · Console commands · Eloquent models │
            └───────────────────────────────────────┬───────────────────────────────────────┘
                                                    ▼
            ┌────────────── src/*/Application ──────────────┐
            │ Use cases: CreateContract, GenerateDueFees…   │
            └──────────────────────┬────────────────────────┘
                                   ▼
            ┌────────────── src/*/Domain ───────────────────┐      ┌── src/*/Infrastructure ──┐
            │ Entities, value objects, rules, ports         │ ◀─── │ Eloquent repositories,   │
            │ (UnitRepository, TenantEraser…) — pure PHP    │      │ adapters of the ports    │
            └───────────────────────────────────────────────┘      └──────────────────────────┘
```

- **Domain** — pure PHP: no Laravel. Money and areas are integer hundredths. Booking modes are
  `Schedule` subclasses (open/closed: a new mode is a new class).
- **Application** — one use case per class with `handle()`, depending only on the domain.
- **Infrastructure** — adapters implementing the ports, bound in `DomainServiceProvider`.
- Modules that must not be deleted while referenced declare `*Usage` ports implemented by the
  referring module, so module dependencies never form a cycle.
- `tests/Unit/ArchitectureTest.php` enforces that dependencies only point inward; use cases are
  unit-tested with in-memory adapters (`tests/Doubles`).

**Multi-tenancy**: one shared database with a `tenant_id` on every row. The slug in the URL is
resolved into the current tenant and passed explicitly to every use case, so another business's
ids are simply "not found". An Eloquent global scope remains as a safety net.

## Getting started

Requirements: PHP 8.4+, Composer, Node 22+, SQLite (default) or MySQL/PostgreSQL.

```bash
composer setup          # install, .env, key, migrate, build
php artisan db:seed     # demo business (safe to run again: it rebuilds it)
composer dev            # development environment (php artisan dev)
```

Demo login: `demo@gar-age.test` / `password`.

## Quality

| Command                                 | What it checks                                                         |
| --------------------------------------- | ---------------------------------------------------------------------- |
| `composer test`                         | Pint, PHPStan (Larastan) and the Pest suite                            |
| `npm run check` / `npm run types:check` | Frontend lint, format and TypeScript                                   |
| `composer ci:check`                     | Everything CI runs (GitHub Actions)                                    |
| `composer quality`                      | Quality gate: coverage ≥ 70 %, duplication ≤ 1 %, CRAP ≤ 30 per method |

The quality gate runs as a pre-commit hook (`composer hooks:install`) and needs Xdebug or PCOV.
Current figures: ~99 % line coverage, < 1 % duplication, highest CRAP 5.

## Production notes

- Set `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` and a real database
  (`DB_CONNECTION`, …), then `php artisan migrate --force` and `npm run build`.
- **Scheduler**: fees are issued by `fees:generate` at 03:00 every day. Run the scheduler every
  minute (`* * * * * php artisan schedule:run`) or the host's equivalent; if it is missed, the
  command can be run by hand (`php artisan fees:generate [YYYY-MM-DD]`) or from the Fees page,
  and it never issues duplicates.
- **Hosts without cron**: `POST /cron/fees` with `Authorization: Bearer <CRON_TOKEN>` does the
  same as `fees:generate` for today. The endpoint only exists when `CRON_TOKEN` is set.
- **Public demo** (`DEMO_MODE=true`): the login page shows the demo credentials; the demo account
  cannot change its profile, password, second factor or passkeys, nor delete its business; and
  `POST /cron/demo` (same token) rebuilds the demo business and account. The scheduler workflow
  calls it every night before issuing fees.
- Production defaults (`AppServiceProvider`): immutable dates, destructive database commands
  blocked and strict password rules.

## Deploying (free: Render + Neon)

The repository ships a production `Dockerfile` (Apache + PHP 8.5, frontend built in a separate
stage; migrations run when the container starts), a Render Blueprint (`render.yaml`) and a
GitHub Actions workflow that plays the scheduler (`.github/workflows/scheduler.yml`). CI also runs
the test suite on PostgreSQL.

1. **Database** — a free project on [Neon](https://neon.com) (Frankfurt), linked in `neon.ts`.
   `neon env pull` writes its two connection strings to `.env`: `DATABASE_URL` (pooled, used by
   the app) and `DATABASE_URL_UNPOOLED` (direct, used by migrations, which need session state).
   The `pgsql` connection falls back to `DATABASE_URL` when `DB_URL` is not set.
2. **App** — on [Render](https://render.com), _New → Blueprint_ and pick this repository. Fill in
   the values it asks for:
    - `APP_KEY`: the output of `php artisan key:generate --show`;
    - `APP_URL`: the service URL, e.g. `https://gar-age.onrender.com`;
    - `DATABASE_URL` and `DATABASE_URL_UNPOOLED`: the two Neon connection strings.
3. **Demo data** (optional) — with the Neon variables in `.env`:
   `DB_CONNECTION=pgsql php artisan db:seed --force`.
4. **Scheduler** — in the GitHub repository, add the secrets `APP_URL` and `CRON_TOKEN` (copy the
   value Render generated). Run the _scheduler_ workflow once by hand to check it.

Free plan caveats: the app sleeps after 15 minutes without traffic and takes about a minute to
wake up; Neon's free compute also scales to zero; there is no outgoing mail (`MAIL_MAILER=log`), so email verification is off
(`AUTH_VERIFY_EMAIL=false`);
GitHub pauses scheduled workflows after 60 days without repository activity.

## Known risks and decisions

- **Deleting a business** cannot rely on a database cascade alone: contracts and fees hold
  `restrictOnDelete` keys to units, customers and contracts (a safety net against deleting data
  still in use). `TenantEraser` deletes the rows child-first in one transaction instead.
- **No orphan businesses**: an owner cannot delete their account while they own a business.
- **Dashboard queries** load a business's contracts and fees in memory — fine for a small
  business; range queries would be the next step for large histories.
- **Temporary passwords** are not forced to change on first login; employees change them in
  their security settings.
