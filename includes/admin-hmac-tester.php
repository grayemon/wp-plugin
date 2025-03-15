<?php

if (!defined('ABSPATH')) exit;
//if (get_option('chatwootEnableHmacTester') !== '1') return; // 👈 early exit

class Chatwoot_HMAC_Tester_Admin {

    public function __construct() {
        if (get_option('chatwootEnableHmacTester') !== '1') {
            return; // 👈 early exit
        }

        add_action('admin_menu', [$this, 'register_tool_page']);
    }

    public function register_tool_page() {
        add_management_page(
            'Chatwoot HMAC Tester',
            'Chatwoot HMAC Test',
            'manage_options',
            'chatwoot-hmac-test',
            [$this, 'render_page']
        );
    }

    public function render_page() {
        if (!current_user_can('manage_options')) {
            wp_die('🚫 Access Denied');
        }

        require_once plugin_dir_path(__FILE__) . 'crypto.php';

        // Handle test input
        $visitor_id = isset($_POST['visitor_id']) ? sanitize_text_field($_POST['visitor_id']) : '';
        $hash = '';
        $decrypted_token = '';
        $error = '';

        if (!empty($visitor_id)) {
            $encrypted_token = get_option('chatwootWebWidgetHmacToken');

            if (!$encrypted_token) {
                $error = '❌ Encrypted HMAC token not found in settings.';
            } else {
                $decrypted_token = chatwoot_decrypt($encrypted_token);
                if (!$decrypted_token) {
                    $error = '❌ Failed to decrypt HMAC token. Check your wp-config.php encryption key and IV.';
                } else {
                    $hash = hash_hmac('sha256', $visitor_id, $decrypted_token);
                }
            }
        }

        $is_dev = defined('WP_DEBUG') && WP_DEBUG === true;

        ?>
        <div class="wrap">
            <h1>🔐 Chatwoot HMAC Tester</h1>

            <?php if ($error): ?>
                <div class="notice notice-error"><p><?php echo esc_html($error); ?></p></div>
            <?php endif; ?>

            <form method="POST">
                <table class="form-table">
                    <tr>
                        <th><label for="visitor_id">Visitor Identifier</label></th>
                        <td>
                            <input type="text" id="visitor_id" name="visitor_id"
                                   class="regular-text" required
                                   value="<?php echo esc_attr($visitor_id); ?>" />
                            <p class="description">Enter a UUID or WordPress user ID</p>
                        </td>
                    </tr>
                </table>

                <?php submit_button('Generate HMAC'); ?>
            </form>

            <?php if ($visitor_id && $hash): ?>
                <h2>✅ HMAC Result</h2>
                <table class="widefat striped">
                    <tbody>
                        <tr>
                            <th>Visitor ID</th>
                            <td><code><?php echo esc_html($visitor_id); ?></code></td>
                        </tr>
                        <tr>
                            <th>Identifier Hash</th>
                            <td><code><?php echo esc_html($hash); ?></code></td>
                        </tr>

                        <?php if ($is_dev): ?>
                            <tr>
                                <th>Decrypted HMAC Token</th>
                                <td><code><?php echo esc_html($decrypted_token); ?></code></td>
                            </tr>
                        <?php else: ?>
                            <tr>
                                <th>Decrypted HMAC Token</th>
                                <td><em>🔒 Hidden in production (WP_DEBUG is false)</em></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <h3>💬 Sample JavaScript</h3>
                <pre>
<code>
window.$chatwoot.setUser('<?php echo esc_js($visitor_id); ?>', {
identifier_hash: '<?php echo esc_js($hash); ?>'
});
</code>
                </pre>
            <?php endif; ?>
        </div>
        <?php
    }
}

new Chatwoot_HMAC_Tester_Admin();
