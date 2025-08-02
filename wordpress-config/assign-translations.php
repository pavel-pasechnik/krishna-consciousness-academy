<?php
// assign-translations.php

// Set languages for each page
$translations = [
  'uk' => 2,    // ID About the Academy page
  'ru_RU' => 102,  // Page ID "About the Academy"
  'en_US' => 202,  // Page ID "About the Academy"
];

foreach ($translations as $lang => $post_id) {
  pll_set_post_language($post_id, $lang);
  echo "✅ Set language $lang for post ID $post_id\n";
}

pll_save_post_translations($translations);
echo "🔗 Translations linked for \"about\"\n";
