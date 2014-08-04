<?php
/*
Addon Name:  Javascript Events
Plugin URI:  http://premium.wpmudev.org/project/the-pop-over-plugin/
Description: Show Pop Up when user leaves the page or clicks somewhere.
Author:      Ve (Incsub)
Author URI:  http://premium.wpmudev.org
Type:        Rule
Version:     1.0
*/

class IncPopupRule_Events extends IncPopupRule {

	/**
	 * Initialize the rule object.
	 *
	 * @since  4.6
	 */
	protected function init() {
		$this->filename = basename( __FILE__ );

		// 'on_exit' rule.
		$this->add_rule(
			'on_exit',
			__( 'When user leaves the page', PO_LANG ),
			__( 'Shows the Pop Up when the user tries to leave the page.', PO_LANG ),
			'',
			30
		);

		// 'on_click' rule.
		$this->add_rule(
			'on_click',
			__( 'Show on click', PO_LANG ),
			__( 'Shows the Pop Up when the user clicks at a certain element on the page.', PO_LANG ),
			'',
			30
		);

		// -- Initialize rule.

		add_filter(
			'popup-output-data',
			array( $this, 'append_data_on_exit' ),
			10, 2
		);

		add_filter(
			'popup-output-data',
			array( $this, 'append_data_on_click' ),
			10, 2
		);
	}


	/*=============================*\
	=================================
	==                             ==
	==           ON_EXIT           ==
	==                             ==
	=================================
	\*=============================*/


	/**
	 * Append data to the popup javascript-variable.
	 *
	 * @since  4.6
	 * @param  array $data Data collection that is printed to javascript.
	 * @param  IncPopupItem $popup The original popup object.
	 * @return array Modified data collection.
	 */
	public function append_data_on_exit( $script_data, $popup ) {
		if ( $popup->uses_rule( 'on_exit' ) ) {
			$script_data['wait_for_event'] = true;
			$script_data['fire_on_exit'] = true;

			add_action(
				'wp_footer',
				array( $this, 'inject_script_on_exit' )
			);
		}
	}

	/**
	 * Injects some javascript for the rule into the page footer.
	 *
	 * @since  4.6
	 */
	public function inject_script_on_exit() {
		?>
		<script>
			(function() {
				//
				// TODO: REVIEW AND FIX THIS. LOOKS STRANGE (why "mouseleave"??)....
				//
				jQuery(document).on('popover-init', function( e, popover, data ) {
					var data = data || {};
					if ( ! data.wait_for_event || ! data.fire_on_exit ) {
						return true;
					}

					jQuery(document).one('mouseleave', function() {
						popover.resolve();
						return false;
					});
				});
			})();
		</script>
		<?php
	}


	/*==============================*\
	==================================
	==                              ==
	==           ON_CLICK           ==
	==                              ==
	==================================
	\*==============================*/


	/**
	 * Output the Admin-Form for the active rule.
	 *
	 * @since  4.6
	 * @param  mixed $data Rule-data which was saved via the save_() handler.
	 */
	protected function form_on_click( $data ) {
		?>
		<div>
			<label for="po-rule-data-on-click-selector">
				<?php _e( 'Element selector', PO_LANG ); ?>
			</label>
			<input type="text"
				name="po_rule_data[on_click][selector]"
				id="po-rule-data-on-click-selector"
				value="<?php echo esc_attr( @$data['selector'] ); ?>" />
		</div>
		<div>
			<label>
				<input type="radio"
					name="po_rule_data[on_click][multi_open]"
					id="po-rule-data-on-click-multi-off"
					value="0"
					<?php checked( ! @$data['multi_open'] ); ?>/>
				<?php _e( 'Open Pop Up only once (on first click)', PO_LANG ); ?>
			</label><br />
			<label>
				<input type="radio"
					name="po_rule_data[on_click][multi_open]"
					id="po-rule-data-on-click-multi-on"
					value="1"
					<?php checked( ! ! @$data['multi_open'] ); ?>/>
				<?php _e( 'Open Pop Up on every click', PO_LANG ); ?>
			</label>
		</div>
		<?php
	}

	/**
	 * Update and return the $settings array to save the form values.
	 *
	 * @since  4.6
	 * @return mixed Data collection of this rule.
	 */
	protected function save_on_click() {
		return @$_POST['po_rule_data']['on_click'];
	}

	/**
	 * Append data to the popup javascript-variable.
	 *
	 * @since  4.6
	 * @param  array $data Data collection that is printed to javascript.
	 * @param  IncPopupItem $popup The original popup object.
	 * @return array Modified data collection.
	 */
	public function append_data_on_click( $script_data, $popup ) {
		if ( $popup->uses_rule( 'on_exit' ) ) {
			$script_data['wait_for_event'] = true;
			$script_data['click_selector'] = $popup->rule_data['on_click']['selector'];
			$script_data['multi_open'] = ! ! $popup->rule_data['on_click']['multi_open'];

			add_action(
				'wp_footer',
				array( $this, 'inject_script_on_click' )
			);
		}

		return $script_data;
	}

	/**
	 * Injects some javascript for the rule into the page footer.
	 *
	 * @since  4.6
	 */
	public function inject_script_on_click() {
		?>
		<script>
			(function() {
				//
				// TODO: REVIEW AND FIX THIS. LOOKS STRANGE (why "mouseleave"??)....
				//
				jQuery(document).on('popover-init', function( e, popover, data ) {
					var data = data || {};
					if ( ! data.wait_for_event || ! data.click_selector ) {
						return true;
					}

					jQuery(document).one('click', data.click_selector, function (e) {
						popover.resolve();
						return false;
					});
				});
			});
		</script>
		<?php
	}

};

IncPopupRules::register( 'IncPopupRule_Events' );
