<?php
class relatedPostsOnTags {
	function __construct() {
		
	}
	
	private function getTag( $tagName ) {
		$tag = get_tags( array('name__like' => $tagName, 'order' => 'ASC') );
		return $tag; 
	}

	private function tagToId( $tagObject ) {
		return $tagObject->term_id;
	}

	public function search( $tags, $includeContent = false ) {
		$matches = $this->fetchPosts( $tags );
		$posts = array();

		foreach( $matches as $k => $match ) {
			$post = get_post( $match['postid'], "ARRAY_A" );
			$p = array();
			if( $post['post_status'] != 'publish' ) {
				continue;
			}
			$p['postid'] = $post['ID'];
			$p['title'] = $post['post_title'];
			$p['date'] = $post['post_date'];
			$p['timestamp'] = strtotime( $post['post_date'] );

			if( get_the_post_thumbnail( $post['ID'] ) != '' ) {
				$p['image_full'] = wp_get_attachment_image_src( get_post_thumbnail_id( $post['ID'] ), 'full');
				$p['image_large'] = wp_get_attachment_image_src( get_post_thumbnail_id( $post['ID'] ), 'large');
				$p['image_medium'] = wp_get_attachment_image_src( get_post_thumbnail_id( $post['ID'] ), 'medium');
				$p['image_small'] = wp_get_attachment_image_src( get_post_thumbnail_id( $post['ID'] ), 'small');
			}

			if( $includeContent ) {
				$p['content'] = $post['content'];
			}

			$p['url'] = get_permalink( $post['ID'] );
			$p['position'] = $k;
			$p['weight'] = $match['weight'];

			$posts[] = (Object) $p;
		}	

		return $posts;
	}

	private function fetchPosts( $tags ) {
		
		$tagids = array();
		foreach( $tags as $tag ) {
			$t = $this->getTag( $tag );
			if( count( $t ) > 0 ) {
				$tagids[] = $t[0]->term_id;
			}
		}

		$posts = array();
		$postids = array();
		$weights = array();
		$query = new WP_Query( array( 'tag__in' => $tagids ) );
		$c = 0;

		while ( $query->have_posts() ) {
			$query->the_post();
			
			$tags = get_the_tags();
			if( !$tags ) {
				continue;
			}

			$posttags = array_map( array( $this, 'tagToId' ), $tags );
			
			$w = count( array_intersect($posttags, $tagids) );
			$postids[] = get_the_ID();
			$weights[] = $w;
			$posts[] = array(
				'postid' => get_the_ID(),
				'weight' => $w
			);

			$c++;
		}
		array_multisort($weights, SORT_DESC, $posts);

		wp_reset_postdata();
		
		return $posts;
	}

	public function debug( $obj ) {
		echo '<pre>';
		print_r( $obj );
		echo '</pre>';
	}
}

?>