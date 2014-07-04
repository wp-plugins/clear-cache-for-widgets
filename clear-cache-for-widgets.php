<?php 
/*
Plugin Name: Clear Cache For Me
Plugin URI: http://mymonkeydo.com/caching-with-widgets
Description: Purges all cache on WT3, WP Super Cache, or WPEngine when updating the menus or widgets.  Also adds a button to the dashboard to clear the cache.
Author: Webhead LLC
Author URI: http://webheadcoder.com 
Version: 0.3
*/

/**
 * Only run our stuff if we can do something.
 */
function ccfm_init() {
    if ( ccfm_supported_caching_exists() ) {
        add_action( 'wp_update_nav_menu', 'clear_cache_for_me', 10 );
        add_filter( 'widget_update_callback', 'ccfm_clear_cache_for_widgets', 10 );
        add_action('wp_dashboard_setup', 'ccfm_dashboard_widget' );
        add_action( 'admin_init', 'ccfm_clear_cache_requested' );
        add_action( 'admin_init', 'ccfm_set_capability' );
    }
}
add_action( 'init', 'ccfm_init' );

/**
 * Return true if known caching systems exists.
 */
function ccfm_supported_caching_exists() {
    return function_exists( 'w3tc_pgcache_flush' ) || function_exists( 'wp_cache_clean_cache' ) || class_exists( 'WpeCommon' );
}

/**
 * Clear the caches!
 */
function ccfm_clear_cache_for_me() {
    // if W3 Total Cache is being used, clear the cache
    if ( function_exists( 'w3tc_pgcache_flush' ) ) { 
        w3tc_pgcache_flush(); 
    }
    // if WP Super Cache is being used, clear the cache
    else if ( function_exists( 'wp_cache_clean_cache' ) ) {
        global $file_prefix;
        wp_cache_clean_cache( $file_prefix );
    }
    else if ( class_exists( 'WpeCommon' ) ) {
        WpeCommon::purge_memcached();
        WpeCommon::clear_maxcdn_cache();
        WpeCommon::purge_varnish_cache();   
    }
}

/**
 * Clear the caches for widgets.
 */
function ccfm_clear_cache_for_widgets( $instance ) {
    ccfm_clear_cache_for_me();
    return $instance;
}

/**
 * Add a button to clear the cache on the dashboard.
 */
function ccfm_dashboard_widget() {
    $needed_cap = get_option( 'ccfm_permission', 'manage_options' );
    if ( current_user_can( $needed_cap ) ) {
        wp_add_dashboard_widget('dashboard_ccfm_widget', 'Clear Cache for Me', 'ccfm_dashboard_widget_output');       
    }
}

function ccfm_dashboard_widget_output() {
    global $wp_roles; 
    if ( current_user_can( 'manage_options' ) ) {
        $roles = $wp_roles->roles;
        $caps = array();
        foreach( $roles as $role ) {
            if ( !empty( $role['capabilities'] ) ) {
                foreach ( $role['capabilities'] as $capability => $val ) {
                    $caps[ $capability ] = $capability;
                }   
            }
        }
        asort( $caps );
    }
    $needed_cap = get_option( 'ccfm_permission', 'manage_options' );
    if ( current_user_can( $needed_cap ) ) : ?>
    <p>
    <form method="get">
        <input type="submit" name="ccfm" class="button button-primary button-large" value="Clear Cache Now!">
    </form>
    </p>
    <?php if ( current_user_can( 'manage_options' ) ) : ?>
    <p>
    <form method="post">
        Show button for users with capability:<br>
        <select name="ccfm_permission">
            <?php foreach ( $caps as $cap ) : ?>
                <option value="<?php echo esc_attr($cap); ?>" <?php selected( $needed_cap, $cap );?>><?php echo $cap; ?></option>
            <?php endforeach; ?>
        </select>
        <input type="submit" class="button button-large" value="Set">
    </form>
    </p>
    <?php
         endif;
    endif;
}

/**
 * Clear the cache if requested.
 */
function ccfm_clear_cache_requested() {
    if ( isset( $_GET['ccfm'] ) ) {
        $needed_cap = get_option( 'ccfm_permission', 'manage_options' );
        if ( current_user_can( $needed_cap ) ) {
            ccfm_clear_cache_for_me();
            add_action( 'admin_notices', 'ccfm_success' );
        }
        else {
            add_action( 'admin_notices', 'ccfm_error' );   
        }
    }
}

/**
 * Set the capability needed to view the button.
 */
function ccfm_set_capability() {
    if ( isset( $_POST['ccfm_permission'] ) ) {
        if ( current_user_can( 'manage_options' ) ) {
            update_option( 'ccfm_permission', sanitize_title( $_POST['ccfm_permission'] ) );
        }
        wp_safe_redirect( admin_url() );
        exit;
    }
}

/**
 * Show the success notice.
 */
function ccfm_success() { ?>
    <div class="updated">
        <p><?php _e( 'Cache cleared!', 'ccfm' ); ?></p>
    </div>
<?php
}

/**
 * Show the error notice.
 */
function ccfm_error() { ?>
    <div class="error">
        <p><?php _e( 'You do not have permission to clear the cache.', 'ccfm' ); ?></p>
    </div>
<?php
}



