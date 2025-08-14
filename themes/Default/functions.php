<?php
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
				__('Меню', 'krishna-academy'),
				__('Меню', 'krishna-academy'),
				'edit_theme_options',
				'edit.php?post_type=wp_navigation'
			);
		});

		add_editor_style([
			'style.css',
			'assets/css/header.css',
			'assets/css/hero.css',
			'assets/css/front-page.css',
			'assets/css/course.css',
			'assets/css/courses.css',
			'assets/css/page.css',
			'assets/css/footer.css'
		]);
	}
endif;
add_action('after_setup_theme', 'krishna_academy_support');


#include get_parent_theme_file_path('inc/helpers.php');


# insert styles
add_action('wp_enqueue_scripts', 'krishna_academy_enqueue_styles');
add_action('init', function () {
	add_post_type_support('post', 'page-attributes');
});

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
add_action('wp_enqueue_scripts', 'krishna-academy_enqueue_scripts');

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

# Area named Loop for assigning parts of the template

add_filter('default_wp_template_part_areas', function ($areas) {
	$areas[] = array(
		'area'        => 'header',
		'area_tag'    => 'header',
		'label'       => __('Header', 'krishna-academy'),
		'description' => __('Site Header', 'krishna-academy'),
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
	$areas[] = array(
		'area'        => 'loop',
		'area_tag'    => 'section',
		'label'       => __('Loop', 'krishna-academy'),
		'description' => __('Custom description', 'krishna-academy'),
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

# Set default logo when activating theme
add_action('after_switch_theme', function () {
	$logo_path = get_theme_file_path('assets/images/logo.svg');
	$logo_url  = get_theme_file_uri('assets/images/logo.svg');

	// Upload the image to the media library when you first activate the theme.
	if (!get_theme_mod('custom_logo')) {
		$upload_dir = wp_upload_dir();
		$filename   = basename($logo_path);
		$dest_file  = $upload_dir['path'] . '/' . $filename;

		if (!file_exists($dest_file)) {
			copy($logo_path, $dest_file);
		}

		$attachment = [
			'post_mime_type' => 'image/svg+xml',
			'post_title'     => sanitize_file_name($filename),
			'post_content'   => '',
			'post_status'    => 'inherit'
		];

		$attach_id = wp_insert_attachment($attachment, $dest_file);
		require_once ABSPATH . 'wp-admin/includes/image.php';
		$attach_data = wp_generate_attachment_metadata($attach_id, $dest_file);
		wp_update_attachment_metadata($attach_id, $attach_data);

		set_theme_mod('custom_logo', $attach_id);
	}
});

# Allow SVG uploads
add_filter('upload_mimes', function ($mimes) {
	$mimes['svg'] = 'image/svg+xml';
	return $mimes;
});


// Automatically connect all patterns from the patterns/ folder
add_action('init', function () {
	$pattern_dir = get_theme_file_path('patterns');
	if (file_exists($pattern_dir)) {
		foreach (glob($pattern_dir . '/*.php') as $pattern_file) {
			require_once $pattern_file;
		}
	}
});

// Registering a template category for Krishna Academy templates


register_block_pattern_category(
	'krishna-academy',
	[
		'label' => __('Krishna Academy', 'krishna-academy')
	]
);

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
