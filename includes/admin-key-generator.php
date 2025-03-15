<?php

if (!defined('ABSPATH')) exit; // 🔐 Exit if accessed directly

class Chatwoot_Key_Generator_Admin {

    public function __construct() {
        if (get_option('chatwootEnableKeyGenerator') !== '1') {
            return; // 👈 early exit
        }

        add_action('admin_menu', [$this, 'register_tool_page']);
    }

    public function register_tool_page() {
        add_management_page(
            'Chatwoot Key Generator',
            'Chatwoot Key Generator',
            'manage_options',
            'chatwoot-key-generator',
            [$this, 'render_page']
        );
    }

    public function render_page() {
        if (!current_user_can('manage_options')) {
            wp_die('🚫 Access denied');
        }

        $key = '';
        $iv = '';
        $error = '';

        if (isset($_POST['generate_keys'])) {
            try {
                $key = 'base64:' . base64_encode(random_bytes(32)); // AES-256
                $iv  = 'base64:' . base64_encode(random_bytes(16)); // 16-byte IV
            } catch (Exception $e) {
                $error = '❌ Error generating keys: ' . esc_html($e->getMessage());
            }
        }
        ?>
        <div class="wrap chatwoot-key-generator">
            <h1>🔐 Chatwoot AES Key Generator</h1>

            <p>This tool will generate a secure AES-256 encryption key and IV for use with <code>chatwoot_encrypt()</code> / <code>chatwoot_decrypt()</code>.</p>
            <p><strong>⚠️ IMPORTANT:</strong> Only generate keys ONCE and paste them into your <code>wp-config.php</code>. If you lose them, encrypted data will become unreadable.</p>

            <?php if (!empty($error)) : ?>
                <div class="notice notice-error"><p><?php echo $error; ?></p></div>
            <?php endif; ?>

            <form method="post">
                <?php submit_button('Generate New Key + IV', 'primary', 'generate_keys'); ?>
            </form>

            <?php if (!empty($key) && !empty($iv)) : ?>
                <hr>
                <h2>✅ Generated Key + IV</h2>
                <table class="widefat striped">
                    <tr>
                        <th>AES-256 Key (32 bytes)</th>
                        <td><code id="cw-key"><?php echo esc_html($key); ?></code></td>
                    </tr>
                    <tr>
                        <th>Initialization Vector (16 bytes)</th>
                        <td><code id="cw-iv"><?php echo esc_html($iv); ?></code></td>
                    </tr>
                </table>

                <h3>📄 Paste into <code>wp-config.php</code>:</h3>
<pre><code>define('CHATWOOT_ENCRYPTION_KEY', '<?php echo esc_js($key); ?>');
 define('CHATWOOT_ENCRYPTION_IV', '<?php echo esc_js($iv); ?>');</code></pre>

                <p>
                    <button class="button button-secondary" type="button" onclick="copyToClipboard('cw-key')">📋 Copy Key</button>
                    <button class="button button-secondary" type="button" onclick="copyToClipboard('cw-iv')">📋 Copy IV</button>
                </p>

                <script>
                    function copyToClipboard(id) {
                        const el = document.getElementById(id);
                        if (!el) return;

                        const text = el.textContent || el.innerText;

                        if (navigator.clipboard && window.isSecureContext) {
                            navigator.clipboard.writeText(text).then(function() {
                            alert("Copied to clipboard!");
                            }, function(err) {
                            alert("Failed to copy: " + err);
                            });
                        } else {
                            // Fallback for non-secure context
                            const textarea = document.createElement("textarea");
                            textarea.value = text;
                            textarea.style.position = "fixed";  // Prevent scrolling to bottom
                            document.body.appendChild(textarea);
                            textarea.focus();
                            textarea.select();

                            try {
                            const successful = document.execCommand("copy");
                            if (successful) {
                                alert("Copied to clipboard!");
                            } else {
                                alert("Failed to copy with fallback.");
                            }
                            } catch (err) {
                            alert("Fallback failed: " + err);
                            }

                            document.body.removeChild(textarea);
                        }
                    }
                </script>

            <?php endif; ?>
        </div>
        <?php
    }
}

//new Chatwoot_Key_Generator_Admin();

// 🧠 Load when plugin is initialized (instead of global scope)
add_action('plugins_loaded', function () {
    new Chatwoot_Key_Generator_Admin();
});