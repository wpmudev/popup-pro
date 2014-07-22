<?php

function popover_helpimage( $image ) {
	echo "<img src='" . PO_HELP_URL . 'img/' . $image . "' />";
}

function popover_db_prefix(&$wpdb, $table) {

	if( defined('PO_GLOBAL') && PO_GLOBAL == true ) {
		if(!empty($wpdb->base_prefix)) {
			return $wpdb->base_prefix . $table;
		} else {
			return $wpdb->prefix . $table;
		}
	} else {
		return $wpdb->prefix . $table;
	}

}

function get_popover_addons() {
	if ( is_dir( PO_INC_DIR . 'addons' ) ) {
		if ( $dh = opendir( PO_INC_DIR . 'addons' ) ) {
			$pop_addons = array ();
			while ( ( $addon = readdir( $dh ) ) !== false )
				if ( substr( $addon, -4 ) == '.php' )
					$pop_addons[] = $addon;
			closedir( $dh );
			sort( $pop_addons );

			return apply_filters('popover_available_addons', $pop_addons);

		}
	}

	return false;
}

function load_popover_addons() {

	$addons = get_option('popover_activated_addons', array());

	if ( is_dir( PO_INC_DIR . 'addons') ) {
		if ( $dh = opendir( PO_INC_DIR . 'addons') ) {
			$pop_addons = array ();
			while ( ( $addon = readdir( $dh ) ) !== false )
				if ( substr( $addon, -4 ) == '.php' )
					$pop_addons[] = $addon;
			closedir( $dh );
			sort( $pop_addons );

			$pop_addons = apply_filters('popover_available_addons', $pop_addons);

			foreach( $pop_addons as $pop_addon ) {
				if(in_array($pop_addon, (array) $addons)) {
					include_once( PO_INC_DIR . 'addons/'. $pop_addon );
				}
			}

		}
	}
}

function load_all_popover_addons() {
	if ( is_dir( PO_INC_DIR . 'addons' ) ) {
		if ( $dh = opendir( PO_INC_DIR . 'addons' ) ) {
			$pop_addons = array ();
			while ( ( $addon = readdir( $dh ) ) !== false )
				if ( substr( $addon, -4 ) == '.php' )
					$pop_addons[] = $addon;
			closedir( $dh );
			sort( $pop_addons );
			foreach( $pop_addons as $pop_addon )
				include_once( PO_INC_DIR . 'addons/' . $pop_addon );
		}
	}
}

function P_style_urls( $styles = array() ) {

	$styles['Default'] = PO_TPL_URL . 'default';
	$styles['Default Fixed'] = PO_TPL_URL . 'fixed';
	$styles['Dark Background Fixed'] = PO_TPL_URL . 'fullbackground';

	return $styles;
}
add_filter( 'popover_available_styles_url', 'P_style_urls');

function P_style_dirs ($styles=array()) {
	$styles['Default'] = PO_TPL_DIR . 'default';
	$styles['Default Fixed'] = PO_TPL_DIR . 'fixed';
	$styles['Dark Background Fixed'] = PO_TPL_DIR . 'fullbackground';

	return $styles;
}
add_filter( 'popover_available_styles_directory', 'P_style_dirs');

