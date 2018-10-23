<?php

/**
* Third parties integration
*/
class Permalink_Manager_Third_Parties extends Permalink_Manager_Class {

	public function __construct() {
		add_action('init', array($this, 'init_hooks'), 99);
	}

	function init_hooks() {
		global $sitepress_settings, $permalink_manager_options, $polylang;

		// 1. WPML & Polylang
		if($sitepress_settings || !empty($polylang->links_model->options)) {
			// Detect Post/Term function
			if(!empty($permalink_manager_options['general']['fix_language_mismatch'])) {
				add_filter('permalink-manager-detected-post-id', array($this, 'wpml_language_mismatch_fix'), 9, 3);
				add_filter('permalink-manager-detected-term-id', array($this, 'wpml_language_mismatch_fix'), 9, 3);
			}

			// URI Editor
			add_filter('permalink-manager-uri-editor-extra-info', array($this, 'wpml_lang_column_content_uri_editor'), 9, 3);

			if((isset($sitepress_settings['language_negotiation_type']) && $sitepress_settings['language_negotiation_type'] == 1) || (isset($polylang->links_model->options['force_lang']) && in_array($polylang->links_model->options['force_lang'], array(1, 3)))) {
				add_filter('permalink-manager-detect-uri', array($this, 'wpml_detect_post'), 9, 3);
				add_filter('permalink_manager-filter-permalink-base', array($this, 'wpml_append_lang_prefix'), 9, 2);
				add_filter('template_redirect', array($this, 'wpml_redirect'), 0, 998 );
			} else if(isset($sitepress_settings['language_negotiation_type']) && $sitepress_settings['language_negotiation_type'] == 3) {
				add_filter('permalink-manager-detect-uri', array($this, 'wpml_ignore_lang_query_parameter'), 9);
			}

			// Translate slugs
			if(class_exists('WPML_Slug_Translation')) {
				add_filter('permalink_manager_filter_post_type_slug', array($this, 'wpml_translate_post_type_slug'), 9, 3);
				// add_filter('permalink_manager_filter_taxonomy_slug', array($this, 'wpml_translate_taxonomy_slug'), 9, 3);
			}
		}

		// 2. AMP
		if(defined('AMP_QUERY_VAR')) {
			// Detect AMP endpoint
			add_filter('permalink-manager-detect-uri', array($this, 'detect_amp'), 10, 2);
			add_filter('request', array($this, 'enable_amp'), 10, 1);
		}

		// 4. WP All Import
		add_action('pmxi_after_xml_import', array($this, 'pmxi_fix_permalinks'), 10);

		// 5. WooCommerce
		if(class_exists('WooCommerce')) {
			add_filter('request', array($this, 'woocommerce_detect'), 9, 1);
			add_filter('template_redirect', array($this, 'woocommerce_checkout_fix'), 9);

			if(class_exists('WooCommerce') && class_exists('Permalink_Manager_Pro_Functions')) {
				if(is_admin()){
					add_filter('woocommerce_coupon_data_tabs', 'Permalink_Manager_Pro_Functions::woocommerce_coupon_tabs');
					add_action('woocommerce_coupon_data_panels', 'Permalink_Manager_Pro_Functions::woocommerce_coupon_panel');
					add_action('woocommerce_coupon_options_save', 'Permalink_Manager_Pro_Functions::woocommerce_save_coupon_uri', 9, 2);
				}
				add_filter('request', 'Permalink_Manager_Pro_Functions::woocommerce_detect_coupon_code', 1, 1);
				add_filter('permalink-manager-disabled-post-types', 'Permalink_Manager_Pro_Functions::woocommerce_coupon_uris', 9, 1);
			}
		}

		// 6. Theme My Login
		if(class_exists('Theme_My_Login')) {
			add_filter('permalink_manager_filter_final_post_permalink', array($this, 'tml_keep_query_parameters'), 9, 3);
		}

		// 7. Yoast SEO
		add_filter('wpseo_xml_sitemap_post_url', array($this, 'yoast_fix_sitemap_urls'));

		// 8. WooCommerce Wishlist Plugin
		if(function_exists('tinv_get_option')) {
			add_filter('permalink-manager-detect-uri', array($this, 'ti_woocommerce_wishlist_uris'), 15, 3);
		}
	}

	/**
	 * 1. WPML filters
	 */
	public static function wpml_get_language_code($element) {
		if(isset($element->post_type)) {
			$element_id = $element->ID;
			$element_type = $element->post_type;
		} else if(isset($element->taxonomy)) {
			$element_id = $element->term_taxonomy_id;
			$element_type = $element->taxonomy;
		} else {
			return false;
		}

		$lang_code = apply_filters('wpml_element_language_code', null, array('element_id' => $element_id, 'element_type' => $element_type));

		// Use default language if nothing detected
		return ($lang_code) ? $lang_code : self::get_default_language();
	}

