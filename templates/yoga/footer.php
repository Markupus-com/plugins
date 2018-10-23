<?php
/**
 * The template for displaying the footer
 */

?>

<footer class="footer">
	<div class="container">
		<div class="footer__inner">
			<div class="footer__col">
				<div class="footer__info">
					<?php
					$footer_copy = get_option('footer_copy');
					echo ( !empty( $footer_copy ) ) ? $footer_copy : 'Yoga Poses Gallery by Yoga Practice.';
					?>
					<span class="footer__info-link">
						<?php
						$privacy_link = get_option('privacy_link');
						$terms_link   = get_option('terms_link');
						?>
						<a href="<?php echo esc_attr( ( !empty( $privacy_link ) ) ? $privacy_link : '#' ); ?>" class="footer__link"><?php echo __( 'Privacy Policy', 'yoga' ); ?></a> <?php echo __( 'and', 'yoga' ); ?>
						<a href="<?php echo esc_attr( ( !empty( $terms_link ) ) ? $terms_link : '#' ); ?>" class="footer__link"><?php echo __( 'Terms of Service', 'yoga' ); ?></a>.
					</span>

				</div>



			</div>
			<div class="footer__col">
				<div class="footer__social social">
					<?php
					$instagram_link = get_option( 'instagram_link' );
					$twitter_link   = get_option( 'twitter_link' );
					$facebook_link  = get_option( 'facebook_link' );
					$pinterest_link = get_option( 'pinterest_link' );
					?>

					<?php if ( !empty( $pinterest_link ) || !empty( $twitter_link ) || !empty( $facebook_link ) || !empty( $instagram_link ) ) : ?>

					<ul class="social-list">
						<?php if ( !empty( $instagram_link ) ) : ?>
						<li class="social-list__item">
							<a href="<?php echo esc_attr( $instagram_link ); ?>" class="social-list__link" target="_blank">

								<svg class="icon__instagram" width="1em" height="1em">
									<use xlink:href="#instagram"></use>
								</svg>

							</a>
						</li>
						<?php endif; ?>
						<?php if ( !empty( $facebook_link ) ) : ?>
						<li class="social-list__item">
							<a href="<?php echo esc_attr( $facebook_link ); ?>" class="social-list__link" target="_blank">

								<svg class="icon__facebook" width="1em" height="1em">
									<use xlink:href="#facebook"></use>
								</svg>

							</a>
						</li>
						<?php endif; ?>
						<?php if ( !empty( $twitter_link ) ) : ?>
						<li class="social-list__item">
							<a href="<?php echo esc_attr( $twitter_link ); ?>" class="social-list__link" target="_blank">

								<svg class="icon__twitter" width="1em" height="1em">
									<use xlink:href="#twitter"></use>
								</svg>

							</a>
						</li>
						<?php endif; ?>
						<?php if ( !empty( $pinterest_link ) ) : ?>
						<li class="social-list__item">
							<a href="<?php echo esc_attr( $pinterest_link ); ?>" class="social-list__link" target="_blank">

								<svg class="icon__pinterest" width="1em" height="1em">
									<use xlink:href="#pinterest"></use>
								</svg>

							</a>
						</li>
						<?php endif; ?>
					</ul>
					<?php endif; ?>

				</div>
			</div>	

			<a class="footer__link book-yoga"
			   href="https://yogaretreats.org/"><?php echo __( 'Book Yoga Retreats', 'yoga' ); ?>
			</a>

		</div>
	</div>
</footer>
</div>

<?php wp_footer(); ?>

</body>

</html>
