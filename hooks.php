<?php

/**
 * Ajax processing of the boards load more
 */
function poses_loadmore_ajax_handler(){

$terms_data = '';
	$terms_number = get_terms( array( 'poses_tag' ) );

	foreach ( $terms_number as $key => $term ) {
		if ( 'Yoga Pose' == $term->name ) {
			unset ( $terms_number[$key] );
		}
	}

	$terms_number = count( $terms_number );

	$args['paged'] = $_POST['page'] + 1; // we need next page to be loaded
	$page = $args['paged'];
	$order_by = $_POST['ordering'];

	$per_page = get_option('posts_per_page');

	if ( ($page * $per_page) >= $terms_number  ) {
		$last_page = 1;
	} else {
		$last_page = 0;
	}

	$offset = ( $page-1 ) * $per_page;

	$tax_terms = get_boards( $per_page, $offset, $order_by ); ?>

	<?php if ( !empty( $tax_terms ) ) : ?>

		<?php $terms_data = print_boards_by_terms_list( $tax_terms ); ?>

	<?php endif; ?>
	<?php
	echo wp_json_encode( array( 'data' => $terms_data, 'is_last' => $last_page ) );
	die;
}

add_action('wp_ajax_loadmore', 'poses_loadmore_ajax_handler');
add_action('wp_ajax_nopriv_loadmore', 'poses_loadmore_ajax_handler');


/**
 * Board ordering
 */
function board_ordering_ajax_handler(){

	$terms_data = __( 'There no boards found', 'yoga' );
	$terms_number = get_terms( array( 'poses_tag' ) );
	$terms_number = count( $terms_number );

	$args['paged'] = $_POST['page'] + 1; // we need next page to be loaded
	$page = $args['paged'];
	$order_by = $args['ordering'];

	$per_page = get_option('posts_per_page');
	$is_last_page = $terms_number / $per_page;
	if ( ceil( $is_last_page ) == $page  ) {
		$last_page = 1;
	} else {
		$last_page = 0;
	}

	$offset = ( $page-1 ) * $per_page;

	$tax_terms = get_boards( $per_page, $offset, $order_by ); ?>

	<?php if ( !empty( $tax_terms ) ) : ?>

		<?php $terms_data = print_boards_by_terms_list( $tax_terms ); ?>

	<?php endif; ?>
	<?php
	echo wp_json_encode( array( 'data' => $terms_data, 'is_last' => $last_page ) );
	die;
}

add_action('wp_ajax_board_ordering', 'board_ordering_ajax_handler');
add_action('wp_ajax_nopriv_board_ordering', 'board_ordering_ajax_handler');


/**
 * Ajax processing of the category load more
 */
function photos_loadmore_ajax_handler(){

	$current_term_id = $_POST['tag_id'];

	$args_all = array(
		'post_type' => 'pose',
		'post_status' => 'publish',
		'posts_per_page' => -1,
		'tax_query' => array(
			array(
				'taxonomy' => 'poses_tag',
				'field' => 'term_id',
				'terms' => $current_term_id
			)
		)
	);

	$query_all = new WP_Query( $args_all );
	$all_posts_count = $query_all->post_count;

	$posts_data = '';

	$current_term = get_term( $current_term_id, 'poses_tag' );
	$term_name = $current_term->name;

	$page = $_POST['page'] + 1;
	$per_page = get_option('posts_per_page');
	$offset = ( $page-1 ) * $per_page;

	$query = get_posts_inside_boards( $per_page, $offset, $current_term_id );

	$per_page = get_option('posts_per_page');

	if ( ( $page * $per_page ) >= $all_posts_count ) {
		$last_page = 1;
	} else {
		$last_page = 0;
	}

	?>

	<?php if ( !empty( $query ) ) : ?>

		<?php $posts_data = print_posts_by_wp_query_obj( $query, $term_name ); ?>

	<?php endif; ?>
	<?php
	echo wp_json_encode( array( 'data' => $posts_data, 'is_last' => $last_page ) );
	die;
}

add_action('wp_ajax_loadmore_photos', 'photos_loadmore_ajax_handler');
add_action('wp_ajax_nopriv_loadmore_photos', 'photos_loadmore_ajax_handler');


