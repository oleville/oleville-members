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
            //TODO: add datatypes here for additional members (ask pause, BORSC, others?)
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

            //Ajax requests go here (if needed)
    	} // END public function init()

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
    				update_post_meta($post_id, $field_name, $_POST[$field_name]);
    			}
    		}
    		else
    		{
    			return;
    		} 
    	} // END save_post

        //function to handle AJAX requests for member info
        public function get_member_info()
        {

            $htmlstring = '<div>';

            $cid = $_POST['cid'];

            $member = get_post($cid);
            $member_metas = get_post_custom($cid);

            $thumb = get_post_thumbnail_id($cid);
            $featured_img = '';
            if ($thumb != false)
            {
                $featured_img = wp_get_attachment_image_src($thumb, 'medium');
                $featured_img = $featured_img[0];
            }

            $return_data = array(
                'name' => $candidate->post_title,
                'featured_image' => $featured_img,
                'content' => apply_filters('the_content', $member->post_content),
            );

            //other stuff needed?
        }


        // not needed
        public function delete_post($post_id)
        {

        }

    	/**
    	 * hook into WP's admin_init action hook
    	 */
    	public function admin_init()
    	{			
    		// Add metaboxes
    		add_action('add_meta_boxes', array(&$this, 'add_meta_boxes'));
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

	} // END class Post_Type_Template
} // END if(!class_exists('Post_Type_Template'))