/* country list array from http://snipplr.com/view.php?codeview&id=33825 */
function P_CountryList() {

	$textdomain = 'popover';

	$countries = array(
		'AU' => __("Australia", $textdomain ),
		'AF' => __("Afghanistan", $textdomain ),
		'AL' => __("Albania", $textdomain ),
		'DZ' => __("Algeria", $textdomain ),
		'AS' => __("American Samoa", $textdomain ),
		'AD' => __("Andorra", $textdomain ),
		'AO' => __("Angola", $textdomain ),
		'AI' => __("Anguilla", $textdomain ),
		'AQ' => __("Antarctica", $textdomain ),
		'AG' => __("Antigua & Barbuda", $textdomain ),
		'AR' => __("Argentina", $textdomain ),
		'AM' => __("Armenia", $textdomain ),
		'AW' => __("Aruba", $textdomain ),
		'AT' => __("Austria", $textdomain ),
		'AZ' => __("Azerbaijan", $textdomain ),
		'BS' => __("Bahamas", $textdomain ),
		'BH' => __("Bahrain", $textdomain ),
		'BD' => __("Bangladesh", $textdomain ),
		'BB' => __("Barbados", $textdomain ),
		'BY' => __("Belarus", $textdomain ),
		'BE' => __("Belgium", $textdomain ),
		'BZ' => __("Belize", $textdomain ),
		'BJ' => __("Benin", $textdomain ),
		'BM' => __("Bermuda", $textdomain ),
		'BT' => __("Bhutan", $textdomain ),
		'BO' => __("Bolivia", $textdomain ),
		'BA' => __("Bosnia/Hercegovina", $textdomain ),
		'BW' => __("Botswana", $textdomain ),
		'BV' => __("Bouvet Island", $textdomain ),
		'BR' => __("Brazil", $textdomain ),
		'IO' => __("British Indian Ocean Territory", $textdomain ),
		'BN' => __("Brunei Darussalam", $textdomain ),
		'BG' => __("Bulgaria", $textdomain ),
		'BF' => __("Burkina Faso", $textdomain ),
		'BI' => __("Burundi", $textdomain ),
		'KH' => __("Cambodia", $textdomain ),
		'CM' => __("Cameroon", $textdomain ),
		'CA' => __("Canada", $textdomain ),
		'CV' => __("Cape Verde", $textdomain ),
		'KY' => __("Cayman Is", $textdomain ),
		'CF' => __("Central African Republic", $textdomain ),
		'TD' => __("Chad", $textdomain ),
		'CL' => __("Chile", $textdomain ),
		'CN' => __("China, People's Republic of", $textdomain ),
		'CX' => __("Christmas Island", $textdomain ),
		'CC' => __("Cocos Islands", $textdomain ),
		'CO' => __("Colombia", $textdomain ),
		'KM' => __("Comoros", $textdomain ),
		'CG' => __("Congo", $textdomain ),
		'CD' => __("Congo, Democratic Republic", $textdomain ),
		'CK' => __("Cook Islands", $textdomain ),
		'CR' => __("Costa Rica", $textdomain ),
		'CI' => __("Cote d'Ivoire", $textdomain ),
		'HR' => __("Croatia", $textdomain ),
		'CU' => __("Cuba", $textdomain ),
		'CY' => __("Cyprus", $textdomain ),
		'CZ' => __("Czech Republic", $textdomain ),
		'DK' => __("Denmark", $textdomain ),
		'DJ' => __("Djibouti", $textdomain ),
		'DM' => __("Dominica", $textdomain ),
		'DO' => __("Dominican Republic", $textdomain ),
		'TP' => __("East Timor", $textdomain ),
		'EC' => __("Ecuador", $textdomain ),
		'EG' => __("Egypt", $textdomain ),
		'SV' => __("El Salvador", $textdomain ),
		'GQ' => __("Equatorial Guinea", $textdomain ),
		'ER' => __("Eritrea", $textdomain ),
		'EE' => __("Estonia", $textdomain ),
		'ET' => __("Ethiopia", $textdomain ),
		'FK' => __("Falkland Islands", $textdomain ),
		'FO' => __("Faroe Islands", $textdomain ),
		'FJ' => __("Fiji", $textdomain ),
		'FI' => __("Finland", $textdomain ),
		'FR' => __("France", $textdomain ),
		'FX' => __("France, Metropolitan", $textdomain ),
		'GF' => __("French Guiana", $textdomain ),
		'PF' => __("French Polynesia", $textdomain ),
		'TF' => __("French South Territories", $textdomain ),
		'GA' => __("Gabon", $textdomain ),
		'GM' => __("Gambia", $textdomain ),
		'GE' => __("Georgia", $textdomain ),
		'DE' => __("Germany", $textdomain ),
		'GH' => __("Ghana", $textdomain ),
		'GI' => __("Gibraltar", $textdomain ),
		'GR' => __("Greece", $textdomain ),
		'GL' => __("Greenland", $textdomain ),
		'GD' => __("Grenada", $textdomain ),
		'GP' => __("Guadeloupe", $textdomain ),
		'GU' => __("Guam", $textdomain ),
		'GT' => __("Guatemala", $textdomain ),
		'GN' => __("Guinea", $textdomain ),
		'GW' => __("Guinea-Bissau", $textdomain ),
		'GY' => __("Guyana", $textdomain ),
		'HT' => __("Haiti", $textdomain ),
		'HM' => __("Heard Island And Mcdonald Island", $textdomain ),
		'HN' => __("Honduras", $textdomain ),
		'HK' => __("Hong Kong", $textdomain ),
		'HU' => __("Hungary", $textdomain ),
		'IS' => __("Iceland", $textdomain ),
		'IN' => __("India", $textdomain ),
		'ID' => __("Indonesia", $textdomain ),
		'IR' => __("Iran", $textdomain ),
		'IQ' => __("Iraq", $textdomain ),
		'IE' => __("Ireland", $textdomain ),
		'IL' => __("Israel", $textdomain ),
		'IT' => __("Italy", $textdomain ),
		'JM' => __("Jamaica", $textdomain ),
		'JP' => __("Japan", $textdomain ),
		'JT' => __("Johnston Island", $textdomain ),
		'JO' => __("Jordan", $textdomain ),
		'KZ' => __("Kazakhstan", $textdomain ),
		'KE' => __("Kenya", $textdomain ),
		'KI' => __("Kiribati", $textdomain ),
		'KP' => __("Korea, Democratic Peoples Republic", $textdomain ),
		'KR' => __("Korea, Republic of", $textdomain ),
		'KW' => __("Kuwait", $textdomain ),
		'KG' => __("Kyrgyzstan", $textdomain ),
		'LA' => __("Lao People's Democratic Republic", $textdomain ),
		'LV' => __("Latvia", $textdomain ),
		'LB' => __("Lebanon", $textdomain ),
		'LS' => __("Lesotho", $textdomain ),
		'LR' => __("Liberia", $textdomain ),
		'LY' => __("Libyan Arab Jamahiriya", $textdomain ),
		'LI' => __("Liechtenstein", $textdomain ),
		'LT' => __("Lithuania", $textdomain ),
		'LU' => __("Luxembourg", $textdomain ),
		'MO' => __("Macau", $textdomain ),
		'MK' => __("Macedonia", $textdomain ),
		'MG' => __("Madagascar", $textdomain ),
		'MW' => __("Malawi", $textdomain ),
		'MY' => __("Malaysia", $textdomain ),
		'MV' => __("Maldives", $textdomain ),
		'ML' => __("Mali", $textdomain ),
		'MT' => __("Malta", $textdomain ),
		'MH' => __("Marshall Islands", $textdomain ),
		'MQ' => __("Martinique", $textdomain ),
		'MR' => __("Mauritania", $textdomain ),
		'MU' => __("Mauritius", $textdomain ),
		'YT' => __("Mayotte", $textdomain ),
		'MX' => __("Mexico", $textdomain ),
		'FM' => __("Micronesia", $textdomain ),
		'MD' => __("Moldavia", $textdomain ),
		'MC' => __("Monaco", $textdomain ),
		'MN' => __("Mongolia", $textdomain ),
		'MS' => __("Montserrat", $textdomain ),
		'MA' => __("Morocco", $textdomain ),
		'MZ' => __("Mozambique", $textdomain ),
		'MM' => __("Union Of Myanmar", $textdomain ),
		'NA' => __("Namibia", $textdomain ),
		'NR' => __("Nauru Island", $textdomain ),
		'NP' => __("Nepal", $textdomain ),
		'NL' => __("Netherlands", $textdomain ),
		'AN' => __("Netherlands Antilles", $textdomain ),
		'NC' => __("New Caledonia", $textdomain ),
		'NZ' => __("New Zealand", $textdomain ),
		'NI' => __("Nicaragua", $textdomain ),
		'NE' => __("Niger", $textdomain ),
		'NG' => __("Nigeria", $textdomain ),
		'NU' => __("Niue", $textdomain ),
		'NF' => __("Norfolk Island", $textdomain ),
		'MP' => __("Mariana Islands, Northern", $textdomain ),
		'NO' => __("Norway", $textdomain ),
		'OM' => __("Oman", $textdomain ),
		'PK' => __("Pakistan", $textdomain ),
		'PW' => __("Palau Islands", $textdomain ),
		'PS' => __("Palestine", $textdomain ),
		'PA' => __("Panama", $textdomain ),
		'PG' => __("Papua New Guinea", $textdomain ),
		'PY' => __("Paraguay", $textdomain ),
		'PE' => __("Peru", $textdomain ),
		'PH' => __("Philippines", $textdomain ),
		'PN' => __("Pitcairn", $textdomain ),
		'PL' => __("Poland", $textdomain ),
		'PT' => __("Portugal", $textdomain ),
		'PR' => __("Puerto Rico", $textdomain ),
		'QA' => __("Qatar", $textdomain ),
		'RE' => __("Reunion Island", $textdomain ),
		'RO' => __("Romania", $textdomain ),
		'RU' => __("Russian Federation", $textdomain ),
		'RW' => __("Rwanda", $textdomain ),
		'WS' => __("Samoa", $textdomain ),
		'SH' => __("St Helena", $textdomain ),
		'KN' => __("St Kitts & Nevis", $textdomain ),
		'LC' => __("St Lucia", $textdomain ),
		'PM' => __("St Pierre & Miquelon", $textdomain ),
		'VC' => __("St Vincent", $textdomain ),
		'SM' => __("San Marino", $textdomain ),
		'ST' => __("Sao Tome & Principe", $textdomain ),
		'SA' => __("Saudi Arabia", $textdomain ),
		'SN' => __("Senegal", $textdomain ),
		'SC' => __("Seychelles", $textdomain ),
		'SL' => __("Sierra Leone", $textdomain ),
		'SG' => __("Singapore", $textdomain ),
		'SK' => __("Slovakia", $textdomain ),
		'SI' => __("Slovenia", $textdomain ),
		'SB' => __("Solomon Islands", $textdomain ),
		'SO' => __("Somalia", $textdomain ),
		'ZA' => __("South Africa", $textdomain ),
		'GS' => __("South Georgia and South Sandwich", $textdomain ),
		'ES' => __("Spain", $textdomain ),
		'LK' => __("Sri Lanka", $textdomain ),
		'XX' => __("Stateless Persons", $textdomain ),
		'SD' => __("Sudan", $textdomain ),
		'SR' => __("Suriname", $textdomain ),
		'SJ' => __("Svalbard and Jan Mayen", $textdomain ),
		'SZ' => __("Swaziland", $textdomain ),
		'SE' => __("Sweden", $textdomain ),
		'CH' => __("Switzerland", $textdomain ),
		'SY' => __("Syrian Arab Republic", $textdomain ),
		'TW' => __("Taiwan, Republic of China", $textdomain ),
		'TJ' => __("Tajikistan", $textdomain ),
		'TZ' => __("Tanzania", $textdomain ),
		'TH' => __("Thailand", $textdomain ),
		'TL' => __("Timor Leste", $textdomain ),
		'TG' => __("Togo", $textdomain ),
		'TK' => __("Tokelau", $textdomain ),
		'TO' => __("Tonga", $textdomain ),
		'TT' => __("Trinidad & Tobago", $textdomain ),
		'TN' => __("Tunisia", $textdomain ),
		'TR' => __("Turkey", $textdomain ),
		'TM' => __("Turkmenistan", $textdomain ),
		'TC' => __("Turks And Caicos Islands", $textdomain ),
		'TV' => __("Tuvalu", $textdomain ),
		'UG' => __("Uganda", $textdomain ),
		'UA' => __("Ukraine", $textdomain ),
		'AE' => __("United Arab Emirates", $textdomain ),
		'GB' => __("United Kingdom", $textdomain ),
		'UM' => __("US Minor Outlying Islands", $textdomain ),
		'US' => __("USA", $textdomain ),
		'HV' => __("Upper Volta", $textdomain ),
		'UY' => __("Uruguay", $textdomain ),
		'UZ' => __("Uzbekistan", $textdomain ),
		'VU' => __("Vanuatu", $textdomain ),
		'VA' => __("Vatican City State", $textdomain ),
		'VE' => __("Venezuela", $textdomain ),
		'VN' => __("Vietnam", $textdomain ),
		'VG' => __("Virgin Islands (British)", $textdomain ),
		'VI' => __("Virgin Islands (US)", $textdomain ),
		'WF' => __("Wallis And Futuna Islands", $textdomain ),
		'EH' => __("Western Sahara", $textdomain ),
		'YE' => __("Yemen Arab Rep.", $textdomain ),
		'YD' => __("Yemen Democratic", $textdomain ),
		'YU' => __("Yugoslavia", $textdomain ),
		'ZR' => __("Zaire", $textdomain ),
		'ZM' => __("Zambia", $textdomain ),
		'ZW' => __("Zimbabwe", $textdomain )
	);

	return apply_filters( 'popover_country_list', $countries );

}

