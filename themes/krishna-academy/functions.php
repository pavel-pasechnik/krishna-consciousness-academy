
<?php

/**
 * Catch Krishna Academy functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package krishna-academy
 * @since 1.0
 */

// IDE stubs for Polylang (to satisfy Intelephense signatures). Safe: only define if plugin not loaded yet.
if (!function_exists('pll__')) {
	function pll__($text)
	{
		return $text;
	}
}
if (!function_exists('pll_translate_string')) {
	function pll_translate_string($text, $lang = null)
	{
		return $text;
	}
}

if (! function_exists('krishna_academy_support')) :

	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	function krishna_academy_support()
	{
		add_theme_support('block-templates');
		add_theme_support('block-template-parts');
		add_theme_support('site-logo');
		add_theme_support('wp-block-styles');
		add_theme_support('align-wide');
		add_theme_support('editor-styles');
		add_theme_support('responsive-embeds');
		add_theme_support('custom-logo', [
			'height'      => 100,
			'width'       => 100,
			'flex-height' => true,
			'flex-width'  => true,
		]);

		// Load theme translations
		load_theme_textdomain('krishna-academy', get_template_directory() . '/languages');

		add_post_type_support('page', 'excerpt');

		add_action('admin_menu', function () {
			add_submenu_page(
				'themes.php',
				__('Меню шаблона', 'krishna-academy'),
				__('Меню шаблона', 'krishna-academy'),
				'edit_theme_options',
				'edit.php?post_type=wp_navigation'
			);
		});

		add_editor_style([
			'style.css',
			'assets/css/reset.css',
			'assets/css/header.css',
			'assets/css/hero.css',
			'assets/css/front-page.css',
			'assets/css/course.css',
			'assets/css/courses.css',
			'assets/css/page.css',
			'assets/css/footer.css',
			'https://cdn.jsdelivr.net/npm/modern-normalize/modern-normalize.css'
		]);
	}

endif;

add_action('after_setup_theme', 'krishna_academy_support');

# Fallback: Enqueue normalize for editor if add_editor_style doesn't load external links
add_action('enqueue_block_editor_assets', function () {
	wp_enqueue_style(
		'krishna-academy-editor-normalize',
		'https://cdn.jsdelivr.net/npm/modern-normalize/modern-normalize.css',
		[],
		null
	);
});


# insert styles
add_action('wp_enqueue_scripts', 'krishna_academy_enqueue_styles');
add_action('init', function () {
	add_post_type_support('post', 'page-attributes');
});

/**
 * Enqueue styles.
 *
 * @since 1.0
 *
 * @return void
 */
function krishna_academy_enqueue_styles()
{
	wp_enqueue_style(
		'krishna-academy-main',
		get_stylesheet_uri(),
		[],
		wp_get_theme()->get('Version'),
		'all'
	);

	wp_enqueue_style(
		'modern-normalize',
		'https://cdn.jsdelivr.net/npm/modern-normalize/modern-normalize.css',
		array(),
		null
	);

	wp_enqueue_style(
		'krishna-academy-reset',
		get_theme_file_uri('assets/css/reset.css'),
		array('modern-normalize'),
		wp_get_theme()->get('Version'),
		'all'
	);

	// Connecting header styles
	wp_enqueue_style(
		'krishna-academy-header',
		get_theme_file_uri('assets/css/header.css'),
		array('modern-normalize', 'krishna-academy-reset'),
		wp_get_theme()->get('Version'),
		'all'
	);

	// Connecting hero styles
	wp_enqueue_style(
		'krishna-academy-hero',
		get_theme_file_uri('assets/css/hero.css'),
		array('modern-normalize', 'krishna-academy-reset'),
		wp_get_theme()->get('Version'),
		'all'
	);

	// Connecting front-pages styles
	wp_enqueue_style(
		'krishna-academy-front-page',
		get_theme_file_uri('assets/css/front-page.css'),
		array('modern-normalize', 'krishna-academy-reset'),
		wp_get_theme()->get('Version'),
		'all'
	);

	// Connecting course styles
	wp_enqueue_style(
		'krishna-academy-course',
		get_theme_file_uri('assets/css/course.css'),
		array('modern-normalize', 'krishna-academy-reset'),
		wp_get_theme()->get('Version'),
		'all'
	);

	// Connecting courses styles
	wp_enqueue_style(
		'krishna-academy-courses',
		get_theme_file_uri('assets/css/courses.css'),
		array('modern-normalize', 'krishna-academy-reset'),
		wp_get_theme()->get('Version'),
		'all'
	);

	// Connecting pages styles
	wp_enqueue_style(
		'krishna-academy-page',
		get_theme_file_uri('assets/css/page.css'),
		array('modern-normalize', 'krishna-academy-reset'),
		wp_get_theme()->get('Version'),
		'all'
	);

	// Connecting footer styles
	wp_enqueue_style(
		'krishna-academy-footer',
		get_theme_file_uri('assets/css/footer.css'),
		array('modern-normalize', 'krishna-academy-reset'),
		wp_get_theme()->get('Version'),
		'all'
	);
}



