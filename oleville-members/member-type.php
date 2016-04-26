<?php
if(!class_exists('Oleville_Members_Type'))
{
	class Oleville_Members_Type
	{
		const POST_TYPE	= "member";

		private $_meta	= array(
			'position',
			'major',
			'contact',
			'class',
			'subcommittee',
			'hometown',
			'officeHours',
		);
		
		/**
		 * The Constructor
		 */
		public function __construct()
		{
			// register actions
			add_action('init', array(&$this, 'init'));
			add_action('admin_init', array(&$this, 'admin_init'));
		} // END public function __construct()

		/**
		 * hook into WP's init action hook
		 */
		public function init()
		{
			// Initialize Post Type
			$this->create_post_type();
			add_action('save_post', array(&$this, 'save_post'));
			add_action('wp_trash_post', array(&$this, 'delete_post'));
			add_action('publish_post', array(&$this, 'publish_post'));
			add_action('update_post', array(&$this, 'update_post'));

			//handle ajax requests
			add_action('wp_ajax_get_member_info', array(&$this, 'get_member_info'));
			add_action('wp_ajax_nopriv_get_member_info', array(&$this, 'get_member_info')); // not sure if we need this one
		}

		/**
		 * Create the post type
		 */
		public function create_post_type()
		{
			$labels = array(
				'name'               => _x( 'Members', 'post type general name' ),
				'singular_name'      => _x( 'Member', 'post type singular name' ),
				'add_new'            => _x( 'Add New', 'member' ),
				'add_new_item'       => __( 'Add New Member' ),
				'edit_item'          => __( 'Edit Member' ),
				'new_item'           => __( 'New Member' ),
				'all_items'          => __( 'All Members' ),
				'view_item'          => __( 'View Member' ),
				'search_items'       => __( 'Search Members' ),
				'not_found'          => __( 'No members found' ),
				'not_found_in_trash' => __( 'No members found in the Trash' ), 
				'parent_item_colon'  => '',
				'menu_name'          => 'Members'
			);
			$args = array(
					'labels'        => $labels,
					'description'   => 'Holds our members and bios',
					'public'        => true,
					'menu_position' => 5,
					'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt'),
					'has_archive'   => true,
					'menu_icon'     => 'dashicons-id', // load the icon
			);
			register_post_type(self::POST_TYPE, $args);
		}
	
		/**
		 * Save the metaboxes for this custom post type
		 */
		public function save_post($post_id)
		{
			// verify if this is an auto save routine. 
			// If it is, our form has not been submitted, so we dont want to do anything
			if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
			{
				return;
			}
			
			if(isset($_POST['post_type']) && $_POST['post_type'] == self::POST_TYPE && current_user_can('edit_post', $post_id))
			{
				foreach($this->_meta as $field_name)
				{
					// Update the post's meta field
					if(isset( $_POST[$field_name]))
					{
						update_post_meta($post_id, $field_name, sanitize_text_field($_POST[$field_name]));                    
					}
				}
			} else {
				return;
			}

			$max_repeats = (integer)$_POST['max_repeats'];
			//repeat loop
			$repeat_list = array();
			for ($i = 0; $i <= $max_repeats; $i++)
			{
				if(isset($_POST['st'.$i], $_POST['et'.$i], $_POST['dow'.$i]) && $_POST['st'.$i] != '' && $_POST['et'.$i] != '' && $_POST['dow'.$i] != '')
				{
					array_push($repeat_list, array(
						$_POST['st'.$i],
						$_POST['et'.$i],
						$_POST['dow'.$i]
					));
				}
			}
			update_post_meta($post_id, 'officeHours', serialize($repeat_list));
			//write_log(get_post_meta($post_id, 'officeHours', TRUE));

		} // END save_post

		/**
		 * Delete the metaboxes for this custom post type
		 */
		public function delete_post($post_id)
		{

		}

		//function to handle AJAX requests for member info
		public function get_member_info()
		{

			$htmlstring = '<div>';

			$memberID = $_POST['memberID'];

			$member = get_post($memberID);
			$member_metas = get_post_custom($memberID);

			$thumb = get_post_thumbnail_id($memberID);
			$featured_img = '';
			if ($thumb != false)
			{
				$featured_img = wp_get_attachment_image_src($thumb, 'medium');
				$featured_img = $featured_img[0];
			}

			$return_data = array(
				'name' => $member->post_title,
				'featured_image' => $featured_img,
				'position' => $member_metas['position'],
				'major' => $member_metas['major'],
				'class' => $member_metas['class'],
				'hometown' => $member_metas['hometown'],
				'officeHours' => $member_metas['officeHours'],
				'content' => apply_filters('the_content', $member->post_content),
			);

			echo json_encode($return_data); // return the JSON data
			wp_die(); // clean up
		}

		/**
		 * hook into WP's admin_init action hook
		 */
		public function admin_init()
		{			
			// Add metaboxes
			add_action('add_meta_boxes', array(&$this, 'add_meta_boxes'));
			// enqueue scripts
			add_action('admin_enqueue_scripts', array(&$this, 'admin_enqueue_scripts'));

		} // END public function admin_init()
			
		/**
		 * hook into WP's add_meta_boxes action hook
		 */
		public function add_meta_boxes()
		{
			// Add this metabox to every selected post
			add_meta_box( 
				sprintf('oleville_members_%s_section', self::POST_TYPE),
				sprintf('%s Information', ucwords(str_replace("_", " ", self::POST_TYPE))),
				array(&$this, 'add_inner_meta_boxes'),
				self::POST_TYPE,
				'normal',
				'high'
			);					
		}

		/**
		 * called off of the add meta box
		 */		
		public function add_inner_meta_boxes($post)
		{		
			wp_nonce_field( plugin_basename( __FILE__ ), 'oleville_members_member_section_nonce' );
			// Render the job order metabox
			include(sprintf("%s/templates/member_metabox.php", dirname(__FILE__)));			
		}

		/**
		 * Function hooked to WP's admin_enqueue_scripts action
		 */
		public function admin_enqueue_scripts($hook)
		{
			global $post;
			// Check to see that these scripts are only loaded for post-new.php
			// and the type is eventmail
			if('post-new.php' != $hook && 'post.php' != $hook)
				return;
			if($_GET['post_type'] != self::POST_TYPE && 'post.php' != $hook)
				return;

			// Register the JS and CSS files with WordPress
			wp_register_style(
				'oleville-members-member-css',
				plugins_url('templates/css/member_metabox.css', __FILE__)
			);
			wp_register_script(
				'oleville-members-member-js',
				plugins_url('templates/js/member_metabox.js', __FILE__),
				array('jquery', 'jquery-ui-datepicker')
			);
			wp_register_script(
				'oleville-members-timepicker-js',
				plugins_url('templates/js/jquery.timepicker.min.js', __FILE__),
				array('jquery')
			);

			wp_localize_script(
				'oleville-members-member-js',
				'repeat_member',
				array('members' => json_encode(unserialize(get_post_meta($post->ID, 'officeHours', TRUE))))
			);


			// Enqueue the styles and scripts
			wp_enqueue_style('oleville-members-member-css');
			wp_enqueue_script('oleville-members-member-js');
			wp_enqueue_script('oleville-members-timepicker-js');
		}

	} // END class Post_Type_Template
} // END if(!class_exists('Post_Type_Template'))