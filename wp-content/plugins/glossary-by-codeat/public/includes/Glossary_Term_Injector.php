<?php

/**
 * Glossary_Term_Injector
 *
 * @package   Glossary
 * @author    Codeat <support@codeat.co>
 * @copyright 2015 GPL
 * @link      http://codeat.co
 * @license   GPL-2.0+
 */
/**
 * EProcess the content to inject tooltips
 */
class Glossary_Term_Injector
{
    /**
     * Initialize the class with all the hooks
     *
     * @since 1.0.0
     */
    public function initialize()
    {
        $this->terms = array();
        $this->already_find = array();
    }
    
    /**
     * Wrap the string with a tooltip/link.
     *
     * @param string $text  The string to find.
     * @param array  $terms The list of links.
     *
     * @return string
     */
    public function do_wrap( $text, $terms )
    {
        $this->terms = $this->already_find = array();
        
        if ( !empty($text) && !empty($terms) ) {
            $text = trim( $text );
            $this->regex_match( $text, $terms );
            if ( !empty($this->terms) ) {
                $text = $this->replace_with_utf_8( $text );
            }
            if ( !empty($this->terms) && function_exists( 'iconv' ) ) {
                // This eventually remove broken UTF-8
                return iconv( 'UTF-8', 'UTF-8//IGNORE', $text );
            }
        }
        
        return $text;
    }
    
    /**
     * Find terms with the regex
     *
     * @param string $text The text to analyze.
     * @param array  $terms The list of terms.
     *
     * @return array The list of terms finded in the text.
     */
    public function regex_match( $text, $terms )
    {
        foreach ( $terms as $term ) {
            try {
                $this->create_html_pair( $term, $text );
            } catch ( Exception $e ) {
                error_log( $e->getMessage() . ', regex:' . $term['regex'] );
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log, Squiz.PHP.DiscouragedFunctions.Discouraged -- In few cases was helpful on debugging.
            }
        }
        return $this->terms;
    }
    
    /**
     * Inject based on the settings
     *
     * @param array $term List of terms.
     * @param text  $text The content.
     */
    public function create_html_pair( $term, $text )
    {
        $matches = array();
        $generate = new Glossary_Tooltip_Engine();
        if ( preg_match_all(
            $term['regex'],
            $text,
            $matches,
            PREG_OFFSET_CAPTURE
        ) ) {
            foreach ( $matches[0] as $match ) {
                list( $term['replace'], $text_found ) = $match;
                
                if ( !$this->is_already_find( $text_found, $term ) ) {
                    $this->terms[$text_found] = array( $term['long'], $generate->link_or_tooltip( $term ), $term['replace'] );
                    $this->already_find[$text_found] = $text_found + $term['long'];
                    if ( gl_get_bool_settings( 'first_occurrence' ) ) {
                        break;
                    }
                }
            
            }
        }
    }
    
    /**
     * Is already find
     *
     * @param array $text_found Found.
     * @param text  $term       Term data.
     */
    public function is_already_find( $text_found, $term )
    {
        // Avoid annidate detection
        foreach ( $this->already_find as $previous_init => $previous_end ) {
            if ( $previous_init <= $text_found && $text_found + $term['long'] <= $previous_end ) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Replace the terms with the link or tooltip with UTF-8 support
     *
     * @param string $text The text to analyze.
     *
     * @return string The new text.
     */
    public function replace_with_utf_8( $text )
    {
        uksort( $this->terms, 'strnatcmp' );
        $new_pos = key( $this->terms );
        // Copy of text is required for replace
        $new_term_length = $old_pos = 0;
        $new_text = $text;
        $old_term_length = '';
        foreach ( $this->terms as $pos => $term ) {
            list( $length, $term_value, $value ) = $term;
            // Calculate the cursor position after the first loop
            
            if ( $old_pos !== 0 ) {
                $old_pos_temp = $pos - ($old_pos + $old_term_length);
                $new_pos += $new_term_length + $old_pos_temp;
            }
            
            $new_term_length = gl_get_len( $term_value );
            $old_term_length = $real_length = $length;
            $encode = mb_detect_encoding( $value );
            // With utf-8 character with multiple bits this is the workaround for the right value
            if ( $encode !== 'ASCII' ) {
                
                if ( gl_text_is_rtl( $text ) ) {
                    $multiply = 0;
                    // Seems that when there are symbols I need to add 2 for every of them
                    $multiply += mb_substr_count( $text, '-' ) + mb_substr_count( $text, '.' ) + mb_substr_count( $text, ':' );
                    if ( $multiply > 0 ) {
                        $real_length += $multiply * 2;
                    }
                    $real_length += $real_length;
                }
            
            }
            // 0 is the term long, 1 is the new html
            $new_text = substr_replace(
                $new_text,
                $term_value,
                $new_pos,
                $real_length
            );
            $old_pos = $pos;
        }
        return $new_text;
    }

}