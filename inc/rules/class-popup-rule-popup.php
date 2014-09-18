<?php
/*
Name:        PopUp Details
Plugin URI:  http://premium.wpmudev.org/project/the-pop-over-plugin/
Description: Test for PopUp specific values.
Author:      Philipp (Incsub)
Author URI:  http://premium.wpmudev.org
Type:        Rule
Rules:       PopUp shown less than
Version:     1.0

NOTE: DON'T RENAME THIS FILE!!
This filename is saved as metadata with each popup that uses these rules.
Renaming the file will DISABLE the rules, which is very bad!
*/

class IncPopupRule_Popup extends IncPopupRule {

	/**
	 * Initialize the rule object.
	 *
	 * @since  4.6
	 */
	protected function init() {
		$this->filename = basename( __FILE__ );

		// 'count' rule.
		$this->add_rule(
			'count',
			__( 'PopUp shown less than', PO_LANG ),
			__( 'Shows the PopUp if the user has only seen it less than a specific number of times.', PO_LANG ),
			'',
			5
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
	 * @param  IncPopupItem $popup The PopUp that is displayed.
	 * @return bool Decission to display popup or not.
	 */
	protected function apply_count( $data, $popup ) {
		$max_count = absint( $data );
		$count = absint( @$_COOKIE['po_c-' . $popup->id] );
		return $count < $max_count;
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
			<?php _e( 'Display PopUp this often:', PO_LANG ); ?>
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

IncPopupRules::register( 'IncPopupRule_Popup' );