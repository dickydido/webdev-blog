<?php

/**
 * Glossary_Tooltip_Engine
 *
 * @package   Glossary
 * @author    Codeat <support@codeat.co>
 * @copyright 2015 GPL
 * @license   GPL-2.0+
 * @link      http://codeat.co
 */
/**
 * Engine system that add the tooltips
 */
class Glossary_Tooltip_Engine
{
    /**
     * Instance of this class.
     *
     * @var object
     */
    protected static  $instance = null ;
    /**
     * Load the settings
     */
    public function __construct()
    {
        $this->settings = gl_get_settings();
        $is_page = new Glossary_Is_Methods();
        if ( $is_page->is_amp() ) {
            $this->settings['tooltip'] = 'link';
        }
    }
    
    /**
     * Get the excerpt by our limit
     *
     * @param object  $theid      The ID.
     * @param boolean $noreadmore This link it's internal?.
     * @param boolean $link       The link.
     * @return string
     */
    public function get_the_excerpt( $theid, $noreadmore = false, $link = '' )
    {
        $excerpt = $theid;
        
        if ( is_numeric( $theid ) ) {
            $term = get_post( $theid );
            $excerpt = $term->post_excerpt;
            if ( empty($excerpt) ) {
                $excerpt = $term->post_content;
            }
        }
        
        // We cannot use wp_strip_all_tags because remove HTML lists
        // and span cannot include lists so this is a workaround
        $excerpt = str_replace( '<li>', '• ', $excerpt );
        $excerpt = str_replace( '<ul>', '<br>', $excerpt );
        $excerpt = str_replace( '</li>', '<br>', $excerpt );
        // Code extracted from wp_strip_all_tags
        $excerpt = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', $excerpt );
        $excerpt = strip_tags( $excerpt, '<br>' );
        $excerpt = preg_replace( '/[\\r\\n\\t ]+/', ' ', trim( $excerpt ) );
        /**
         * Filter the excerpt before printing
         *
         * @param string $excerpt The excerpt.
         * @param string $theid      The ID.
         *
         * @since 1.2.0
         *
         * @return string $excerpt The excerpt filtered.
         */
        $excerpt = apply_filters( 'glossary_excerpt', $excerpt, $theid );
        $readmore = $this->noreadmore( $link, $noreadmore );
        return $this->elaborate_the_excerpt( $excerpt, $readmore );
    }
    
    /**
     * Generate the read more link
     *
     * @param number $link       The real link.
     * @param string $noreadmore The read more text.
     *
     * @return string
     */
    public function noreadmore( $link, $noreadmore )
    {
        if ( $noreadmore ) {
            return '';
        }
        if ( !empty($this->settings['more_link']) ) {
            return '';
        }
        $text_readmore = __( 'More', 'glossary-by-codeat' );
        if ( !empty($this->settings['more_link_text']) ) {
            $text_readmore = $this->settings['more_link_text'];
        }
        return ' <a href="' . $link . '">' . $text_readmore . '</a>';
    }
    
    /**
     * Limit excerpt based on the settings
     *
     * @param string $excerpt  The excerpt.
     * @param string $readmore The read more link.
     *
     * @return string
     */
    public function limit_excerpt( $excerpt, $readmore )
    {
        $excerpt_temp = $excerpt;
        
        if ( absint( $this->settings['excerpt_limit'] ) !== 0 ) {
            if ( strlen( $excerpt ) >= absint( $this->settings['excerpt_limit'] ) ) {
                $excerpt_temp = substr( $excerpt, 0, absint( $this->settings['excerpt_limit'] ) ) . '...' . $readmore;
            }
            // Strip the excerpt based on the words or char limit
            
            if ( !empty($this->settings['excerpt_words']) ) {
                $char_limit = absint( $this->settings['excerpt_limit'] );
                if ( strlen( $excerpt ) >= $char_limit ) {
                    $excerpt_temp = wp_trim_words( $excerpt, $char_limit, '' ) . '...' . $readmore;
                }
            }
        
        }
        
        return $excerpt_temp;
    }
    
    /**
     * Elaborate the excerpt based on the settings
     *
     * @param string $excerpt  The excerpt.
     * @param string $readmore The read more link.
     *
     * @return string
     */
    public function elaborate_the_excerpt( $excerpt, $readmore )
    {
        $excerpt = $this->limit_excerpt( $excerpt, $readmore );
        $excerpt = str_replace( array(
            '<b...',
            '<...',
            '<br...',
            '<br>...'
        ), '...', $excerpt );
        return trim( $excerpt );
    }
    
