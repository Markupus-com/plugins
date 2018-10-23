<div class="panel-action__breadcrumb">
	<?php if ( !is_front_page() ) : ?>
	<ul class="breadcrumb">
		<li class="breadcrumb__item">
			<a href="/" class="breadcrumb__link">
				<?php echo __( 'Yoga Poses', 'yoga' ) ?>
			</a>
		</li>
		<li class="breadcrumb__item">
			<span class="breadcrumb__link is-active">
				<?php
				if ( is_archive() ) {
					echo esc_html( get_queried_object()->name );
				} elseif ( is_single() ) {
					$current_term       = wp_get_post_terms( get_the_ID(), 'poses_tag', array( 'hide_empty' => true ) )[0];
					if ( !empty( $current_term ) ) {
						$sanscrit_name      = get_term_meta( $current_term->term_id, 'sanscrit_tag_name', 1 );
					} else {
						$sanscrit_name = '';
					}
					$term_link = get_term_link( $current_term->term_id );
					$title_part = '<a href="' . $term_link . '">' . $current_term->name . '</a>';
					echo $title_part;
				} elseif ( is_page() ) {
					echo get_the_title();
				} elseif ( is_search() ) {
					echo $_GET['s'];
				}
					?>
			</span>
		</li>
		<?php if ( is_single() ) : ?>
		<?php
			$title = get_the_title();

			if ( empty( $title ) ) {
				$title = get_post_meta( get_the_ID(), 'instagram_username', true );
			}
			?>
			<?php if ( !empty( $title ) ) : ?>
			<li class="breadcrumb__item">
				<span class="breadcrumb__link is-active">
					<?php echo $title; ?>
				</span>
			</li>
			<?php endif; ?>

		<?php endif; ?>
	</ul>
	<?php endif; ?>
</div>