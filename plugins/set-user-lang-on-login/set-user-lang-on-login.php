<?php

/**
 * Plugin Name: Set User Language on Login
 * Description: Sets the user's language when logging in based on the selection on the authorization screen.
 * Version: 1.0.6
 * Author: Pavel Pasechnik
 * Text Domain: set-user-lang-on-login
 * Requires at least: 6.3
 * Requires PHP: 7.4
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI: https://github.com/pavel-pasechnik/wordpress/tree/main/plugins/set-user-lang-on-login
 * GitHub Plugin URI: https://github.com/pavel-pasechnik/wordpress
 * Primary Branch: main
 * Directory: plugins/set-user-lang-on-login
 */


// Download translations
add_action('plugins_loaded', function () {
  load_plugin_textdomain('set-user-lang-on-login', false, dirname(plugin_basename(__FILE__)) . '/languages');

  // Auto-install required core language packs so the login language switcher works
  if (is_admin() && current_user_can('install_languages')) {
    require_once ABSPATH . 'wp-admin/includes/translation-install.php';

    // Remove transient throttling (no longer used)

    // Build locales list from Polylang (if present), always include English, allow filter
    $locales = [];
    if (function_exists('pll_languages_list')) {
      // Get locales configured in Polylang (e.g., ['uk', 'ru_RU', 'en_US'])
      $pll_locales = (array) pll_languages_list(['fields' => 'locale']);
      $locales = array_filter(array_map('strval', $pll_locales));
    }
    // Ensure English is available as a safe fallback alongside Polylang languages
    $locales = array_unique(array_merge($locales, ['en_US']));
    // If Polylang is not active/configured, fall back to defaults
    if (empty($locales)) {
      $locales = ['uk', 'ru_RU', 'en_US'];
    }
    // Allow customization via filter
    $locales = apply_filters('sulol_locales', $locales);

    foreach ($locales as $locale) {
      wp_download_language_pack($locale);
    }
  }
});

// Save the selected language in user_meta
add_action('wp_login', function ($user_login, $user) {
  if (!empty($_POST['language'])) {
    $lang = sanitize_text_field($_POST['language']);
    if (in_array($lang, get_available_languages())) {
      update_user_meta($user->ID, 'locale', $lang);
      add_filter('login_redirect', function ($redirect_to, $request, $user) {
        add_action('admin_notices', function () {
          echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Your language was saved successfully.', 'set-user-lang-on-login') . '</p></div>';
        });
        return $redirect_to;
      }, 10, 3);
    }
  }
}, 10, 2);

// Insert a hidden language field into the login form
add_action('login_form', function () {
?>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const langSelect = document.querySelector('#language-switcher select');
      if (langSelect) {
        const form = document.querySelector('#loginform');
        let hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = 'language';
        hidden.value = langSelect.value;
        form.appendChild(hidden);

        langSelect.addEventListener('change', function() {
          hidden.value = langSelect.value;
        });
      }
    });
  </script>
<?php
});

// Auto-install required core language packs so the login language switcher works
add_action('admin_init', function () {
  if (!is_admin()) return;
  if (!current_user_can('update_core')) return;

  // Avoid doing this too often
  if (get_transient('sulol_langpacks_checked')) return;
  set_transient('sulol_langpacks_checked', 12 * HOUR_IN_SECONDS);

  // Functions for installing language packs
  if (!function_exists('wp_can_install_language_pack') || !function_exists('wp_download_language_pack')) {
    require_once ABSPATH . 'wp-admin/includes/translation-install.php';
  }

  if (!function_exists('wp_can_install_language_pack') || !wp_can_install_language_pack()) return;

  // Locales you want to ensure are present (can be customized via filter)
  $locales = apply_filters('sulol_locales', ['uk', 'ru_RU']);

  // Installed core language packs
  $installed = get_available_languages();

  foreach ($locales as $loc) {
    if (!in_array($loc, $installed, true)) {
      // Silently try to download the language pack
      wp_download_language_pack($loc);
    }
  }
});
