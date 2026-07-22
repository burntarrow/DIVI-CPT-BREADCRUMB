# Divi 5 CPT Breadcrumbs

A native Divi 5 module that builds breadcrumbs for any public WordPress post type and taxonomy, including complete parent/child taxonomy paths.

The resolver reads WordPress content relationships instead of splitting the current URL. A custom permalink such as:

```text
/services/residential/new-construction/custom-home-building/
```

can therefore render as:

```text
Home / Services / Residential / New Construction / Custom Home Building
```

## Features

- Native Divi 5 module registration and server-side rendering.
- **Post Type** dropdown populated from registered public or publicly queryable post types.
- **Taxonomy** dropdown populated from registered public or publicly queryable taxonomies.
- Taxonomy options filter to taxonomies registered for the selected post type.
- `Automatic` mode follows the current query and selects an appropriate taxonomy.
- `None` omits taxonomy terms.
- Complete hierarchical taxonomy ancestry.
- Hierarchical page and CPT ancestor support.
- CPT archives, the Posts page, taxonomy archives, search, 404, and generic archives.
- Yoast SEO and Rank Math primary-term support.
- Accessible `<nav>` and ordered-list markup.
- Optional Schema.org `BreadcrumbList` microdata.
- Divi typography, spacing, sizing, background, border, animation, and Custom CSS controls.

## Requirements

- WordPress 6.5 or newer.
- PHP 7.4 or newer.
- A current Divi 5 build with third-party module support.

## Installation

1. Upload and activate `divi-cpt-breadcrumbs.zip`.
2. Add **CPT Breadcrumbs** to a Divi layout or Theme Builder template.
3. Open **Content → Data Source** to select the post type and taxonomy.

## Data Source settings

### Post Type

- **Automatic (current request)** uses the current post, archive, or taxonomy context.
- A selected post type scopes the taxonomy dropdown and supplies a fallback in ambiguous archive contexts.

The endpoint includes every registered post type that is public or publicly queryable. Site-specific code can remove unwanted entries with `divi_cpt_breadcrumbs_post_type_is_selectable`.

### Taxonomy

- **Automatic** prefers an assigned hierarchical taxonomy, then another assigned taxonomy, then the first public taxonomy registered for the post type.
- **None** outputs no taxonomy crumbs.
- A selected taxonomy is used when registered for the current post type.

When a concrete post type is selected, the Visual Builder filters this dropdown to matching taxonomies. The saved taxonomy remains visible so a previous selection cannot silently disappear.

## Builder option discovery

The Visual Builder requests:

```text
/wp-json/divi-cpt-breadcrumbs/v1/data-sources
```

The endpoint requires a logged-in user with the WordPress `read` capability. It returns registered object labels, slugs, and taxonomy-to-post-type relationships; it does not return post content.

## Automatic term selection

The resolver uses this order:

1. `divi_cpt_breadcrumbs_primary_term_id`.
2. Yoast SEO primary-term metadata.
3. Rank Math primary-term metadata.
4. The deepest assigned term, with the lowest term ID as a deterministic tie-breaker.

Selecting a child term automatically includes every taxonomy ancestor.

## Filters

```php
add_filter( 'divi_cpt_breadcrumbs_items', 'my_breadcrumb_items', 10, 2 );
add_filter( 'divi_cpt_breadcrumbs_primary_term_id', 'my_primary_term', 10, 4 );
add_filter( 'divi_cpt_breadcrumbs_archive_url', 'my_archive_url', 10, 3 );
add_filter( 'divi_cpt_breadcrumbs_archive_label', 'my_archive_label', 10, 3 );
add_filter( 'divi_cpt_breadcrumbs_term_url', 'my_term_url', 10, 3 );
add_filter( 'divi_cpt_breadcrumbs_post_type_is_selectable', 'my_post_type_visibility', 10, 2 );
add_filter( 'divi_cpt_breadcrumbs_taxonomy_is_selectable', 'my_taxonomy_visibility', 10, 2 );
```

The completed dropdown arrays can also be changed with `divi_cpt_breadcrumbs_post_type_options` and `divi_cpt_breadcrumbs_taxonomy_options`.

## Development

```bash
npm install
npm run build
npm test
npm run zip
```

The installable ZIP is written to `dist/divi-cpt-breadcrumbs.zip`.

The repository includes PHP linting, JSON validation, bundle checks, smoke tests, and a Visual Builder source-build job in GitHub Actions.

## Compatibility note

Divi 5's third-party module API continues to evolve. Perform a staging runtime test against the exact Divi 5 build used by the target site before production deployment.

## License

GPL-2.0-or-later.
