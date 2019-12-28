<?php

/**
 * Glossary
 *
 * @package   Glossary
 * @author    Codeat <support@codeat.co>
 * @copyright 2016 GPL 2.0+
 * @license   GPL-2.0+
 * @link      http://codeat.co
 */

/**
 * Generate the list of terms
 *
 * @param string $order Order.
 * @param int    $num   Amount.
 * @param string $tax   Taxonomy name.
 *
 * @return string
 */
function get_glossary_terms_list( $order, $num, $tax = '' ) {
	if ( $order === 'asc' ) {
		$order = 'ASC';
	}

	$args = array(
		'post_type'              => 'glossary',
		'order'                  => $order,
		'orderby'                => 'title',
        'posts_per_page'         => $num,
        'post_status'            => 'publish',
        'update_post_meta_cache' => false,
        'fields'                 => 'ids',
    );

    if ( !empty( $tax ) && $tax !== 'any' ) {
        $args[ 'tax_query' ] = array( // WPCS: slow query ok.
            array(
                'taxonomy' => 'glossary-cat',
                'terms'    => $tax,
                'field'    => 'slug',
            ),
        );
    }

    $glossary = new WP_Query( $args );
    if ( $glossary->have_posts() ) {
        $out = '<dl class="glossary-terms-list">';
        while ( $glossary->have_posts() ) :
            $glossary->the_post();
			$out .= '<dt><a href="' . get_glossary_term_url( get_the_ID() ) . '">' . get_the_title() . '</a></dt>';
        endwhile;
		$out .= '</dl>';
		wp_reset_postdata();

		return $out;
    }
}

/**
 * Get the url of the term attached
 *
 * @param int $term_id The term ID.
 *
 * @return string
 */
function get_glossary_term_url( $term_id = '' ) {
    if ( empty( $term_id ) ) {
        $term_id = get_the_ID();
    }

    $type = esc_html( get_post_meta( $term_id, GT_SETTINGS . '_link_type', true ) );
    $link = esc_html( get_post_meta( $term_id, GT_SETTINGS . '_url', true ) );
    $cpt  = esc_html( get_post_meta( $term_id, GT_SETTINGS . '_cpt', true ) );

    if ( empty( $link ) && empty( $cpt ) ) {
        return get_the_permalink( $term_id );
    }

    if ( $type === 'external' || empty( $type ) ) {
        return $link;
    }

    if ( $type === 'internal' ) {
        return get_the_permalink( $cpt );
    }
}

/**
 * Generate a list of category terms
 *
 * @param string $order Order.
 * @param int    $num   Amount.
 *
 * @return string
 */
function get_glossary_cats_list( $order = 'ASC', $num = '0' ) {
    $taxs = get_terms(
        array(
            'hide_empty' => false,
            'taxonomy'   => 'glossary-cat',
            'order'      => $order,
            'number'     => $num,
            'orderby'    => 'title',
        )
    );

    $out = '<dl class="glossary-terms-list">';
    if ( !empty( $taxs ) && !is_wp_error( $taxs ) ) {
        foreach ( $taxs as $tax ) {
            if ( !empty( $tax->parent ) ) {
                continue;
            }

            $subout = '';
            $out   .= '<dt><a href="' . esc_url( get_term_link( $tax ) ) . '">' . $tax->name . '</a>';
            foreach ( $taxs as $index => $subcategory ) {
                if ( $subcategory->parent === $tax->term_id ) {
                    $subout .= '<dt><a href="' . esc_url( get_term_link( $subcategory ) ) . '">' . $subcategory->name . '</a></dt>';
                }
            }

            if ( !empty( $subout ) ) {
                $out .= '<dl>' . $subout . '</dl>';
            }

            $out .= '</dt>';
        }

        $out .= '</dl>';
        return $out;
    }
}

/**
 * Check if text is RTL
 *
 * @param string $string The string.
 *
 * @return bool
 */
function gl_text_is_rtl( $string ) {
    $rtl_chars_pattern = '/[\x{0590}-\x{05ff}\x{0600}-\x{06ff}]/u';
    return preg_match( $rtl_chars_pattern, $string );
}

