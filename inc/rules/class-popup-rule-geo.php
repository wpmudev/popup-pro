<?php
/*
Name:        Visitor Location
Plugin URI:  http://premium.wpmudev.org/project/the-pop-over-plugin/
Description: Conditions based on the location of the visitor.
Author:      Philipp (Incsub)
Author URI:  http://premium.wpmudev.org
Type:        Rule
Rules:       In a specific Country, Not in a specific Country
Limit:       pro
Version:     1.0

NOTE: DON'T RENAME THIS FILE!!
This filename is saved as metadata with each popup that uses these rules.
Renaming the file will DISABLE the rules, which is very bad!
*/


class IncPopupRule_Geo extends IncPopupRule {

	/**
	 * Initialize the rule object.
	 *
	 * @since  4.6
	 */
	protected function init() {
		$this->filename = basename( __FILE__ );

		// 'country' rule.
		$this->add_rule(
			'country',
			__( 'In a specific Country', 'popover' ),
			__( 'Shows the PopUp if the user is in a certain country.', 'popover' ),
			'no_country',
			25
		);

		// 'no_country' rule.
		$this->add_rule(
			'no_country',
			__( 'Not in a specific Country', 'popover' ),
			__( 'Shows the PopUp if the user is not in a certain country.', 'popover' ),
			'country',
			25
		);

		add_action(
			'popup-ajax-test-geo',
			array( $this, 'ajax_test_geo' )
		);
	}


	/*=============================*\
	=================================
	==                             ==
	==           COUNTRY           ==
	==                             ==
	=================================
	\*=============================*/


	/**
	 * Apply the rule-logic to the specified popup
	 *
	 * @since  4.6
	 * @param  mixed $data Rule-data which was saved via the save_() handler.
	 * @return bool Decission to display popup or not.
	 */
	protected function apply_country( $data ) {
		if ( is_string( $data ) ) { $data = array( $data ); }
		if ( ! is_array( $data ) ) { return true; }

		return $this->test_country( $data );
	}

	/**
	 * Output the Admin-Form for the active rule.
	 *
	 * @since  4.6
	 * @param  mixed $data Rule-data which was saved via the save_() handler.
	 */
	protected function form_country( $data ) {
		if ( is_string( $data ) ) { $data = array( $data ); }
		if ( ! is_array( $data ) ) { $data = array(); }
		$countries = $this->country_list();

		?>
		<label for="po-rule-data-country">
			<?php _e( 'Included countries:', 'popover' ); ?>
		</label>
		<select name="po_rule_data[country][]"
			id="po-rule-data-country"
			multiple="multiple"
			placeholder="<?php _e( 'Click here to select a country', 'popover' ); ?>">
			<?php foreach ( $countries as $code => $name ) :
				?><option value="<?php echo esc_attr( $code ); ?>" <?php
					selected( in_array( $code, $data ) );
					?>> <?php
					echo esc_attr( $name );
				?></option><?php
			endforeach; ?>
		</select>
		<?php
	}

	/**
	 * Update and return the $settings array to save the form values.
	 *
	 * @since  4.6
	 * @param  array $data The contents of $_POST['po_rule_data'].
	 * @return mixed Data collection of this rule.
	 */
	protected function save_country( $data ) {
		lib3()->array->equip( $data, 'country' );
		return $data['country'];
	}


	/*================================*\
	====================================
	==                                ==
	==           NO_COUNTRY           ==
	==                                ==
	====================================
	\*================================*/


	/**
	 * Apply the rule-logic to the specified popup
	 *
	 * @since  4.6
	 * @param  mixed $data Rule-data which was saved via the save_() handler.
	 * @return bool Decission to display popup or not.
	 */
	protected function apply_no_country( $data ) {
		if ( is_string( $data ) ) { $data = array( $data ); }
		if ( ! is_array( $data ) ) { return true; }

		return ! $this->test_country( $data );
	}

	/**
	 * Output the Admin-Form for the active rule.
	 *
	 * @since  4.6
	 * @param  mixed $data Rule-data which was saved via the save_() handler.
	 */
	protected function form_no_country( $data ) {
		if ( is_string( $data ) ) { $data = array( $data ); }
		if ( ! is_array( $data ) ) { $data = array(); }
		$countries = $this->country_list();

		?>
		<label for="po-rule-data-no-country">
			<?php _e( 'Excluded countries:', 'popover' ); ?>
		</label>
		<select name="po_rule_data[no_country][]"
			id="po-rule-data-no-country"
			multiple="multiple"
			placeholder="<?php _e( 'Click here to select a country', 'popover' ); ?>">
			<?php foreach ( $countries as $code => $name ) :
				?><option value="<?php echo esc_attr( $code ); ?>" <?php
					selected( in_array( $code, $data ) );
					?>> <?php
					echo esc_attr( $name );
				?></option><?php
			endforeach; ?>
		</select>
		<?php
	}

