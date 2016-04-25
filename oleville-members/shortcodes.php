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
			'officeHours',
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

			if(!shortcode_exists('members-show-front-page')) {
				add_shortcode('members-show-front-page',array(&$this, 'front_handler'));
			}
		}

		public function member_handler($attr)
		{
			return $this->show_members();
		}

		public function front_handler($atts)
		{
			return $this->show_front_page();
		}

		//TODO: decide if this needs to be refactored
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

		// returns true if the member is in according to the arduino's data
		public function check_arduino($member)
		{
			return false; // temporary until I build the arduino box TODO: Implement
			
		}

		public function getDayOfWeek()
		{
			return strtolower(date("l")); // get the day of week as a lowercase string (to match the data that we're pulling from the JSON)
		}

		// this will return true if the member is in the office, and false otherwise
		public function is_member_in($member)
		{
			if($this->check_arduino($member))
			{
				return true;
			}

			//TODO: check this code. Is is getting the right part into $m? Is it iterating the right number times over each sub-array of $m?

			date_default_timezone_set('America/Chicago'); //set the default timezone for the location of the server. TODO: make this a setting.
			//write_log($member['officeHours']);

			foreach ($member['officeHours'] as $m)
			{
				if (strcasecmp($m[2], $this->getDayOfWeek()) == 0) // if the strings are the same
				{
					// ASSERT: the member has OH on the current day of the week
					$startTime = date("H:i", strtotime($m[0])); // convert the start time to 24 hour
					$endTime = date("H:i", strtotime($m[1])); // and the end time
					$currentTime = date("H");
					if ($startTime > $currentTime && $currentTime < $endTime) 
					{
						// ASSERT: The member is in.
						return true;
					}
				}
			}
			//ASSERT: the member is not in. The call to checkArduino returned false, and the time does not match.
			return false;
		}

		//this will return a structure if all members that are in the database, and their associated metadata
		public function get_all_members()
		{
			global $wpdb;

			//query the DB for the members
			$args = array(
				'post_type' => 'member',
				'posts_per_page' => -1,
				'orderby' => 'menu_order',
				'order' => 'ASC', // what code is this?
			);
			$query = new WP_Query($args); // make the query

			//$formattedMemberData; // an array that will hold references to all the members in the database

			//add them to the custom data structure, built out of nested arrays (kinda like a JSON structure, which is convenient because that's how the OH are stored)
			while ($query -> have_posts())
			{
				$query->the_post();

				$memberId = get_the_ID(); // the member's id
				$position = get_post_meta($memberId, 'position', TRUE);
				//write_log($memberId);

				$thisMember = array(
					'name' => get_the_title(), // the member's name
					'id' => $memberId, // the member's ID
					'picture'  => get_the_post_thumbnail(), // the member's picture
					'officeHours' => unserialize(get_post_meta($memberId, 'officeHours', TRUE)), //the office hours
					'position' => get_post_meta($memberId, 'position', TRUE) // the position
					);

				$formattedMemberData[] = $thisMember; // add this member's array to the array of all members
			}

			return $formattedMemberData; // return the array of all members
		}

		//display the OH page
		public function show_front_page()
		{
			$members = $this->get_all_members();
			$result = '<table class="office-table">'; // start the result string of HTML
			$membersIn = 0; //number of members in now

			foreach ($members as $member)
			{
				if ($this->is_member_in($member))
				{
					$position = '<div class="member-position">' . $member['position'] . '</div>'; // grab the position to be used below

					//add the member to the result string
					$result .= '<tr class="member-front-list"><td colspan="' . $colspan . '" class="member" style="padding-bottom: 10px;"><div class="member_name"><h2>' . $member['name'] . '</h2></div><div class="member-position"><h3>' . $position . '</h3></div></tr>';
					$membersIn += 1;
				}
			}

			//end the page, return it
			switch ($membersIn) {
				case 0:
					$result .= '<tr class="member-front-list-empty"><td colspan="' . $colspan . '" class="member" style="padding-bottom: 10px;"><div class="empty-office">Sorry, no scheduled office hours now.</div><div class="member_name"><h2> </h2></div><div class="member-position"><h3></h3></div></tr>';
				case 1:
					$result .= '<tr class="member-front-list-empty"><td colspan="' . $colspan . '" class="member" style="padding-bottom: 10px;"><div class="member_name"><h2> </h2></div><div class="member-position"><h3></h3></div></tr>';
				case 2:
					$result .= '<tr class="member-front-list-empty"><td colspan="' . $colspan . '" class="member" style="padding-bottom: 10px;"><div class="member_name"><h2> </h2></div><div class="member-position"><h3></h3></div></tr>';
				default:
					break;
			}
			$result .= '</table>'; // end the table
			$result .= '<div style="display:none;"><div id="member"><h3 class="member-name">Member Name</h3><div class="member-content-wrapper"><div class="image-wrapper"><img class="member-picture" src="" /></div><h2 class="member-position">Position</h2><h4 class="member-major">Major</h4><div class="member-content">Placeholder</div></div></div>'; // uses some of the CSS from members (I hope...)

			$result .= '</div>';
			return $result; // finish the page
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