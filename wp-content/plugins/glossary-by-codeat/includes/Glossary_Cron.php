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
 * The Cron system
 */
class Glossary_Cron {

	/**
	 * Initialize the class
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'count_terms' ) );
		if ( !class_exists( 'CronPlus' ) ) {
            include_once 'CronPlus/cronplus.php';
		}

        $args     = array(
            'recurrence' => 'daily',
            'name'       => 'glossary_terms_counter',
            'cb'         => 'gl_update_counter',
        );
        $cronplus = new CronPlus( $args );
        $cronplus->schedule_event();
    }

    /**
     * Check if the page and user is admin to do the update
     *
     * @return bool
     */
    public function can_update_counter() {
        return is_admin() && ( function_exists( 'wp_doing_ajax' ) && !wp_doing_ajax() ) || !defined( 'DOING_AJAX' ) && current_user_can( 'manage_options' );
    }

    /**
     * Force a manual update of count terms for the caching
     *
     * @return void
     */
    public function count_terms() {
        if ( !$this->can_update_counter() ) {
            return;
        }

        if ( empty( $_GET[ 'gl_count_terms' ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Checking only if empty.
            return;
        }

        gl_update_counter();
    }

}

new Glossary_Cron();
