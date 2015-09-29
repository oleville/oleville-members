<?php
if(!class_exists('Oleville_Members_Settings'))
{
	class Oleville_Members_Settings
	{
		/**
		 * Construct the plugin object
		 */
		public function __construct()
		{
			// register actions
            add_action('admin_init', array(&$this, 'admin_init'));
        	add_action('admin_menu', array(&$this, 'add_menu'));
		} // END public function __construct
		
        /**
         * hook into WP's admin_init action hook
         */
        public function admin_init()
        {
        	// register your plugin's settings
        	register_setting('oleville_members-group', 'branch_name');
					register_setting('oleville_members-group', 'branch_note');
        	register_setting('oleville_members-group', 'branch_note_title');


        	// add your settings section
        	add_settings_section(
        	    'oleville_members-section', 
        	    'Oleville Members Settings', 
        	    array(&$this, 'settings_section_oleville_members'), 
        	    'oleville_members'
        	);
        	
        	// add your setting's fields
            add_settings_field(
                'oleville_members-branch_name', 
                'Branch Name Shortcode', 
                array(&$this, 'settings_field_input_text'), 
                'oleville_members', 
                'oleville_members-section',
                array(
                    'field' => 'branch_name',
					'default' => get_bloginfo( 'name' ),
                )
            );
						// add your setting's fields
            add_settings_field(
                'oleville_members-branch_note_title', 
                'Note Title', 
                array(&$this, 'settings_field_input_text'), 
                'oleville_members', 
                'oleville_members-section',
                array(
                    'field' => 'branch_note_title',
                )
            );
						// add your setting's fields
            add_settings_field(
                'oleville_members-branch_note', 
                'Note', 
                array(&$this, 'settings_field_paragraph_text'), 
                'oleville_members', 
                'oleville_members-section',
                array(
                    'field' => 'branch_note',
                )
            );
			
        } // END public static function activate
        
        public function settings_section_wp_plugin_template()
        {
            // Think of this as help text for the section.
            echo 'Modify the display settings for branch members.';
        }
        
        /**
         * This function provides text inputs for settings fields
         */
        public function settings_field_input_text($args)
        {
            // Get the field name from the $args array
            $field = $args['field'];
            // Get the value of this setting
            $value = get_option($field, $args['default']);
            // echo a proper input type="text"
            echo sprintf('<input type="text" name="%s" id="%s" value="%s" placeholder="SGA"/>', $field, $field, $value);
        } // END public function settings_field_input_text($args)
        
				/**
         * This function provides text inputs for settings fields
         */
        public function settings_field_paragraph_text($args)
        {
            // Get the field name from the $args array
            $field = $args['field'];
            // Get the value of this setting
            $value = get_option($field, $args['default']);
            // echo a proper input type="text"
            echo sprintf('<textarea type="textarea" cols="80" rows="10" name="%s" id="%s">%s</textarea>', $field, $field, $value);
        } // END public function settings_field_input_text($args)
        /**
         * add a menu
         */		
        public function add_menu()
        {
            // Add a page to manage this plugin's settings
			// NOTE: the post_type query has to match the post type defined in member-type.php
        	add_submenu_page(
				'edit.php?post_type=member',
        	    'Oleville Members Settings', 
        	    'Settings', 
        	    'manage_options', 
        	    'oleville_members', 
        	    array(&$this, 'plugin_settings_page')
        	);
        } // END public function add_menu()
    
        /**
         * Menu Callback
         */		
        public function plugin_settings_page()
        {
        	if(!current_user_can('manage_options'))
        	{
        		wp_die(__('You do not have sufficient permissions to access this page.'));
        	}
	
        	// Render the settings template
        	include(sprintf("%s/templates/settings.php", dirname(__FILE__)));
        } 
    }
} 