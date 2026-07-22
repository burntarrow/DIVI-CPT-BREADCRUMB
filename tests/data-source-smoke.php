<?php
/**
 * Standalone smoke test for Visual Builder data-source discovery.
 * Run: php tests/data-source-smoke.php
 */

define( 'ABSPATH', __DIR__ );

function __( $text ) { return $text; }
function apply_filters( $tag, $value ) { return $value; }
function sanitize_key( $key ) { return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $key ) ); }
function wp_strip_all_tags( $text ) { return strip_tags( (string) $text ); }
function current_user_can( $capability ) { return 'read' === $capability && ! empty( $GLOBALS['dcb_can_read'] ); }

function get_post_types() {
	return array(
		'post' => (object) array(
			'name' => 'post',
			'labels' => (object) array( 'name' => 'Posts' ),
			'label' => 'Posts',
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
		),
		'services' => (object) array(
			'name' => 'services',
			'labels' => (object) array( 'name' => 'Services' ),
			'label' => 'Services',
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
		),
		'headless_item' => (object) array(
			'name' => 'headless_item',
			'labels' => (object) array( 'name' => 'Headless Items' ),
			'label' => 'Headless Items',
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => false,
		),
		'internal_record' => (object) array(
			'name' => 'internal_record',
			'labels' => (object) array( 'name' => 'Internal Records' ),
			'label' => 'Internal Records',
			'public' => false,
			'publicly_queryable' => false,
			'show_ui' => true,
		),
	);
}

function get_taxonomies() {
	return array(
		'category' => (object) array(
			'name' => 'category',
			'labels' => (object) array( 'name' => 'Categories' ),
			'label' => 'Categories',
			'object_type' => array( 'post' ),
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
		),
		'service-category' => (object) array(
			'name' => 'service-category',
			'labels' => (object) array( 'name' => 'Service Categories' ),
			'label' => 'Service Categories',
			'object_type' => array( 'services' ),
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
		),
		'headless-tax' => (object) array(
			'name' => 'headless-tax',
			'labels' => (object) array( 'name' => 'Headless Taxonomy' ),
			'label' => 'Headless Taxonomy',
			'object_type' => array( 'headless_item' ),
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => false,
		),
		'internal_tax' => (object) array(
			'name' => 'internal_tax',
			'labels' => (object) array( 'name' => 'Internal Taxonomy' ),
			'label' => 'Internal Taxonomy',
			'object_type' => array( 'internal_record' ),
			'public' => false,
			'publicly_queryable' => false,
			'show_ui' => true,
		),
	);
}

require dirname( __DIR__ ) . '/includes/class-data-source-controller.php';

use BurntArrow\DiviCptBreadcrumbs\DataSourceController;


$GLOBALS['dcb_can_read'] = false;
if ( DataSourceController::permission_check() ) {
	fwrite( STDERR, "Users without the read capability should be denied.\n" );
	exit( 1 );
}

$GLOBALS['dcb_can_read'] = true;
if ( ! DataSourceController::permission_check() ) {
	fwrite( STDERR, "Users with the read capability should be allowed.\n" );
	exit( 1 );
}

$payload = DataSourceController::data_sources_payload();

$required_post_types = array( 'auto', 'post', 'services', 'headless_item' );
foreach ( $required_post_types as $post_type ) {
	if ( ! isset( $payload['postTypes'][ $post_type ] ) ) {
		fwrite( STDERR, "Missing post type option: {$post_type}\n" );
		exit( 1 );
	}
}

if ( isset( $payload['postTypes']['internal_record'] ) ) {
	fwrite( STDERR, "Internal post type should not be selectable.\n" );
	exit( 1 );
}

$required_taxonomies = array( 'auto', 'none', 'category', 'service-category', 'headless-tax' );
foreach ( $required_taxonomies as $taxonomy ) {
	if ( ! isset( $payload['taxonomies'][ $taxonomy ] ) ) {
		fwrite( STDERR, "Missing taxonomy option: {$taxonomy}\n" );
		exit( 1 );
	}
}

if ( array( 'services' ) !== $payload['taxonomyPostTypes']['service-category'] ) {
	fwrite( STDERR, "Taxonomy-to-post-type mapping is incorrect.\n" );
	exit( 1 );
}

if ( false === strpos( $payload['taxonomies']['service-category']['label'], 'services' ) ) {
	fwrite( STDERR, "Taxonomy label should identify its associated post type.\n" );
	exit( 1 );
}

echo "Data source smoke tests passed.\n";
