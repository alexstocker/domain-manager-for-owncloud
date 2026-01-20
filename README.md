# Domain Manager for ownCloud 10

The Domain Manager is a powerful ownCloud application designed to centralize the management of your domains across multiple providers.

## Features

- **Hybrid Storage Architecture**: Store your domain list locally in ownCloud or sync it with a dedicated remote DMS.
- **Multi-Provider Support**: Seamlessly manage domains from different sources:
    - **Cloudflare**: Manage DNS zones via API.
    - **ISPConfig**: Integrate with your hosting control panel.
    - **Robot API (Webtropia/WIIT/Hetzner)**: Connect to provider-specific automation interfaces.
- **RDAP Integration**:
    - Automatic fetching of domain expiration dates.
    - Automatic provider identification based on nameserver analysis.
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
- **Provider Credentials**: Set global API tokens and URLs for Cloudflare, ISPConfig, and Robot API.
- **RDAP Lookups**: Enable or disable automatic data enrichment and provider guessing.

## Development

The app uses a Repository-based architecture, making it easy to add new providers by implementing the `IDomainRepository` interface.

### Dependencies
- ownCloud 10.x
- PHP 7.0 - 8.0

## License
MIT
