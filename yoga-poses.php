<?php
/*
Plugin Name: Yoga Poses
Plugin URI: https://wordpress.org/
Description: Create on your site yoga poses boards like the Pinterest
Author: Mikhail Zhirov
Version: 1.0
Author URI: https://wordpress.org/
*/

require_once 'class-yoga-poses.php';
require_once 'hooks.php';
require_once 'helpers.php';
require_once 'vendor/tinymce/taxonomy-tinymce.php';
require_once 'vendor/image-rotation/image-rotation.php';
require_once 'vendor/easy-social-share-buttons3/easy-social-share-buttons3.php';
require_once 'vendor/remove-taxonomy-base-slug/remove-taxonomy-base-slug.php';
require_once 'vendor/permalink-manager/permalink-manager.php';


register_activation_hook( __FILE__, 'add_share_count_field' );
register_deactivation_hook( __FILE__, 'deactivation_yoga_plugin' );
register_uninstall_hook( __FILE__, 'uninstall_yoga_plugin' );

add_filter( 'theme_root_uri', 'change_theme_root_dir' );
add_filter( 'theme_root',     'change_theme_root_dir' );
add_action( 'admin_menu', 'add_yoga_options' );
add_action( 'admin_init', 'yoga_plugin_settings' );

add_action( 'after_setup_theme', 'yoga_get_set_popularity' );

/**
 * Add share count fields if not exests
 */
function add_share_count_field() {
	global $wpdb;
	$field_exists = false;

	// add field for posts
	$fields = $wpdb->get_results("SHOW fields FROM $wpdb->posts", ARRAY_A );
	foreach($fields as $field){
		if( $field['Field'] == 'share_count' ){
			$field_exists = true;
		}
	}
	if ( false === $field_exists ) {
		$query = "ALTER TABLE $wpdb->posts ADD share_count INT NOT NULL DEFAULT '0'";
		$wpdb->query($query);
	}

	$term_field_exists = false;

	// add field for terms
	$fields_term = $wpdb->get_results("SHOW fields FROM $wpdb->terms", ARRAY_A );
	foreach($fields_term as $field_term){
		if( $field_term['Field'] == 'all_share_count' ){
			$term_field_exists = true;
		}
	}
	if ( false === $term_field_exists ) {
		$query = "ALTER TABLE $wpdb->terms ADD all_share_count INT NOT NULL DEFAULT '0'";
		$wpdb->query($query);
	}
	
	// add field for terms
	$fields_term = $wpdb->get_results("SHOW fields FROM $wpdb->terms", ARRAY_A );
	foreach($fields_term as $field_term){
		if( $field_term['Field'] == 'cat_updated' ){
			$term_field_exists = true;
		}
	}
	if ( false === $term_field_exists ) {
		$query = "ALTER TABLE $wpdb->terms ADD cat_updated VARCHAR (255) NOT NULL DEFAULT '0'";
		$wpdb->query($query);
	}

	flush_rewrite_rules();
}

function deactivation_yoga_plugin(){
	return_back_std_themes_settings();
	flush_rewrite_rules();
}


/**
 * Removing share count fields if plugin removing
 */
function uninstall_yoga_plugin() {

	global $wpdb;
	$field_exists = false;

	// add field for posts
	$fields = $wpdb->get_results("SHOW fields FROM $wpdb->posts", ARRAY_A );
	foreach($fields as $field){
		if( $field['Field'] == 'share_count' ){
			$field_exists = true;
		}
	}
	if ( true === $field_exists ) {
		$query = "ALTER TABLE $wpdb->posts DROP COLUMN share_count";
		$wpdb->query($query);
	}

	$term_field_exists = false;

	// add field for terms
	$fields_term = $wpdb->get_results("SHOW fields FROM $wpdb->terms", ARRAY_A );
	foreach($fields_term as $field_term){
		if( $field_term['Field'] == 'all_share_count' ){
			$term_field_exists = true;
		}
	}
	if ( false === $term_field_exists ) {
		$query = "ALTER TABLE $wpdb->terms DROP COLUMN all_share_count";
		$wpdb->query($query);
	}
	
	// remove field for terms
	$fields_term = $wpdb->get_results("SHOW fields FROM $wpdb->terms", ARRAY_A );
	foreach($fields_term as $field_term){
		if( $field_term['Field'] == 'cat_updated' ){
			$term_field_exists = true;
		}
	}
	if ( false === $term_field_exists ) {
		$query = "ALTER TABLE $wpdb->terms DROP COLUMN cat_updated";
		$wpdb->query($query);
	}

	return_back_std_themes_settings();
	flush_rewrite_rules();
}


