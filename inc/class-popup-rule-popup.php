<?php
/**
 * Core rule: Count
 *
 * @since  4.6
 */
class IncPopupRule_Count extends IncPopupRule {

	/**
	 * Initialize the rule object.
	 *
	 * @since  4.6
	 */
	protected function init() {
		// 'count' rule.
		$this->add_info(
			'count',
			__( 'Pop Up shown less than', PO_LANG ),
			__( 'Shows the Pop Up if the user has only seen it less than a specific number of times.', PO_LANG )
		);
	}


	/*===========================*\
	===============================
	==                           ==
	==           COUNT           ==
	==                           ==
	===============================
	\*===========================*/


	/**
	 * Apply the rule-logic to the specified popup
	 *
	 * @since  4.6
	 * @param  mixed $data Rule-data which was saved via the save_() handler.
	 * @return bool Decission to display popup or not.
	 */
	protected function apply_count( $data ) {
		return true;
	}

	/**
	 * Output the Admin-Form for the active rule.
	 *
	 * @since  4.6
	 * @param  mixed $data Rule-data which was saved via the save_() handler.
	 */
	protected function form_count( $data ) {
		$count = absint( $data );
		if ( $count < 1 ) { $count = 1; }
		?>
		<label for="po-max-count">
			<?php _e( 'Display Pop Up this often:', PO_LANG ); ?>
		</label>
		<input type="number"
			id="po-max-count"
			class="inp-small"
			name="po_rule_data[count]"
			min="1"
			max="999"
			maxlength="3"
			placeholder="10"
			value="<?php echo esc_attr( absint( $count ) ); ?>" />
		<?php
	}

	/**
	 * Update and return the $settings array to save the form values.
	 *
	 * @since  4.6
	 * @return mixed Data collection of this rule.
	 */
	protected function save_count() {
		$count = absint( @$_POST['po_rule_data']['count'] );
		if ( $count < 1 ) { $count = 1; }
		return $count;
	}

};

IncPopupRules::register( 'IncPopupRule_Count' );