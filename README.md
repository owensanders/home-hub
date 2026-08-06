# HouseHub

Family home organiser — dashboard, meal planner, shopping lists and chore board.
Built from the HouseHub design (`HouseHub.dc.html`) as a Laravel 12 + Inertia 2 +
Vue 3 (TypeScript) + Tailwind app, running under Laravel Sail.

## Running it

```bash
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate --seed
./vendor/bin/sail npm install
./vendor/bin/sail npm run dev
```

Then <http://localhost:8090>. Sign in as `sarah@househub.test` / `password`
(also `james@`, `mia@`, `noah@`).

Ports are shifted off the defaults so they don't clash with the `~/docker`
monorepo's services if both are running: app `8090`, Vite `5174`, MySQL `3307`,
Redis `6380`.

## Checks

```bash
./vendor/bin/sail artisan test
./vendor/bin/sail php vendor/bin/phpstan analyse    # level 6, clean
./vendor/bin/sail npx vue-tsc --noEmit              # see caveat below
```

## Layout

Clean architecture, matching the conventions in `../CLAUDE.md`:

| Layer | Where | Notes |
|---|---|---|
| Use cases | `app/UseCases/**` | All business logic — one class per screen or action |
| Repositories | `app/Repositories/**` | Behind `app/Contracts/Repositories/*Interface.php`, bound in `RepositoryServiceProvider` |
| DTOs | `app/Data/**` | `spatie/laravel-data`; mirrored in `resources/js/types/househub.ts` |
| Screens | `resources/js/pages/*.vue` | Wrapped in `layouts/HouseHubLayout.vue` |

Design tokens live as `--hh-*` custom properties in `resources/css/app.css`
and are surfaced as Tailwind `hh-*` colours. They key off the same `.dark`
class the starter kit's appearance switcher already toggles, so the design's
dark theme comes for free.

## Known rough edges

- Calendar, Budget, House, Documents and Maintenance render the design's
  "not designed yet" empty state — those screens weren't in this design pass.
- `vue-tsc` reports ~15 pre-existing type errors in starter-kit components
  (`AppHeader`, `AppSidebar`, `Welcome`, auth pages). None are in HouseHub code.
- The dashboard weather panel reads a static forecast from `config/household.php`.
