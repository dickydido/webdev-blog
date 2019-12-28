<?php

/**
 * Glossary_Search_Engine
 *
 * @package   Glossary
 * @author    Codeat <support@codeat.co>
 * @copyright 2015 GPL
 * @link      http://codeat.co
 * @license   GPL-2.0+
 */
/**
 * Engine system that search :-P
 */
class Glossary_Search_Engine
{
    /**
     * Instance of this class.
     *
     * @var object
     */
    protected static  $instance = null ;
    /**
     * The list of terms parsed
     *
     * @var array
     */
    public  $terms_queue = array() ;
    /**
     * Initialize the class with all the hooks
     *
     * @since 1.0.0
     */
    public function initialize()
    {
        $priority = 999;
        if ( defined( 'ELEMENTOR_VERSION' ) ) {
            add_filter( 'widget_text', array( $this, 'check_auto_link' ), $priority );
        }
        $this->injector = new Glossary_Term_Injector();
        $this->injector->initialize();
        $this->content = new Glossary_Is_Methods();
        add_filter( 'the_content', array( $this, 'check_auto_link' ), $priority );
        add_filter( 'the_excerpt', array( $this, 'check_auto_link' ), $priority );
    }
    
    /**
     * Return an instance of this class.
     *
     * @since 1.0.0
     *
     * @return object A single instance of this class.
     */
    public static function get_instance()
    {
        // If the single instance hasn't been set, set it now.
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Validate to show the auto link
     *
     * @param string $text The content.
     *
     * @return string
     */
    public function check_auto_link( $text )
    {
        if ( !$this->content->is_already_parsed( $text ) && apply_filters( 'glossary_is_page_to_parse', $this->content->is_page_type_to_check() ) ) {
            return $this->auto_link( $text );
        }
        return $text;
    }
    
    /**
     * That method return the regular expression
     *
     * @param string $term Terms.
     *
     * @return string
     */
    public function search_string( $term )
    {
        $term = preg_quote( $term, '/' );
        $caseinsensitive = '(?i)' . $term . '(?-i)';
        $symbols = '(?=[ \\.\\,\\:\\;\\*\\"\\)\\!\\?\\/\\%\\$\\€\\£\\|\\^\\<\\>\\“\\”])';
        $unicode = 'u';
        
        if ( preg_match( '/[\\p{Han}]/simu', $term ) ) {
            $symbols = '';
            $unicode = '';
        }
        
        /**
         * The regex that Glossary will use for the first step of scanning
         *
         * @param string $regex The regex.
         * @param string $term  The term of the regex.
         *
         * @since 1.1.0
         *
         * @return array $regex We need the regex.
         */
        $regex = apply_filters( 'glossary_regex', '/(?<![\\w\\-\\.\\/]|=")(' . $caseinsensitive . ')' . $symbols . '(?![^<]*(\\/>|<span|<a|<h|<\\/button|<\\/h|<\\/a|<\\/pre|\\"))/' . $unicode, $term );
        return $regex;
    }
    
    /**
     * The magic function that add the glossary terms to your content
     *
     * @param string $text String that wrap with a tooltip/link.
     *
     * @return string
     */
    public function auto_link( $text )
    {
        list( $what_queue, $term_taxs ) = $this->get_term_queue();
        /**
         * Use a different set of terms and avoid the WP_Query
         *
         * @param array $term_queue The terms.
         * @since 1.5.0
         *
         * @return array $term_queue We need the terms.
         */
        $this->terms_queue = apply_filters( 'glossary_custom_terms', $this->terms_queue );
        
        if ( empty($this->terms_queue[$what_queue]) ) {
            $query_args = $this->get_query_args( $term_taxs );
            $gl_query = new WP_Query( $query_args );
            if ( !$gl_query->have_posts() ) {
                return $text;
            }
            while ( $gl_query->have_posts() ) {
                if ( empty(get_the_title()) ) {
                    continue;
                }
                $gl_query->the_post();
                $id_term = get_the_ID();
                $term_value = $this->get_lower( get_the_title() );
                $this->default_term_parameters( $id_term, $term_value );
                if ( !isset( $this->terms_queue[$what_queue][$term_value] ) ) {
                    // Add tooltip based on the title of the term
                    $this->enqueue_term( $id_term, get_the_title( $id_term ), $what_queue );
                }
                $this->enqueue_related_post( $id_term, $what_queue );
            }
            wp_reset_postdata();
            /**
             * All the terms parsed in array
             *
             * @param array $term_queue The terms.
             *
             * @since 1.4.4
             *
             * @return array $term_queue We need the terms.
             */
            $this->terms_queue[$what_queue] = apply_filters( 'glossary_terms_results', $this->terms_queue[$what_queue] );
            // We need to sort by long to inject the long version of terms and not the most short
            usort( $this->terms_queue[$what_queue], 'gl_sort_by_long' );
        }
        
        $new_text = $this->injector->do_wrap( $text, $this->terms_queue[$what_queue] );
        return $new_text;
    }
    
    /**
     * Set default parameters
     *
     * @param integer $id_term The term id.
     * @param string  $term_value The value.
     */
    public function default_term_parameters( $id_term, $term_value )
    {
        $this->parameters = array();
        $this->parameters['url'] = get_post_meta( $id_term, GT_SETTINGS . '_url', true );
        $this->parameters['type'] = get_post_meta( $id_term, GT_SETTINGS . '_link_type', true );
        $this->parameters['link'] = get_glossary_term_url( $id_term );
        $this->parameters['target'] = get_post_meta( $id_term, GT_SETTINGS . '_target', true );
        $this->parameters['nofollow'] = get_post_meta( $id_term, GT_SETTINGS . '_nofollow', true );
        $this->parameters['noreadmore'] = false;
        // Get the post of the glossary loop
        if ( empty($this->parameters['url']) && empty($this->parameters['type']) || $this->parameters['type'] === 'internal' ) {
            $this->parameters['noreadmore'] = true;
        }
        $this->parameters['hash'] = md5( $term_value );
    }
    
    /**
     * Enqueue the related post
     *
     * @param integer $id_term The term id to search for related.
     * @param string  $what_queue The string about the queue type.
     */
    public function enqueue_related_post( $id_term, $what_queue )
    {
        // Add tooltip based on the related post term of the term
        $related = gl_related_post_meta( get_post_meta( $id_term, GT_SETTINGS . '_tag', true ) );
        if ( is_array( $related ) ) {
            foreach ( $related as $value ) {
                
                if ( !empty($value) ) {
                    $term_value = $this->get_lower( $value );
                    if ( !isset( $this->terms_queue[$what_queue][$term_value] ) ) {
                        $this->enqueue_term( $id_term, $value, $what_queue );
                    }
                }
            
            }
        }
    }
    
    /**
     * Enqueue the term
     *
     * @param integer $id_term The term id to search for related.
     * @param string  $value   The text.
     * @param string  $what_queue The string about the queue type.
     */
    public function enqueue_term( $id_term, $value, $what_queue )
    {
        $this->terms_queue[$what_queue][$value] = array(
            'value'      => $value,
            'regex'      => $this->search_string( $value ),
            'link'       => $this->parameters['link'],
            'term_ID'    => $id_term,
            'target'     => $this->parameters['target'],
            'nofollow'   => $this->parameters['nofollow'],
            'noreadmore' => $this->parameters['noreadmore'],
            'long'       => gl_get_len( $value ),
            'hash'       => $this->parameters['hash'],
        );
    }
    
    /**
     * Return a lower string using the settings
     *
     * @param string $term The term.
     *
     * @return string
     */
    public function get_lower( $term )
    {
        return $term;
    }
    
    /**
     * Return the queue for that post
     *
     * @return string
     */
    public function get_term_queue()
    {
        $what_queue = 'general';
        $term_taxs = array();
        return array( $what_queue, $term_taxs );
    }
    
    /**
     * Set query args
     *
     * @param array $term_taxs Term taxs.
     *
     * @return array
     */
    public function get_query_args( $term_taxs )
    {
        $query_args = array(
            'post_type'              => 'glossary',
            'order'                  => 'ASC',
            'orderby'                => 'title',
            'posts_per_page'         => -1,
            'post_status'            => 'publish',
            'no_found_rows'          => true,
            'update_post_term_cache' => false,
            'glossary_auto_link'     => true,
        );
        if ( gl_get_bool_settings( 'match_same_page' ) ) {
            $query_args['post__not_in'] = array( get_the_ID() );
        }
        return $query_args;
    }

}
$gt_search_engine = new Glossary_Search_Engine();
$gt_search_engine->initialize();