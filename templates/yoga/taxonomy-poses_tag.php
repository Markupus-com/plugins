<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Twenty_Seventeen
 * @since 1.0
 * @version 1.0
 */

get_header(); ?>

	<main>
		<div class="banner banner--inside">

			<div class="container">
				<div class="banner__row">
					<div class="banner__col">
						<div class="banner__info">
							<?php $currend_teg_obj = get_queried_object(); ?>
							<?php if ( $currend_teg_obj->name ) : ?>
							<?php
								$sanscrit_name = get_term_meta( $currend_teg_obj->term_id, 'sanscrit_tag_name', 1 );
								$sanscrit_title = ( $sanscrit_name ) ? ' — ' . $sanscrit_name : '';
							?>
								<h1 class="banner__title h2"><?php echo esc_html( $currend_teg_obj->name ) . $sanscrit_title; ?></h1>
							<?php endif; ?>

							<?php if ( $currend_teg_obj->description ) : ?>
								<?php echo wpautop( $currend_teg_obj->description ); ?>
							<?php endif; ?>
						</div>
					</div>
					<div class="banner__col banner__col--shrink">
						<?php $pose_cat_image_input = get_term_meta( $currend_teg_obj->term_id, 'pose_cat_image_input', 1 ); ?>
						<?php if ( !empty( $pose_cat_image_input ) ) : ?>
						<div class="banner__img">
							<img class="board-main-img" src="<?php echo esc_attr( $pose_cat_image_input ); ?>" alt="">
						</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>

		<div class="container">
			<div class="panel-action">
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
			$current_term = get_queried_object();
			$current_term_id = $current_term->term_id;
			?>
			<div class="grid tag-poses-list" data-taxid="<?php echo esc_attr( $current_term_id ); ?>">
				<?php
				$page = ( get_query_var('paged') ) ? get_query_var( 'paged' ) : 1;
				$per_page = get_option('posts_per_page');
				$offset = ( $page-1 ) * $per_page;
				$term_name = $current_term->name;

				// get all posts count in current board
				global $wpdb;
				$all_posts_count_sql = get_posts_boards_sql( $current_term_id );
				$all_posts_count_result = $wpdb->get_results($all_posts_count_sql);
				$all_posts_count = count( $all_posts_count_result );
				$query = get_posts_inside_boards( $per_page, $offset, $current_term_id );

				echo print_posts_by_wp_query_obj( $query, $term_name );
				?>

			</div>
			<?php if ( $all_posts_count > $per_page ) : ?>
			<div class="grid__button inside-board-ajax-load">
				<a href="" class="button loadmore-inside-bord-button"><?php echo __( 'Load more photos', 'yoga' ); ?></a>
			</div>
			<?php endif; ?>
		</div>
	</main>

<?php get_footer();