	public static function get_default_language() {
		global $sitepress;

		if(function_exists('pll_default_language')) {
			$def_lang = pll_default_language('slug');
		} else if(is_object($sitepress)) {
			$def_lang = $sitepress->get_default_language();
		} else {
			$def_lang = '';
		}

		return $def_lang;
	}

	function wpml_language_mismatch_fix($item_id, $uri_parts, $is_term = false) {
		global $wp, $language_code;

		if($is_term) {
			$element = get_term($item_id);
			if(!empty($element) && !is_wp_error($element)) {
				$element_id = $element->term_taxonomy_id;
			} else {
				return false;
			}
		} else {
			$element = get_post($item_id);
			$element_type = $element->post_type;
			$element_id = $item_id;
		}

		// Stop if no term or post is detected
		if(empty($element)) { return false; }

		$language_code = self::wpml_get_language_code($element);

		if(!empty($uri_parts['lang']) && ($uri_parts['lang'] != $language_code)) {
			$wpml_item_id = apply_filters('wpml_object_id', $element_id);
			$item_id = (is_numeric($wpml_item_id)) ? $wpml_item_id : $item_id;
		}

		return $item_id;
	}

	function wpml_detect_post($uri_parts, $request_url, $endpoints) {
		global $sitepress, $sitepress_settings, $polylang;

		if(!empty($sitepress_settings['active_languages'])) {
			$languages_list = implode("|", $sitepress_settings['active_languages']);
			$default_language = $sitepress->get_default_language();
		} elseif(function_exists('pll_languages_list')) {
			$languages_array = pll_languages_list();
			$languages_list = (is_array($languages_array)) ? implode("|", $languages_array) : "";
			$default_language = pll_default_language();
		}

		// Fix for multidomain language configuration
		if(!empty($polylang->options['force_lang']) && $polylang->options['force_lang'] == 3) {
			$domains = (array) $polylang->options['domains'];

			foreach($domains as &$domain) {
				$domain = preg_replace("/http(s)?:\/\/(www\.)?(.+?)\/?$/", "http://$3", $domain);
			}

			$request_url = trim(str_replace($domains, "", $request_url), "/");
		}

		if(!empty($languages_list)) {
			//preg_match("/^(?:({$languages_list})\/)?(.+?)(?|\/({$endpoints})[\/$]([^\/]*)|\/()([\d+]))?\/?$/i", $request_url, $regex_parts);
			preg_match("/^(?:({$languages_list})\/)?(.+?)(?|\/({$endpoints})(?|\/(.*)|$)|\/()([\d]+)\/?)?$/i", $request_url, $regex_parts);

			$uri_parts['lang'] = (!empty($regex_parts[1])) ? $regex_parts[1] : $default_language;
			$uri_parts['uri'] = (!empty($regex_parts[2])) ? $regex_parts[2] : "";
			$uri_parts['endpoint'] = (!empty($regex_parts[3])) ? $regex_parts[3] : "";
			$uri_parts['endpoint_value'] = (!empty($regex_parts[4])) ? $regex_parts[4] : "";
		}

		return $uri_parts;
	}

	function wpml_append_lang_prefix($base, $element) {
		global $sitepress_settings, $polylang, $permalink_manager_uris;

		$language_code = self::wpml_get_language_code($element);
		$default_language_code = self::get_default_language();

		// Last instance - use language paramater from &_GET array
		if(is_admin()) {
			$language_code = (empty($language_code) && !empty($_GET['lang'])) ? $_GET['lang'] : $language_code;
		}

		// Adjust URL base
		if(!empty($language_code)) {
			// A. Different domain per language
			if(!empty($polylang->options['force_lang']) && $polylang->options['force_lang'] == 3) {
				$domains = $polylang->options['domains'];

				$is_term = (!empty($element->term_taxonomy_id)) ? true : false;
				$element_id = ($is_term) ? "tax-{$element->term_taxonomy_id}" : $element->ID;

				// Filter only custom permalinks
				if(empty($permalink_manager_uris[$element_id]) || empty($domains)) { return $base; }

				// Replace the domain name
				if(!empty($domains[$language_code])) {
					$base = trim($domains[$language_code], "/");
				}
			}
			// B. Append prefix
			else {
				// Hide language code if "Use directory for default language" option is enabled
				$hide_prefix_for_default_lang = ((isset($sitepress_settings['urls']['directory_for_default_language']) && $sitepress_settings['urls']['directory_for_default_language'] != 1) || !empty($polylang->links_model->options['hide_default'])) ? true : false;

				if($hide_prefix_for_default_lang && ($default_language_code == $language_code)) {
					return $base;
				} else {
					$base .= "/{$language_code}";
				}
			}
		}

		return $base;
	}

