# Domain Manager for ownCloud 10

The Domain Manager is a powerful ownCloud application designed to centralize the management of your domains across multiple providers.

## Features

- **Hybrid Storage Architecture**: Store your domain list locally in ownCloud or sync it with a dedicated remote DMS.
- **Multi-Provider Support**: Seamlessly manage domains from different sources:
    - **Cloudflare**: Manage DNS zones via API.
    - **ISPConfig**: Integrate with your hosting control panel.
    - **Robot API (Webtropia/WIIT/Hetzner)**: Connect to provider-specific automation interfaces.
    - **Easyname**: A placeholder for easyname.eu integration (requires implementation).
- **Extensible Lookup Services**:
    - **RDAP Integration**: Automatic fetching of domain data and provider identification based on nameserver analysis.
    - **ccTLD-Specific Lookups**: Support for direct lookups for `.at` and `.de` domains.
- **Modern UI**: Styled to match the ownCloud 10 Files app for a native experience.
- **Admin Configuration**: Manage global API tokens and provider settings directly from the ownCloud admin panel.
- **Per-user domain ownership**: Domains may be owned by a specific ownCloud user (nullable `owner` column) — this enables user-scoped listings and owner-only edit/delete operations.
- **Admin: Unowned Domains Panel**: Admins can list domains that have no owner (`owner = NULL`) and either assign them to users or delete them.

## Installation

1. Place the `domain_manager` folder in your ownCloud `apps/` directory.
2. Ensure PHP extensions required by the app are available (`json`, `pdo`). The app's `composer.json` declares `ext-json` and `ext-pdo`.
3. Enable the app via the ownCloud web interface or using `occ`:
   ```bash
   php occ app:enable domain_manager
   ```

## Migration / DB changes

A migration was added to create the `owner` column and index. Because this is still a pre-beta development version, no backfill is performed automatically.

Run migrations inside the ownCloud container (example using Docker):

```bash
# optional: enable maintenance mode
docker exec -u www-data owncloud_server php /var/www/owncloud/occ maintenance:mode --on
# run migrations for the app
docker exec -u www-data owncloud_server php /var/www/owncloud/occ migrations:migrate --app domain_manager
# optional: disable maintenance mode
docker exec -u www-data owncloud_server php /var/www/owncloud/occ maintenance:mode --off
```

Note: container names vary by setup — replace `owncloud_server` with your container name.

## Configuration

Navigate to **Settings -> Admin -> Additional** to configure:

- **Default Backend**: Choose between `Local` (ownCloud database) or `Remote` storage.
- **Provider Credentials**: Set global API tokens and URLs for various providers.
- **Lookup Services**: Enable or disable generic and ccTLD-specific lookups.
- **Unowned Domains**: Admins can open the "Manage unowned domains" panel from the same admin page to claim or delete unassigned domains.

## Development

The application is built on a modular and extensible architecture, making it easy to add new functionality.

### Architecture overview
- `OCA\DomainManager\Db` contains storage-related classes and the primary `IDomainRepository` used for app persistence. This includes `DbDomainRepository` and migration logic.
- `OCA\DomainManager\Provider` contains provider connectors that implement `IDomainProviderRepository` (Cloudflare, RDAP, ISPConfig, Robot API, Easyname placeholder).
- `DomainProviderManager` wires storage and providers together; `DomainService` acts as the application service layer used by controllers.
- `LookupServiceFacade` implements a chain-of-responsibility to pick the best lookup service (rdap / cctld) for a domain.

### Adding a new domain provider
1. Create a provider class under `lib/Provider/` implementing `IDomainProviderRepository`.
2. Register the provider in `lib/AppInfo/Application.php` with `DomainProviderManager` and add admin settings if necessary.

### Adding a new lookup service
1. Implement `ILookupService` under `lib/Service/Lookup/` and register it with `LookupServiceFacade` in `AppInfo/Application.php`.

### Admin API: unowned domains
- GET `/apps/domain_manager/api/domains/unowned` (admin-only)
- POST `/apps/domain_manager/api/domains/{id}/assign` with `owner` body param (admin-only)

### Important development tips
- If you change interfaces (`IDomainRepository` or `IDomainProviderRepository`), update all implementations and the DI wiring in `lib/AppInfo/Application.php` to avoid signature mismatches.
- The codebase currently contains shims to ease the transition between provider and db interfaces. When refactoring further, update or remove these shims accordingly.

## Testing & Lint

- Use ownCloud `occ` commands for migrations and simple runtime checks.
- Use the project's static checks (`get_errors`) to catch signature/namespace mismatches early.

## Next steps and TODOs
- Validate that assigned owner UIDs exist before setting `owner` (IUserManager).
- Add audit logging for assign/delete actions.
- Replace confirm() with native ownCloud modal dialogs for a better UX in the admin UI.

## License

MIT