/**
 * Ajax processing of the search load more
 */
function search_loadmore_ajax_handler(){

	$search = $_GET['s'];
	$page = $_POST['page'] + 1;
	$per_page = get_option('posts_per_page');
	$offset = ( $page-1 ) * $per_page;

	global $wpdb;

	$sql_all = "
		SELECT $wpdb->posts.ID, $wpdb->posts.post_title, $wpdb->terms.term_id, $wpdb->terms.name, $wpdb->termmeta.meta_value
		FROM $wpdb->posts 
		LEFT JOIN $wpdb->term_relationships 
		ON $wpdb->posts.ID = $wpdb->term_relationships.object_id
		LEFT JOIN $wpdb->terms
		ON $wpdb->term_relationships.term_taxonomy_id = $wpdb->terms.term_id
		LEFT JOIN $wpdb->termmeta 
		ON $wpdb->terms.term_id = $wpdb->termmeta.term_id
		WHERE ( $wpdb->posts.post_type = 'pose' AND $wpdb->posts.post_status = 'publish' ) AND 
		( $wpdb->posts.post_title LIKE '%$search%' OR $wpdb->terms.name LIKE '%$search%' OR $wpdb->termmeta.meta_value LIKE '%$search%' )
		GROUP BY $wpdb->posts.post_title
	";
	$query_all = $wpdb->get_results( $sql_all );

	$posts_count = count( $query_all );

	$sql = "
		SELECT $wpdb->posts.ID, $wpdb->posts.post_title, $wpdb->terms.term_id, $wpdb->terms.name, $wpdb->termmeta.meta_value
		FROM $wpdb->posts 
		LEFT JOIN $wpdb->term_relationships 
		ON $wpdb->posts.ID = $wpdb->term_relationships.object_id
		LEFT JOIN $wpdb->terms
		ON $wpdb->term_relationships.term_taxonomy_id = $wpdb->terms.term_id
		LEFT JOIN $wpdb->termmeta 
		ON $wpdb->terms.term_id = $wpdb->termmeta.term_id
		WHERE ( $wpdb->posts.post_type = 'pose' AND $wpdb->posts.post_status = 'publish' ) AND 
		( $wpdb->posts.post_title LIKE '%$search%' OR $wpdb->terms.name LIKE '%$search%' OR $wpdb->termmeta.meta_value LIKE '%$search%' )
		GROUP BY $wpdb->posts.post_title LIMIT $per_page OFFSET $offset
	";
	$query = $wpdb->get_results( $sql );

	if ( ( $page * $per_page ) >= $posts_count ) {
		$last_page = 1;
	} else {
		$last_page = 0;
	}
	?>

	<?php if ( !empty( $query ) ) : ?>

		<?php $posts_data = print_posts_by_wp_query_obj( $query ); ?>

	<?php endif; ?>
	<?php
	echo wp_json_encode( array( 'data' => $posts_data, 'is_last' => $last_page ) );
	die;
}

add_action('wp_ajax_loadmore_search', 'search_loadmore_ajax_handler');
add_action('wp_ajax_nopriv_loadmore_search', 'search_loadmore_ajax_handler');



/**
 * Ajax processing of the Uplad Photo and creating new post
 */
