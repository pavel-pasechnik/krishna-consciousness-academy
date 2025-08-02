<?php
if (! function_exists('krishna_academy_support')) :
	function krishna_academy_support()
	{
		add_theme_support('block-templates');
		add_theme_support('block-template-parts');
		add_theme_support('site-logo');
		add_theme_support('menus');
		register_nav_menus([
			'primary' => __('Primary Menu', 'krishna_academy'),
		]);
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

		add_editor_style([
			'style.css',
			'assets/css/header.css',
			'assets/css/hero.css',
			'assets/css/front-page.css',
			'assets/css/footer.css'
		]);
	}
endif;
add_action('after_setup_theme', 'krishna_academy_support');

#include get_parent_theme_file_path('inc/helpers.php');


# insert styles
add_action('wp_enqueue_scripts', 'krishna_academy_enqueue_styles');

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
}

# Area named Loop for assigning parts of the template

add_filter('default_wp_template_part_areas', function ($areas) {
	$areas[] = array(
		'area'        => 'header',
		'area_tag'    => 'header',
		'label'       => __('Header', 'krishna_academy'),
		'description' => __('Site Header', 'krishna_academy'),
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


// 👇 Registration of a custom record type "event"
add_action('init', function () {
	register_post_type('event', [
		'label' => 'Events',
		'public' => true,
		'has_archive' => true,
		'show_in_rest' => true,
		'supports' => ['title', 'editor', 'thumbnail'],
		'rewrite' => ['slug' => 'events']
	]);

	register_post_type('promo', [
		'label' => 'Promo',
		'public' => true,
		'has_archive' => true,
		'show_in_rest' => true,
		'supports' => ['title', 'editor', 'thumbnail'],
	]);

	register_taxonomy('event_category', 'event', [
		'label' => 'Event Categories',
		'hierarchical' => true,
		'show_in_rest' => true,
		'rewrite' => ['slug' => 'event-category']
	]);
});

// 👇 Enabling multilingualism for CPT and taxonomies in Polylang
add_filter('pll_get_post_types', function ($post_types) {
	$post_types[] = 'event';
	return $post_types;
});

add_filter('pll_get_taxonomies', function ($taxonomies) {
	$taxonomies[] = 'event_category';
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


// Автоматическое подключение всех паттернов из папки patterns/
add_action('init', function () {
	$pattern_dir = get_theme_file_path('patterns');
	if (file_exists($pattern_dir)) {
		foreach (glob($pattern_dir . '/*.php') as $pattern_file) {
			require_once $pattern_file;
		}
	}
});

// Регистрация категории шаблонов для шаблонов Krishna Academy
register_block_pattern_category(
	'krishna-academy',
	[
		'label' => __('Krishna Academy', 'krishna_academy')
	]
);