/**
 * Check if word is written with latin characters
 *
 * @param string $string The string to validate.
 *
 * @return bool
 */
function gl_is_latin( $string ) {
    return !preg_match( '/[^\\p{Common}\\p{Latin}]/u', $string );
}

/**
 * Return the cached value of terms count
 *
 * @return number
 */
function gl_get_terms_count() {
    return get_option( GT_SETTINGS . '_count_terms', true );
}

/**
 * Return the cached value of related terms count
 *
 * @return number
 */
function gl_get_related_terms_count() {
    return get_option( GT_SETTINGS . '_count_related_terms', true );
}

/**
 * Update the database with cached value for count of terms and related terms
 *
 * @return void
 */
function gl_update_counter() {
    $args  = array(
        'post_type'      => 'glossary',
        'posts_per_page' => -1,
        'order'          => 'asc',
        'post_status'    => 'publish',
    );
    $query = new WP_Query( $args );

    $count         = 0;
    $count_related = 0;

    foreach ( $query->posts as $post ) {
        $count++;
        $related = gl_related_post_meta( get_post_meta( $post->ID, GT_SETTINGS . '_tag', true ) );
        if ( is_array( $related ) ) {
            $count_related += count( $related );
        }
    }

    update_option( GT_SETTINGS . '_count_terms', $count );
    update_option( GT_SETTINGS . '_count_related_terms', $count_related );
}

/**
 * Get the list of terms by A2Z index
 *
 * @param array $atts The parameters.
 *
 * return array The terms.
 */
function gl_get_a2z_initial( $atts = array() ) {
    global $wpdb;
    $default   = array(
        'show_counts' => false,
        'taxonomy'    => '',
        'letters'     => '',
    );
    $atts      = array_merge( $default, $atts );
    $count_col = $join = $tax_slug = '';
    if ( $atts[ 'show_counts' ] ) {
        $count_col = ", COUNT( substring( TRIM( UPPER( $wpdb->posts.post_title ) ), 1, 1) ) as counts";
    }

    if ( !empty( $atts[ 'taxonomy' ] ) ) {
        $tax_slug = " AND $wpdb->terms.slug = '" . $atts[ 'taxonomy' ] . "' AND $wpdb->term_taxonomy.taxonomy = 'glossary-cat' ";
        $join     = " LEFT JOIN $wpdb->term_relationships ON ($wpdb->posts.ID = $wpdb->term_relationships.object_id) LEFT JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id) LEFT JOIN $wpdb->terms ON ($wpdb->term_taxonomy.term_id = $wpdb->terms.term_id)";
    }

    $filter_initial = '';
    if ( !empty( $atts[ 'letters' ] ) ) {
        $filter_initial    = ' AND (';
        $atts[ 'letters' ] = explode( ',', $atts[ 'letters' ] );
        foreach ( $atts[ 'letters' ] as $key => $initial ) {
            $filter_initial .= ' SUBSTRING(' . $wpdb->posts . '.post_title,1,1) = "' . $initial . '" OR';
            if ( count( $atts[ 'letters' ] ) === ( $key + 1 ) ) {
                $filter_initial = mb_substr( $filter_initial, 0, -2 );
            }
        }

        $filter_initial .= ')';
    }

    $querystr = "SELECT DISTINCT SUBSTRING( TRIM( UPPER( $wpdb->posts.post_title ) ), 1, 1) as initial" . $count_col . " FROM $wpdb->posts" . $join . " WHERE $wpdb->posts.post_status = 'publish' AND $wpdb->posts.post_type = 'glossary'" . $tax_slug . $filter_initial . " GROUP BY initial ORDER BY TRIM( UPPER( $wpdb->posts.post_title ) );";

    return $wpdb->get_results( $querystr, ARRAY_A ); // WPCS: cache ok, db call ok.
}

/**
 * Return initials and ids
 *
 * @param array $atts The parameters.
 *
 * @return array Initial and Terms.
 */
