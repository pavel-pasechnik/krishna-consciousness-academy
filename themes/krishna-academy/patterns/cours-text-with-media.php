<?php
register_block_pattern(
  'krishna-academy/cours-text-with-media',
  [
    'title'       => 'Media and Text with background',
    'description' => 'Ready-made block with background and adaptability.',
    'content'     => '<!-- wp:media-text {"mediaPosition":"left","className":"media-text-bg"} -->
                      <div class="wp-block-media-text has-media-on-the-left media-text-bg">
                        <figure class="wp-block-media-text__media">
                        </figure>
                        <div class="wp-block-media-text__content">
                        <!-- wp:paragraph --><p>Block description. This can contain text, a button, a list, etc.</p><!-- /wp:paragraph -->
                        <!-- wp:heading {"level":3}  --><h3>Title</h3><!-- /wp:heading -->
                        </div>
                      </div>
',
    'categories'  => ['krishna-academy'],
  ]
);
