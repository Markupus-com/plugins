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
		<div class="banner">
			<div class="banner__image">
				<?php
				$home_title       = get_option( 'home_title' );
				$home_subtitle    = get_option( 'home_subtitle' );
				$home_description = get_option( 'home_description' );
				$home_bg_logo     = get_option( 'home_bg_logo' );
				$bg_mob_img       = get_option( 'home_mob_bg_logo' );
				?>
				<?php if ( !empty( $home_bg_logo ) ) : ?>
					<div class="banner__image-desktop" style="background-image: url('<?php echo esc_attr( $home_bg_logo ); ?>')"></div>
				<?php endif; ?>

				<?php if ( !empty( $bg_mob_img ) ) : ?>
					<div class="banner__image-mobile" style="background-image: url('<?php echo esc_attr( $bg_mob_img );	?>')"></div>
				<?php endif; ?>
			</div>
			<div class="container">
				<div class="banner__inner">
					<?php if ( !empty( $home_title ) ) : ?>
						<h1 class="banner__title h1"><?php echo esc_attr( $home_title ); ?></h1>
					<?php endif; ?>
					<?php if ( !empty( $home_subtitle ) ) : ?>
						<p class="banner__subtitle subtitle"><?php echo esc_attr( $home_subtitle ); ?></p>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<div class="container">
			<?php if ( !empty( $home_description ) ) : ?>
			<p class="text">
				<?php echo $home_description; ?>
			</p>
			<?php endif; ?>
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
			<div class="board">
				<?php
				$page = ( get_query_var('paged') ) ? get_query_var( 'paged' ) : 1;
				$per_page = get_option('posts_per_page');
				$offset = ( $page-1 ) * $per_page;
				$tax_terms = get_boards( $per_page, $offset, 'date' );

				$sql = get_all_boards_sql();
				$results = $wpdb->get_results($sql);

				$all_boards = count( $results );
				
				?>
				<?php if ( !empty( $tax_terms ) ) : ?>
					<?php echo print_boards_by_terms_list( $tax_terms ); ?>
				<?php endif; ?>
				<span class="board-list-before"></span>
			</div>
			<?php if ( $per_page < $all_boards ) : ?>
			<div class="grid__button board-ajax-load">
				<a href="" class="button loadmore-bord-button"><?php echo __( 'Load more boards', 'yoga' ); ?></a>
			</div>
			<?php endif; ?>
		</div>
	</main>

<?php get_footer();
