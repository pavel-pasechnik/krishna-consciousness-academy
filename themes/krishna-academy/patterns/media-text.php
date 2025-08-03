<?php
register_block_pattern(
  'krishna-academy/media-text',
  [
    'title'       => 'Media and Text',
    'description' => 'Ready-made block with adaptability.',
    'content'     => '
                      <!-- wp:media-text {"mediaPosition":"left","className":"media-text"} -->
                      <div class="wp-block-media-text has-media-on-the-left media-text">
                        <figure class="wp-block-media-text__media">
                        </figure>
                        <div class="wp-block-media-text__content">
                          <!-- wp:heading {"level":3}  --><h3>Title</h3><!-- /wp:heading -->
                          <!-- wp:paragraph --><p>Block description. This can contain text, a button, a list, etc.</p><!-- /wp:paragraph -->
                        </div>
                      </div>
    ',
    'categories'  => ['krishna-academy'],
  ]
);
