<?php
if (!defined('ABSPATH')) exit;

require_once __DIR__ . '/crypto.php';

/**
 * Returns the visitor ID — user ID or anonymous UUID from cookie.
 */
function chatwoot_get_visitor_id() {
    static $visitor_id = null;

    if ($visitor_id !== null) {
        return $visitor_id;
    }

    if (is_user_logged_in()) {
        $visitor_id = (string) get_current_user_id();
    } else {
        $visitor_id = isset($_COOKIE['cw_vid']) ? $_COOKIE['cw_vid'] : chatwoot_generate_anonymous_id();
    }

    return $visitor_id;
}

/**
 * Generates and stores anonymous UUID in secure cookie
 */
function chatwoot_generate_anonymous_id() {
    $uuid = wp_generate_uuid4();
    setcookie('cw_vid', $uuid, [
        'expires' => time() + 31536000, // 1 year
        'path' => '/',
        'secure' => is_ssl(),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    return $uuid;
}

/**
 * Generates the HMAC identifier_hash
 */
function chatwoot_generate_hash($visitor_id) {
    static $cached_token = null;

    if ($cached_token === null && function_exists('chatwoot_decrypt')) {
        $hmac_token_option = get_option('chatwootWebWidgetHmacToken');
        $decrypted = $hmac_token_option ? chatwoot_decrypt($hmac_token_option) : '';
        $cached_token = $decrypted ?: '';
    }

    $token = $cached_token;
    $visitor_id = (string) $visitor_id;

    return $token ? hash_hmac('sha256', $visitor_id, $token) : '';
}


/**
 * Returns full Chatwoot identity payload for JS injection
 */
function chatwoot_get_user_payload() {
    $visitor_id = chatwoot_get_visitor_id();
    $identifier_hash = chatwoot_generate_hash($visitor_id);

    $payload = [
        'identifier' => $visitor_id,
        'hash' => $identifier_hash
    ];

    if (is_user_logged_in()) {
        $user = wp_get_current_user();
        $payload['name'] = $user->display_name;
        $payload['email'] = $user->user_email;
    }

    return $payload;
}