/**
 * Return back standard themes settings
 *
 * @return mixed|string
 */
function return_back_std_themes_settings() {

	$get_all_themes = wp_get_themes();

	$root_plugin_path = get_theme_root();
	$root_plugin_path = str_replace( 'plugins/yoga-poses/templates/', '', $root_plugin_path );
	$root_plugin_path .= 'themes/';

	register_theme_directory( $root_plugin_path );

	// activate first theme in list
	foreach ( $get_all_themes as $key => $one_theme ) {
		switch_theme( $key );
		break;
	}

	return $root_plugin_path;
}


// include media library button
add_action('admin_enqueue_scripts', function(){
	if ( ( isset( $_GET['page'] ) && "yoga-poses-option" == $_GET['page'] ) || ( isset( $_GET['taxonomy'] ) && "poses_tag" == $_GET['taxonomy'] ) ) {
		wp_enqueue_media();
		$ver = '1.0.0';
		$folder_url = plugin_dir_url( __FILE__ );
		wp_enqueue_script( 'yoga-admin-js', $folder_url . 'assets/js/admin-yoga.js', array('jquery'), $ver );
	}
});


/**
 * Update share count for yoga posts
 */
function yoga_get_set_popularity( $term ) {

	if ( is_singular() && 'pose' == get_post_type( get_the_ID() ) ) {

		global $wpdb;
		$json        = file_get_contents( 'https://graph.facebook.com/?id=' . get_the_permalink( get_the_ID() ) );
		$obj         = json_decode( $json );
		$share_count = $obj->share->share_count;

		$json_pinterest = file_get_contents( 'https://api.pinterest.com/v1/urls/count.json?url=' . get_the_permalink( get_the_ID() ) );
		$json_pinterest = str_replace( 'receiveCount(', '', $json_pinterest );
		$json_pinterest = str_replace( ')', '', $json_pinterest );
		$obj_pinterest  = json_decode( $json_pinterest );
		$share_count    += (int)$obj_pinterest->count;

		$wpdb->update(
			$wpdb->posts,
			array( 'share_count' => $share_count ),
			array( 'ID' => get_the_ID() )
		);

		$term_posts = $wpdb->get_results(
				"SELECT $wpdb->posts.ID, $wpdb->posts.share_count
						FROM $wpdb->posts
						LEFT JOIN $wpdb->term_relationships
						ON $wpdb->term_relationships.term_taxonomy_id = $term->term_id
						WHERE $wpdb->term_relationships.object_id = $wpdb->posts.ID  
						AND $wpdb->posts.post_status = 'publish'
						AND $wpdb->posts.post_type = 'pose' " );
		$counter = 0;
		foreach ( $term_posts as $one_post ) {
			$counter += (int)$one_post->share_count;
		}

		$wpdb->update(
			$wpdb->terms,
			array( 'all_share_count' => $counter ),
			array( 'term_id' => $term->term_id )
		);

	}

}


/**
 * Change theme directory location
 *
 * @return string
 */
function change_theme_root_dir(){
	$new_root = dirname( __FILE__ ) . '/templates/';
	register_theme_directory( $new_root );
	return $new_root;
}

// turn on custom yoga theme after the plugin activation
switch_theme('yoga');

// create plugin main object
if ( class_exists( 'Yoga_Poses' ) ) {
	new Yoga_Poses();
}

/**
 * Init creation of yoga options page
 */
function add_yoga_options(){
	add_menu_page( 'Yoga Poses Options', 'Yoga Options', 'manage_options', 'yoga-poses-option', 'yoga_options_page', '', 25 );
}

/**
 * Fields settings
 */
function yoga_plugin_settings() {
	register_setting( 'yoga-settings-array', 'home_title' );
	register_setting( 'yoga-settings-array', 'home_subtitle' );
	register_setting( 'yoga-settings-array', 'home_description' );
	register_setting( 'yoga-settings-array', 'home_bg_logo' );
	register_setting( 'yoga-settings-array', 'home_mob_bg_logo' );
	register_setting( 'yoga-settings-array', 'logo_text' );
	register_setting( 'yoga-settings-array', 'upload_photo_text' );
	register_setting( 'yoga-settings-array', 'footer_copy' );
	register_setting( 'yoga-settings-array', 'privacy_link' );
	register_setting( 'yoga-settings-array', 'terms_link' );
	register_setting( 'yoga-settings-array', 'google_api_key' );
	register_setting( 'yoga-settings-array', 'facebook_app_id' );
	register_setting( 'yoga-settings-array', 'facebook_comments_to_show' );
	register_setting( 'yoga-settings-array', 'facebook_comments_width' );

	register_setting( 'yoga-settings-array', 'instagram_link' );
	register_setting( 'yoga-settings-array', 'facebook_link' );
	register_setting( 'yoga-settings-array', 'twitter_link' );
	register_setting( 'yoga-settings-array', 'pinterest_link' );

	register_setting( 'yoga-settings-array', 're_public_key' );
	register_setting( 'yoga-settings-array', 're_private_key' );
	register_setting( 'yoga-settings-array', 'soc_shortcode' );
}

/**
 * Create plugin options page
 */
function yoga_options_page() { ?>
	<div class="wrap">
		<h1>Yoga Poses options</h1>

		<form method="post" action="options.php">
		    <?php settings_fields( 'yoga-settings-array' ); ?>
		    <?php do_settings_sections( 'yoga-settings-array' ); ?>
		    <table class="form-table">
				<tr>
					<td>
						<h3>Header options</h3>
						<hr>
					</td>
				</tr>
			    <tr valign="top">
				    <th scope="row">
						Logo text
					</th>
				    <td><input type="text" size="80" name="logo_text" value="<?php echo esc_attr( get_option('logo_text') ); ?>" /></td>
			    </tr>
			    <tr valign="top">
				    <th scope="row">Upload photo button text</th>
				    <td><input type="text" size="80" name="upload_photo_text" value="<?php echo esc_attr( get_option('upload_photo_text') ); ?>" /></td>
			    </tr>
				<tr>
					<td>
						<h3>API options</h3>
						<hr>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">Google map API key (for address autocomplete)</th>
					<td><input type="text" size="80" name="google_api_key" value="<?php echo esc_attr( get_option('google_api_key') ); ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row">Facebook comments App ID</th>
					<td><input type="text" size="80" name="facebook_app_id" value="<?php echo esc_attr( get_option('facebook_app_id') ); ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row">Facebook comments to show</th>
					<td><input type="text" size="80" name="facebook_comments_to_show" value="<?php echo esc_attr( get_option('facebook_comments_to_show') ); ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row">Facebook comments form width (px)</th>
					<td><input type="text" size="80" name="facebook_comments_width" value="<?php echo esc_attr( get_option('facebook_comments_width') ); ?>" /></td>
				</tr>
				<tr>
					<td>
						<h3>Home page options</h3>
						<hr>
					</td>
				</tr>
		        <tr valign="top">
		            <th scope="row">Title</th>
		            <td><input type="text" size="80" name="home_title" value="<?php echo esc_attr( get_option('home_title') ); ?>" /></td>
		        </tr>
			    <tr valign="top">
				    <th scope="row">Subtitle</th>
				    <td><input type="text" size="80" name="home_subtitle" value="<?php echo esc_attr( get_option('home_subtitle') ); ?>" /></td>
			    </tr>
			    <tr valign="top">
				    <th scope="row">Description</th>
				    <td><textarea cols="80" rows="10" name="home_description" value="" /><?php echo get_option('home_description'); ?></textarea></td>
			    </tr>
			    <tr valign="top">
				    <th scope="row">Top desktop background image</th>
				    <td>
					    <?php $bg_img = get_option( 'home_bg_logo' ); ?>
					    <img class="img_home_bg_logo" src="<?php echo ( $bg_img ) ? esc_attr( $bg_img ) : ''; ?>" style="max-width: 700px">
					    <input type="hidden" size="80" class="home_bg_logo" name="home_bg_logo" value="<?php echo esc_attr( get_option('home_bg_logo') ); ?>" />
					    <button class="button bg_yoga_upload">Upload</button>
					    <button class="button bg_yoga_remove">Remove</button>
				    </td>
			    </tr>
			    <tr valign="top">
				    <th scope="row">Top mobile background image</th>
				    <td>
					    <?php $bg_mob_img = get_option( 'home_mob_bg_logo' ); ?>
					    <img class="img_home_mob_bg_logo" src="<?php echo ( $bg_mob_img ) ? esc_attr( $bg_mob_img ) : ''; ?>" style="max-width: 700px">
					    <input type="hidden" size="80" class="home_mob_bg_logo" name="home_mob_bg_logo" value="<?php echo esc_attr( get_option('home_mob_bg_logo') ); ?>" />
					    <button class="button bg_mob_yoga_upload">Upload</button>
					    <button class="button bg_mob_yoga_remove">Remove</button>
				    </td>
			    </tr>

				<tr>
					<td>
						<h3>Invisible reCAPTCHA</h3>
						<hr>
					</td>
				</tr>
			    <tr valign="top">
				    <th scope="row">reCAPTCHA public key</th>
				    <td><input type="text" size="80" name="re_public_key" value="<?php echo esc_attr( get_option('re_public_key') ); ?>" /></td>
			    </tr>
				<tr valign="top">
				    <th scope="row">reCAPTCHA private key</th>
				    <td><input type="text" size="80" name="re_private_key" value="<?php echo esc_attr( get_option('re_private_key') ); ?>" /></td>
			    </tr>

				<tr>
					<td>
						<h3>Footer options</h3>
						<hr>
					</td>
				</tr>
			    <tr valign="top">
				    <th scope="row">Footer copyright text</th>
				    <td><input type="text" size="80" name="footer_copy" value="<?php echo esc_attr( get_option('footer_copy') ); ?>" /></td>
			    </tr>
			    <tr valign="top">
				    <th scope="row">Privacy Policy link</th>
				    <td><input type="text" size="80" name="privacy_link" value="<?php echo esc_attr( get_option('privacy_link') ); ?>" /></td>
			    </tr>
			    <tr valign="top">
				    <th scope="row">Terms of Service link</th>
				    <td><input type="text" size="80" name="terms_link" value="<?php echo esc_attr( get_option('terms_link') ); ?>" /></td>
			    </tr>

				<tr>
					<td>
						<h3>Social share shortcode</h3>
						<hr>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">Shortcode</th>
					<td><input type="text" size="80" name="soc_shortcode" value="<?php echo esc_attr( get_option('soc_shortcode') ); ?>" /></td>
				</tr>

				<tr>
					<td>
						<h3>Social pages links</h3>
						<hr>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">Instagram link</th>
					<td><input type="text" size="80" name="instagram_link" value="<?php echo esc_attr( get_option('instagram_link') ); ?>" /></td>
				</tr>

				<tr valign="top">
					<th scope="row">Facebook link</th>
					<td><input type="text" size="80" name="facebook_link" value="<?php echo esc_attr( get_option('facebook_link') ); ?>" /></td>
				</tr>

				<tr valign="top">
					<th scope="row">Twitter link</th>
					<td><input type="text" size="80" name="twitter_link" value="<?php echo esc_attr( get_option('twitter_link') ); ?>" /></td>
				</tr>

				<tr valign="top">
					<th scope="row">Pinterest link</th>
					<td><input type="text" size="80" name="pinterest_link" value="<?php echo esc_attr( get_option('pinterest_link') ); ?>" /></td>
				</tr>

		    </table>

		    <?php submit_button(); ?>

		</form>
	</div>
<?php
}