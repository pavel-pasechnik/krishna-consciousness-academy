<?php

/**
 * Plugin Name: Set User Language on Login
 * Author: Pavel Pasechnik
 * Description: Sets the user's language when logging in based on the selection on the authorization screen.
 * Text Domain: set-user-lang-on-login
 * Version: 1.0.6
 * Requires at least: 6.3
 * Requires PHP: 7.4
 * Update URI: https://github.com/pavel-pasechnik/wordpress/tree/main/plugins/set-user-lang-on-login
 * GitHub Plugin URI: https://github.com/pavel-pasechnik/wordpress/plugins/set-user-lang-on-login
 * Network: true
 * Primary Branch: main
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */


// Download translations
add_action('plugins_loaded', function () {
  // Load plugin translations only; no auto-install of language packs
  load_plugin_textdomain('set-user-lang-on-login', false, dirname(plugin_basename(__FILE__)) . '/languages');
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

// Check for missing core language packs and show an admin notice with a button
add_action('admin_init', function () {
  if (! is_admin()) return;
  if (! current_user_can('install_languages')) return;

  // If there are missing locales, enqueue a notice
  if (function_exists('sulol_get_missing_locales')) {
    $missing = sulol_get_missing_locales();
    if (! empty($missing)) {
      add_action('admin_notices', 'sulol_langpacks_notice');
    }
  }
});

/**
 * Normalize a locale to WordPress canonical form, e.g. en -> en_US, ru -> ru_RU, pt-br -> pt_BR.
 */
function sulol_normalize_locale($locale)
{
  $l = trim((string) $locale);
  if ($l === '') return $l;

  // Unify separators and case (xx or xx_YY)
  $l = str_replace('-', '_', $l);
  if (strpos($l, '_') !== false) {
    list($lang, $region) = explode('_', $l, 2);
    $l = strtolower($lang) . '_' . strtoupper($region);
  } else {
    $l = strtolower($l);
  }

  // Common short-to-full mappings
  switch ($l) {
    case 'en':
      return 'en_US';
    case 'ru':
      return 'ru_RU';
    case 'uk_ua':
      return 'uk'; // WP core uses just 'uk' for Ukrainian
    case 'pt_br':
      return 'pt_BR';
    case 'zh_cn':
      return 'zh_CN';
    case 'zh_tw':
      return 'zh_TW';
  }

  return $l;
}

/**
 * Get target locales from Polylang if available, otherwise defaults. Filterable via 'sulol_locales'.
 */
function sulol_get_target_locales()
{
  $locales = [];
  if (function_exists('pll_languages_list')) {
    $pll_locales = (array) pll_languages_list(['fields' => 'locale']);
    $locales = array_filter(array_map('strval', $pll_locales));
  }
  if (empty($locales)) {
    $locales = ['uk', 'ru_RU'];
  }
  $locales = array_map('sulol_normalize_locale', $locales);
  return apply_filters('sulol_locales', array_values(array_unique($locales)));
}

/**
 * Calculate which locales are missing from the installed core language packs.
 */
function sulol_get_missing_locales()
{
  $installed = array_map('sulol_normalize_locale', (array) get_available_languages());
  $targets   = sulol_get_target_locales(); // already normalized & filtered
  $missing   = [];

  foreach ($targets as $loc) {
    if (! in_array($loc, $installed, true)) {
      $missing[] = $loc;
    }
  }
  return $missing;
}

// Settings page to install missing language packs
add_action('admin_menu', function () {
  add_options_page(
    __('Language Packs', 'set-user-lang-on-login'),
    __('Language Packs', 'set-user-lang-on-login'),
    'install_languages',
    'sulol-lang-packs',
    'sulol_render_langpacks_page'
  );
});

function sulol_render_langpacks_page()
{
  if (! current_user_can('install_languages')) {
    wp_die(esc_html__('You do not have permission to install languages.', 'set-user-lang-on-login'));
  }

  $missing = sulol_get_missing_locales();
  echo '<div class="wrap">';
  echo '<h1>' . esc_html__('Language Packs', 'set-user-lang-on-login') . '</h1>';

  if (isset($_GET['sulol_status'])) {
    if ($_GET['sulol_status'] === 'ok') {
      echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Language packs installed.', 'set-user-lang-on-login') . '</p></div>';
    } elseif ($_GET['sulol_status'] === 'none') {
      echo '<div class="notice notice-info is-dismissible"><p>' . esc_html__('All target languages are already installed.', 'set-user-lang-on-login') . '</p></div>';
    } elseif ($_GET['sulol_status'] === 'error') {
      echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__('Some language packs could not be installed.', 'set-user-lang-on-login') . '</p></div>';
    }
  }

  if (empty($missing)) {
    echo '<p>' . esc_html__('Nothing to install. All target languages are present.', 'set-user-lang-on-login') . '</p>';
  } else {
    echo '<p>' . esc_html__('Missing language packs:', 'set-user-lang-on-login') . ' ' . esc_html(implode(', ', array_map('sulol_normalize_locale', $missing))) . '</p>';
    echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
    echo '<input type="hidden" name="action" value="sulol_install_langpacks" />';
    wp_nonce_field('sulol_install_langpacks');
    echo '<p><button class="button button-primary">' . esc_html__('Install Language Packs', 'set-user-lang-on-login') . '</button></p>';
    echo '</form>';
  }

  echo '</div>';
}

function sulol_langpacks_notice()
{
  if (! current_user_can('install_languages')) return;
  $missing = sulol_get_missing_locales();
  if (empty($missing)) return;
  $url = admin_url('options-general.php?page=sulol-lang-packs');
  echo '<div class="notice notice-warning is-dismissible"><p>'
    . esc_html__('Some language packs required by the login language switcher are not installed.', 'set-user-lang-on-login')
    . ' ' . sprintf('<a href="%s">%s</a>', esc_url($url), esc_html__('Install now', 'set-user-lang-on-login'))
    . '</p></div>';
}

add_action('admin_post_sulol_install_langpacks', function () {
  if (! current_user_can('install_languages')) {
    wp_die(esc_html__('You do not have permission to install languages.', 'set-user-lang-on-login'));
  }
  check_admin_referer('sulol_install_langpacks');

  // Ensure required includes are available
  if (! function_exists('request_filesystem_credentials')) {
    require_once ABSPATH . 'wp-admin/includes/file.php';
  }
  if (! function_exists('wp_download_language_pack')) {
    require_once ABSPATH . 'wp-admin/includes/translation-install.php';
  }

  $missing = sulol_get_missing_locales();

  if (empty($missing)) {
    wp_redirect(add_query_arg('sulol_status', 'none', admin_url('options-general.php?page=sulol-lang-packs')));
    exit;
  }

  $had_error = false;
  foreach ($missing as $loc) {
    $r = wp_download_language_pack(sulol_normalize_locale($loc));
    if (is_wp_error($r)) {
      $had_error = true;
    }
  }

  $status = $had_error ? 'error' : 'ok';
  wp_redirect(add_query_arg('sulol_status', $status, admin_url('options-general.php?page=sulol-lang-packs')));
  exit;
});

// Add "Settings" link on the Plugins screen
add_filter('plugin_action_links_' . plugin_basename(__FILE__), function ($links) {
  $url = admin_url('options-general.php?page=sulol-lang-packs');
  $links[] = '<a href="' . esc_url($url) . '">' . esc_html__('Settings', 'set-user-lang-on-login') . '</a>';
  return $links;
});
