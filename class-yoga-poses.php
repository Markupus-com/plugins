<?php

/**
 * Class Yoga_Poses
 */
class Yoga_Poses{

	/**
	 * Yoga_Poses constructor.
	 */
	public function __construct() {

		$this->_register_yoga_cpt();
		$this->_create_upload_photo_page();

		// add sanscrit name field
		add_action( 'poses_tag_edit_form_fields', array( $this, 'poses_tag_edit_form_fields' ) );
		add_action( 'poses_tag_edit_form', array( $this, 'poses_tag_edit_form' ) );
		add_action( 'poses_tag_add_form_fields', array( $this, 'poses_tag_edit_form_fields' ) );
		add_action( 'poses_tag_add_form', array( $this, 'poses_tag_edit_form' ) );
		add_action( "create_poses_tag", array( $this, 'save_custom_poses_tag' ) );
		add_action( "edited_poses_tag", array( $this, 'save_custom_poses_tag' ) );

		// add metabox for additional fields
		add_action('add_meta_boxes', function (){
			add_meta_box('pose_custom_fields_mb', 'Additional fields', array( $this, 'add_pose_custom_fields' ), array('pose'), 'normal', 'high');
		} );

		// save meta fields when post saving
		add_action( 'save_post', array( $this, 'save_post_meta_fields' ) );

		// create custom meta data
		add_action( 'wp_head', array( $this, 'generate_custom_meta_fields' ) , 2 );

		// create custom titles
		add_filter( 'wp_title', array( $this, 'override_pages_titles' ), 10, 2 );
	}


	/**
	 * Register main custom post type Pose and Taxnomy poses_tag
	 */
	private function _register_yoga_cpt() {

		add_action('init', function() {
			register_post_type('pose', array(
				'label'  => null,
				'labels' => array(
					'name'               => 'Poses',
					'singular_name'      => 'Pose',
					'add_new'            => 'Add pose',
					'add_new_item'       => 'Adding pose',
					'edit_item'          => 'Edit pose',
					'new_item'           => 'New pose',
					'view_item'          => 'Look',
					'search_items'       => 'Search poses',
					'not_found'          => 'Not found',
					'not_found_in_trash' => 'Not found in the trash',
					'parent_item_colon'  => '',
					'menu_name'          => 'Yoga Poses',
				),
				'public'              => true,
				'hierarchical'        => true,
				'supports'            => array( 'thumbnail', 'title', 'editor', 'revisions' ),
				'taxonomies'          => array( 'poses_tag' ),
				'has_archive'         => false,
				'rewrite'             => true
			) );
		} );

		// create yoga taxonomy
		add_action('init', function (){
			register_taxonomy('poses_tag', array('pose'), array(
				'label'                 => 'Poses tags',
				'labels'                => array(
					'name'              => 'Poses tags',
					'singular_name'     => 'Pose tag',
					'search_items'      => 'Search Pose tag',
					'all_items'         => 'All Poses tags',
					'view_item '        => 'View Pose tag',
					'parent_item'       => 'Parent Pose tag',
					'parent_item_colon' => 'Parent Pose tag:',
					'edit_item'         => 'Edit Pose tag',
					'update_item'       => 'Update Pose tag',
					'add_new_item'      => 'Add New Pose tag',
					'new_item_name'     => 'New Pose tag Name',
					'menu_name'         => 'Poses tags',
				),
				'public'                => true,
				'hierarchical'          => true,
				'query_var'    => true,
				'rewrite' => array( 'slug' => '.', 'with_front' => false )
				//'rewrite'      => array('slug' => '.', 'with_front' => false)
			) );
		});

		// create default category
		add_action( 'init', array($this, 'create_poses_default_category'), 20 );
	}


	/**
	 * Default category for poses
	 */
	function create_poses_default_category() {
		if( !term_exists( 'Yoga Pose', 'poses_tag' ) ) {
			wp_insert_term(
				'Yoga Pose',
				'poses_tag',
				array(
					'description' => 'Default category for poses, if uer forget pose name',
					'slug'        => 'default'
				)
			);
		}
	}


	/**
	 * Modify tag admin page
	 */
	public function poses_tag_edit_form() {
		?>
		<script type="text/javascript">
			jQuery(document).ready(function(){
				jQuery('#edittag').attr( "enctype", "multipart/form-data" ).attr( "encoding", "multipart/form-data" );
			});
		</script>
		<?php
	}


