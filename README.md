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

## Architecture

Business code lives in `src/` (namespace `GarAge\`), one module per bounded context:
`Tenancy`, `Units`, `Customers`, `Contracts`, `Billing`, `Pricing`, `Dashboard`, plus `Shared`.

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

Requirements: PHP 8.3+, Composer, Node 22+, SQLite (default) or MySQL/PostgreSQL.

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
- Production defaults (`AppServiceProvider`): immutable dates, destructive database commands
  blocked and strict password rules.

## Known risks and decisions

- **Deleting a business** cannot rely on a database cascade alone: contracts and fees hold
  `restrictOnDelete` keys to units, customers and contracts (a safety net against deleting data
  still in use). `TenantEraser` deletes the rows child-first in one transaction instead.
- **No orphan businesses**: an owner cannot delete their account while they own a business.
- **Dashboard queries** load a business's contracts and fees in memory — fine for a small
  business; range queries would be the next step for large histories.
- **Roles**: memberships store a role (`owner`/`staff`), but staff permissions are not built yet.

## Roadmap

- Team members and roles (staff, policies).
- Public page listing free units.
