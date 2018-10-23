<?php

/**
 * Get post yoga attachments images id's
 *
 * @param $term_id
 *
 * @return array|bool
 */
function get_images_id_by_term_id( $term_id ) {

	if ( !empty( $term_id ) ) {

		$posts = get_posts(array(
			'post_type' => 'pose',
			'numberposts' => -1,
			'tax_query' => array(
				array(
					'taxonomy' => 'poses_tag',
					'field' => 'id',
					'terms' => $term_id,
					'include_children' => false
				)
			)
		));

		$images_attachments = array();

		foreach ( $posts as $one_post  ) {
			$images_attachments[] = get_post_thumbnail_id( $one_post->ID );
		}

		return $images_attachments;

	} else {
		return false;
	}

}


/**
 * Print all boards by terms list (using on the pages and ajax)
 *
 * @param $tax_terms
 *
 * @return string
 */
function print_boards_by_terms_list( $tax_terms ) { ?>

	<?php ob_start(); ?>

	<?php foreach( $tax_terms as $one_term ) : ?>
		<?php
		$images_ids = get_images_id_by_term_id( $one_term->term_id );
		?>
	<?php $term_link = get_term_link( (int)$one_term->term_id ); ?>
	<?php
		// if one image in the board - add some class
		if ( 1 == count(  $images_ids) ) {
			$add_class = 'board__item--single';
		} else {
			$add_class = '';
		}
		?>
		<a href="<?php echo $term_link; ?>" class="board__item <?php echo $add_class; ?>">
			<div class="board__image image">
				<div class="image__top">
					<?php if ( !empty( $images_ids ) ) : ?>
						<?php echo wp_get_attachment_image( $images_ids[0], 'board-thumb', false ); ?>
					<?php endif; ?>
					<div class="image__top-overlay overlay">
						<div class="overlay__count"><?php echo count(  $images_ids); ?></div>
						<div class="overlay__value"><?php echo __( 'photos', 'yoga' ); ?></div>
					</div>
				</div>
				<div class="image__bottom">
					<?php foreach( $images_ids as $key => $one_id ) : ?>
						<?php if ( 0 == $key ) {
							continue;
						} elseif ( 4 == $key ) {
							break;
						} ?>
						<div class="image__bottom-item">
							<?php echo wp_get_attachment_image( $one_id, 'board-images-list', false ); ?>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
			<div class="board__body">
				<div class="board__title title"><?php echo esc_html( $one_term->name ); ?></div>
				<?php $sans_name = get_term_meta( $one_term->term_id, 'sanscrit_tag_name', 1 ); ?>
				<?php if ( $sans_name ) : ?>
					<div class="board__subtitle subtitle"><?php echo esc_html( $sans_name ); ?></div>
				<?php endif; ?>
			</div>
		</a>
	<?php endforeach; ?>

	<?php
	 $terms_data = ob_get_contents();
	 ob_end_clean();

	 return $terms_data;
}


/**
 * Print all posts by wp_query object (using on the pages and ajax)
 *
 * @param $query_obj
 * @param $term_name
 *
 * @return string
 */
