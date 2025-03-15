<?php
/**
 * Plugin Name:     Chatwoot Plugin
 * Plugin URI:      https://www.chatwoot.com/
 * Description:     Chatwoot Plugin for WordPress. This plugin helps you to quickly integrate Chatwoot live-chat widget on Wordpress websites.
 * Author:          antpb
 * Author URI:      chatwoot.com
 * Text Domain:     chatwoot-plugin
 * Version:         0.3.1
 *
 * @package         chatwoot-plugin
 */

// Include crypto.php file. Place the file in your plugin's "includes" folder.
require_once plugin_dir_path(__FILE__) . 'includes/crypto.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin-hmac-tester.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin-key-generator.php';
require_once plugin_dir_path(__FILE__) . 'includes/identity.php';


add_action('admin_enqueue_scripts', 'admin_styles');
/**
 * Load Chatwoot Admin CSS.
 *
 * @since 0.1.0
 *
 * @return {void}.
 */
function admin_styles() {
  wp_enqueue_style('admin-styles', plugin_dir_url(__FILE__) . '/admin.css');
}

 add_action( 'wp_enqueue_scripts', 'chatwoot_assets' );
/**
 * Load Chatwoot Assets.
 *
 * @since 0.1.0
 *
 * @return {void}.
 */
function chatwoot_assets() {
    wp_enqueue_script( 'chatwoot-client', plugins_url( '/js/chatwoot.js' , __FILE__ ) );
}

add_action( 'wp_enqueue_scripts', 'chatwoot_load' );
/**
 * Initialize embed code options.
 *
 * @since 0.1.0
 *
 * @return {void}.
 */

function chatwoot_load() {
  $chatwoot_data = [
    'token' => get_option('chatwootSiteToken'),
    'url' => get_option('chatwootSiteURL'),
    'locale' => get_option('chatwootWidgetLocale'),
    'type' => get_option('chatwootWidgetType'),
    'position' => get_option('chatwootWidgetPosition'),
    'launcherTitle' => get_option('chatwootLauncherText'),
    'debug' => get_option('chatwootEnableDebugMode') === '1', // 👈 DEBUG FLAG
  ];

  // Pass all variables in a single localized JS object
  wp_localize_script('chatwoot-client', 'chatwootSettings', $chatwoot_data);
}

add_action('admin_menu', 'chatwoot_setup_menu');
/**
 * Set up Settings options page.
 *
 * @since 0.1.0
 *
 * @return {void}.
 */
function chatwoot_setup_menu(){
    add_options_page('Option', 'Chatwoot Settings', 'manage_options', 'chatwoot-plugin-options', 'chatwoot_options_page');
}

add_action( 'admin_init', 'chatwoot_register_settings' );
/**
 * Register Settings.
 *
 * @since 0.1.0
 *
 * @return {void}.
 */
function chatwoot_register_settings() {
  add_option('chatwootSiteToken', '');
  add_option('chatwootSiteURL', '');
  add_option('chatwootWidgetLocale', 'en');
  add_option('chatwootWidgetType', 'standard');
  add_option('chatwootWidgetPosition', 'right');
  add_option('chatwootLauncherText', '');
  add_option('chatwootWebWidgetHmacToken', ''); 

  register_setting('chatwoot-plugin-options', 'chatwootSiteToken' );
  register_setting('chatwoot-plugin-options', 'chatwootSiteURL');
  register_setting('chatwoot-plugin-options', 'chatwootWidgetLocale' );
  register_setting('chatwoot-plugin-options', 'chatwootWidgetType' );
  register_setting('chatwoot-plugin-options', 'chatwootWidgetPosition' );
  register_setting('chatwoot-plugin-options', 'chatwootLauncherText' );
  register_setting('chatwoot-plugin-options', 'chatwootWebWidgetHmacToken', [
    'type' => 'string',
    'sanitize_callback' => 'chatwoot_encrypt',
  ]);

  // Advanced settings options
  add_option('chatwootEnableHmacTester', '0');
  add_option('chatwootEnableKeyGenerator', '0');
  add_option('chatwootEnableDebugMode', '0');

  register_setting('chatwoot-plugin-options', 'chatwootEnableHmacTester');
  register_setting('chatwoot-plugin-options', 'chatwootEnableKeyGenerator');
  register_setting('chatwoot-plugin-options', 'chatwootEnableDebugMode');
}

add_action('wp_footer', 'chatwoot_inject_identity');

function chatwoot_inject_identity() {
  $payload = chatwoot_get_user_payload();

  if (!$payload || empty($payload['identifier']) || empty($payload['hash'])) return;
    echo '<script>window.chatwootIdentity = ' . json_encode($payload, JSON_UNESCAPED_SLASHES) . ';</script>';

  if (get_option('chatwootEnableDebugMode') === '1') {
    echo '<script>console.info("🛠 Chatwoot Debug Mode is ON");</script>';
  }
    
}

/**
 * Render page.
 *
 * @since 0.1.0
 *
 * @return {void}.
 */