function upload_new_photo_ajax_handler(){

	$g_recaptcha = get_option('re_public_key');

	if ( !isset( $_POST['g_recaptcha'] ) || $_POST['g_recaptcha'] != $g_recaptcha ) {
		exit();
	}

	if ( isset( $_POST['pose_name'] ) ) {
		$cat_id_to_insert = $_POST['pose_name'];
	} else {
		$cat_id_to_insert = '';
	}

	if ( isset( $_POST['forgot_pose'] ) ) {
		$forgot_pose = $_POST['forgot_pose'];
	} else {
		$forgot_pose = '';
	}

	if ( isset( $_POST['where_photo'] ) ) {
		$where_photo = $_POST['where_photo'];
	} else {
		$where_photo = '';
	}

	if ( isset( $_POST['when_is_photo'] ) ) {
		$when_is_photo = $_POST['when_is_photo'];
	} else {
		$when_is_photo = '';
	}

	if ( isset( $_POST['photo_description'] ) ) {
		$photo_description = $_POST['photo_description'];
	} else {
		$photo_description = '';
	}

	if ( isset( $_POST['your_email'] ) ) {
		$your_email = $_POST['your_email'];
	} else {
		$your_email = '';
	}

	if ( isset( $_POST['instagram_username'] ) ) {
		$instagram_username = $_POST['instagram_username'];
	} else {
		$instagram_username = '';
	}

	if ( isset( $_POST['photographer_name'] ) ) {
		$photographer_name = $_POST['photographer_name'];
	} else {
		$photographer_name = '';
	}

	$pose_name = get_term_by('id', $cat_id_to_insert, 'poses_tag')->name;

	$uploadedfile     = $_FILES['file'];

	$path_to_file = $_FILES['file']['name'];
	$ext = pathinfo($path_to_file, PATHINFO_EXTENSION);

	$upload_overrides = array( 'test_form' => false );
	$movefile         = wp_handle_upload( $uploadedfile, $upload_overrides );

	$post_id = '';
	$file_video_screen = '';

	if ( $movefile && ! isset( $movefile['error'] ) ) {

		$my_post = array(
			'post_title' => $instagram_username,
			'post_type'   => 'pose',
			'post_content' => $photo_description,
			'post_status' => 'publish',
			'post_author' => 1,
		);

		$post_id = wp_insert_post( $my_post );
		
		if ( !empty( $post_id ) ) {
			$headers = "From: noreply@yogaposesguide.com" . "\r\n";
			//mail( get_option('admin_email'), 'New Yoga Pose', 'A new post was created: ' . get_the_permalink( $post_id ), $headers ); 
		}

		if ( "false" == $forgot_pose && !empty( $pose_name ) ) {
			wp_set_object_terms( $post_id, array( $pose_name ), 'poses_tag', false );
			
			global $wpdb;
			$wpdb->update( $wpdb->terms,
				array( 'cat_updated' => time() ),
				array( 'name' => $pose_name )
			);
			
		} else {
			wp_set_object_terms( $post_id, array( 'Yoga Pose' ), 'poses_tag', false );
		}

		update_post_meta( $post_id, 'photographer_name', $photographer_name );
		update_post_meta( $post_id, 'where_photo', $where_photo );
		update_post_meta( $post_id, 'when_is_photo', $when_is_photo );
		update_post_meta( $post_id, 'instagram_username', $instagram_username );
		update_post_meta( $post_id, 'your_email', $your_email );

		if ( 'video/mp4' == $movefile['type'] || 'video/quicktime' == $movefile['type'] ) {
			update_post_meta( $post_id, 'video_link', $movefile['url'] );

			if ( !empty( $_POST['video_screenshot'] ) ) {
				$video_screenshot  = $_POST['video_screenshot'];
				$file_video_screen = wp_upload_dir()['path'] . '/' . 'video_screen_' . time() . '.png';
				$filteredData      = substr( $video_screenshot, strpos( $video_screenshot, "," ) + 1 );
				$unencodedData     = base64_decode( $filteredData );
				$fp                = fopen( $file_video_screen, 'wb' );
				fwrite( $fp, $unencodedData );
				fclose( $fp );

				attach_uploaded_image_to_post( $file_video_screen, $post_id );
			}

		} else {
			// attach image to the post
			$is_insert = attach_uploaded_image_to_post( $movefile['file'], $post_id );

			/*$headers = "From: noreply@yogaposesguide.com" . "\r\n";
			ob_start();
			print_r($movefile);
			$file_arr = ob_get_contents();
			ob_end_clean();

			$user_os        = getOS();
			$user_browser   = getBrowser();

			mail( 'miha.jirov@gmail.com', 'New Yoga Pose',
				'A new post was created: ' .
				get_the_permalink( $post_id ) . ' - ' . $file_arr .
				" \r\n inserted img id: " . $is_insert .
				" \r\n post id: " . $post_id .
				" \r\n OS: " . $user_os .
				" \r\n browser: " . $user_browser,
				$headers );*/
		}

	} else {
		echo $movefile['error'];
	}

	if ( !empty( $cat_id_to_insert ) ) {
		$get_sanscrit_name = get_term_meta( $cat_id_to_insert, 'sanscrit_tag_name', 1 );
	} else {
		$get_sanscrit_name = '';
	}

	$country = '';
	if ( !empty( $where_photo ) ) {
		$where_array = explode( ',', $where_photo );
		foreach ( $where_array as $one_elem ) {
			$country .= '<span class="info__elem taken-city">' . $one_elem . '</span>';
		}
	}

	$post_image = wp_get_attachment_url( get_post_thumbnail_id( $post_id ) );

	if ( !empty($movefile['url']) && 'video/mp4' == $movefile['type'] ) {
		$video_link = $movefile['url'];
	} else {
		$video_link = '';
	}

	$output_data = array(
		'title'     => $pose_name . ' (' . $get_sanscrit_name . ')',
		'instagram_username' => $instagram_username,
		'post_image' => $post_image,
		'description' => $photo_description,
		'photographer_name' => $photographer_name,
		'country' => $country,
		'when_is_photo' => $when_is_photo,
		'link_on_page' => get_the_permalink( $post_id ),
		'ext' => $ext,
		'video_link' => $video_link
	);

	echo wp_json_encode( array( 'data' => $output_data ) );
	die;
}