	/**
	 * Add to admin pose tag page custom fields
	 *
	 * @param $term
	 */
	public function poses_tag_edit_form_fields ( $term ) {
		?>
		<tr class="form-field sanscrit-tag-name">
			<th valign="top" scope="row">
				<label for="catpic"><?php _e('Sanscrit Name', ''); ?></label>
			</th>
			<td>
				<?php
				if ( is_object( $term ) ) {
					$term_name = get_term_meta( $term->term_id, 'sanscrit_tag_name', 1 );
				} else {
					$term_name = '';
				}
				?>
				<input type="text" id="sanscrit_tag_name" name="sanscrit_tag_name" value="<?php echo ( $term_name ) ? $term_name : ''; ?>"/>
				<p class="description"><?php _e('Sanscrit name of current pose', ''); ?></p>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row">Choose pose board image</th>
			<td>
				<?php
				if ( is_object( $term ) ) {
					$pose_cat_image_input = get_term_meta( $term->term_id, 'pose_cat_image_input', 1 );
				?>
					<img class="pose_cat_image" src="<?php echo ( $pose_cat_image_input ) ? esc_attr( $pose_cat_image_input ) : ''; ?>" style="max-width: 700px">
				<?php } ?>
				<input type="hidden" size="80" class="pose_cat_image_input" name="pose_cat_image_input" value="<?php echo esc_attr( get_option('pose_cat_image') ); ?>" />
				<button class="button pose_cat_image_upload">Upload</button>
				<button class="button pose_cat_image_remove">Remove</button>
			</td>
		</tr>
		<?php
	}

	/**
	 * Saving custom fields
	 *
	 * @param $term_id
	 */
	public function save_custom_poses_tag($term_id) {

		if ( !isset( $_POST['sanscrit_tag_name'] ) || !isset( $_POST['pose_cat_image_input'] ) )  return;

		$sanscrit_tag_name = sanitize_text_field($_POST['sanscrit_tag_name']);
		$pose_cat_image_input = sanitize_text_field($_POST['pose_cat_image_input']);

		update_term_meta( $term_id, 'sanscrit_tag_name', $sanscrit_tag_name );
		update_term_meta( $term_id, 'pose_cat_image_input', $pose_cat_image_input );
	}

	/**
	 * Programmatically creating Upload photo page
	 */
	protected function _create_upload_photo_page() {

		$page_title        = 'Upload your photo';
		$page_content      = '';
		$new_page_template = '';

		$page_check = get_page_by_title( $page_title );
		$new_page   = array(
			'post_type'    => 'page',
			'post_title'   => $page_title,
			'post_content' => $page_content,
			'post_status'  => 'publish',
			'post_author'  => 1,
		);

		if ( ! isset( $page_check->ID ) ) {
			$new_page_id = wp_insert_post( $new_page );
			if ( ! empty( $new_page_template ) ) {
				update_post_meta( $new_page_id, '_wp_page_template', $new_page_template );
			}
		}

	}


	/**
	 * Add pose post meta fields
	 *
	 * @param $post
	 * @param $meta
	 */
	public function add_pose_custom_fields( $post, $meta ) {

		// verification
		wp_nonce_field( plugin_basename(__FILE__), 'yoga-poses' );

		$where_photo = get_post_meta( $post->ID, 'where_photo', true );
		$when_is_photo = get_post_meta( $post->ID, 'when_is_photo', true );
		$your_email = get_post_meta( $post->ID, 'your_email', true );
		$instagram_username = get_post_meta( $post->ID, 'instagram_username', true );
		$photographer_name = get_post_meta( $post->ID, 'photographer_name', true );
		$video_link = get_post_meta( $post->ID, 'video_link', true );

		ob_start();
		echo '<label for="where_photo">' . __("Where was this photo taken?", 'yoga' ) . '</label><br> ';
		echo '<input type="text" id= "where_photo" name="where_photo" value="'. $where_photo .'" size="60" /><br><br>';

		echo '<label for="when_is_photo">' . __("When was it taken?", 'yoga' ) . '</label> <br>';
		echo '<input type="text" id= "when_is_photo" name="when_is_photo" value="'. $when_is_photo .'" size="60" /><br><br>';

		echo '<label for="your_email">' . __("User email", 'yoga' ) . '</label> <br>';
		echo '<input type="text" id= "your_email" name="your_email" value="'. $your_email .'" size="60" /><br><br>';

		echo '<label for="instagram_username">' . __("Instagram Username", 'yoga' ) . '</label> <br>';
		echo '<input type="text" id= "instagram_username" name="instagram_username" value="'. $instagram_username .'" size="60" /><br><br>';

		echo '<label for="photographer_name">' . __("Photographer name", 'yoga' ) . '</label> <br>';
		echo '<input type="text" id= "photographer_name" name="photographer_name" value="'. $photographer_name .'" size="60" /><br><br>';

		echo '<label for="photographer_name">' . __("Link to video", 'yoga' ) . '</label> <br>';
		echo '<input type="text" id= "video_link" name="video_link" value="'. $video_link .'" size="80" /><br><br>';
		$custom_fields = ob_get_contents();
		ob_end_clean();

		echo $custom_fields;
	}

