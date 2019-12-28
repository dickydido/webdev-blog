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
 * Initialize the post type.
 */
class Glossary_PostType
{
    /**
     * Initialize the plugin by setting localization and loading public scripts
     * and styles.
     *
     * @since 1.0.0
     */
    public function initialize()
    {
        $this->settings = gl_get_settings();
        $this->register_post_type();
        // Create the args for the `glossary-cat` taxonomy
        $glossary_term_tax = array(
            'public'       => true,
            'capabilities' => array(
            'manage_terms' => 'manage_glossaries',
            'edit_terms'   => 'manage_glossaries',
            'delete_terms' => 'manage_glossaries',
            'assign_terms' => 'read_glossary',
        ),
        );
        if ( !empty($this->settings['slug_cat']) ) {
            $glossary_term_tax['rewrite']['slug'] = $this->settings['slug_cat'];
        }
        register_via_taxonomy_core( array( __( 'Term Category', 'glossary-by-codeat' ), __( 'Terms Categories', 'glossary-by-codeat' ), 'glossary-cat' ), $glossary_term_tax, array( 'glossary' ) );
        add_filter(
            'posts_orderby',
            array( $this, 'orderby_whitespace' ),
            9999,
            2
        );
    }
    
    /**
     * Change the orderby for the glossary auto link system to add priority based on number of the spaces
     *
     * @param string $orderby How to oder the query.
     * @param object $object  The object.
     *
     * @global object $wpdb
     *
     * @return string
     */
    public function orderby_whitespace( $orderby, $object )
    {
        
        if ( isset( $object->query['glossary_auto_link'] ) ) {
            global  $wpdb ;
            $orderby = '(LENGTH(' . $wpdb->prefix . 'posts.post_title) - LENGTH(REPLACE(' . $wpdb->prefix . "posts.post_title, ' ', ''))+1) DESC";
        }
        
        return $orderby;
    }
    
    public function register_post_type()
    {
        $this->settings = gl_get_settings();
        // Create the args for the `glossary` post type
        $glossary_term_cpt = array(
            'taxonomies'      => array( 'glossary-cat' ),
            'map_meta_cap'    => false,
            'yarpp_support'   => true,
            'menu_icon'       => 'dashicons-book-alt',
            'capability_type' => array( 'glossary', 'glossaries' ),
            'supports'        => array(
            'thumbnail',
            'author',
            'editor',
            'title',
            'genesis-seo',
            'genesis-layouts',
            'genesis-cpt-archive-settings',
            'revisions'
        ),
        );
        if ( !empty($this->settings['slug']) ) {
            $glossary_term_cpt['rewrite']['slug'] = $this->settings['slug'];
        }
        if ( isset( $this->settings['archive'] ) ) {
            $glossary_term_cpt['has_archive'] = false;
        }
        $single = __( 'Glossary Term', 'glossary-by-codeat' );
        $multi = __( 'Glossary', 'glossary-by-codeat' );
        register_via_cpt_core( array( $single, $multi, 'glossary' ), $glossary_term_cpt );
    }

}
$glossary_posttype = new Glossary_PostType();
$glossary_posttype->initialize();