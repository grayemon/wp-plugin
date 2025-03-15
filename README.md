# 🔐 Chatwoot Plugin for WordPress — Secure Identity Validation Edition

Easily embed the [Chatwoot](https://www.chatwoot.com) live-chat widget on your WordPress site — with **optional HMAC-based user identity validation** for authenticated support experiences.

[![WordPress Tested](https://img.shields.io/badge/WordPress-6.4-green.svg)](https://wordpress.org/plugins/)
[![PHP](https://img.shields.io/badge/PHP-%3E=7.2-blue)](https://www.php.net/)
[![License: GPLv2](https://img.shields.io/badge/license-GPLv2-blue.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

---

## 🎯 Features

- ✅ Embed Chatwoot widget via WP settings
- 🌍 Set language, position, style, launcher text
- 🔐 Secure identity validation with `setUser()` and `identifier_hash`
- 🔑 AES-256 encrypted HMAC token storage
- 🧠 Guest users get unique fallback names (e.g. `Guest a8b14f12`)
- 🔎 DevTools debugging with `window.ChatwootDebug = true;`
- 🧪 Built-in tools:
  - HMAC Validator
  - Key Generator (base64-ready)
- ⚙️ Multi-site compatible

---

## 📦 Installation

1. Clone or download this repo  
2. Upload to `/wp-content/plugins/chatwoot-secure`  
3. Activate in **Plugins > Installed Plugins**
4. Go to **Settings > Chatwoot Settings**
5. Fill in the following:

| Field | Example |
|-------|---------|
| Website Token | `abc123xyz456` |
| Installation URL | `https://app.chatwoot.com` |
| Web Widget HMAC Token | (paste your token here) |

---

## 🔐 Identity Validation

To verify users in Chatwoot, we pass:

```js
window.$chatwoot.setUser('<identifier>', {
  name: 'John Doe',
  email: 'john@example.com',
  identifier_hash: '<server-side-hmac>'
});
```
### ✅ Who gets identified?

-   **Logged-in users:** WP user ID + email/name + `identifier_hash`
    
-   **Guests:** Anonymous UUID + fallback name like `Guest 7c92a9b1`
    

### ✅ HMAC: How it works

1.  The plugin uses `hash_hmac('sha256', $visitor_id, $token)`
    
2.  The token is encrypted and stored in WP options
    
3.  You inject `identifier_hash` only via PHP — **never exposed**
---
## 🛠 Setup Encryption Keys

Edit `wp-config.php` and add:

```php
define('CHATWOOT_ENCRYPTION_KEY', 'base64:...');
define('CHATWOOT_ENCRYPTION_IV', 'base64:...');
``` 

You can generate secure values via:

-   **Tools → Generate Chatwoot Key** (admin-only page)
    
-   Or run:
```php
base64_encode(random_bytes(32)); // key  
base64_encode(random_bytes(16)); // IV
```
---
## 🧪 Test Identity Validation

Enable admin test page under:
**Tools → Test Chatwoot HMAC**
Also test manually via browser:
```js
window.ChatwootDebug = true;
``` 
🔍 DevTools Console will show:
```yaml
🔐  Chatwoot  Identity  Debug  
🆔  Identifier:  42  
🔑  HMAC Hash:  c0ffee3...  
👤  Name:  Guest  7c92a9b1
``` 
And Chatwoot’s API should respond with:
```json
"hmac_verified":  true
```
---
## 💡 Disable Debug Tools in Production
In `wp-config.php`:
```php
define('DISABLE_CHATWOOT_HMAC_TEST', true);
define('DISABLE_CHATWOOT_KEY_GENERATOR', true);
```
---
## 🖼 Screenshots

Admin Settings

|Frontend| Chatwoot|
|---|---|
|pic1|pic2|
---
## 🗂 Directory Structure

``` pgsql
chatwoot-secure/
├── chatwoot-plugin.php
├── js/
│   └── chatwoot.js
├── includes/
│   ├── crypto.php
│   ├── identity.php
│   ├── admin-key-generator.php
│   └── admin-hmac-tester.php
├── admin.css
└── readme.txt
```
---
## 📄 License

This plugin is licensed under the [GNU GPL v2 or later](https://www.gnu.org/licenses/gpl-2.0.html).

---
## 🙌 Credits

-   🧠 Original plugin by [antpb](https://profiles.wordpress.org/antpb)
    
-   🔒 HMAC support & crypto by [you / your org]
    
-   💬 Chatwoot team for the open-source chat platform
---
## 🌐 Links

-   🔗 [Chatwoot Documentation](https://www.chatwoot.com/help-center)
    
-   💻 [Chatwoot GitHub](https://github.com/chatwoot/chatwoot)
    
-   ⚙️ [Plugin Repository](https://github.com/grayemon/wp-plugin)
---
## 🚀 Roadmap

-   Support multi-identity context switching
    
-   REST API hook for external HMAC tools
    
-   WooCommerce & membership integrations
---
