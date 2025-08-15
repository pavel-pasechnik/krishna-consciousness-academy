<?php

/**
 * Plugin Name: Krishna Academy Extras
 * Description: Custom dynamic blocks and helpers for the Krishna Academy theme (kept out of the theme to satisfy Theme Review).
 * Author: Pavel Pasechnik
 * Version: 1.0.0
 * Text Domain: krishna-academy-extrax
 * Requires at least: 6.3
 * Requires PHP: 7.4
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI: https://github.com/pavel-pasechnik/wordpress/tree/main/plugins/krishna-academy-extras
 * GitHub Plugin URI: https://github.com/pavel-pasechnik/wordpress
 * Primary Branch: main
 * Directory: plugins/krishna-academy-extras
 */

if (!defined('ABSPATH')) {
  exit;
}


add_action('init', function () {
  $handle = 'ka-extras';
  $src = plugins_url('css/style.css', __FILE__);
  $ver = file_exists(__DIR__ . '/css/style.css') ? filemtime(__DIR__ . '/css/style.css') : '1.0.0';
  wp_register_style($handle, $src, [], $ver);
});

add_action('enqueue_block_assets', function () {
  wp_enqueue_style('ka-extras');
});

add_filter('block_categories_all', function ($categories, $post) {
  foreach ($categories as $cat) {
    if (!empty($cat['slug']) && $cat['slug'] === 'krishna-academy') {
      return $categories; // already added
    }
  }
  $categories[] = [
    'slug'  => 'krishna-academy',
    'title' => __('Krishna Academy', 'krishna-academy-extrax'),
    'icon'  => null,
  ];
  return $categories;
}, 10, 2);

// === Dynamic block: ka/link (editable link with optional image and Polylang key) ===
add_action('init', function () {
  register_block_type('ka/link', [
    'api_version'     => 2,
    'render_callback' => function ($attrs, $content) {
      $attrs = wp_parse_args($attrs, [
        'key'        => '',
        'href'       => '',
        'text'       => '',
        'imgId'      => 0,
        'imgURL'     => '',
        'imgAlt'     => '',
        'className'  => '',
        'linkClass'  => 'footer-link',
        'imgClass'   => '',
        'newTab'     => true,
        'rel'        => 'noopener',
        'wrap'       => 'p', // p|div|span|none
      ]);

      $defaults = [
        'footer-link-1'   => 'Свідоцтво академії',
        'footer-link-2'   => 'Статут академії',
        'footer-adress-1' => 'Зоряний провулок, 16, Київ',
      ];

      $text = (string)($attrs['text'] ?? '');
      if ($text === '' && !empty($attrs['key'])) {
        $default = $defaults[$attrs['key']] ?? $attrs['key'];
        $text = function_exists('pll__') ? pll__($default) : $default;
      }

      $img_html = '';
      if (!empty($attrs['imgId'])) {
        $img_html = wp_get_attachment_image((int)$attrs['imgId'], 'full', false, [
          'class' => esc_attr($attrs['imgClass'] ?? ''),
          'alt'   => esc_attr($attrs['imgAlt'] ?? ''),
        ]);
      } elseif (!empty($attrs['imgURL'])) {
        $img_html = sprintf(
          '<img src="%s" alt="%s"%s />',
          esc_url($attrs['imgURL']),
          esc_attr($attrs['imgAlt'] ?? ''),
          !empty($attrs['imgClass']) ? ' class="' . esc_attr($attrs['imgClass']) . '"' : ''
        );
      }

      $href      = !empty($attrs['href']) ? $attrs['href'] : '#';
      $target    = !empty($attrs['newTab']) ? ' target="_blank"' : '';
      $rel       = !empty($attrs['rel']) ? ' rel="' . esc_attr($attrs['rel']) . '"' : '';
      $linkClass = !empty($attrs['linkClass']) ? ' class="' . esc_attr($attrs['linkClass']) . '"' : '';

      $inner  = $img_html . ($text !== '' ? (($img_html !== '' ? ' ' : '') . esc_html($text)) : '');
      $anchor = sprintf('<a href="%s"%s%s%s>%s</a>', esc_url($href), $target, $rel, $linkClass, $inner);

      $wrapTag = in_array($attrs['wrap'], ['p', 'div', 'span', 'none'], true) ? $attrs['wrap'] : 'p';
      $wrapperClass = 'wp-block-ka-link' . (!empty($attrs['className']) ? ' ' . esc_attr($attrs['className']) : '');

      if ($wrapTag === 'none') {
        return $anchor;
      }
      return sprintf('<%1$s class="%2$s">%3$s</%1$s>', $wrapTag, $wrapperClass, $anchor);
    },
    'attributes' => [
      'key'       => ['type' => 'string',  'default' => ''],
      'href'      => ['type' => 'string',  'default' => ''],
      'text'      => ['type' => 'string',  'default' => ''],
      'imgId'     => ['type' => 'integer', 'default' => 0],
      'imgURL'    => ['type' => 'string',  'default' => ''],
      'imgAlt'    => ['type' => 'string',  'default' => ''],
      'className' => ['type' => 'string',  'default' => ''],
      'linkClass' => ['type' => 'string',  'default' => 'footer-link'],
      'imgClass'  => ['type' => 'string',  'default' => ''],
      'newTab'    => ['type' => 'boolean', 'default' => true],
      'rel'       => ['type' => 'string',  'default' => 'noopener'],
      'wrap'      => ['type' => 'string',  'default' => 'p'],
    ],
    'supports' => [
      'html'      => false,
      'anchor'    => false,
      'className' => true,
      'inserter'  => true,
    ],
  ]);
});

