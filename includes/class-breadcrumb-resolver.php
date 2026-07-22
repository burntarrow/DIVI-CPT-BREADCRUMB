<?php
/**
 * Resolve breadcrumb items from WordPress content relationships.
 *
 * @package RenoPlus\Divi5Breadcrumbs
 */

namespace RenoPlus\Divi5Breadcrumbs;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds breadcrumb data without parsing the current URL.
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
				'post_type'    => 'services',
				'taxonomy'     => 'service-category',
				'home_label'   => __( 'Home', 'reno-plus-divi5-breadcrumbs' ),
				'archive_label'=> '',
			)
		);

		$post_type = sanitize_key( $args['post_type'] );
		$taxonomy  = sanitize_key( $args['taxonomy'] );
		$items     = array();

		$items[] = $this->make_item(
			(string) $args['home_label'],
			home_url( '/' ),
			is_front_page(),
			'home'
		);

		if ( is_front_page() ) {
			return $this->filter_items( $items, $args );
		}

		if ( is_post_type_archive( $post_type ) ) {
			$items[] = $this->archive_item( $post_type, (string) $args['archive_label'], true );
			return $this->filter_items( $items, $args );
		}

		if ( is_tax( $taxonomy ) ) {
			$archive = $this->archive_item( $post_type, (string) $args['archive_label'], false );
			if ( '' !== $archive['label'] ) {
				$items[] = $archive;
			}

			$term = get_queried_object();
			if ( $term instanceof \WP_Term ) {
				$items = array_merge( $items, $this->term_path_items( $term, $taxonomy, true ) );
			}

			return $this->filter_items( $items, $args );
		}

		if ( is_singular( $post_type ) ) {
			$post = get_queried_object();
			if ( ! $post instanceof \WP_Post ) {
				$post = get_post();
			}

			$archive = $this->archive_item( $post_type, (string) $args['archive_label'], false );
			if ( '' !== $archive['label'] ) {
				$items[] = $archive;
			}

			if ( $post instanceof \WP_Post && taxonomy_exists( $taxonomy ) ) {
				$term = $this->primary_term( $post->ID, $taxonomy );
				if ( $term instanceof \WP_Term ) {
					$items = array_merge( $items, $this->term_path_items( $term, $taxonomy, false ) );
				}
			}

			if ( $post instanceof \WP_Post ) {
				$items[] = $this->make_item(
					get_the_title( $post ) ?: __( '(no title)', 'reno-plus-divi5-breadcrumbs' ),
					get_permalink( $post ),
					true,
					'current'
				);
			}

			return $this->filter_items( $items, $args );
		}

		if ( is_page() ) {
			$post = get_queried_object();
			if ( $post instanceof \WP_Post ) {
				$ancestor_ids = array_reverse( get_post_ancestors( $post ) );
				foreach ( $ancestor_ids as $ancestor_id ) {
					$ancestor = get_post( $ancestor_id );
					if ( $ancestor instanceof \WP_Post ) {
						$items[] = $this->make_item(
							get_the_title( $ancestor ),
							get_permalink( $ancestor ),
							false,
							'ancestor'
						);
					}
				}
				$items[] = $this->make_item( get_the_title( $post ), get_permalink( $post ), true, 'current' );
			}
			return $this->filter_items( $items, $args );
		}

		if ( is_singular() ) {
			$post = get_queried_object();
			if ( $post instanceof \WP_Post ) {
				$post_type_object = get_post_type_object( $post->post_type );
				if ( $post_type_object && $post_type_object->has_archive ) {
					$items[] = $this->archive_item( $post->post_type, '', false );
				}
				$items[] = $this->make_item( get_the_title( $post ), get_permalink( $post ), true, 'current' );
			}
			return $this->filter_items( $items, $args );
		}

		if ( is_search() ) {
			$items[] = $this->make_item(
				sprintf(
					/* translators: %s: search query. */
					__( 'Search results for “%s”', 'reno-plus-divi5-breadcrumbs' ),
					get_search_query()
				),
				'',
				true,
				'current'
			);
		} elseif ( is_404() ) {
			$items[] = $this->make_item( __( 'Page not found', 'reno-plus-divi5-breadcrumbs' ), '', true, 'current' );
		} elseif ( is_archive() ) {
			$items[] = $this->make_item( wp_strip_all_tags( get_the_archive_title() ), '', true, 'current' );
		} elseif ( is_home() ) {
			$page_for_posts = (int) get_option( 'page_for_posts' );
			$label          = $page_for_posts ? get_the_title( $page_for_posts ) : __( 'Blog', 'reno-plus-divi5-breadcrumbs' );
			$items[]        = $this->make_item( $label, get_post_type_archive_link( 'post' ), true, 'current' );
		}

		return $this->filter_items( $items, $args );
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
		$object = get_post_type_object( $post_type );
		$label  = $override;

		if ( '' === $label && $object ) {
			$label = $object->labels->name ?: $object->label;
		}

		$url = get_post_type_archive_link( $post_type );
		$url = $url ? $url : '';
		$url = apply_filters( 'reno_plus_divi5_breadcrumbs_archive_url', $url, $post_type );

		return $this->make_item( (string) $label, (string) $url, (bool) $current, 'archive' );
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
		$url = apply_filters( 'reno_plus_divi5_breadcrumbs_term_url', $url, $term );

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
			'reno_plus_divi5_breadcrumbs_primary_term_id',
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
		return (array) apply_filters( 'reno_plus_divi5_breadcrumbs_items', $items, $args );
	}
}
