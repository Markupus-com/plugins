<?php
/**
 * The header for our theme
 */

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js no-svg">
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta http-equiv="x-ua-compatible" content="ie=edge">
	<title><?php echo wp_title( '', true ); ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
	<meta content="telephone=no" name="format-detection">
	<!-- This make sence for mobile browsers. It means, that content has been optimized for mobile browsers -->
	<meta name="HandheldFriendly" content="true">
	<?php
	ob_start();
	wp_head();
	$var_head = ob_get_contents();
	ob_end_clean();
	$var_head = preg_replace('/<title>(.*?)<\/title>/s', '', $var_head );
	$var_head = str_replace( "defer'", 'defer', $var_head );
	echo $var_head;
	?>
	<script>
		(function(H){H.className=H.className.replace(/\bno-js\b/,'js')})(document.documentElement)
	</script>
	<script src='https://www.google.com/recaptcha/api.js'></script>

</head>

<body <?php body_class(); ?>>
<?php
	$fb_form_app_id = get_option( 'facebook_app_id' );
?>
<?php if ( !empty( $fb_form_app_id ) ) : ?>
<div id="fb-root"></div>
<script>(function(d, s, id) {
		var js, fjs = d.getElementsByTagName(s)[0];
		if (d.getElementById(id)) return;
		js = d.createElement(s); js.id = id;
		js.src = 'https://connect.facebook.net/<?php echo get_locale(); ?>/sdk.js#xfbml=1&version=v2.12&appId=<?php echo $fb_form_app_id; ?>&autoLogAppEvents=1';
		fjs.parentNode.insertBefore(js, fjs);
	}(document, 'script', 'facebook-jssdk'));</script>
<?php endif; ?>

