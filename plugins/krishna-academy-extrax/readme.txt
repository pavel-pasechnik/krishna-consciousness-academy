=== Krishna Academy Extras ===
Contributors: Pavel Pasechnik
Tags: blocks, gutenberg, polylang, links, patterns
Requires at least: 6.3
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Custom dynamic blocks and helpers for the Krishna Academy theme (extracted from the theme to comply with Theme Review requirements).

== Description ==

*Krishna Academy Extras* is a small plugin with a custom dynamic block and helper functions for the **Krishna Academy** theme.

Main features:

- Block category `krishna-academy` in the editor.
- Dynamic block **KA Link** (`ka/link`): an editable link with text or a Polylang key and an optional image. Rendered server‑side (dynamic block) and suitable for use in the footer and templates.
- Pattern template **KA: Footer Link** for quickly adding a link to the footer.
- SVG upload allowed (extended MIME types).

The plugin is designed to work together with the *Krishna Academy* theme but can also be used separately. For key‑based text translations, the block uses `pll__()` when **Polylang** is installed (optional).

== Installation ==

1. Copy the plugin folder `krishna-academy-extrax` into `/wp-content/plugins/`.
2. Activate the plugin in **Admin → Plugins**.
3. (Optional) Install and activate **Polylang** if you want to substitute translations by keys in the `ka/link` block.

== Usage ==

### Block category
The plugin adds the `Krishna Academy` category (slug: `krishna-academy`) to the Block Inserter so that the blocks and patterns are easy to find.

### Block `ka/link` (KA Link)
Dynamic block with server‑side rendering. Attributes:

- `key` *(string)* — optional key for translation via Polylang. If specified and Polylang is enabled, the text will be taken from `pll__()`; otherwise, the default value will be used.
- `text` *(string)* — explicit link text, takes priority over `key`.
- `href` *(string)* — link URL. Defaults to `#`.
- `imgId` *(number)* / `imgURL` *(string)* / `imgAlt` *(string)* / `imgClass` *(string)* — parameters for an image placed to the left of the text (can be selected from the Media Library or entered manually).
- `linkClass` *(string)* — CSS class for the `<a>` tag (default `footer-link`).
- `className` *(string)* — CSS class for the block wrapper.
- `newTab` *(boolean)* — open in a new tab (default `true`).
- `rel` *(string)* — `rel` attribute (default `noopener`).
- `wrap` *(string)* — wrapper tag: `p` | `div` | `span` | `none` (default `p`).

The visual editor contains a settings panel and preview. In `wrap = none` mode, the block outputs only `<a>` without a wrapper.

### Predefined keys
For convenience, default values are provided when using `key`:

- `footer-link-1` → "Academy Certificate"
- `footer-link-2` → "Academy Charter"
- `footer-adress-1` → "Zoryanyi Lane, 16, Kyiv"

You can override the texts via Polylang or set an explicit `text` in the block attributes.

### Pattern `krishna-academy/footer-link`
Adds a single instance of the `ka/link` block in the `Krishna Academy` pattern category.

== Internationalization ==

> Note: The plugin and theme are separated intentionally (Theme Review).

== Frequently Asked Questions ==

= Is Polylang required? =
No. If Polylang is not installed, the block uses the keys to substitute default phrases or just takes `text`.

= Can I output the link without a wrapper? =
Yes — set `wrap` to `none`.

= How can I add my own predefined key? =
Extend the `$defaults` array in the PHP render callback of the `ka/link` block or simply set the text explicitly.

== Screenshots ==
1. KA Link block settings panel in the editor.
2. Example link in the footer.

== Changelog ==

= 1.0.0 =
* Initial release: block category, dynamic block `ka/link`, pattern `KA: Footer Link`, SVG upload support.

== Upgrade Notice ==

= 1.0.0 =
Initial version.
