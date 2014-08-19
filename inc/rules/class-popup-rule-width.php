<?php
/*
Name:        Screen Size
Plugin URI:  http://premium.wpmudev.org/project/the-pop-over-plugin/
Description: Adds a condition that can limit PopUps to certain screen sizes.
Author:      Ve (Incsub)
Author URI:  http://premium.wpmudev.org
Type:        Rule
Rules:       Depending on screen size
Limit:       pro
Version:     1.0

NOTE: DON'T RENAME THIS FILE!!
This filename is saved as metadata with each popup that uses these rules.
Renaming the file will DISABLE the rules, which is very bad!
*/

class IncPopupRule_Width extends IncPopupRule {

	/**
	 * Initialize the rule object.
	 *
	 * @since  4.6
	 */
	protected function init() {
		$this->filename = basename( __FILE__ );

		// 'width' rule.
		$this->add_rule(
			'width',
			__( 'Depending on screen size', PO_LANG ),
			__(
				'Shows the PopUp if the window-width is within the defined ' .
				'limits. Note: The window size is checked upon page load! ' .
				'when the user resizes the window after the page is loaded it ' .
				'will not affect this rule.', PO_LANG
			),
			'',
			30
		);

		// -- Init the rule.

		$this->max_width = apply_filters( 'popup-rule-max-screen-width', 2400 );

		add_filter(
			'popup-output-data',
			array( $this, 'append_data_width' ),
			10, 2
		);
	}

	/**
	 * Returns the javascript to evaluate the rule.
	 *
	 * @since  4.6
	 */
	public function script_width() {
		ob_start();
		?>
		var apply_rule = function (e, popup, data) {
			var reject = false, width = jQuery(window).width();
			data = data || {};
			if ( ! isNaN(data.width_min) && data.width_min > 0 ) {
				if ( width < data.width_min ) { reject = true; }
			}
			if ( ! isNaN(data.width_max) && data.width_max > 0 ) {
				if ( width > data.width_max ) { reject = true; }
			}

			if ( reject ) {
				popup.reject();
			}
		};

		jQuery(document).on( 'popup-init', apply_rule );
		<?php
		$code = ob_get_clean();
		return $code;
	}

	/**
	 * Append data to the popup javascript-variable.
	 *
	 * @since  4.6
	 * @param  array $data Data collection that is printed to javascript.
	 * @param  IncPopupItem $popup The original popup object.
	 * @return array Modified data collection.
	 */
	public function append_data_width( $script_data, $popup ) {
		if ( $popup->uses_rule( 'width' ) ) {
			$data = $this->sanitize_values( @$popup->rule_data['width'] );

			if ( $data['max'] >= $this->max_width ) { $data['max'] = 0; }

			$script_data['width_min'] = $data['min'];
			$script_data['width_max'] = $data['max'];
			$script_data['script'] = $this->script_width();
		}

		return $script_data;
	}


	/*===========================*\
	===============================
	==                           ==
	==           WIDTH           ==
	==                           ==
	===============================
	\*===========================*/


	/**
	 * Output the Admin-Form for the active rule.
	 *
	 * @since  4.6
	 * @param  mixed $data Rule-data which was saved via the save_() handler.
	 */
	protected function form_width( $data ) {
		$data = $this->sanitize_values( $data );
		?>
		<div class="slider-wrap">
			<div class="slider-data">
				<label for="po-rule-data-width-min">
					<?php _e( 'At least:', PO_LANG ); ?>
				</label>

				<span class="slider-min-input">
					<input type="number"
						min="0"
						max="<?php echo esc_attr( $this->max_width ); ?>"
						max-length="4"
						name="po_rule_data[width][min]"
						id="po-rule-data-width-min"
						class="inp-small"
						value="<?php echo esc_attr( $data['min'] ); ?>" />px
				</span>
				<input type="text"
					class="slider-min-ignore inp-small"
					readonly="readonly"
					value="<?php _e( 'Any size', PO_LANG ); ?>" />
				<br />

				<label for="po-rule-data-width-max">
					<?php _e( 'At most:', PO_LANG ); ?>
				</label>

				<span class="slider-max-input">
					<input type="number"
						min="0"
						max="<?php echo esc_attr( $this->max_width ); ?>"
						max-length="4"
						name="po_rule_data[width][max]"
						id="po-rule-data-width-max"
						class="inp-small"
						value="<?php echo esc_attr( $data['max'] ); ?>" />px
				</span>
				<input type="text"
					class="slider-max-ignore inp-small"
					readonly="readonly"
					value="<?php _e( 'Any size', PO_LANG ); ?>" />
			</div>
			<div class="slider"
				data-min="0"
				data-max="<?php echo esc_attr( $this->max_width ); ?>"
				data-input="#po-rule-data-width-">
			</div>
		</div>
		<?php
	}

	/**
	 * Update and return the $settings array to save the form values.
	 *
	 * @since  4.6
	 * @return mixed Data collection of this rule.
	 */
	protected function save_width() {
		return $this->sanitize_values( @$_POST['po_rule_data']['width'] );
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
		if ( is_numeric( $data ) ) {
			$data = array( 'min' => $data, 'max' => $this->max_width );
		} else if ( ! is_array( $data ) ) {
			$data = array();
		}

		$data['min'] = absint( @$data['min'] );
		$data['max'] = absint( @$data['max'] );

		if ( $data['max'] == 0 || $data['max'] < $data['min'] ) {
			$data['max'] = $this->max_width;
		}

		return $data;
	}

};

IncPopupRules::register( 'IncPopupRule_Width' );
