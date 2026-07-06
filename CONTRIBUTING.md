# Contributing to Software Compass

Thanks for your interest in contributing! This project welcomes issues and
pull requests.

## Getting started

Follow the [Quickstart with Docker](./README.md#quickstart-with-docker-recommended-for-contributors)
section in the README to get a local environment running.

## Development workflow

1. Fork the repository and create a feature branch from `main`.
2. Make your changes, keeping the architecture boundaries described in
   [`COPILOT_IMPLEMENTATION_BRIEFING.md`](./COPILOT_IMPLEMENTATION_BRIEFING.md)
   in mind: Domain stays framework-agnostic, controllers delegate to
   Application Services, Infrastructure owns persistence/security details.
3. Run the quality checks before opening a PR:

   ```bash
   docker compose exec app composer qa   # format, test and lint
   ```

   Prefer running tests through Docker whenever database access is involved;
   the Docker image provides the intended PHP version, PDO extensions and the
   MariaDB service.
4. Keep the UI copy in English. Avoid introducing organization-specific
   names, credentials or data anywhere in code, documentation or package
   metadata — an organization-specific deployment is purely a matter of
   configuration and seed data.
5. Prefer DRY implementations: extract repeated request parsing, CSRF checks,
   view helpers and persistence/audit behavior into shared services/helpers
   instead of duplicating logic across controllers and views.

## Commit messages and pull requests

* Use clear, descriptive commit messages.
* Describe the "why" behind a change in the PR description, not just the
  "what".
* Link related issues where applicable.
* Keep PRs focused on a single concern when possible.

## Reporting bugs and requesting features

Please use GitHub Issues. Include steps to reproduce, expected vs. actual
behavior, and relevant environment details (PHP version, browser, etc.) for
bug reports.

## Security issues

Please do not open a public issue for security vulnerabilities. See
[SECURITY.md](./SECURITY.md) instead.

## License of contributions

Software Compass is licensed under
[`AGPL-3.0-only`](./LICENSE). By submitting a pull request, you agree that
your contribution is licensed under the same terms as the rest of the project.

Maintainer preference (non-binding): if you operate a hosted fork, publish
its source code publicly whenever possible. AGPL requires source access for
interacting users; public publication is encouraged for transparency and easier
collaboration.

See also the [Trademark Policy](./TRADEMARK.md) regarding use of the project
name and branding for forks.

## Code of Conduct

By participating in this project, you agree to abide by the
[Code of Conduct](./CODE_OF_CONDUCT.md).