	/**
	 * Update and return the $settings array to save the form values.
	 *
	 * @since  4.6
	 * @return mixed Data collection of this rule.
	 */
	protected function save_no_country( $data ) {
		lib3()->array->equip( $data, 'no_country' );
		return $data['no_country'];
	}


	/*======================================*\
	==========================================
	==                                      ==
	==           HELPER FUNCTIONS           ==
	==                                      ==
	==========================================
	\*======================================*/


	/**
	 * Tries to get the public IP address of the current user.
	 *
	 * @since  4.6
	 * @return string The IP Address
	 */
	protected function get_users_ip() {
		static $Ip_address = null;

		if ( null === $Ip_address ) {
			if ( getenv( 'HTTP_CLIENT_IP' ) ) {
				$Ip_address = getenv( 'HTTP_CLIENT_IP' );
			} else if ( getenv( 'HTTP_X_FORWARDED_FOR' ) ) {
				$Ip_address = getenv( 'HTTP_X_FORWARDED_FOR' );
			} else if ( getenv( 'HTTP_X_FORWARDED' ) ) {
				$Ip_address = getenv( 'HTTP_X_FORWARDED' );
			} else if ( getenv( 'HTTP_FORWARDED_FOR' ) ) {
				$Ip_address = getenv( 'HTTP_FORWARDED_FOR' );
			} else if ( getenv( 'HTTP_FORWARDED' ) ) {
				$Ip_address = getenv( 'HTTP_FORWARDED' );
			} else if ( getenv( 'REMOTE_ADDR' ) ) {
				$Ip_address = getenv( 'REMOTE_ADDR' );
			} else {
				$Ip_address = 'UNKNOWN';
			}
		}

		return $Ip_address;
	}

	/**
	 * Checks if the users IP address belongs to a certain country.
	 *
	 * @since  4.6
	 * @return bool
	 */
	protected function get_user_country() {
		// Grab the users IP address
		$ip = $this->get_users_ip();
		$country = false;

		// See if an add-on provides the country for us.
		$country = apply_filters( 'popup-get-country', $country, $ip );

		if ( empty( $country ) ) {
			// Next check the local caching-table.
			$country = IncPopupDatabase::get_country( $ip );
		}

		if ( empty( $country ) ) {
			$service = $this->get_service();

			// Finally use the external API to find the country.
			$country = $this->country_from_api( $ip, $service );

			// Locally cache the API result.
			if ( ! empty( $country ) ) {
				IncPopupDatabase::add_ip( $ip, $country );
			}
		}

		if ( empty( $country ) ) {
			$country = 'XX';
		}

		return $country;
	}

	/**
	 * Returns the lookup-service details
	 *
	 * @since  4.6.1.1
	 * @return object Service object for geo lookup
	 */
	static protected function get_service( $type = null ) {
		$service = false;
		if ( null === $type ) {
			// Default service.
			if ( defined( 'PO_REMOTE_IP_URL' ) && strlen( PO_REMOTE_IP_URL ) > 5 ) {
				$type = '';
			} else {
				$settings = IncPopupDatabase::get_settings();
				$type = @$settings['geo_lookup'];
			}
		}

		if ( '' == $type ) {
			$service = (object) array(
				'url' => PO_REMOTE_IP_URL,
				'label' => 'wp-config.php',
				'type' => 'text',
			);
		} else if ( 'geo_db' === $type ) {
			$service = (object) array(
				'url' => 'db',
				'label' => __( 'Local IP Lookup Table', 'popover' ),
				'type' => 'text',
			);
		} else {
			$geo_service = IncPopupDatabase::get_geo_services();
			$service = @$geo_service[ $type ];
		}

		return $service;
	}

