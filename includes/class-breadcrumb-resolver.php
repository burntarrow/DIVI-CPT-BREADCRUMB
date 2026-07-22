<?php
/**
 * Resolve breadcrumb items from WordPress content relationships.
 *
 * @package BurntArrow\DiviCptBreadcrumbs
 */

namespace BurntArrow\DiviCptBreadcrumbs;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds breadcrumb data from the queried object, post type, and taxonomy.
 *
 * The resolver intentionally does not split the request URL. WordPress remains
 * the source of truth for post archives, term ancestors, primary terms, and
 * permalinks.
 */
class BreadcrumbResolver {

	/**
	 * Resolve breadcrumb items for the current request.
	 *
	 * @param array $args Resolver arguments.
	 * @return array<int,array<string,mixed>>
	 */
	public function resolve( array $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'post_type'    => 'auto',
				'taxonomy'     => 'auto',
				'home_label'   => __( 'Home', 'divi-cpt-breadcrumbs' ),
				'archive_label' => '',
			)
		);

		$selected_post_type = $this->normalize_source( $args['post_type'], 'auto' );
		$selected_taxonomy  = $this->normalize_source( $args['taxonomy'], 'auto' );
		$current_post       = $this->current_post();
		$post_type          = $this->resolve_post_type( $selected_post_type, $current_post );
		$taxonomy           = $this->resolve_taxonomy( $selected_taxonomy, $post_type, $current_post );
		$items              = array();

		$args['resolved_post_type'] = $post_type;
		$args['resolved_taxonomy']  = $taxonomy;

		$items[] = $this->make_item(
			(string) $args['home_label'],
			home_url( '/' ),
			is_front_page(),
			'home'
		);

		if ( is_front_page() ) {
			return $this->filter_items( $items, $args );
		}

		if ( is_search() ) {
			$items[] = $this->make_item(
				sprintf(
					/* translators: %s: search query. */
					__( 'Search results for “%s”', 'divi-cpt-breadcrumbs' ),
					get_search_query()
				),
				'',
				true,
				'current'
			);

			return $this->filter_items( $items, $args );
		}

		if ( is_404() ) {
			$items[] = $this->make_item( __( 'Page not found', 'divi-cpt-breadcrumbs' ), '', true, 'current' );
			return $this->filter_items( $items, $args );
		}

		if ( is_home() ) {
			$items[] = $this->archive_item( 'post', (string) $args['archive_label'], true );
			return $this->filter_items( $items, $args );
		}

		$queried_term = $this->current_term();
		if ( $queried_term instanceof \WP_Term ) {
			$term_post_type = $this->post_type_for_taxonomy( $selected_post_type, $queried_term->taxonomy );
			$archive        = $this->archive_item( $term_post_type, (string) $args['archive_label'], false );

			if ( '' !== $archive['label'] ) {
				$items[] = $archive;
			}

			$items = array_merge(
				$items,
				$this->term_path_items( $queried_term, $queried_term->taxonomy, true )
			);

			$args['resolved_post_type'] = $term_post_type;
			$args['resolved_taxonomy']  = $queried_term->taxonomy;

			return $this->filter_items( $items, $args );
		}

		$archive_post_type = $this->current_archive_post_type( $post_type );
		if ( '' !== $archive_post_type ) {
			$items[] = $this->archive_item( $archive_post_type, (string) $args['archive_label'], true );
			$args['resolved_post_type'] = $archive_post_type;
			return $this->filter_items( $items, $args );
		}

		if ( is_singular() && $current_post instanceof \WP_Post ) {
			$actual_post_type = sanitize_key( $current_post->post_type );
			$archive          = $this->archive_item( $actual_post_type, (string) $args['archive_label'], false );

			if ( '' !== $archive['label'] ) {
				$items[] = $archive;
			}

			$items = array_merge( $items, $this->post_ancestor_items( $current_post ) );

			$actual_taxonomy = $this->resolve_taxonomy( $selected_taxonomy, $actual_post_type, $current_post );
			if ( '' !== $actual_taxonomy ) {
				$term = $this->primary_term( $current_post->ID, $actual_taxonomy );
				if ( $term instanceof \WP_Term ) {
					$items = array_merge( $items, $this->term_path_items( $term, $actual_taxonomy, false ) );
				}
			}

			$items[] = $this->make_item(
				get_the_title( $current_post ) ?: __( '(no title)', 'divi-cpt-breadcrumbs' ),
				get_permalink( $current_post ),
				true,
				'current'
			);

			$args['resolved_post_type'] = $actual_post_type;
			$args['resolved_taxonomy']  = $actual_taxonomy;

			return $this->filter_items( $items, $args );
		}

		if ( is_archive() ) {
			$items[] = $this->make_item( wp_strip_all_tags( get_the_archive_title() ), '', true, 'current' );
		}

		return $this->filter_items( $items, $args );
	}

	/**
	 * Return the current queried post when available.
	 *
	 * @return \WP_Post|null
	 */
	private function current_post() {
		$queried = get_queried_object();
		if ( $queried instanceof \WP_Post ) {
			return $queried;
		}

		$post = get_post();
		return $post instanceof \WP_Post ? $post : null;
	}

	/**
	 * Return the current queried term for category, tag, or custom taxonomy archives.
	 *
	 * @return \WP_Term|null
	 */
	private function current_term() {
		if ( ! is_category() && ! is_tag() && ! is_tax() ) {
			return null;
		}

		$queried = get_queried_object();
		return $queried instanceof \WP_Term ? $queried : null;
	}

	/**
	 * Resolve the post type for the request.
	 *
	 * The actual queried post wins on singular requests. The configured value is
	 * used as a fallback for taxonomy templates and other contexts where WordPress
	 * does not expose one unambiguous post type.
	 *
	 * @param string        $selected Selected post type or `auto`.
	 * @param \WP_Post|null $post     Current post.
	 * @return string
	 */
	private function resolve_post_type( $selected, $post ) {
		if ( $post instanceof \WP_Post && post_type_exists( $post->post_type ) ) {
			return sanitize_key( $post->post_type );
		}

		if ( is_home() ) {
			return 'post';
		}

		$query_post_type = get_query_var( 'post_type' );
		if ( is_array( $query_post_type ) ) {
			$query_post_type = reset( $query_post_type );
		}
		$query_post_type = sanitize_key( (string) $query_post_type );
		if ( '' !== $query_post_type && post_type_exists( $query_post_type ) ) {
			return $query_post_type;
		}

		if ( 'auto' !== $selected && post_type_exists( $selected ) ) {
			return $selected;
		}

		return '';
	}

	/**
	 * Resolve the selected taxonomy, including automatic discovery.
	 *
	 * @param string        $selected  Selected taxonomy, `auto`, or `none`.
	 * @param string        $post_type Resolved post type.
	 * @param \WP_Post|null $post      Current post.
	 * @return string
	 */
	private function resolve_taxonomy( $selected, $post_type, $post ) {
		if ( 'none' === $selected ) {
			return '';
		}

		$current_term = $this->current_term();
		if ( $current_term instanceof \WP_Term ) {
			return sanitize_key( $current_term->taxonomy );
		}

		if ( 'auto' !== $selected ) {
			if ( taxonomy_exists( $selected ) && ( '' === $post_type || is_object_in_taxonomy( $post_type, $selected ) ) ) {
				return $selected;
			}

			return '';
		}

		if ( '' === $post_type || ! post_type_exists( $post_type ) ) {
			return '';
		}

		$taxonomies = get_object_taxonomies( $post_type, 'objects' );
		$taxonomies = array_filter(
			$taxonomies,
			static function ( $taxonomy ) {
				return ! empty( $taxonomy->public ) || ! empty( $taxonomy->publicly_queryable );
			}
		);

		if ( empty( $taxonomies ) ) {
			return '';
		}

		// Prefer a hierarchical taxonomy that actually has a term on this post.
		if ( $post instanceof \WP_Post ) {
			foreach ( array( true, false ) as $hierarchical ) {
				foreach ( $taxonomies as $taxonomy ) {
					if ( (bool) $taxonomy->hierarchical !== $hierarchical ) {
						continue;
					}

					$terms = get_the_terms( $post->ID, $taxonomy->name );
					if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
						return sanitize_key( $taxonomy->name );
					}
				}
			}
		}

		// With no assigned terms, prefer the first hierarchical taxonomy.
		foreach ( $taxonomies as $taxonomy ) {
			if ( ! empty( $taxonomy->hierarchical ) ) {
				return sanitize_key( $taxonomy->name );
			}
		}

		$first = reset( $taxonomies );
		return $first ? sanitize_key( $first->name ) : '';
	}

	/**
	 * Choose a post type associated with a taxonomy archive.
	 *
	 * @param string $selected_post_type Selected post type or `auto`.
	 * @param string $taxonomy          Taxonomy slug.
	 * @return string
	 */
	private function post_type_for_taxonomy( $selected_post_type, $taxonomy ) {
		if (
			'auto' !== $selected_post_type
			&& post_type_exists( $selected_post_type )
			&& is_object_in_taxonomy( $selected_post_type, $taxonomy )
		) {
			return $selected_post_type;
		}

		$object = get_taxonomy( $taxonomy );
		if ( ! $object || empty( $object->object_type ) ) {
			return '';
		}

		$available = array_values(
			array_filter(
				array_map( 'sanitize_key', (array) $object->object_type ),
				'post_type_exists'
			)
		);

		foreach ( $available as $post_type ) {
			$post_type_object = get_post_type_object( $post_type );
			if ( 'post' === $post_type || ( $post_type_object && $post_type_object->has_archive ) ) {
				return $post_type;
			}
		}

		return isset( $available[0] ) ? $available[0] : '';
	}

	/**
	 * Detect a post type archive request.
	 *
	 * @param string $fallback Resolved fallback post type.
	 * @return string
	 */
	private function current_archive_post_type( $fallback ) {
		if ( '' !== $fallback && is_post_type_archive( $fallback ) ) {
			return $fallback;
		}

		if ( ! is_post_type_archive() ) {
			return '';
		}

		$query_post_type = get_query_var( 'post_type' );
		if ( is_array( $query_post_type ) ) {
			$query_post_type = reset( $query_post_type );
		}

		$query_post_type = sanitize_key( (string) $query_post_type );
		return post_type_exists( $query_post_type ) ? $query_post_type : '';
	}

	/**
	 * Build the post type archive item.
	 *
	 * @param string $post_type Post type slug.
	 * @param string $override  Optional label override.
	 * @param bool   $current   Whether this is the current item.
	 * @return array<string,mixed>
	 */
	private function archive_item( $post_type, $override, $current ) {
		if ( '' === $post_type || ! post_type_exists( $post_type ) ) {
			return $this->make_item( '', '', $current, 'archive' );
		}

		$object = get_post_type_object( $post_type );
		$label  = trim( (string) $override );
		$url    = '';

		if ( 'post' === $post_type ) {
			$page_for_posts = (int) get_option( 'page_for_posts' );
			if ( '' === $label ) {
				$label = $page_for_posts
					? get_the_title( $page_for_posts )
					: __( 'Blog', 'divi-cpt-breadcrumbs' );
			}
			$url = $page_for_posts ? get_permalink( $page_for_posts ) : home_url( '/' );
		} else {
			if ( '' === $label && $object ) {
				$label = $object->labels->name ?: $object->label;
			}

			if ( $object && $object->has_archive ) {
				$url = get_post_type_archive_link( $post_type );
				$url = $url ? $url : '';
			}
		}

		$url   = apply_filters( 'divi_cpt_breadcrumbs_archive_url', $url, $post_type, $current );
		$label = apply_filters( 'divi_cpt_breadcrumbs_archive_label', $label, $post_type, $current );

		// A non-current archive crumb must have a real destination.
		if ( ! $current && '' === (string) $url ) {
			$label = '';
		}

		return $this->make_item( (string) $label, (string) $url, (bool) $current, 'archive' );
	}

	/**
	 * Return hierarchical post ancestors.
	 *
	 * @param \WP_Post $post Current post.
	 * @return array<int,array<string,mixed>>
	 */
	private function post_ancestor_items( \WP_Post $post ) {
		$object = get_post_type_object( $post->post_type );
		if ( ! $object || empty( $object->hierarchical ) ) {
			return array();
		}

		$items        = array();
		$ancestor_ids = array_reverse( get_post_ancestors( $post ) );

		foreach ( $ancestor_ids as $ancestor_id ) {
			$ancestor = get_post( $ancestor_id );
			if ( ! $ancestor instanceof \WP_Post ) {
				continue;
			}

			$items[] = $this->make_item(
				get_the_title( $ancestor ),
				get_permalink( $ancestor ),
				false,
				'ancestor'
			);
		}

		return $items;
	}

	/**
	 * Return all ancestor terms plus the selected term.
	 *
	 * @param \WP_Term $term            Selected term.
	 * @param string   $taxonomy        Taxonomy slug.
	 * @param bool     $current_is_term Whether the selected term is current.
	 * @return array<int,array<string,mixed>>
	 */
	private function term_path_items( \WP_Term $term, $taxonomy, $current_is_term ) {
		$items        = array();
		$ancestor_ids = array_reverse( get_ancestors( $term->term_id, $taxonomy, 'taxonomy' ) );

		foreach ( $ancestor_ids as $ancestor_id ) {
			$ancestor = get_term( $ancestor_id, $taxonomy );
			if ( ! $ancestor instanceof \WP_Term || is_wp_error( $ancestor ) ) {
				continue;
			}
			$items[] = $this->term_item( $ancestor, false );
		}

		$items[] = $this->term_item( $term, $current_is_term );
		return $items;
	}

	/**
	 * Build a term breadcrumb item.
	 *
	 * @param \WP_Term $term    Term object.
	 * @param bool     $current Whether current.
	 * @return array<string,mixed>
	 */
	private function term_item( \WP_Term $term, $current ) {
		$url = get_term_link( $term );
		$url = is_wp_error( $url ) ? '' : $url;
		$url = apply_filters( 'divi_cpt_breadcrumbs_term_url', $url, $term, $current );

		return $this->make_item( $term->name, (string) $url, (bool) $current, 'term' );
	}

	/**
	 * Select one assigned term, preferring SEO-plugin primary-term metadata and
	 * otherwise selecting the deepest term in the hierarchy.
	 *
	 * @param int    $post_id  Post ID.
	 * @param string $taxonomy Taxonomy slug.
	 * @return \WP_Term|null
	 */
	private function primary_term( $post_id, $taxonomy ) {
		$terms = get_the_terms( $post_id, $taxonomy );
		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return null;
		}

		$primary_id = (int) apply_filters(
			'divi_cpt_breadcrumbs_primary_term_id',
			0,
			$post_id,
			$taxonomy,
			$terms
		);

		if ( ! $primary_id ) {
			$primary_id = (int) get_post_meta( $post_id, '_yoast_wpseo_primary_' . $taxonomy, true );
		}
		if ( ! $primary_id ) {
			$primary_id = (int) get_post_meta( $post_id, 'rank_math_primary_' . $taxonomy, true );
		}

		if ( $primary_id ) {
			foreach ( $terms as $term ) {
				if ( (int) $term->term_id === $primary_id ) {
					return $term;
				}
			}
		}

		usort(
			$terms,
			static function ( $term_a, $term_b ) use ( $taxonomy ) {
				$depth_a = count( get_ancestors( $term_a->term_id, $taxonomy, 'taxonomy' ) );
				$depth_b = count( get_ancestors( $term_b->term_id, $taxonomy, 'taxonomy' ) );

				if ( $depth_a === $depth_b ) {
					return (int) $term_a->term_id <=> (int) $term_b->term_id;
				}

				return $depth_b <=> $depth_a;
			}
		);

		return $terms[0] instanceof \WP_Term ? $terms[0] : null;
	}

	/**
	 * Normalize a selected data-source slug.
	 *
	 * @param mixed  $value    Raw value.
	 * @param string $fallback Fallback value.
	 * @return string
	 */
	private function normalize_source( $value, $fallback ) {
		$value = sanitize_key( (string) $value );
		return '' === $value ? $fallback : $value;
	}

	/**
	 * Create one item.
	 *
	 * @param string $label   Label.
	 * @param string $url     URL.
	 * @param bool   $current Current state.
	 * @param string $type    Item type.
	 * @return array<string,mixed>
	 */
	private function make_item( $label, $url, $current, $type ) {
		return array(
			'label'   => wp_strip_all_tags( (string) $label ),
			'url'     => (string) $url,
			'current' => (bool) $current,
			'type'    => sanitize_key( $type ),
		);
	}

	/**
	 * Expose final items to site-specific customization.
	 *
	 * @param array $items Items.
	 * @param array $args  Resolver arguments.
	 * @return array
	 */
	private function filter_items( array $items, array $args ) {
		return (array) apply_filters( 'divi_cpt_breadcrumbs_items', $items, $args );
	}
}