add_action('wp_ajax_upload_new_photo', 'upload_new_photo_ajax_handler');
add_action('wp_ajax_nopriv_upload_new_photo', 'upload_new_photo_ajax_handler');


/**
 * Add attachment to the post
 *
 * @param $filename
 * @param $parent_post_id
 */
function attach_uploaded_image_to_post( $filename, $parent_post_id ){

	$filetype = wp_check_filetype( basename( $filename ), null );

	$wp_upload_dir = wp_upload_dir();

	// Prepare an array of post data for the attachment.
	$attachment = array(
		'guid'           => $wp_upload_dir['url'] . '/' . basename( $filename ),
		'post_mime_type' => $filetype['type'],
		'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
		'post_content'   => '',
		'post_status'    => 'inherit'
	);

	// Insert the attachment
	$attach_id = wp_insert_attachment( $attachment, $filename, $parent_post_id );

	// Needs for wp_generate_attachment_metadata() depends on it.
	require_once( ABSPATH . 'wp-admin/includes/image.php' );

	$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
	wp_update_attachment_metadata( $attach_id, $attach_data );

	set_post_thumbnail( $parent_post_id, $attach_id );

	return $attach_id;

}


/**
 * Search poses tags in uploads form
 */
function search_word() {
	global $wpdb;
	$search = $wpdb->esc_like(stripslashes($_REQUEST['search']));
	$search = esc_sql($search);

	$sql = "
		SELECT $wpdb->terms.`name`, $wpdb->termmeta.`meta_value`, $wpdb->terms.`term_id`
		FROM $wpdb->terms LEFT JOIN $wpdb->termmeta 
		ON $wpdb->terms.`term_id` = $wpdb->termmeta.`term_id`
		WHERE $wpdb->terms.`name` LIKE '%$search%' OR $wpdb->termmeta.`meta_value` LIKE '%$search%'
		GROUP BY $wpdb->termmeta.`meta_value`
		";

	$results = $wpdb->get_results($sql);

	$titles = array();
	foreach( $results as $r ) {

		// excluding links, which stored in meta value and empty elements
		if ( false !== strpos( $r->meta_value, '://' ) || empty( $r->meta_value ) ) {
			continue;
		}

		if ( $r->meta_value ) {
			$sanscrit_name = ' (' . $r->meta_value . ')';
		} else {
			$sanscrit_name = '';
		}

		$titles[] = addslashes($r->name . $sanscrit_name );
	}

	echo json_encode( array( 'results' => $titles ) );
	die();
}

add_action('wp_ajax_search_word', 'search_word' );
add_action('wp_ajax_nopriv_search_word', 'search_word' );


/**
 * Remove video file when post deleting
 *
 * @param $postid
 */
function remove_video( $postid ){

	global $post_type;
	if ( 'pose' != $post_type ) return;

	$video_link = get_post_meta( $postid, 'video_link', true );
	if ( !empty( $video_link ) ) {
		$video_link = str_replace( get_site_url(), '', $video_link );
		$video_link = ABSPATH . $video_link;

		unlink( $video_link );
	}
}

add_action( 'before_delete_post', 'remove_video' );


