# Chatwoot Plugin #

**Contributors:** [antpb](https://profiles.wordpress.org/antpb), [chatwootengineering](https://profiles.wordpress.org/chatwootengineering)  
**Tags:** chat, live-chat, support, chat-support, customer-support, customer-engagement  
**Requires at least:** 5.2  
**Tested up to:** 6.4  
**Requires PHP:** 7.2  
**Stable tag:** 0.3.0  
**License:** GPLv2 or later  
**License URI:** https://www.gnu.org/licenses/gpl-2.0.html  

## Description ##

This plugin allows you to add a Chatwoot live-chat widget on every page of your WordPress website. It also supports advanced customization options including:

- Widget locale (language)
- Widget position (left or right)
- Design type (standard or expanded)
- Optional launcher text
- 🔐 **(New)** Secure identity validation via HMAC token for authenticated users (optional)

This allows you to integrate Chatwoot’s live support system with full user verification using the secure Chatwoot SDK.

## Installation ##

1. Download the plugin as a zip file.
2. Upload it via the **Plugins > Add New > Upload Plugin** screen in your WordPress admin.
3. Activate the plugin.
4. Go to **Settings > Chatwoot Settings** to configure your website token and URL.

## Frequently Asked Questions ##

### 1. Do I need an account at Chatwoot to use this plugin?

Yes. You need an account either on a **self-hosted Chatwoot instance** or on **Chatwoot Cloud** (`https://app.chatwoot.com`).  
You must create a **website inbox** and use the generated website token in the plugin settings.

### 2. Can I use the live-chat in different languages?

Yes. The widget supports multiple languages. You can select the locale in the settings panel.

### 3. Does this plugin support secure identity validation?

Yes (as of version 0.3.0). If you define a secure HMAC token, the plugin will inject identity validation via `window.$chatwoot.setUser(...)`, ensuring verified user sessions.

### 4. Can I disable the built-in tools for production?

Yes. Use these constants in your `wp-config.php`:

define('DISABLE_CHATWOOT_HMAC_TEST', true);
define('DISABLE_CHATWOOT_KEY_GENERATOR', true);

This will hide the test/debug admin tools from WordPress Dashboard.

---

## Screenshots ##

1. Chatwoot widget on the front-end
2. Admin panel settings interface

---

## Changelog ##

### 0.3.0 ###
- 🔐 Added optional identity validation using HMAC tokens.
- 🧼 Secured token storage with AES-256 encryption.
- 🛠 Added admin tool: HMAC tester.
- 🛠 Added admin tool: key generator for `wp-config.php`.

### 0.2.0 ###
- Added options for customizing widget locale, position, and launcher text.
- Updated admin settings styles.

### 0.1.0 ###
- Initial release with simple embed via saved options.

---

## Upgrade Notice ##

### 0.3.0 ###
If using identity validation, define `CHATWOOT_ENCRYPTION_KEY` and `CHATWOOT_ENCRYPTION_IV` in `wp-config.php`.  
Existing settings will continue to work without changes.
