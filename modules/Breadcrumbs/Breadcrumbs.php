<?php
/**
 * Divi 5 CPT Breadcrumbs module.
 *
 * @package BurntArrow\DiviCptBreadcrumbs
 */

namespace BurntArrow\DiviCptBreadcrumbs\Modules\Breadcrumbs;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use BurntArrow\DiviCptBreadcrumbs\BreadcrumbResolver;
use ET\Builder\Framework\DependencyManagement\Interfaces\DependencyInterface;
use ET\Builder\FrontEnd\BlockParser\BlockParserStore;
use ET\Builder\FrontEnd\Module\Style;
use ET\Builder\Packages\Module\Module;
use ET\Builder\Packages\Module\Options\Css\CssStyle;
use ET\Builder\Packages\Module\Options\Element\ElementComponents;
use ET\Builder\Packages\ModuleLibrary\ModuleRegistration;

/**
 * Native Divi 5 module dependency and server-side renderer.
 */
class Breadcrumbs implements DependencyInterface {

	/**
	 * Register the module with Divi's module library.
	 *
	 * @return void
	 */
	public function load() {
		add_action(
			'init',
			static function () {
				ModuleRegistration::register_module(
					DCB_MODULES_JSON_PATH . 'breadcrumbs/',
					array(
						'render_callback' => array( self::class, 'render_callback' ),
					)
				);
			}
		);
	}

	/**
	 * Render the module on the front end.
	 *
	 * @param array     $attrs    Saved module attributes.
	 * @param string    $content  Block content.
	 * @param \WP_Block $block    Parsed block.
	 * @param object    $elements Divi module elements helper.
	 * @return string
	 */
	public static function render_callback( $attrs, $content, $block, $elements ) {
		unset( $content );

		$config = self::config( $attrs );
		$items  = ( new BreadcrumbResolver() )->resolve(
			array(
				'post_type'     => $config['postType'],
				'taxonomy'      => $config['taxonomy'],
				'home_label'    => $config['homeLabel'],
				'archive_label' => $config['archiveLabel'],
			)
		);

		$items = self::apply_visibility( $items, $config );
		$html  = self::render_breadcrumbs( $items, $config );

		$parsed       = $block->parsed_block ?? array();
		$block_id     = $parsed['id'] ?? '';
		$store        = $parsed['storeInstance'] ?? '';
		$order_index  = $parsed['orderIndex'] ?? 0;
		$parent       = $block_id && $store ? BlockParserStore::get_parent( $block_id, $store ) : null;
		$parent_attrs = $parent->attrs ?? array();
		$module_name  = isset( $block->block_type->name ) ? $block->block_type->name : 'burnt-arrow/cpt-breadcrumbs';
		$module_cat   = isset( $block->block_type->category ) ? $block->block_type->category : 'module';

		return Module::render(
			array(
				'orderIndex'         => $order_index,
				'storeInstance'      => $store,
				'attrs'              => $attrs,
				'elements'           => $elements,
				'id'                 => $block_id,
				'name'               => $module_name,
				'moduleCategory'     => $module_cat,
				'classnamesFunction' => array( self::class, 'module_classnames' ),
				'stylesComponent'    => array( self::class, 'module_styles' ),
				'parentAttrs'        => $parent_attrs,
				'parentId'           => $parent->id ?? '',
				'parentName'         => $parent->blockName ?? '',
				'children'           => array(
					ElementComponents::component(
						array(
							'attrs'         => $attrs['module']['decoration'] ?? array(),
							'id'            => $block_id,
							'orderIndex'    => $order_index,
							'storeInstance' => $store,
						)
					),
					$html,
				),
			)
		);
	}

	/**
	 * Add module-specific classes.
	 *
	 * @param array $args Divi classname arguments.
	 * @return void
	 */
	public static function module_classnames( $args ) {
		$instance = $args['classnamesInstance'];
		$attrs    = $args['attrs'] ?? array();
		$config   = self::config( $attrs );

		$instance->add( 'dcb-breadcrumbs--inline' );
		if ( self::is_on( $config['schema'] ) ) {
			$instance->add( 'dcb-breadcrumbs--schema' );
		}
	}

