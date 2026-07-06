# Software Compass

**Software Compass** is an open-source, self-hosted Enterprise Architecture
repository: a lightweight, pragmatic inventory of software components, tools,
interfaces/dependencies and customer journeys inside an organization. It is
**not** a replacement for full EA suites — it's meant to be a fast, always
up-to-date, developer-friendly single source of truth for "what exists, who
owns it, and how does it talk to what".


## Tech stack

* Apache + PHP 8.4
* [Tempest 2.x](https://tempestphp.com) (router, view engine, database/query builder)
* MariaDB
* TypeScript + SASS (compiled via Vite)
* [Mermaid.js](https://mermaid.js.org) for diagram rendering

## Quickstart with Docker (recommended for contributors)

The fastest way to get a working local environment — no local PHP/MariaDB
installation required:

```bash
cp .env.example .env
docker compose up -d --build
docker compose exec app php tempest migrate:up --force
docker compose exec app php tempest database:seed --all --force
```

The lookup/reference-data seeder creates 2–5 neutral sample entries for each
reference data type, such as component types, statuses, criticality levels,
environments, deployment locations, dependency types, communication protocols,
data objects and tags. The demo seeder additionally creates neutral demo
components, dependencies, journeys and an administrator login.

The app is then available at <http://localhost:8080>. Adminer is available at
<http://localhost:8081> (server: `mariadb`, credentials from `.env`).

## Guided first-run setup

On a fresh database (no users yet), opening the app redirects to `/setup`.
This guided setup creates:

* the network name shown in the app header,
* the first administrator account,
* the linked person record and admin role assignment.

After setup, the app switches to the regular login form (`/login`) using the
credentials you configured.

The demo seeder creates a default login (via `/login`):

```text
username: admin
password: admin
```

To build the frontend assets (TypeScript/SASS via Vite):

```bash
docker compose run --rm vite npm install
docker compose run --rm vite npm run build
```

Useful commands:

```bash
docker compose exec app php tempest migrate:fresh --force   # drop & rebuild schema
docker compose exec app php tempest database:seed --all --force # seed lookup + demo data
docker compose exec app php ./vendor/bin/tempest discovery:generate --no-interaction # refresh Tempest discovery/cache after adding/removing classes/views
docker compose exec app composer test                       # run PHPUnit
docker compose logs -f app                                  # tail app logs
docker compose down                                          # stop everything
docker compose down -v                                       # stop and wipe DB data
```

Run unit and feature tests that touch the database through Docker. The Docker
image provides the intended PHP version, `pdo_mysql` and the MariaDB service;
host PHP installations often miss the required PDO driver.

PHPUnit is configured to force `DB_DATABASE=software_compass_test`, so test
setup/migrate/fresh operations do not modify your main `software_compass`
database. Docker initializes that test database automatically for new volumes.

> If port `3306` (MariaDB) or `8080`/`8081` (app/Adminer) are already taken
> on your host, override them in `.env`, e.g. `DB_PORT=3307`, `APP_PORT=8090`.

## Manual installation (without Docker)

### Requirements

* PHP 8.4+ with the `pdo_mysql`, `dom`, `intl`, `mbstring`, `zip` extensions
* Composer 2.x
* MariaDB 10.6+ (or MySQL 8+)
* Node.js 20+ (for building TypeScript/SASS assets)
* Apache 2.4 with `mod_rewrite`

### Steps

```bash
composer install
cp .env.example .env
# edit .env: set DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD
php tempest key:generate
php tempest migrate:up --force
php tempest database:seed --all --force
npm install
npm run build
```

### Apache virtual host

All requests must be routed through `public/index.php`:

```apache
<VirtualHost *:80>
    ServerName software-compass.local
    DocumentRoot /var/www/software-compass/public

    <Directory /var/www/software-compass/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/software-compass-error.log
    CustomLog ${APACHE_LOG_DIR}/software-compass-access.log combined
</VirtualHost>
```

(See also `docker/apache/software-compass.conf`, used by the Docker image.)

### Running tests

```bash
composer test
```

For database-backed tests prefer Docker:

```bash
docker compose exec app composer test
```

Install the local pre-commit hook once to enforce a full Docker test run before
every commit:

```bash
composer hooks:install
```

The hook runs the complete PHPUnit suite and blocks commits on failures.

## Architecture overview

```text
app/
  Domain/          Framework-agnostic entities, value objects and repository interfaces
  Application/      Use-case services (ComponentService, DependencyService, DiagramService, ...)
  Infrastructure/    Tempest-backed repository implementations, DB models, security middleware
  Presentation/      HTTP controllers and .view.php templates
  Shared/            Cross-cutting value objects, enums, exceptions, helpers

database/
  migrations/        Tempest PHP migration classes (see section 0.4 of the briefing)
  seeders/           Lookup + demo data seeders

resources/assets/    TypeScript and SASS sources, bundled via Vite
```

Controllers contain no business logic — they translate HTTP requests into
Application Service calls and render views. Domain entities are framework
agnostic and validate their own invariants (e.g. a dependency can't target
its own source component).

## Data model overview

The schema is defined by Tempest PHP migrations in `database/migrations` and
is persisted exclusively in MariaDB:

* `people` and `users` separate accountable owners from login credentials.
* Lookup tables (`component_types`, `component_statuses`,
  `criticality_levels`, `environments`, `deployment_locations`,
  `dependency_types`, `communication_protocols`, `data_objects`, `tags`)
  normalize stable master data.
* `components` stores applications, tools, APIs, databases and other systems,
  including ownership, deployment, purpose, lifecycle and documentation fields.
* `component_inheritance` stores optional many-to-many parent/child
  relationships between components. A component may have multiple parents and
  multiple children, and neither relationship is required.
* `dependencies` stores interfaces and communication paths between components;
  `dependency_data_objects` links interfaces to the data they exchange.
* `journeys`, `journey_steps` and `journey_step_components` model customer
  journeys and the components used at each step.
* `governance_reviews` tracks checklist state and approval decisions for new
  or changed components.
* `audit_logs` records create/update/delete and governance approval events with
  old/new values and the authenticated user's linked `people` id when present.

## Authentication (MVP)

The MVP protects the whole application with a **session-based login form**,
checked against the `users` table (see `App\Presentation\Http\Controller\AuthController`
and `App\Infrastructure\Security\BasicAuthMiddleware`).
The demo seeder (`Database\Seeders\DemoDataSeeder`) creates a default
`admin` / `admin` user linked to a `people` record. To add further users,
insert a row into `users` with a `password_hash` generated via
`password_hash($password, PASSWORD_DEFAULT)`, e.g. through Adminer or a
custom seeder.

The authenticated user can open `/account` to view profile details and change
the preferred UI language.

## Admin user management

Users with the `admin` role can open `/admin/users` to manage accounts:

* create new users,
* assign an initial role (`viewer`, `editor`, `architect`, `admin`),
* activate/deactivate existing users.

Non-admin users receive `403 Forbidden` on admin user routes.

## Reference data management

Users with the `admin` role can open `/master-data` to manage shared reference
data used by forms, filters and diagrams: component types/statuses,
criticality levels, environments, deployment locations, dependency types,
communication protocols, data objects and tags.

Reference data is modeled with typed enums/DTOs (`ReferenceDataType`,
`ReferenceDataField`, `ReferenceDataFieldType`, `ReferenceDataEntry`) instead
of free-form table or field names. All write actions use CSRF protection;
deletes are POST-only and still referenced entries are protected by foreign-key
constraints.

## Component inheritance

Components can optionally be related through inheritance-style parent/child
links. Both sides are many-to-many and optional. Component forms let users
assign parents and children, detail pages list both directions, and component
diagrams render parents as Mermaid containers for their children. Additional
parents are shown as dashed inheritance links when Mermaid cannot place one
node in multiple containers.

## Tempest cache notes

After adding, renaming or deleting discovered classes or view components,
refresh discovery:

```bash
docker compose exec app php ./vendor/bin/tempest discovery:generate --no-interaction
```

If a stale view-compilation error remains after changing a view, clear compiled
views as well:

```bash
docker compose exec app sh -lc 'rm -rf .tempest/cache/views/* && php ./vendor/bin/tempest discovery:generate --no-interaction'
```

## Governance process

New components in status `Idea`, `In Review` or `Planned` automatically get
a Governance Review with a checklist (duplicate check, interface check,
owner check, data check, deployment check). See `App\Application\Governance`.

## Mermaid diagrams

Diagrams are generated at runtime from current database state and are never
stored as files. `App\Application\Diagram\DiagramService` provides:

* component overview diagrams at `/diagrams/components`,
* component neighborhood diagrams from component detail pages,
* customer journey diagrams at `/diagrams/journeys/{id}`.

All Mermaid node ids and labels are sanitized through shared helpers before
rendering. The frontend initializes Mermaid via TypeScript and offers optional
copy-to-clipboard controls for generated Mermaid source.

## Coding standards

* Keep Domain framework-agnostic; HTTP, persistence and security details belong
  in Presentation/Infrastructure.
* Controllers should parse requests, enforce CSRF and delegate use cases to
  Application Services; do not place business rules in controllers.
* Prefer DRY shared helpers/services for repeated request parsing, CSRF,
  view formatting, audit logging and persistence behavior.
* Use strict object-oriented PHP with `declare(strict_types=1)`, `final` where
  appropriate, explicit repository interfaces and dependency injection.
* Keep UI copy in English and avoid organization-specific hardcoding.
* Use TypeScript only for progressive UX enhancements; no business logic in the
  frontend.
* Keep SASS modular, with status/criticality represented through CSS classes.

## Contributing

Issues and pull requests are welcome. Please read [`CONTRIBUTING.md`](./CONTRIBUTING.md)
and run `composer qa` before submitting a PR. There are no company-specific
names or credentials in this codebase — an organization-specific deployment
is purely a matter of configuration and seed data. This project follows a
[Code of Conduct](./CODE_OF_CONDUCT.md).

## License

Software Compass is licensed under the **GNU Affero General Public License,
version 3 only (AGPL-3.0-only)** — see [`LICENSE`](./LICENSE).

In short:

* You may use, modify, self-host, and distribute the software (including
  commercial usage).
* If you run a modified version for users over a network (SaaS), you must
  offer those users access to the corresponding source code of that version.
* All distributed or network-deployed modified versions remain under AGPL.

Project preference: hosted forks are encouraged to publish their source code
publicly (for example in a public Git repository), even though AGPL formally
requires source access for interacting users.

The project name and logo are additionally covered by a
[Trademark Policy](./TRADEMARK.md), independent of the code license.

## Security

Please see [`SECURITY.md`](./SECURITY.md) for how to report vulnerabilities.

