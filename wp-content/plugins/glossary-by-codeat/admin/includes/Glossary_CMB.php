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
class Glossary_CMB
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
        require_once plugin_dir_path( __FILE__ ) . '/CMB2/init.php';
        require_once plugin_dir_path( __FILE__ ) . '/cmb2-extra.php';
        require_once plugin_dir_path( __FILE__ ) . '/cmb2-post-search-field.php';
        add_filter( 'multicheck_posttype_posttypes', array( $this, 'hide_glossary' ) );
        // Add metabox
        require_once plugin_dir_path( __FILE__ ) . '/Glossary_CMB_MetaBox.php';
        add_action(
            'cmb2_save_options-page_fields',
            array( $this, 'permalink_alert' ),
            4,
            9999
        );
    }
    
    /**
     * Hide glossary post type from settings
     *
     * @param array $cpts The cpts.
     *
     * @return array
     */
    public function hide_glossary( $cpts )
    {
        unset( $cpts['attachment'] );
        return $cpts;
    }
    
    /**
     * Prompt a reminder to flush the pernalink
     *
     * @param string $object_id CMB Object ID.
     * @param string $cmb_id    CMB ID.
     * @param string $updated   Status.
     * @param array  $object    The CMB object.
     *
     * @return void
     */
    public function permalink_alert(
        $object_id,
        $cmb_id,
        $updated,
        $object
    )
    {
        
        if ( $cmb_id === GT_SETTINGS . '_options' ) {
            $notice = new WP_Admin_Notice( __( 'You must flush the permalink if you changed the slug, go on Settings->Permalink and press Save changes!', 'glossary-by-codeat' ), 'updated' );
            $notice->output();
        }
    
    }

}
new Glossary_CMB();