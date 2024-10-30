<?php
/**
 * Country_Info class
 *
 * @package Country_Info_Visual_Editor_Button
 */

namespace Country_Info_Visual_Editor_Button;
/**
 * Class Country_Info
 */
class Country_Info {
	/**
	 * Base URL of the API
	 *
	 * @var string
	 */
	const WB_API_BASE_URL = 'http://api.worldbank.org';
	/**
	 * Default number of years to check back
	 *
	 * @var int
	 */
	const NUMBER_OF_YEARS_TO_CHECK_BACK = 5;
	/**
	 * Get Country info
	 */
	function get_country_info() {
		// Ignore the request if the current user doesn't have sufficient permissions.
		if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
			return;
		}

		// Read and sanitize the country code from the post call.
		$country_code = sanitize_text_field( $_POST['country_code'] );
		// Validate the country code.
		if ( ! isset( $country_code ) || 2 !== strlen( $country_code ) ) {
			$this->ajax_error_message( 'Invalid country code. 2 letter ISO code is expected.' );
		}

		// More validation.
		// Check if the entered country code exists in the counties array.
		if ( ! array_key_exists( strtoupper( $country_code ), Countries::get_list() ) ) {
			$this->ajax_error_message( 'Invalid country code!' );
		}

		// Start the building the info text.
		$country_info_text = '<p>';

		// Get general info about the country.
		$api_request = wp_remote_get( self::WB_API_BASE_URL . '/countries/' . $country_code . '?format=json' );
		if ( is_wp_error( $api_request ) ) {
			$this->ajax_error_message( 'Error: Can\'t connect to the API.' );
		}
		$api_result        = json_decode( wp_remote_retrieve_body( $api_request ), true );
		$country_info      = $api_result[1][0];
		$country_info_text .= '<strong>Country name: </strong>' . $country_info['name'] . '<br />';
		$country_info_text .= '<strong>Capital City: </strong>' . $country_info['capitalCity'] . '<br />';
		$country_info_text .= '<strong>ISO Code: </strong>' . $country_info['iso2Code'] . '<br />';
		$country_info_text .= '<strong>Region: </strong>' . $country_info['region']['value'] . '<br />';
		$country_info_text .= '<strong>Income Level: </strong>' . $country_info['incomeLevel']['value'] . '<br />';

		// Get the population info.
		$country_info_text .= '<strong>Population: </strong>';
		$country_info_text .= $this->get_country_indicators( $country_code, 'population', self::NUMBER_OF_YEARS_TO_CHECK_BACK );
		$country_info_text .= '<br />';

		// Get the GDP info.
		$country_info_text .= '<strong>GDP (in current USD): </strong>';
		$country_info_text .= $this->get_country_indicators( $country_code, 'gdp', self::NUMBER_OF_YEARS_TO_CHECK_BACK );
		$country_info_text .= '<br />';

		$country_info_text .= '</p>';

		// Clean the HTML result before echoing.
		$safe_tags = array(
			'br'     => array(),
			'p'      => array(),
			'strong' => array(),
		);
		echo wp_kses( $country_info_text, $safe_tags );

		// Terminate immediately and return a proper response.
		wp_die();
	}

	/**
	 * Returns country indicators for number of years as a string with comma separated values
	 *
	 * @param string $country_code Country Code.
	 * @param string $indicator Indicator.
	 * @param int    $number_of_years Number of years to check.
	 *
	 * @return string
	 */
	function get_country_indicators( $country_code, $indicator, $number_of_years ) {
		$current_year = date( 'Y' );
		$start_year   = $current_year - $number_of_years;

		$indicators_api_url = '';
		switch ( $indicator ) {
			case 'population':
				$indicators_api_url = self::WB_API_BASE_URL . '/countries/' . $country_code .
					'/indicators/SP.POP.TOTL?format=json&date=' . $start_year . ':' . $current_year;
				break;
			case 'gdp':
				$indicators_api_url = self::WB_API_BASE_URL . '/countries/' . $country_code .
					'/indicators/NY.GDP.MKTP.CD?format=json&date=' . $start_year . ':' . $current_year;
				break;
		}

		$api_request = wp_remote_get( $indicators_api_url );
		if ( is_wp_error( $api_request ) ) {
			return 'Error: Can\'t connect to the API.';
		}
		$api_result = json_decode( wp_remote_retrieve_body( $api_request ), true );
		$indicators = $api_result[1];

		$formatted_array = array();
		foreach ( $indicators as $indicator ) {
			$value             = empty( $indicator['value'] ) ? 'N/A' : number_format( $indicator['value'] );
			$formatted_array[] = sprintf( '%s (%s)', $value, $indicator['date'] );
		}

		return implode( ', ', $formatted_array );
	}

	/**
	 * Echos a message as an AJAX response and dies immediately.
	 *
	 * @param string $message Error Message.
	 */
	function ajax_error_message( $message ) {
		echo '<p><strong>' . esc_html( $message ) . '</strong></p>';
		wp_die();
	}
}

