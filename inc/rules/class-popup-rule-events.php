<?php
/*
Name:        JavaScript Events
Plugin URI:  http://premium.wpmudev.org/project/the-pop-over-plugin/
Description: New Behavior Options: Show PopUp when the mouse leaves the browser window or when the user clicks somewhere.
Author:      Ve (Incsub)
Author URI:  http://premium.wpmudev.org
Type:        Rule
Rules:
Limit:       pro
Version:     1.0

NOTE: DON'T RENAME THIS FILE!!
This filename is saved as metadata with each popup that uses these rules.
Renaming the file will DISABLE the rules, which is very bad!
*/


class IncPopupRule_Events extends IncPopupRule {

	/**
	 * Initialize the rule object.
	 *
	 * @since  4.6
	 */
	protected function init() {
		$this->filename = basename( __FILE__ );

		IncPopupItem::$display_opts[] = 'leave';
		IncPopupItem::$display_opts[] = 'click';

		add_action(
			'popup-display-behavior',
			array( $this, 'display_options' ),
			10, 1
		);

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

	/**
	 * Renders the new display options on the meta_behavior.php view
	 *
	 * @since  4.6
	 * @param  IncPopupItem $popup The PopUp that is displayed
	 */
	public function display_options( $popup ) {
		$this->form_mouseleave( $popup );
		$this->form_click( $popup );
	}


	/*=============================*\
	=================================
	==                             ==
	==           ON_EXIT           ==
	==                             ==
	=================================
	\*=============================*/


	protected function form_mouseleave( $popup ) {
		?>
		<div class="col-12 inp-row">
			<label class="inp-height">
				<input type="radio"
					name="po_display"
					id="po-display-leave"
					value="leave"
					data-toggle=".opt-display-leave"
					<?php checked( $popup->display, 'leave' ); ?> />
				<?php _e( 'Appear when the mouse leaves the browser window', 'popover' ); ?>
			</label>
		</div>
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
	public function append_data_on_exit( $script_data, $popup ) {
		$script_data = lib3()->array->get( $script_data );

		if ( 'leave' == $popup->display ) {
			if ( ! isset( $script_data['script'] ) ) {
				$script_data['script'] = '';
			}

			$script_data['script'] .= 'me.custom_handler = ' . $this->script_on_exit();
		}

		return $script_data;
	}

	/**
	 * Returns the javascript code that triggers the exit event.
	 *
	 * @since  4.6
	 */
	public function script_on_exit() {
		ob_start();
		?>
		function( me ) {
			var tmr = null;

			function set( ev ) {
				if ( ! me ) return;
				tmr = setTimeout( function trigger() {
					me.show_popup();
					me = false;

					jQuery( 'html' ).off( 'mousemove', reset );
					jQuery( document ).off( 'mouseleave', set );
				}, 10 );
			}

			function reset( ev ) {
				clearTimeout( tmr );
			}

			jQuery( 'html' ).on( 'mousemove', reset );
			jQuery( document ).on( 'mouseleave', set );
		}
		<?php
		$code = ob_get_clean();
		return $code;
	}


	/*==============================*\
	==================================
	==                              ==
	==           ON_CLICK           ==
	==                              ==
	==================================
	\*==============================*/


	protected function form_click( $popup ) {
		?>
		<div class="col-12 inp-row">
			<label>
				<input type="radio"
					name="po_display"
					id="po-display-click"
					value="click"
					data-toggle=".opt-display-click"
					<?php checked( $popup->display, 'click' ); ?> />
				<?php _e( 'Appear when user clicks on a CSS selector', 'popover' ); ?>
			</label>
			<span class="opt-display-click">
				<input type="text"
					name="po_display_data[click]"
					value="<?php echo esc_attr( @$popup->display_data['click'] ); ?>"
					placeholder="<?php _e( '.class or #id', 'popover' ); ?>" />
			</span>
			<span class="opt-display-click">
				<label data-tooltip="Repeated: The PopUp will be displayed on every click. Otherwise it will be opened only once (on the first click)" data-pos="top" data-width="200">
					<input type="checkbox"
						name="po_display_data[click_multi]"
						<?php checked( ! empty( $popup->display_data['click_multi'] ) ); ?>/>
					<?php _e( 'Repeated', 'popover' ); ?>
				</label>
			</span>
		</div>
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
	public function append_data_on_click( $script_data, $popup ) {
		$script_data = lib3()->array->get( $script_data );

		if ( 'click' == $popup->display ) {
			if ( ! isset( $script_data['script'] ) ) {
				$script_data['script'] = '';
			}

			$script_data['script'] .= 'me.custom_handler = ' . $this->script_on_click();
		}

		return $script_data;
	}

	/**
	 * Returns the javascript code that triggers the click event.
	 *
	 * @since  4.6
	 */
	public function script_on_click() {
		ob_start();
		?>
		function( me ) {
			if ( me.data.display_data['click_multi'] ) {
				jQuery(document).on( 'click', me.data.display_data['click'], me.show_popup );
			} else {
				jQuery(document).one( 'click', me.data.display_data['click'], me.show_popup );
			}
		}
		<?php
		$code = ob_get_clean();
		return $code;
	}
};

IncPopupRules::register( 'IncPopupRule_Events' );

