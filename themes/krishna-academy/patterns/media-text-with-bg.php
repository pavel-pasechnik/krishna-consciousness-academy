<?php
register_block_pattern(
  'krishna-academy/media-text-with-bg',
  [
    'title'       => 'Media and Text with Background',
    'description' => 'Ready-made block with background and adaptability.',
    'content'     => '
                      <!-- wp:media-text {"mediaPosition":"left","className":"media-text-bg"} -->
<div class="wp-block-media-text has-media-on-the-left is-stacked-on-mobile media-text-bg">
  <figure class="wp-block-media-text__media">
  </figure>
  <div class="wp-block-media-text__content">
    <!-- wp:heading --><h2>Title</h2><!-- /wp:heading -->
    <!-- wp:paragraph --><p>Block description. This can contain text, a button, a list, etc.</p><!-- /wp:paragraph -->
  </div>
</div>
    ',
    'categories'  => ['krishna-academy'],
  ]
);
