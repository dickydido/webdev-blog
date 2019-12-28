<?php

/**
 * Glossary_Is_Methods
 *
 * @package   Glossary
 * @author    Codeat <support@codeat.co>
 * @copyright 2017 GPL
 * @license   GPL-2.0+
 * @link      http://codeat.co
 */
/**
 * Support for the Genesis framework
 */
class Glossary_Is_Methods
{
    /**
     * Initialize the class with all the hooks
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->settings = gl_get_settings();
    }
    
    /**
     * We can inject Glossary
     *
     * @return boolean
     */
    public function is_page_type_to_check()
    {
        return !$this->is_head() && ($this->is_feed() || $this->is_singular() || $this->is_home() || $this->is_category() || $this->is_tag() || $this->is_arc_glossary() || $this->is_tax_glossary() || $this->is_yoast());
    }
    
    /**
     * If the most parent hok is the head block the check
     *
     * @return boolean
     */
    public function is_head()
    {
        global  $wp_current_filter ;
        return $wp_current_filter[0] === 'wp_head';
    }
    
    /**
     * Check the settings and if is a single page
     *
     * @return boolean
     */
    public function is_singular()
    {
        if ( isset( $this->settings['posttypes'] ) && is_singular( $this->settings['posttypes'] ) ) {
            return !(get_post_meta( get_queried_object_id(), GT_SETTINGS . '_disable', true ) === 'on');
        }
        return false;
    }
    
    /**
     * Check the settings and if is the home page
     *
     * @return boolean
     */
    public function is_home()
    {
        return isset( $this->settings['is'] ) && in_array( 'home', $this->settings['is'], true ) && is_home();
    }
    
    /**
     * Check the settings and if is a category page
     *
     * @return boolean
     */
    public function is_category()
    {
        return isset( $this->settings['is'] ) && in_array( 'category', $this->settings['is'], true ) && is_category();
    }
    
    /**
     * Check the settings and if is tag
     *
     * @return boolean
     */
    public function is_tag()
    {
        return isset( $this->settings['is'] ) && in_array( 'tag', $this->settings['is'], true ) && is_tag();
    }
    
    /**
     * Check the settings and if is an archive glossary
     *
     * @return boolean
     */
    public function is_arc_glossary()
    {
        return isset( $this->settings['is'] ) && in_array( 'arc_glossary', $this->settings['is'], true ) && is_post_type_archive( 'glossary' );
    }
    
    /**
     * Check the settings and if is a tax glossary page
     *
     * @return boolean
     */
    public function is_tax_glossary()
    {
        return isset( $this->settings['is'] ) && in_array( 'tax_glossary', $this->settings['is'], true ) && is_tax( 'glossary-cat' );
    }
    
    /**
     * Check the settings and if is a feed page
     *
     * @return boolean
     */
    public function is_feed()
    {
        return false;
    }
    
    /**
     * Check if it is Yoast link watcher
     *
     * @return boolean
     */
    public function is_yoast()
    {
        return is_admin() && defined( 'WPSEO_FILE' ) && get_the_ID() !== false && !isset( $_GET['revision'] );
        // phpcs:ignore
    }
    
    /**
     * Check if it is not AMP
     *
     * @return boolean
     */
    public function is_amp()
    {
        return function_exists( 'is_amp_endpoint' ) && is_amp_endpoint();
    }
    
    /**
     * Compare the hash version of all the text processed with the one in input
     *
     * @param string $text Text to compare.
     *
     * @return bool
     */
    public function is_already_parsed( $text )
    {
        if ( empty($text) ) {
            return true;
        }
        if ( strpos( $text, '"glossary-' ) !== false && strpos( $text, ' glossary-' ) !== false ) {
            return true;
        }
        return false;
    }

}