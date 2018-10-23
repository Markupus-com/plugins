<?php
/**
 * Template for displaying search forms in Twenty Seventeen
 */

?>

<?php $unique_id = esc_attr( uniqid( 'search-form-' ) ); ?>

<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<input type="text" id="<?php echo $unique_id; ?>" class="search__control" placeholder="<?php echo __( 'Search Yoga Poses', 'yoga' ); ?>" value="<?php echo get_search_query(); ?>" maxlength="30" name="s" />
	<span class="search__icon">
		<svg class="icon__search" width="1em" height="1em">
			<use xlink:href="#search"></use>
		</svg>
	</span>
</form>