function gl_get_a2z_ids( $atts = array() ) {
    global $wpdb;
    $default = array(
        'show_counts' => false,
        'taxonomy'    => '',
        'letters'     => '',
    );
    $atts    = array_merge( $default, $atts );
    $join    = $tax_slug = '';

    if ( !empty( $atts[ 'taxonomy' ] ) ) {
        $tax_slug = " AND $wpdb->terms.slug = '" . $atts[ 'taxonomy' ] . "' AND $wpdb->term_taxonomy.taxonomy = 'glossary-cat' ";
        $join     = " LEFT JOIN $wpdb->term_relationships ON ($wpdb->posts.ID = $wpdb->term_relationships.object_id) LEFT JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id) LEFT JOIN $wpdb->terms ON ($wpdb->term_taxonomy.term_id = $wpdb->terms.term_id)";
    }

    $filter_initial = '';
    if ( !empty( $atts[ 'letters' ] ) ) {
        $filter_initial    = ' AND (';
        $atts[ 'letters' ] = explode( ',', $atts[ 'letters' ] );
        foreach ( $atts[ 'letters' ]as $key => $initial ) {
            $filter_initial .= ' SUBSTRING(' . $wpdb->posts . '.post_title,1,1) = "' . $initial . '" OR';
            if ( count( $atts[ 'letters' ] ) === ( $key + 1 ) ) {
                $filter_initial = substr( $filter_initial, 0, -2 );
            }
        }

        $filter_initial .= ')';
    }

    $querystr = "SELECT ID FROM $wpdb->posts" . $join . " WHERE $wpdb->posts.post_status = 'publish' AND $wpdb->posts.post_type = 'glossary'" . $tax_slug . $filter_initial . " ORDER BY TRIM( UPPER( $wpdb->posts.post_title ) );";

    $ids = $wpdb->get_results( $querystr, ARRAY_A ); // WPCS: cache ok, db call ok.

    $id_cleaned = array();
    foreach ( $ids as $id ) {
        $id_cleaned[] = $id[ 'ID' ];
    }

    return $id_cleaned;
}

/**
 * Length of the string based on encode
 *
 * @param string $string The string to get the length.
 *
 * @return string
 */
function gl_get_len( $string ) {
    if ( gl_text_is_rtl( $string ) ) {
        return mb_strlen( $string );
    }

    return mb_strlen( $string, 'latin1' );
}

/**
 * Function for usort to order all the terms on DESC
 *
 * @param array $first Previous index.
 * @param array $second Next index.
 *
 * @return boolean
 */
function gl_sort_by_long( $first, $second ) {
    return $second[ 'long' ] - $first[ 'long' ];
}

/**
 * Get a checkbox settings as boolean
 *
 * @param string $value The ID label of the settings.
 *
 * @return boolean
 */
function gl_get_bool_settings( $value ) {
    $settings = gl_get_settings();
    if ( isset( $settings[ $value ] ) && $settings[ $value ] ) {
        return true;
    }

    return false;
}

/**
 * Check the settings and if is a single term page
 *
 * @param string $related Contain the terms related to split with a comma.
 *
 * @return boolean
 */
function gl_related_post_meta( $related ) {
    $value = array_map( 'trim', explode( ',', $related ) );
    if ( empty( $value[ 0 ] ) ) {
        $value = false;
    }

    return $value;
}

/**
 * Return the settings of the plugin
 *
 * @return array
 */
function gl_get_settings() {
    $settings = get_option( GT_SETTINGS . '-settings' );
    /**
     * Alter the global settings
     *
     * @param array $settings The settingss.
     *
     * @since 1.5.0
     *
     * @return array $term_queue We need the settings.
     */
    return apply_filters( 'glossary_settings', $settings );
}

/**
 * Return the base url for glossary post type
 *
 * @return string
 */
function gl_get_base_url() {
    $base_url = get_post_type_archive_link( 'glossary' );
    if ( !$base_url ) {
        $base_url = esc_url( home_url( '/' ) );
        if ( get_option( 'show_on_front' ) === 'page' ) {
            $base_url = esc_url( get_permalink( get_option( 'page_for_posts' ) ) );
        }
    }

    return $base_url;
}
