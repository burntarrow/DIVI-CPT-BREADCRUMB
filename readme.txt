=== Divi 5 CPT Breadcrumbs ===
Contributors: burntarrow
Tags: divi, divi 5, breadcrumbs, custom post type, taxonomy
Requires at least: 6.5
Requires PHP: 7.4
Stable tag: 0.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A native Divi 5 breadcrumb module for any public post type and taxonomy.

== Description ==

Adds a CPT Breadcrumbs module to Divi 5. The Data Source group contains Post Type and Taxonomy dropdowns populated from registered public or publicly queryable WordPress objects. Selecting a post type filters the taxonomy dropdown to compatible taxonomies.

The resolver reads the queried post, post type archive, assigned terms, and taxonomy hierarchy instead of splitting URL segments. It supports hierarchical term paths, hierarchical posts, accessible markup, optional Schema.org microdata, and Yoast or Rank Math primary terms.

== Installation ==

1. Upload and activate the plugin.
2. Add the CPT Breadcrumbs module to a Divi 5 layout or Theme Builder template.
3. In Content > Data Source, keep Automatic mode or select a post type and taxonomy.

== Frequently Asked Questions ==

= Which post types appear? =

Every registered post type that is public or publicly queryable. A filter is available to exclude site-specific entries.

= Which taxonomies appear? =

Every public or publicly queryable taxonomy associated with at least one post type. Selecting a concrete post type filters the builder list to matching taxonomies.

= What happens when a post has multiple terms? =

Yoast SEO or Rank Math primary-term metadata wins. Otherwise the deepest assigned term is selected deterministically.

= Does the plugin parse the URL? =

No. It uses WordPress query and taxonomy APIs, so custom rewrite structures remain compatible.

== Changelog ==

= 0.2.0 =
* Generalized the module for any public post type and taxonomy.
* Added dynamically populated Post Type and Taxonomy dropdowns.
* Added Automatic and None data-source options.
* Added hierarchical page and CPT ancestor support.
* Added a protected REST endpoint for Visual Builder option discovery.
