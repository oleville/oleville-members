<?php
/*
	Plugin Name: Oleville Members
    Plugin URI: http://www.oleville.com
    Description: Provides Member functionality
    Version: 1.2
    Author:Elijah Verdoorn
	Author URI: www.elijahverdoorn.com
    License: GPL2
*/
/*
Copyright 2015 Elijah Verdoorn  (email : elijah@elijahverdoorn.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if(!class_exists('Oleville_Members'))
{
    class Oleville_Members
    {
        /**
         * Construct the plugin object
         */
        public function __construct()
        {
			// Initialize Settings
			require_once(sprintf("%s/settings.php", dirname(__FILE__)));
			$oleville_members_settings = new Oleville_Members_Settings();
			
			// Register member post type
			require_once(sprintf("%s/member-type.php", dirname(__FILE__)));
			$oleville_members_type = new Oleville_Members_Type();
			
			// Register display widget
			require_once(sprintf("%s/member-display.php", dirname(__FILE__)));
			$oleville_members_display = new Oleville_Members_Display();

            //activate shortcode
            require_once(sprintf("%s/shortcodes.php", dirname(__FILE__)));
            $oleville_members_shortcode = new Oleville_Members_Shortcode();
        } // END public function __construct

        /**
         * Activate the plugin
         */
        public static function activate()
        {
            // Do nothing
        } // END public static function activate

        /**
         * Deactivate the plugin
         */     
        public static function deactivate()
        {
            // Do nothing
        } // END public static function deactivate
	} 
} // END if(!class_exists())

if(class_exists('Oleville_Members'))
{
    // Installation and uninstallation hooks
    register_activation_hook(__FILE__, array('Oleville_Members', 'activate'));
    register_deactivation_hook(__FILE__, array('Oleville_Members', 'deactivate'));

    // instantiate the plugin class
    $oleville_members = new Oleville_Members();
}