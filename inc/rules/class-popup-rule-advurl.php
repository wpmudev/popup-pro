<?php
/*
Name:        Advanced URL
Plugin URI:  http://premium.wpmudev.org/project/the-pop-over-plugin/
Description: Adds advanced URL matching with regex support.
Author:      Ve (Incsub)
Author URI:  http://premium.wpmudev.org
Type:        Rule
Rules:       On approximate URL, Not on approximate URL
Limit:       pro
Version:     1.0

NOTE: DON'T RENAME THIS FILE!!
This filename is saved as metadata with each popup that uses these rules.
Renaming the file will DISABLE the rules, which is very bad!
*/

class IncPopupRule_AdvUrl extends IncPopupRule {

	/**
	 * Initialize the rule object.
	 *
	 * @since  4.6
	 */
	protected function init() {
		$this->filename = basename( __FILE__ );

		// 'url' rule.
		$this->add_rule(
			'adv_url',
			__( 'On approximate URL', PO_LANG ),
			__( 'Shows the Pop Up if the user is on a certain URL.', PO_LANG ),
			'no_adv_url',
			30
		);

		// 'no_url' rule.
		$this->add_rule(
			'no_adv_url',
			__( 'Not on approximate URL', PO_LANG ),
			__( 'Shows the Pop Up if the user is not on a certain URL.', PO_LANG ),
			'adv_url',
			30
		);
	}


	/*=============================*\
	=================================
	==                             ==
	==           ADV_URL           ==
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
	protected function apply_adv_url( $data ) {
		if ( is_string( $data ) ) { $data = array( $data ); }
		if ( ! is_array( $data ) ) { return true; }
		$url = $this->current_url();

		return $this->check_adv_url( $url, $data );
	}

	/**
	 * Output the Admin-Form for the active rule.
	 *
	 * @since  4.6
	 * @param  mixed $data Rule-data which was saved via the save_() handler.
	 */
	protected function form_adv_url( $data ) {
		if ( is_string( $data ) ) { $urls = $data; }
		else if ( is_array( $data ) ) { $urls = implode( "\n", $data ); }
		else { $urls = ''; }
		?>
		<label for="po-rule-data-adv-url">
			<?php _e( 'URL regex (one per line):', PO_LANG ); ?>
		</label>
		<textarea name="po_rule_data[adv-url]" id="po-rule-data-adv-url" class="block"><?php
			echo esc_html( $urls );
		?></textarea>
		<?php
	}

	/**
	 * Update and return the $settings array to save the form values.
	 *
	 * @since  4.6
	 * @return mixed Data collection of this rule.
	 */
	protected function save_adv_url() {
		return explode( "\n", @$_POST['po_rule_data']['adv-url'] );
	}


	/*================================*\
	====================================
	==                                ==
	==           NO_ADV_URL           ==
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
	protected function apply_no_adv_url( $data ) {
		if ( is_string( $data ) ) { $data = array( $data ); }
		if ( ! is_array( $data ) ) { return true; }
		$url = $this->current_url();

		return ! $this->check_adv_url( $url, $data );
	}

	/**
	 * Output the Admin-Form for the active rule.
	 *
	 * @since  4.6
	 * @param  mixed $data Rule-data which was saved via the save_() handler.
	 */
	protected function form_no_adv_url( $data ) {
		if ( is_string( $data ) ) { $urls = $data; }
		else if ( is_array( $data ) ) { $urls = implode( "\n", $data ); }
		else { $urls = ''; }
		?>
		<label for="po-rule-data-no-adv-url">
			<?php _e( 'URL regex (one per line):', PO_LANG ); ?>
		</label>
		<textarea name="po_rule_data[no_adv_url]" id="po-rule-data-no-adv-url" class="block"><?php
			echo esc_html( $urls );
		?></textarea>
		<?php
	}

	/**
	 * Update and return the $settings array to save the form values.
	 *
	 * @since  4.6
	 * @return mixed Data collection of this rule.
	 */
	protected function save_no_adv_url() {
		return explode( "\n", @$_POST['po_rule_data']['no_adv_url'] );
	}


	/*======================================*\
	==========================================
	==                                      ==
	==           HELPER FUNCTIONS           ==
	==                                      ==
	==========================================
	\*======================================*/


	/**
	 * Returns the URL which can be defined by REQUEST[theform] or wp->request.
	 *
	 * @since  4.6
	 * @return string
	 */
	protected function current_url() {
		global $wp;
		$current_url = '';

		if ( empty( $_REQUEST['thefrom'] ) ) {
			$current_url = home_url( $wp->request );
		} else {
			$current_url = $_REQUEST['thefrom'];
		}

		return $current_url;
	}

	/**
	 * Tests if the $test_url matches any pattern defined in the $list.
	 *
	 * @since  4.6
	 * @param  string $test_url The URL to test.
	 * @param  array $list List of URL-patterns to test against.
	 * @return bool
	 */
	protected function check_adv_url( $test_url, $list ) {
		$response = false;
		$list = array_map( 'trim', $list );

		if ( empty( $list ) ) {
			$response = true;
		} else {
			foreach ( $list as $match ) {
				if ( preg_match( '#' . $match . '#i', $test_url ) ) {
					$response = true;
					break;
				}
			}
		}

		return $response;
	}

};

IncPopupRules::register( 'IncPopupRule_AdvUrl' );
