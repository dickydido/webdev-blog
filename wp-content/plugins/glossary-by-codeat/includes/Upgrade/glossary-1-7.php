<?php

/**
 * Upgrades for Glossary 1.7
 *
 * @package Glossary
 */

// Typo on ids of settings
$settings = gl_get_settings();

if ( isset( $settings[ 'slug-cat' ] ) ) {
    $settings[ 'slug_cat' ] = $settings[ 'slug-cat' ];
    unset( $settings[ 'slug-cat' ] );
}

if ( isset( $settings[ 'label-single' ] ) ) {
    $settings[ 'label_single' ] = $settings[ 'label-single' ];
    unset( $settings[ 'label-single' ] );
}

if ( isset( $settings[ 'label-multi' ] ) ) {
    $settings[ 'label_multi' ] = $settings[ 'label-multi' ];
    unset( $settings[ 'label-multi' ] );
}

update_option( GT_SETTINGS . '-settings', $settings );
