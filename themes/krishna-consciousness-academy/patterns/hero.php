<?php

/**
 * Title: Hero
 * Slug: krishna-consciousness-academy/hero
 * Categories: hero
 * Block Types: core/template-part/hero
 * Description: A hero section.
 *
 * @package Krishna_Consciousness_Academy
 */

if (! function_exists('ka_cat_id')) {
	function ka_cat_id($slug)
	{
		$slug = sanitize_title((string) $slug);
		$term = get_term_by('slug', $slug, 'category');
		if ($term && ! is_wp_error($term)) {
			return (int) $term->term_id;
		}
		return 0;
	}
}

$cat_id = ka_cat_id('branding');

// Theme-relative image URLs (do not hardcode /wp-content/themes/...)
$img_mobile      = get_theme_file_uri('assets/images/hero-mobile.jpg');
$img_mobile_2x   = get_theme_file_uri('assets/images/hero-mobile@2x.jpg');
$img_tablet      = get_theme_file_uri('assets/images/hero-tablet.jpg');
$img_tablet_2x   = get_theme_file_uri('assets/images/hero-tablet@2x.jpg');
$img_desktop     = get_theme_file_uri('assets/images/hero-desktop.jpg');
$img_desktop_2x  = get_theme_file_uri('assets/images/hero-desktop@2x.jpg');

$srcset = sprintf(
	'%1$s 375w, %2$s 750w, %3$s 768w, %4$s 1536w, %5$s 1440w, %6$s 2880w',
	esc_url($img_mobile),
	esc_url($img_mobile_2x),
	esc_url($img_tablet),
	esc_url($img_tablet_2x),
	esc_url($img_desktop),
	esc_url($img_desktop_2x)
);

$alt = esc_attr__('Vaishnav studies', 'krishna-consciousness-academy');
?>

<!-- wp:group {"className":"hero-section container"} -->
<div class="wp-block-group hero-section container">
	<!-- wp:image {"sizeSlug":"full","className":"hero-picture"} -->
	<figure class="wp-block-image size-full hero-picture">
		<img
			srcset="<?php echo esc_attr($srcset); ?>"
			sizes="(min-width: 1440px) 1440px, (min-width: 768px) 768px, (max-width: 767px) 375px"
			src="<?php echo esc_url($img_mobile); ?>"
			alt="<?php echo $alt; ?>" />
	</figure>
	<!-- /wp:image -->

	<!-- wp:group {"className":"hero-promo"} -->
	<div class="wp-block-group hero-promo">
		<!-- wp:query {"queryId":1,"query":{"perPage":1,"pages":1,"offset":0,"postType":"post","postStatus":"publish","inherit":false,"orderBy":"date","order":"asc","categoryIds":[<?php echo (int) $cat_id; ?>]},"displayLayout":{"type":"grid","columns":1}} -->
		<!-- wp:post-template -->
		<!-- wp:post-content /-->
		<!-- /wp:post-template -->
		<!-- /wp:query -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->