	function wpml_translate_post_type_slug($post_type_slug, $element, $post_type) {
		$translations = WPML_Slug_Translation::get_translations($post_type);

		if(empty($translations[1])) { return $post_type_slug; }

		$post = (is_integer($element)) ? get_post($element) : $element;
		$language_code = self::wpml_get_language_code($post);

		// Translate %post_type% tag in custom permastructures
		return (!empty($translations[1][$language_code]['value'])) ? $translations[1][$language_code]['value'] : $post_type_slug;
	}

	function wpml_lang_column_uri_editor($columns) {
		if(class_exists('SitePress') || class_exists('Polylang')) {
			$columns['post_lang'] = __('Language', 'permalink-manager');
		}

		return $columns;
	}

	function wpml_lang_column_content_uri_editor($output, $column, $element) {
		$language_code = self::wpml_get_language_code($element);
		$output .= (!empty($language_code)) ? sprintf(" | <span><strong>%s:</strong> %s</span>", __("Language"), $language_code) : "";

		return $output;
	}

	function wpml_ignore_lang_query_parameter($uri_parts) {
		global $permalink_manager_uris;

		foreach($permalink_manager_uris as &$uri) {
			$uri = trim(strtok($uri, '?'), "/");
		}

		return $uri_parts;
	}

	function wpml_redirect() {
		global $language_code, $wp_query;

		if(!empty($language_code) && defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE != $language_code && !empty($wp_query->query['do_not_redirect'])) {
			unset($wp_query->query['do_not_redirect']);
		}
	}

	/**
	 * 2. AMP hooks
	 */
	function detect_amp($uri_parts, $request_url) {
		global $amp_enabled;
		$amp_query_var = AMP_QUERY_VAR;

		// Check if AMP should be triggered
		preg_match("/^(.+?)\/({$amp_query_var})?\/?$/i", $uri_parts['uri'], $regex_parts);
		if(!empty($regex_parts[2])) {
			$uri_parts['uri'] = $regex_parts[1];
			$amp_enabled = true;
		}

		return $uri_parts;
	}

	function enable_amp($query) {
		global $amp_enabled;

		if(!empty($amp_enabled)) {
			$query[AMP_QUERY_VAR] = 1;
		}

		return $query;
	}

	/**
	 * 3. Custom Permalinks
	 */
	public static function custom_permalinks_uris() {
		global $wpdb;

		$custom_permalinks_uris = array();

	  // 1. List tags/categories
	  $table = get_option('custom_permalink_table');
	  if($table && is_array($table)) {
	    foreach ( $table as $permalink => $info ) {
	      $custom_permalinks_uris[] = array(
					'id' => "tax-" . $info['id'],
					'uri' => trim($permalink, "/")
				);
	    }
	  }

	  // 2. List posts/pages
	  $query = "SELECT p.ID, m.meta_value FROM $wpdb->posts AS p LEFT JOIN $wpdb->postmeta AS m ON (p.ID = m.post_id)  WHERE m.meta_key = 'custom_permalink' AND m.meta_value != '';";
	  $posts = $wpdb->get_results($query);
	  foreach($posts as $post) {
	    $custom_permalinks_uris[] = array(
				'id' => $post->ID,
				'uri' => trim($post->meta_value, "/"),
			);
	  }

		return $custom_permalinks_uris;
	}

	static public function import_custom_permalinks_uris() {
		global $permalink_manager_uris, $permalink_manager_before_sections_html;

		$custom_permalinks_plugin = 'custom-permalinks/custom-permalinks.php';

		if(is_plugin_active($custom_permalinks_plugin) && !empty($_POST['disable_custom_permalinks'])) {
			deactivate_plugins($custom_permalinks_plugin);
		}

		// Get a list of imported URIs
		$custom_permalinks_uris = self::custom_permalinks_uris();

		if(!empty($custom_permalinks_uris) && count($custom_permalinks_uris) > 0) {
			foreach($custom_permalinks_uris as $item) {
				$permalink_manager_uris[$item['id']] = $item['uri'];
			}

			$permalink_manager_before_sections_html .= Permalink_Manager_Admin_Functions::get_alert_message(__( '"Custom Permalinks" URIs were imported!', 'permalink-manager' ), 'updated');
			update_option('permalink-manager-uris', $permalink_manager_uris);
		} else {
			$permalink_manager_before_sections_html .= Permalink_Manager_Admin_Functions::get_alert_message(__( 'No "Custom Permalinks" URIs were imported!', 'permalink-manager' ), 'error');
		}
	}

