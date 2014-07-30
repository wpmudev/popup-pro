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
	public function init() {
		// 'url' rule.
		$this->add_info(
			'url',
			__( 'On specific URL', PO_LANG ),
			__( 'Shows the Pop Up if the user is on a certain URL (enter one URL per line)', PO_LANG )
		);

		// 'no_url' rule.
		$this->add_info(
			'no_url',
			__( 'Not on specific URL', PO_LANG ),
			__( 'Shows the Pop Up if the user is not on a certain URL (enter one URL per line)', PO_LANG )
		);
	}

	/**
	 * Apply the rule-logic to the specified popup
	 *
	 * @since  4.6
	 * @param  bool $show Current decission whether popup should be displayed.
	 * @param  Object $popup The popup that is evaluated.
	 * @return bool Updated decission to display popup or not.
	 */
	public function apply( $show, $popup ) {
		return $show;
	}

	/**
	 * Output the Admin-Form for the active rule.
	 *
	 * @since  4.6
	 * @param  Object $popup The popup that is edited.
	 * @param  string $key Rule-ID.
	 */
	public function form( $popup, $key ) {
		?>
		Form options...
		<?php
	}

	/**
	 * Update and return the $settings array to save the form values.
	 *
	 * @since  4.6
	 * @param  array $settings Collection of rule-settings.
	 * @param  string $key Rule-ID.
	 * @return array The updated rule-settings collection.
	 */
	public function save( $settings, $key ) {
		return $settings;
	}

};

IncPopupRules::register( 'IncPopupRule_Url' );