<?php
if(!class_exists('Oleville_Members_Shortcode'))
{
	class Oleville_Members_Shortcode
	{
		const POST_TYPE = "member";
		const SHORTCODE = "member";

		private $_meta = array(
			'position',
			'major',
			'contact',
			'subcommittee',
			'branch',
			'office-hours',
			'hometown',
		);

		private $messages = array(
			'success' => array(),
			'error' => array(),
			);

		/**
		* Constructor
		*/
		public function __construct()
		{
			// Register Action Hooks
			add_action('init', array(&$this, 'init'));
			add_action('admin_init', array(&$this, 'admin_init'));

		}

		/**
		* Function hooked to WP's init action
		*/
		public function init()
		{
			//error_log("Adding Short Code");

			global $wpdb;

			// registering external scripts
			wp_register_script( 'member-colorbox', WP_PLUGIN_URL.'/oleville-members/js/jquery.colorbox-min.js', array('jquery') );
            wp_register_script( 'ov-members-js', WP_PLUGIN_URL.'/oleville-members/js/member-lightbox.js', array('jquery') );
            wp_localize_script( 'ov-members-js', 'myAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));

			wp_enqueue_script( 'ov-members-js');
			wp_enqueue_script( 'member-colorbox');

			wp_enqueue_style( 'member-colorbox', WP_PLUGIN_URL.'/oleville-members/css/colorbox.css');


			// Add the shortcode hook
			if (!shortcode_exists('show-members')) {
				add_shortcode('show-members', array(&$this, 'member_handler'));
			}
			if(!shortcode_exists('show-office-hours')) {
				add_shortcode('show-office-hours',array(&$this, 'hours_handler'));
			}
			if(!shortcode_exists('members-show-front-page')) {
				add_shortcode('members-show-front-page',array(&$this, 'front_handler'));
			}
		}

		public function member_handler($attr)
		{
			return $this->show_members();
		}

		public function hours_handler($attr)
		{
			return $this->show_office_hours();
		}

		public function front_handler($atts)
		{
			return $this->show_front_page();
		}

		public function show_members()
		{
				global $wpdb; // get a reference to the database

				$result .= '<table class="member-table">';

				$args = array( //args for the query
					'post-type' => 'member',
					'posts_per_page' => -1, // unlimited
					'orderby' => 'menu_order',
					'order' => 'ASC', //ascending order
				);

				/// make the query
				$query = new WP_Query($args);

				// set parameters for the HTML table that will be used to display the members
				$num_cols = 4; // number of columns
				$col_count = 0; // counting variable that we use to start a new row whenever we have the specified number of columns

				//loop through the results of the query
				while ($query -> have_posts())
				{
					$query -> the_post(); //reference the post from the query

					$postitionID = get_the_ID();
					$postitionTitle = get_the_title();
					$thumb = get_the_post_thumbnail($postitionID, "thumbnail");
					if (!$thumb)  // if there is no thumbnail, set the image to a placeholder
					{
						$thumb = '<img src="' . get_template_directory_uri() . '/img/placeholder_thumb.png" width="150" height"150">';
					}

					//build the HTML for this member
					$result .= '<td colspan="'.$colspan.'" class="member" style="padding-bottom: 10px;"><center><div class="member_picture">' . $thumb . '</div><div class="member_name"><h3>' . get_the_title() . '</h3></div>';
					$result .= '<div class="button">'. '<button type="button" class="btn btn-primary member_profile" href="#lightbox-wrapper" data-toggle="modal" data-target="'. get_the_ID() . '">Member Profile</button><div class="profile">';
					$result .= '</center></div></td>';

					if ($col_count >= 3)//check if we need a new row for the next member
					{
						$result .= '</tr>';//make a new row
						$col_count = 0;
					} else {
						$col_count++;//keep going on this row
					}
				}

			$result .= '</table>'; // end the table

			//this is the HTML placeholder that is modified by the JS in member-lightbox.js whenever the user clicks on the "Member Profile" button.
      $result .= '<div style="display:none;"><div id="member-lightbox"><h3 class="member-name">Member Name</h3><div class="member-content-wrapper"><div class="image-wrapper"><img class="member-picture" src="" /></div><h2 class="member-position">Position</h2><h4 class="member-major">Major</h4>';
      if (get_post_meta($positionID, 'hometown', TRUE) != '') // some of the members don't have this property attached to them, so we're only going to show it if they do have it.
			{
      	$result .= '<strong>Hometown:</strong> <span style="font-weight: 400;" class="member-hometown">Hometown</span>';
      }
      $result .= '<div class="member-content">Placeholder</div></div></div>'; // uses some of the CSS from members (I hope...)

			return $result; // finish the page
		}

		public function check_arduino($member)
		{
			//call the RESTful API to check if the member is in
			
		}

		//this will return a structure of all the members that are currently in the office, and some of the associated metadata
		public function is_member_in($member)
		{
		
		}

		//this will return a structure if all members that are in the database, and their associated metadata
		public function get_all_members()
		{
			$global wpdb;

			//query the DB for the members
			$args = array(
				'post-type' => 'member', // get all posts that are members
				'posts_per_page' => -1, // no limit to the number of members that we want to get
				'orderby' => 'menu_order', // order them in menu order
				'order' => 'ASC' //ascending
			);
			$query = new WP_Query($args); // make the query

			$formattedMemberData = new array();

			//add them to the custom data structure, built out of nested arrays (kinda like a JSON structure, which is convenient because that's how the OH are stored)
			while ($query -> have_posts())
			{
				$thisMember = array(
					'name' => get_the_title(),
					'id' => get_the_ID(),
					'picture'  => get_the_post_thumbnail()
					);
				array_push($thisMember, processOfficeHours(serialize(get_post_meta(get_the_ID(), 'repeat_list', TRUE))));

				array_push($formattedMemberData, $thisMember);
			}
		}

		//display the OH page
		public function show_office_hours()
		{
			$members = get_all_members();
			foreach ($members as $member)
			{
				if (is_member_in($member))
				{
					//add the member to the result string
				} else {

				}
			}
			//here we should build the page that holds all the OH and displays them to the user
		}

		//display the front page content
		public function show_front_page()
		{
			$members = get_all_members();
			foreach ($members as $m)
			{
				if (is_member_in($m))
				{
					//add them to the front page
				}

			}
		}

		public function processOfficeHours($officeHours)
		{
			$decoded = json_decode($officeHours); // decode the JSON object that holds all the OH
			//write_log($decoded);
		}

		/**
		* Function hooked to WP's admin_init action
		*/
		public function admin_init()
		{
			//do nothing
		}
	}
}