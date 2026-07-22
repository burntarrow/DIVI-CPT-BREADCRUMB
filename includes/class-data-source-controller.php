<?php
/**
 * Visual Builder data-source options.
 *
 * @package BurntArrow\DiviCptBreadcrumbs
 */

namespace BurntArrow\DiviCptBreadcrumbs;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Supplies registered post types and taxonomies to Divi's settings panel.
 */
class DataSourceController {

	/**
	 * Register REST routes.
	 *
	 * @return void
	 */
	public static function register_routes() {
		register_rest_route(
			'divi-cpt-breadcrumbs/v1',
			'/data-sources',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( self::class, 'get_data_sources' ),
				'permission_callback' => array( self::class, 'permission_check' ),
			)
		);
	}

	/**
	 * Restrict builder option discovery to authenticated users.
	 *
	 * The response contains only registered public object metadata. Requiring the
	 * standard read capability keeps the endpoint builder-only while remaining
	 * compatible with CPTs that use custom editing capabilities.
	 *
	 * @return bool
	 */
	public static function permission_check() {
		return current_user_can( 'read' );
	}

	/**
	 * REST callback.
	 *
	 * @return \WP_REST_Response
	 */
	public static function get_data_sources() {
		return rest_ensure_response( self::data_sources_payload() );
	}

	/**
	 * Build the option payload consumed by the Visual Builder.
	 *
	 * Select options use the same object shape as Divi's field library:
	 * `{ value: { label: "Human label" } }`.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	public static function data_sources_payload() {
		$post_types        = self::post_type_options();
		$taxonomies        = self::taxonomy_options();
		$taxonomy_map      = array();
		$taxonomy_objects  = get_taxonomies( array(), 'objects' );

		foreach ( $taxonomy_objects as $taxonomy ) {
			if ( ! self::is_selectable_taxonomy( $taxonomy ) ) {
				continue;
			}

			$taxonomy_map[ $taxonomy->name ] = array_values(
				array_map( 'sanitize_key', (array) $taxonomy->object_type )
			);
		}

		return array(
			'postTypes'        => $post_types,
			'taxonomies'       => $taxonomies,
			'taxonomyPostTypes' => $taxonomy_map,
		);
	}

	/**
	 * Get selectable post types.
	 *
	 * @return array<string,array<string,string>>
	 */
	private static function post_type_options() {
		$options = array(
			'auto' => array(
				'label' => __( 'Automatic (current request)', 'divi-cpt-breadcrumbs' ),
			),
		);
		$objects = get_post_types( array(), 'objects' );

		uasort(
			$objects,
			static function ( $left, $right ) {
				return strnatcasecmp( self::object_label( $left ), self::object_label( $right ) );
			}
		);

		foreach ( $objects as $object ) {
			if ( ! self::is_selectable_post_type( $object ) ) {
				continue;
			}

			$options[ $object->name ] = array(
				'label' => sprintf(
					/* translators: 1: post type label, 2: post type slug. */
					__( '%1$s (%2$s)', 'divi-cpt-breadcrumbs' ),
					self::object_label( $object ),
					$object->name
				),
			);
		}

		return (array) apply_filters( 'divi_cpt_breadcrumbs_post_type_options', $options, $objects );
	}

	/**
	 * Get selectable taxonomies.
	 *
	 * @return array<string,array<string,string>>
	 */
	private static function taxonomy_options() {
		$options = array(
			'auto' => array(
				'label' => __( 'Automatic (best taxonomy for the post)', 'divi-cpt-breadcrumbs' ),
			),
			'none' => array(
				'label' => __( 'None (omit taxonomy terms)', 'divi-cpt-breadcrumbs' ),
			),
		);
		$objects = get_taxonomies( array(), 'objects' );

		uasort(
			$objects,
			static function ( $left, $right ) {
				return strnatcasecmp( self::object_label( $left ), self::object_label( $right ) );
			}
		);

		foreach ( $objects as $object ) {
			if ( ! self::is_selectable_taxonomy( $object ) ) {
				continue;
			}

			$object_types = implode( ', ', array_map( 'sanitize_key', (array) $object->object_type ) );
			$options[ $object->name ] = array(
				'label' => sprintf(
					/* translators: 1: taxonomy label, 2: taxonomy slug, 3: associated post-type slugs. */
					__( '%1$s (%2$s) — %3$s', 'divi-cpt-breadcrumbs' ),
					self::object_label( $object ),
					$object->name,
					$object_types
				),
			);
		}

		return (array) apply_filters( 'divi_cpt_breadcrumbs_taxonomy_options', $options, $objects );
	}

	/**
	 * Decide whether a post type should appear in the builder dropdown.
	 *
	 * @param object $object Post type object.
	 * @return bool
	 */
	private static function is_selectable_post_type( $object ) {
		$selectable = ! empty( $object->name )
			&& ( ! empty( $object->public ) || ! empty( $object->publicly_queryable ) );

		return (bool) apply_filters( 'divi_cpt_breadcrumbs_post_type_is_selectable', $selectable, $object );
	}

	/**
	 * Decide whether a taxonomy should appear in the builder dropdown.
	 *
	 * @param object $object Taxonomy object.
	 * @return bool
	 */
	private static function is_selectable_taxonomy( $object ) {
		$selectable = ! empty( $object->name )
			&& ! empty( $object->object_type )
			&& ( ! empty( $object->public ) || ! empty( $object->publicly_queryable ) );

		return (bool) apply_filters( 'divi_cpt_breadcrumbs_taxonomy_is_selectable', $selectable, $object );
	}

	/**
	 * Read a registered object's plural label safely.
	 *
	 * @param object $object Registered object.
	 * @return string
	 */
	private static function object_label( $object ) {
		if ( isset( $object->labels->name ) && $object->labels->name ) {
			return wp_strip_all_tags( (string) $object->labels->name );
		}

		if ( isset( $object->label ) && $object->label ) {
			return wp_strip_all_tags( (string) $object->label );
		}

		return isset( $object->name ) ? (string) $object->name : '';
	}
}
