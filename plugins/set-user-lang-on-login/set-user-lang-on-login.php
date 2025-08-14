<?php

/**
 * Plugin Name: Set User Language on Login
 * Description: Sets the user's language when logging in based on the selection on the authorization screen.
 * Version: 1.0.0
 * Author: Pavel Pasechnik
 * Text Domain: krishna-academy
 * Requires at least: 6.3
 * Requires PHP: 7.4
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Download translations
add_action('plugins_loaded', function () {
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
