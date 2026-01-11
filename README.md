# Coreline

Essential hardening and cleanup for every WordPress site.

## Description

Coreline is a lightweight WordPress security and optimization plugin that provides essential hardening features for your WordPress installation.

## Features

- **Emoji Script Removal**: Removes WordPress emoji detection scripts to improve performance
- **WordPress Version Hiding**: Removes WordPress version numbers from HTML and RSS feeds for better security
- **Custom Login URL**: Changes wp-login.php to a custom URL (e.g., `/secure-login/`) to prevent automated brute-force attacks.
- **Hotlink Protection**: Prevents other websites from hotlinking your images (works on both Apache and Nginx)
- **Disable Pingbacks & Trackbacks**: Disables XML-RPC pingbacks and trackbacks for improved security

## Requirements

- PHP 7.4 or higher
- WordPress 5.0 or higher

## Installation

1. Download the plugin
2. Upload the `coreline` folder to `/wp-content/plugins/`
3. Run `composer install --no-dev` in the plugin directory
4. Activate the plugin through the 'Plugins' menu in WordPress

## Development

### Install Dependencies

```bash
composer install
```


### Code Quality Checks

```bash
# WordPress Coding Standards Check
composer phpcs

# Auto-fix coding standards issues
composer phpcs:fix

# Static Analysis with PHPStan
composer phpstan

# Run all quality checks
composer lint
```

### Coding Standards

Coreline follows:
- ✅ **WordPress Coding Standards** (WPCS 3.0)
- ✅ **WordPress VIP Go Standards** (Enterprise-grade)
- ✅ **PHP Compatibility** (7.4+)
- ✅ **PHPStan Level 8** (Strictest static analysis)
- ✅ **PSR-12** (Where compatible with WordPress)

## Architecture

Coreline follows SOLID principles and uses dependency injection for maximum testability:

- **PSR-4 Autoloading**: Proper namespace structure
- **WordPress Coding Standards**: WPCS 3.0 + VIP Go standards
- **Dependency Injection**: No dependency container, pure constructor injection
- **Interface-based Design**: All features implement `FeatureInterface`
- **Open/Closed Principle**: Easy to extend with new features
- **Type Safety**: Strict types, PHPStan level 8

### Project Structure

```
coreline/
├── src/
│   ├── Abstracts/
│   │   └── AbstractFeature.php
│   ├── Contracts/
│   │   └── FeatureInterface.php
│   ├── Features/
│   │   ├── DisableEmojis.php
│   │   ├── DisablePingbacks.php
│   │   ├── HideWordPressVersion.php
│   │   ├── HotlinkProtection.php
│   │   └── ProtectWpLogin.php
│   └── Plugin.php
├── composer.json
└── coreline.php
```

### Filtering Features

```php
add_filter('coreline_features', function($features) {
    $features[] = new MyCustomFeature();
    return $features;
});
```

## Security Features Explained

### Custom Login URL
- Changes `/wp-login.php` to a custom URL (e.g., `/secure-login/`)
- Blocks direct access to `/wp-login.php` (returns 404)
- Blocks `/wp-admin/` access for non-authenticated users
- Works on all servers (Apache, Nginx, LiteSpeed)

### Hotlink Protection
- Prevents bandwidth theft from image hotlinking
- Allows search engines (Google, Bing, Yahoo, DuckDuckGo)
- Works on both Apache and Nginx servers
- PHP-based implementation (no .htaccess required)

### Disable Pingbacks
- Completely disables XML-RPC pingback functionality
- Removes X-Pingback header
- Prevents pingback DDoS attacks
