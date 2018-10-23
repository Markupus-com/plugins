<?php
/**
 * The template for displaying pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages and that
 * other "pages" on your WordPress site will use a different template.
 *
 * @package WordPress
 * @subpackage Twenty_Sixteen
 * @since Twenty Sixteen 1.0
 */

get_header(); ?>

<main>
	<div class="container">

		<div class="panel-action panel-action--profile">
			<div class="panel-action__col">
				<?php get_template_part( 'partials/catalog-list' ); ?>
				<?php get_template_part( 'partials/breadcrumbs' ); ?>
			</div>
			<div class="panel-action__col">

			</div>
		</div>

		<form action="#" id="wizard" class="wizard loader" enctype="multipart/form-data">
			<h2 class="wizard__step"><?php echo __( 'Photo / Video', 'yoga' ); ?></h2>
			<section class="wizard__content">
				<!-- CANVAS NEED FOR SCREENSHOT FROM VIDEO -->
				<canvas id="canvas-video"></canvas>
				<img src="" id="video-screenshot" alt="">
				<!-- / CANVAS NEED FOR SCREENSHOT FROM VIDEO -->
				<div class="container__inner container__inner--sm">
					<div class="form__group drop">
						<input type="file" name="file" class="upload-file" required>
					</div>
					<div class="form__note"><?php echo __( 'Limit 10 Mb. JPG, PNG or MP4 files only', 'yoga' ); ?></div>
				</div>
			</section>

			<h2 class="wizard__step"><?php echo __( 'Description', 'yoga' ); ?></h2>
			<section class="wizard__content">
				<div class="container__inner container__inner--xs">
					<div class="head">
						<h2><?php echo __( 'Tell us a bit more about this beautiful photo', 'yoga' ); ?></h2>
					</div>

					<div class="form__group">
						<label for="input-1" class="form__label"><?php echo __( 'What yoga pose is this?', 'yoga' ); ?></label>

						<?php

						$taxonomia = array(
							'poses_tag',
						);

						$args = array(
							'orderby'           => 'name',
							'order'             => 'ASC',
							'hide_empty'        => false,
							'exclude'           => array(),
							'exclude_tree'      => array(),
							'include'           => array(),
							'number'            => '',
							'fields'            => 'all',
							'slug'              => '',
							'parent'            => '',
							'hierarchical'      => true,
							'child_of'          => 0,
							'childless'         => false,
							'get'               => '',
							'name__like'        => '',
							'description__like' => '',
							'pad_counts'        => false,
							'offset'            => '',
							'search'            => '',
							'cache_domain'      => 'core'
						);

						$terms_list = get_terms($taxonomia, $args);
						?>

						<select class="js-select2-full" style="width: 100%;" data-placeholder="Enter a yoga pose name" name="selectPose" required>
							<option value="">&nbsp;</option>

							<?php
							$sans_names = array();
							foreach ( $terms_list as $one_term ) {
								$sanscrit_name = get_term_meta( $one_term->term_id, 'sanscrit_tag_name', 1 );
								if ( empty( $sanscrit_name ) ) continue;
								$sans_names[$one_term->term_id] = $sanscrit_name;
							}
							asort($sans_names);
							?>

							<?php foreach ( $sans_names as $key => $name ) : ?>
								<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $name ); ?></option>
							<?php endforeach; ?>

							<?php foreach ( $terms_list as $item ) : ?>
							<?php if ( 'Yoga Pose' == $item->name || '' == $item->name ) { continue; } ?>
								<option value="<?php echo esc_attr( $item->term_id ); ?>"><?php echo esc_html( $item->name ); ?></option>
							<?php endforeach; ?>
						</select>

						<div class="form__checkbox">
							<input type="checkbox" id="checkPose" class="form__control-checkbox forgot-pose">
							<label for="checkPose">
								<span class="checkbox">
									<span class="checkbox__icon">

										<svg class="icon__check" width="1em" height="1em">
											<use xlink:href="#check"></use>
										</svg>

									</span>
								</span>
								<span class="checkbox__label"><?php echo __( 'I forgot the name of the pose', 'yoga' ); ?></span>
							</label>
						</div>
					</div>

					<div class="form__group">
						<label for="input-2" class="form__label"><?php echo __( 'Where was this photo / video taken?', 'yoga' ); ?></label>
						<input type="text" id="input-2" onkeyup="Latin_only(this);" name="wherePhoto" class="form__control where-photo geocomplete" placeholder="<?php echo __( 'Enter a City or Country', 'yoga' ); ?>" required>
					</div>

					<div class="form__group">
						<label for="input-3" class="form__label"><?php echo __( 'When was it taken?', 'yoga' ); ?></label>

						<input type="text" id="input-3" data-toggle="datepicker" class="form__control form__control--date when-is-photo" placeholder="<?php echo __( 'Enter a date MM/DD/YYYY (optional)', 'yoga' ); ?>" >
					</div>

					<div class="form__group">
						<label for="input-3" class="form__label"><?php echo __( 'What is special about this photo / video?', 'yoga' ); ?></label>
						<textarea class="form__control form__control--textarea photo-description" placeholder="<?php echo __( 'Is there a story involved? Tell us your story. We love all kind of stories.', 'yoga' ); ?>" name="aboutPhoto" required></textarea>
					</div>
				</div>
			</section>

			<h2 class="wizard__step"><?php echo __( 'Contacts', 'yoga' ); ?></h2>
			<section class="wizard__content">
				<div class="container__inner container__inner--xs">
					<div class="head">
						<h2><?php echo __( 'Almost done. Add your contact info. No spam. Promise', 'yoga' ); ?></h2>
					</div>

					<div class="form__group">
						<label for="input-mail" class="form__label"><?php echo __( 'What is your real e-mail? No spam. Promise!', 'yoga' ); ?></label>
						<input type="email" id="input-mail" name="email" class="form__control your-email" placeholder="Enter your e-mail" required>
					</div>

					<div class="form__group">
						<label for="input-username" class="form__label"><?php echo __( 'What is your Instagram username? (without @)', 'yoga' ); ?></label>
						<input type="text" maxlength="22" minlength="5" id="input-username" onkeyup="no_spaces(this);" name="inputUsername" class="form__control instagram-username" placeholder="<?php echo __( 'Enter a username', 'yoga' ); ?>" required>
					</div>

					<div class="form__group">
						<label for="input-photo" class="form__label"><?php echo __( 'Who is the photographer?', 'yoga' ); ?></label>
						<input type="text" maxlength="22" minlength="5" id="input-photo" name="inputPhoto" class="form__control photographer-name" placeholder="<?php echo __( 'Enter a photographer’s username (optional)', 'yoga' ); ?>">
					</div>

					<div class="form__group form__checkbox">
						<input type="checkbox" id="checkInput-1" name="checkInput1" class="form__control-checkbox control-give-rights" required>
						<label for="checkInput-1">
							<span class="checkbox">
								<span class="checkbox__icon">

									<svg class="icon__check" width="1em" height="1em">
										<use xlink:href="#check"></use>
									</svg>

								</span>
							</span>
							<span class="checkbox__label"><?php echo __( 'I am the person pictured in this image, and I have the full rights to distribute this image.', 'yoga' ); ?></span>
						</label>
					</div>
					<div class="form__group form__checkbox">
						<input type="checkbox" id="checkInput-2" name="checkInput2" class="form__control-checkbox control-nudity" required>
						<label for="checkInput-2">
							<span class="checkbox">
								<span class="checkbox__icon">

									<svg class="icon__check" width="1em" height="1em">
										<use xlink:href="#check"></use>
									</svg>

								</span>
							</span>
							<span class="checkbox__label"><?php echo __( 'This image doesn’t contain any nudity of any form.', 'yoga' ); ?></span>
						</label>
					</div>
					<div class="form__group form__checkbox">
						<input type="checkbox" id="checkInput-3" name="checkInput3" class="form__control-checkbox control-give-permission" required>
						<label for="checkInput-3">
							<span class="checkbox">
								<span class="checkbox__icon">

									<svg class="icon__check" width="1em" height="1em">
										<use xlink:href="#check"></use>
									</svg>

								</span>
							</span>
							<span class="checkbox__label"><?php echo __( 'I give my permission to use said image for the Yoga Poses website and  Instagram accounts with credits.', 'yoga' ); ?></span>
						</label>
					</div>
				</div>
			</section>

			<h2 class="wizard__step"><?php echo __( 'Review', 'yoga' ); ?></h2>
			<section class="wizard__content">
				<div class="container__inner">
					<div class="profile profile--upload">
						<div class="profile__top">
							<div class="profile__head">
								<h2 class="profile__title to-insert"></h2>
								<a href="#" class="profile__link link-instagram">
									<span class="profile__link-icon">

										<svg class="icon__instagram" width="1em" height="1em">
											<use xlink:href="#instagram"></use>
										</svg>

									</span>
									<span class="profile__link-text to-insert insta-author-name"></span>
								</a>
							</div>
							<div class="profile__media">
								<div class="profile__image to-insert">
									<img src="" alt="">
								</div>
								<div class="profile__loader">
									<img src="<?php echo esc_attr( WP_PLUGIN_URL . '/yoga-poses/templates/yoga/static/images/general/loader.gif' ); ?>" alt="">
								</div>
								<div class="profile__video to-insert">
									<video class="video-after-ajax" controls="true" preload="auto" width="100%">
										<source src="" type="video/mp4">
									</video>
								</div>
							</div>
						</div>
						<p class="profile__description to-insert">
						</p>

						<div class="profile__info info">
							<div class="info__item taken-country">
							</div>
							<div class="info__item">
								<span class="info__elem photo-date"></span>
							</div>
							<div class="info__item">
								<?php echo __( 'Photo by ', 'yoga' ); ?><a href="#" class="info__link author-link"></a>
							</div>
						</div>

						<div class="profile__head">
							<h2 class="profile__title"><?php echo __( 'Share it everywhere!', 'yoga' ); ?></h2>
							<p><?php echo __( 'Most shared photos will be published on our Instaram account', 'yoga' ); ?></p>
						</div>

						<?php $soc_shortcode = get_option('soc_shortcode'); ?>
						<?php if ( !empty( $soc_shortcode ) ) : ?>
							<div class="profile__social">
								<?php echo do_shortcode( $soc_shortcode ); ?>
							</div>
						<?php endif; ?>
						<div class="clr"></div>

						<div class="profile__button to-insert">
							<input type="text" id="link-on-page" name="link_on_page" value="">
							<a href="#" class="button copy-link-button"><?php echo __( 'Copy link', 'yoga' ); ?></a>
						</div>

					</div>
				</div>
			</section>
			<button
					class="g-recaptcha"
					data-sitekey="<?php echo ( get_option('re_public_key') ) ? get_option('re_public_key') : ''; ?>"
					data-callback="YourOnSubmitFn">
				Submit
			</button>
			<script>
				function Latin_only(obj) {
					if (/^[a-zA-Z0-9 ,.\-:"()]*?$/.test(obj.value))
						obj.defaultValue = obj.value;
					else
						obj.value = obj.defaultValue;
				}
				function no_spaces(obj) {
					if (/^[a-zA-Z0-9,.\-:"()_]*?$/.test(obj.value))
						obj.defaultValue = obj.value;
					else
						obj.value = obj.defaultValue;
				}
			</script>
		</form>

	</div>
</main>

<?php get_footer(); ?>
