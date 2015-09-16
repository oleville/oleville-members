<?php

class Oleville_Members_Display extends WP_Widget {

function __construct() {
	add_action('wp_footer', array(&$this, "print_scripts"), 30);
	add_action( 'widgets_init', array(&$this, 'wpb_load_widget') );
	add_action( 'init', array(&$this, 'check_widget') );

parent::__construct(
// Base ID of your widget
'ov_md',
'Oleville Member Display',
array(			'classname'   => 'oleville member-display secondary-color', 'description' => 'Displays committee members', )
);
}


// Creating widget front-end
// This is where the action happens
public function widget($args, $instance) {
		if($instance['display_type'] == 'carousel')
		{
			carousel_feature($args, $instance);
		} else {
			single_feature($args, $instance);
		}

}

// Widget Backend
public function form( $instance ) {
	if (isset($instance['title']))
	{
		$title = $instance[ 'title' ];
	}else{
		$title = get_option('branch_name');
	}

	if (isset($instance['display_type']))
	{
		$display_type = $instance[ 'display_type' ];
	}else{
		$display_type = 'single';
	}
// Widget admin form
?>
<p>
<label for="<?php echo $this->get_field_id( 'title' ); ?>">Branch Short Code:</label>
<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
</p>
<p>
<label for="<?php echo $this->get_field_id( 'display_type' ); ?>">Display Type</label>
<select id="<?php echo $this->get_field_id( 'display_type' ); ?>" name="<?php echo $this->get_field_name( 'display_type' ); ?>" class="widefat">
  <option value="single"<?php selected( $display_type, 'single' ); ?>>Single Member</option>
  <option value="carousel"<?php selected( $display_type, 'carousel' ); ?>>Carousel</option>
</select>
</p>
<?php
}

// Updating widget replacing old instances with new
public function update( $new_instance, $old_instance ) {
$instance = array();
$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
$instance['display_type'] = $new_instance['display_type'];
return $instance;
}


// Register and load the widget
public function wpb_load_widget() {
	register_widget( 'Oleville_Members_Display' );
}



public  function check_widget() {
        wp_enqueue_script('jquery-ui', plugins_url( 'js/jquery-ui-1.10.3.custom.min.js', __FILE__ ), array( 'jquery' ), array(), '1.0.0', true);
				wp_enqueue_script('jquery-mousewheel', plugins_url( 'js/jquery.mousewheel.min.js', __FILE__ ), array( 'jquery' ), array(), '1.0.0', true);
				wp_enqueue_script('jquery-kinetic', plugins_url( 'js/jquery.kinetic.min.js', __FILE__ ), array( 'jquery' ), array(), '1.0.0', true);
				wp_enqueue_script('smoothdivscroll', plugins_url( 'js/jquery.smoothDivScroll-1.3.js', __FILE__ ), array( 'jquery' ), array(), '1.0.1', true);
				wp_enqueue_script('ovmd-load', plugins_url( 'js/load.js', __FILE__ ), array( 'jquery' ), array(), '1.0.0', true);
				wp_enqueue_style('ovmd-style', plugins_url('css/smoothDivScroll.css', __FILE__));
}



function single_feature($args, $instance) {
	extract( $args, EXTR_SKIP );
	$title = apply_filters( 'widget_title', $instance['title'] );
	echo $before_widget;

		echo '<div class="branch-block branch-color">';

		echo '<a href="' . esc_url(home_url('member')) . '" title="' . esc_attr( $title ) . '">meet the<span class="short-name">' . $title . '</span>members</a>' . $after_title;

		echo '</div>';

		global $post;
		/* Set up the query arguments. */
		$args = array(
			'posts_per_page'   => 1,
			'post_type'        => 'member',
			'orderby'          => 'rand',
		);
		/**
		 * The main Query
		 *
		 * @link http://codex.wordpress.org/Function_Reference/get_posts
		 */
		$post_set = get_posts( $args );
		/* Check if posts exist. */
		if ( $post_set ) {
		?>
					<?php foreach ( $post_set as $post ) : setup_postdata( $post ); ?>

						<div class="member-info">

								<?php if ( has_post_thumbnail() ) { // Check If post has post thumbnail. ?>

									<a href="<?php echo esc_url(home_url('member')).'?m='.basename(get_permalink()); ?>" class="thumbnail" title="<?php the_title_attribute(); ?>" rel="bookmark">
										<?php the_post_thumbnail(
											array( 150, 150, true ),
											array(
												'class' => 'right member-photo',
												'alt'   => esc_attr( get_the_title() ),
												'title' => esc_attr( get_the_title() )
											)
										); ?>
                    <div class="position"> <?php echo get_post_meta(get_the_ID(), 'position', TRUE); ?> </div>
									</a>

								<?php } ?>

							<h3 class="member-name">
								<a href="<?php echo esc_url(home_url('member')).'?m='.basename(get_permalink()); ?>" class="branch-color-text" title="<?php the_title_attribute(); ?>" rel="bookmark"><?php the_title(); echo ' \'' . get_post_meta(get_the_ID(), 'class', TRUE);  ?></a>
							</h3>
								<div class="bio">
									<?php $content = get_the_content();
									$trimmed_content = wp_trim_words( $content, 30 );
									echo $trimmed_content; ?>
									<?php echo '<a href="' .  esc_url(home_url('member')) . '" class="more-link branch-color">Meet the others »</a>'; ?>
								</div>

						</div>

					<?php endforeach; wp_reset_postdata(); ?>

		<?php
		} /* End check. */

		echo '<div class="clear"></div>';

		echo $after_widget;
}

public function carousel_feature($args, $instance) {
	extract( $args, EXTR_SKIP );
	$title = apply_filters( 'widget_title', $instance['title'] );
	echo $before_widget;
		global $post;
		/* Set up the query arguments. */
		$args = array(
			'posts_per_page'   => -1,
			'post_type'        => 'member',
			'orderby'          => 'menu_order',
		);


		/**
		 * The main Query
		 *
		 * @link http://codex.wordpress.org/Function_Reference/get_posts
		 */
		$post_set = get_posts( $args );


		/* Check if posts exist. */
		if ( $post_set ) {
		?>
    <div class="carousel">
    <div class="featured">
					<?php foreach ( $post_set as $post ) : setup_postdata( $post ); ?>

            <?php
						$slug = basename(get_permalink());
					if(!$first)
						$first = $slug;
					/* Include the Post-Format-specific template for the content.
					 * If you want to override this in a child theme, then include a file
					 * called content-___.php (where ___ is the Post Format name) and that will be used instead.
					 */
					include(locate_template('content-member-display-front.php'))


						?>


					<?php endforeach; wp_reset_postdata(); ?>
          </div>
          <div id="members">
          <div class="scrollingHotSpotLeft" style="display: block; opacity: 0;"></div>
          <div class="scrollingHotSpotRight" style="opacity: 0; display: block;"></div>
          <div class="scrollWrapper">
          <div class="scrollableArea">
          <?php foreach ( $post_set as $post ) : setup_postdata( $post ); ?>

           		 <a href="#" data-target="<?php echo basename(get_permalink()) ?>" class="thumbnail">
										<?php if ( has_post_thumbnail() ) { // Check If post has post thumbnail. ?>
										<?php the_post_thumbnail(
											array( 75, 75 ),
											array(
												'class' => 'member-photo',
												'alt'   => esc_attr( get_the_title() ),
												'title' => esc_attr( get_the_title() )
											)
										); ?>
                    <?php } else { ?>
                    	<div style="display: block; background: #CCC; width:75px; height: 75px;"></div>
                    <?php } ?>
								</a>

					<?php endforeach; wp_reset_postdata(); ?>
          </div>
          </div>
          </div>
    <h1 class="title">Meet the <?php echo $title; ?> Members</h1>
    </div>
		<?php
		} /* End check. */
		?>

    <?php

		echo '<div class="clear"></div>';

		echo $after_widget;


		}

public function print_scripts () {
	if ( wp_script_is( 'smoothdivscroll', 'done' ) ) {
	echo '<script type="text/javascript">
	jQuery(document).ready(function($){
			// None of the options are set
			$("#members").smoothDivScroll({
				autoScrollingMode: "always",
				visibleHotSpotBackgrounds: "always",
				manualContinuousScrolling: true,
				autoScrollingInterval: 20,
			});
			$( "#members" ).bind( "mouseover", function () {
$( this ).smoothDivScroll( "stopAutoScrolling" );
} ).bind( "mouseout", function () {
$( this ).smoothDivScroll( "startAutoScrolling" );
} );
		});
</script>';
	}
}
} // Class ov_md widget ends here
