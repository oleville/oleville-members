<?php

$args = array(
	'post_type' => 'member',
	'posts_per_page' => -1,
	'orderby' => 'menu_order',
	'order' => 'ASC',
);

// the query
$query = new WP_Query($args);
//remove_filter('posts_orderby', 'custom_posts_orderby');

//the loop
while ($query -> have_posts())
{
	$query -> the_post();
	$postID = get_the_ID();
	$title = get_the_title();
	$description = get_post_meta($post -> ID, 'description', TRUE);
}
$returner ='
<!-- Modal -->
<div class="modal fade" id="" tabindex="-1" role="dialog" aria-labelledby="Label" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
		</div>		
		<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
	</div>';
//return returner;
?>
