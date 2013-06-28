<?php
/*
Plugin Name: Related posts on tags
Plugin URI: http://www.benjaminhorn.se
Description: Fetches related posts based on tag
Version: 0.0.1
Author: Benjamin Horn
Author URI: http://www.benjaminhorn.se
*/

include( 'class.relatedPostsOnTags.plugin.php' );
function rpot_search_on_tags() {
	$categories = array();
	$limit = 5;

	// Should really clean up the data validation.
	if( !isset( $_REQUEST['tags'] ) ) {
		echo 'false';
		exit;
	}

	if( isset( $_REQUEST['cats'] ) ) {
		$categories = @json_decode( stripslashes_deep( $_REQUEST['cats'] ) );

		if( relatedPostsOnTags::hasJsonErrored() ) {
			echo json_encode( relatedPostsOnTags::hasJsonErrored() );
			exit;
		}
	}

	if( isset( $_REQUEST['limit'] ) ) {
		$limit = intval( $_REQUEST['limit'] );
	}

	$rpot = new relatedPostsOnTags();
	$tags = @json_decode( stripslashes_deep( $_REQUEST['tags'] ) );

	if( relatedPostsOnTags::hasJsonErrored() ) {
		echo json_encode( relatedPostsOnTags::hasJsonErrored() );
		exit;
	}

	echo json_encode( $rpot->search( $tags, $limit, $categories ) );
	exit;
}

add_action('wp_ajax_search_on_tags', 'rpot_search_on_tags');
add_action('wp_ajax_nopriv_search_on_tags', 'rpot_search_on_tags');

?>