function print_posts_by_wp_query_obj( $query_obj, $term_name = '' ) { ?>

	<?php ob_start(); ?>
	<?php foreach( $query_obj as $one_post ) : ?>
		<?php
		$image_id = get_post_thumbnail_id( $one_post->ID );
		?>
		<?php
		$video_link = get_post_meta( $one_post->ID, 'video_link', 1 );
		?>
		<?php if ( strlen( $video_link ) < 3 ) : ?>
		<a href="<?php echo get_the_permalink( $one_post->ID ) ?>" class="grid__item">
			<div class="grid__img">
				<?php $item_pinterest_title = get_pinterest_shared_string( get_the_ID() ); ?>
				<?php echo wp_get_attachment_image( $image_id, 'inside-board-image-list', false, array( 'alt' => $item_pinterest_title ) ); ?>
				<div class="overlay-icon">
					<div class="icon-wrap">
						<span class="icon__image">
							<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 28 28" id="pinterest-single">
								<circle cx="14" cy="14" r="14" fill="currentColor"></circle>
								<path fill="currentColor" d="M14 2C7.375 2 2 7.372 2 14c0 4.913 2.955 9.135 7.184 10.991-.034-.837-.005-1.844.208-2.756l1.544-6.538s-.383-.766-.383-1.9c0-1.778 1.032-3.106 2.315-3.106 1.09 0 1.618.82 1.618 1.803 0 1.096-.7 2.737-1.06 4.257-.3 1.274.638 2.312 1.894 2.312 2.274 0 3.805-2.92 3.805-6.38 0-2.63-1.771-4.598-4.993-4.598-3.64 0-5.907 2.714-5.907 5.745 0 1.047.307 1.784.79 2.354.223.264.253.368.172.67-.056.219-.189.752-.244.963-.08.303-.326.413-.6.3-1.678-.684-2.458-2.52-2.458-4.585 0-3.408 2.875-7.497 8.576-7.497 4.582 0 7.598 3.317 7.598 6.875 0 4.708-2.617 8.224-6.476 8.224-1.294 0-2.514-.7-2.931-1.494 0 0-.698 2.764-.844 3.298-.254.924-.752 1.85-1.208 2.57 1.08.318 2.22.492 3.4.492 6.628 0 12-5.372 12-12S20.628 2 14 2"></path>
							</svg>
						</span>
						<span class="icon__text"><?php echo __( 'Save', 'yoga' ); ?></span>
					</div>
				</div>
			</div>
			<div class="grid__body">
				<?php if ( empty( $term_name ) ) {
					$term_name = wp_get_post_terms( $one_post->ID, 'poses_tag' )[0];
				} ?>
				<?php if ( is_object( $term_name ) ) : ?>
					<div class="grid__title title"><?php echo esc_html( $term_name->name ); ?></div>
				<?php else: ?>
					<div class="grid__title title"><?php echo esc_html( $term_name ); ?></div>
				<?php endif; ?>
				<?php 
					$instagram_username = get_post_meta( $one_post->ID, 'instagram_username', true );
				?>
				<?php if ( $instagram_username ) : ?>
				<div class="grid__subtitle subtitle"><?php echo esc_html( $instagram_username ); ?></div>
				<?php endif; ?>
			</div>
		</a>
		<?php else: ?>

		<a href="<?php echo get_the_permalink( $one_post->ID ) ?>" class="grid__item grid__item--video">
			<div class="grid__img">
				<?php $item_pinterest_title = get_pinterest_shared_string( get_the_ID() ); ?>
				<?php echo wp_get_attachment_image( $image_id, 'board-images-list', false, array( 'alt' => $item_pinterest_title ) ); ?>
				<div class="overlay-icon">
					<div class="icon-wrap">
						<span class="icon__image">
							<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 28 28" id="pinterest-single">
								<circle cx="14" cy="14" r="14" fill="currentColor"/>
								<path fill="currentColor" d="M14 2C7.375 2 2 7.372 2 14c0 4.913 2.955 9.135 7.184 10.991-.034-.837-.005-1.844.208-2.756l1.544-6.538s-.383-.766-.383-1.9c0-1.778 1.032-3.106 2.315-3.106 1.09 0 1.618.82 1.618 1.803 0 1.096-.7 2.737-1.06 4.257-.3 1.274.638 2.312 1.894 2.312 2.274 0 3.805-2.92 3.805-6.38 0-2.63-1.771-4.598-4.993-4.598-3.64 0-5.907 2.714-5.907 5.745 0 1.047.307 1.784.79 2.354.223.264.253.368.172.67-.056.219-.189.752-.244.963-.08.303-.326.413-.6.3-1.678-.684-2.458-2.52-2.458-4.585 0-3.408 2.875-7.497 8.576-7.497 4.582 0 7.598 3.317 7.598 6.875 0 4.708-2.617 8.224-6.476 8.224-1.294 0-2.514-.7-2.931-1.494 0 0-.698 2.764-.844 3.298-.254.924-.752 1.85-1.208 2.57 1.08.318 2.22.492 3.4.492 6.628 0 12-5.372 12-12S20.628 2 14 2"/>
							</svg>
						</span>
						<span class="icon__text"><?php echo __( 'Save', 'yoga' ); ?></span>
					</div>
				</div>
				<div class="overlay-video">
					<svg class="icon__play" width="1em" height="1em">
						<use xlink:href="#play"></use>
					</svg>
				</div>
			</div>
			<div class="grid__body">
				<?php if ( empty( $term_name ) ) {
					$term_name = wp_get_post_terms( $one_post->ID, 'poses_tag' )[0];
				} ?>
				<?php if ( is_object( $term_name ) ) : ?>
					<div class="grid__title title"><?php echo esc_html( $term_name->name ); ?></div>
				<?php else: ?>
					<div class="grid__title title"><?php echo esc_html( $term_name ); ?></div>
				<?php endif; ?>
				<?php 
					$instagram_username = get_post_meta( $one_post->ID, 'instagram_username', true );
				?>
				<?php if ( $instagram_username ) : ?>
				<div class="grid__subtitle subtitle"><?php echo esc_html( $instagram_username ); ?></div>
				<?php endif; ?>
			</div>
		</a>
		<?php endif; ?>


	<?php endforeach; ?>
	<?php
	$posts_data = ob_get_contents();
	ob_end_clean();
	?>

	<?php
	return $posts_data;
}


