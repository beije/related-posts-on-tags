<?php
class relatedPostsOnTags {

	private $limit = 5;
	private $categories = array();

	function __construct() {
		
	}
	
	static function hasJsonErrored() {
		switch (json_last_error()) {
			case JSON_ERROR_NONE:
				return false;
			break;
			case JSON_ERROR_DEPTH:
				return 'Maximum stack depth exceeded';
			break;
			case JSON_ERROR_STATE_MISMATCH:
				return 'Underflow or the modes mismatch';
			break;
			case JSON_ERROR_CTRL_CHAR:
				return 'Unexpected control character found';
			break;
			case JSON_ERROR_SYNTAX:
				return 'Syntax error, malformed JSON';
			break;
			case JSON_ERROR_UTF8:
				return 'Malformed UTF-8 characters, possibly incorrectly encoded';
			break;
			default:
				return 'Unknown error';
			break;
		}

	}

	private function getTag( $tagName ) {
		$tag = get_tags( 
			array(
				'name__like' => $tagName, 
				'order' => 'ASC'
			) 
		);

		return $tag; 
	}

	private function tagToId( $tagObject ) {
		return $tagObject->term_id;
	}

	private function catSlugToId( $catslug ) {
		$cat = get_category_by_slug( $catslug );
		return $cat->term_id;
	}

	public function search( $tags, $limit = 5, $categories = array(), $includeContent = false ) {
		$this->limit = $limit;
		$this->categories = $categories;

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
		$args = array();
		
		$args['tag__in'] = $tagids;

		// This is very expensive performance wise,
		// but it's the only way to look through all
		// posts without resorting to a real query :()
		$args['posts_per_page'] = -1;
		
		if( count($this->categories) > 0 ) {
			$args['cat__in'] = array_map( array($this, 'catSlugToId'), $this->categories );
		}
		$query = new WP_Query( $args );

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
		
		// Because we aren't able to use posts_per_page limitor
		// we have to limit the return data
		return array_splice( $posts, 0 , $this->limit );
	}

	public function debug( $obj ) {
		echo '<pre>';
		print_r( $obj );
		echo '</pre>';
	}
}

?>