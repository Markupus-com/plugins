jQuery(function($){

	// load more boards
	$('.loadmore-bord-button').click(function(e){
		e.preventDefault();
		var button = $(this),
			data = {
				'action': 'loadmore',
				'page' : poses_loadmore_params.current_page,
				'ordering' : $( '.filter-selected-value' ).attr( 'data-sort-val' )
			};
		$.ajax({
			url : poses_loadmore_params.ajaxurl,
			data : data,
			type : 'POST',
			success : function( data ){

				data = JSON.parse( data );

				if( data.data ) {
					$('.board-list-before').before(data.data);
					poses_loadmore_params.current_page++;

					if ( data.is_last ) {
						$('.board-ajax-load').hide();
					}
				}
			}
		});
	});


	// load more posts inside the boards
	$('body').on('click', '.loadmore-inside-bord-button', function(e){
		$('.grid__button.inside-board-ajax-load').hide();
		e.preventDefault();
		var button = $(this),
			data = {
				'action': 'loadmore_photos',
				'page' : poses_loadmore_params.current_page,
				'tag_id' : $('.tag-poses-list').attr('data-taxid')
			};
		$.ajax({
			url : poses_loadmore_params.ajaxurl,
			data : data,
			type : 'POST',
			success : function( data ){
				data = JSON.parse( data );

				if( data.data ) {
					var $items = $(data.data);
					var $grid = $('.grid.tag-poses-list');
					$grid.append( $items );
					$grid.masonry( 'appended', $items );
					//$grid.masonry( 'reloadItems' ); // на крайний случай

					$grid.imagesLoaded().progress( function() {
						setTimeout(function () {
							$grid.masonry('layout');
						}, 500);
					});

					setTimeout(function () {
						$('.grid__button.inside-board-ajax-load').show();
					}, 500);
					poses_loadmore_params.current_page++;
					if ( data.is_last ) {
						$('.inside-board-ajax-load').remove();
					}
				}

				// if( data.data ) {
				// 	var $items = data.data;
				// 	var $grid = $('.grid.tag-poses-list');
				// 	$grid.append( $items );
				//
				// 	setTimeout(function () {
				// 		$grid.imagesLoaded().progress( function() {
				// 			$grid.masonry('layout');
				// 		});
				// 		//$grid.masonry( 'layout' );
				// 		//$grid.masonry( 'reloadItems' );
				// 		$('.grid__button.inside-board-ajax-load').show();
				// 	}, 300);
				// 	poses_loadmore_params.current_page++;
				// 	if ( data.is_last ) {
				// 		$('.inside-board-ajax-load').remove();
				// 	}
				// }
			}
		});
	});


	// load more search posts inside the search page
	$('.loadmore-search-button').click(function(e){
		e.preventDefault();
		var button = $(this),
			data = {
				'action': 'loadmore_search',
				'page' : poses_loadmore_params.current_page,
			};
		$.ajax({
			url : poses_loadmore_params.ajaxurl,
			data : data,
			type : 'POST',
			success : function( data ){

				data = JSON.parse( data );

				if( data.data ) {

					var $items = data.data;
					var $grid = $('.grid.tag-poses-list');
					$grid.append($items);
					$grid.imagesLoaded().progress( function() {
						$grid.masonry();
					});
					setTimeout(function () {
						$grid.masonry('reloadItems');
						$grid.masonry('layout');
					}, 300);

					poses_loadmore_params.current_page++;

					if ( data.is_last ) {
						$('.inside-board-ajax-load').remove();
					}
				}
			}
		});
	});


	// create object of the form, to send for inserting
	var file_data;
	var form_data;
	$('input[type=file]').on('change', fileUpload);
	function fileUpload (event) {
		file_data = jQuery(this).prop('files')[0];
		form_data = new FormData();
		form_data.append('file', file_data);
		form_data.append('action', 'upload_new_photo');
	}

	// Upload form submitting data and inserting new post
	$("#wizard .actions a[href='#next']").on('click', function() {

		var form_errors = false;
		$( '.form__group' ).each(function(){
			if ( $(this).hasClass('has-error') ) {
				form_errors = true;
			}
		});

		if ( 'Finish' === $(this).text() && false === form_errors ) {

			var pose_name      = $('.js-select2-full').val(),
			forgot_pose        = $('.forgot-pose').is(':checked'), // checkbox
			where_photo        = $('.where-photo').val(),
			when_is_photo      = $('.when-is-photo').val(),
			photo_description  = $('.photo-description').val(),
			your_email         = $('.your-email').val(),
			instagram_username = $('.instagram-username').val(),
			photographer_name  = $('.photographer-name').val();
			video_screenshot   = $('#video-screenshot').attr('src');
			g_recaptcha        = $('.g-recaptcha').attr('data-sitekey');

			form_data.append( 'pose_name', pose_name );
			form_data.append( 'forgot_pose', forgot_pose );
			form_data.append( 'where_photo', where_photo );
			form_data.append( 'when_is_photo', when_is_photo );
			form_data.append( 'photo_description', photo_description );
			form_data.append( 'your_email', your_email );
			form_data.append( 'instagram_username', instagram_username );
			form_data.append( 'photographer_name', photographer_name );
			form_data.append( 'video_screenshot', video_screenshot );
			form_data.append( 'g_recaptcha', g_recaptcha );

			$('.profile__image.to-insert img').hide();
			$('.video-after-ajax').hide();
			$('.profile__link-icon').hide();
			$('.profile__info.info').hide();

			$.ajax({
				url: poses_loadmore_params.ajaxurl,
				type: 'post',
				contentType: false,
				processData: false,
				data: form_data,
				success: function (response) {

					data = JSON.parse( response );
					var to_insert = data.data;
					var to_insert_title = to_insert.title.replace('(', ' — ').replace(')', '');
					if ( to_insert_title === '  — ' ) {
						to_insert_title = "Default";
					}
					$('.profile__title.to-insert').text( to_insert_title );
					$('.taken-city').text( to_insert.city );
					$('.photo-date').text( to_insert.when_is_photo );
					$('.taken-country').html( '<div class="info__item taken-country"' + to_insert.country + '</div>' );
					$('.profile__description.to-insert').text( to_insert.description );

					$('.profile__media').hide();

					if ( to_insert.video_link.length > 2 ) {

						$('.profile__image.to-insert img').hide();
						$('.video-after-ajax').show();
						$('.profile__link-icon').show();
						$('.profile__info.info').show();

						$('.profile__image.to-insert').hide();
						$('.profile__video.to-insert video source').attr( 'src', to_insert.video_link );
						$('.profile__media').show();
						$('.profile__loader').hide();
						$('.video-after-ajax').get(0).load();
					} else {
						$('.profile__image.to-insert img').show();
						$('.video-after-ajax').hide();
						$('.profile__link-icon').show();
						$('.profile__info.info').show();

						$('.profile__video.to-insert').hide();
						$('.profile__image.to-insert img').attr( 'src', to_insert.post_image );
						$('.profile__loader').hide();
						$('.profile__media').show();
					}

					$('.profile__link.link-instagram').attr( 'href', 'https://www.instagram.com/' + to_insert.instagram_username );
					$('.author-link').attr( 'href', 'https://www.instagram.com/' + to_insert.instagram_username );

					if ( to_insert.photographer_name.length === 0 ) {
						$('.insta-author-name').text( to_insert.instagram_username );
					} else {
						$('.insta-author-name').text( to_insert.photographer_name );
					}
					$('.author-link').text( to_insert.instagram_username );
					$('#link-on-page').val(to_insert.link_on_page);
				},
				error: function (response) {
					console.log(response);
				}
			});
		}
	});


	// when clicking on pinterest icon
	$(document.body).on('click', '.icon-wrap', function(e){
		e.stopPropagation();
		var that = $(this);
		PinUtils.pinOne({
			'url': location.href,
			'media': that.parent().prev().attr('src'),
			'description': that.parent().prev('img').attr('alt'),
		});

		return false;
	});


	// Where photo - address aucocomplete field
	$(".geocomplete").geocomplete();
	$("#find").click(function(){
		$(".geocomplete").trigger("geocode");
	});


	// copy link on image page when click on Copy button
	$('.copy-link-button').on('click', function(e) {
		e.preventDefault();
	});
	var inputhidden = $('#link-on-page');
	new Clipboard('.copy-link-button', {
		text: function (trigger) {
			$('.copy-link-button').text('Copied');
			return inputhidden.val();
		}
	});


	// select 2 on select - for ordering posts, boards
	$('.js-select2-light').on('select2:select', function (e) {
		$('.filter-selected-value').attr('data-sort-val', $(this).val());

		var ordering = $( '.filter-selected-value' ).attr( 'data-sort-val' );

		$('.board-ajax-load').show();

		poses_loadmore_params.current_page = 0;
		var button = $(this),
			data = {
				'action': 'loadmore',
				'page' : poses_loadmore_params.current_page,
				'ordering' : ordering
			};
		$.ajax({
			url : poses_loadmore_params.ajaxurl,
			data : data,
			type : 'POST',
			success : function( data ){

				data = JSON.parse( data );

				if( data.data ) {
					$('.board a').each(function() {
						$(this).remove();
						poses_loadmore_params.current_page = 0;
					});

					$('.board-list-before').before(data.data);
					poses_loadmore_params.current_page++;

					if ( data.is_last ) {
						$('.board-ajax-load').hide();
					}
				}
			}
		});

	});


});
