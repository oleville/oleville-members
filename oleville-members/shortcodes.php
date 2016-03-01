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
            $result .= '<div style="display:none;"><div id="member-lightbox"><h3 class="member-name">Member Name</h3><div class="member-content-wrapper"><div class="image-wrapper"><img class="member-picture" src="" /></div><h2 class="member-position">Position</h2><h4 class="member-major">Major</h4>';
            if (get_post_meta($positionID, 'hometown', TRUE) != '') {
            	$result .= '<strong>Hometown:</strong> <span style="font-weight: 400;" class="member-hometown">Hometown</span>';
            }
            $result .= '<div class="member-content">Placeholder</div></div></div>'; // uses some of the CSS from members (I hope...)

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
			$num_cols = 3;
			$col_count = 1;

			$currentDay = strtolower(current_time('l')); // the day of the week, no caps so that we can compare it to how we save the data in the member metadata
			$currentTimeHr = current_time('G') + 1; // 1 offset for timezone stuff (TODO: check this related to daylight savings time in future)
			$currentTimeMin = current_time('i');

			//the loop
			while ($query -> have_posts())
			{
				$query -> the_post();

				$memberID = get_the_ID();
				$memberTitle = get_the_title();
				$individual_position = '<div class="member-position">' . get_post_meta(get_the_ID(), 'position', TRUE) . '</div>';
				$in_the_office = FALSE;
				$thumb = get_the_post_thumbnail(get_the_ID(), "thumbnail");
				if(!$thumb) {
					$thumb = '<img src="'.get_template_directory_uri().'/img/placeholder_thumb.png" width="150" height="150">';
				}

				$m = array();
				$t = array();
				$w = array();
				$th = array();
				$f = array();

				// let's get the data into a more managable structure. Also, let's do military time, cause that's fun, right?
				$day = get_post_meta($memberID, 'day_of_week', TRUE);
				$startTime = get_post_meta($memberID, 'start_time', TRUE);
				$endTime = get_post_meta($memberID, 'end_time', TRUE);

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

				if(strtolower($day) == 'thursday')
				{
					$letter_day = 'Th';
				} else {
					$letter_day = strtoupper(substr($day, 0, 1));
				}

				if (($currentDay == $day)) { //if member has office hours today
					if (($currentTimeHr >= $startTimeHr) && ($currentTimeHr <= $endTimeHr)) { // and we are between the start and ending hours
						if ((($currentTimeMin >= $startTimeMin) && ($currentTimeMin <= $endTimeMin)) || ($currentTimeHr != $startTimeHr) || ($currentTimeMin != $endTimeHr)) { // and (if we are on either the starting or ending hour) we are between the starting and ending min
							//the member is in the office. Display them...
								$in_the_office = TRUE;
						}
					}
				}

				if ($startTimeHr > 12) {
					$startTimeHr = ($startTimeHr - 12);
					$startTimeMin .= 'pm';
				} else {
					$startTimeMin .= 'am';
				}
				if ($endTimeHr > 12) {
					$endTimeHr = ($endTimeHr - 12);
					$endTimeMin .= 'pm';
				} else {
					$endTimeMin .= 'am';
				}

				$to_push = $startTimeHr . ':' . $startTimeMin . ' - ' . $endTimeHr . ':' . $endTimeMin . '<br>';
				switch (strtolower($letter_day)) {
					case 'm':
						array_push($m, $to_push);
						break;
					case 't':
						array_push($t, $to_push);
						break;
					case 'w':
						array_push($w, $to_push);
						break;
					case 'th':
						array_push($th, $to_push);
						break;
					case 'f':
						array_push($f, $to_push);
						break;
					default:
						break;
				}

				$repeat_list = serialize(get_post_meta($memberID, 'repeat_list', TRUE));
				if (strlen($repeat_list) != 6) 
				{ // there are multiple office hours, so let's deal with that
					$i = strpos($repeat_list, "\"");
					$repeat_list = substr_replace($repeat_list, "", 0, $i); // get rid of the first part, only do this once

					$num_extra = substr($repeat_list, 3, 1); // this is the number of extra office hours

					for ($j = 0; $j != $num_extra; $j++)
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

						if(strtolower($day) == 'thursday')
						{
							$letter_day = 'Th';
						} else {
							$letter_day = strtoupper(substr($day, 0, 1));
						}

						if (($currentDay == $day)) { //if member has office hours today
							if (($currentTimeHr >= $startTimeHr) && ($currentTimeHr <= $endTimeHr)) { // and we are between the start and ending hours
								if ((($currentTimeMin >= $startTimeMin) && ($currentTimeMin <= $endTimeMin)) || ($currentTimeHr != $startTimeHr) || ($currentTimeMin != $endTimeHr)) { // and (if we are on either the starting or ending hour) we are between the starting and ending min
									//the member is in the office. Display them...
										$in_the_office = TRUE;
								}
							}
						}

						if ($startTimeHr > 12) {
							$startTimeHr = ($startTimeHr - 12);
							$startTimeMin .= 'pm';
						} else {
							$startTimeMin .= 'am';
						}
						if ($endTimeHr > 12) {
							$endTimeHr = ($endTimeHr - 12);
							$endTimeMin .= 'pm';
						} else {
							$endTimeMin .= 'am';
						}

						$to_push = $startTimeHr . ':' . $startTimeMin . ' - ' . $endTimeHr . ':' . $endTimeMin . '<br>';
						switch (strtolower($letter_day)) {
							case 'm':
								array_push($m, $to_push);
								break;
							case 't':
								array_push($t, $to_push);
								break;
							case 'w':
								array_push($w, $to_push);
								break;
							case 'th':
								array_push($th, $to_push);
								break;
							case 'f':
								array_push($f, $to_push);
								break;
							default:
								break;
						}
					}
				}

				//if statement checking if they are in/out of office and then changing the color of in/out
				if ($in_the_office){
					$result .= '<td colspan="'.$colspan.'" class="member" style="padding-bottom: 10px;"><center><div class="member_picture">' . $thumb . '</div><div class = "member" style = "color:green;"><strong>IN</strong></div><div class="member_name"><h3>' . get_the_title() . '</h3></div><div class="member-position"><h3>' . $individual_position . '</h3></div>'; // in (color)
				} else {
					$result .= '<td colspan="'.$colspan.'" class="member" style="padding-bottom: 10px;"><center><div class="member_picture">' . $thumb . '</div><div class = "member" style = "color:red;"><strong>OUT</strong></div><div class="member_name"><h3>' . get_the_title() . '</h3></div><div class="member-position"><h3>' . $individual_position . '</h3></div>'; // out (color)
				}

				if (!empty($m))
				{
					$result .= '<center><div class="member">' . 'M' . ' ';
					foreach ($m as $value) {
						$result .= $value;
					}
					$result .= '</div>';
				}
				if (!empty($t))
				{
					$result .= '<center><div class="member">' . 'T' . ' ';
					foreach ($t as $value) {
						$result .= $value;
					}
					$result .= '</div>';
				}

				if (!empty($w))
				{
					$result .= '<center><div class="member">' . 'W' . ' ';
					foreach ($w as $value) {
						$result .= $value;
					}
					$result .= '</div>';
				}

				if (!empty($th))
				{
					$result .= '<center><div class="member">' . 'Th' . ' ';
					foreach ($th as $value) {
						$result .= $value;
					}
					$result .= '</div>';
				}
				if (!empty($f))
				{
					$result .= '<center><div class="member">' . 'F' . ' ';
					foreach ($f as $value) {
						$result .= $value;
					}
					$result .= '</div>';
				}

