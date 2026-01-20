# Changelog

All notable changes to the Domain Manager app will be documented in this file.

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

