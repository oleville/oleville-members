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
			'class',
			'subcommittee',
			'branch',
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

			// registering external scripts
			wp_register_script( 'members-colorbox', WP_PLUGIN_URL.'/oleville-members/js/jquery.colorbox-min.js', array('jquery'));

			wp_enqueue_script( 'jquery' );

			//registering the members colorbox
			//TODO: recreate this, currently stealing the one from voting
			wp_enqueue_style( 'members-colorbox', WP_PLUGIN_URL.'/oleville-members/css/colorbox.css');

			// Add the shortcode hook
			if (!shortcode_exists('show-members')) {
				add_shortcode('show-members', array(&$this, 'member_handler'));
			}
		}


		public function member_handler($attr) {

			return $this->show_members();
		}

		public function show_members() {
			global $wpdb;
			$result .= apply_filters( 'the_content', $this->current_election['post_content'] );
			$result .= '<table class="member-table">';

			$args = array(
				'post_type' => 'member',
				'posts_per_page' => -1,
				'orderby' => 'menu_order',
				'order' => 'ASC', // what code is this?
				'category' => '' // how to get command-line style args from the shortcode call?
			);

			// the query
			$query = new WP_Query($args);

			//the loop
			while ($query -> have_posts())
			{
				$query -> the_post();
				$positionID = get_the_ID();
				$positionTitle = get_the_title();

				$mem_args = array(
				'posts_per_page' => -1,
				'post_type' => 'member',
				'orderby' => 'title',
				);
				$members_query = new WP_Query($mem_args);

				$result .= '<tr>';
				// need to set it to display in 4 columns, unlimited number of rows
				$num_cols = 4;
				$col_count = 0;

				$colspan = 12/$candidates_query->post_count;
				while($members_query -> have_posts()) {
					$members_query -> the_post();

					$thumb = get_the_post_thumbnail(get_the_ID(), "thumbnail");

					if(!$thumb) {
						$thumb = '<img src="'.get_template_directory_uri().'/img/placeholder_thumb.png" width="150" height="150">';
					}

					$result .= '<td colspan="'.$colspan.'" class="member" style="padding-bottom: 10px;"><center><div class="member_picture">' . $thumb . '</div><div class="member_name"><h3>' . get_the_title() . '</h3></div>';

					$result .= '<div class="button">'. '<button type="button" class="btn btn-primary member_profile" href="#lightbox-wrapper" data-toggle="modal" data-target="'. get_the_ID() . '">Member Profile</button><div class="profile">';

					$result .= '</center></div></td>';

					if ($col_count == 3)//check if we need a new row
					{
						$result .= '</tr>';//make a new row
						$col_count = 0;
					} else {
						$col_count++;//keep going on this row
					}
				}
			}

			$result .= '</table>'; // end the table

			$result .= '<div style="display:none;"><div id="member-lightbox"><h3 class="member-name">Member Name</h3><div class="member-content-wrapper"><div class="image-wrapper"><img class="member-picture" src="" /></div><div class="member-content">Placeholder</div></div></div>';

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
