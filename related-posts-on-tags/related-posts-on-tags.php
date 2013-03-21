<?php
/*
Plugin Name: Related posts on tags
Plugin URI: http://www.benjaminhorn.se
Description: Fetches related posts based on tag
Version: 0.0.0
Author: Benjamin Horn
Author URI: http://www.benjaminhorn.se
*/

include( 'class.relatedPostsOnTags.plugin.php' );
function rpot_search_on_tags() {
	
	// Should really clean up the data validation.
	if( !isset( $_REQUEST['tags'] ) ) {
		echo 'false';
		exit;
	}

	$rpot = new relatedPostsOnTags();
	$tags = (Array) json_decode( stripslashes_deep( $_REQUEST['tags'] ) );

	echo json_encode( $rpot->search( $tags ) );
	exit;
}

add_action('wp_ajax_search_on_tags', 'rpot_search_on_tags');
add_action('wp_ajax_nopriv_search_on_tags', 'rpot_search_on_tags');

?>