function popover_is_on_network () {
	if (!is_multisite()) return false; // Short on first check
	if (defined('PO_GLOBAL') && PO_GLOBAL) return true; // Do quick/easy thing first

	if (!function_exists('is_plugin_active_for_network')) {
		require_once(ABSPATH . '/wp-admin/includes/plugin.php');
	}
	return is_plugin_active_for_network('popover/popover.php'); // Do this last because it might require loading dependencies
}

function get_popover_option($key, $default = false) {
	//if(is_multisite() && function_exists('is_plugin_active_for_network') && is_plugin_active_for_network('popover/popover.php')) {
	if (popover_is_on_network()) {
		return get_site_option($key, $default);
	} else {
		return get_option($key, $default);
	}

}

function update_popover_option($key, $value) {
	//if(is_multisite() && function_exists('is_plugin_active_for_network') && is_plugin_active_for_network('popover/popover.php')) {
	if (popover_is_on_network()) {
		return update_site_option($key, $value);
	} else {
		return update_option($key, $value);
	}

}

function delete_popover_option($key) {
	//if(is_multisite() && function_exists('is_plugin_active_for_network') && is_plugin_active_for_network('popover/popover.php')) {
	if (popover_is_on_network()) {
		return delete_site_option($key);
	} else {
		return delete_option($key);
	}

}



