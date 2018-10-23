<?php

get_header(); ?>

<main>
	<div class="container">
		<div class="panel-action panel-action--profile">

			<section class="error-404 not-found">
				<header class="page-header">
					<h1 class="page-title"><?php _e( 'Oops! That page can&rsquo;t be found.', 'twentyseventeen' ); ?></h1>
				</header>
				<div class="page-content">
					<p><?php _e( 'It looks like nothing was found at this location. Maybe try a search?', 'twentyseventeen' ); ?></p>
				</div>
			</section>

		</div>
	</div>
</main>

<?php get_footer();