	/**
	 * Update post meta fields
	 *
	 * @param $post_id
	 */
	public function save_post_meta_fields( $post_id ) {

		if ( ! isset( $_POST['your_email'] )
		     && ! isset( $_POST['photographer_name'] )
		     && ! isset( $_POST['where_photo'] )
		     && ! isset( $_POST['when_is_photo'] )
		     && ! isset( $_POST['instagram_username'] )
		) {

			return;
		}

		// check nonce of our plugin
		if ( ! wp_verify_nonce( $_POST['yoga-poses'], plugin_basename(__FILE__) ) )
			return;

		// if autosaving do nothing
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )
			return;

		// check user right
		if( ! current_user_can( 'edit_post', $post_id ) )
			return;

		$photographer_name  = sanitize_text_field( $_POST['photographer_name'] );
		$where_photo        = sanitize_text_field( $_POST['where_photo'] );
		$when_is_photo      = sanitize_text_field( $_POST['when_is_photo'] );
		$instagram_username = sanitize_text_field( $_POST['instagram_username'] );
		$your_email         = sanitize_text_field( $_POST['your_email'] );
		$video_link         = sanitize_text_field( $_POST['video_link'] );

		// update fields
		update_post_meta( $post_id, 'photographer_name', $photographer_name );
		update_post_meta( $post_id, 'where_photo', $where_photo );
		update_post_meta( $post_id, 'when_is_photo', $when_is_photo );
		update_post_meta( $post_id, 'instagram_username', $instagram_username );
		update_post_meta( $post_id, 'your_email', $your_email );
		update_post_meta( $post_id, 'video_link', $video_link );
	}


	/**
	 * Override standard pages, categories meta keywords and description tags
	 */
	public function generate_custom_meta_fields(){

		global $post;

		if ( is_single() ) {
			$instagram_username = get_post_meta( get_the_ID(), 'instagram_username', 1 );
			$current_term       = wp_get_post_terms( get_queried_object()->ID, 'poses_tag', array( 'hide_empty' => true ) )[0];
			$sanscrit_name      = get_term_meta( $current_term->term_id, 'sanscrit_tag_name', 1 );
			$meta               = strip_tags( $post->post_content );
			$meta               = strip_shortcodes( $meta );
			$meta               = str_replace( array( "\n", "\r", "\t" ), ' ', $meta );
			$meta               = substr( $meta, 0, 125 );
			$pose               = $current_term->name . ' — ' . $sanscrit_name;
			$pose_key           = $current_term->name . ', ' . $sanscrit_name;

			echo '<meta name="description" content="' . $pose . ' — by ' . $instagram_username . '. ' . $meta . '" />' . "\n";
			echo '<meta name="keywords" content="' . $pose_key . ', Yoga Poses, Yoga practice, ' . $instagram_username . '" />' . "\n";
		}

		if ( is_front_page() ) {
			$home_description = get_option( 'home_description' );
			echo '<meta name="description" content="' . $home_description . '" />' . "\n";
			echo '<meta name="keywords" content="Yoga Poses, Yoga practice" />' . "\n";
		}

		if ( is_archive() ) {
			$current_term  = get_queried_object();
			$sanscrit_name = get_term_meta( $current_term->term_id, 'sanscrit_tag_name', 1 );
			$pose_key      = $current_term->name . ', ' . $sanscrit_name;
			$pose          = $current_term->name . ' — ' . $sanscrit_name;

			$all_text = preg_replace( '/<ol.*?>(.*?)<\/ol>/s', '', $current_term->description );
			$all_text = strip_tags( $all_text );

			echo '<meta name="description" content="' . $pose . '. ' . $all_text . '" />' . "\n";
			echo '<meta name="keywords" content="' . $pose_key . ', Yoga Poses, Yoga practice' . '" />' . "\n";
		}
	}


	/**
	 * Override standard pages, categories titles
	 *
	 * @return string
	 */
	public function override_pages_titles() {

		if ( is_single() ) {
			global $post;
			$instagram_username = get_post_meta( get_the_ID(), 'instagram_username', 1 );
			$current_term       = wp_get_post_terms( get_queried_object()->ID, 'poses_tag', array( 'hide_empty' => true ) )[0];
			$sanscrit_name      = get_term_meta( $current_term->term_id, 'sanscrit_tag_name', 1 );
			$pose               = $current_term->name . ' — ' . $sanscrit_name;

			return $pose . ' by ' . $instagram_username . ' | Yoga Poses | Yoga practice';
		}

		if ( is_front_page() ) {
			return 'Yoga Poses | Yoga practice';
		}

		if ( is_archive() ) {
			$current_term  = get_queried_object();
			$sanscrit_name = get_term_meta( $current_term->term_id, 'sanscrit_tag_name', 1 );
			$pose          = $current_term->name . ' — ' . $sanscrit_name;

			return $pose . ' | Yoga Poses | Yoga practice';
		}

	}

}
