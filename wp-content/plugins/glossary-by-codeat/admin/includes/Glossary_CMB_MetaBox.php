<?php

/**
 * The CMB code
 *
 * @package   Glossary
 * @author    Codeat <support@codeat.co>
 * @copyright 2016 GPL 2.0+
 * @license   GPL-2.0+
 * @link      http://codeat.co
 */
/**
 * Provide CMB
 */
class Glossary_CMB_MetaBox
{
    /**
     * Initialize the class
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $plugin = Glossary::get_instance();
        $this->cpts = $plugin->get_cpts();
        $this->settings = gl_get_settings();
        if ( empty($this->settings['posttypes']) ) {
            $this->settings['posttypes'] = array( 'post' );
        }
        add_action( 'cmb2_init', array( $this, 'glossary_post_types' ) );
        add_action( 'cmb2_init', array( $this, 'glossary' ) );
    }
    
    /**
     * Metabox for post types
     *
     * @return void
     */
    public function glossary_post_types()
    {
        $cmb_post = new_cmb2_box( array(
            'id'           => 'glossary_post_metabox',
            'title'        => __( 'Glossary Post Override', 'glossary-by-codeat' ),
            'object_types' => $this->settings['posttypes'],
            'context'      => 'normal',
            'priority'     => 'high',
            'show_names'   => true,
        ) );
        $cmb_post->add_field( array(
            'name' => __( 'Disable Glossary for this post', 'glossary-by-codeat' ),
            'id'   => GT_SETTINGS . '_disable',
            'type' => 'checkbox',
        ) );
    }
    
    /**
     * Metabox for glossary post type
     *
     * @return void
     */
    public function glossary()
    {
        $cmb = new_cmb2_box( array(
            'id'           => 'glossary_metabox',
            'title'        => __( 'Glossary Auto-Link settings', 'glossary-by-codeat' ),
            'object_types' => $this->cpts,
            'context'      => 'normal',
            'priority'     => 'high',
            'show_names'   => true,
        ) );
        $cmb->add_field( array(
            'name' => __( 'Additional key terms for this definition', 'glossary-by-codeat' ),
            'desc' => __( 'Case-insensitive. To add more than one, separate them with commas', 'glossary-by-codeat' ),
            'id'   => GT_SETTINGS . '_tag',
            'type' => 'text',
        ) );
        $cmb->add_field( array(
            'name'    => __( 'What type of link?', 'glossary-by-codeat' ),
            'id'      => GT_SETTINGS . '_link_type',
            'type'    => 'radio',
            'default' => 'external',
            'options' => array(
            'external' => 'External URL',
            'internal' => 'Internal URL',
        ),
        ) );
        $cmb->add_field( array(
            'name'      => __( 'Link to external URL', 'glossary-by-codeat' ),
            'desc'      => __( 'If this is left blank, the previous options defaults back and key term is linked to internal definition page', 'glossary-by-codeat' ),
            'id'        => GT_SETTINGS . '_url',
            'type'      => 'text_url',
            'protocols' => array( 'http', 'https' ),
        ) );
        $cmb->add_field( array(
            'name'        => __( 'Internal', 'glossary-by-codeat' ),
            'desc'        => __( 'Select a post type of your site', 'glossary-by-codeat' ),
            'id'          => GT_SETTINGS . '_cpt',
            'type'        => 'post_search_text',
            'select_type' => 'radio',
            'onlyone'     => true,
        ) );
        if ( empty($this->settings['open_new_window']) ) {
            $cmb->add_field( array(
                'name' => __( 'Open external link in a new window', 'glossary-by-codeat' ),
                'id'   => GT_SETTINGS . '_target',
                'type' => 'checkbox',
            ) );
        }
        $cmb->add_field( array(
            'name' => __( 'Mark this link as "No Follow"', 'glossary-by-codeat' ),
            'desc' => __( 'To learn more about No-Follow links, check <a href="https://support.google.com/webmasters/answer/96569?hl=en">this article</a>', 'glossary-by-codeat' ),
            'id'   => GT_SETTINGS . '_nofollow',
            'type' => 'checkbox',
        ) );
    }

}
new Glossary_CMB_MetaBox();