abstract class Popover_Rules_Rule {

	abstract public function apply_rule ($show, $popover);
	abstract public static function add ();

	protected $_id;
	protected $_defaults = array();
	protected $_info = array(
		"title" => '',
		"message" => '',
	);

	protected function __construct () {
		$this->_add_hooks();
	}

	protected function _get_field_name () {
		$args = func_get_args();
		return esc_attr($this->_id . '[' . join('][', $args) . ']');
	}
	protected function _get_field_id () {
		$args = func_get_args();
		return esc_attr($this->_id . join('-', $args));
	}

	protected function _add_hooks () {
		if (!$this->_id) return false;
		add_filter('popover_nice_rule_name', array($this, 'add_rule_column'), 10, 2);
		add_action('popover_active_rule_' . $this->_id, array($this, 'add_main_active_rule'), 10, 2); // Shown
		add_action('popover_additional_rules_main', array($this, 'add_active_rule'), 10, 2); // Hidden
		add_action('popover_additional_rules_sidebar', array($this, 'add_draggable_rule'));

		add_filter('popover-data-save', array($this, 'save_settings'));

		add_filter('popover_process_rule_' . $this->_id, array($this, 'apply_rule'), 10, 2);
	}

	public function get_admin_interface ($data) {
		return '';
	}

