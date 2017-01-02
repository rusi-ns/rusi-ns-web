<?php
function rusi_enqueue_styles() {

    $parent_style = 'colormag-style';

    wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array( $parent_style ),
        wp_get_theme()->get('Version')
    );
}
add_action( 'wp_enqueue_scripts', 'rusi_enqueue_styles' );


/**
 * function to show the footer info, copyright information
 */
function colormag_footer_copyright() {
   $site_link = '<a href="' . esc_url( home_url( '/' ) ) . '" title="' . esc_attr( get_bloginfo( 'name', 'display' ) ) . '" ><span>' . get_bloginfo( 'name', 'display' ) . '</span></a>';

   $default_footer_value = sprintf( __( 'Copyright &copy; %1$s %2$s. All rights reserved.', 'colormag' ), date( 'Y' ), $site_link );

   $colormag_footer_copyright = '<div class="copyright">'.$default_footer_value.'</div>';
   echo $colormag_footer_copyright;
}

?>
