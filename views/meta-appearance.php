<?php
/**
 * Metabox "Appearance"
 *
 * Used in class-popup-admin.php
 * Available variables: $popup
 */

$styles = apply_filters( 'popup-styles', array() );
$animations = IncPopup::get_animations();

?>
<div class="wpmui-grid-12">
	<div class="col-12">
		<label for="po-style">
			<strong>
				<?php _e( 'Select which style you want to use:', 'popover' ); ?>
			</strong>
		</label>
	</div>
</div>
<div class="wpmui-grid-12">
	<div class="col-7">
		<input type="hidden"
			class="po-orig-style"
			name="po_orig_style"
			value="<?php echo esc_attr( $popup->style ); ?>" />
		<input type="hidden"
			class="po-orig-style-old"
			name="po_orig_style_old"
			value="<?php echo esc_attr( $popup->deprecated_style ); ?>" />
		<select class="block" id="po-style" name="po_style">
			<?php
			$disabled_items = array();
			foreach ( $styles as $key => $data ) :
				if ( ! isset( $data->deprecated ) ) { $data->deprecated = false; }
				if ( $data->deprecated && $popup->style != $key ) { continue; }
				if ( 'pro' == PO_VERSION || ! $data->pro ) { ?>
					<option value="<?php echo esc_attr( $key ); ?>"
						data-old="<?php echo esc_attr( $data->deprecated ); ?>"
						<?php selected( $key, $popup->style ); ?>>
						<?php echo esc_attr( $data->name ); ?>
						<?php if ( $data->deprecated ) : ?>*)<?php endif; ?>
					</option>
				<?php
				} else {
					$disabled_items[] = $data;
				}
			endforeach;
			foreach ( $disabled_items as $data ) : ?>
				<option disabled="disabled">
					<?php echo esc_attr( $data->name ); ?> -
					<?php _e( 'PRO Version only', PO_LANG ); ?>
				</option>
			<?php endforeach; ?>
		</select>
	</div>
	<div class="col-5">
		<label>
			<input type="checkbox"
				name="po_no_round_corners"
				<?php checked( $popup->round_corners, false ); ?> />
			<?php _e( 'No rounded corners', 'popover' ); ?>
		</label>
	</div>
</div>
<?php if ( $popup->deprecated_style ) :
	?>
	<div class="wpmui-grid-12">
		<div class="col-12">
			<p style="margin-top:0"><em><?php
			_e(
				'*) This style is outdated and does not support all options '.
				'on this page. ' .
				'Once you save your PopUp with a new style you cannot ' .
				'revert to this style!<br />' .
				'Tipp: Use the Preview function to test this PopUp with one ' .
				'of the new styles before saving it.', 'popover'
			);
			?></em></p>
		</div>
	</div>
	<?php
endif; ?>

<div class="wpmui-grid-12">
	<div class="col-12 inp-row">
		<label>
			<input type="checkbox"
				name="po_custom_colors"
				id="po-custom-colors"
				data-toggle=".chk-custom-colors"
				<?php checked( $popup->custom_colors ); ?> />
			<?php _e( 'Use custom colors', 'popover' ); ?>
		</label>
	</div>
</div>
<div class="wpmui-grid-12 chk-custom-colors">
	<div class="col-colorpicker inp-row">
		<input type="text"
			class="colorpicker inp-small"
			name="po_color[col1]"
			value="<?php echo esc_attr( $popup->color['col1'] ); ?>" />
		<br />
		<?php _e( 'Links, button background, heading and subheading', 'popover' ); ?>
	</div>
	<div class="col-colorpicker inp-row">
		<input type="text"
			class="colorpicker inp-small"
			name="po_color[col2]"
			value="<?php echo esc_attr( $popup->color['col2'] ); ?>" />
		<br />
		<?php _e( 'Button text', 'popover' ); ?>
	</div>
</div>

<div class="wpmui-grid-12">
	<div class="col-12 inp-row">
		<label>
			<input type="checkbox"
				name="po_custom_size"
				id="po-custom-size"
				data-toggle=".chk-custom-size"
				<?php checked( $popup->custom_size ); ?> />
			<?php _e( 'Use custom size (if selected the PopUp won\'t be responsive)', 'popover' ); ?>
		</label>
	</div>
</div>
<div class="wpmui-grid-12 chk-custom-size">
	<div class="col-5 inp-row">
		<label for="po-size-width"><?php _e( 'Width:', 'popover' ); ?></label>
		<input type="text"
			id="po-size-width"
			name="po_size_width"
			class="inp-small"
			value="<?php echo esc_attr( $popup->size['width'] ); ?>"
			placeholder="600px" />
	</div>
	<div class="col-5 inp-row">
		<label for="po-size-height"><?php _e( 'Height:', 'popover' ); ?></label>
		<input type="text"
			id="po-size-height"
			name="po_size_height"
			class="inp-small"
			value="<?php echo esc_attr( $popup->size['height'] ); ?>"
			placeholder="300px" />
	</div>
</div>

<div class="wpmui-grid-12">
	<div class="col-12 inp-row">
		<label>
			<input type="checkbox"
				name="po_scroll_body"
				id="po-scroll-body"
				data-toggle=".chk-scroll-body"
				<?php checked( $popup->scroll_body ); ?> />
			<?php _e( 'Allow page to be scrolled while PopUp is visible', 'popover' ); ?>
		</label>
	</div>
</div>

<hr />

<div class="wpmui-grid-12">
	<div class="col-6 inp-row">
		<label for="po-animation-in">
			<?php _e( 'PopUp display animation', 'popover' ); ?>
		</label>
	</div>
	<div class="col-6 inp-row">
		<label for="po-animation-out">
			<?php _e( 'PopUp closing animation', 'popover' ); ?>
		</label>
	</div>
	<div class="col-6 inp-row">
		<select id="po-animation-in" name="po_animation_in">
			<?php foreach ( $animations->in as $group => $items ) : ?>
				<?php if ( ! empty( $group ) ) : ?>
				<optgroup label="<?php echo esc_attr( $group ); ?>">
				<?php endif; ?>

				<?php inc_popup_show_options( $items, $popup->animation_in ); ?>

				<?php if ( ! empty( $group ) ) : ?>
				</optgroup>
				<?php endif; ?>
			<?php endforeach; ?>
		</select>
	</div>

	<div class="col-6 inp-row">
		<select id="po-animation-out" name="po_animation_out">
			<?php foreach ( $animations->out as $group => $items ) : ?>
				<?php if ( ! empty( $group ) ) : ?>
				<optgroup label="<?php echo esc_attr( $group ); ?>">
				<?php endif; ?>

				<?php inc_popup_show_options( $items, $popup->animation_out ); ?>

				<?php if ( ! empty( $group ) ) : ?>
				</optgroup>
				<?php endif; ?>
			<?php endforeach; ?>
		</select>
	</div>
</div>

<?php
function inc_popup_show_options( $items, $selected = false ) {
	$pro_only = ' - ' . __( 'PRO Version', 'popover' );

	foreach ( $items as $key => $label ) {
		if ( strpos( $label, $pro_only ) ) {
			printf(
				'<option disabled>%1$s</option>',
				esc_attr( $label )
			);
		} else {
			printf(
				'<option value="%2$s" %3$s>%1$s</option>',
				esc_attr( $label ),
				esc_attr( $key ),
				selected( $key, $selected, false )
			);
		}
	}
}