	/**
	 * Queries an external geo-API to find the country of the specified IP.
	 *
	 * @since  4.6
	 * @param  string $ip The IP Address.
	 * @param  object $service Lookup-Service details.
	 * @return string The country code.
	 */
	static protected function country_from_api( $ip, $service ) {
		$country = false;

		if ( is_object( $service ) && ! empty( $service->url ) ) {
			$url = str_replace( '%ip%', $ip, $service->url );
			$response = wp_remote_get( $url );

			if ( ! is_wp_error( $response )
				&& '200' == $response['response']['code']
				&& 'XX' != $response['body']
			) {
				if ( 'text' == $service->type ) {
					$country = trim( $response['body'] );
				} else if ( 'json' == $service->type ) {
					$data = (array) json_decode( $response['body'] );
					$country = @$data[ @$service->field ];
				}
			}
		}

		if ( ! $country ) {
			$country = PO_DEFAULT_COUNTRY;
		}

		return $country;
	}

	/**
	 * Checks if the current user IP belongs to one of the countries defined in
	 * country_codes list.
	 *
	 * @since  1.0.0
	 * @param  array $country_codes List of country codes.
	 * @return bool
	 */
	protected function test_country( $country_codes ) {
		$response = true;
		$country = $this->get_user_country();

		if ( 'XX' == $country ) {
			return $response;
		}

		return in_array( $country, $country_codes );
	}

	/**
	 * Test the geo lookup function.
	 *
	 * @since  4.6.1.1
	 */
	public function ajax_test_geo() {
		$type = @$_POST['type'];
		$ip = $this->get_users_ip();
		$service = $this->get_service( $type );

		if ( 'db' == $service->url ) {
			IncPopupAddon_GeoDB::init();
			$country = apply_filters( 'popup-get-country', $country, $ip );
		} else {
			$country = $this->country_from_api( $ip, $service );
		}

		echo 'IP: ' . $ip . "\nService: " . $service->label . "\nCountry: " . $country;
		die();
	}



	/*-----  Country list  ------*/


