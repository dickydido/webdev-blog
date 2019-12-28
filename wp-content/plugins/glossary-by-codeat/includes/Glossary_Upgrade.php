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
 * The Upgrade system
 */
class Glossary_Upgrade {

	/**
	 * Initialize the class
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function __construct() {
		register_activation_hook( 'glossary-by-codeat/glossary-by-codeat.php', array( __CLASS__, 'activate' ) );
		add_action( 'admin_init', array( $this, 'activate' ) );
    }

    /**
     * Check if the page and user is admin to do the upgrade
     *
     * @return bool
     */
    public static function can_current_page_upgrade() {
        return is_admin() && ( function_exists( 'wp_doing_ajax' ) && !wp_doing_ajax() ) || !defined( 'DOING_AJAX' ) && current_user_can( 'manage_options' );
    }

    /**
     * Do the routine for upgrades
     *
     * @return void
     */
    public static function activate() {
        if ( self::can_current_page_upgrade() ) {
            $version = get_option( 'glossary-version' );
            if ( version_compare( GT_VERSION, $version, '>' ) ) {
                include_once 'Requirements/requirements.php';
                new Plugin_Requirements(
                    GT_NAME,
                    'glossary-by-codeat',
                    array(
                        'WP'        => new WordPress_Requirement( '4.7.0' ),
                        'Extension' => new PHP_Extension_Requirement( array( 'mbstring' ) ),
                    )
                );
                self::add_admin_cap();
                update_option( 'glossary-version', GT_VERSION );
            }

            self::run_upgrades_by_version( $version );
        }
    }

    /**
     * Execute the various version upgrades
     *
     * @param string $version The actual version.
     */
    public static function run_upgrades_by_version( $version ) {
        if ( version_compare( $version, '1.3', '<' ) ) {
            include_once 'Upgrade/glossary-1-3.php';
        }

        if ( version_compare( $version, '1.5', '<' ) ) {
            include_once 'Upgrade/glossary-1-5.php';
        }

        if ( version_compare( $version, '1.6', '<' ) ) {
            include_once 'Upgrade/glossary-1-6.php';
        }

        if ( version_compare( $version, '1.7', '<' ) ) {
            include_once 'Upgrade/glossary-1-7.php';
        }

        if ( version_compare( GT_VERSION, $version, '>' ) ) {
            flush_rewrite_rules();
        }
    }

    /**
     * Add admin capabilities
     *
     * @return void
     */
    public static function add_admin_cap() {
        $caps  = array(
            'create_glossaries',
            'read_glossary',
            'read_private_glossaries',
            'edit_glossary',
            'edit_glossaries',
            'edit_private_glossaries',
            'edit_published_glossaries',
            'edit_others_glossaries',
            'publish_glossaries',
            'delete_glossary',
            'delete_glossaries',
            'delete_private_glossaries',
            'delete_published_glossaries',
            'delete_others_glossaries',
            'manage_glossaries',
        );
        $roles = array(
            get_role( 'administrator' ),
            get_role( 'editor' ),
            get_role( 'author' ),
            get_role( 'contributor' ),
            get_role( 'subscriber' ),
        );
        foreach ( $roles as $role ) {
            if ( !is_null( $role ) ) {
                foreach ( $caps as $cap ) {
                    $role->add_cap( $cap );
                }
            }
        }

        $bad_caps = array(
            'create_glossaries',
            'read_private_glossaries',
            'edit_glossary',
            'edit_glossaries',
            'edit_private_glossaries',
            'edit_published_glossaries',
            'edit_others_glossaries',
            'publish_glossaries',
            'delete_glossary',
            'delete_glossaries',
            'delete_private_glossaries',
            'delete_published_glossaries',
            'delete_others_glossaries',
            'manage_glossaries',
        );
        $roles    = array(
            get_role( 'author' ),
            get_role( 'contributor' ),
            get_role( 'subscriber' ),
        );
        foreach ( $roles as $role ) {
            if ( !is_null( $role ) ) {
                foreach ( $bad_caps as $cap ) {
                    $role->remove_cap( $cap );
                }
            }
        }
    }

}

new Glossary_Upgrade();

