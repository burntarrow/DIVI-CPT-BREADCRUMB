<?php
/**
 * Plugin Name:       Divi 5 CPT Breadcrumbs
 * Plugin URI:        https://github.com/burntarrow/DIVI-CPT-BREADCRUMB
 * Description:       A native Divi 5 breadcrumb module for any public post type and taxonomy, including hierarchical taxonomy paths.
 * Version:           0.2.0
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Author:            Burnt Arrow
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       divi-cpt-breadcrumbs
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'DCB_VERSION', '0.2.0' );
define( 'DCB_FILE', __FILE__ );
define( 'DCB_PATH', plugin_dir_path( __FILE__ ) );
define( 'DCB_URL', plugin_dir_url( __FILE__ ) );
define( 'DCB_MODULES_JSON_PATH', DCB_PATH . 'modules-json/' );

require_once DCB_PATH . 'includes/class-breadcrumb-resolver.php';
require_once DCB_PATH . 'includes/class-data-source-controller.php';

/**
 * Load translations.
 *
 * @return void
 */
function dcb_load_textdomain() {
	load_plugin_textdomain(
		'divi-cpt-breadcrumbs',
		false,
		dirname( plugin_basename( __FILE__ ) ) . '/languages'
	);
}
add_action( 'plugins_loaded', 'dcb_load_textdomain' );

/**
 * Register the Visual Builder data-source endpoint.
 *
 * @return void
 */
function dcb_register_rest_routes() {
	\BurntArrow\DiviCptBreadcrumbs\DataSourceController::register_routes();
}
add_action( 'rest_api_init', 'dcb_register_rest_routes' );

/**
 * Register the PHP module dependency after Divi 5 creates its dependency tree.
 *
 * Keeping the Divi-specific class load inside this hook prevents fatal errors
 * when the plugin remains active while Divi is temporarily inactive.
 *
 * @param object $dependency_tree Divi dependency tree.
 * @return void
 */
function dcb_register_module_dependency( $dependency_tree ) {
	require_once DCB_PATH . 'modules/Breadcrumbs/Breadcrumbs.php';

	$dependency_tree->add_dependency(
		new \BurntArrow\DiviCptBreadcrumbs\Modules\Breadcrumbs\Breadcrumbs()
	);
}
add_action( 'divi_module_library_modules_dependency_tree', 'dcb_register_module_dependency' );

/**
 * Register the prebuilt Visual Builder JavaScript and CSS packages.
 *
 * @return void
 */
function dcb_register_visual_builder_assets() {
	if ( ! function_exists( 'et_builder_d5_enabled' ) || ! et_builder_d5_enabled() ) {
		return;
	}

	if ( ! function_exists( 'et_core_is_fb_enabled' ) || ! et_core_is_fb_enabled() ) {
		return;
	}

	if ( ! class_exists( '\\ET\\Builder\\VisualBuilder\\Assets\\PackageBuildManager' ) ) {
		return;
	}

	\ET\Builder\VisualBuilder\Assets\PackageBuildManager::register_package_build(
		array(
			'name'    => 'divi-cpt-breadcrumbs-builder-script',
			'version' => DCB_VERSION,
			'script'  => array(
				'src'                => DCB_URL . 'scripts/bundle.js',
				'deps'               => array(
					'divi-module-library',
					'divi-vendor-wp-hooks',
				),
				'enqueue_top_window' => false,
				'enqueue_app_window' => true,
			),
		)
	);

	\ET\Builder\VisualBuilder\Assets\PackageBuildManager::register_package_build(
		array(
			'name'    => 'divi-cpt-breadcrumbs-builder-style',
			'version' => DCB_VERSION,
			'style'   => array(
				'src'                => DCB_URL . 'styles/vb-bundle.css',
				'deps'               => array(),
				'enqueue_top_window' => false,
				'enqueue_app_window' => true,
			),
		)
	);
}
add_action( 'divi_visual_builder_assets_before_enqueue_scripts', 'dcb_register_visual_builder_assets' );

/**
 * Load the front-end stylesheet.
 *
 * @return void
 */
function dcb_enqueue_frontend_style() {
	wp_enqueue_style(
		'divi-cpt-breadcrumbs',
		DCB_URL . 'styles/bundle.css',
		array(),
		DCB_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'dcb_enqueue_frontend_style' );
