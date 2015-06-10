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
			__( 'In a specific Country', PO_LANG ),
			__( 'Shows the PopUp if the user is in a certain country.', PO_LANG ),
			'no_country',
			25
		);

		// 'no_country' rule.
		$this->add_rule(
			'no_country',
			__( 'Not in a specific Country', PO_LANG ),
			__( 'Shows the PopUp if the user is not in a certain country.', PO_LANG ),
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
			<?php _e( 'Included countries:', PO_LANG ); ?>
		</label>
		<select name="po_rule_data[country][]"
			id="po-rule-data-country"
			multiple="multiple"
			placeholder="<?php _e( 'Click here to select a country', PO_LANG ); ?>">
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
		lib2()->array->equip( $data, 'country' );
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
			<?php _e( 'Excluded countries:', PO_LANG ); ?>
		</label>
		<select name="po_rule_data[no_country][]"
			id="po-rule-data-no-country"
			multiple="multiple"
			placeholder="<?php _e( 'Click here to select a country', PO_LANG ); ?>">
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
		lib2()->array->equip( $data, 'no_country' );
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
				'label' => __( 'Local IP Lookup Table', PO_LANG ),
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
				'AU' => __( 'Australia', PO_LANG ),
				'AF' => __( 'Afghanistan', PO_LANG ),
				'AL' => __( 'Albania', PO_LANG ),
				'DZ' => __( 'Algeria', PO_LANG ),
				'AS' => __( 'American Samoa', PO_LANG ),
				'AD' => __( 'Andorra', PO_LANG ),
				'AO' => __( 'Angola', PO_LANG ),
				'AI' => __( 'Anguilla', PO_LANG ),
				'AQ' => __( 'Antarctica', PO_LANG ),
				'AG' => __( 'Antigua & Barbuda', PO_LANG ),
				'AR' => __( 'Argentina', PO_LANG ),
				'AM' => __( 'Armenia', PO_LANG ),
				'AW' => __( 'Aruba', PO_LANG ),
				'AT' => __( 'Austria', PO_LANG ),
				'AZ' => __( 'Azerbaijan', PO_LANG ),
				'BS' => __( 'Bahamas', PO_LANG ),
				'BH' => __( 'Bahrain', PO_LANG ),
				'BD' => __( 'Bangladesh', PO_LANG ),
				'BB' => __( 'Barbados', PO_LANG ),
				'BY' => __( 'Belarus', PO_LANG ),
				'BE' => __( 'Belgium', PO_LANG ),
				'BZ' => __( 'Belize', PO_LANG ),
				'BJ' => __( 'Benin', PO_LANG ),
				'BM' => __( 'Bermuda', PO_LANG ),
				'BT' => __( 'Bhutan', PO_LANG ),
				'BO' => __( 'Bolivia', PO_LANG ),
				'BA' => __( 'Bosnia/Hercegovina', PO_LANG ),
				'BW' => __( 'Botswana', PO_LANG ),
				'BV' => __( 'Bouvet Island', PO_LANG ),
				'BR' => __( 'Brazil', PO_LANG ),
				'IO' => __( 'British Indian Ocean Territory', PO_LANG ),
				'BN' => __( 'Brunei Darussalam', PO_LANG ),
				'BG' => __( 'Bulgaria', PO_LANG ),
				'BF' => __( 'Burkina Faso', PO_LANG ),
				'BI' => __( 'Burundi', PO_LANG ),
				'KH' => __( 'Cambodia', PO_LANG ),
				'CM' => __( 'Cameroon', PO_LANG ),
				'CA' => __( 'Canada', PO_LANG ),
				'CV' => __( 'Cape Verde', PO_LANG ),
				'KY' => __( 'Cayman Is', PO_LANG ),
				'CF' => __( 'Central African Republic', PO_LANG ),
				'TD' => __( 'Chad', PO_LANG ),
				'CL' => __( 'Chile', PO_LANG ),
				'CN' => __( 'China, People\'s Republic of', PO_LANG ),
				'CX' => __( 'Christmas Island', PO_LANG ),
				'CC' => __( 'Cocos Islands', PO_LANG ),
				'CO' => __( 'Colombia', PO_LANG ),
				'KM' => __( 'Comoros', PO_LANG ),
				'CG' => __( 'Congo', PO_LANG ),
				'CD' => __( 'Congo, Democratic Republic', PO_LANG ),
				'CK' => __( 'Cook Islands', PO_LANG ),
				'CR' => __( 'Costa Rica', PO_LANG ),
				'CI' => __( 'Cote d\'Ivoire', PO_LANG ),
				'HR' => __( 'Croatia', PO_LANG ),
				'CU' => __( 'Cuba', PO_LANG ),
				'CY' => __( 'Cyprus', PO_LANG ),
				'CZ' => __( 'Czech Republic', PO_LANG ),
				'DK' => __( 'Denmark', PO_LANG ),
				'DJ' => __( 'Djibouti', PO_LANG ),
				'DM' => __( 'Dominica', PO_LANG ),
				'DO' => __( 'Dominican Republic', PO_LANG ),
				'TP' => __( 'East Timor', PO_LANG ),
				'EC' => __( 'Ecuador', PO_LANG ),
				'EG' => __( 'Egypt', PO_LANG ),
				'SV' => __( 'El Salvador', PO_LANG ),
				'GQ' => __( 'Equatorial Guinea', PO_LANG ),
				'ER' => __( 'Eritrea', PO_LANG ),
				'EE' => __( 'Estonia', PO_LANG ),
				'ET' => __( 'Ethiopia', PO_LANG ),
				'FK' => __( 'Falkland Islands', PO_LANG ),
				'FO' => __( 'Faroe Islands', PO_LANG ),
				'FJ' => __( 'Fiji', PO_LANG ),
				'FI' => __( 'Finland', PO_LANG ),
				'FR' => __( 'France', PO_LANG ),
				'FX' => __( 'France, Metropolitan', PO_LANG ),
				'GF' => __( 'French Guiana', PO_LANG ),
				'PF' => __( 'French Polynesia', PO_LANG ),
				'TF' => __( 'French South Territories', PO_LANG ),
				'GA' => __( 'Gabon', PO_LANG ),
				'GM' => __( 'Gambia', PO_LANG ),
				'GE' => __( 'Georgia', PO_LANG ),
				'DE' => __( 'Germany', PO_LANG ),
				'GH' => __( 'Ghana', PO_LANG ),
				'GI' => __( 'Gibraltar', PO_LANG ),
				'GR' => __( 'Greece', PO_LANG ),
				'GL' => __( 'Greenland', PO_LANG ),
				'GD' => __( 'Grenada', PO_LANG ),
				'GP' => __( 'Guadeloupe', PO_LANG ),
				'GU' => __( 'Guam', PO_LANG ),
				'GT' => __( 'Guatemala', PO_LANG ),
				'GN' => __( 'Guinea', PO_LANG ),
				'GW' => __( 'Guinea-Bissau', PO_LANG ),
				'GY' => __( 'Guyana', PO_LANG ),
				'HT' => __( 'Haiti', PO_LANG ),
				'HM' => __( 'Heard Island And Mcdonald Island', PO_LANG ),
				'HN' => __( 'Honduras', PO_LANG ),
				'HK' => __( 'Hong Kong', PO_LANG ),
				'HU' => __( 'Hungary', PO_LANG ),
				'IS' => __( 'Iceland', PO_LANG ),
				'IN' => __( 'India', PO_LANG ),
				'ID' => __( 'Indonesia', PO_LANG ),
				'IR' => __( 'Iran', PO_LANG ),
				'IQ' => __( 'Iraq', PO_LANG ),
				'IE' => __( 'Ireland', PO_LANG ),
				'IL' => __( 'Israel', PO_LANG ),
				'IT' => __( 'Italy', PO_LANG ),
				'JM' => __( 'Jamaica', PO_LANG ),
				'JP' => __( 'Japan', PO_LANG ),
				'JT' => __( 'Johnston Island', PO_LANG ),
				'JO' => __( 'Jordan', PO_LANG ),
				'KZ' => __( 'Kazakhstan', PO_LANG ),
				'KE' => __( 'Kenya', PO_LANG ),
				'KI' => __( 'Kiribati', PO_LANG ),
				'KP' => __( 'Korea, Democratic Peoples Republic', PO_LANG ),
				'KR' => __( 'Korea, Republic of', PO_LANG ),
				'KW' => __( 'Kuwait', PO_LANG ),
				'KG' => __( 'Kyrgyzstan', PO_LANG ),
				'LA' => __( 'Lao People\'s Democratic Republic', PO_LANG ),
				'LV' => __( 'Latvia', PO_LANG ),
				'LB' => __( 'Lebanon', PO_LANG ),
				'LS' => __( 'Lesotho', PO_LANG ),
				'LR' => __( 'Liberia', PO_LANG ),
				'LY' => __( 'Libyan Arab Jamahiriya', PO_LANG ),
				'LI' => __( 'Liechtenstein', PO_LANG ),
				'LT' => __( 'Lithuania', PO_LANG ),
				'LU' => __( 'Luxembourg', PO_LANG ),
				'MO' => __( 'Macau', PO_LANG ),
				'MK' => __( 'Macedonia', PO_LANG ),
				'MG' => __( 'Madagascar', PO_LANG ),
				'MW' => __( 'Malawi', PO_LANG ),
				'MY' => __( 'Malaysia', PO_LANG ),
				'MV' => __( 'Maldives', PO_LANG ),
				'ML' => __( 'Mali', PO_LANG ),
				'MT' => __( 'Malta', PO_LANG ),
				'MH' => __( 'Marshall Islands', PO_LANG ),
				'MQ' => __( 'Martinique', PO_LANG ),
				'MR' => __( 'Mauritania', PO_LANG ),
				'MU' => __( 'Mauritius', PO_LANG ),
				'YT' => __( 'Mayotte', PO_LANG ),
				'MX' => __( 'Mexico', PO_LANG ),
				'FM' => __( 'Micronesia', PO_LANG ),
				'MD' => __( 'Moldavia', PO_LANG ),
				'MC' => __( 'Monaco', PO_LANG ),
				'MN' => __( 'Mongolia', PO_LANG ),
				'MS' => __( 'Montserrat', PO_LANG ),
				'MA' => __( 'Morocco', PO_LANG ),
				'MZ' => __( 'Mozambique', PO_LANG ),
				'MM' => __( 'Union Of Myanmar', PO_LANG ),
				'NA' => __( 'Namibia', PO_LANG ),
				'NR' => __( 'Nauru Island', PO_LANG ),
				'NP' => __( 'Nepal', PO_LANG ),
				'NL' => __( 'Netherlands', PO_LANG ),
				'AN' => __( 'Netherlands Antilles', PO_LANG ),
				'NC' => __( 'New Caledonia', PO_LANG ),
				'NZ' => __( 'New Zealand', PO_LANG ),
				'NI' => __( 'Nicaragua', PO_LANG ),
				'NE' => __( 'Niger', PO_LANG ),
				'NG' => __( 'Nigeria', PO_LANG ),
				'NU' => __( 'Niue', PO_LANG ),
				'NF' => __( 'Norfolk Island', PO_LANG ),
				'MP' => __( 'Mariana Islands, Northern', PO_LANG ),
				'NO' => __( 'Norway', PO_LANG ),
				'OM' => __( 'Oman', PO_LANG ),
				'PK' => __( 'Pakistan', PO_LANG ),
				'PW' => __( 'Palau Islands', PO_LANG ),
				'PS' => __( 'Palestine', PO_LANG ),
				'PA' => __( 'Panama', PO_LANG ),
				'PG' => __( 'Papua New Guinea', PO_LANG ),
				'PY' => __( 'Paraguay', PO_LANG ),
				'PE' => __( 'Peru', PO_LANG ),
				'PH' => __( 'Philippines', PO_LANG ),
				'PN' => __( 'Pitcairn', PO_LANG ),
				'PL' => __( 'Poland', PO_LANG ),
				'PT' => __( 'Portugal', PO_LANG ),
				'PR' => __( 'Puerto Rico', PO_LANG ),
				'QA' => __( 'Qatar', PO_LANG ),
				'RE' => __( 'Reunion Island', PO_LANG ),
				'RO' => __( 'Romania', PO_LANG ),
				'RU' => __( 'Russian Federation', PO_LANG ),
				'RW' => __( 'Rwanda', PO_LANG ),
				'WS' => __( 'Samoa', PO_LANG ),
				'SH' => __( 'St Helena', PO_LANG ),
				'KN' => __( 'St Kitts & Nevis', PO_LANG ),
				'LC' => __( 'St Lucia', PO_LANG ),
				'PM' => __( 'St Pierre & Miquelon', PO_LANG ),
				'VC' => __( 'St Vincent', PO_LANG ),
				'SM' => __( 'San Marino', PO_LANG ),
				'ST' => __( 'Sao Tome & Principe', PO_LANG ),
				'SA' => __( 'Saudi Arabia', PO_LANG ),
				'SN' => __( 'Senegal', PO_LANG ),
				'SC' => __( 'Seychelles', PO_LANG ),
				'SL' => __( 'Sierra Leone', PO_LANG ),
				'SG' => __( 'Singapore', PO_LANG ),
				'SK' => __( 'Slovakia', PO_LANG ),
				'SI' => __( 'Slovenia', PO_LANG ),
				'SB' => __( 'Solomon Islands', PO_LANG ),
				'SO' => __( 'Somalia', PO_LANG ),
				'ZA' => __( 'South Africa', PO_LANG ),
				'GS' => __( 'South Georgia and South Sandwich', PO_LANG ),
				'ES' => __( 'Spain', PO_LANG ),
				'LK' => __( 'Sri Lanka', PO_LANG ),
				'XX' => __( 'Stateless Persons', PO_LANG ),
				'SD' => __( 'Sudan', PO_LANG ),
				'SR' => __( 'Suriname', PO_LANG ),
				'SJ' => __( 'Svalbard and Jan Mayen', PO_LANG ),
				'SZ' => __( 'Swaziland', PO_LANG ),
				'SE' => __( 'Sweden', PO_LANG ),
				'CH' => __( 'Switzerland', PO_LANG ),
				'SY' => __( 'Syrian Arab Republic', PO_LANG ),
				'TW' => __( 'Taiwan, Republic of China', PO_LANG ),
				'TJ' => __( 'Tajikistan', PO_LANG ),
				'TZ' => __( 'Tanzania', PO_LANG ),
				'TH' => __( 'Thailand', PO_LANG ),
				'TL' => __( 'Timor Leste', PO_LANG ),
				'TG' => __( 'Togo', PO_LANG ),
				'TK' => __( 'Tokelau', PO_LANG ),
				'TO' => __( 'Tonga', PO_LANG ),
				'TT' => __( 'Trinidad & Tobago', PO_LANG ),
				'TN' => __( 'Tunisia', PO_LANG ),
				'TR' => __( 'Turkey', PO_LANG ),
				'TM' => __( 'Turkmenistan', PO_LANG ),
				'TC' => __( 'Turks And Caicos Islands', PO_LANG ),
				'TV' => __( 'Tuvalu', PO_LANG ),
				'UG' => __( 'Uganda', PO_LANG ),
				'UA' => __( 'Ukraine', PO_LANG ),
				'AE' => __( 'United Arab Emirates', PO_LANG ),
				'GB' => __( 'United Kingdom', PO_LANG ),
				'UM' => __( 'US Minor Outlying Islands', PO_LANG ),
				'US' => __( 'USA', PO_LANG ),
				'HV' => __( 'Upper Volta', PO_LANG ),
				'UY' => __( 'Uruguay', PO_LANG ),
				'UZ' => __( 'Uzbekistan', PO_LANG ),
				'VU' => __( 'Vanuatu', PO_LANG ),
				'VA' => __( 'Vatican City State', PO_LANG ),
				'VE' => __( 'Venezuela', PO_LANG ),
				'VN' => __( 'Vietnam', PO_LANG ),
				'VG' => __( 'Virgin Islands (British)', PO_LANG ),
				'VI' => __( 'Virgin Islands (US)', PO_LANG ),
				'WF' => __( 'Wallis And Futuna Islands', PO_LANG ),
				'EH' => __( 'Western Sahara', PO_LANG ),
				'YE' => __( 'Yemen Arab Rep.', PO_LANG ),
				'YD' => __( 'Yemen Democratic', PO_LANG ),
				'YU' => __( 'Yugoslavia', PO_LANG ),
				'ZR' => __( 'Zaire', PO_LANG ),
				'ZM' => __( 'Zambia', PO_LANG ),
				'ZW' => __( 'Zimbabwe', PO_LANG )
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