# Add javascript
add_action('wp_enqueue_scripts', 'krishna_academy_enqueue_scripts');

function krishna_academy_enqueue_scripts()
{
	if (is_admin()) {
		return;
	}

	wp_enqueue_script(
		'krishna-academy-nav-scroll',
		get_theme_file_uri('assets/js/nav-scroll.js'),
		[],
		wp_get_theme()->get('Version'),
		false
	);

	wp_enqueue_script(
		'ka-anchor-offset',
		get_theme_file_uri('assets/js/anchor-offset.js'),
		[],
		wp_get_theme()->get('Version'),
		true // внизу перед </body>
	);

	// Pass current-language home URL to JS (used to intercept same-page anchors)
	$ka_home_url = function_exists('pll_home_url') ? pll_home_url() : home_url('/');
	wp_localize_script('ka-anchor-offset', 'KA_NAV', [
		'homeUrl' => esc_url_raw($ka_home_url),
	]);
}

// === Dynamic host for generated URLs (front-end) ===
// Ensures home_url(), site_url() and all their consumers (permalinks, login/logout, etc.)
// use the current request's scheme+host (incl. ports like :8000) without changing values in DB.
if (! function_exists('ka_current_origin')) {
	/** Return current request origin like https://example.com:8443 or '' if CLI/cron. */
	function ka_current_origin()
	{
		// Prefer proxy headers if present
		$proto = isset($_SERVER['HTTP_X_FORWARDED_PROTO']) ? strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) : '';
		$host  = isset($_SERVER['HTTP_X_FORWARDED_HOST'])  ? $_SERVER['HTTP_X_FORWARDED_HOST'] : '';

		if (!$host) {
			$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
		}
		if (!$proto) {
			$proto = (is_ssl() ? 'https' : 'http');
		}
		if ($host) {
			return $proto . '://' . $host; // host may already include :port
		}
		return '';
	}
}

/** Replace scheme+host of a URL with current request origin; keep path/query/fragment intact. */
if (! function_exists('ka_url_with_current_host')) {
	function ka_url_with_current_host($url)
	{
		$origin = ka_current_origin();
		if ($origin === '') {
			return $url; // in CLI/cron contexts leave as-is
		}
		$parts = wp_parse_url($url);
		if ($parts === false) {
			return $url;
		}
		// Build path+query+fragment
		$path = isset($parts['path']) ? $parts['path'] : '';
		$query = isset($parts['query']) && $parts['query'] !== '' ? ('?' . $parts['query']) : '';
		$frag = isset($parts['fragment']) && $parts['fragment'] !== '' ? ('#' . $parts['fragment']) : '';
		return trailingslashit($origin) . ltrim($path, '/') . $query . $frag;
	}
}

// Apply on front-end only to avoid surprising the admin panel URLs
if (! is_admin()) {
	add_filter('home_url', function ($url) {
		return ka_url_with_current_host($url);
	}, 20);

	add_filter('site_url', function ($url) {
		return ka_url_with_current_host($url);
	}, 20);

	// Also ensure login/logout/register URLs follow the current host
	add_filter('login_url', function ($login, $redirect, $force_reauth) {
		return ka_url_with_current_host($login);
	}, 20, 3);
	add_filter('logout_url', function ($logout, $redirect) {
		return ka_url_with_current_host($logout);
	}, 20, 2);
	add_filter('register_url', function ($register) {
		return ka_url_with_current_host($register);
	}, 20);
}

