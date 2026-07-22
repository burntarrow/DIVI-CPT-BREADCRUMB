<?php
/**
 * Standalone smoke test for the breadcrumb resolver.
 * Run: php tests/resolver-smoke.php
 */

define( 'ABSPATH', __DIR__ );

class WP_Term {
	public $term_id;
	public $name;
	public $taxonomy;
	public function __construct( $id, $name, $taxonomy ) {
		$this->term_id = $id;
		$this->name = $name;
		$this->taxonomy = $taxonomy;
	}
}
class WP_Post {
	public $ID;
	public $post_type;
	public $post_title;
	public function __construct( $id, $post_type, $title ) {
		$this->ID = $id;
		$this->post_type = $post_type;
		$this->post_title = $title;
	}
}
class WP_Error {}

$GLOBALS['dcb_context'] = 'single';
$GLOBALS['dcb_service_post'] = new WP_Post( 101, 'services', 'Custom Home Building' );
$GLOBALS['dcb_page_parent'] = new WP_Post( 201, 'page', 'About' );
$GLOBALS['dcb_page_child'] = new WP_Post( 202, 'page', 'Team' );
$GLOBALS['dcb_post'] = $GLOBALS['dcb_service_post'];
$GLOBALS['dcb_terms'] = array(
	1 => new WP_Term( 1, 'Residential', 'service-category' ),
	2 => new WP_Term( 2, 'New Construction', 'service-category' ),
	3 => new WP_Term( 3, 'Renovations', 'service-category' ),
);
$GLOBALS['dcb_assigned'] = array( 1, 2 );
$GLOBALS['dcb_primary'] = 0;
$GLOBALS['dcb_filter_calls'] = array();
$GLOBALS['dcb_queried_term'] = $GLOBALS['dcb_terms'][2];

function __( $text ) { return $text; }
function wp_parse_args( $args, $defaults ) { return array_merge( $defaults, $args ); }
function sanitize_key( $key ) { return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $key ) ); }
function wp_strip_all_tags( $text ) { return strip_tags( (string) $text ); }
function home_url() { return 'https://example.com/'; }
function is_front_page() { return false; }
function is_search() { return false; }
function get_search_query() { return ''; }
function is_404() { return false; }
function is_home() { return 'home' === $GLOBALS['dcb_context']; }
function is_page() { return 'page' === $GLOBALS['dcb_context']; }
function is_category() { return false; }
function is_tag() { return false; }
function is_tax() { return 'taxonomy' === $GLOBALS['dcb_context']; }
function is_archive() { return in_array( $GLOBALS['dcb_context'], array( 'archive', 'taxonomy' ), true ); }
function get_the_archive_title() { return 'Archive'; }
function is_singular() { return in_array( $GLOBALS['dcb_context'], array( 'single', 'page' ), true ); }
function is_post_type_archive( $post_type = '' ) {
	if ( 'archive' !== $GLOBALS['dcb_context'] ) {
		return false;
	}
	return '' === $post_type || 'services' === $post_type;
}
function get_queried_object() {
	return 'taxonomy' === $GLOBALS['dcb_context'] ? $GLOBALS['dcb_queried_term'] : $GLOBALS['dcb_post'];
}
function get_post( $post_id = null ) {
	if ( 201 === (int) $post_id ) {
		return $GLOBALS['dcb_page_parent'];
	}
	return $GLOBALS['dcb_post'];
}
function get_query_var( $key ) {
	return 'post_type' === $key && 'archive' === $GLOBALS['dcb_context'] ? 'services' : '';
}
function post_type_exists( $post_type ) {
	return in_array( $post_type, array( 'post', 'page', 'services' ), true );
}
function taxonomy_exists( $taxonomy ) { return 'service-category' === $taxonomy; }
function is_object_in_taxonomy( $post_type, $taxonomy ) {
	return 'services' === $post_type && 'service-category' === $taxonomy;
}
function get_object_taxonomies( $post_type ) {
	if ( 'services' !== $post_type ) {
		return array();
	}
	return array(
		'service-category' => (object) array(
			'name' => 'service-category',
			'public' => true,
			'publicly_queryable' => true,
			'hierarchical' => true,
		),
	);
}
function get_taxonomy( $taxonomy ) {
	if ( 'service-category' !== $taxonomy ) {
		return false;
	}
	return (object) array(
		'name' => 'service-category',
		'object_type' => array( 'services' ),
	);
}
function get_post_type_object( $post_type ) {
	$objects = array(
		'post' => (object) array(
			'labels' => (object) array( 'name' => 'Posts' ),
			'label' => 'Posts',
			'has_archive' => false,
			'hierarchical' => false,
		),
		'page' => (object) array(
			'labels' => (object) array( 'name' => 'Pages' ),
			'label' => 'Pages',
			'has_archive' => false,
			'hierarchical' => true,
		),
		'services' => (object) array(
			'labels' => (object) array( 'name' => 'Services' ),
			'label' => 'Services',
			'has_archive' => true,
			'hierarchical' => false,
		),
	);
	return isset( $objects[ $post_type ] ) ? $objects[ $post_type ] : false;
}
function get_post_type_archive_link( $post_type ) {
	return 'services' === $post_type ? 'https://example.com/services/' : false;
}
function get_option() { return 0; }
function get_post_ancestors( $post ) {
	return 202 === (int) $post->ID ? array( 201 ) : array();
}
function get_the_terms() {
	return array_map(
		static function ( $id ) { return $GLOBALS['dcb_terms'][ $id ]; },
		$GLOBALS['dcb_assigned']
	);
}
function apply_filters( $tag, $value ) {
	$GLOBALS['dcb_filter_calls'][] = $tag;
	return $value;
}
function get_post_meta( $post_id, $key ) {
	if ( '_yoast_wpseo_primary_service-category' === $key ) {
		return $GLOBALS['dcb_primary'];
	}
	return 0;
}
function get_ancestors( $term_id ) {
	return in_array( (int) $term_id, array( 2, 3 ), true ) ? array( 1 ) : array();
}
function get_term( $term_id ) { return $GLOBALS['dcb_terms'][ $term_id ]; }
function is_wp_error( $value ) { return $value instanceof WP_Error; }
function get_term_link( $term ) {
	$slugs = array( 1 => 'residential', 2 => 'new-construction', 3 => 'renovations' );
	if ( 1 === (int) $term->term_id ) {
		return 'https://example.com/service-category/residential/';
	}
	return 'https://example.com/service-category/residential/' . $slugs[ $term->term_id ] . '/';
}
function get_the_title( $post ) {
	if ( is_int( $post ) ) {
		$post = get_post( $post );
	}
	return $post->post_title;
}
function get_permalink( $post ) {
	if ( is_int( $post ) ) {
		$post = get_post( $post );
	}
	if ( 101 === (int) $post->ID ) {
		return 'https://example.com/services/residential/new-construction/custom-home-building/';
	}
	if ( 201 === (int) $post->ID ) {
		return 'https://example.com/about/';
	}
	if ( 202 === (int) $post->ID ) {
		return 'https://example.com/about/team/';
	}
	return 'https://example.com/';
}