<<<<<<< HEAD
				if ($col_count >= $num_cols)//check if we need a new row
				{
					$result .= '</tr><tr>';//make a new row
					$col_count = 0;
				} else {
					$col_count++;//keep going on this row
				}
			}
				if (!empty($f))
				{
					$result .= '<center><div class="member">' . 'F' . ' ';
					foreach ($f as $value) {
						$result .= $value;
					}
					$result .= '</div>';
				}

				if ($col_count >= $num_cols)//check if we need a new row
				{
					$result .= '</tr><tr>';//make a new row
					$col_count = 0;
				} else {
					$col_count++;//keep going on this row
				}
			}


			$result .= '</table>'; // end the table
            $result .= '<div style="display:none;"><div id="member"><h3 class="member-name">Member Name</h3><div class="member-content-wrapper"><div class="image-wrapper"><img class="member-picture" src="" /></div><h2 class="member-position">Position</h2><h4 class="member-major">Major</h4><div class="member-content">Placeholder</div></div></div>'; // uses some of the CSS from members (I hope...)
			return $result; // finish the page
		}


		public function show_front_page()
		{
			global $wpdb;
			
			$result .= '<div class="members"><table class="office-table">';

			$args = array(
				'post_type' => 'member',
				'posts_per_page' => -1,
				'orderby' => 'menu_order',
				'order' => 'ASC', // what code is this?
			);
			
			$membersIn = 0; // the number of members who are in right now

			// the query
			$query = new WP_Query($args);

			// need to set it to display in 4 columns, unlimited number of rows
			$num_cols = 3;
			$col_count = 1;

			$currentDay = strtolower(current_time('l')); // the day of the week, no caps so that we can compare it to how we save the data in the member metadata
			$currentTimeHr = current_time('G') + 1; // 1 offset for timezone stuff (TODO: check this related to daylight savings time in future)
			$currentTimeMin = current_time('i');

			//the loop
			while ($query -> have_posts())
			{
				$query -> the_post();

				$memberID = get_the_ID();
				$memberTitle = get_the_title();
				$individual_position = '<div class="member-position">' . get_post_meta(get_the_ID(), 'position', TRUE) . '</div>';
				$in_the_office = FALSE;

				// let's get the data into a more managable structure. Also, let's do military time, cause that's fun, right?
				$day = get_post_meta($memberID, 'day_of_week', TRUE);
				$startTime = get_post_meta($memberID, 'start_time', TRUE);
				$endTime = get_post_meta($memberID, 'end_time', TRUE);

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
						if ((($currentTimeMin >= $startTimeMin) && ($currentTimeMin <= $endTimeMin)) || ($currentTimeHr != $startTimeHr) || ($currentTimeHr != $endTimeHr)) { // and (if we are on either the starting or ending hour) we are between the starting and ending min
							//the member is in the office. Display them...
								$in_the_office = TRUE;
						}
					}
				}

				$repeat_list = serialize(get_post_meta($memberID, 'repeat_list', TRUE));
				if (strlen($repeat_list) != 6) 
				{ // there are multiple office hours, so let's deal with that
					$i = strpos($repeat_list, "\"");
					$repeat_list = substr_replace($repeat_list, "", 0, $i); // get rid of the first part, only do this once

					$num_extra = substr($repeat_list, 3, 1); // this is the number of extra office hours

					for ($j = 0; $j != $num_extra; $j++)
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
										$in_the_office = TRUE;
								}
							}
						}
					}
				}

				//if statement checking if they are in/out of office and then changing the color of in/out
				$letter_day = strtoupper(substr($day, 0, 1));
				if ($in_the_office){
					$result .= '<tr class="member-front-list"><td colspan="' . $colspan . '" class="member" style="padding-bottom: 10px;"><div class="member_name"><h2>' . get_the_title() . '</h2></div><div class="member-position"><h3>' . $individual_position . '</h3></div></tr>';
					$membersIn += 1;
				}
			}
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