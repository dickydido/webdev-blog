<?php
/**
 * Represents the view for the administration dashboard.
 *
 * @package   Glossary
 * @author    Codeat <support@codeat.co>
 * @copyright 2016 GPL 2.0+
 * @license   GPL-2.0+
 * @link      http://codeat.co
 * @phpcs:disable WordPress.Security.EscapeOutput
 */
?>
<div id="tabs-shortcodes" class="metabox-holder">
    <div class="postbox">
        <h3 class="hndle"><span><?php _e( 'Shortcodes available in Free version', 'glossary-by-codeat' ); ?></span>
        </h3>
        <div class="inside">
            <ul>
                <li><b>[glossary-cats]</b> - <?php _e( 'This shortcode will generate an index for your Glossary that will create an indexed page, for all your key terms.', 'glossary-by-codeat' ); ?> [<a href='https://codeat.co/glossary/docs/list-of-categories/' target='_blank'>Documentation</a>]</li>
                <li><b>[glossary-terms]</b> - <?php _e( 'This shortcode will generate a list of your glossary’s terms.', 'glossary-by-codeat' ); ?> [<a href='https://codeat.co/glossary/docs/list-of-terms/' target='_blank'>Documentation</a>]</li>
            </ul>
        </div>
    </div>

    <div class="postbox">
        <h3 class="hndle"><span><?php _e( 'Shortcodes available in PRO version', 'glossary-by-codeat' ); ?></span></h3>
        <div class="inside">
            <ul>
                <li><b>[glossary-list]</b> - <?php _e( 'This PRO shortcode will generate an index for your Glossary that will create an indexed page.', 'glossary-by-codeat' ); ?> [<a href='https://codeat.co/glossary/docs/glossary-index-pro/' target='_blank'>Documentation</a>]</li>
                <li><b>[glossary]</b> - <?php _e( 'This parse a specific content wrap with this shortcode, useful for Visual Composer or Page Builder.', 'glossary-by-codeat' ); ?> [<a href='http://codeat.co/glossary/docs/parse-the-content-that-you-want/' target='_blank'>Documentation</a>]</li>
                <li><b>[glossary-ignore]</b> - <?php _e( 'To prevent Glossary from processing a term, wrap with this shortcode.', 'glossary-by-codeat' ); ?> [<a href='https://codeat.co/glossary/docs/ignore-terms-inside-the-content-pro/' target='_blank'>Documentation</a>]</li>
            </ul>
        </div>
    </div>
</div>
