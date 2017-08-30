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

   $default_footer_value = sprintf( __( 'Patron: His Honour, The Honourable Arthur J. LeBlanc, ONS, QC, Lieutenant Governor of Nova Scotia, Lieutenant Governor of Nova Scotia.<br/> Copyright &copy; %1$s %2$s. All rights 
reserved.', 'colormag' 
), 
date( 'Y' ), $site_link );

   $colormag_footer_copyright = <<<HEREDOC
<div class="copyright">{$default_footer_value}
</div>
HEREDOC;

   echo $colormag_footer_copyright;
}

function rusi_wp_nav_menu_args( $args = '' ) 
{
    if( is_user_logged_in() ) {
        $args['menu'] = 'Logged In Main Menu';
    } else {
        $args['menu'] = 'Main Menu';
    }
    return $args;
}

add_filter( 'wp_nav_menu_args', 'rusi_wp_nav_menu_args' );

?>