	/* country list array from http://snipplr.com/view.php?codeview&id=33825 */
	public function country_list() {
		$Countries = null;

		if ( null === $Countries ) {
			$Countries = array(
				'AU' => __( 'Australia', 'popover' ),
				'AF' => __( 'Afghanistan', 'popover' ),
				'AL' => __( 'Albania', 'popover' ),
				'DZ' => __( 'Algeria', 'popover' ),
				'AS' => __( 'American Samoa', 'popover' ),
				'AD' => __( 'Andorra', 'popover' ),
				'AO' => __( 'Angola', 'popover' ),
				'AI' => __( 'Anguilla', 'popover' ),
				'AQ' => __( 'Antarctica', 'popover' ),
				'AG' => __( 'Antigua & Barbuda', 'popover' ),
				'AR' => __( 'Argentina', 'popover' ),
				'AM' => __( 'Armenia', 'popover' ),
				'AW' => __( 'Aruba', 'popover' ),
				'AT' => __( 'Austria', 'popover' ),
				'AZ' => __( 'Azerbaijan', 'popover' ),
				'BS' => __( 'Bahamas', 'popover' ),
				'BH' => __( 'Bahrain', 'popover' ),
				'BD' => __( 'Bangladesh', 'popover' ),
				'BB' => __( 'Barbados', 'popover' ),
				'BY' => __( 'Belarus', 'popover' ),
				'BE' => __( 'Belgium', 'popover' ),
				'BZ' => __( 'Belize', 'popover' ),
				'BJ' => __( 'Benin', 'popover' ),
				'BM' => __( 'Bermuda', 'popover' ),
				'BT' => __( 'Bhutan', 'popover' ),
				'BO' => __( 'Bolivia', 'popover' ),
				'BA' => __( 'Bosnia/Hercegovina', 'popover' ),
				'BW' => __( 'Botswana', 'popover' ),
				'BV' => __( 'Bouvet Island', 'popover' ),
				'BR' => __( 'Brazil', 'popover' ),
				'IO' => __( 'British Indian Ocean Territory', 'popover' ),
				'BN' => __( 'Brunei Darussalam', 'popover' ),
				'BG' => __( 'Bulgaria', 'popover' ),
				'BF' => __( 'Burkina Faso', 'popover' ),
				'BI' => __( 'Burundi', 'popover' ),
				'KH' => __( 'Cambodia', 'popover' ),
				'CM' => __( 'Cameroon', 'popover' ),
				'CA' => __( 'Canada', 'popover' ),
				'CV' => __( 'Cape Verde', 'popover' ),
				'KY' => __( 'Cayman Is', 'popover' ),
				'CF' => __( 'Central African Republic', 'popover' ),
				'TD' => __( 'Chad', 'popover' ),
				'CL' => __( 'Chile', 'popover' ),
				'CN' => __( 'China, People\'s Republic of', 'popover' ),
				'CX' => __( 'Christmas Island', 'popover' ),
				'CC' => __( 'Cocos Islands', 'popover' ),
				'CO' => __( 'Colombia', 'popover' ),
				'KM' => __( 'Comoros', 'popover' ),
				'CG' => __( 'Congo', 'popover' ),
				'CD' => __( 'Congo, Democratic Republic', 'popover' ),
				'CK' => __( 'Cook Islands', 'popover' ),
				'CR' => __( 'Costa Rica', 'popover' ),
				'CI' => __( 'Cote d\'Ivoire', 'popover' ),
				'HR' => __( 'Croatia', 'popover' ),
				'CU' => __( 'Cuba', 'popover' ),
				'CY' => __( 'Cyprus', 'popover' ),
				'CZ' => __( 'Czech Republic', 'popover' ),
				'DK' => __( 'Denmark', 'popover' ),
				'DJ' => __( 'Djibouti', 'popover' ),
				'DM' => __( 'Dominica', 'popover' ),
				'DO' => __( 'Dominican Republic', 'popover' ),
				'TP' => __( 'East Timor', 'popover' ),
				'EC' => __( 'Ecuador', 'popover' ),
				'EG' => __( 'Egypt', 'popover' ),
				'SV' => __( 'El Salvador', 'popover' ),
				'GQ' => __( 'Equatorial Guinea', 'popover' ),
				'ER' => __( 'Eritrea', 'popover' ),
				'EE' => __( 'Estonia', 'popover' ),
				'ET' => __( 'Ethiopia', 'popover' ),
				'FK' => __( 'Falkland Islands', 'popover' ),
				'FO' => __( 'Faroe Islands', 'popover' ),
				'FJ' => __( 'Fiji', 'popover' ),
				'FI' => __( 'Finland', 'popover' ),
				'FR' => __( 'France', 'popover' ),
				'FX' => __( 'France, Metropolitan', 'popover' ),
				'GF' => __( 'French Guiana', 'popover' ),
				'PF' => __( 'French Polynesia', 'popover' ),
				'TF' => __( 'French South Territories', 'popover' ),
				'GA' => __( 'Gabon', 'popover' ),
				'GM' => __( 'Gambia', 'popover' ),
				'GE' => __( 'Georgia', 'popover' ),
				'DE' => __( 'Germany', 'popover' ),
				'GH' => __( 'Ghana', 'popover' ),
				'GI' => __( 'Gibraltar', 'popover' ),
				'GR' => __( 'Greece', 'popover' ),
				'GL' => __( 'Greenland', 'popover' ),
				'GD' => __( 'Grenada', 'popover' ),
				'GP' => __( 'Guadeloupe', 'popover' ),
				'GU' => __( 'Guam', 'popover' ),
				'GT' => __( 'Guatemala', 'popover' ),
				'GN' => __( 'Guinea', 'popover' ),
				'GW' => __( 'Guinea-Bissau', 'popover' ),
				'GY' => __( 'Guyana', 'popover' ),
				'HT' => __( 'Haiti', 'popover' ),
				'HM' => __( 'Heard Island And Mcdonald Island', 'popover' ),
				'HN' => __( 'Honduras', 'popover' ),
				'HK' => __( 'Hong Kong', 'popover' ),
				'HU' => __( 'Hungary', 'popover' ),
				'IS' => __( 'Iceland', 'popover' ),
				'IN' => __( 'India', 'popover' ),
				'ID' => __( 'Indonesia', 'popover' ),
				'IR' => __( 'Iran', 'popover' ),
				'IQ' => __( 'Iraq', 'popover' ),
				'IE' => __( 'Ireland', 'popover' ),
				'IL' => __( 'Israel', 'popover' ),
				'IT' => __( 'Italy', 'popover' ),
				'JM' => __( 'Jamaica', 'popover' ),
				'JP' => __( 'Japan', 'popover' ),
				'JT' => __( 'Johnston Island', 'popover' ),
				'JO' => __( 'Jordan', 'popover' ),
				'KZ' => __( 'Kazakhstan', 'popover' ),
				'KE' => __( 'Kenya', 'popover' ),
				'KI' => __( 'Kiribati', 'popover' ),
				'KP' => __( 'Korea, Democratic Peoples Republic', 'popover' ),
				'KR' => __( 'Korea, Republic of', 'popover' ),
				'KW' => __( 'Kuwait', 'popover' ),
				'KG' => __( 'Kyrgyzstan', 'popover' ),
				'LA' => __( 'Lao People\'s Democratic Republic', 'popover' ),
				'LV' => __( 'Latvia', 'popover' ),
				'LB' => __( 'Lebanon', 'popover' ),
				'LS' => __( 'Lesotho', 'popover' ),
				'LR' => __( 'Liberia', 'popover' ),
				'LY' => __( 'Libyan Arab Jamahiriya', 'popover' ),
				'LI' => __( 'Liechtenstein', 'popover' ),
				'LT' => __( 'Lithuania', 'popover' ),
				'LU' => __( 'Luxembourg', 'popover' ),
				'MO' => __( 'Macau', 'popover' ),
				'MK' => __( 'Macedonia', 'popover' ),
				'MG' => __( 'Madagascar', 'popover' ),
				'MW' => __( 'Malawi', 'popover' ),
				'MY' => __( 'Malaysia', 'popover' ),
				'MV' => __( 'Maldives', 'popover' ),
				'ML' => __( 'Mali', 'popover' ),
				'MT' => __( 'Malta', 'popover' ),
				'MH' => __( 'Marshall Islands', 'popover' ),
				'MQ' => __( 'Martinique', 'popover' ),
				'MR' => __( 'Mauritania', 'popover' ),
				'MU' => __( 'Mauritius', 'popover' ),
				'YT' => __( 'Mayotte', 'popover' ),
				'MX' => __( 'Mexico', 'popover' ),
				'FM' => __( 'Micronesia', 'popover' ),
				'MD' => __( 'Moldavia', 'popover' ),
				'MC' => __( 'Monaco', 'popover' ),
				'MN' => __( 'Mongolia', 'popover' ),
				'MS' => __( 'Montserrat', 'popover' ),
				'MA' => __( 'Morocco', 'popover' ),
				'MZ' => __( 'Mozambique', 'popover' ),
				'MM' => __( 'Union Of Myanmar', 'popover' ),
				'NA' => __( 'Namibia', 'popover' ),
				'NR' => __( 'Nauru Island', 'popover' ),
				'NP' => __( 'Nepal', 'popover' ),
				'NL' => __( 'Netherlands', 'popover' ),
				'AN' => __( 'Netherlands Antilles', 'popover' ),
				'NC' => __( 'New Caledonia', 'popover' ),
				'NZ' => __( 'New Zealand', 'popover' ),
				'NI' => __( 'Nicaragua', 'popover' ),
				'NE' => __( 'Niger', 'popover' ),
				'NG' => __( 'Nigeria', 'popover' ),
				'NU' => __( 'Niue', 'popover' ),
				'NF' => __( 'Norfolk Island', 'popover' ),
				'MP' => __( 'Mariana Islands, Northern', 'popover' ),
				'NO' => __( 'Norway', 'popover' ),
				'OM' => __( 'Oman', 'popover' ),
				'PK' => __( 'Pakistan', 'popover' ),
				'PW' => __( 'Palau Islands', 'popover' ),
				'PS' => __( 'Palestine', 'popover' ),
				'PA' => __( 'Panama', 'popover' ),
				'PG' => __( 'Papua New Guinea', 'popover' ),
				'PY' => __( 'Paraguay', 'popover' ),
				'PE' => __( 'Peru', 'popover' ),
				'PH' => __( 'Philippines', 'popover' ),
				'PN' => __( 'Pitcairn', 'popover' ),
				'PL' => __( 'Poland', 'popover' ),
				'PT' => __( 'Portugal', 'popover' ),
				'PR' => __( 'Puerto Rico', 'popover' ),
				'QA' => __( 'Qatar', 'popover' ),
				'RE' => __( 'Reunion Island', 'popover' ),
				'RO' => __( 'Romania', 'popover' ),
				'RU' => __( 'Russian Federation', 'popover' ),
				'RW' => __( 'Rwanda', 'popover' ),
				'WS' => __( 'Samoa', 'popover' ),
				'SH' => __( 'St Helena', 'popover' ),
				'KN' => __( 'St Kitts & Nevis', 'popover' ),
				'LC' => __( 'St Lucia', 'popover' ),
				'PM' => __( 'St Pierre & Miquelon', 'popover' ),
				'VC' => __( 'St Vincent', 'popover' ),
				'SM' => __( 'San Marino', 'popover' ),
				'ST' => __( 'Sao Tome & Principe', 'popover' ),
				'SA' => __( 'Saudi Arabia', 'popover' ),
				'SN' => __( 'Senegal', 'popover' ),
				'SC' => __( 'Seychelles', 'popover' ),
				'SL' => __( 'Sierra Leone', 'popover' ),
				'SG' => __( 'Singapore', 'popover' ),
				'SK' => __( 'Slovakia', 'popover' ),
				'SI' => __( 'Slovenia', 'popover' ),
				'SB' => __( 'Solomon Islands', 'popover' ),
				'SO' => __( 'Somalia', 'popover' ),
				'ZA' => __( 'South Africa', 'popover' ),
				'GS' => __( 'South Georgia and South Sandwich', 'popover' ),
				'ES' => __( 'Spain', 'popover' ),
				'LK' => __( 'Sri Lanka', 'popover' ),
				'XX' => __( 'Stateless Persons', 'popover' ),
				'SD' => __( 'Sudan', 'popover' ),
				'SR' => __( 'Suriname', 'popover' ),
				'SJ' => __( 'Svalbard and Jan Mayen', 'popover' ),
				'SZ' => __( 'Swaziland', 'popover' ),
				'SE' => __( 'Sweden', 'popover' ),
				'CH' => __( 'Switzerland', 'popover' ),
				'SY' => __( 'Syrian Arab Republic', 'popover' ),
				'TW' => __( 'Taiwan, Republic of China', 'popover' ),
				'TJ' => __( 'Tajikistan', 'popover' ),
				'TZ' => __( 'Tanzania', 'popover' ),
				'TH' => __( 'Thailand', 'popover' ),
				'TL' => __( 'Timor Leste', 'popover' ),
				'TG' => __( 'Togo', 'popover' ),
				'TK' => __( 'Tokelau', 'popover' ),
				'TO' => __( 'Tonga', 'popover' ),
				'TT' => __( 'Trinidad & Tobago', 'popover' ),
				'TN' => __( 'Tunisia', 'popover' ),
				'TR' => __( 'Turkey', 'popover' ),
				'TM' => __( 'Turkmenistan', 'popover' ),
				'TC' => __( 'Turks And Caicos Islands', 'popover' ),
				'TV' => __( 'Tuvalu', 'popover' ),
				'UG' => __( 'Uganda', 'popover' ),
				'UA' => __( 'Ukraine', 'popover' ),
				'AE' => __( 'United Arab Emirates', 'popover' ),
				'GB' => __( 'United Kingdom', 'popover' ),
				'UM' => __( 'US Minor Outlying Islands', 'popover' ),
				'US' => __( 'USA', 'popover' ),
				'HV' => __( 'Upper Volta', 'popover' ),
				'UY' => __( 'Uruguay', 'popover' ),
				'UZ' => __( 'Uzbekistan', 'popover' ),
				'VU' => __( 'Vanuatu', 'popover' ),
				'VA' => __( 'Vatican City State', 'popover' ),
				'VE' => __( 'Venezuela', 'popover' ),
				'VN' => __( 'Vietnam', 'popover' ),
				'VG' => __( 'Virgin Islands (British)', 'popover' ),
				'VI' => __( 'Virgin Islands (US)', 'popover' ),
				'WF' => __( 'Wallis And Futuna Islands', 'popover' ),
				'EH' => __( 'Western Sahara', 'popover' ),
				'YE' => __( 'Yemen Arab Rep.', 'popover' ),
				'YD' => __( 'Yemen Democratic', 'popover' ),
				'YU' => __( 'Yugoslavia', 'popover' ),
				'ZR' => __( 'Zaire', 'popover' ),
				'ZM' => __( 'Zambia', 'popover' ),
				'ZW' => __( 'Zimbabwe', 'popover' ),
			);

			/**
			 * Filter the countries so users can add/remove/rename items.
			 */
			$Countries = apply_filters( 'popover-country-list', $Countries );

			// Deprecated filter name.
			$Countries = apply_filters( 'popover_country_list', $Countries );
		}

		return $Countries;
	}
};

IncPopupRules::register( 'IncPopupRule_Geo' );

