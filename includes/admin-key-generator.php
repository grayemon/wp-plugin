<?php
if (!defined('ABSPATH')) exit;

class Chatwoot_Key_Generator_Admin {

    public function __construct() {
        if (
            defined('DISABLE_CHATWOOT_KEY_GENERATOR') &&
            DISABLE_CHATWOOT_KEY_GENERATOR === true
        ) {
            return;
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

        if (isset($_POST['generate_keys'])) {
            try {
                $key = 'base64:' . base64_encode(random_bytes(32)); // AES-256
                $iv  = 'base64:' . base64_encode(random_bytes(16)); // 16-byte IV
            } catch (Exception $e) {
                $error = 'Error generating keys: ' . $e->getMessage();
            }
        }
        ?>
        <div class="wrap">
            <h1>🔐 Chatwoot AES Key Generator</h1>

            <p>This tool will generate a secure AES-256 encryption key and IV for use with <code>chatwoot_encrypt()</code> / <code>chatwoot_decrypt()</code>.</p>
            <p><strong>⚠️ IMPORTANT:</strong> Only generate keys ONCE and paste them into your <code>wp-config.php</code>. If you lose them, encrypted data will become unreadable.</p>

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

                <button class="button button-secondary" onclick="navigator.clipboard.writeText(document.getElementById('cw-key').innerText)">📋 Copy Key</button>
                <button class="button button-secondary" onclick="navigator.clipboard.writeText(document.getElementById('cw-iv').innerText)">📋 Copy IV</button>
            <?php endif; ?>
        </div>
        <?php
    }
}

new Chatwoot_Key_Generator_Admin();
