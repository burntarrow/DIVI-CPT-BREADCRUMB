<?php
/**
 * Standalone smoke test for breadcrumb HTML rendering.
 * Run: php tests/markup-smoke.php
 */

namespace ET\Builder\Framework\DependencyManagement\Interfaces {
	interface DependencyInterface {
		public function load();
	}
}

namespace {
	define( 'ABSPATH', __DIR__ );

	function __( $text ) { return $text; }
	function esc_html( $text ) { return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' ); }
	function esc_attr( $text ) { return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' ); }
	function esc_url( $url ) { return htmlspecialchars( (string) $url, ENT_QUOTES, 'UTF-8' ); }
	function sanitize_key( $key ) { return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( $key ) ); }

	require dirname( __DIR__ ) . '/modules/Breadcrumbs/Breadcrumbs.php';

	$class = 'RenoPlus\\Divi5Breadcrumbs\\Modules\\Breadcrumbs\\Breadcrumbs';
	$render = new \ReflectionMethod( $class, 'render_breadcrumbs' );
	$render->setAccessible( true );
	$visibility = new \ReflectionMethod( $class, 'apply_visibility' );
	$visibility->setAccessible( true );

	$items = array(
		array( 'label' => 'Home', 'url' => 'https://example.com/', 'current' => false, 'type' => 'home' ),
		array( 'label' => 'Services', 'url' => 'https://example.com/services/', 'current' => false, 'type' => 'archive' ),
		array( 'label' => 'Residential', 'url' => 'https://example.com/service-category/residential/', 'current' => false, 'type' => 'term' ),
		array( 'label' => 'Custom Home', 'url' => 'https://example.com/services/residential/custom-home/', 'current' => true, 'type' => 'current' ),
	);
	$config = array(
		'separator' => '/',
		'ariaLabel' => 'Breadcrumb',
		'schema' => 'on',
		'showHome' => 'on',
		'showArchive' => 'on',
		'showCurrent' => 'on',
	);

	$html = $render->invoke( null, $items, $config );
	$checks = array(
		'<nav class="rp-d5-breadcrumbs__nav" aria-label="Breadcrumb">',
		'itemtype="https://schema.org/BreadcrumbList"',
		'href="https://example.com/service-category/residential/"',
		'aria-current="page"',
		'content="4"',
	);
	foreach ( $checks as $needle ) {
		if ( false === strpos( $html, $needle ) ) {
			fwrite( STDERR, "Missing expected markup: {$needle}\n{$html}\n" );
			exit( 1 );
		}
	}

	$config['showCurrent'] = 'off';
	$visible = $visibility->invoke( null, $items, $config );
	$html = $render->invoke( null, $visible, $config );
	if ( false !== strpos( $html, 'aria-current="page"' ) ) {
		fwrite( STDERR, "A linked ancestor was incorrectly marked as current.\n{$html}\n" );
		exit( 1 );
	}
	if ( false === strpos( $html, 'href="https://example.com/service-category/residential/"' ) ) {
		fwrite( STDERR, "The last visible ancestor was not kept as a link.\n{$html}\n" );
		exit( 1 );
	}

	echo "Markup smoke tests passed.\n";
}
