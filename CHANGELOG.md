# Changelog

All notable changes to the Domain Manager app will be documented in this file.

## 0.0.2 (UNRELEASED)

### Added
- **Extensible Lookup Service Architecture**: Implemented `ILookupService`, `RdapLookupService`, `CctldLookupService`, and `LookupServiceFacade` using a Chain of Responsibility pattern for intelligent service selection based on domain TLD.
- **ccTLD-Specific Lookup Service**: Added support for `.at` and `.de` domains with dedicated RDAP endpoints.
- **Easyname Domain Provider (Placeholder)**: Introduced framework for easyname.eu integration, including `EasynameDomainRepository`, corresponding settings in `SettingsController`, and admin UI in `templates/admin.php`.
- **Configuration Options**: Added `cctld_lookup_enabled` and `easyname_enabled` settings to the admin panel.
- **Domain Details API**: Added a new backend endpoint `GET /api/domains/{id}` (`PageController::getDomain`) and repository method `findById(int $id)` to fetch a single domain by id with provider metadata.
- **Right-side Details Drawer UI**: Implemented a right-side drawer in `templates/main.php` with a details view, configuration display and lookup events; wired in `js/domainmanager.js` to open on demand.

### Changed
- **Refactored RDAP Logic**: Moved RDAP lookup functionality from `RdapDomainRepository` to `RdapLookupService` for better separation of concerns.
- **Automated Lookup Selection**: `PageController::lookup` now automatically selects the appropriate lookup service via `LookupServiceFacade`, removing the need for a `service` parameter in the API call.
- **UI: Details Trigger & Actions**: Replaced the inline 'Details' button in the domain table with the ownCloud-style icon (`<span class="icon icon-more">`) and moved the Update/Delete actions from the table row into the right-side drawer actions panel.
- **Frontend Changes**: Updated `js/domainmanager.js` to support the drawer (details fetch, frontend-initiated lookup, update/delete via drawer), adjusted `css/style.css` for the drawer styling, and updated `templates/main.php` markup.
- **Service API**: Added `DomainService::findById(int $id)` to expose repository lookup by id to controllers.
- **Composer metadata**: Added `ext-json` and `ext-pdo` to `domain_manager/composer.json` to document required PHP extensions used by the DB and JSON code paths.

### Fixed
- **Bugfix: Drawer variable initialization**: Fixed a runtime ReferenceError caused by drawer DOM variables being referenced before initialization by moving drawer DOM references and open/close helpers into the module initialization (`js/domainmanager.js`).
- **Security Enhancement**: Ensured `PageController::lookup` endpoint is properly secured with `@NoAdminRequired` (removing `@PublicPage`) to prevent unauthorized access and incorrect `302` redirects.

## 0.0.1 (UNRELEASED)

### Added
- **Multi-Provider Support**: Integration for Cloudflare, ISPConfig, and Robot API (Webtropia/WIIT/Hetzner).
- **Admin Settings Page**: Dedicated interface in ownCloud admin panel for global configurations.
- **RDAP Enrichment**:
    - Display of domain expiration dates.
    - Automatic provider identification based on nameservers.
- **Improved UI**: Native ownCloud 10 look and feel, styled like the Files app.
- **Per-Domain Configuration**: Support for domain-specific API tokens and settings.
- **Duplicate Prevention**: Backend and frontend checks for existing domains.
- Initial release with basic CRUD operations.
- Local database storage for domains.
- Simple domain validation.

### Changed
- **Hybrid Architecture**: Decoupled storage backend (local/remote) from domain providers.
- **Repository Pattern**: Refactored data layer for better extensibility.
- **AJAX Error Handling**: Centralized error notifications and improved server-side validation.