	/**
	 * Generate Divi design styles on the front end.
	 *
	 * @param array $args Divi style arguments.
	 * @return void
	 */
	public static function module_styles( $args ) {
		$attrs    = $args['attrs'] ?? array();
		$elements = $args['elements'];
		$settings = $args['settings'] ?? array();

		Style::add(
			array(
				'id'            => $args['id'],
				'name'          => $args['name'],
				'orderIndex'    => $args['orderIndex'],
				'storeInstance' => $args['storeInstance'],
				'styles'        => array(
					$elements->style(
						array(
							'attrName'   => 'module',
							'styleProps' => array(
								'disabledOn' => array(
									'disabledModuleVisibility' => $settings['disabledModuleVisibility'] ?? null,
								),
							),
						)
					),
					$elements->style( array( 'attrName' => 'linkText' ) ),
					$elements->style( array( 'attrName' => 'currentText' ) ),
					$elements->style( array( 'attrName' => 'separatorText' ) ),
					CssStyle::style(
						array(
							'selector'  => $args['orderClass'],
							'attr'      => $attrs['css'] ?? array(),
							'cssFields' => self::custom_css(),
						)
					),
				),
			)
		);
	}

	/**
	 * Custom CSS targets shown in Divi's Advanced tab.
	 *
	 * @return array<string,array<string,string>>
	 */
	private static function custom_css() {
		return array(
			'nav'       => array(
				'label'          => __( 'Breadcrumb navigation', 'divi-cpt-breadcrumbs' ),
				'subName'        => 'nav',
				'selectorSuffix' => ' .dcb-breadcrumbs__nav',
			),
			'list'      => array(
				'label'          => __( 'Breadcrumb list', 'divi-cpt-breadcrumbs' ),
				'subName'        => 'list',
				'selectorSuffix' => ' .dcb-breadcrumbs__list',
			),
			'item'      => array(
				'label'          => __( 'Breadcrumb item', 'divi-cpt-breadcrumbs' ),
				'subName'        => 'item',
				'selectorSuffix' => ' .dcb-breadcrumbs__item',
			),
			'link'      => array(
				'label'          => __( 'Breadcrumb link', 'divi-cpt-breadcrumbs' ),
				'subName'        => 'link',
				'selectorSuffix' => ' .dcb-breadcrumbs__link',
			),
			'current'   => array(
				'label'          => __( 'Current item', 'divi-cpt-breadcrumbs' ),
				'subName'        => 'current',
				'selectorSuffix' => ' .dcb-breadcrumbs__current',
			),
			'separator' => array(
				'label'          => __( 'Separator', 'divi-cpt-breadcrumbs' ),
				'subName'        => 'separator',
				'selectorSuffix' => ' .dcb-breadcrumbs__separator',
			),
		);
	}

	/**
	 * Read scalar module settings from Divi's responsive attribute format.
	 *
	 * @param array $attrs Saved attributes.
	 * @return array<string,string>
	 */
	private static function config( $attrs ) {
		$value = $attrs['breadcrumb']['innerContent']['desktop']['value'] ?? array();
		$value = is_array( $value ) ? $value : array();

		return array(
			'homeLabel'    => isset( $value['homeLabel'] ) ? (string) $value['homeLabel'] : __( 'Home', 'divi-cpt-breadcrumbs' ),
			'archiveLabel' => isset( $value['archiveLabel'] ) ? (string) $value['archiveLabel'] : '',
			'separator'    => isset( $value['separator'] ) ? (string) $value['separator'] : '/',
			'ariaLabel'    => isset( $value['ariaLabel'] ) ? (string) $value['ariaLabel'] : __( 'Breadcrumb', 'divi-cpt-breadcrumbs' ),
			'postType'     => isset( $value['postType'] ) ? sanitize_key( $value['postType'] ) : 'auto',
			'taxonomy'     => isset( $value['taxonomy'] ) ? sanitize_key( $value['taxonomy'] ) : 'auto',
			'showHome'     => isset( $value['showHome'] ) ? (string) $value['showHome'] : 'on',
			'showArchive'  => isset( $value['showArchive'] ) ? (string) $value['showArchive'] : 'on',
			'showCurrent'  => isset( $value['showCurrent'] ) ? (string) $value['showCurrent'] : 'on',
			'schema'       => isset( $value['schema'] ) ? (string) $value['schema'] : 'on',
		);
	}