add_action('init', function () {
  if (!function_exists('register_block_pattern') || !function_exists('register_block_pattern_category')) {
    return;
  }
  // Ensure our pattern category exists
  register_block_pattern_category('krishna-academy', [
    'label' => __('Krishna Academy', 'krishna-academy-extrax'),
  ]);

  // Simple pattern showcasing KA Link
  register_block_pattern('krishna-academy/footer-link', [
    'title'       => __('KA: Footer Link', 'krishna-academy-extrax'),
    'description' => __('Ссылка для подвала на основе блока KA Link.', 'krishna-academy-extrax'),
    'categories'  => ['krishna-academy'],
    'content'     => '<!-- wp:ka/link {"key":"footer-link-1","href":"#","linkClass":"footer-link"} /-->',
  ]);
});

// Inline editor UI (no build step)
add_action('enqueue_block_editor_assets', function () {
  $handle = 'ka-link-block';
  // Register an empty script *with dependencies* so WordPress loads core block packages first
  wp_register_script(
    $handle,
    '',
    ['wp-blocks', 'wp-element', 'wp-i18n', 'wp-components', 'wp-block-editor', 'wp-editor'],
    '1.0.0',
    true
  );
  wp_enqueue_script($handle);
  if (function_exists('wp_set_script_translations')) {
    wp_set_script_translations($handle, 'krishna-academy-extrax', plugin_dir_path(__FILE__) . 'languages');
  }
  $lines = [
    "(function(wp){",
    "  const { registerBlockType } = wp.blocks;",
    "  const { __ } = wp.i18n;",
    "  const { InspectorControls, MediaUpload, useBlockProps } = wp.blockEditor || wp.editor;",
    "  const { PanelBody, TextControl, ToggleControl, SelectControl, Button } = wp.components;",
    "",
    "  registerBlockType('ka/link', {",
    "    apiVersion: 2,",
    "    title: __('KA Link', 'krishna-academy-extrax'),",
    "    description: __('Ссылка с текстом/Polylang-ключом и опциональной картинкой', 'krishna-academy-extrax'),",
    "    icon: 'admin-links',",
    "    // Move the block to its own theme category so that it is clearly visible in any editor (including Site Editor/Patterns).",
    "    category: 'krishna-academy',",
    "    keywords: ['link', 'ссылка', 'footer', 'ka'],",
    "    supports: { html: false, anchor: false, inserter: true },",
    "    attributes: {",
    "      key:       { type: 'string',  default: '' },",
    "      href:      { type: 'string',  default: '' },",
    "      text:      { type: 'string',  default: '' },",
    "      imgId:     { type: 'number',  default: 0 },",
    "      imgURL:    { type: 'string',  default: '' },",
    "      imgAlt:    { type: 'string',  default: '' },",
    "      className: { type: 'string',  default: '' },",
    "      linkClass: { type: 'string',  default: 'footer-link' },",
    "      imgClass:  { type: 'string',  default: '' },",
    "      newTab:    { type: 'boolean', default: true },",
    "      rel:       { type: 'string',  default: 'noopener' },",
    "      wrap:      { type: 'string',  default: 'p' },",
    "    },",
    "    edit: function(props){",
    "      const { attributes, setAttributes } = props;",
    "      const blockProps = useBlockProps();",
    "      const previewText = attributes.text || (attributes.key ? '['+attributes.key+']' : __('(нет текста)', 'krishna-academy-extrax'));",
    "",
    "      return wp.element.createElement(wp.element.Fragment, {},",
    "        wp.element.createElement(InspectorControls, {},",
    "          wp.element.createElement(PanelBody, {title: __('Текст и Polylang', 'krishna-academy-extrax'), initialOpen: true},",
    "            wp.element.createElement(TextControl, { label: __('Polylang key (опционально)', 'krishna-academy-extrax'), value: attributes.key,  onChange: (v)=>setAttributes({key:v}) }),",
    "            wp.element.createElement(TextControl, { label: __('Текст (перебивает key)', 'krishna-academy-extrax'),     value: attributes.text, onChange: (v)=>setAttributes({text:v}) }),",
    "          ),",
    "          wp.element.createElement(PanelBody, {title: __('Ссылка', 'krishna-academy-extrax')},",
    "            wp.element.createElement(TextControl, { label: __('URL', 'krishna-academy-extrax'), value: attributes.href, onChange: (v)=>setAttributes({href:v}) }),",
    "            wp.element.createElement(ToggleControl, { label: __('Открывать в новой вкладке', 'krishna-academy-extrax'), checked: !!attributes.newTab, onChange: (v)=>setAttributes({newTab: !!v}) }),",
    "            wp.element.createElement(TextControl, { label: __('rel', 'krishna-academy-extrax'),  value: attributes.rel, onChange: (v)=>setAttributes({rel:v}) }),",
    "            wp.element.createElement(TextControl, { label: __('Класс ссылки', 'krishna-academy-extrax'), value: attributes.linkClass, onChange: (v)=>setAttributes({linkClass:v}) }),",
    "            wp.element.createElement(SelectControl, { label: __('Обёртка', 'krishna-academy-extrax'), value: attributes.wrap, options: [",
    "              {label:'p', value:'p'},{label:'div', value:'div'},{label:'span', value:'span'},{label:'none', value:'none'}",
    "            ], onChange: (v)=>setAttributes({wrap:v}) }),",
    "          ),",
    "          wp.element.createElement(PanelBody, {title: __('Картинка', 'krishna-academy-extrax')},",
    "            wp.element.createElement(MediaUpload, {",
    "              onSelect: (media)=>{ setAttributes({ imgId: media.id||0, imgURL: media.url||'', imgAlt: media.alt||'' }); },",
    "              allowedTypes: ['image'],",
    "              value: attributes.imgId,",
    "              render: ({open}) => wp.element.createElement(Button, {onClick: open, isSecondary:true}, attributes.imgId ? __('Заменить', 'krishna-academy-extrax') : __('Выбрать', 'krishna-academy-extrax'))",
    "            }),",
    "            wp.element.createElement(TextControl, { label: __('URL картинки (если не используете медиа)', 'krishna-academy-extrax'), value: attributes.imgURL, onChange: (v)=>setAttributes({imgURL:v}) }),",
    "            wp.element.createElement(TextControl, { label: __('alt', 'krishna-academy-extrax'), value: attributes.imgAlt, onChange: (v)=>setAttributes({imgAlt:v}) }),",
    "            wp.element.createElement(TextControl, { label: __('Класс картинки', 'krishna-academy-extrax'), value: attributes.imgClass, onChange: (v)=>setAttributes({imgClass:v}) }),",
    "          )",
    "        ),",
    "        wp.element.createElement('div', Object.assign({}, blockProps, {className: (blockProps.className||'') + ' ka-link-editor-preview'}),",
    "          attributes.imgURL ? wp.element.createElement('img', {src: attributes.imgURL, alt: attributes.imgAlt, className: attributes.imgClass || undefined}) :",
    "            (attributes.imgId ? wp.element.createElement('div', {}, __('(Картинка из медиа будет показана на сайте)', 'krishna-academy-extrax')) : null),",
    "          ' ',",
    "          wp.element.createElement('a', { href: attributes.href || '#', target: attributes.newTab ? '_blank' : undefined, rel: attributes.rel || undefined, className: attributes.linkClass || undefined }, previewText)",
    "        )",
    "      );",
    "    },",
    "    save: function(){ return null; }",
    "  });",
    "})(window.wp);",
  ];
  $js = implode("\n", $lines);
  wp_add_inline_script($handle, $js);
});

// Optional: SVG uploads moved to plugin territory (enable if you need SVG support);
add_filter('upload_mimes', function ($mimes) {
  $mimes['svg'] = 'image/svg+xml';
  return $mimes;
});
