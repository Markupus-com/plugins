<?php

// turn on post thumbnails
add_theme_support('post-thumbnails' );
add_theme_support('title-tag' );

/**
 * Enqueue scripts and styles
 */
function yoga_load_scripts_styles() {

	$folder_url = plugin_dir_url( __FILE__ );
	$ver        = '2.0.61';

	wp_deregister_script( 'jquery' );
	wp_register_script( 'jquery', $folder_url . 'static/js/jquery.min.js', array(), $ver );
	wp_enqueue_script( 'jquery' );

	wp_enqueue_style( 'yoga-main-css', $folder_url . 'static/css/main.css', array(), $ver );
	wp_enqueue_style( 'yoga-awesome-css','https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css', array(), $ver );
	wp_enqueue_style( 'yoga-template-css', $folder_url . 'style.css', array(), $ver );
	wp_enqueue_style( 'yoga-jssocials-css', $folder_url . 'static/css/jssocials.css', array(), $ver );
	wp_enqueue_style( 'yoga-jssocials-classic-css', $folder_url . 'static/css/jssocials-theme-classic.css', array(), $ver );
	wp_enqueue_style( 'yoga-autocomplete-css', $folder_url . 'static/css/jquery.auto-complete.css', array(), $ver );

	wp_enqueue_script( 'yoga-main-js', $folder_url . 'static/js/main.js#asyncload', array(
		'jquery',
		//'yoga-imagesloaded-js',
	), $ver );


	//wp_enqueue_script( 'yoga-imagesloaded-js', 'https://unpkg.com/imagesloaded@4/imagesloaded.pkgd.js', array( 'jquery' ), $ver );
	wp_enqueue_script( 'yoga-pinit-js', '//assets.pinterest.com/js/pinit.js', array( 'jquery', 'yoga-custom-js' ), $ver );
	wp_enqueue_script( 'yoga-clipboard-js', '//cdnjs.cloudflare.com/ajax/libs/clipboard.js/1.5.12/clipboard.min.js', array( 'jquery', 'yoga-custom-js' ), $ver );

	wp_enqueue_script( 'yoga-jquery-geocomplete-js', $folder_url . 'static/js/jquery.geocomplete.js', array(
		'jquery',
	), $ver );

	$google_api_key = get_option( 'google_api_key' );
	$google_api_key = ( $google_api_key ) ? $google_api_key : 'AIzaSyDETi22ZO5M8_06vFL1dChb366w3T4Ai1I';
	wp_enqueue_script( 'yoga-aucocomplete-address-js', 'https://maps.googleapis.com/maps/api/js?key='.$google_api_key.'&libraries=places&language=en&region=USA', array(
		'jquery',
		'yoga-custom-js',
		'yoga-jquery-geocomplete-js',
	), $ver );


	wp_enqueue_script( 'yoga-autocomplete-js', $folder_url . 'static/js/jquery.auto-complete.min.js', array(
		'jquery',
		//'yoga-imagesloaded-js',
	), $ver );

	wp_enqueue_script( 'yoga-custom-js', $folder_url . 'static/js/custom.js#asyncload', array(
		'jquery',
		'yoga-main-js',
		//'yoga-imagesloaded-js',
		'yoga-autocomplete-js',
	), $ver );

	wp_enqueue_script( 'yoga-socials-js', $folder_url . 'static/js/jssocials.min.js', array( 'jquery' ), $ver );

	wp_localize_script( 'yoga-custom-js', 'poses_loadmore_params', array(
		'ajaxurl' => site_url() . '/wp-admin/admin-ajax.php', // WordPress AJAX
		'current_page' => get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1,
	) );
}
add_action( 'wp_enqueue_scripts', 'yoga_load_scripts_styles' );

function add_async_forscript($url){
	if (strpos($url, '#asyncload')===false)
		return $url;
	else if (is_admin())
		return str_replace('#asyncload', '', $url);
	else
		$def_script = str_replace('#asyncload', '', $url)."'" . 'defer';
		$def_script = str_replace("defer'", 'defer', $def_script);

		return $def_script;
}
add_filter('clean_url', 'add_async_forscript', 11, 1);

if ( function_exists( 'add_image_size' ) ) {
	add_image_size( 'board-thumb', 450, 300 );
	add_image_size( 'inside-board-image-list', 450, 9999 );
	add_image_size( 'single-post-image', 1200, 9999 );
	add_image_size( 'board-images-list', 130, 130 );
}
add_filter('jpeg_quality', function($arg){return 60;}); 

/**
 * Generate custom url structure
 *
 * @param $wp_rewrite
 */
function resources_cpt_generating_rule($wp_rewrite) {
	$rules = array();
	$terms = get_terms( array(
		'taxonomy' => 'poses_tag',
		'hide_empty' => false,
	) );

	$post_type = 'pose';
	foreach ($terms as $term) {

		$rules[$term->slug . '/([^/]*)$'] = 'index.php?post_type=' . $post_type. '&pose=$matches[1]&name=$matches[1]';

	}
	// merge with global rules
	$wp_rewrite->rules = $rules + $wp_rewrite->rules;
}
add_filter('generate_rewrite_rules', 'resources_cpt_generating_rule');


/**
 * Change permalink structure for single photo
 *
 * @param $permalink
 * @param $post
 *
 * @return string
 */
function change_link( $permalink, $post ) {

	if ( 'pose' == $post->post_type ) {
		$resource_terms = get_the_terms( $post, 'poses_tag' );
		$term_slug      = '';
		if ( ! empty( $resource_terms ) ) {
			foreach ( $resource_terms as $term ) {
				if ( $term->slug == 'featured' ) {
					continue;
				}
				$term_slug = $term->slug;
				break;
			}
		}
		$permalink = get_home_url() . '/' . $term_slug . '/' . $post->post_name;
	}

	return $permalink;
}
add_filter('post_type_link',"change_link",10,2);

/*
function __custom_messagetypes_link( $link, $term, $taxonomy )
{
	if ( $taxonomy !== 'poses' )
		return $link;

	return str_replace( 'poses/', '', $link );
}
add_filter( 'term_link', '__custom_messagetypes_link', 100, 3 );
*/


/*
function rewrite_pose_category_url( $wp_rewrite ) {

	$feed_rules = array(
		'(.+)'    =>  'index.php?poses_tag='. $wp_rewrite->preg_index(1)
	);

	$wp_rewrite->rules = $wp_rewrite->rules + $feed_rules;
}
// refresh/flush permalinks in the dashboard if this is changed in any way
add_filter( 'generate_rewrite_rules', 'rewrite_pose_category_url' );
*/

function sort_yoga_by_date( $wp_query ) {
	if ( is_admin() ) {
		$post_type = $wp_query->query['post_type'];
		if ( $post_type == 'pose') {
			$wp_query->set('orderby', 'date');
			$wp_query->set('order', 'DESC');
		}
	}
}
add_filter('pre_get_posts', 'sort_yoga_by_date');


add_filter( 'wp_title', 'custom_uploads_title' );
function custom_uploads_title( $title ) {

	if ( 5 === get_the_ID() ) {
		$title = get_the_title( get_the_ID() );

		return $title;
	} else {
		return $title;
	}

}