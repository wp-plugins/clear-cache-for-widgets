=== Clear Cache for Me ===
Contributors: webheadllc
Tags: wpengine, widget, menu, cache, clear, purge, w3t, W3 Total Cache, WP Super Cache
Requires at least: 3.8
Tested up to: 4.1
Stable tag: 0.6.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Purges all cache on WPEngine, W3 Total Cache, WP Super Cache when updating widgets or menus.

== Description ==

**NOTE:  If you do not use the W3 Total Cache or WP Super Cache plugins or do not host your website on WPEngine, this plugin will do nothing for you.**

W3 Total Cache and WP Super Cache are great caching plugins, but they do not know when a widget is updated.  WPEngine is the best place to host your WordPress installation, but their caching system is no smarter when it comes to updating widgets and menus.  I created this plugin because my website did not see any changes when saving widgets or menus using these caching systems.  Clear Cache For Me will purge ALL your cache each time you save a widget or menu.  It may be overkill, which may be why it's not built in, but some people need simplicity.

Now there is a convenient clear cache button on the dashboard for users with the right capability.  Admins (users with the 'manage_options' capability) can set which capability a user needs to view the button.


== Changelog ==

= 0.6.2 =
minor updates to css class names

= 0.6.1 =
Updated German translation (thanks to Ov3rfly!).
Updated admin HTML and styles (thanks to Ov3rfly!).

= 0.6 =
Fixed cache not clearing when widgets are re-ordered or deleted (thanks to Ov3rfly!).  
Added optional instructions to be shown above the button (thanks to Ov3rfly!).  
Added to and updated German translations (thanks to Ov3rfly!).  
Added more security checks. (thanks to Ov3rfly!).  
Added customize theme detection.  Clears cache when customizing theme.  
Reorganized code a bit.

= 0.5 =
Added German language translation (thanks to Ov3rfly)
Added hooks for 3rd party code.

= 0.4 =
Bug fixed: Fixed cache not clearing when updating nav menu. (thanks to Ov3rfly for catching this and supplying the fix)

= 0.3 =
Added clear caching for menus
Added clear cache button to dashboard
Added option to set what capability is needed to view the clear cache button for admins.

= 0.2 =
Removed garbage at the end of the plugin url.

= 0.1 =
Initial release.