	public function save_settings ($settings) {
		if (empty($_POST[$this->_id])) return $settings;

		$data = stripslashes_deep($_POST[$this->_id]);
		$result = array();
		$keys = array_keys($this->_defaults);
		foreach ($keys as $key) {
			if (empty($data[$key])) continue;
			$result[$key] = array_filter(array_map('wp_strip_all_tags', $data[$key]));
		}
		$settings[$this->_id] = $result;
		return $settings;
	}

	public function add_rule_column ($rule, $key) {
		if ($key != $this->_id) return $rule;
		return esc_html($this->_info["message"]);
	}

	public function add_main_active_rule ($popover, $check) {
		$in_use = !empty($check[$this->_id]) ? $check[$this->_id] : false;
		if (!$in_use) return false;
		$this->add_active_rule($popover, $check);
	}

	public function add_active_rule ($popover, $check) {
		$data = !empty($popover->popover_settings) ? $popover->popover_settings : false;
		?>
<div class='popover-operation' id='main-<?php echo $this->_id; ?>'>
	<h2 class='sidebar-name'><?php echo esc_html($this->_info["title"]); ?><span><a href='#remove' class='removelink' id='remove-<?php echo $this->_id; ?>' title='<?php _e("Remove %s tag from this rules area.",'popover', esc_html($this->_info["title"])); ?>'><?php _e('Remove','popover'); ?></a></span></h2>
	<div class='inner-operation'>
		<p><?php echo esc_html($this->_info["message"]); ?></p>
		<?php echo $this->get_admin_interface($data); ?>
		<input type='hidden' name='popovercheck[<?php echo $this->_id; ?>]' value='yes' />
	</div>
</div>
		<?php
	}

	public function add_draggable_rule ($check) {
		if (isset($check[$this->_id])) return false;
?>
<li class='popover-draggable' id='<?php echo $this->_id; ?>'>
	<div class='action action-draggable'>
		<div class='action-top closed'>
		<a href="#available-actions" class="action-button hide-if-no-js"></a>
		<?php echo esc_html($this->_info["title"]); ?>
		</div>
		<div class='action-body closed'>
			<?php if(!empty($this->_info["message"])) { ?>
				<p>
					<?php echo esc_html($this->_info["message"]); ?>
				</p>
			<?php } ?>
			<p>
				<a href='#addtopopover' class='action-to-popover' title='<?php _e('Add this rule to the popover.','popover'); ?>'><?php _e('Add this rule to the popover.','popover'); ?></a>
			</p>
		</div>
	</div>
</li>
<?php
	}

}
