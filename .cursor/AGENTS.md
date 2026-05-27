## Cursor AI Agents for Domain Manager

This file describes how AI agents (like Cursor’s coding agents) should behave when working on this repository. The goal is to keep the app stable and safe while still benefiting from AI assistance.

### 1. Global rules (apply to all agents)

- **Respect project constraints**:
  - Always read `README.md`, `GUIDELINES.md`, `composer.json`, and `appinfo/info.xml` before making non‑trivial changes.
  - Never assume support for PHP or ownCloud/Nextcloud versions beyond what is declared in these files.
- **Be conservative with risk**:
  - Prefer minimal, clearly‑scoped changes over large refactors.
  - Do not introduce new runtime dependencies without explicit user approval.
  - Treat DB migrations, authentication, and access control as high‑risk areas.
- **Security and privacy**:
  - Never suggest committing real API keys, passwords, or tokens.
  - Avoid logging sensitive provider configuration or secrets.
  - When generating examples, use obviously fake credentials.

### 2. Recommended agent specializations

Agents should pick or be configured with a clear role so their behavior stays predictable.

- **Docs & guidance agent**
  - **Purpose**: Explain architecture, propose designs, and improve documentation.
  - **Primary files**: `README.md`, `GUIDELINES.md`, `CHANGELOG.md`, `LICENSE`, templates in `templates/`.
  - **Allowed actions**:
    - Improve or add documentation and inline comments where intent is non‑obvious.
    - Propose high‑level designs for new providers or lookup services.
  - **Restrictions**:
    - Do not change PHP service or repository logic.
    - Do not modify migrations or provider wiring.

- **PHP application agent**
  - **Purpose**: Implement and refactor backend logic within the existing architecture.
  - **Primary files**: `lib/Service/*`, `lib/Db/*`, `lib/Provider/*`, `lib/Controller/*`, `lib/AppInfo/Application.php`.
  - **Allowed actions**:
    - Implement new providers that follow `IDomainProviderRepository`.
    - Implement new lookup services that follow `ILookupService`.
    - Add or adjust service methods in `DomainService` without breaking documented behavior.
    - Add tests under `tests/` for new behavior.
  - **Restrictions**:
    - Do not change public method signatures on interfaces without clearly documenting the impact.
    - Do not remove existing fields from DB entities without an explicit migration and maintainer approval.
    - Do not introduce hard dependencies on specific provider APIs beyond what is configured in admin settings.

- **Migration & schema agent (advanced / opt‑in)**
  - **Purpose**: Safely evolve the database schema via migrations.
  - **Primary files**: `appinfo/Migrations/*.php`, `lib/Db/*`.
  - **Allowed actions**:
    - Add new migrations that are idempotent and align with ownCloud migration patterns.
    - Update repositories to work with the new schema.
  - **Restrictions**:
    - Only run when explicitly requested by a maintainer.
    - Must describe up/down behavior, compatibility concerns, and test steps in the PR description.
    - Avoid destructive schema operations unless the user has acknowledged the risk.

- **Frontend & UX agent**
  - **Purpose**: Improve templates, JS, and CSS while preserving behavior.
  - **Primary files**: `templates/*.php`, `js/*.js`, `css/style.css`.
  - **Allowed actions**:
    - Refine UI to better match ownCloud 10 look and feel.
    - Improve usability and accessibility without changing server‑side behavior.
  - **Restrictions**:
    - Do not introduce heavy client‑side frameworks or build steps.
    - Do not change server routes or API contracts.

### 3. Interaction model and prompts

When a human collaborates with an agent on this repo, they should:

- **Provide context up front**:
  - Mention whether the task is backend, schema, or UI focused.
  - Link or open the relevant files (e.g., `DomainService`, specific provider, migration).
  - State whether behavior is allowed to change, or if the change must be strictly behavior‑preserving.
- **Ask for plans before large changes**:
  - For refactors that touch multiple files, the agent should first propose a short plan and get confirmation before editing.
  - Plans should clearly call out any changes to:
    - DB schema
    - interfaces in `Db/` or `Provider/`
    - public controller endpoints

### 4. Guardrails for high‑risk areas

- **Database and migrations**:
  - Agents must not auto‑generate or run migration commands; they may only edit migration PHP files when requested.
  - Any schema change must be accompanied by:
    - A description of expected impact on existing data.
    - Guidance for operators (e.g. migration commands, potential downtime).
- **Access control and ownership logic**:
  - Changes to `getAllForUser`, ownership checks, and admin endpoints must preserve the principle that:
    - Regular users only see/manage their own domains.
    - Admin views (`unowned`, global lists) are clearly separated and guarded.
- **External providers and APIs**:
  - When adjusting provider logic, agents should:
    - Preserve existing configuration keys and wire them through admin settings.
    - Avoid tying the code to a single provider’s quirks where possible.

### 5. Review expectations for AI‑generated changes

- Any non‑trivial AI‑authored change should:
  - Be kept in a focused commit or PR with a clear description.
  - Include notes on which parts were AI‑generated and which were manually refined.
  - Include or update tests where behavior changes.
- Human reviewers should:
  - Validate that public contracts (routes, interfaces, schema) are not unintentionally broken.
  - Pay special attention to error handling, null handling, and edge cases.

### 6. Extending these rules

- If recurring patterns or constraints emerge (e.g. provider‑specific rules, TypeScript usage in future tooling), maintainers should:
  - Add dedicated rule files under `.cursor/rules/` with narrower scopes.
  - Keep this `AGENTS.md` as the high‑level, human‑readable overview of how AI should collaborate on this project.

