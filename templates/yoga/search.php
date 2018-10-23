<?php
/**
 * The template for displaying search results pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#search-result
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
							<?php $search = $_GET['s']; ?>
							<?php if ( $search ) : ?>
								<div class="banner__title h2"><?php echo __( 'search results for: ', 'yoga' ) . $search; ?></div>
							<?php else: ?>
								<div class="banner__title h2"><?php echo __( 'Empty query ', 'yoga' ) . $search; ?></div>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="container">
			<?php if ( $search ) : ?>

				<?php
					$per_page = get_option('posts_per_page');
					global $wpdb;
					$query_all = $wpdb->get_results( get_search_posts_sql( $search ) );
					$sql = get_search_posts_sql( $search ) . " LIMIT $per_page ";
					$query = $wpdb->get_results($sql);
				?>
				<?php if ( !empty( $query ) ) : ?>
				<div class="panel-action">
					<div class="panel-action__col">
						<?php get_template_part( 'partials/catalog-list' ); ?>
						<?php get_template_part( 'partials/breadcrumbs' ); ?>
					</div>
					<div class="panel-action__col">
						<?php get_template_part( 'partials/ordering' ); ?>
						<div class="panel-action__search">
							<div class="search">
								<?php get_search_form(); ?>
							</div>
						</div>
					</div>
				</div>
				<div class="grid tag-poses-list">
					<?php

					echo print_posts_by_wp_query_obj( $query );
					?>
					<span class="tag-poses-list-before"></span>
				</div>
					<?php if ( count($query_all) > get_option( 'posts_per_page' ) ) : ?>
					<div class="grid__button inside-board-ajax-load">
						<a href="" class="button loadmore-search-button"><?php echo __( 'Load more photos', 'yoga' ); ?></a>
					</div>
					<?php endif; ?>

				<?php else: ?>
					<div class="banner__title h2"><?php echo __('Results not found', 'yoga'); ?></div>
				<?php endif; ?>

			<?php endif; ?>

		</div>
	</main>

<?php get_footer();
