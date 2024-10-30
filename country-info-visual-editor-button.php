<?php
/**
 * Plugin Name: Country Info Visual Editor (WYSIWYG) Button
 * Plugin URI: https://github.com/behzod/Country-Info-Visual-Editor-WYSIWYG-Button
 * Description: Adds a visual editor button for pulling country info from The World Bank API.
 * Version: 1.0.1
 * Author: Behzod Saidov
 * Author URI: https://github.com/behzod
 *
 * @package Country_Info_Visual_Editor_Button
 */

namespace Country_Info_Visual_Editor_Button;
require_once 'lib/class-countries.php';
require_once 'lib/class-country-info.php';

// AJAX endpoint.
$country_info = new Country_Info();
add_action( 'wp_ajax_ci_get_country_info', array( $country_info, 'get_country_info' ) );

// Add the TinyMCE button if the user can edit posts/pages and the the visual editor is not disabled in the user profile.
add_action(
	'admin_init', function () {
		if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
			return;
		}
		if ( 'true' !== get_user_option( 'rich_editing' ) ) {
			return;
		}
		add_filter( 'mce_external_plugins', function ( $plugin_array ) {
			$plugin_array['country_info_visual_editor_button'] = plugin_dir_url( __FILE__ ) . 'assets/button.js';
			return $plugin_array;
		} );
		add_filter( 'mce_buttons', function( $buttons ) {
			array_push( $buttons, 'country_code' );
			return $buttons;
		} );
	}
);
