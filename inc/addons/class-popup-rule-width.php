<?php
/*
Addon Name:  Minimum width rule
Plugin URI:  http://premium.wpmudev.org/project/the-pop-over-plugin/
Description: Adds screen maximum width rule.
Author:      Ve (Incsub)
Author URI:  http://premium.wpmudev.org
Type:        Rule
Version:     1.0
*/

class IncPopupRule_Width extends IncPopupRule {

	/**
	 * Initialize the rule object.
	 *
	 * @since  4.6
	 */
	protected function init() {
		$this->filename = basename( __FILE__ );

		// 'min_width' rule.
		$this->add_rule(
			'min_width',
			__( 'On widows wider than...', PO_LANG ),
			__(
				'Shows the Pop Up if the window is wider than the defined ' .
				'minimum-width. The window size is checked upon page load; ' .
				'when user resizes the window after the page is loaded it ' .
				'will not affect this rule.', PO_LANG
			),
			'',
			30
		);

		// -- Init the rule.

		add_action(
			'wp_footer',
			array( $this, 'inject_script' )
		);

		add_filter(
			'popup-output-data',
			array( $this, 'append_data' ),
			10, 2
		);
	}

	/**
	 * This javascript is the actual condition.
	 *
	 * @since  4.6
	 */
	public function inject_script() {
		?>
		<script>
			(function ($) {
				var apply_rule = function (e, popup, data) {
					var data = data || {};
					if ( ! data.threshold_min ) return true;
					if ( jQuery(window).width() > data.threshold_min ) return true;
					popup.reject();
				};

				jQuery(document).on( 'popup-init', apply_rule );
			});
		</script>
		<?php
	}

	/**
	 * Append data to the popup javascript-variable.
	 *
	 * @since  4.6
	 * @param  array $data Data collection that is printed to javascript.
	 * @param  IncPopupItem $popup The original popup object.
	 * @return array Modified data collection.
	 */
	public function append_data( $data, $popup ) {
		$data['threshold_min'] = absint( @$popup->rule_data['min_width'] );
		return $data;
	}


	/*===============================*\
	===================================
	==                               ==
	==           MIN_WIDTH           ==
	==                               ==
	===================================
	\*===============================*/


	/**
	 * Output the Admin-Form for the active rule.
	 *
	 * @since  4.6
	 * @param  mixed $data Rule-data which was saved via the save_() handler.
	 */
	protected function form_min_width( $data ) {
		$data = absint( $data );
		$val = $data > 0 ? $data : '';
		?>
		<label for="po-rule-data-min-width">
			<?php _e( 'Threshold width:', PO_LANG ); ?>
		</label>

		<input type="number"
			min="1"
			max="99999"
			max-length="5"
			name="po_rule_data[min_width]"
			id="po-rule-data-min-width"
			value="<?php echo esc_attr( $val ); ?>" />px
		<?php
	}

	/**
	 * Update and return the $settings array to save the form values.
	 *
	 * @since  4.6
	 * @return mixed Data collection of this rule.
	 */
	protected function save_min_width() {
		return absint( @$_POST['po_rule_data']['min_width'] );
	}

};

IncPopupRules::register( 'IncPopupRule_Width' );
