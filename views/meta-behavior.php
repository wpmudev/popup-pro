<?php
/**
 * Metabox "Behavior"
 *
 * Used in class-popup-admin.php
 * Available variables: $popup
 */

?>
<div class="wpmui-grid-12">
	<div class="col-12">
		<strong><?php _e( 'When to show the PopUp:', 'popover' ); ?></strong>
	</div>
</div>
<div class="wpmui-grid-12" style="overflow: visible">
	<div class="col-12 inp-row">
		<label>
			<input type="radio"
				name="po_display"
				id="po-display-delay"
				value="delay"
				data-toggle=".opt-display-delay"
				<?php checked( $popup->display, 'delay' ); ?> />
			<?php _e( 'Appear after', 'popover' ); ?>
		</label>
		<span class="opt-display-delay">
			<input type="number"
				min="0"
				max="999"
				maxlength="3"
				name="po_display_data[delay]"
				class="inp-small"
				value="<?php echo esc_attr( $popup->display_data['delay'] ); ?>"
				placeholder="10" />
			<select name="po_display_data[delay_type]">
				<option value="s" <?php selected( $popup->display_data['delay_type'], 's' ); ?>>
					<?php _e( 'Seconds', 'popover' ); ?>
				</option>
				<option value="m" <?php selected( $popup->display_data['delay_type'], 'm' ); ?>>
					<?php _e( 'Minutes', 'popover' ); ?>
				</option>
			</select>
		</span>
	</div>

	<div class="col-12 inp-row">
		<label>
			<input type="radio"
				name="po_display"
				id="po-display-scroll"
				value="scroll"
				data-toggle=".opt-display-scroll"
				<?php checked( $popup->display, 'scroll' ); ?> />
			<?php _e( 'Appear after', 'popover' ); ?>
		</label>
		<span class="opt-display-scroll">
			<input type="number"
				min="0"
				max="9999"
				maxlength="4"
				name="po_display_data[scroll]"
				class="inp-small"
				value="<?php echo esc_attr( $popup->display_data['scroll'] ); ?>"
				placeholder="25" />
			<select name="po_display_data[scroll_type]">
				<option value="%" <?php selected( $popup->display_data['scroll_type'], '%' ); ?>>
					<?php _e( '%', 'popover' ); ?>
				</option>
				<option value="px" <?php selected( $popup->display_data['scroll_type'], 'px' ); ?>>
					<?php _e( 'px', 'popover' ); ?>
				</option>
			</select>
		</span>
		<?php _e( 'of the page has been scrolled.', 'popover' ); ?>
	</div>
	<div class="col-12 inp-row">
		<label>
			<input type="radio"
				name="po_display"
				id="po-display-anchor"
				value="anchor"
				data-toggle=".opt-display-anchor"
				<?php checked( $popup->display, 'anchor' ); ?> />
			<?php _e( 'Appear after user scrolled until CSS selector', 'popover' ); ?>
		</label>
		<span class="opt-display-anchor">
			<input type="text"
				maxlength="50"
				name="po_display_data[anchor]"
				value="<?php echo esc_attr( $popup->display_data['anchor'] ); ?>"
				placeholder="<?php _e( '.class or #id', 'popover' ); ?>" />
		</span>
	</div>
	<?php do_action( 'popup-display-behavior', $popup ); ?>
</div>

<hr />

<div class="wpmui-grid-12">
	<div class="col-12">
		<strong><?php _e( '"Never see this message again" settings:', 'popover' ); ?></strong>
	</div>
</div>
<div class="wpmui-grid-12">
	<div class="col-12 inp-row">
		<label>
			<input type="checkbox"
				name="po_can_hide"
				id="po-can-hide"
				data-toggle=".chk-can-hide"
				data-or="#po-can-hide,#po-close-hides"
				<?php checked( $popup->can_hide ); ?>/>
			<?php _e( 'Add "Never see this message again" link', 'popover' ); ?>
		</label>
	</div>
	<div class="col-12 inp-row">
		<label>
			<input type="checkbox"
				name="po_close_hides"
				id="po-close-hides"
				data-toggle=".chk-can-hide"
				data-or="#po-can-hide,#po-close-hides"
				<?php checked( $popup->close_hides ); ?>/>
			<?php _e( 'Close button acts as "Never see this message again" link', 'popover' ); ?>
		</label>
	</div>
	<div class="col-12 inp-row chk-can-hide">
		<label for="po-hide-expire">
			<?php _e( 'Expiry time', 'popover' ); ?>
			<input type="number"
				name="po_hide_expire"
				id="po-hide-expire"
				class="inp-small"
				value="<?php echo esc_attr( $popup->hide_expire ); ?>"
				placeholder="365" />
			<?php _e( 'days', 'popover' ); ?>
			<?php _e( '(upon expiry, user will see this PopUp again)', 'popover' ); ?>
		</label>
	</div>
</div>

<hr />

<div class="wpmui-grid-12">
	<div class="col-12">
		<strong><?php _e( 'Closing Pop-up conditions', 'popover' ); ?></strong>
	</div>
</div>
<div class="wpmui-grid-12">
	<div class="col-12 inp-row">
		<label>
			<input type="checkbox"
				name="po_overlay_close"
				<?php checked( ! $popup->overlay_close ); ?>
				/>
			<?php _e( 'Click on the background does not close PopUp.', 'popover' ); ?>
		</label>
	</div>

</div>

<hr />

<?php
/**
 * Choose what to do when the PopUp contains a form.
 *
 * @since  4.7.0
 */
?>
<div class="wpmui-grid-12">
	<div class="col-12">
		<strong><?php _e( 'Form submit', 'popover' ); ?></strong>
	</div>
</div>
<div class="wpmui-grid-12">
	<div class="col-12 inp-row">
		<label for="po-form-submit">
			<?php _e( 'In case your PopUp contains a form (e.g. a contact form) then you can change the form-submit behavior here.', 'popover' ); ?>
		</label>
	</div>
	<div class="col-12 inp-row">
		<select name="po_form_submit" id="po-form-submit">
			<option value="close" <?php selected( $popup->form_submit, 'close' ); ?>>
				<?php _e( 'Always close after form submit', 'popover' ); ?>
			</option>
			<option value="default" <?php selected( $popup->form_submit, 'default' ); ?>>
				<?php _e( 'Refresh PopUp or close (default)', 'popover' ); ?>
			</option>
			<option value="ignore" <?php selected( $popup->form_submit, 'ignore' ); ?>>
				<?php _e( 'Refresh PopUp or do nothing (use for Ajax Forms)', 'popover' ); ?>
			</option>
			<option value="redirect" <?php selected( $popup->form_submit, 'redirect' ); ?>>
				<?php _e( 'Redirect to form target URL', 'popover' ); ?>
			</option>
		</select>
	</div>

</div>