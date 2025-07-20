<?php

/**
 * Title: Header
 * Slug: krishna-academy/header
 * Categories: header
 * Description:
 * Viewport Width:
 * Inserter: true
 * Keywords: header
 * Block Types:
 * Post Types:
 * Template Types:
 */

<?php

register_block_pattern(
    'krishna-academy/header',
    [
        'title'       => __('Header', 'krishna-academy'),
        'categories'  => ['header'],
        'inserter'    => true,
        'content'     => file_get_contents(
            get_theme_file_path('parts/header.html')
        ),
    ]
);