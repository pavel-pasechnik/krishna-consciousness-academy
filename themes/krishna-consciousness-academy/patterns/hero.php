<?php

/**
 * Title: Hero
 * Slug: krishna-consciousness-academy/hero
 * Categories: hero
 * Block Types: core/template-part/hero
 * Description: A hero section.
 *
 * @package WordPress
 * @subpackage Twenty_Twenty_Five
 * @since Krishna Consciousness Academy 1.0
 */

if (! function_exists('ka_cat_id')) {
	function ka_cat_id($slug)
	{
		$slug = sanitize_title((string) $slug);
		$term = get_term_by('slug', $slug, 'category');
		if ($term && ! is_wp_error($term)) {
			return (int) $term->term_id;
		}
	}
}

$cat_id = ka_cat_id('branding');
?>

<!-- wp:group -->
<div class="wp-block-group hero-section container">
	<!-- wp:html -->
	<img
		class="wp-block-image hero-picture"
		srcset="
      /wp-content/themes/krishna-academy/assets/images/hero-mobile.jpg      375w,
      /wp-content/themes/krishna-academy/assets/images/hero-mobile@2x.jpg   750w,
      /wp-content/themes/krishna-academy/assets/images/hero-tablet.jpg      768w,
      /wp-content/themes/krishna-academy/assets/images/hero-tablet@2x.jpg  1536w,
      /wp-content/themes/krishna-academy/assets/images/hero-desktop.jpg    1440w,
      /wp-content/themes/krishna-academy/assets/images/hero-desktop@2x.jpg 2880w
    "
		sizes="(min-width: 1440px) 1440px, (min-width: 768px) 768px, (max-width: 767px) 375px"
		src="/wp-content/themes/krishna-academy/assets/images/hero-mobile.jpg"
		alt="Vaishnav studies" />
	<!-- /wp:html -->
	<!-- wp:group -->
	<div class="wp-block-group hero-promo">
		<!-- wp:query {"queryId":1,"query":{"perPage":1,"pages":1,"offset":0,"postType":"post","postStatus":"publish","inherit":false,"orderBy":"date","order":"asc","taxQuery":{"category":[<?php echo $cat_id; ?>]}} ,"displayLayout":{"type":"grid","columns":1}} -->
		<!-- wp:post-template -->
		<!-- wp:post-content /-->
		<!-- /wp:post-template -->
		<!-- /wp:query -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->