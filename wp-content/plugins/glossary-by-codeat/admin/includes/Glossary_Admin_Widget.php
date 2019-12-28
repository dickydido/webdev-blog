<?php
/**
 * The ACF support code
 *
 * @package   Glossary
 * @author    Codeat <support@codeat.co>
 * @copyright 2016 GPL 2.0+
 * @license   GPL-2.0+
 * @link      http://codeat.co
 */

/**
 * Provide support for ACF Admin
 */
class Glossary_Admin_Widget {

	/**
	 * Initialize the class
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$plugin     = Glossary::get_instance();
		$this->cpts = $plugin->get_cpts();
		// At Glance Dashboard widget for your cpts
		add_filter( 'dashboard_glance_items', array( $this, 'cpt_glance_dashboard_support' ), 10, 1 );
		// Activity Dashboard widget for your cpts
		add_filter( 'dashboard_recent_posts_query_args', array( $this, 'cpt_activity_dashboard_support' ), 10, 1 );
	}

    /**
     * Add the counter of your CPTs in At Glance widget in the dashboard
     *        Reference:  http://wpsnipp.com/index.php/functions-php/wordpress-post-types-dashboard-at-glance-widget/
     *
     * @param array $items The list of post types.
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function cpt_glance_dashboard_support( $items = array() ) {
        $post_types = $this->cpts;
        foreach ( $post_types as $type ) {
            if ( !post_type_exists( $type ) ) {
                continue;
            }

            $num_posts = wp_count_posts( $type );
            if ( $num_posts ) {
                $published = intval( $num_posts->publish );
                $post_type = get_post_type_object( $type );
                $text      = $published . '  ' . $post_type->labels->singular_name;
                if ( $published > 1 ) {
                    $text = $published . '  ' . $post_type->labels->name;
                }

                if ( current_user_can( $post_type->cap->edit_posts ) ) {
                    $temp = '<a class="' . $post_type->name . '-count" href="edit.php?post_type=' . $post_type->name . '">' . sprintf( '%2$s', $type, $text ) . "</a>\n";
                }

                $items[] = $temp;
            }

            return $items;
        }

    }

    /**
     * Add the recents post type in the activity widget
     *
     * @param array $query_args All the parameters.
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function cpt_activity_dashboard_support( $query_args ) {
        if ( !is_array( $query_args[ 'post_type' ] ) ) {
            // Set default post type
            $query_args[ 'post_type' ] = array( 'page' );
        }

        $query_args[ 'post_type' ] = array_merge( $query_args[ 'post_type' ], $this->cpts );
        return $query_args;
    }

}

new Glossary_Admin_Widget();
