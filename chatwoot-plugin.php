<?php
/**
 * Plugin Name:     Chatwoot Plugin
 * Plugin URI:      https://www.chatwoot.com/
 * Description:     Chatwoot Plugin for WordPress. This plugin helps you to quickly integrate Chatwoot live-chat widget on Wordpress websites.
 * Author:          antpb
 * Author URI:      chatwoot.com
 * Text Domain:     chatwoot-plugin
 * Version:         0.2.0
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

  // new options
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
    
  //if (get_option('chatwootEnableDebugMode') === '1') {
    //echo '<script>window.ChatwootDebug = true;</script>';
  //}
}

/**
 * Render page.
 *
 * @since 0.1.0
 *
 * @return {void}.
 */

 /* to deprecate
function chatwoot_options_page() {
  ?>
  <div>
    <h2>Chatwoot Settings</h2>
    <form method="post" action="options.php" class="chatwoot--form">
      <?php settings_fields('chatwoot-plugin-options'); ?>
      <div class="form--input">
        <label for="chatwootSiteToken">Chatwoot Website Token</label>
        <input
          type="text"
          name="chatwootSiteToken"
          value="<?php echo get_option('chatwootSiteToken'); ?>"
        />
      </div>
      <div class="form--input">
        <label for="chatwootSiteURL">Chatwoot Installation URL</label>
        <input
          type="text"
          name="chatwootSiteURL"
          value="<?php echo get_option('chatwootSiteURL'); ?>"
        />
      </div>
      <div class="form--input">
        <label for="chatwootWebWidgetHmacToken">Web Widget HMAC Token</label>
        <input
          type="text"
          name="chatwootWebWidgetHmacToken"
          value="<?php echo esc_attr(chatwoot_decrypt(get_option('chatwootWebWidgetHmacToken'))); ?>"
        />
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
          <option <?php selected(get_option('chatwootWidgetLocale'), 'ar'); ?> value="ar">العربية (ar)</option>
          <option <?php selected(get_option('chatwootWidgetLocale'), 'ca'); ?> value="ca">Català (ca)</option>
          <option <?php selected(get_option('chatwootWidgetLocale'), 'cs'); ?> value="cs">čeština (cs)</option>
          <option <?php selected(get_option('chatwootWidgetLocale'), 'da'); ?> value="da">dansk (da)</option>
          <option <?php selected(get_option('chatwootWidgetLocale'), 'de'); ?> value="de">Deutsch (de)</option>
          <option <?php selected(get_option('chatwootWidgetLocale'), 'el'); ?> value="el">ελληνικά (el)</option>
          <option <?php selected(get_option('chatwootWidgetLocale'), 'en'); ?> value="en">English (en)</option>
          <option <?php selected(get_option('chatwootWidgetLocale'), 'es'); ?> value="es">Español (es)</option>
          <option <?php selected(get_option('chatwootWidgetLocale'), 'fa'); ?> value="fa">فارسی (fa)</option>
          <option <?php selected(get_option('chatwootWidgetLocale'), 'fi'); ?> value="fi">suomi, suomen kieli (fi)</option>
          <option <?php selected(get_option('chatwootWidgetLocale'), 'fr'); ?> value="fr">Français (fr)</option>
          <option <?php selected(get_option('chatwootWidgetLocale'), 'hi'); ?> value="hi'">हिन्दी (hi)</option>
          <option <?php selected(get_option('chatwootWidgetLocale'), 'hu'); ?> value="hu">magyar nyelv (hu)</option>
          <option <?php selected(get_option('chatwootWidgetLocale'), 'id'); ?> value="id">Bahasa Indonesia (id)</option>
          <option <?php selected(get_option('chatwootWidgetLocale'), 'it'); ?> value="it">Italiano (it)</option>
          <option <?php selected(get_option('chatwootWidgetLocale'), 'ja'); ?> value="ja">日本語 (ja)</option>
          <option <?php selected(get_option('chatwootWidgetLocale'), 'ko'); ?> value="ko">한국어 (ko)</option>
          <option <?php selected(get_option('chatwootWidgetLocale'), 'ml'); ?> value="ml">മലയാളം (ml)</option>
          <option <?php selected(get_option('chatwootWidgetLocale'), 'nl'); ?> value="nl">Nederlands (nl) </option>
          <option <?php selected(get_option('chatwootWidgetLocale'), 'no'); ?> value="no">norsk (no)</option>
          <option <?php selected(get_option('chatwootWidgetLocale'), 'pl'); ?> value="pl">język polski (pl)</option>
          <option <?php selected(get_option('chatwootWidgetLocale'), 'pt_BR'); ?> value="pt_BR">Português Brasileiro (pt-BR)
          <option <?php selected(get_option('chatwootWidgetLocale'), 'pt'); ?> value="pt">Português (pt)</option>
          <option <?php selected(get_option('chatwootWidgetLocale'), 'ro'); ?> value="ro">Română (ro)</option>
          <option <?php selected(get_option('chatwootWidgetLocale'), 'ru'); ?> value="ru">русский (ru)</option>
          <option <?php selected(get_option('chatwootWidgetLocale'), 'sv'); ?> value="sv">Svenska (sv)</option>
          <option <?php selected(get_option('chatwootWidgetLocale'), 'ta'); ?> value="ta">தமிழ் (ta)</option>
          <option <?php selected(get_option('chatwootWidgetLocale'), 'tr'); ?> value="tr">Türkçe (tr)</option>
          <option <?php selected(get_option('chatwootWidgetLocale'), 'vi'); ?> value="vi">Tiếng Việt (vi)</option>
          <option <?php selected(get_option('chatwootWidgetLocale'), 'zh_CN'); ?> value="zh_CN">中文 (zh-CN)</option>
          <option <?php selected(get_option('chatwootWidgetLocale'), 'zh_TW'); ?> value="zh_TW">中文 (台湾) (zh-TW)</option>
          <option <?php selected(get_option('chatwootWidgetLocale'), 'zh'); ?> value="zh'">中文 (zh)</option>
        </select>
      </div>
      <?php if (get_option('chatwootWidgetType') == 'expanded_bubble') : ?>
        <div class="form--input">
          <label for="chatwootLauncherText">Launcher Text (Optional)</label>
          <input
            type="text"
            name="chatwootLauncherText"
            value="<?php echo get_option('chatwootLauncherText'); ?>"
          />
        </div>
      <?php endif; ?>
      <?php if (!defined('DISABLE_CHATWOOT_DEV_TOOLS') || DISABLE_CHATWOOT_DEV_TOOLS === false): ?>
  <hr />
  <h3 style="margin-top:30px;">🛠 Advanced Developer Tools</h3>

  <div class="form--input">
    <label for="chatwootEnableHmacTester">
      <input type="checkbox" name="chatwootEnableHmacTester" value="1" <?php checked(get_option('chatwootEnableHmacTester'), '1'); ?> />
      Enable HMAC Test Page (Tools > Test Chatwoot HMAC)
    </label>
  </div>

  <div class="form--input">
    <label for="chatwootEnableKeyGenerator">
      <input type="checkbox" name="chatwootEnableKeyGenerator" value="1" <?php checked(get_option('chatwootEnableKeyGenerator'), '1'); ?> />
      Show Chatwoot Key Generator (Tools > Chatwoot Key Generator)
    </label>
  </div>

  <div class="form--input">
    <label for="chatwootEnableDebugMode">
      <input type="checkbox" name="chatwootEnableDebugMode" value="1" <?php checked(get_option('chatwootEnableDebugMode'), '1'); ?> />
      Enable window.ChatwootDebug (Logs to browser console)
    </label>
  </div>

  <div class="form--input">
    <details>
      <summary>🔍 View Chatwoot Identity Debug</summary>
      <pre style="background:#f1f1f1;padding:10px;"><?php echo esc_html(print_r(chatwoot_get_user_payload(), true)); ?></pre>
    </details>
  </div>

  <div class="form--input">
    <details>
      <summary>🐞 Listen to Widget Errors</summary>
      <pre style="background:#f9f9f9;padding:10px;">
        window.addEventListener("chatwoot:error", function (e) {
          console.error("❌ Chatwoot Widget Error", e.detail);
        });
      </pre>
    </details>
</div>
<?php endif; ?>
      <?php submit_button(); ?>
    </form>
  </div>
<?php
}
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
            <summary>🐞 Listen to Widget Errors</summary>
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



