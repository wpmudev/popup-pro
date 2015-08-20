<?php
/*
Name:        Date specific
Plugin URI:  http://premium.wpmudev.org/project/the-pop-over-plugin/
Description: Date/time-related popups.
Author:      Philipp (Incsub)
Author URI:  http://premium.wpmudev.org
Type:        Rule
Rules:       Show PopUp on Date, Hide PopUp on Date
Limit:       pro
Version:     1.0

NOTE: DON'T RENAME THIS FILE!!
This filename is saved as metadata with each popup that uses these rules.
Renaming the file will DISABLE the rules, which is very bad!
*/

class IncPopupRule_Datetime extends IncPopupRule {

	/**
	 * Initialize the rule object.
	 *
	 * @since  4.7.1
	 */
	protected function init() {
		$this->filename = basename( __FILE__ );

		// 'date_from' rule.
		$this->add_rule(
			'date_from',
			__( 'Show PopUp on Date', PO_LANG ),
			sprintf(
				__( 'Shows the PopUp only when the specified date is reached.', PO_LANG ) . '<br />' .
				__( 'Values in %sUTC%s. Current UTC time: %s', PO_LANG ),
				'<a heref="https://en.wikipedia.org/wiki/Coordinated_Universal_Time" target="_blank">',
				'</a>',
				'<tt>' . gmdate( 'Y-m-d H:i' ) . '</tt>'
			),
			'',
			5
		);

		// 'date_until' rule.
		$this->add_rule(
			'date_until',
			__( 'Hide PopUp on Date', PO_LANG ),
			sprintf(
				__( 'Hide the PopUp once a specific date is reached.', PO_LANG ) . '<br />' .
				__( 'Values in %sUTC%s. Current UTC time: %s', PO_LANG ),
				'<a heref="https://en.wikipedia.org/wiki/Coordinated_Universal_Time" target="_blank">',
				'</a>',
				'<tt>' . gmdate( 'Y-m-d H:i' ) . '</tt>'
			),
			'',
			5
		);
	}


	/*===============================*\
	===================================
	==                               ==
	==           DATE_FROM           ==
	==                               ==
	===================================
	\*===============================*/


	/**
	 * Apply the rule-logic to the specified popup
	 *
	 * @since  4.7.1
	 * @param  mixed $data Rule-data which was saved via the save_() handler.
	 * @param  IncPopupItem $popup The PopUp that is displayed.
	 * @return bool Decission to display popup or not.
	 */
	protected function apply_date_from( $data, $popup ) {
		$data = intval( $data );

		if ( $data > 0 && time() >= $data ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Output the Admin-Form for the active rule.
	 *
	 * @since  4.7.1
	 * @param  mixed $data Rule-data which was saved via the save_() handler.
	 */
	protected function form_date_from( $data ) {
		$data = intval( $data );
		if ( ! $data ) { $data = time(); }

		$date_from = gmdate( 'Y-m-d', $data );
		$time_from = gmdate( 'H:i', $data );
		?>
		<div>
			<label for="po-date-from">
				<?php _e( 'Display when date is reached:', PO_LANG ); ?>
			</label>
			<input type="date"
				id="po-date-from"
				name="po_rule_data[date_from]"
				maxlength="10"
				value="<?php echo esc_attr( $date_from ); ?>" />
		</div>
		<div>
			<label for="po-time-from">
				<?php _e( 'After this time:', PO_LANG ); ?>
			</label>
			<input type="time"
				id="po-time-from"
				name="po_rule_data[date_from_time]"
				maxlength="5"
				value="<?php echo esc_attr( $time_from ); ?>" />
		</div>
		<?php
	}

	/**
	 * Update and return the $settings array to save the form values.
	 *
	 * @since  4.7.1
	 * @param  array $data The contents of $_POST['po_rule_data'].
	 * @return mixed Data collection of this rule.
	 */
	protected function save_date_from( $data ) {
		lib2()->array->equip( $data, 'date_from', 'date_from_time' );

		$date_str = $data['date_from'] . ' ' . $data['date_from_time'];
		$date_from = strtotime( $date_str );
		return $date_from;
	}

	/*================================*\
	====================================
	==                                ==
	==           DATE_UNTIL           ==
	==                                ==
	====================================
	\*================================*/


	/**
	 * Apply the rule-logic to the specified popup
	 *
	 * @since  4.7.1
	 * @param  mixed $data Rule-data which was saved via the save_() handler.
	 * @param  IncPopupItem $popup The PopUp that is displayed.
	 * @return bool Decission to display popup or not.
	 */
	protected function apply_date_until( $data, $popup ) {
		$data = intval( $data );

		if ( $data > 0 && time() <= $data ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Output the Admin-Form for the active rule.
	 *
	 * @since  4.7.1
	 * @param  mixed $data Rule-data which was saved via the save_() handler.
	 */
	protected function form_date_until( $data ) {
		$data = intval( $data );
		if ( ! $data ) { $data = time(); }

		$date_until = gmdate( 'Y-m-d', $data );
		$time_from = gmdate( 'H:i', $data );
		?>
		<div>
			<label for="po-date-until">
				<?php _e( 'Hide when date is reached:', PO_LANG ); ?>
			</label>
			<input type="date"
				id="po-date-until"
				name="po_rule_data[date_until]"
				maxlength="10"
				value="<?php echo esc_attr( $date_until ); ?>" />
		</div>
		<div>
			<label for="po-time-until">
				<?php _e( 'After this time:', PO_LANG ); ?>
			</label>
			<input type="time"
				id="po-time-until"
				name="po_rule_data[date_until_time]"
				maxlength="5"
				value="<?php echo esc_attr( $time_from ); ?>" />
		</div>
		<?php
	}

	/**
	 * Update and return the $settings array to save the form values.
	 *
	 * @since  4.7.1
	 * @param  array $data The contents of $_POST['po_rule_data'].
	 * @return mixed Data collection of this rule.
	 */
	protected function save_date_until( $data ) {
		lib2()->array->equip( $data, 'date_until', 'date_until_time' );

		$date_str = $data['date_until'] . ' ' . $data['date_until_time'];
		$date_until = strtotime( $date_str );
		if ( $date_until ) {
			$date_until += 59; // add 59 seconds
		}
		return $date_until;
	}

};

IncPopupRules::register( 'IncPopupRule_Datetime' );