<div class="wrapper">
	<svg xmlns="http://www.w3.org/2000/svg" style="border: 0 !important; clip: rect(0 0 0 0) !important; height: 1px !important; margin: -1px !important; overflow: hidden !important; padding: 0 !important; position: absolute !important; width: 1px !important;"
		 class="root-svg-symbols-element">
		<symbol id="check" viewBox="0 0 28 28">
			<title><?php echo __( 'check', 'yoga' ); ?></title>
			<path d="M24.157 6.176a1.17 1.17 0 0 0-1.648 0L10.5 18.185l-5.009-5.009c-.452-.452-1.196-.452-1.648 0s-.452 1.196 0 1.648l5.833 5.833c.226.226.525.343.824.343s.598-.117.824-.343L24.157 7.824a1.158 1.158 0 0 0 0-1.648z"
			/>
		</symbol>
		<symbol id="facebook" viewBox="0 0 24 24">
			<path fill="#4460A0" fill-rule="evenodd" d="M12.82 24H1.324A1.325 1.325 0 0 1 0 22.675V1.325C0 .593.593 0 1.325 0h21.35C23.407 0 24 .593 24 1.325v21.35c0 .732-.593 1.325-1.325 1.325H16.56v-9.294h3.12l.466-3.622H16.56V8.77c0-1.048.29-1.763 1.795-1.763h1.918v-3.24c-.332-.045-1.47-.143-2.795-.143-2.766 0-4.659 1.688-4.659 4.788v2.67H9.692v3.623h3.127V24z"
			/>
		</symbol>
		<symbol id="instagram" viewBox="0 0 551.034 551.034">
			<linearGradient id="a" gradientUnits="userSpaceOnUse" x1="275.517" y1="4.57" x2="275.517" y2="549.72" gradientTransform="matrix(1 0 0 -1 0 554)">
				<stop offset="0" stop-color="#e09b3d" />
				<stop offset=".3" stop-color="#c74c4d" />
				<stop offset=".6" stop-color="#c21975" />
				<stop offset="1" stop-color="#7024c4" />
			</linearGradient>
			<path d="M386.878 0H164.156C73.64 0 0 73.64 0 164.156v222.722c0 90.516 73.64 164.156 164.156 164.156h222.722c90.516 0 164.156-73.64 164.156-164.156V164.156C551.033 73.64 477.393 0 386.878 0zM495.6 386.878c0 60.045-48.677 108.722-108.722 108.722H164.156c-60.045 0-108.722-48.677-108.722-108.722V164.156c0-60.046 48.677-108.722 108.722-108.722h222.722c60.045 0 108.722 48.676 108.722 108.722v222.722z"
				  fill="url(#a)" />
			<linearGradient id="b" gradientUnits="userSpaceOnUse" x1="275.517" y1="4.57" x2="275.517" y2="549.72" gradientTransform="matrix(1 0 0 -1 0 554)">
				<stop offset="0" stop-color="#e09b3d" />
				<stop offset=".3" stop-color="#c74c4d" />
				<stop offset=".6" stop-color="#c21975" />
				<stop offset="1" stop-color="#7024c4" />
			</linearGradient>
			<path d="M275.517 133C196.933 133 133 196.933 133 275.516s63.933 142.517 142.517 142.517S418.034 354.1 418.034 275.516 354.101 133 275.517 133zm0 229.6c-48.095 0-87.083-38.988-87.083-87.083s38.989-87.083 87.083-87.083c48.095 0 87.083 38.988 87.083 87.083 0 48.094-38.989 87.083-87.083 87.083z"
				  fill="url(#b)" />
			<linearGradient id="c" gradientUnits="userSpaceOnUse" x1="418.31" y1="4.57" x2="418.31" y2="549.72" gradientTransform="matrix(1 0 0 -1 0 554)">
				<stop offset="0" stop-color="#e09b3d" />
				<stop offset=".3" stop-color="#c74c4d" />
				<stop offset=".6" stop-color="#c21975" />
				<stop offset="1" stop-color="#7024c4" />
			</linearGradient>
			<circle cx="418.31" cy="134.07" r="34.15" fill="url(#c)" />
		</symbol>
		<symbol id="pinterest" viewBox="0 0 24 24">
			<path fill="#CC2127" fill-rule="evenodd" d="M12 0C5.375 0 0 5.372 0 12c0 4.913 2.955 9.135 7.184 10.991-.034-.837-.005-1.844.208-2.756l1.544-6.538s-.383-.766-.383-1.9c0-1.778 1.032-3.106 2.315-3.106 1.09 0 1.618.82 1.618 1.803 0 1.096-.7 2.737-1.06 4.257-.3 1.274.638 2.312 1.894 2.312 2.274 0 3.805-2.92 3.805-6.38 0-2.63-1.771-4.598-4.993-4.598-3.64 0-5.907 2.714-5.907 5.745 0 1.047.307 1.784.79 2.354.223.264.253.368.172.67-.056.219-.189.752-.244.963-.08.303-.326.413-.6.3-1.678-.684-2.458-2.52-2.458-4.585 0-3.408 2.875-7.497 8.576-7.497 4.582 0 7.598 3.317 7.598 6.875 0 4.708-2.617 8.224-6.476 8.224-1.294 0-2.514-.7-2.931-1.494 0 0-.698 2.764-.844 3.298-.254.924-.752 1.85-1.208 2.57 1.08.318 2.22.492 3.4.492 6.628 0 12-5.372 12-12S18.628 0 12 0"
			/>
		</symbol>
		<symbol id="play" viewBox="0 0 32 32">
			<title><?php echo __( 'play', 'yoga' ); ?></title>
			<path fill="currentColor" d="M8.997 5.903l16.656 9.518a.667.667 0 0 1 0 1.158L8.997 26.097a.667.667 0 0 1-.998-.579V6.482a.667.667 0 0 1 .998-.579z" />
		</symbol>
		<symbol id="search" viewBox="0 0 32 32">
			<title><?php echo __( 'search', 'yoga' ); ?></title>
			<path fill="currentColor" d="M12.733 1.311c-6.298 0-11.422 5.124-11.422 11.422s5.124 11.422 11.422 11.422 11.422-5.124 11.422-11.422S19.031 1.311 12.733 1.311zm0 24.156C5.712 25.467 0 19.755 0 12.734 0 5.714 5.712.001 12.733.001s12.733 5.713 12.733 12.733c0 7.021-5.713 12.733-12.733 12.733z"
			/>
			<path fill="currentColor" d="M30.813 31.775l-10.02-10.021.963-.963 10.02 10.021z" />
		</symbol>
		<symbol id="triangle" viewBox="0 0 9 4">
			<path fill="none" stroke="currentColor" d="M8 0L4.5 4 1 0" />
		</symbol>
		<symbol id="twitter" viewBox="0 0 24 20">
			<path fill="#00AAEC" fill-rule="evenodd" d="M24 2.368a9.617 9.617 0 0 1-2.827.794A5.038 5.038 0 0 0 23.338.37a9.698 9.698 0 0 1-3.129 1.223A4.856 4.856 0 0 0 16.616 0c-2.718 0-4.922 2.26-4.922 5.049 0 .396.042.78.126 1.15C7.728 5.988 4.1 3.979 1.67.922a5.14 5.14 0 0 0-.666 2.54c0 1.751.87 3.297 2.19 4.203a4.834 4.834 0 0 1-2.23-.63v.062c0 2.447 1.697 4.488 3.951 4.95a4.695 4.695 0 0 1-1.297.178c-.317 0-.627-.03-.927-.09.626 2.006 2.444 3.466 4.599 3.505A9.722 9.722 0 0 1 0 17.733 13.71 13.71 0 0 0 7.548 20c9.058 0 14.01-7.692 14.01-14.365 0-.22-.005-.439-.013-.654A10.1 10.1 0 0 0 24 2.368"
			/>
		</symbol>
	</svg>
	<header class="header">
		<div class="container">
			<div class="header__inner">
				<div class="header__logo">
					<?php $logo_text = get_option( 'logo_text' ); ?>
						<a href="/" class="logo"><?php echo ( !empty( $logo_text ) ) ? $logo_text : __( 'yoga poses', 'yoga' ); ?></a>
				</div>
				<?php $upload_photo_text = get_option( 'upload_photo_text' ); ?>
				<div class="header__button">
					<a href="/upload-your-photo/" class="button"><?php echo ( !empty( $upload_photo_text ) ) ? $upload_photo_text : __( 'Upload your photo', 'yoga' ); ?></a>
				</div>
			</div>
		</div>
	</header>
