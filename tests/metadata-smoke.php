<?php
/**
 * Standalone smoke test for Divi module metadata.
 * Run: php tests/metadata-smoke.php
 */

$path = dirname( __DIR__ ) . '/modules-json/breadcrumbs/module.json';
$metadata = json_decode( file_get_contents( $path ), true );

if ( ! is_array( $metadata ) ) {
	fwrite( STDERR, "Module metadata is invalid JSON.\n" );
	exit( 1 );
}

$items = $metadata['attributes']['breadcrumb']['settings']['innerContent']['items'] ?? array();
$defaults = $metadata['attributes']['breadcrumb']['default']['innerContent']['desktop']['value'] ?? array();

foreach ( array( 'postType', 'taxonomy' ) as $field ) {
	if ( 'divi/select' !== ( $items[ $field ]['component']['name'] ?? '' ) ) {
		fwrite( STDERR, "{$field} must use the Divi select field.\n" );
		exit( 1 );
	}
}

if ( 'auto' !== ( $defaults['postType'] ?? '' ) || 'auto' !== ( $defaults['taxonomy'] ?? '' ) ) {
	fwrite( STDERR, "Universal data-source defaults must use automatic discovery.\n" );
	exit( 1 );
}

if ( 'burnt-arrow/cpt-breadcrumbs' !== ( $metadata['name'] ?? '' ) ) {
	fwrite( STDERR, "Unexpected module name.\n" );
	exit( 1 );
}

echo "Metadata smoke tests passed.\n";
