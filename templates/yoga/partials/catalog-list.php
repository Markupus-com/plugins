<div class="panel-action__dropdown catalog dropdown dropdown--lg">
	<a href="#" class="catalog__toggle dropdown__toggle">
		<span class="dropdown__text">
			<span class="dropdown__text-desktop">
				<?php echo __( 'Catalog', 'yoga' ); ?>
			</span>
            <span class="dropdown__text-mobile">
				<?php echo __( 'Yoga Poses', 'yoga' ); ?>
			</span>
		</span>
		<span class="dropdown__icon">
            <svg class="dropdown-icon" width="1em" height="1em">
                <use xlink:href="#triangle"></use>
            </svg>
        </span>
	</a>
	<div class="dropdown__menu">
		<ul class="dropdown__tabs tabs">
			<li class="tabs__link is-current" data-tab="tab-1"><?php echo __( 'English names', 'yoga' ); ?></li>
			<li class="tabs__link" data-tab="tab-2"><?php echo __( 'Sanskrit names', 'yoga' ); ?></li>
		</ul>
		<?php
		$terms_list = get_terms( array( 'poses_tag' ) );
		?>
		<?php $currend_teg_obj = get_queried_object(); ?>
		<?php
		if ( !empty( $currend_teg_obj ) ) {
			$pose_cat_image_input = get_term_meta( $currend_teg_obj->term_id, 'pose_cat_image_input', 1 );
			$pose_name = $currend_teg_obj->name;
		} else {
			$pose_cat_image_input = '';
			$pose_name = '';
		}

		?>
		<?php if ( !empty( $pose_cat_image_input ) ) {
			$def_img_url = $pose_cat_image_input;
		} else {
			$def_img_url = '';
		}?>

		<div id="tab-1" class="tabs__content is-current">
			<div class="tabs__inner">
				<div class="tabs__list">
					<ul class="list">
						<?php foreach ( $terms_list as $item ) : ?>
						<?php if ( 'Yoga Pose' == $item->name ) { continue; } ?>
							<?php $pose_cat_image_input = get_term_meta( $item->term_id, 'pose_cat_image_input', 1 ); ?>
							<li class="list__item">
								<a href="<?php echo get_term_link( $item->term_id ); ?>" data-img="<?php echo esc_attr( $pose_cat_image_input ); ?>" class="list__link hover-link"><?php echo esc_html( $item->name ); ?></a>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>
		</div>

		<div id="tab-2" class="tabs__content">
			<div class="tabs__inner">
				<div class="tabs__list">
					<ul class="list">
						<?php foreach ( $terms_list as $item ) : ?>
							<?php $sanscrit_name = get_term_meta( $item->term_id, 'sanscrit_tag_name', 1 ); ?>
							<?php $pose_cat_image_input = get_term_meta( $item->term_id, 'pose_cat_image_input', 1 ); ?>
							<li class="list__item">
								<a href="<?php echo get_term_link( $item->term_id ); ?>" data-img="<?php echo esc_attr( $pose_cat_image_input ); ?>" class="list__link hover-link"><?php echo esc_html( $sanscrit_name ); ?></a>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>
		</div>

	</div>
</div>