function chatwoot_options_page() {
  ?>
  <div class="chatwoot-admin-settings">
    <h2>Chatwoot Settings</h2>
    <form method="post" action="options.php" class="chatwoot--form">
      <?php settings_fields('chatwoot-plugin-options'); ?>

      <div class="form--input">
        <label for="chatwootSiteToken">Chatwoot Website Token</label>
        <input type="text" name="chatwootSiteToken" value="<?php echo esc_attr(get_option('chatwootSiteToken')); ?>" />
      </div>

      <div class="form--input">
        <label for="chatwootSiteURL">Chatwoot Installation URL</label>
        <input type="text" name="chatwootSiteURL" value="<?php echo esc_url(get_option('chatwootSiteURL')); ?>" />
      </div>

      <div class="form--input">
        <label for="chatwootWebWidgetHmacToken">Web Widget HMAC Token</label>
        <input type="text" name="chatwootWebWidgetHmacToken" value="<?php echo esc_attr(chatwoot_decrypt(get_option('chatwootWebWidgetHmacToken'))); ?>" />
        <p class="description">Used for HMAC identity validation (will not be exposed publicly).</p>
      </div>

      <hr />

      <div class="form--input">
        <label for="chatwootWidgetType">Widget Design</label>
        <select name="chatwootWidgetType">
          <option value="standard" <?php selected(get_option('chatwootWidgetType'), 'standard'); ?>>Standard</option>
          <option value="expanded_bubble" <?php selected(get_option('chatwootWidgetType'), 'expanded_bubble'); ?>>Expanded Bubble</option>
        </select>
      </div>

      <div class="form--input">
        <label for="chatwootWidgetPosition">Widget Position</label>
        <select name="chatwootWidgetPosition">
          <option value="left" <?php selected(get_option('chatwootWidgetPosition'), 'left'); ?>>Left</option>
          <option value="right" <?php selected(get_option('chatwootWidgetPosition'), 'right'); ?>>Right</option>
        </select>
      </div>

      <div class="form--input">
        <label for="chatwootWidgetLocale">Language</label>
        <select name="chatwootWidgetLocale">
          <?php
          $locales = [
            'ar' => 'العربية', 'ca' => 'Català', 'cs' => 'čeština', 'da' => 'dansk', 'de' => 'Deutsch',
            'el' => 'ελληνικά', 'en' => 'English', 'es' => 'Español', 'fa' => 'فارسی', 'fi' => 'suomi',
            'fr' => 'Français', 'hi' => 'हिन्दी', 'hu' => 'magyar', 'id' => 'Bahasa Indonesia',
            'it' => 'Italiano', 'ja' => '日本語', 'ko' => '한국어', 'ml' => 'മലയാളം', 'nl' => 'Nederlands',
            'no' => 'norsk', 'pl' => 'język polski', 'pt_BR' => 'Português Brasileiro', 'pt' => 'Português',
            'ro' => 'Română', 'ru' => 'русский', 'sv' => 'Svenska', 'ta' => 'தமிழ்', 'tr' => 'Türkçe',
            'vi' => 'Tiếng Việt', 'zh_CN' => '中文 (zh-CN)', 'zh_TW' => '中文 (台湾)', 'zh' => '中文'
          ];
          foreach ($locales as $code => $label) {
            printf('<option value="%s" %s>%s (%s)</option>', esc_attr($code), selected(get_option('chatwootWidgetLocale'), $code, false), esc_html($label), esc_html($code));
          }
          ?>
        </select>
      </div>

      <?php if (get_option('chatwootWidgetType') === 'expanded_bubble') : ?>
        <div class="form--input">
          <label for="chatwootLauncherText">Launcher Text (Optional)</label>
          <input type="text" name="chatwootLauncherText" value="<?php echo esc_attr(get_option('chatwootLauncherText')); ?>" />
        </div>
      <?php endif; ?>

      <?php if (!defined('DISABLE_CHATWOOT_DEV_TOOLS') || DISABLE_CHATWOOT_DEV_TOOLS === false): ?>
        <hr />
        <h3>🛠 Advanced Developer Tools</h3>

        <div class="form--input checkbox-group">
          <label>
            <input type="checkbox" name="chatwootEnableHmacTester" value="1" <?php checked(get_option('chatwootEnableHmacTester'), '1'); ?> />
            Enable HMAC Test Page (Tools > Test Chatwoot HMAC)
          </label>
        </div>

        <div class="form--input checkbox-group">
          <label>
            <input type="checkbox" name="chatwootEnableKeyGenerator" value="1" <?php checked(get_option('chatwootEnableKeyGenerator'), '1'); ?> />
            Show Chatwoot Key Generator (Tools > Chatwoot Key Generator)
          </label>
        </div>

        <div class="form--input checkbox-group">
          <label>
            <input type="checkbox" name="chatwootEnableDebugMode" value="1" <?php checked(get_option('chatwootEnableDebugMode'), '1'); ?> />
            Enable <code>window.ChatwootDebug</code> (Logs to browser console)
          </label>
        </div>

        <div class="form--input">
          <details>
            <summary>🔍 View Chatwoot Identity Debug</summary>
            <pre><?php echo esc_html(print_r(chatwoot_get_user_payload(), true)); ?></pre>
          </details>
        </div>

        <div class="form--input">
          <details>
            <summary>🐞 What are Widget Errors?</summary>
            <p>When Chatwoot fails to load or has issues, it emits a <code>chatwoot:error</code> event. The plugin already listens to these events if <strong>Debug Mode</strong> is enabled. Below is an example:</p>
            <pre>
window.addEventListener("chatwoot:error", function (e) {
  console.error("❌ Chatwoot Widget Error", e.detail);
});
            </pre>
          </details>
        </div>
      <?php endif; ?>

      <div class="form--input submit">
        <?php submit_button(); ?>
      </div>
    </form>
  </div>
<?php
}