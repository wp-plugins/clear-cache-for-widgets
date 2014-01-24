<?php 
/*
Plugin Name: Clear Cache For Widgets
Plugin URI: http://mymonkeydo.com/caching-with-widgets/ ‎
Description: Purges all cache on WT3, WP Super Cache, or WPEngine when updating a widget.
Author: Webhead LLC
Author URI: http://webheadcoder.com 
Version: 0.1
*/

function clear_cache_for_widgets( $instance ) {

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

    return $instance;
}
add_filter( 'widget_update_callback', 'clear_cache_for_widgets', 10 );