# Area named Loop for assigning parts of the template

add_filter('default_wp_template_part_areas', function ($areas) {
	$areas[] = array(
		'area'        => 'header',
		'area_tag'    => 'header',
		'label'       => __('Header', 'krishna-academy'),
		'description' => __('Site header', 'krishna-academy'),
		'icon'        => 'layout'
	);
	$areas[] = array(
		'area'        => 'main',
		'area_tag'    => 'main',
		'label'       => __('Main', 'krishna-academy'),
		'description' => __('Site Main', 'krishna-academy'),
		'icon'        => 'layout'
	);
	$areas[] = array(
		'area'        => 'hero',
		'area_tag'    => 'section',
		'label'       => __('Section hero', 'krishna-academy'),
		'description' => __('Site Section', 'krishna-academy'),
		'icon'        => 'layout'
	);
	$areas[] = array(
		'area'        => 'footer',
		'area_tag'    => 'footer',
		'label'       => __('Footer', 'krishna-academy'),
		'description' => __('Site Footer', 'krishna-academy'),
		'icon'        => 'layout'
	);
	return $areas;
});

# This code removes jquery-migrate's dependency on jquery in WordPress. After executing this code, jquery-migrate will no longer be loaded along with jquery on your site.

add_action('wp_default_scripts', function ($scripts) {
	if (!empty($scripts->registered['jquery'])) {
		$scripts->registered['jquery']->deps = array_diff($scripts->registered['jquery']->deps, ['jquery-migrate']);
	}
});


// 👇 Enable multilingualism for standard categories in Polylang
add_filter('pll_get_taxonomies', function ($taxonomies) {
	$taxonomies[] = 'category';
	return $taxonomies;
});

// Enable multilingualism for block menus (FSE Navigation) in Polylang
add_filter('pll_get_post_types', function ($post_types, $is_settings) {
	// Register the block-based menus so each language can have its own Navigation
	$post_types['wp_navigation'] = 'wp_navigation';
	return $post_types;
}, 10, 2);

// Enable multilingualism for template parts (FSE) in Polylang
add_filter('pll_get_post_types', function ($post_types, $is_settings) {
	$post_types['wp_template_part'] = 'wp_template_part';
	return $post_types;
}, 10, 2);

// Auto-assign language to block menus and backfill existing menus without language
add_action('save_post_wp_navigation', function ($post_id, $post, $update) {
	if (! function_exists('pll_set_post_language') || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)) {
		return;
	}
	// If the menu already has a language, do nothing
	if (function_exists('pll_get_post_language') && pll_get_post_language($post_id)) {
		return;
	}
	// Set current admin language or default language
	$lang = function_exists('pll_current_language') ? pll_current_language('slug') : '';
	if (! $lang && function_exists('pll_default_language')) {
		$lang = pll_default_language('slug');
	}
	if ($lang) {
		pll_set_post_language($post_id, $lang);
	}
}, 10, 3);

// One-time backfill: assign default language to existing wp_navigation items without language
add_action('admin_init', function () {
	if (! function_exists('pll_set_post_language') || ! function_exists('pll_default_language')) return;
	$flag = get_option('ka_pll_nav_backfilled');
	if ($flag) return;
	$default = pll_default_language('slug');
	if (! $default) return;
	$menus = get_posts([
		'post_type'      => 'wp_navigation',
		'posts_per_page' => -1,
		'post_status'    => 'any',
	]);
	foreach ($menus as $m) {
		if (! function_exists('pll_get_post_language') || ! pll_get_post_language($m->ID)) {
			pll_set_post_language($m->ID, $default);
		}
	}
	update_option('ka_pll_nav_backfilled', 1);
});


// === Polylang strings & shortcode for FSE template parts ===
add_action('init', function () {
	if (! function_exists('pll_register_string')) return;
	// Register strings that will be translated via Polylang UI
	pll_register_string('footer-link-1',   'Свідоцтво академії',        'Footer', true);
	pll_register_string('footer-link-2',   'Статут академії',           'Footer', true);
	pll_register_string('footer-adress-1', 'Зоряний провулок, 16, Київ', 'Footer', true); // note: key matches current template
});


