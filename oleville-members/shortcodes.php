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
			//error_log("Calling Short Code");
			// Register Action Hooks
			add_action('init', array(&$this, 'init'));
			add_action('admin_init', array(&$this, 'admin_init'));
			//add_filter('single_template', array(&$this, 'elections_template'));

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
		}


		public function member_handler($attr) {

			return $this->show_members();
		}

		public function hours_handler($attr) {

			return $this->show_office_hours();
		}

		public function show_members() {
			global $wpdb;
			
			$result .= '<table class="member-table">';

			$args = array(
				'post_type' => 'member',
				'posts_per_page' => -1,
				'orderby' => 'menu_order',
				'order' => 'ASC', // what code is this?
			);

			// the query
			$query = new WP_Query($args);

			// need to set it to display in 4 columns, unlimited number of rows
			$num_cols = 4;
			$col_count = 0;

			//the loop
			while ($query -> have_posts())
			{
				$query -> the_post();

				$positionID = get_the_ID();
				$positionTitle = get_the_title();

				$thumb = get_the_post_thumbnail(get_the_ID(), "thumbnail");
				if(!$thumb) {
					$thumb = '<img src="'.get_template_directory_uri().'/img/placeholder_thumb.png" width="150" height="150">';
				}

				$result .= '<td colspan="'.$colspan.'" class="member" style="padding-bottom: 10px;"><center><div class="member_picture">' . $thumb . '</div><div class="member_name"><h3>' . get_the_title() . '</h3></div>';
				$result .= '<div class="button">'. '<button type="button" class="btn btn-primary member_profile" href="#lightbox-wrapper" data-toggle="modal" data-target="'. get_the_ID() . '">Member Profile</button><div class="profile">';
				$result .= '</center></div></td>';

				if ($col_count >= 3)//check if we need a new row
				{
					$result .= '</tr>';//make a new row
					$col_count = 0;
				} else {
					$col_count++;//keep going on this row
				}

			}

			$result .= '</table>'; // end the table
            $result .= '<div style="display:none;"><div id="member-lightbox"><h3 class="member-name">Member Name</h3><div class="member-content-wrapper"><div class="image-wrapper"><img class="member-picture" src="" /></div><h2 class="member-position">Position</h2><h4 class="member-major">Major</h4><div class="member-content">Placeholder</div></div></div>'; // uses some of the CSS from members (I hope...)

			return $result; // finish the page
		}

		public function show_office_hours()
		{
			global $wpdb;
			
			$result .= '<table class="office-table">';

			$args = array(
				'post_type' => 'member',
				'posts_per_page' => -1,
				'orderby' => 'menu_order',
				'order' => 'ASC', // what code is this?
			);

			// the query
			$query = new WP_Query($args);

			// need to set it to display in 4 columns, unlimited number of rows
			$num_cols = 2;
			$col_count = 0;

			$currentDay = strtolower(current_time('l')); // the day of the week, no caps so that we can compare it to how we save the data in the member metadata
			$currentTimeHr = current_time('G') + 1; //1 offset for timezone stuff (TODO: check this related to daylight savings time in future)
			$currentTimeMin = current_time('i');

			//the loop
			while ($query -> have_posts())
			{
				$query -> the_post();

				$memberID = get_the_ID();
				$memberTitle = get_the_title();

				// let's get the data into a more managable structure. Also, let's do military time, cause that's fun, right?
				$day = get_post_meta($memberID, 'day_of_week', TRUE);
				$startTime = get_post_meta($memberID, 'start_time', TRUE);
				$endTime = get_post_meta($memberID, 'end_time', TRUE);
				$doneAlready = FALSE;

				// put the times into a more usable structure. Military time, the hours and mins seperated so that we can compare them individually
				if (strlen($startTime) == 7)
				{
					$startTimeHr = substr($startTime, 0, 2);
					$startTimeMin = substr($startTime, 3, 2);
				} else {
					$startTimeHr = substr($startTime, 0, 1);
					$startTimeMin = substr($startTime, 2, 2);
				}
				if (strpos($startTime, 'pm')) {
					$startTimeHr += 12; 
				}
				if (strlen($endTime) == 7)
				{
					$endTimeHr = substr($endTime, 0, 2);
					$endTimeMin = substr($endTime, 3, 2);
				} else {
					$endTimeHr = substr($endTime, 0, 1);
					$endTimeMin = substr($endTime, 2, 2);
				}
				if (strpos($endTime, 'pm')) {
					$endTimeHr += 12; 
				}

				if (($currentDay == $day)) { //if member has office hours today
					if (($currentTimeHr >= $startTimeHr) && ($currentTimeHr <= $endTimeHr)) { // and we are between the start and ending hours
						if ((($currentTimeMin >= $startTimeMin) && ($currentTimeMin <= $endTimeMin)) || ($currentTimeHr != $startTimeHr) || ($currentTimeMin != $endTimeHr)) { // and (if we are on either the starting or ending hour) we are between the starting and ending min
							//the member is in the office. Display them...
							if (!$doneAlready) { // but only if we haven't done so already
								$doneAlready = TRUE; // and now we have, so let's make sure that we don't do it again.


								//TODO: WRITE THE MEMBER DISPLAY STUFF HERE
								write_log("oleville-members " . $memberTitle);




							}
						}
					}
				}

				if(!$doneAlready) // if we already displayed it, then don't do it again!!!
				{
					$repeat_list = serialize(get_post_meta($memberID, 'repeat_list', TRUE));
					if (strlen($repeat_list) != 6) { // there are multiple office hours, so let's deal with that
						$i = strpos($repeat_list, "\"");
						$repeat_list = substr_replace($repeat_list, "", 0, $i); // get rid of the first part, only do this once

						$num_extra = substr($repeat_list, 3, 1); // this is the number of extra office hours

						for ($j = 0; ($j != $num_extra) && (!$doneAlready); $j++)
						{ 
							$i = strpos($repeat_list, "\"", 1); // 1 offset for the first character being the "
							$repeat_list = substr_replace($repeat_list, "", 0, $i); // start
							$a = strpos($repeat_list, "\"", 1);
							$startTime = substr($repeat_list, 1, $a - 1);
							write_log($startTime);
							$repeat_list = substr_replace($repeat_list, "", 0, $a);


							$i = strpos($repeat_list, "\"", 1);
							$repeat_list = substr_replace($repeat_list, "", 0, $i); // end
							$a = strpos($repeat_list, "\"", 1);
							$endTime = substr($repeat_list, 1, $a - 1);
							write_log($endTime);
							$repeat_list = substr_replace($repeat_list, "", 0, $a);

							$i = strpos($repeat_list, "\"", 1);
							$repeat_list = substr_replace($repeat_list, "", 0, $i); // day
							$a = strpos($repeat_list, "\"", 1);
							$day = substr($repeat_list, 1, $a - 1);
							write_log($day);
							$repeat_list = substr_replace($repeat_list, "", 0, $a);

							// put the times into a more usable structure. Military time, the hours and mins seperated so that we can compare them individually
							if (strlen($startTime) == 7)
							{
								$startTimeHr = substr($startTime, 0, 2);
								$startTimeMin = substr($startTime, 3, 2);
							} else {
								$startTimeHr = substr($startTime, 0, 1);
								$startTimeMin = substr($startTime, 2, 2);
							}
							if (strpos($startTime, 'pm')) {
								$startTimeHr += 12; 
							}
							if (strlen($endTime) == 7)
							{
								$endTimeHr = substr($endTime, 0, 2);
								$endTimeMin = substr($endTime, 3, 2);
							} else {
								$endTimeHr = substr($endTime, 0, 1);
								$endTimeMin = substr($endTime, 2, 2);
							}
							if (strpos($endTime, 'pm')) {
								$endTimeHr += 12; 
							}

							if (($currentDay == $day)) { //if member has office hours today
								if (($currentTimeHr >= $startTimeHr) && ($currentTimeHr <= $endTimeHr)) { // and we are between the start and ending hours
									if ((($currentTimeMin >= $startTimeMin) && ($currentTimeMin <= $endTimeMin)) || ($currentTimeHr != $startTimeHr) || ($currentTimeMin != $endTimeHr)) { // and (if we are on either the starting or ending hour) we are between the starting and ending min
										//the member is in the office. Display them...
										if (!$doneAlready) { // but only if we haven't done so already
											$doneAlready = TRUE; // and now we have, so let's make sure that we don't do it again.


											//TODO: WRITE THE MEMBER DISPLAY STUFF HERE
											write_log("oleville-members " . $memberTitle);




										}
									}
								}
							}
						}
					}
				}

				if ($col_count >= 3)//check if we need a new row
				{
					$result .= '</tr>';//make a new row
					$col_count = 0;
				} else {
					$col_count++;//keep going on this row
				}

			}

			$result .= '</table>'; // end the table
            $result .= '<div style="display:none;"><div id="member-lightbox"><h3 class="member-name">Member Name</h3><div class="member-content-wrapper"><div class="image-wrapper"><img class="member-picture" src="" /></div><h2 class="member-position">Position</h2><h4 class="member-major">Major</h4><div class="member-content">Placeholder</div></div></div>'; // uses some of the CSS from members (I hope...)

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