/**
 * Get boards with ordering
 *
 * @param $per_page
 * @param $offset
 * @param $order_by
 *
 * @return array|null|object
 */
function get_boards( $per_page, $offset, $order_by ) {

	if ( 'english' == $order_by ) {
		$orderby = 'name';
		$ord = 'ASC';
	} elseif( 'date' == $order_by ) {
		$orderby = 'cat_updated';
		$ord = 'DESC';
	} elseif( 'sanscrit' == $order_by ) {
		$orderby = 'meta_value';
		$ord = 'ASC';
	} elseif( 'popularity' == $order_by ) {
		$orderby = 'all_share_count';
		$ord = 'DESC';
	}

	global $wpdb;

	$sql = get_all_boards_sql() . " ORDER BY `$orderby` $ord LIMIT $per_page OFFSET $offset";

	$results = $wpdb->get_results($sql);

	return $results;
}


/**
 * Get all boards count SQL query
 *
 * @return string
 */
function get_all_boards_sql () {

	global $wpdb;

	return $sql = "SELECT $wpdb->terms.`name`, $wpdb->termmeta.`meta_value`, $wpdb->termmeta.`meta_key`, $wpdb->terms.`term_id`, $wpdb->terms.`all_share_count`, $wpdb->terms.`cat_updated`, $wpdb->term_taxonomy.`taxonomy`, COUNT($wpdb->posts.`ID`) as posts_count
		FROM $wpdb->terms 
		LEFT JOIN $wpdb->termmeta 
		ON $wpdb->terms.`term_id` = $wpdb->termmeta.`term_id`
		LEFT JOIN $wpdb->term_taxonomy
		ON $wpdb->term_taxonomy.`term_id` = $wpdb->terms.`term_id`
		LEFT JOIN $wpdb->term_relationships
		ON $wpdb->term_relationships.`term_taxonomy_id` = $wpdb->termmeta.`term_id`
		LEFT JOIN $wpdb->posts
		ON $wpdb->posts.`ID` = $wpdb->term_relationships.`object_id`
		WHERE $wpdb->term_taxonomy.`taxonomy` = 'poses_tag' AND $wpdb->terms.`name` NOT LIKE 'Yoga Pose' AND ( $wpdb->termmeta.`meta_value` NOT LIKE '%://%' OR $wpdb->termmeta.`meta_key` = 'all_term_share_count' )
		GROUP BY $wpdb->terms.`name`
		HAVING COUNT($wpdb->posts.`ID`) > 0
		";
}


