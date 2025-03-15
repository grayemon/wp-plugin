# Chatwoot Plugin #

**Contributors:** [antpb](https://profiles.wordpress.org/antpb), [chatwootengineering](https://profiles.wordpress.org/chatwootengineering)  
**Tags:** chat, live-chat, support, chat-support, customer-support, identity, hmac, secure  
**Requires at least:** 5.2  
**Tested up to:** 6.4  
**Requires PHP:** 7.2  
**Stable tag:** 0.3.0  
**License:** GPLv2 or later  
**License URI:** https://www.gnu.org/licenses/gpl-2.0.html  

## Description ##

The Chatwoot Plugin for WordPress lets you embed a customizable live chat widget powered by [Chatwoot](https://www.chatwoot.com).

### 🔐 Now with optional HMAC-based identity validation

This plugin supports both **anonymous** and **authenticated** visitor tracking using the Chatwoot SDK’s `setUser()` method — with server-side identity hashes for secure user verification.

### ✅ Core Features

- Easily embed the Chatwoot widget on every page
- Set widget locale (language), position (left/right), design type
- Optionally show a custom launcher text
- Identity validation for logged-in users using HMAC
- Secure fallback for guest users (`Guest abc123`)
- Encrypted HMAC token storage using AES-256-CBC
- Built-in dev tools: HMAC tester, key generator (admin-only)

---

## Installation ##

1. Download the plugin as a ZIP.
2. Upload it to your site via **Plugins > Add New > Upload Plugin**.
3. Activate the plugin.
4. Go to **Settings > Chatwoot Settings**.
5. Fill out:
   - **Chatwoot Website Token**
   - **Installation URL**
   - (Optional) **Web Widget HMAC Token**

---

## Configuration ##

### 🔐 Enable secure identity validation (optional)

If you wish to secure sessions via HMAC:

1. Generate an HMAC token in your Chatwoot inbox.
2. Go to **Settings > Chatwoot Settings** and paste it in the “Web Widget HMAC Token” field.
3. For encrypted storage, define the following constants in `wp-config.php`:
 
define('CHATWOOT_ENCRYPTION_KEY', 'base64:...'); 
define('CHATWOOT_ENCRYPTION_IV', 'base64:...');

4. Save settings. The token will be encrypted at rest.

---

## Debugging ##

To test identity injection:

1. Open your browser’s **Developer Tools → Console**
2. Enter: 

window.ChatwootDebug = true;

3. Reload the page. You’ll see identity logs:

🔐 Chatwoot Identity Debug 
🆔 Identifier: 42 
🔑 HMAC Hash: abcd123... 
👤 Name: Guest 1a2b3c


Check your **Network tab** for the Chatwoot SDK API response:

"hmac_verified": true` confirms identity is validated.

---

## Frequently Asked Questions ##

### 1. Do I need a Chatwoot account?

Yes. You need an account on:
- Chatwoot Cloud (`https://app.chatwoot.com`) **OR**
- A self-hosted Chatwoot instance

Set up a **website inbox**, then copy its Website Token into the plugin settings.

### 2. Can I support guests and logged-in users?

Yes:
- Logged-in users are identified by their WordPress ID, name, and email.
- Guests receive a secure anonymous ID stored in a cookie (`cw_vid`).

### 3. What happens if I skip HMAC setup?

Chatwoot will still work, but sessions won’t be identity-verified.  
If you don’t need secure verification, you can leave the HMAC token blank.

### 4. Can I disable admin tools in production?

Yes. Add this to your `wp-config.php`:

define('DISABLE_CHATWOOT_HMAC_TEST', true); define('DISABLE_CHATWOOT_KEY_GENERATOR', true);

This removes the tools from the WordPress dashboard.

---

## Screenshots ##

1. The Chatwoot widget on the front-end.
2. The plugin settings panel.
3. Debug output in browser console using `window.ChatwootDebug = true`.

---

## Changelog ##

### 0.3.0 ###
- 🔐 Added support for secure identity validation using HMAC
- 🧊 AES-256 encryption for the HMAC token
- 👤 Guest fallback name now unique (e.g., “Guest 1a2b3c”)
- 🧪 Added dev tools: HMAC Tester and Key Generator
- 🧼 Fixed localization warnings (`wp_localize_script`)
- ✅ Cookies now set securely in `init` to avoid header issues

### 0.2.0 ###
- Added UI options for widget customization
- Locale, position, and launcher text support
- Updated admin UI styles

### 0.1.0 ###
- Initial release with basic Chatwoot integration

---

## Upgrade Notice ##

### 0.3.0 ###
If you enable identity validation, define the following constants in `wp-config.php`:

define('CHATWOOT_ENCRYPTION_KEY', 'base64:...'); define('CHATWOOT_ENCRYPTION_IV', 'base64:...');

You should also re-save your plugin settings after upgrade to apply encryption to the token.