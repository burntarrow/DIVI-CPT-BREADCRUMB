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

$GLOBALS['rp_test_context'] = 'single';
$GLOBALS['rp_test_post'] = new WP_Post( 101, 'services', 'Custom Home Building' );
$GLOBALS['rp_test_terms'] = array(
	1 => new WP_Term( 1, 'Residential', 'service-category' ),
	2 => new WP_Term( 2, 'New Construction', 'service-category' ),
	3 => new WP_Term( 3, 'Renovations', 'service-category' ),
);
$GLOBALS['rp_test_assigned'] = array( 1, 2 );
$GLOBALS['rp_test_primary'] = 0;
$GLOBALS['rp_test_queried_term'] = $GLOBALS['rp_test_terms'][2];

function __( $text ) { return $text; }
function wp_parse_args( $args, $defaults ) { return array_merge( $defaults, $args ); }
function sanitize_key( $key ) { return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( $key ) ); }
function wp_strip_all_tags( $text ) { return strip_tags( $text ); }
function home_url() { return 'https://example.com/'; }
function is_front_page() { return false; }
function is_post_type_archive( $post_type = '' ) { return 'archive' === $GLOBALS['rp_test_context'] && 'services' === $post_type; }
function is_tax( $taxonomy = '' ) { return 'taxonomy' === $GLOBALS['rp_test_context'] && 'service-category' === $taxonomy; }
function is_singular( $post_type = '' ) {
	if ( 'single' !== $GLOBALS['rp_test_context'] ) { return false; }
	return '' === $post_type || 'services' === $post_type;
}
function is_page() { return false; }
function is_search() { return false; }
function is_404() { return false; }
function is_archive() { return false; }
function is_home() { return false; }
function get_queried_object() {
	return 'taxonomy' === $GLOBALS['rp_test_context'] ? $GLOBALS['rp_test_queried_term'] : $GLOBALS['rp_test_post'];
}
function get_post() { return $GLOBALS['rp_test_post']; }
function get_post_type_object() {
	return (object) array(
		'labels' => (object) array( 'name' => 'Services' ),
		'label' => 'Services',
		'has_archive' => true,
	);
}
function get_post_type_archive_link() { return 'https://example.com/services/'; }
function taxonomy_exists( $taxonomy ) { return 'service-category' === $taxonomy; }
function get_the_terms() {
	return array_map(
		static function ( $id ) { return $GLOBALS['rp_test_terms'][ $id ]; },
		$GLOBALS['rp_test_assigned']
	);
}
function apply_filters( $tag, $value ) { return $value; }
function get_post_meta( $post_id, $key ) {
	if ( '_yoast_wpseo_primary_service-category' === $key ) {
		return $GLOBALS['rp_test_primary'];
	}
	return 0;
}
function get_ancestors( $term_id ) {
	return in_array( (int) $term_id, array( 2, 3 ), true ) ? array( 1 ) : array();
}
function get_term( $term_id ) { return $GLOBALS['rp_test_terms'][ $term_id ]; }
function is_wp_error( $value ) { return $value instanceof WP_Error; }
function get_term_link( $term ) {
	$slugs = array( 1 => 'residential', 2 => 'new-construction', 3 => 'renovations' );
	if ( 1 === (int) $term->term_id ) {
		return 'https://example.com/service-category/residential/';
	}
	return 'https://example.com/service-category/residential/' . $slugs[ $term->term_id ] . '/';
}
function get_the_title( $post ) { return $post->post_title; }
function get_permalink( $post ) {
	if ( 101 === (int) $post->ID ) {
		return 'https://example.com/services/residential/new-construction/custom-home-building/';
	}
	return 'https://example.com/';
}

require dirname( __DIR__ ) . '/includes/class-breadcrumb-resolver.php';

use RenoPlus\Divi5Breadcrumbs\BreadcrumbResolver;

function assert_labels( $expected, $items, $message ) {
	$actual = array_column( $items, 'label' );
	if ( $expected !== $actual ) {
		fwrite( STDERR, $message . "\nExpected: " . json_encode( $expected ) . "\nActual: " . json_encode( $actual ) . "\n" );
		exit( 1 );
	}
}

$resolver = new BreadcrumbResolver();
$args = array(
	'post_type' => 'services',
	'taxonomy' => 'service-category',
	'home_label' => 'Home',
	'archive_label' => 'Services',
);

$items = $resolver->resolve( $args );
assert_labels(
	array( 'Home', 'Services', 'Residential', 'New Construction', 'Custom Home Building' ),
	$items,
	'Single service hierarchy failed.'
);

$GLOBALS['rp_test_context'] = 'taxonomy';
$items = $resolver->resolve( $args );
assert_labels(
	array( 'Home', 'Services', 'Residential', 'New Construction' ),
	$items,
	'Taxonomy hierarchy failed.'
);

$GLOBALS['rp_test_context'] = 'single';
$GLOBALS['rp_test_assigned'] = array( 2, 3 );
$GLOBALS['rp_test_primary'] = 3;
$items = $resolver->resolve( $args );
assert_labels(
	array( 'Home', 'Services', 'Residential', 'Renovations', 'Custom Home Building' ),
	$items,
	'Primary term selection failed.'
);

echo "Resolver smoke tests passed.\n";