/**
 * Register custom block styles (Theme Review: recommended)
 */
add_action('init', function () {
	if (function_exists('register_block_style')) {
		// Paragraph: Lead
		register_block_style('core/paragraph', [
			'name'  => 'ka-lead',
			'label' => __('Lead (larger intro)', 'krishna-academy'),
			'inline_style' => '.is-style-ka-lead{font-size:clamp(1.125rem,1rem+1vw,1.5rem);line-height:1.5;font-weight:500;}',
		]);
		// Image: Soft shadow
		register_block_style('core/image', [
			'name'  => 'ka-soft-shadow',
			'label' => __('Soft shadow', 'krishna-academy'),
			'inline_style' => '.is-style-ka-soft-shadow img{box-shadow:0 8px 24px rgba(0,0,0,.08);border-radius:8px;}',
		]);
		// Buttons: Pill
		register_block_style('core/buttons', [
			'name'  => 'ka-pill',
			'label' => __('Pill buttons', 'krishna-academy'),
			'inline_style' => '.is-style-ka-pill .wp-block-button__link{border-radius:999px;padding-inline:1.25em;}',
		]);
	}
});

/**
 * Register block patterns and category (Theme Review: recommended)
 * Uses file-based PHP arrays in /patterns/*.php
 */
add_action('init', function () {
	if (! function_exists('register_block_pattern') || ! function_exists('register_block_pattern_category')) {
		return;
	}
	// Ensure category exists
	register_block_pattern_category('krishna-academy', [
		'label' => __('Krishna Academy', 'krishna-academy'),
	]);
	// Load all PHP pattern files and register
	$pattern_dir = get_theme_file_path('patterns');
	if ($pattern_dir && file_exists($pattern_dir)) {
		foreach (glob(trailingslashit($pattern_dir) . '*.php') as $file) {
			$pattern = require $file;
			if (is_array($pattern)) {
				// If file does not define slug, derive from filename
				if (empty($pattern['slug'])) {
					$slug = 'krishna-academy/' . basename($file, '.php');
				} else {
					$slug = $pattern['slug'];
				}
				$registry = WP_Block_Patterns_Registry::get_instance();
				if (! $registry->is_registered($slug)) {
					register_block_pattern($slug, $pattern);
				}
			}
		}
	}
});

# Set default logo when activating theme
add_action('after_switch_theme', function () {
	$logo_path = get_theme_file_path('assets/images/logo.svg');
	if (! file_exists($logo_path)) {
		return; // nothing to do if theme logo missing
	}

	// Do nothing if a custom logo is already set
	if (get_theme_mod('custom_logo')) {
		return;
	}

	$upload_dir = wp_upload_dir();
	if (! empty($upload_dir['error'])) {
		return;
	}

	// Ensure upload subdirectory exists
	if (! file_exists($upload_dir['path'])) {
		wp_mkdir_p($upload_dir['path']);
	}

	$filename  = sanitize_file_name(basename($logo_path));
	$dest_file = trailingslashit($upload_dir['path']) . $filename;

	// Copy file into uploads if not there yet
	if (! file_exists($dest_file)) {
		copy($logo_path, $dest_file);
	}

	// Detect mime type (SVG or raster)
	$filetype = wp_check_filetype($filename);
	$mime     = $filetype['type'] ? $filetype['type'] : 'image/svg+xml';

	$attachment = [
		'post_mime_type' => $mime,
		'post_title'     => pathinfo($filename, PATHINFO_FILENAME),
		'post_content'   => '',
		'post_status'    => 'inherit',
	];

	$attach_id = wp_insert_attachment($attachment, $dest_file);

	// For raster images this will generate sizes; for SVG it will be a no-op.
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$attach_data = wp_generate_attachment_metadata($attach_id, $dest_file);
	wp_update_attachment_metadata($attach_id, $attach_data);

	set_theme_mod('custom_logo', $attach_id);
});


// Ensure shortcodes render inside template parts (footer/header etc.)
add_filter('render_block_core/template-part', function ($content, $block) {
	// Run shortcodes on the rendered HTML of template parts
	return do_shortcode($content);
}, 9, 2);

