## Domain Manager for ownCloud – Contribution Guidelines

This document describes how to work on this app in a way that keeps it stable, secure, and easy to maintain – whether you are a human contributor or using an AI assistant.

### 1. Scope and goals

- **Primary goal**: Provide a reliable, auditable way to manage domains and DNS across providers inside ownCloud 10, without surprising side‑effects for admins or end users.
- **Non‑goals**:
  - Turning this into a general purpose DNS control panel outside ownCloud.
  - Supporting PHP or ownCloud/Nextcloud versions outside those declared in `composer.json` and `appinfo/info.xml`.

### 2. Architecture expectations

- **Respect existing boundaries**:
  - `OCA\DomainManager\Db`: persistence and low‑level DB entities.
  - `OCA\DomainManager\Provider`: provider connectors implementing `IDomainProviderRepository`.
  - `OCA\DomainManager\Service`: application services (`DomainService`, lookup services, etc.).
  - `OCA\DomainManager\Controller`: HTTP-facing controllers only; no business logic.
  - `templates/`, `css/`, `js/`: UI layer only.
- **Additions**:
  - New providers go under `lib/Provider/` and implement `IDomainProviderRepository`.
  - New lookup strategies go under `lib/Service/Lookup/` and implement `ILookupService`.
  - All wiring belongs in `lib/AppInfo/Application.php` (DI container and event wiring).

### 3. Coding standards

- **Language and versions**:
  - PHP version support is defined in `composer.json` and `appinfo/info.xml`. Do not use language features beyond the maximum supported PHP version.
- **General style**:
  - Use strict types (`declare(strict_types=1);`).
  - Prefer **constructor injection** over service locators or static calls.
  - Keep controllers thin; push logic into services.
  - Follow ownCloud and PSR‑4 namespacing (see `composer.json` for the root namespace).
- **Error handling**:
  - Fail fast on misconfiguration (missing tokens, unreachable endpoints) with clear error messages.
  - Do not silently swallow exceptions; log them using ownCloud’s logging facilities.
  - Avoid leaking provider secrets in error messages or logs.

### 4. Data, security, and privacy

- **Secrets & credentials**:
  - Never commit real API keys, passwords, or tokens.
  - Keep provider configuration in admin settings, not hard‑coded in PHP or JS.
- **Access control**:
  - Any new controller action must:
    - Check authentication and appropriate permissions / admin status.
    - Respect configured groups and access restrictions.
  - When exposing domain data, ensure that users can only see and modify what they own, unless explicitly in an admin view.
- **Migrations and schema changes**:
  - New DB fields and tables must be added through `appinfo/Migrations/*` only.
  - Migrations must be **idempotent** and safe to run multiple times.
  - Avoid destructive schema operations in minor/patch releases unless strictly necessary.

### 5. UX and UI

- **Look and feel**:
  - Match ownCloud 10 Files app styling as much as possible.
  - Prefer consistency over novelty; reuse existing CSS conventions before adding new ones.
- **Interactions**:
  - Avoid native `confirm()` for new code; favor ownCloud’s modal / dialog system.
  - Validate user inputs on both client and server.
  - For long‑running operations (e.g. remote lookups, provider sync), provide user feedback and avoid blocking the UI indefinitely.

### 6. Testing and quality

- **Unit and integration tests**:
  - Add or update tests under `tests/` when changing behavior, especially in:
    - `DomainService`
    - Repositories in `Db/`
    - Provider implementations
  - Mock external providers and APIs; never hit real endpoints in tests.
- **Static checks**:
  - Use the project’s static checking tools (e.g. `get_errors`) to verify signatures and namespaces.
  - Fix new warnings you introduce; avoid increasing the technical‑debt surface.

### 7. Working with AI assistants

- **Before asking an AI to edit code**:
  - Provide explicit context: mention ownCloud 10, PHP version bounds, and the relevant files or classes.
  - Clarify whether the change is a **refactor**, **bugfix**, or **new feature**, and how it should affect behavior.
- **Safe change boundaries**:
  - It is generally safe for AI to:
    - Refactor purely internal methods without changing public signatures.
    - Improve error messages, logging, or small UX issues.
    - Add tests that mirror documented behavior.
  - AI should be used with extra care when:
    - Touching `appinfo/` (app metadata, migrations wiring).
    - Changing DB schemas or migrations.
    - Modifying permission checks and access control.
    - Altering provider integrations or external API calls.
- **Review expectations**:
  - All non‑trivial AI‑generated changes must be reviewed by a human maintainer.
  - Pay special attention to:
    - Backwards compatibility for public APIs and DB schemas.
    - Error handling paths and edge cases (null/empty values, missing configuration).
    - Performance for operations that may run across large domain sets.

### 8. Pull requests and issue workflow

- **When opening a PR**:
  - Describe the problem, the proposed solution, and any alternative approaches considered.
  - Note whether changes were produced or assisted by an AI and which parts were heavily edited by hand.
  - Include testing notes: which commands were run, which providers or flows were exercised.
- **When reviewing**:
  - Prefer small, focused PRs over broad refactors.
  - Ask for additional tests when behavior changes or new edge cases are introduced.

### 9. Backwards compatibility and deprecations

- Avoid breaking changes to:
  - Public service interfaces (`DomainService`, repository interfaces).
  - Controller endpoints and routes exposed to clients.
- If you must break compatibility:
  - Document it in `CHANGELOG.md`.
  - Provide migration or fallback paths where feasible.