	/**
	 * 4. WP All Import
	 */
	function pmxi_fix_permalinks($import_id) {
		global $permalink_manager_uris, $wpdb;

		$post_ids = $wpdb->get_col($wpdb->prepare("SELECT post_id FROM {$wpdb->prefix}pmxi_posts WHERE import_id = %s", $import_id));

 		// Just in case
 		sleep(3);

 		if(array($post_ids)) {
 			foreach($post_ids as $id) {
				// Continue only if no custom URI is already assigned
				if(!empty($permalink_manager_uris[$id])) { continue; }

				// Get default post URI
 			  $new_uri = Permalink_Manager_URI_Functions_Post::get_default_post_uri($id);
 			  $permalink_manager_uris[$id] = $new_uri;
 			}
 		}

 	  update_option('permalink-manager-uris', $permalink_manager_uris);
 	}

	/**
	 * 5. WooCommerce
	 */
	function woocommerce_detect($query) {
		global $woocommerce, $pm_query;

		$shop_page_id = get_option('woocommerce_shop_page_id');

		// WPML - translate shop page id
		$shop_page_id = apply_filters('wpml_object_id', $shop_page_id, 'page', TRUE);

		// Fix shop page
		if(!empty($pm_query['id']) && is_numeric($pm_query['id']) && $shop_page_id == $pm_query['id']) {
			$query['post_type'] = 'product';
			unset($query['pagename']);
		}

		// Fix WooCommerce pages
		if(!empty($woocommerce->query->query_vars)) {
			$query_vars = $woocommerce->query->query_vars;

			foreach($query_vars as $key => $val) {
				if(isset($query[$key])) {
					$woocommerce_page = true;
					$query['do_not_redirect'] = 1;
					break;
				}
			}
		}

		return $query;
	}

	function woocommerce_checkout_fix() {
		global $wp_query, $pm_query, $permalink_manager_options;

		// Redirect from Shop archive to selected page
		if(is_shop() && empty($pm_query['id'])) {
			$redirect_mode = (!empty($permalink_manager_options['general']['redirect'])) ? $permalink_manager_options['general']['redirect'] : false;
			$redirect_shop = apply_filters('permalink-manager-redirect-shop-archive', false);
			$shop_page = get_option('woocommerce_shop_page_id');

			if($redirect_mode && $redirect_shop && $shop_page && empty($wp_query->query_vars['s'])) {
				$shop_url = get_permalink($shop_page);
				wp_safe_redirect($shop_url, $redirect_mode);
				exit();
			}
		}

		// Do not redirect "thank you" & another WooCommerce pages
		if(is_checkout() || is_wc_endpoint_url()) {
			$wp_query->query_vars['do_not_redirect'] = 1;
		}
	}

	/**
	 * 6. Theme My Login
	 */
	function tml_keep_query_parameters($permalink, $post, $old_permalink) {
		// Get the query string from old permalink
		$get_parameters = (($pos = strpos($old_permalink, "?")) !== false) ? substr($old_permalink, $pos) : "";

		return $permalink . $get_parameters;
	}

	/**
	 * 7. Fix Yoast's homepage URL
	 */
	function yoast_fix_sitemap_urls($permalink) {
		if(class_exists('WPSEO_Utils')) {
			$home_url = WPSEO_Utils::home_url();
			$home_protocol = parse_url($home_url, PHP_URL_SCHEME);

			$permalink = preg_replace("/http(s)?/", $home_protocol, $permalink);
		}

		return $permalink;
	}

	/**
	 * 8. Support WooCommerce Wishlist Plugin
	 */
	function ti_woocommerce_wishlist_uris($uri_parts, $request_url, $endpoints) {
		global $permalink_manager_uris, $wp;

		$wishlist_pid = tinv_get_option('general', 'page_wishlist');

		// Find the Wishlist page URI
		if(is_numeric($wishlist_pid) && !empty($permalink_manager_uris[$wishlist_pid])) {
			$wishlist_uri = preg_quote($permalink_manager_uris[$wishlist_pid], '/');

			// Extract the Wishlist ID
			preg_match("/^({$wishlist_uri})\/([^\/]+)\/?$/", $uri_parts['uri'], $output_array);

			if(!empty($output_array[2])) {
				$uri_parts['uri'] = $output_array[1];
				$uri_parts['endpoint'] = 'tinvwlID';
				$uri_parts['endpoint_value'] = $output_array[2];
			}
		}

		return $uri_parts;
	}

}
?>
