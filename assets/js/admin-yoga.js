jQuery(document).ready(function($){

	var custom_uploader
		, click_elem = jQuery('.bg_yoga_upload')
		, click_mob_elem = jQuery('.bg_mob_yoga_upload')
		, remove_button = jQuery('.bg_yoga_remove')
		, remove_mob_button = jQuery('.bg_mob_yoga_remove')

		, remove_tag_button = jQuery('.pose_cat_image_remove')
		, click_upload_tag_elem = jQuery('.pose_cat_image_upload')

	// remove desktop image
	remove_button.click(function(e) {
		e.preventDefault();
		$('.home_bg_logo').val('');
		$('.img_home_bg_logo').attr('src', '');
	});

	// upload desktop image
	click_elem.click(function(e) {
		e.preventDefault();

		if (custom_uploader) {
			custom_uploader.open();
			return;
		}
		custom_uploader = wp.media.frames.file_frame = wp.media({
			title: 'Choose Image',
			button: {
				text: 'Choose Image'
			},
			multiple: false
		});
		custom_uploader.on('select', function() {
			attachment = custom_uploader.state().get('selection').first().toJSON();
			$('.home_bg_logo').val(attachment.url);
			$('.img_home_bg_logo').attr('src', attachment.url);
		});
		custom_uploader.open();
	});


	// remove mobile image
	remove_mob_button.click(function(e) {
		e.preventDefault();
		$('.home_mob_bg_logo').val('');
		$('.img_home_mob_bg_logo').attr('src', '');
	});

	// upload mobile image
	click_mob_elem.click(function(e) {
		custom_uploader = '';
		e.preventDefault();

		if (custom_uploader) {
			custom_uploader.open();
			return;
		}
		custom_uploader = wp.media.frames.file_frame = wp.media({
			title: 'Choose Image',
			button: {
				text: 'Choose Image'
			},
			multiple: false
		});
		custom_uploader.on('select', function() {
			attachment = custom_uploader.state().get('selection').first().toJSON();
			$('.home_mob_bg_logo').val(attachment.url);
			$('.img_home_mob_bg_logo').attr('src', attachment.url);
		});
		custom_uploader.open();
	});


	// remove poses tag image
	remove_tag_button.click(function(e) {
		e.preventDefault();
		$('.pose_cat_image_input').val('');
		$('.pose_cat_image').attr('src', '');
	});

	// poses tag image
	click_upload_tag_elem.click(function(e) {
		custom_uploader = '';
		e.preventDefault();

		if (custom_uploader) {
			custom_uploader.open();
			return;
		}
		custom_uploader = wp.media.frames.file_frame = wp.media({
			title: 'Choose Image',
			button: {
				text: 'Choose Image'
			},
			multiple: false
		});
		custom_uploader.on('select', function() {
			attachment = custom_uploader.state().get('selection').first().toJSON();
			$('.pose_cat_image_input').val(attachment.url);
			$('.pose_cat_image').attr('src', attachment.url);
		});
		custom_uploader.open();
	});

	if ( '' == $('.pose_cat_image_input').val() && '' !== $('.pose_cat_image').attr('src') ) {
		$('.pose_cat_image_input').val($('.pose_cat_image').attr('src'));
	}

	// if there is sanscrit tag name - pt it after the title
	if ( $('.sanscrit-tag-name').length ) {
		var snscrit_tag = $('.sanscrit-tag-name').remove();
		$('.term-name-wrap').after(snscrit_tag);
	}

});