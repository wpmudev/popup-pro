<?php
/**
 * Core rule: On Url / Not On Url
 *
 * @since  4.6
 */
class IncPopupRule_Url extends IncPopupRule {

	/**
	 * Initialize the rule object.
	 *
	 * @since  4.6
	 */
	protected function init() {
		// 'url' rule.
		$this->add_info(
			'url',
			__( 'On specific URL', PO_LANG ),
			__( 'Shows the Pop Up if the user is on a certain URL.', PO_LANG ),
			'no_url'
		);

		// 'no_url' rule.
		$this->add_info(
			'no_url',
			__( 'Not on specific URL', PO_LANG ),
			__( 'Shows the Pop Up if the user is not on a certain URL.', PO_LANG ),
			'url'
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
		return true;
	}

	/**
	 * Output the Admin-Form for the active rule.
	 *
	 * @since  4.6
	 * @param  mixed $data Rule-data which was saved via the save_() handler.
	 */
	protected function form_url( $data ) {
		if ( is_string( $data ) ) { $urls = $data; }
		else if ( is_array( $data ) ) { $urls = implode( "\n", $data ); }
		else { $urls = ''; }
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
		return explode( "\n", @$_POST['po_rule_data']['url'] );
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
		return true;
	}

	/**
	 * Output the Admin-Form for the active rule.
	 *
	 * @since  4.6
	 * @param  mixed $data Rule-data which was saved via the save_() handler.
	 */
	protected function form_no_url( $data ) {
		if ( is_string( $data ) ) { $urls = $data; }
		else if ( is_array( $data ) ) { $urls = implode( "\n", $data ); }
		else { $urls = ''; }
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
		return explode( "\n", @$_POST['po_rule_data']['no_url'] );
	}

};

IncPopupRules::register( 'IncPopupRule_Url' );