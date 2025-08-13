<?php

/**
 * Catch Krishna Academy functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Krishna_Academy
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

		add_post_type_support('page', 'excerpt');

		add_action('admin_menu', function () {
			add_submenu_page(
				'themes.php',
				__('Меню шаблона', 'krishna_academy'),
				__('Меню шаблона', 'krishna_academy'),
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
		'label'       => __('Header', 'krishna_academy'),
		'description' => __('Site header', 'krishna_academy'),
		'icon'        => 'layout'
	);
	$areas[] = array(
		'area'        => 'main',
		'area_tag'    => 'main',
		'label'       => __('Main', 'krishna_academy'),
		'description' => __('Site Main', 'krishna_academy'),
		'icon'        => 'layout'
	);
	$areas[] = array(
		'area'        => 'hero',
		'area_tag'    => 'section',
		'label'       => __('Section hero', 'krishna_academy'),
		'description' => __('Site Section', 'krishna_academy'),
		'icon'        => 'layout'
	);
	$areas[] = array(
		'area'        => 'footer',
		'area_tag'    => 'footer',
		'label'       => __('Footer', 'krishna_academy'),
		'description' => __('Site Footer', 'krishna_academy'),
		'icon'        => 'layout'
	);
	$areas[] = array(
		'area'        => 'loop',
		'area_tag'    => 'section',
		'label'       => __('Loop', 'krishna_academy'),
		'description' => __('Custom description', 'krishna_academy'),
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

# Allow SVG uploads
add_filter('upload_mimes', function ($mimes) {
	$mimes['svg'] = 'image/svg+xml';
	return $mimes;
});



// Ensure pattern category exists and register file-based patterns programmatically (for reliability across WP versions)
add_action('init', function () {
	// 1) Category (safe to re-register)
	if (function_exists('register_block_pattern_category')) {
		register_block_pattern_category(
			'krishna-academy',
			[
				'label' => __('Krishna Academy', 'krishna_academy')
			]
		);
	}

	// 2) Header pattern: load array from patterns/header.php and register with explicit slug
	if (function_exists('register_block_pattern')) {
		$file = get_theme_file_path('patterns/header.php');
		if (file_exists($file)) {
			$pattern = require $file;
			if (is_array($pattern)) {
				if (empty($pattern['slug'])) {
					$pattern['slug'] = 'krishna-academy/header';
				}
				$slug = $pattern['slug'];
				$registry = WP_Block_Patterns_Registry::get_instance();
				if (! $registry->is_registered($slug)) {
					register_block_pattern($slug, $pattern);
				}
			}
		}
	}
});

/**
 * === Footer links via Polylang strings + shortcodes (Variant #3) ===
 * One common footer; texts/URLs translated via String Translations.
 */
if (! function_exists('ka_footer_defaults')) {
	function ka_footer_defaults()
	{
		return [
			'group'            => 'Theme: Krishna Academy',
			'address_text'     => 'Зоряний провулок, 16, Київ',
			'address_url'      => 'https://maps.app.goo.gl/7iRstgRygdGF47E67',
			'certificate_text' => 'Свідоцтво академії',
			'certificate_url'  => '/',
			'charter_text'     => 'Статут академії',
			'charter_url'      => '/',
		];
	}
}

// Register strings for translation in Polylang → String translations
add_action('init', function () {
	if (! function_exists('pll_register_string')) return;
	$defs = ka_footer_defaults();
	pll_register_string('Footer: address_text',     $defs['address_text'],     $defs['group']);
	pll_register_string('Footer: address_url',      $defs['address_url'],      $defs['group']);
	pll_register_string('Footer: certificate_text', $defs['certificate_text'], $defs['group']);
	pll_register_string('Footer: certificate_url',  $defs['certificate_url'],  $defs['group']);
	pll_register_string('Footer: charter_text',     $defs['charter_text'],     $defs['group']);
	pll_register_string('Footer: charter_url',      $defs['charter_url'],      $defs['group']);
});

if (! function_exists('ka_footer_get')) {
	/** Get translated value for footer field; fallback to defaults. */
	function ka_footer_get($key)
	{
		$defs = ka_footer_defaults();
		$val  = isset($defs[$key]) ? $defs[$key] : '';
		// Texts
		if (function_exists('pll__') && in_array($key, ['address_text', 'certificate_text', 'charter_text'], true)) {
			return pll__($val);
		}
		// URLs
		if (
			function_exists('pll_translate_string') &&
			function_exists('pll_current_language') &&
			in_array($key, ['address_url', 'certificate_url', 'charter_url'], true)
		) {
			return pll_translate_string($val, pll_current_language('slug'));
		}
		return $val;
	}
}

// Shortcodes used in the footer template part
add_shortcode('ka_footer_address', function () {
	$href = esc_url(ka_footer_get('address_url'));
	$text = esc_html(ka_footer_get('address_text'));
	return '<p class="footer-address"><a class="footer-link" href="' . $href . '" target="_blank">' . $text . '</a></p>';
});

add_shortcode('ka_footer_link', function ($atts) {
	$atts = shortcode_atts(['slug' => 'certificate'], $atts, 'ka_footer_link');
	$slug = ($atts['slug'] === 'charter') ? 'charter' : 'certificate';
	$text = esc_html(ka_footer_get($slug . '_text'));
	$href = esc_url(ka_footer_get($slug . '_url'));
	$img  = '<img class="footer-' . esc_attr($slug) . '-icon" src="' . esc_url(get_theme_file_uri('assets/images/award-certificate.svg')) . '" alt="' . ($slug === 'certificate' ? 'certificate icon' : 'charter icon') . '" />';
	$wrapper = ($slug === 'certificate') ? 'footer-academy-certificate' : 'footer-academy-charter';
	return '<p class="' . esc_attr($wrapper) . '"><a class="footer-link" href="' . $href . '" target="_blank">' . $img . $text . '</a></p>';
});

// Cleanup previously seeded inline footers from page content (to avoid duplicates)
add_action('init', function () {
	$front_id = (int) get_option('page_on_front');
	if (! $front_id) return;
	$clean = function (int $page_id) {
		$content = (string) get_post_field('post_content', $page_id);
		$start = strpos($content, '<!-- KA:footer -->');
		if ($start === false) return;
		$end = strpos($content, '<!-- /KA:footer -->', $start);
		if ($end === false) return;
		$new = trim(substr($content, 0, $start) . substr($content, $end + strlen('<!-- /KA:footer -->')));
		wp_update_post(['ID' => $page_id, 'post_content' => $new]);
	};
	$clean($front_id);
	if (function_exists('pll_get_post_translations')) {
		$translations = (array) pll_get_post_translations($front_id);
		foreach ($translations as $pid) {
			if ((int)$pid !== (int)$front_id) $clean((int)$pid);
		}
	}
	delete_option('ka_footer_seeded_v2');
});

// Ensure shortcodes render inside template parts (footer/header etc.)
add_filter('render_block_core/template-part', function ($content, $block) {
	// Run shortcodes on the rendered HTML of template parts
	return do_shortcode($content);
}, 9, 2);