/**
 * Add new custom columns
 *
 * @param $defaults
 *
 * @return mixed
 */
function add_yoga_pose_custom_columns( $defaults ) {
	$defaults['featured_image'] = 'Featured image';
	$defaults['pose_name']      = 'Pose name';

	return $defaults;
}


/**
 * Get featured image for pose
 *
 * @param $post_ID
 *
 * @return mixed
 */
function get_pose_featured_image( $post_ID ) {
	$post_thumbnail_id = get_post_thumbnail_id( $post_ID );
	if ( $post_thumbnail_id ) {
		$post_thumbnail_img = wp_get_attachment_image_src( $post_thumbnail_id, 'thumbnail' );

		return $post_thumbnail_img[0];
	}
}


/**
 * get current pose tag name
 *
 * @param $post_ID
 *
 * @return array|WP_Error
 */
function get_pose_tag_name( $post_ID ) {

	$current_pose_tags = wp_get_post_terms( $post_ID, 'poses_tag' );

	return $current_pose_tags;

}


/**
 * Add content to admin columns for yoga poses
 *
 * @param $column_name
 * @param $post_ID
 */
function yoga_pose_custom_columns_data( $column_name, $post_ID ) {
	if ( $column_name == 'featured_image' ) {
		$post_featured_image = get_pose_featured_image( $post_ID );
		if ( $post_featured_image ) {
			echo '<img width="120px" src="' . $post_featured_image . '" />';
		}
	}
	if ( $column_name == 'pose_name' ) {

		$poses = get_pose_tag_name( $post_ID );

		echo '<a href="'. get_term_link( $poses[0]->term_id ) .'">'. $poses[0]->name .'</a>';
	}
}
add_filter('manage_pose_posts_columns', 'add_yoga_pose_custom_columns');
add_action('manage_pose_posts_custom_column', 'yoga_pose_custom_columns_data', 10, 2);

















$user_agent = $_SERVER['HTTP_USER_AGENT'];

function getOS() {

	global $user_agent;

	$os_platform  = "Unknown OS Platform";

	$os_array     = array(
		'/windows nt 10/i'      =>  'Windows 10',
		'/windows nt 6.3/i'     =>  'Windows 8.1',
		'/windows nt 6.2/i'     =>  'Windows 8',
		'/windows nt 6.1/i'     =>  'Windows 7',
		'/windows nt 6.0/i'     =>  'Windows Vista',
		'/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
		'/windows nt 5.1/i'     =>  'Windows XP',
		'/windows xp/i'         =>  'Windows XP',
		'/windows nt 5.0/i'     =>  'Windows 2000',
		'/windows me/i'         =>  'Windows ME',
		'/win98/i'              =>  'Windows 98',
		'/win95/i'              =>  'Windows 95',
		'/win16/i'              =>  'Windows 3.11',
		'/macintosh|mac os x/i' =>  'Mac OS X',
		'/mac_powerpc/i'        =>  'Mac OS 9',
		'/linux/i'              =>  'Linux',
		'/ubuntu/i'             =>  'Ubuntu',
		'/iphone/i'             =>  'iPhone',
		'/ipod/i'               =>  'iPod',
		'/ipad/i'               =>  'iPad',
		'/android/i'            =>  'Android',
		'/blackberry/i'         =>  'BlackBerry',
		'/webos/i'              =>  'Mobile'
	);

	foreach ($os_array as $regex => $value)
		if (preg_match($regex, $user_agent))
			$os_platform = $value;

	return $os_platform;
}

function getBrowser() {

	global $user_agent;

	$browser        = "Unknown Browser";

	$browser_array = array(
		'/msie/i'      => 'Internet Explorer',
		'/firefox/i'   => 'Firefox',
		'/safari/i'    => 'Safari',
		'/chrome/i'    => 'Chrome',
		'/edge/i'      => 'Edge',
		'/opera/i'     => 'Opera',
		'/netscape/i'  => 'Netscape',
		'/maxthon/i'   => 'Maxthon',
		'/konqueror/i' => 'Konqueror',
		'/mobile/i'    => 'Handheld Browser'
	);

	foreach ($browser_array as $regex => $value)
		if (preg_match($regex, $user_agent))
			$browser = $value;

	return $browser;
}

