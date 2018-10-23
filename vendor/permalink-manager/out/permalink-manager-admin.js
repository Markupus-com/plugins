jQuery(document).ready(function() {

	/**
	 * "(Un)select all" checkboxes
	 */
	var checkbox_actions = ['select_all', 'unselect_all'];
 	checkbox_actions.forEach(function(element) {
		jQuery('#permalink-manager .' + element).on('click', function() {
			jQuery(this).parents('.field-container').find('.checkboxes input[type="checkbox"]').each(function() {
				var action = (element == 'select_all') ? true : false;
				jQuery(this).prop('checked', action);
			});

			return false;
		});
	});

	jQuery('#permalink-manager .checkboxes label').not('input').on('click', function(ev) {
		var input = jQuery(this).find("input");
		if(!jQuery(ev.target).is("input")) {
			input.prop('checked', !(input.prop("checked")));
		}
	});

	/**
	 * Filter by dates in "Permalink editor"
	 */
	jQuery('#permalink-manager #months-filter-button').on('click', function() {
		var filter_name = jQuery("#months-filter-select").attr('name');
		var filter_value = jQuery("#months-filter-select").val();
		var url = jQuery(this).parent().data('filter-url');

		if(filter_name != '' && filter_value != '' && url != ''){
			document.location.href = url + "&" + filter_name + "=" + filter_value;
		}
		return false;
	});

	/**
	 * Filter by content types in "Tools"
	 */
	jQuery('#permalink-manager *[data-field="content_type"] select').on('change', function() {
		var content_type = jQuery(this).val();
		if(content_type == 'post_types') {
			jQuery(this).parents('.form-table').find('*[data-field="post_types"],*[data-field="post_statuses"]').removeClass('hidden');
			jQuery(this).parents('.form-table').find('*[data-field="taxonomies"]').addClass('hidden');
		} else {
			jQuery(this).parents('.form-table').find('*[data-field="post_types"],*[data-field="post_statuses"]').addClass('hidden');
			jQuery(this).parents('.form-table').find('*[data-field="taxonomies"]').removeClass('hidden');
		}
	}).trigger("change");

	/**
	 * Toggle "Edit URI" box
	 */
	jQuery('#permalink-manager-toggle, .permalink-manager-edit-uri-box .close-button').on('click', function() {
		jQuery('.permalink-manager-edit-uri-box').slideToggle();

		return false;
	});

	/**
	 * Toggle "Edit Redirects" box
	 */
	jQuery('#toggle-redirect-panel').on('click', function() {
		jQuery('#redirect-panel-inside').slideToggle();

		return false;
	});

	jQuery('.permalink-manager.redirects-panel #permalink-manager-new-redirect').on('click', function() {
		// Find the table
		var table = jQuery(this).parents('.redirects-panel').find('table');

		// Copy the row from the sample
		var new_row = jQuery(this).parents('.redirects-panel').find('.sample-row').clone().removeClass('sample-row');

		// Adjust the array key
		var last_key = jQuery(table).find("tr:last-of-type input[data-index]").data("index") + 1;
		jQuery("input[data-index]", new_row).attr("data-index", last_key).attr("name", function(){ return jQuery(this).attr("name") + "[" + last_key + "]" });

		// Append the new row
		jQuery(table).append(new_row);

		return false;
	});

	jQuery('.permalink-manager.redirects-panel').on('click', '.remove-redirect', function() {
		var table = jQuery(this).closest('tr').remove();
		return false;
	});

	/**
	 * Synchronize "Edit URI" input field with the sample permalink
	 */
	var custom_uri_input = jQuery('.permalink-manager-edit-uri-box input[name="custom_uri"]');
	jQuery(custom_uri_input).on('keyup change', function() {
		jQuery('.sample-permalink-span .editable').text(jQuery(this).val());
	});

	/**
	 * Synchronize "Coupon URI" input field with the final permalink
	 */
	jQuery('#permalink-manager-coupon-url input[name="custom_uri"]').on('keyup change', function() {
		var uri = jQuery(this).val();
		jQuery('#permalink-manager-coupon-url code span').text(uri);

		if(!uri) {
			jQuery('#permalink-manager-coupon-url .coupon-full-url').addClass("hidden");
		} else {
			jQuery('#permalink-manager-coupon-url .coupon-full-url').removeClass("hidden");
		}
	});

	function permalink_manager_duplicate_check(custom_uri_input, multi) {
		// Set default values
		custom_uri_input = typeof custom_uri_input !== 'undefined' ? custom_uri_input : false;
  	multi = typeof multi !== 'undefined' ? multi : false;

		var all_custom_uris_values = {};

		if(custom_uri_input) {
			var custom_uri = jQuery(custom_uri_input).val();
			var element_id = jQuery(custom_uri_input).attr("data-element-id");

			all_custom_uris_values[element_id] = custom_uri;
		} else {
			jQuery('.custom_uri').each(function(i, obj) {
				var field_name = jQuery(obj).attr('data-element-id');
			  all_custom_uris_values[field_name] = jQuery(obj).val();
			});
		}

		if(all_custom_uris_values) {
			jQuery.ajax(permalink_manager.ajax_url, {
				type: 'POST',
				async: true,
				data: {
					action: 'detect_duplicates',
					custom_uris: all_custom_uris_values
				},
				success: function(data) {
					if(data.length > 5) {
						var results = JSON.parse(data);

						// Loop through results
						jQuery.each(results, function(key, is_duplicate) {
							var alert_container = jQuery('.custom_uri[data-element-id="' + key + '"]').parents('.custom_uri_container').find('.duplicated_uri_alert');

							if(is_duplicate) {
								jQuery(alert_container).text(is_duplicate);
							} else {
								jQuery(alert_container).empty();
							}
						});
					}
				}
			});
		}
	}

	/**
	 * Check if a single custom URI is not duplicated
	 */
	var custom_uri_check_timeout = null;
	jQuery('.custom_uri_container input[name="custom_uri"], .custom_uri_container input.custom_uri').each(function() {
		var input = this;

		jQuery(this).on('keyup change', function() {
			clearTimeout(custom_uri_check_timeout);

			// Wait until user finishes typing
	    custom_uri_check_timeout = setTimeout(function() {
				permalink_manager_duplicate_check(input);
	    }, 500);
		});

	});

	/**
	 * Check if a all any of displayed custom URIs is not duplicated
	 */
	if(jQuery('.custom_uri').length > 0) {
		permalink_manager_duplicate_check(false, true);
	}

	/**
	 * Disable "Edit URI" input if URI should be updated automatically
	 */
	jQuery('.permalink-manager-edit-uri-box select[name="auto_update_uri"]').on('change', function() {
		var selected = jQuery(this).find('option:selected');
		var auto_update_status = jQuery(selected).data('auto-update');

		if(auto_update_status == 1) {
			jQuery(custom_uri_input).attr("readonly", true);
		} else {
			jQuery(custom_uri_input).removeAttr("readonly", true);
		}
	}).trigger("change");

	/**
	 * Restore "Default URI"
	 */
	jQuery('#permalink-manager .restore-default').on('click', function() {
		var input = jQuery(this).parents('.field-container, .permalink-manager-edit-uri-box').find('input[data-default]');
		jQuery(input).val(jQuery(input).data('default')).trigger('keyup');
		return false;
	});

	/**
	 * Hide global admin notices
	 */
	jQuery(document).on('click', '.permalink-manager-notice.is-dismissible .notice-dismiss', function() {
		var alert_id = jQuery(this).closest('.permalink-manager-notice').data('alert_id');

		jQuery.ajax(permalink_manager.ajax_url, {
			type: 'POST',
			data: {
				action: 'dismissed_notice_handler',
				alert_id: alert_id,
			}
		});
	});

	/**
	 * Help tooltips
	 */
	new Tippy('#permalink-manager .help_tooltip', {
		position: 'top-start',
		arrow: true,
		theme: 'tippy-pm',
		distance: 20,
	});

	/**
	 * Stop-words
	 */
	var stop_words_input = '#permalink-manager .field-container textarea.stop_words';

	if(jQuery(stop_words_input).length > 0) {
		var stop_words = new TIB(document.querySelector(stop_words_input), {
			alert: false,
			escape: null,
			classes: ['tags words-editor', 'tag', 'tags-input', 'tags-output', 'tags-view'],
		});
		jQuery('.tags-output').hide();

		// Force lowercase
		stop_words.filter = function(text) {
			return text.toLowerCase();
		};

		// Remove all words
		jQuery('#permalink-manager .field-container .clear_all_words').on('click', function() {
			stop_words.reset();
		});

		// Load stop-words list
		jQuery('#permalink-manager #load_stop_words_button').on('click', function() {
			var lang = jQuery( ".load_stop_words option:selected" ).val();
			if(lang) {
				var json_url = permalink_manager.url + "/includes/ext/stopwords-json/dist/" + lang + ".json";

				// Load JSON with words list
				jQuery.getJSON(json_url, function(data) {
				  var new_words = [];

				  jQuery.each(data, function(key, val) {
				    new_words.push(val);
				  });

				  stop_words.update(new_words);
				});
			}

			return false;
		});
	}

	/**
	 * Quick Edit
	 */
	if(typeof inlineEditPost !== "undefined") {
		var inline_post_editor = inlineEditPost.edit;
		inlineEditPost.edit = function(id) {
			inline_post_editor.apply(this, arguments);

			// Get the Post ID
			var post_id = 0;
			if(typeof(id) == 'object') {
				post_id = parseInt(this.getId(id));
			}

			if(post_id != 0) {
				// Get the row & "Custom URI" field
				custom_uri_field = jQuery('#edit-' + post_id).find('.custom_uri');

				// Prepare the Custom URI
				custom_uri = jQuery("#post-" + post_id).find(".column-permalink-manager-col").text();

				// Fill with the Custom URI
				custom_uri_field.val(custom_uri);

				// Set the element ID
				jQuery('#edit-' + post_id).find('.permalink-manager-edit-uri-element-id').val(post_id);
			}
		}
	}

	if(typeof inlineEditTax !== "undefined") {
		var inline_tax_editor = inlineEditTax.edit;
		inlineEditTax.edit = function(id) {
			inline_tax_editor.apply(this, arguments);

			// Get the Post ID
			var term_id = 0;
			if(typeof(id) == 'object') {
				term_id = parseInt(this.getId(id));
			}

			if(term_id != 0) {
				// Get the row & "Custom URI" field
				custom_uri_field = jQuery('#edit-' + term_id).find('.custom_uri');

				// Prepare the Custom URI
				custom_uri = jQuery("#tag-" + term_id).find(".column-permalink-manager-col").text();

				// Fill with the Custom URI
				custom_uri_field.val(custom_uri);

				// Set the element ID
				jQuery('#edit-' + term_id).find('.permalink-manager-edit-uri-element-id').val("tax-" + term_id);
			}
		}
	}

});
