<?php
/**
 * Plugin Name:       Reno Plus Divi 5 Service Breadcrumbs
 * Description:       A native Divi 5 breadcrumb module that understands hierarchical service-category terms used in services permalinks.
 * Version:           0.1.0
 * Requires at least: 6.5
 * Requires PHP:      7.4
 * Author:            Reno Plus
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       reno-plus-divi5-breadcrumbs
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'RP_D5_BREADCRUMBS_VERSION', '0.1.0' );
define( 'RP_D5_BREADCRUMBS_FILE', __FILE__ );
define( 'RP_D5_BREADCRUMBS_PATH', plugin_dir_path( __FILE__ ) );
define( 'RP_D5_BREADCRUMBS_URL', plugin_dir_url( __FILE__ ) );
define( 'RP_D5_BREADCRUMBS_MODULES_JSON_PATH', RP_D5_BREADCRUMBS_PATH . 'modules-json/' );

require_once RP_D5_BREADCRUMBS_PATH . 'includes/class-breadcrumb-resolver.php';

/**
 * Load translations.
 */
function rp_d5_breadcrumbs_load_textdomain() {
	load_plugin_textdomain(
		'reno-plus-divi5-breadcrumbs',
		false,
		dirname( plugin_basename( __FILE__ ) ) . '/languages'
	);
}
add_action( 'plugins_loaded', 'rp_d5_breadcrumbs_load_textdomain' );

/**
 * Register the PHP module dependency after Divi 5 has created its dependency tree.
 *
 * Keeping the Divi-specific class load inside this hook prevents fatal errors when
 * the plugin is active while Divi is temporarily inactive.
 *
 * @param object $dependency_tree Divi dependency tree.
 */
function rp_d5_breadcrumbs_register_module_dependency( $dependency_tree ) {
	require_once RP_D5_BREADCRUMBS_PATH . 'modules/Breadcrumbs/Breadcrumbs.php';

	$dependency_tree->add_dependency(
		new \RenoPlus\Divi5Breadcrumbs\Modules\Breadcrumbs\Breadcrumbs()
	);
}
add_action( 'divi_module_library_modules_dependency_tree', 'rp_d5_breadcrumbs_register_module_dependency' );

/**
 * Register the prebuilt Visual Builder JavaScript and CSS packages.
 */
function rp_d5_breadcrumbs_register_visual_builder_assets() {
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
			'name'    => 'reno-plus-divi5-breadcrumbs-builder-script',
			'version' => RP_D5_BREADCRUMBS_VERSION,
			'script'  => array(
				'src'                => RP_D5_BREADCRUMBS_URL . 'scripts/bundle.js',
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
			'name'    => 'reno-plus-divi5-breadcrumbs-builder-style',
			'version' => RP_D5_BREADCRUMBS_VERSION,
			'style'   => array(
				'src'                => RP_D5_BREADCRUMBS_URL . 'styles/vb-bundle.css',
				'deps'               => array(),
				'enqueue_top_window' => false,
				'enqueue_app_window' => true,
			),
		)
	);
}
add_action( 'divi_visual_builder_assets_before_enqueue_scripts', 'rp_d5_breadcrumbs_register_visual_builder_assets' );

/**
 * Load the small front-end stylesheet.
 */
function rp_d5_breadcrumbs_enqueue_frontend_style() {
	wp_enqueue_style(
		'reno-plus-divi5-breadcrumbs',
		RP_D5_BREADCRUMBS_URL . 'styles/bundle.css',
		array(),
		RP_D5_BREADCRUMBS_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'rp_d5_breadcrumbs_enqueue_frontend_style' );
