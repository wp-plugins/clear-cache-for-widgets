<?php 
/*
Plugin Name: Clear Cache For Me
Plugin URI: http://mymonkeydo.com/caching-with-widgets
Description: Purges all cache on WT3, WP Super Cache, or WPEngine when updating the menus or widgets.  Also adds a button with optional instructions to the dashboard to clear the cache.
Author: Webhead LLC
Author URI: http://webheadcoder.com 
Version: 0.6.1
*/

// locale
function ccfm_plugins_loaded() {
    load_plugin_textdomain( 'ccfm', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
}
add_action( 'plugins_loaded', 'ccfm_plugins_loaded' );


/**
 * Add widget save, reorder and delete detection.  Thanks to Ov3rfly.
 */
function ccfm_admin_init() {
    if ( ccfm_supported_caching_exists() ) {
        ccfm_handle_requests();
        ccfm_set_capability();

        add_action( 'wp_dashboard_setup', 'ccfm_dashboard_widget' );
        
        //add styles for dashboard
        add_action( 'admin_head', 'ccfm_admin_head' );

        //detect widget save, reorder and delete detection.  Thanks to Ov3rfly.
        add_action( 'wp_ajax_save-widget', 'ccfm_clear_cache_for_widgets_wp_ajax_action', 1 );
        add_action( 'wp_ajax_widgets-order', 'ccfm_clear_cache_for_widgets_wp_ajax_action', 1 );
        add_action( 'sidebar_admin_setup', 'ccfm_clear_cache_for_widgets_sidebar_admin_setup' );
        //detect customize theme actions.
        add_action( 'customize_save_after', 'ccfm_clear_cache_for_customized_theme' );

        //detect nav menu changes
        add_action( 'wp_update_nav_menu', 'ccfm_clear_cache_for_menus' );

        do_action( 'ccfm_admin_init' );
    }
}
add_action( 'admin_init', 'ccfm_admin_init' ); // not 'init'

/**
 * Return true if known caching systems exists.
 */
function ccfm_supported_caching_exists() {
    $supported = function_exists( 'w3tc_pgcache_flush' ) || function_exists( 'wp_cache_clean_cache' ) || class_exists( 'WpeCommon' );
    return apply_filters( 'ccfm_supported_caching_exists', $supported );
}

/**
 * Clear the caches!
 */
function ccfm_clear_cache_for_me( $source ) {
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
    do_action( 'ccfm_clear_cache_for_me', $source );
}

/**
 * Clear the caches for menus.
 */
function ccfm_clear_cache_for_menus() {
    ccfm_clear_cache_for_me( 'menu' );
}

/**
 * Clear the caches for widgets.  Thanks to Ov3rfly
 */
function ccfm_clear_cache_for_widgets_wp_ajax_action() {
    // wp-admin/admin-ajax.php
    // 'widgets-order', 'save-widget'
    ccfm_clear_cache_for_me( 'widget' );
}
function ccfm_clear_cache_for_widgets_sidebar_admin_setup() {
    // wp-admin/widgets.php
    // We're saving/deleting a widget without js/ajax
    if ( !empty( $_POST ) && ( isset($_POST['savewidget']) || isset($_POST['removewidget']) ) ) {
        ccfm_clear_cache_for_me( 'widget' );
    }
}

/**
 * Clear the cache when a theme customizations are saved (in Appearance->Customize)
 */
function ccfm_clear_cache_for_customized_theme() {
    ccfm_clear_cache_for_me( 'customize' );   
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
    $needed_cap = get_option( 'ccfm_permission', 'manage_options' );
    if ( current_user_can( $needed_cap ) ) {
        $infotext = get_option( 'ccfm_infotext', '' );
    ?>
<div class="ccf_widget">
    <form method="post">
    <?php echo ( $infotext ) ? '<p>' . $infotext . '</p>' : ''; ?>
    <p>
        <?php wp_nonce_field( 'ccfm' ); ?>
        <input type="submit" name="ccfm" class="button button-primary button-large" value="<?php _e( 'Clear Cache Now!', 'ccfm' ); ?>">
    </p>
    </form>
</div>
    <?php
        if ( current_user_can( 'manage_options' ) ) {
            global $wp_roles;
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
    ?>
<div class="ccf_widget">
    <h4><?php _e( 'Settings', 'ccfm' ); ?></h4>
    <form method="post">
    <label for="ccfm_permission"><?php _e( 'Show button for users with capability:', 'ccfm' ); ?></label>
    <p>
        <select name="ccfm_permission" id="ccfm_permission">
            <?php foreach ( $caps as $cap ) : ?>
                <option value="<?php echo esc_attr($cap); ?>" <?php selected( $needed_cap, $cap );?>><?php echo $cap; ?></option>
            <?php endforeach; ?>
        </select>
    </p>
    <label for="ccfm_infotext"><?php _e( 'Instructions to show above button (optional):', 'ccfm' ); ?></label>
    <p>
        <input id="ccfm_infotext" name="ccfm_infotext" type="text" value="<?php echo esc_attr( $infotext); ?>" />
    </p>
    <p>
        <?php wp_nonce_field( 'ccfm' ); ?>
        <input type="hidden" name="ccfm_set" value="1" />
        <input type="submit" class="button button-large" value="<?php _e( 'Set', 'ccfm' ); ?>">
    </p>
    </form>
</div>
    <?php
        }
    }
}

/**
 * Add some CSS.
 */
function ccfm_admin_head() {
?>
<style type="text/css">
#dashboard_ccfm_widget .inside {
    margin: 0;
    padding: 0;
}
#dashboard_ccfm_widget .ccf_widget {
    border-top: 1px solid #eee;
    font-size: 13px;
    padding: 8px 12px 4px 12px;
}
#dashboard_ccfm_widget .ccf_widget:first-child {
    border-top: none;
}
#dashboard_ccfm_widget p {
    margin: 0 0 8px 0;
}
#dashboard_ccfm_widget label {
    display: block;
    margin: 0 0 4px 0;
    color: #777;
}
#dashboard_ccfm_widget input[name="ccfm_infotext"] {
    width: 80%;
}
</style>
<?php
}