    /**
     * Add a tooltip for your terms
     *
     * @param string $html_link  The HTML link.
     * @param object $theid      The ID.
     * @param string $noreadmore It is internal the link?.
     * @param string $link       The link itself.
     *
     * @return string
     */
    public function tooltip_html(
        $html_link,
        $theid,
        $noreadmore,
        $link
    )
    {
        $class = 'glossary-link';
        $excerpt = $this->get_the_excerpt( $theid, $noreadmore, $link );
        if ( !empty($this->settings['external_icon']) ) {
            if ( strpos( $link, get_site_url() ) !== 0 ) {
                $class .= ' glossary-external-link';
            }
        }
        $tooltip = '<span class="glossary-tooltip">' . '<span class="' . $class . '">' . $html_link . '</span>';
        $tooltip_container = '<span class="hidden glossary-tooltip-content clearfix">';
        $tooltip .= $tooltip_container;
        $photo = $this->generate_photo( $theid );
        $tooltip .= $photo;
        $tooltip .= '<span class="glossary-tooltip-text">' . $excerpt . '</span>' . '</span>' . '</span>';
        /**
         * Filter the HTML generated
         *
         * @param string $tooltip The tooltip.
         * @param string $title   The title of the term.
         * @param string $excerpt The excerpt.
         * @param string $photo   Photo.
         * @param string $post    The post object.
         * @param string $noreadmore The internal html link.
         *
         * @since 1.2.0
         *
         * @return string $html The tooltip filtered.
         */
        return apply_filters(
            'glossary_tooltip_html',
            $tooltip,
            $excerpt,
            $photo,
            $theid,
            $noreadmore
        );
    }
    
    /**
     * Generate the thumbnail for the tooltip
     *
     * @param object $theid The ID.
     *
     * @return string
     */
    public function generate_photo( $theid )
    {
        $theme = gl_get_settings();
        $photo = '';
        
        if ( !in_array( $theme['tooltip_style'], array( 'box', 'line' ), true ) ) {
            $photo = get_the_post_thumbnail( $theid, 'thumbnail' );
            if ( !empty($photo) && !empty($this->settings['t_image']) ) {
                return $photo;
            }
        }
        
        return $photo;
    }
    
    /**
     * Generate a link or the tooltip
     *
     * @param string $atts Parameters.
     *
     * @global object $post The post object.
     *
     * @return string
     */
    public function link_or_tooltip( $atts )
    {
        $atts = $this->set_atts( $atts );
        $class = $this->set_class( $atts );
        $html = '<a href="' . $atts['link'] . '"' . $atts['target'] . $atts['nofollow'] . $class . '>' . $atts['replace'] . '</a>';
        if ( $this->is_tooltip_set_as( 'link', false ) ) {
            $html = $this->tooltip_html(
                $html,
                $atts['term_ID'],
                $atts['noreadmore'],
                $atts['link']
            );
        }
        return $html;
    }
    
    /**
     * Set atts by other atts
     *
     * @param array $atts The attribute of tooltip.
     *
     * @return array
     */
    public function set_atts( $atts )
    {
        
        if ( !empty($atts['link']) ) {
            if ( !empty($this->settings['open_new_window']) || !empty($atts['target']) ) {
                $atts['target'] = ' target="_blank"';
            }
            if ( !empty($atts['nofollow']) ) {
                $atts['nofollow'] = ' rel="nofollow"';
            }
        }
        
        return $atts;
    }
    
    /**
     * Return the class of tooltip based on atts and settings
     *
     * @param array $atts The attribute of tooltip.
     *
     * @return string
     */
    public function set_class( $atts )
    {
        $class = '';
        if ( !empty($this->settings['external_icon']) ) {
            if ( strpos( $atts['link'], get_site_url() ) !== 0 ) {
                $class = 'glossary-external-link ';
            }
        }
        if ( !empty($class) ) {
            $class = ' class="' . $class . '"';
        }
        return $class;
    }
    
    /**
     * Return the tooltip type
     *
     * @param string  $type    The type of tooltip.
     * @param boolean $as_true As inverse.
     *
     * @return boolean
     */
    public function is_tooltip_set_as( $type, $as_true = true )
    {
        if ( !$as_true ) {
            return isset( $this->settings['tooltip'] ) && $this->settings['tooltip'] !== $type;
        }
        return isset( $this->settings['tooltip'] ) && $this->settings['tooltip'] === $type;
    }

}