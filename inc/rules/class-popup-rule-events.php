<?php
/*
Name:        Javascript Events
Plugin URI:  http://premium.wpmudev.org/project/the-pop-over-plugin/
Description: New Behavior Options: Show Pop Up when the mouse leaves the browser window or when the user clicks somewhere.
Author:      Ve (Incsub)
Author URI:  http://premium.wpmudev.org
Type:        Rule
Rules:
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

		add_action(
			'popup-output-data',
			array( $this, 'append_data_on_exit' ),
			10, 2
		);

		add_action(
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


	public function display_options( $popup ) {
		$this->form_mouseleave( $popup );
		$this->form_click( $popup );
	}

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
				<?php _e( 'Appear when the mouse leaves the browser window', PO_LANG ); ?>
			</label>
		</div>
		<?php
	}

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
				<?php _e( 'Appear when user clicks on a CSS selector', PO_LANG ); ?>
			</label>
			<span class="opt-display-click">
				<input type="text"
					maxlength="50"
					name="po_display_data[click]"
					value="<?php echo esc_attr( @$popup->display_data['click'] ); ?>"
					placeholder="<?php _e( '.class or #id', PO_LANG ); ?>" />
			</span>
			<span class="opt-display-click">
				<label data-tooltip="Repeated: The Pop Up will be displayed on every click. Otherwise it will be opened only once (on the first click)" data-pos="top" data-width="200">
					<input type="checkbox"
						name="po_display_data[click_multi]"
						<?php checked( ! empty( $popup->display_data['click_multi'] ) ); ?>/>
					<?php _e( 'Repeated', PO_LANG ); ?>
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
	public function append_data_on_exit( $script_data, $popup ) {
		if ( 'leave' == $popup->display ) {
			add_action(
				'wp_footer',
				array( $this, 'inject_script_on_exit' )
			);
		}

		return $script_data;
	}

	/**
	 * Injects some javascript for the rule into the page footer.
	 *
	 * @since  4.6
	 */
	public function inject_script_on_exit() {
		?>
		<script>
			jQuery(function(){
				setTimeout(function(){
					inc_popup.extend.custom_handler = function( popup ) {
						jQuery(document).one( 'mouseleave', function() {
							popup.show();
							return false;
						});
					};
				}, 10);
			});
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
	 * Append data to the popup javascript-variable.
	 *
	 * @since  4.6
	 * @param  array $data Data collection that is printed to javascript.
	 * @param  IncPopupItem $popup The original popup object.
	 * @return array Modified data collection.
	 */
	public function append_data_on_click( $script_data, $popup ) {
		if ( 'click' == $popup->display ) {
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
			jQuery(function(){
				setTimeout(function(){
					inc_popup.extend.custom_handler = function( popup ) {
						if ( popup.data.display_data['click_multi'] ) {
							jQuery(document).on( 'click', popup.data.display_data['click'], popup.show );
						} else {
							jQuery(document).one( 'click', popup.data.display_data['click'], popup.show );
						}
					};
				}, 10);
			});

		</script>
		<?php
	}

};

IncPopupRules::register( 'IncPopupRule_Events' );