	/**
	 * Apply module display toggles.
	 *
	 * @param array $items  Breadcrumb items.
	 * @param array $config Module config.
	 * @return array
	 */
	private static function apply_visibility( array $items, array $config ) {
		return array_values(
			array_filter(
				$items,
				static function ( $item ) use ( $config ) {
					if ( 'home' === $item['type'] && ! self::is_on( $config['showHome'] ) ) {
						return false;
					}
					if ( 'archive' === $item['type'] && ! self::is_on( $config['showArchive'] ) ) {
						return false;
					}
					if ( ! empty( $item['current'] ) && ! self::is_on( $config['showCurrent'] ) ) {
						return false;
					}
					return '' !== trim( (string) $item['label'] );
				}
			)
		);
	}

	/**
	 * Render accessible HTML and optional Schema.org BreadcrumbList microdata.
	 *
	 * @param array $items  Breadcrumb items.
	 * @param array $config Module config.
	 * @return string
	 */
	private static function render_breadcrumbs( array $items, array $config ) {
		if ( empty( $items ) ) {
			return '';
		}

		$schema = self::is_on( $config['schema'] );
		$list   = '<ol class="dcb-breadcrumbs__list"' . ( $schema ? ' itemscope itemtype="https://schema.org/BreadcrumbList"' : '' ) . '>';
		$count  = count( $items );

		foreach ( $items as $index => $item ) {
			$position   = $index + 1;
			$is_last     = $position === $count;
			$is_current  = ! empty( $item['current'] );
			$item_attr   = $schema ? ' itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"' : '';
			$list       .= '<li class="dcb-breadcrumbs__item"' . $item_attr . '>';

			if ( ! $is_current && ! empty( $item['url'] ) ) {
				$list .= '<a class="dcb-breadcrumbs__link" href="' . esc_url( $item['url'] ) . '"' . ( $schema ? ' itemprop="item"' : '' ) . '>';
				$list .= '<span' . ( $schema ? ' itemprop="name"' : '' ) . '>' . esc_html( $item['label'] ) . '</span>';
				$list .= '</a>';
			} elseif ( $is_current ) {
				$list .= '<span class="dcb-breadcrumbs__current" aria-current="page"' . ( $schema ? ' itemprop="name"' : '' ) . '>' . esc_html( $item['label'] ) . '</span>';
				if ( $schema && ! empty( $item['url'] ) ) {
					$list .= '<meta itemprop="item" content="' . esc_url( $item['url'] ) . '">';
				}
			} else {
				$list .= '<span class="dcb-breadcrumbs__label"' . ( $schema ? ' itemprop="name"' : '' ) . '>' . esc_html( $item['label'] ) . '</span>';
			}

			if ( $schema ) {
				$list .= '<meta itemprop="position" content="' . esc_attr( (string) $position ) . '">';
			}

			if ( ! $is_last ) {
				$list .= '<span class="dcb-breadcrumbs__separator" aria-hidden="true">' . esc_html( $config['separator'] ) . '</span>';
			}

			$list .= '</li>';
		}

		$list .= '</ol>';

		return '<nav class="dcb-breadcrumbs__nav" aria-label="' . esc_attr( $config['ariaLabel'] ) . '">' . $list . '</nav>';
	}

	/**
	 * Divi toggles are stored as on/off strings.
	 *
	 * @param mixed $value Value.
	 * @return bool
	 */
	private static function is_on( $value ) {
		return in_array( (string) $value, array( 'on', 'yes', 'true', '1' ), true );
	}
}