/**
 * Clear the cache if requested.
 */
function ccfm_handle_requests() {
    if ( isset( $_POST['ccfm'] ) ) {
        check_admin_referer( 'ccfm' );
        $needed_cap = get_option( 'ccfm_permission', 'manage_options' );
        $is_success = 0;
        if ( current_user_can( $needed_cap ) ) {
            ccfm_clear_cache_for_me( 'button' );
            $is_success = 1;
            add_action( 'admin_notices', 'ccfm_success' );
        }
        else {
            add_action( 'admin_notices', 'ccfm_error' );   
        }
        wp_safe_redirect( admin_url() . '?ccfm_success=' . $is_success );
        exit;
    }

    if ( isset( $_GET['ccfm_success'] ) ) {
        if ( !empty( $_GET['ccfm_success'] ) ) {
            add_action( 'admin_notices', 'ccfm_success' );
        }
        else {
            add_action( 'admin_notices', 'ccfm_error' );   
        }
    }
    if ( isset( $_GET['ccfm_asuccess'] ) ) {
        if ( !empty( $_GET['ccfm_asuccess'] ) ) {
            add_action( 'admin_notices', 'ccfm_admin_success' );
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
    if ( isset( $_POST['ccfm_set'] ) ) {
        check_admin_referer( 'ccfm' );
        $is_success = 0;
        if ( current_user_can( 'manage_options' ) ) {
            update_option( 'ccfm_permission', sanitize_title( $_POST['ccfm_permission'] ) );
            update_option( 'ccfm_infotext', wp_unslash( $_POST['ccfm_infotext'] ) );
            $is_success = 1;
        }
        wp_safe_redirect( admin_url() . '?ccfm_asuccess=' . $is_success  );
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
 * Show the success notice for saving options.
 */
function ccfm_admin_success() { ?>
    <div class="updated">
        <p><?php _e( 'Settings Saved!', 'ccfm' ); ?></p>
    </div>
<?php
}

/**
 * Show the error notice.
 */
function ccfm_error() { ?>
    <div class="error">
        <p><?php _e( 'You do not have permission to do that.', 'ccfm' ); ?></p>
    </div>
<?php
}