/**
 * Get posts from particular board by current term id
 *
 * @param $per_page
 * @param $offset
 * @param $current_term_id
 *
 * @return array|null|object
 */
function get_posts_inside_boards ( $per_page, $offset, $current_term_id ) {
	global $wpdb;
	$sql = get_posts_boards_sql( $current_term_id ) . " DESC LIMIT $per_page OFFSET $offset";
	$results = $wpdb->get_results($sql);

	return $results;
}


/**
 * Get post ib the board sql, for loading and ajax
 *
 * @param $current_term_id
 *
 * @return string
 */
function get_posts_boards_sql( $current_term_id ){
	global $wpdb;
	return $sql = "
			SELECT $wpdb->posts.ID, $wpdb->posts.post_title, $wpdb->posts.share_count
			FROM $wpdb->posts WHERE $wpdb->posts.ID IN ( SELECT `object_id`  FROM `$wpdb->term_relationships` WHERE `term_taxonomy_id` = $current_term_id )
			AND $wpdb->posts.post_status = 'publish'
			AND $wpdb->posts.post_type = 'pose'
			GROUP BY $wpdb->posts.ID
			ORDER BY $wpdb->posts.share_count
			";
}


/**
 * Get search page sql, for loading and ajax
 *
 * @param $search
 *
 * @return string
 */
function get_search_posts_sql( $search ) {
	global $wpdb;
	return $sql = "SELECT $wpdb->posts.ID, $wpdb->posts.post_title, $wpdb->terms.term_id, $wpdb->terms.name, $wpdb->termmeta.meta_value
					FROM $wpdb->posts 
					LEFT JOIN $wpdb->term_relationships 
					ON $wpdb->posts.ID = $wpdb->term_relationships.object_id
					LEFT JOIN $wpdb->terms
					ON $wpdb->term_relationships.term_taxonomy_id = $wpdb->terms.term_id
					LEFT JOIN $wpdb->termmeta 
					ON $wpdb->terms.term_id = $wpdb->termmeta.term_id
					WHERE ( $wpdb->posts.post_type = 'pose' AND $wpdb->posts.post_status = 'publish' ) AND 
					( $wpdb->posts.post_title LIKE '%$search%' OR $wpdb->terms.name LIKE '%$search%' OR $wpdb->termmeta.meta_value LIKE '%$search%' )
					GROUP BY $wpdb->posts.post_title ";

}


/**
 * Return default poses term id, needs if user forget poses name
 *
 * @return array|null|object
 */
function get_default_term_id() {
	global $wpdb;
	$sql = "SELECT term_id FROM $wpdb->terms WHERE `name` LIKE '%Yoga Pose%'";
	$term_id = $wpdb->get_results($sql);

	return $term_id;
}


/**
 * Get string for Pinterest sharing title
 *
 * @param $post_id
 *
 * @return string
 */
function get_pinterest_shared_string( $post_id ) {
	$instagram_username = get_post_meta( $post_id, 'instagram_username', 1 );
	$args               = array( 'orderby' => 'name', 'order' => 'ASC', 'fields' => 'all' );
	$post_terms         = wp_get_post_terms( $post_id, 'poses_tag', $args );
	$sanscrit_name      = get_term_meta( $post_terms[0]->term_id, 'sanscrit_tag_name', 1 );
	$pose               = $post_terms[0]->name . ' — ' . $sanscrit_name;

	return $pose . ' by ' . $instagram_username . ' | Yoga Poses | Yoga practice';
}
