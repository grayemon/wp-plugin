<?php
if (!defined('ABSPATH')) exit;

function chatwoot_decode_key($key) {
  return base64_decode(str_replace('base64:', '', $key));
}

function chatwoot_encrypt($plaintext) {
  if (!defined('CHATWOOT_ENCRYPTION_KEY') || !defined('CHATWOOT_ENCRYPTION_IV')) return '';
  $key = chatwoot_decode_key(CHATWOOT_ENCRYPTION_KEY);
  $iv  = chatwoot_decode_key(CHATWOOT_ENCRYPTION_IV);
  return base64_encode(openssl_encrypt($plaintext, 'AES-256-CBC', $key, 0, $iv));
}

function chatwoot_decrypt($ciphertext) {
  if (!defined('CHATWOOT_ENCRYPTION_KEY') || !defined('CHATWOOT_ENCRYPTION_IV')) return '';
  $key = chatwoot_decode_key(CHATWOOT_ENCRYPTION_KEY);
  $iv  = chatwoot_decode_key(CHATWOOT_ENCRYPTION_IV);
  return openssl_decrypt(base64_decode($ciphertext), 'AES-256-CBC', $key, 0, $iv);
}
