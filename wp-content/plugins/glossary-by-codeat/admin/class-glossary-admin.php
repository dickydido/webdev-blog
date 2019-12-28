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
 * Plugin class. This class should ideally be used to work with the
 * administrative side of the WordPress site.
 */
class Glossary_Admin
{
    /**
     * Instance of this class.
     *
     * @var object
     */
    protected static  $instance = null ;
    /**
     * Initialize the plugin by loading admin scripts & styles and adding a
     * settings page and menu.
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function __construct()
    {
        // Add an action link pointing to the options page.
        $plugin_basename = plugin_basename( plugin_dir_path( realpath( dirname( __FILE__ ) ) ) . GT_SETTINGS . '.php' );
        add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );
        $this->includes();
        new Yoast_I18n_WordPressOrg_V3( array(
            'textdomain'  => 'glossary-by-codeat',
            'plugin_name' => GT_NAME,
            'hook'        => 'admin_notices',
        ), true );
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
    
    public function includes()
    {
        /*
         * Enqueue files on admin
         */
        require_once plugin_dir_path( __FILE__ ) . 'includes/Glossary_Admin_Enqueue.php';
        require_once plugin_dir_path( __FILE__ ) . 'includes/Glossary_Admin_Widget.php';
        /*
         * Import Export settings
         */
        require_once plugin_dir_path( __FILE__ ) . 'includes/Glossary_ImpExp.php';
        /**
         * CMB support
         */
        require_once plugin_dir_path( __FILE__ ) . 'includes/Glossary_CMB.php';
        require_once plugin_dir_path( __FILE__ ) . 'includes/WP_Admin_Notice.php';
        require_once plugin_dir_path( __FILE__ ) . 'includes/i18n-module/i18n-v3.php';
        require_once plugin_dir_path( __FILE__ ) . 'includes/i18n-module/i18n-wordpressorg-v3.php';
    }
    
    /**
     * Render the settings page for this plugin.
     *
     * @since 1.0.0
     *
     * @return void
     */
    public static function display_plugin_admin_page()
    {
        include_once 'views/admin.php';
    }
    
    /**
     * Add settings action link to the plugins page.
     *
     * @param array $links The list of links.
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function add_action_links( $links )
    {
        return array_merge( array(
            'settings' => '<a href="' . admin_url( 'edit.php?post_type=glossary&page=' . GT_SETTINGS ) . '">' . __( 'Settings', 'glossary-by-codeat' ) . '</a>',
        ), $links );
    }

}