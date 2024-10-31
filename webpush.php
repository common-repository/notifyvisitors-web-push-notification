<?php
/*
Plugin Name: Notifyvisitors Webpush
Plugin URI: https://www.notifyvisitors.com/product/web-push-notification
Description: Show real time onsite notifications based on customer behaviour. Choose from multiple templates, show targeted messages based on multiple targeting rules.
Version: 1.0
Author: Notifyvisitors
Author URI: https://www.notifyvisitors.com/
Text Domain: Webpush
*/

// Version check
global $wp_version;
if(!version_compare($wp_version, '3.0', '>='))
{
    die("webPush requires WordPress 3.0 or above. <a target='_blank' href='http://codex.wordpress.org/Upgrading_WordPress'>Please update!</a>");
}
// END - Version check


//this is to avoid getting in trouble because of the
//wordpress bug http://core.trac.wordpress.org/ticket/16953
$webpush_file = __FILE__; 

if ( isset( $mu_plugin ) ) { 
    $webpush_file = $mu_plugin; 
} 
if ( isset( $network_plugin ) ) { 
    $webpush_file = $network_plugin; 
} 
if ( isset( $plugin ) ) { 
    $webpush_file = $plugin; 
} 

$GLOBALS['webpush_file'] = $webpush_file;


// Make sure class does not exist already.
if(!class_exists('Webpush')) :

    class WebPushWidget extends WP_Widget {
        function WebPushWidget() {
            parent::WP_Widget(false, 'Webpush Widget', array('description' => 'Description'));
        }

        function widget($args, $instance) {
            echo '<div id="webpush_widget"></div>';
        }

        function update( $new_instance, $old_instance ) {
            // Save widget options
            return parent::update($new_instance, $old_instance);
        }

        function form( $instance ) {
            // Output admin widget options form
            return parent::form($instance);
        }
    }

    function webpush_widget_register_widgets() {
        register_widget('WebPushWidget');
    }

    // Declare and define the plugin class.
    class Webpush
    {
        // will contain id of plugin
        private $plugin_id;
        // will contain option info
        private $options;

        /** function/method
        * Usage: defining the constructor
        * Arg(1): string(alphanumeric, underscore, hyphen)
        * Return: void
        */
        public function __construct($id)
        {
            // set id
            $this->plugin_id = $id;
            // create array of options
            $this->options = array();
            // set default options
            $this->options['secretkey'] = '';            
            $this->options['brandID'] = '';

            /*
            * Add Hooks
            */
            // register the script files into the footer section
            add_action('wp_footer', array(&$this, 'webpush_scripts'));
            // initialize the plugin (saving default options)
            register_activation_hook(__FILE__, array(&$this, 'install'));
            // triggered when plugin is initialized (used for updating options)
            add_action('admin_init', array(&$this, 'init'));
            // register the menu under settings
            add_action('admin_menu', array(&$this, 'menu'));
            // Register sidebar widget
            add_action('widgets_init', 'webpush_widget_register_widgets');

           
        }

        /** function/method
        * Usage: return plugin options
        * Arg(0): null
        * Return: array
        */
        private function get_options()
        {
            // return saved options
            $options = get_option($this->plugin_id);
            return $options;
        }
        /** function/method
        * Usage: update plugin options
        * Arg(0): null
        * Return: void
        */
        private function update_options($options=array())
        {
            // update options
            update_option($this->plugin_id, $options);
        }

        /** function/method
        * Usage: helper for loading webpush.js
        * Arg(0): null
        * Return: void
        */
        public function webpush_scripts()
        {
            if (!is_admin()) {
                $options = $this->get_options();
                $secretkey = trim($options['secretkey']);
                $brandID = trim($options['brandID']);
                $this->show_webpush_reward_js($secretkey,$brandID);
            }
        }
        
        public function show_webpush_reward_js($secretkey="",$brandID="")
        {        	
            $bid = $brandID; 
            $secKey = $secretkey; 
            
			echo '<script> 
        var nv=nv||function(){(window.nv.q=window.nv.q||[]).push(arguments)};nv.l=new Date;var notify_visitors=notify_visitors||function(){
        var e={initialize:!1,ab_overlay:!1,auth:{ bid_e:"'.$secKey.'",bid:"'.$bid.'",t:"420"}};
        return e.data={bid_e:e.auth.bid_e,bid:e.auth.bid,t:e.auth.t,iFrame:window!==window.parent,trafficSource:document.referrer,link_referrer:document.referrer,pageUrl:document.location,path:location.pathname,domain:location.origin,gmOffset:60*(new Date).getTimezoneOffset()*-1,screenWidth:screen.width,screenHeight:screen.height,cookieData:document.cookie},e.options=function(t){t&&"object"==typeof t?e.ab_overlay=t.ab_overlay:console.log("Not a valid option")},e.tokens=function(t){e.data.tokens=t&&"object"==typeof t?JSON.stringify(t):""},e.ruleData=function(t){e.data.ruleData=t&&"object"==typeof t?JSON.stringify(t):""},e.getParams=function(e){url=window.location.href.toLowerCase(),e=e.replace(/[\[\]]/g,"\\$&").toLowerCase();var t=new RegExp("[?&]"+e+"(=([^&#]*)|&|#|$)").exec(url);return t&&t[2]?decodeURIComponent(t[2].replace(/\+/g," ")):""},e.init=function(){if(e.auth&&!e.initialize&&(e.data.storage=e.browserStorage(),e.js_callback="nv_json1",!e.data.iFrame&&"noapi"!==e.getParams("nvcheck"))){var t="?";if(e.ab_overlay){var o=document.createElement("style"),n="body{opacity:0 !important;filter:alpha(opacity=0) !important;background:none !important;}",a=document.getElementsByTagName("head")[0];o.setAttribute("id","_nv_hm_hidden_element"),o.setAttribute("type","text/css"),o.styleSheet?o.styleSheet.cssText=n:o.appendChild(document.createTextNode(n)),a.appendChild(o),setTimeout(function(){var e=this.document.getElementById("_nv_hm_hidden_element");if(e)try{e.parentNode.removeChild(e)}catch(t){e.remove()}},2e3)}for(var i in e.data)e.data.hasOwnProperty(i)&&(t+=encodeURIComponent(i)+"="+encodeURIComponent(e.data[i])+"&");e.load("https://www.notifyvisitors.com/ext/v1/settings"+t),e.initialize=!0}},e.browserStorage=function(){var t={session:e.storage("sessionStorage"),local:e.storage("localStorage")};return JSON.stringify(t)},e.storage=function(e){var t={};return window[e].length>0&&Object.keys(window[e]).forEach(function(o){-1!==o.indexOf("_nv_")&&(t[o]=window[e][o])}),t},e.load=function(e){var t=document,o=t.createElement("script");o.src=e,o.type="text/javascript",t.body?t.body.appendChild(o):t.head.appendChild(o)},e}();
        
        notify_visitors.init();
    </script>';
        }

        /** function/method
        * Usage: helper for hooking activation (creating the option fields)
        * Arg(0): null
        * Return: void
        */
        public function install()
        {
            $this->update_options($this->options);
        }
        
        /** function/method
        * Usage: helper for hooking (registering) options
        * Arg(0): null
        * Return: void
        */
        public function init()
        {
            register_setting($this->plugin_id.'_options', $this->plugin_id);
        }
                
        /** function/method
        * Usage: show options/settings form page
        * Arg(0): null
        * Return: void
        */
        public function options_page()
        {
            if (!current_user_can('manage_options'))
            {
                wp_die( __('You can manage options from the Settings->  Options menu.') );
            }

            // get saved options
            $options = $this->get_options();
            $updated = false;

            if ($updated) {
                $this->update_options($options);
            }
            include('webpush_options_form.php');
        }
        /** function/method
        * Usage: helper for hooking (registering) the plugin menu under settings
        * Arg(0): null
        * Return: void
        */
        public function menu()
        {
			$icon = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAUCAYAAACNiR0NAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyJpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNiAoV2luZG93cykiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6RTE1MUE4MjI4OEM1MTFFODk3NzZDRjk1OURFMUEwOTgiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6RTE1MUE4MjM4OEM1MTFFODk3NzZDRjk1OURFMUEwOTgiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDpFMTUxQTgyMDg4QzUxMUU4OTc3NkNGOTU5REUxQTA5OCIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDpFMTUxQTgyMTg4QzUxMUU4OTc3NkNGOTU5REUxQTA5OCIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PsbADgAAAAKLSURBVHjalFVLa1NBFP7OZJo0KbV0o1It6sLiwvpALcYupGahIII24kI3pVKhu4qvtS7dFC0iuhEXvuqi/gBdiYIEERGp1IVvWwWrTWvbNHfueGbmNg9NYu+Byb03c+eb7/vOOXPJu7g5AeACjz0IE9oHlMdXBdTFzT+jPM5I/jnP4xTChoyBmlqAxhXQ468BP78VoKjgqb2hwRZ+Q6TOQfQ+gOg6DXjzzNTOJA2gCgWWmwat2QlqS7HUeuiPGSA/x+TIzCojWS8ZbD4LWt8F0T0ERBPQj4egxx5a+YvOyuqrKZDiO9NzDLZhH8ShQUBI+CMnoV+NMHADEIkVeInqPs2AWjY5abM/QO3dEIevsPkK/p1e6Oe3eHXEPpeKlFV9WpuEOHLNMtDrOkHbjjHwT/jDJ4C5KVDqLItgwO+j0O+eWtaVAY1PzEqk2ac8S56ecGDZcfj3+20SRM8wiDexMTsJdX0/k5ixjOU/YBsPQBwcdGzuHnfSOxnoyVXob2+AeFOBTZAHfq6r4KHKM5OjbPolx+Z2D/TnF8Dq7bYjaFc/KMlyvZxLVAFPV/AwyKTYPQBMvocyzPhq6oy2pEGtO9zby9ugMzdrVpoDJCaqciyxz5YHshNArNF2hPVxMUwB/68jS63QLDWSvsz9uZL9nIK61+cOgDAtXnZ6NDQDrR22pm3ElpX7tYQoL2xjsBdI9L3QYLU7pVYQlbfoX4BF2SY5nFk3I91CWV9828wZFaV1aA4GXci6NDN8OqIdQthE6LFHQKLZFrkZ+i2fJmrBOfIpYzfVz24Av764Xv760q4LNvlA/AlYxTfcGuiwnhnvDFNzbzrAgGlVFBSNu/LxVVFyNGHBeAz8EWAAQf70mLa3YREAAAAASUVORK5CYII=';
            add_menu_page('Webpush Options', 'Web Push', 'manage_options',$this->plugin_id.'-plugin', array(&$this, 'options_page'),$icon,'80');
			
        }
    }

    // Instantiate the plugin
    $Webpush = new Webpush('webpush');

// END - class exists
endif;
?>
