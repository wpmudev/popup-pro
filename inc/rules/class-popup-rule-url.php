<?php
/*
Name:        Basic URL
Plugin URI:  http://premium.wpmudev.org/project/the-pop-over-plugin/
Description: Simple and fast URL matching.
Author:      Philipp (Incsub)
Author URI:  http://premium.wpmudev.org
Type:        Rule
Rules:       On specific URL, Not on specific URL
Version:     1.0

NOTE: DON'T RENAME THIS FILE!!
This filename is saved as metadata with each popup that uses these rules.
Renaming the file will DISABLE the rules, which is very bad!
*/

class IncPopupRule_Url extends IncPopupRule {

	/**
	 * Initialize the rule object.
	 *
	 * @since  4.6
	 */
	protected function init() {
		$this->filename = basename( __FILE__ );

		// 'url' rule.
		$this->add_rule(
			'url',
			__( 'On specific URL', PO_LANG ),
			__( 'Shows the PopUp if the user is on a certain URL.', PO_LANG ),
			'no_url',
			20
		);

		// 'no_url' rule.
		$this->add_rule(
			'no_url',
			__( 'Not on specific URL', PO_LANG ),
			__( 'Shows the PopUp if the user is not on a certain URL.', PO_LANG ),
			'url',
			20
		);
	}


	/*=========================*\
	=============================
	==                         ==
	==           URL           ==
	==                         ==
	=============================
	\*=========================*/


	/**
	 * Apply the rule-logic to the specified popup
	 *
	 * @since  4.6
	 * @param  mixed $data Rule-data which was saved via the save_() handler.
	 * @return bool Decission to display popup or not.
	 */
	protected function apply_url( $data ) {
		$data = $this->sanitize_values( $data );
		$url = $this->current_url();

		return $this->check_url( $url, $data );
	}

	/**
	 * Output the Admin-Form for the active rule.
	 *
	 * @since  4.6
	 * @param  mixed $data Rule-data which was saved via the save_() handler.
	 */
	protected function form_url( $data ) {
		$data = $this->sanitize_values( $data );
		$urls = implode( "\n", $data );
		?>
		<label for="po-rule-data-url">
			<?php _e( 'URLs (one per line):', PO_LANG ); ?>
		</label>
		<textarea name="po_rule_data[url]" id="po-rule-data-url" class="block"><?php
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
	protected function save_url() {
		return $this->sanitize_values( @$_POST['po_rule_data']['url'] );
	}


	/*============================*\
	================================
	==                            ==
	==           NO_URL           ==
	==                            ==
	================================
	\*============================*/


	/**
	 * Apply the rule-logic to the specified popup
	 *
	 * @since  4.6
	 * @param  mixed $data Rule-data which was saved via the save_() handler.
	 * @return bool Decission to display popup or not.
	 */
	protected function apply_no_url( $data ) {
		$data = $this->sanitize_values( $data );
		$url = $this->current_url();

		return ! $this->check_url( $url, $data );
	}

	/**
	 * Output the Admin-Form for the active rule.
	 *
	 * @since  4.6
	 * @param  mixed $data Rule-data which was saved via the save_() handler.
	 */
	protected function form_no_url( $data ) {
		$data = $this->sanitize_values( $data );
		$urls = implode( "\n", $data );
		?>
		<label for="po-rule-data-no-url">
			<?php _e( 'URLs (one per line):', PO_LANG ); ?>
		</label>
		<textarea name="po_rule_data[no_url]" id="po-rule-data-no-url" class="block"><?php
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
	protected function save_no_url() {
		return $this->sanitize_values( @$_POST['po_rule_data']['no_url'] );
	}


	/*======================================*\
	==========================================
	==                                      ==
	==           HELPER FUNCTIONS           ==
	==                                      ==
	==========================================
	\*======================================*/


	/**
	 * Sanitizes the data parameter so it can be savely used by other functions.
	 *
	 * @since  4.6
	 * @param  mixed $data
	 * @return array
	 */
	protected function sanitize_values( $data ) {
		if ( is_string( $data ) ) {
			$data = explode( "\n", $data );
		} else if ( ! is_array( $data ) ) {
			$data = array();
		}

		return $data;
	}

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
	protected function check_url( $test_url, $list ) {
		$response = false;
		$list = array_map( 'trim', $list );

		if ( empty( $list ) ) {
			$response = true;
		} else {
			foreach ( $list as $match ) {
				$res = stripos( $test_url, $match );
				if ( false !== $res ) {
					$response = true;
					break;
				}
			}
		}

		return $response;
	}

};

IncPopupRules::register( 'IncPopupRule_Url' );