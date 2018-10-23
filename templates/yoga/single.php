<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package WordPress
 * @subpackage Twenty_Seventeen
 * @since 1.0
 * @version 1.0
 */

get_header();
$current_term = wp_get_post_terms( get_queried_object()->ID, 'poses_tag', array( 'hide_empty' => true ) )[0];
yoga_get_set_popularity( $current_term );
?>

	<main>
		<div class="container">

			<div class="panel-action panel-action--profile">
				<div class="panel-action__col">
					<?php get_template_part( 'partials/catalog-list' ); ?>
					<?php get_template_part( 'partials/breadcrumbs' ); ?>
				</div>
				<div class="panel-action__col">
					<div class="panel-action__search">
						<div class="search">
							<?php get_search_form(); ?>
						</div>
					</div>
				</div>
			</div>

			<?php
			/* Start the Loop */
			while ( have_posts() ) : the_post();
			?>
			<div class="container__inner">
				<div class="profile">
					<div class="profile__top">
						<div class="profile__head">
							<?php

							if ( ! empty( $current_term ) ) {
								$sanscrit_name = get_term_meta( $current_term->term_id, 'sanscrit_tag_name', 1 );
							} else {
								$sanscrit_name = '';
							}
							$instagram_username = get_post_meta( get_the_ID(), 'instagram_username', 1 );
							$video_link         = get_post_meta( get_the_ID(), 'video_link', 1 );
							?>
							<h1 class="profile__title">
								<?php echo esc_html( $current_term->name ); ?>
								<?php echo ( $sanscrit_name ) ? '—' : ''; ?>
								<?php echo esc_html( $sanscrit_name ); ?>
							</h1>
							<?php if ( !empty( $instagram_username )) : ?>
							<a href="https://www.instagram.com/<?php echo esc_attr( $instagram_username ); ?>" class="profile__link" target="_blank">
								<span class="profile__link-icon">

									<svg class="icon__instagram" width="1em" height="1em">
										<use xlink:href="#instagram"></use>
									</svg>

								</span>
								<?php
								$title_author_name = preg_replace('/\d+/u', '', get_the_title());
								if ( empty( get_the_title() ) ) {
									$title_author_name = $instagram_username;
								}
								if ( !empty( $instagram_username ) ) {
									$title_author_name = $instagram_username;
								}
								?>
								<span class="profile__link-text"><?php echo esc_html( $title_author_name ); ?></span>
							</a>
							<?php endif; ?>
						</div>
						<div class="profile__media">
							<?php if ( !empty( $video_link ) ) : ?>
								<div class="profile__video">
									<video width="100%" controls poster="">
										<source src="<?php echo esc_attr( $video_link ); ?>" type="video/mp4">
									</video>
								</div>
							<?php else: ?>
							<div class="profile__image">
								<?php
								$image_id = get_post_thumbnail_id( get_the_ID() );
								echo wp_get_attachment_image( $image_id, 'single-post-image', false, array( 'alt' => $current_term->name . ' - ' . $sanscrit_name ) );
								?>
							</div>
							<?php endif; ?>

							<?php
							$prev_post = get_previous_post();
							$next_post = get_next_post();
							?>

							<?php if ( !empty($prev_post->ID) ) : ?>
							<a href="<?php echo get_the_permalink( $prev_post->ID ); ?>" class="profile__arrow profile__arrow--left"></a>
							<?php endif; ?>

							<?php if ( !empty($next_post->ID) ) : ?>
							<a href="<?php echo get_the_permalink( $next_post->ID ); ?>" class="profile__arrow profile__arrow--right"></a>
							<?php endif; ?>

						</div>
					</div>
					<?php $soc_shortcode = get_option('soc_shortcode'); ?>
					<?php if ( !empty( $soc_shortcode ) ) : ?>
					<div class="profile__social">
						<?php echo do_shortcode( $soc_shortcode ); ?>
					</div>
					<?php endif; ?>
					<p class="profile__description">
						<?php echo get_the_content(); ?>
					</p>
					<?php
					$where_photo = get_post_meta( get_the_ID(), 'where_photo', true );
					$when_is_photo = get_post_meta( get_the_ID(), 'when_is_photo', true );
					?>
					<div class="profile__info info">
						<?php if ( !empty( $where_photo ) ) : ?>
						<div class="info__item">

							<?php $where_array = explode( ',', $where_photo );
							foreach ( $where_array as $one_elem ) :
								if ( empty( $one_elem ) ) { continue; }
							?>
							<span class="info__elem taken-city"><?php echo esc_html( $one_elem ); ?></span>
						<?php endforeach; ?>
						</div>
						<?php endif; ?>
						<?php if ( !empty( $when_is_photo ) ) : ?>
						<div class="info__item">
							<span class="info__elem"><?php echo esc_html( $when_is_photo ); ?></span>
						</div>
						<?php endif; ?>
						<?php if ( !empty( $instagram_username ) ) : ?>
						<div class="info__item">
						<?php 
						$title_author_name = preg_replace('/\d+/u', '', get_the_title());
						if ( empty( get_the_title() ) ) {
							$title_author_name = $instagram_username;
						} else{
							$title_author_name = $title_author_name;
						}	
						?>
							<?php echo __( 'Photo by', 'yoga' ); ?>
							<a href="https://www.instagram.com/<?php echo esc_attr( $instagram_username ); ?>" class="info__link" target="_blank">
								<?php echo ( !empty( $title_author_name ) ) ? '' : '';
								echo esc_attr( $title_author_name ); ?>
							</a>
						</div>
					<?php endif; ?>
					</div>
					<?php $facebook_app_id = get_option( 'facebook_app_id' ); ?>
					<?php if ( !empty( $facebook_app_id ) ) : ?>
					<div class="profile__comments">
						<?php
						$facebook_comments_to_show = get_option( 'facebook_comments_to_show' );
						$facebook_comments_width   = get_option( 'facebook_comments_width' );
						?>
						<div class="fb-comments"
							 data-href="<?php echo get_the_permalink( get_the_ID() ); ?>"
							 data-width="<?php echo ( !empty( $facebook_comments_width ) ) ? esc_attr( $facebook_comments_width ) : '600'; ?>"
							 data-numposts="<?php echo ( !empty( $facebook_comments_to_show ) ) ? esc_attr( $facebook_comments_to_show ) : '5'; ?>">
						</div>
					</div>
					<?php endif; ?>
				</div>

				<?php
				$term_name = $current_term->name;
				$term_link = get_term_link( $current_term->term_id );
				$term_id = $current_term->term_id;
				$current_post_id = get_the_ID();

				// get random 4 latest posts from current category
				global $wpdb;
				$sql = "SELECT $wpdb->posts.`ID`, $wpdb->posts.`post_title`
								FROM $wpdb->posts
								LEFT JOIN $wpdb->term_relationships
								ON $wpdb->posts.`ID` = $wpdb->term_relationships.`object_id`
								WHERE $wpdb->term_relationships.`term_taxonomy_id` = $term_id 
								AND $wpdb->posts.`post_status` = 'publish'
								AND $wpdb->posts.`post_type` = 'pose'
								AND $wpdb->posts.`ID` NOT LIKE '$current_post_id'
								ORDER BY RAND() LIMIT 4
						";
				$posts_to_render = $wpdb->get_results( $sql );
				?>

				<?php if ( count( $posts_to_render ) > 0 ) : ?>
				<div class="head">
					<?php $term_name_for_title = '<a class="link-for-poses-term" href="'. $term_link .'">' . $term_name . '</a>'; ?>
					<h2 class="h2"><?php echo __( 'More ' . $term_name_for_title . ' Photos', 'yoga' ); ?></h2>
				</div>
				<?php endif; ?>
				<div class="grid-wrapper">
					<div class="grid grid--sm">
						<?php
						echo print_posts_by_wp_query_obj( $posts_to_render, $term_name );
						?>

					</div>
				</div>
			</div>

			<?php
			endwhile; // End of the loop.
			?>

		</div>
	</main>

<?php get_footer();