require dirname( __DIR__ ) . '/includes/class-breadcrumb-resolver.php';

use BurntArrow\DiviCptBreadcrumbs\BreadcrumbResolver;

function assert_labels( $expected, $items, $message ) {
	$actual = array_column( $items, 'label' );
	if ( $expected !== $actual ) {
		fwrite( STDERR, $message . "\nExpected: " . json_encode( $expected ) . "\nActual: " . json_encode( $actual ) . "\n" );
		exit( 1 );
	}
}

$resolver = new BreadcrumbResolver();
$auto_args = array(
	'post_type' => 'auto',
	'taxonomy' => 'auto',
	'home_label' => 'Home',
	'archive_label' => '',
);

$items = $resolver->resolve( $auto_args );
assert_labels(
	array( 'Home', 'Services', 'Residential', 'New Construction', 'Custom Home Building' ),
	$items,
	'Automatic single hierarchy failed.'
);

foreach (
	array(
		'divi_cpt_breadcrumbs_archive_url',
		'divi_cpt_breadcrumbs_term_url',
		'divi_cpt_breadcrumbs_primary_term_id',
		'divi_cpt_breadcrumbs_items',
	) as $filter_name
) {
	if ( ! in_array( $filter_name, $GLOBALS['dcb_filter_calls'], true ) ) {
		fwrite( STDERR, "Expected compatibility filter was not called: {$filter_name}\n" );
		exit( 1 );
	}
}

$GLOBALS['dcb_context'] = 'taxonomy';
$items = $resolver->resolve( $auto_args );
assert_labels(
	array( 'Home', 'Services', 'Residential', 'New Construction' ),
	$items,
	'Automatic taxonomy hierarchy failed.'
);

$GLOBALS['dcb_context'] = 'archive';
$items = $resolver->resolve( $auto_args );
assert_labels(
	array( 'Home', 'Services' ),
	$items,
	'Post type archive failed.'
);

$GLOBALS['dcb_context'] = 'single';
$GLOBALS['dcb_assigned'] = array( 2, 3 );
$GLOBALS['dcb_primary'] = 3;
$items = $resolver->resolve( $auto_args );
assert_labels(
	array( 'Home', 'Services', 'Residential', 'Renovations', 'Custom Home Building' ),
	$items,
	'Primary term selection failed.'
);

$items = $resolver->resolve(
	array_merge(
		$auto_args,
		array( 'taxonomy' => 'none' )
	)
);
assert_labels(
	array( 'Home', 'Services', 'Custom Home Building' ),
	$items,
	'Taxonomy omission failed.'
);

$GLOBALS['dcb_context'] = 'page';
$GLOBALS['dcb_post'] = $GLOBALS['dcb_page_child'];
$items = $resolver->resolve( $auto_args );
assert_labels(
	array( 'Home', 'About', 'Team' ),
	$items,
	'Hierarchical page trail failed.'
);

echo "Resolver smoke tests passed.\n";
