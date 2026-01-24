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

## Installation

1. Place the `domain_manager` folder in your ownCloud `apps/` directory.
2. Enable the app via the ownCloud web interface or using `occ`:
   ```bash
   php occ app:enable domain_manager
   ```

## Configuration

Navigate to **Settings -> Admin -> Additional** to configure:

- **Default Backend**: Choose between `Local` (ownCloud database) or `Remote` storage.
- **Provider Credentials**: Set global API tokens and URLs for various providers.
- **Lookup Services**: Enable or disable generic and ccTLD-specific lookups.

## Development

The application is built on a modular and extensible architecture, making it easy to add new functionality.

### Adding a New Domain Provider

1.  **Create a Repository**: Create a new class in `lib/Db/` that implements the `IDomainRepository` interface. This class will contain the logic to communicate with the provider's API.
2.  **Add Settings**: Update `lib/Controller/SettingsController.php` and `templates/admin.php` to include any necessary configuration fields for your new provider (e.g., API keys, URLs).
3.  **Register the Provider**: In `lib/AppInfo/Application.php`, register your new repository with the `DomainProviderManager`. This is typically done conditionally based on an "enabled" setting.

### Adding a New Lookup Service

The app uses a **Chain of Responsibility** pattern to find the best service for a given domain.

1.  **Create a Service**: Create a new class in `lib/Service/Lookup/` that implements the `ILookupService` interface.
2.  **Implement `supports()`**: This method should return `true` if your service can handle the given domain (e.g., by checking its TLD).
3.  **Implement `lookup()`**: This method should perform the actual lookup and return the data.
4.  **Register the Service**: In `lib/AppInfo/Application.php`, register your new service with the `LookupServiceFacade`. Be sure to register specific services *before* generic ones to ensure they are prioritized.

### Dependencies
- ownCloud 10.x
- PHP 7.0 - 8.0

## License
MIT