// === Suggest installing/activating recommended plugins for full theme functionality ===
add_action('admin_init', function () {
	// Dismiss handler
	if (isset($_GET['ka-dismiss-plugins']) && current_user_can('install_plugins')) {
		update_option('ka_plugins_notice_dismissed', 1);
		wp_safe_redirect(remove_query_arg('ka-dismiss-plugins'));
		exit;
	}
});

add_action('admin_notices', function () {
	if (! current_user_can('install_plugins')) return;
	if (get_option('ka_plugins_notice_dismissed')) return;

	require_once ABSPATH . 'wp-admin/includes/plugin.php';
	require_once ABSPATH . 'wp-admin/includes/plugin-install.php';

	$plugins = [
		[
			'name' => 'Set User Language on Login',
			'slug' => 'set-user-language-on-login', // wp.org slug
			'repo' => true,
		],
		[
			'name' => 'Safe SVG',
			'slug' => 'safe-svg',
			'repo' => true,
		],
		[
			'name' => 'Polylang Pro',
			'slug' => 'polylang-pro', // commercial, cannot auto-install
			'repo' => false,
			'url'  => 'https://polylang.pro/',
		],
		[
			'name' => 'WordPress Importer',
			'slug' => 'wordpress-importer',
			'repo' => true,
		],
	];

	$all = get_plugins(); // ["dir/file.php" => headers]

	// Helper: find first plugin file within a directory (slug)
	$find_plugin_file = function ($slug) use ($all) {
		foreach ($all as $file => $headers) {
			if (str_starts_with($file, $slug . '/')) return $file;
		}
		return '';
	};

	$dismiss_url = wp_nonce_url(add_query_arg('ka-dismiss-plugins', 1), 'ka_dismiss_plugins');

	echo '<div class="notice notice-info is-dismissible" data-dismissible="ka-plugins">';
	echo '<p><strong>' . esc_html__('Plugins required/recommended for full theme functionality:', 'krishna-academy') . '</strong></p>';
	echo '<ul style="margin-left:1em; list-style:disc;">';
	foreach ($plugins as $p) {
		$slug = $p['slug'];
		$name = $p['name'];
		$repo = !empty($p['repo']);
		$file = $find_plugin_file($slug);
		$is_installed = ($file !== '');
		$is_active = $is_installed && is_plugin_active($file);

		echo '<li style="margin: .25em 0;">';
		echo '<strong>' . esc_html($name) . '</strong> ';

		if ($repo) {
			if (! $is_installed) {
				$install_url = wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=' . $slug), 'install-plugin_' . $slug);
				echo '<a class="button button-small" href="' . esc_url($install_url) . '">' . esc_html__('Install', 'krishna-academy') . '</a> ';
			} elseif (! $is_active) {
				$activate_url = wp_nonce_url(self_admin_url('plugins.php?action=activate&plugin=' . urlencode($file)), 'activate-plugin_' . $file);
				echo '<a class="button button-small button-primary" href="' . esc_url($activate_url) . '">' . esc_html__('Activate', 'krishna-academy') . '</a> ';
			} else {
				echo '<span class="dashicons dashicons-yes" style="color:#46b450"></span> ' . esc_html__('Active', 'krishna-academy');
			}
		} else {
			$url = isset($p['url']) ? $p['url'] : '';
			if (! $is_active) {
				echo '<a class="button button-small" target="_blank" rel="noopener" href="' . esc_url($url) . '">' . esc_html__('Buy/download', 'krishna-academy') . '</a> ';
			}
			if ($is_installed && ! $is_active) {
				$activate_url = wp_nonce_url(self_admin_url('plugins.php?action=activate&plugin=' . urlencode($file)), 'activate-plugin_' . $file);
				echo '<a class="button button-small button-primary" href="' . esc_url($activate_url) . '">' . esc_html__('Activate', 'krishna-academy') . '</a> ';
			}
			if ($is_active) {
				echo '<span class="dashicons dashicons-yes" style="color:#46b450"></span> ' . esc_html__('Active', 'krishna-academy');
			}
		}

		echo '</li>';
	}
	echo '</ul>';
	echo '<p><a href="' . esc_url($dismiss_url) . '" class="button button-link">' . esc_html__('Do not show this again', 'krishna-academy') . '</a></p>';
	echo '</div>';
});
