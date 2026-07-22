# Reno Plus Divi 5 Service Breadcrumbs

A native Divi 5 module for breadcrumb trails that understand the relationship between:

- Custom post type: `services`
- Hierarchical taxonomy: `service-category`
- Single-service permalink: `/services/{parent-term}/{child-term}/{post-name}/`

For a service assigned to **Residential > New Construction**, the module renders:

```text
Home > Services > Residential > New Construction > Post Title
```

## Why this plugin resolves the hierarchy correctly

The module does **not** split the current URL and guess what each segment represents. Instead, it reads WordPress data:

1. The current queried post, taxonomy term, or archive.
2. The `services` post type archive.
3. The selected `service-category` term.
4. The selected term's complete ancestor chain.
5. The current post title.

This remains correct when a slug changes, when only the child term is assigned, or when WordPress changes the generated permalink.

## Link behavior

With the permalink/taxonomy registration described in the project setup, a typical trail links as follows:

```text
Home              -> /
Services          -> /services/
Residential       -> /service-category/residential/
New Construction  -> /service-category/residential/new-construction/
Current service   -> displayed as the current item, not a link
```

The current service's canonical URL can still be:

```text
/services/residential/new-construction/post-title/
```

The module intentionally uses `get_term_link()` for term crumbs. It does not invent `/services/residential/` term-archive URLs unless the site has explicitly registered rewrite rules for those routes.

## Features

- Native Divi 5 module registration and Visual Builder controls.
- Correct parent/child taxonomy hierarchy.
- Deterministic deepest-term selection.
- Yoast SEO primary-term support.
- Rank Math primary-term support.
- Accessible `<nav>` and ordered-list markup.
- Optional Schema.org `BreadcrumbList` microdata.
- Divi controls for labels, separator, visibility, typography, background, spacing, sizing, borders, and custom CSS.
- Filters for site-specific primary-term and URL behavior.
- Graceful plugin loading when Divi is temporarily inactive.

## Install the release ZIP

1. Upload `reno-plus-divi5-breadcrumbs.zip` through **Plugins > Add New > Upload Plugin**.
2. Activate **Reno Plus Divi 5 Service Breadcrumbs**.
3. Open a Divi 5 layout or Theme Builder template.
4. Add the **Service Breadcrumbs** module.
5. Confirm the module's data-source values are:
   - Post type: `services`
   - Taxonomy: `service-category`
6. Save the layout and test a service single page.

If the CPT or taxonomy rewrite configuration changed at the same time, open **Settings > Permalinks** and click **Save Changes** once.

## Recommended Divi placement

The strongest setup is a Divi Theme Builder template assigned to **All Services**. Place the module near the top of the template, before the service title or hero content. The front-end renderer uses the actual queried service post even though the Visual Builder displays an illustrative sample trail.

## Visual Builder preview

The Visual Builder preview displays:

```text
Home > Services > Residential > New Construction > Current Service
```

That preview confirms styling and module settings. On the front end, PHP replaces it with the current WordPress query's real post, archive, and taxonomy hierarchy.

## Primary-term selection

When a service has one child term, that term is used. When both the parent and child are assigned, the deepest term is used and the parent is included only once.

When unrelated child terms are assigned, selection follows this order:

1. `reno_plus_divi5_breadcrumbs_primary_term_id` filter.
2. Yoast SEO primary term.
3. Rank Math primary term.
4. Deepest assigned term.
5. Lowest term ID as a deterministic tie-breaker.

For predictable URLs and breadcrumbs, assigning one logical child category per service remains the best content model.

## Filters

### Override the selected term

```php
add_filter(
    'reno_plus_divi5_breadcrumbs_primary_term_id',
    function ( $term_id, $post_id, $taxonomy, $terms ) {
        if ( 'service-category' !== $taxonomy ) {
            return $term_id;
        }

        return 123; // Term ID that is assigned to this post.
    },
    10,
    4
);
```

### Change a taxonomy crumb URL

```php
add_filter(
    'reno_plus_divi5_breadcrumbs_term_url',
    function ( $url, $term ) {
        // Return a custom registered route when your site supports it.
        return $url;
    },
    10,
    2
);
```

### Change the services archive URL

```php
add_filter(
    'reno_plus_divi5_breadcrumbs_archive_url',
    function ( $url, $post_type ) {
        return 'services' === $post_type ? home_url( '/services/' ) : $url;
    },
    10,
    2
);
```

### Modify the complete trail

```php
add_filter(
    'reno_plus_divi5_breadcrumbs_items',
    function ( $items, $args ) {
        return $items;
    },
    10,
    2
);
```

Each item contains:

```php
array(
    'label'   => 'New Construction',
    'url'     => 'https://example.com/service-category/residential/new-construction/',
    'current' => false,
    'type'    => 'term',
)
```

## Development

The repository includes source code and committed prebuilt assets. The installed release does not need Node.js or Composer.

Requirements for rebuilding the Visual Builder bundle:

- Node.js 18 or newer
- npm 10 or newer
- A staging WordPress installation running the target Divi 5 build

Commands:

```bash
npm install
npm run start
npm run build
npm run zip
```

`npm run zip` creates:

```text
dist/reno-plus-divi5-breadcrumbs.zip
```

## Validation

Run the local checks:

```bash
find . -name '*.php' -not -path './node_modules/*' -print0 | xargs -0 -n1 php -l
python3 -m json.tool modules-json/breadcrumbs/module.json >/dev/null
node --check scripts/bundle.js
php tests/resolver-smoke.php
```

The GitHub Actions workflow repeats the PHP, JSON, and committed-bundle checks on pushes and pull requests.

Before production deployment, complete the staging matrix in `tests/manual-test-matrix.md` against the exact Divi 5 version used by the site.

## Suggested GitHub workflow

Use a dedicated repository. A private repository is appropriate for a client-specific plugin; a public repository is appropriate when the module is intended for reuse.

Recommended branches and releases:

- `main`: deployable source and committed production assets.
- Feature branches: one change per branch.
- Pull requests: validation plus staging screenshots/test notes.
- Tagged releases: `v0.1.0`, `v0.1.1`, and so on.
- Attach `dist/reno-plus-divi5-breadcrumbs.zip` to each GitHub Release.

Do not develop directly in the installed production plugin directory. Build and test on staging, publish a tagged ZIP, then deploy that ZIP.

## Compatibility note

Divi 5's extension API has evolved over time. This scaffold follows Elegant Themes' current Divi 5 module-registration and Visual Builder package patterns. The committed PHP, JSON, and JavaScript have been statically validated, but the final runtime smoke test must be performed on the site's exact Divi 5 build.

## License

GPL-2.0-or-later.
