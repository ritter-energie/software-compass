# Copilot instructions for Software Compass

- Run unit and feature tests through Docker whenever database access is involved. The Docker environment provides the intended PHP version, PDO extensions and MariaDB service.
- Prefer DRY implementations: extract repeated request parsing, CSRF checks, view helpers and persistence/audit behavior into shared services/helpers instead of duplicating logic across controllers and views.
- Keep the UI copy in English and avoid company-specific hardcoding in code, documentation and package metadata.
- Preserve the architecture boundaries from `COPILOT_IMPLEMENTATION_BRIEFING.md`: Domain stays framework-agnostic, controllers delegate business logic to Application Services, Infrastructure owns persistence/security details.
- Keep stack and rendering model aligned with the briefing: Apache + PHP 8.4 + Tempest 2.x + MariaDB, server-rendered HTML, targeted TypeScript enhancements, SASS for styling, Mermaid diagrams generated at runtime.
- Keep persistence Tempest-idiomatic: schema changes as PHP migration classes and repository implementations via Tempest's database/query APIs (no raw SQL assembled from untrusted input).
- Enforce baseline security in all changes: CSRF protection on forms, escaped output, escaped Mermaid labels, URL validation for URL fields, and delete actions via POST endpoints only.
- Keep i18n translator-based with English and German message catalogs; UI defaults to English wording.
- Apply project coding standards consistently: `declare(strict_types=1);`, dependency injection, repository interfaces, short focused methods, and `final`/`readonly` where appropriate.

