<?php
/**
 * Metabox "Appearance"
 *
 * Used in class-popup-admin.php
 * Available variables: $popup
 */

$styles = apply_filters( 'popup-styles', array() );

$animations_in = array(
	'' => array(
		'' => __( '(No Animation)', PO_LANG ),
	),
	__( 'Attention Seekers', PO_LANG ) => array(
		'bounce' => __( 'Bounce', PO_LANG ),
		'flash' => __( 'Flash', PO_LANG ),
		'pulse' => __( 'Pulse', PO_LANG ),
		'rubberBand' => __( 'Rubber Band', PO_LANG ),
		'shake' => __( 'Shake', PO_LANG ),
		'swing' => __( 'Swing', PO_LANG ),
		'tada' => __( 'Tada', PO_LANG ),
		'wobble' => __( 'Wobble', PO_LANG ),
	),
	__( 'Bouncing Entrances', PO_LANG ) => array(
		'bounceIn' => __( 'Bounce In', PO_LANG ),
		'bounceInDown' => __( 'Bounce In Down', PO_LANG ),
		'bounceInLeft' => __( 'Bounce In Left', PO_LANG ),
		'bounceInRight' => __( 'Bounce In Right', PO_LANG ),
		'bounceInUp' => __( 'Bounce In Up', PO_LANG ),
	),
	__( 'Fading Entrances', PO_LANG ) => array(
		'fadeIn' => __( 'Fade In', PO_LANG ),
		'fadeInDown' => __( 'Fade In Down', PO_LANG ),
		'fadeInDownBig' => __( 'Fade In Down Big', PO_LANG ),
		'fadeInLeft' => __( 'Fade In Left', PO_LANG ),
		'fadeInLeftBig' => __( 'Fade In Left Big', PO_LANG ),
		'fadeInRight' => __( 'Fade In Right', PO_LANG ),
		'fadeInRightBig' => __( 'Fade In Right Big', PO_LANG ),
		'fadeInUp' => __( 'Fade In Up', PO_LANG ),
		'fadeInUpBig' => __( 'Fade In Up Big', PO_LANG ),
	),
	__( 'Flippers', PO_LANG ) => array(
		'flip' => __( 'Flip', PO_LANG ),
		'flipInX' => __( 'Flip In X', PO_LANG ),
		'flipInY' => __( 'Flip In Y', PO_LANG ),
	),
	__( 'Lightspeed', PO_LANG ) => array(
		'lightSpeedIn' => __( 'Light Speed In', PO_LANG ),
	),
	__( 'Rotating Entrances', PO_LANG ) => array(
		'rotateIn' => __( 'Rotate In', PO_LANG ),
		'rotateInDownLeft' => __( 'Rotate In Down Left', PO_LANG ),
		'rotateInDownRight' => __( 'Rotate In Down Right', PO_LANG ),
		'rotateInUpLeft' => __( 'Rotate In Up Left', PO_LANG ),
		'rotateInUpRight' => __( 'Rotate In Up Right', PO_LANG ),
	),
	__( 'Specials', PO_LANG ) => array(
		'rollIn' => __( 'Roll In', PO_LANG ),
	),
	__( 'Zoom Entrances', PO_LANG ) => array(
		'zoomIn' => __( 'Zoom In', PO_LANG ),
		'zoomInDown' => __( 'Zoom In Down', PO_LANG ),
		'zoomInLeft' => __( 'Zoom In Left', PO_LANG ),
		'zoomInRight' => __( 'Zoom In Right', PO_LANG ),
		'zoomInUp' => __( 'Zoom In Up', PO_LANG ),
	),
);

$animations_out = array(
	'' => array(
		'' => __( '(No Animation)', PO_LANG ),
	),
	__( 'Bouncing Exits', PO_LANG ) => array(
		'bounceOut' => __( 'Bounce Out', PO_LANG ),
		'bounceOutDown' => __( 'Bounce Out Down', PO_LANG ),
		'bounceOutLeft' => __( 'Bounce Out Left', PO_LANG ),
		'bounceOutRight' => __( 'Bounce Out Right', PO_LANG ),
		'bounceOutUp' => __( 'Bounce Out Up', PO_LANG ),
	),
	__( 'Fading Exits', PO_LANG ) => array(
		'fadeOut' => __( 'Fade Out', PO_LANG ),
		'fadeOutDown' => __( 'Fade Out Down', PO_LANG ),
		'fadeOutDownBig' => __( 'Fade Out Down Big', PO_LANG ),
		'fadeOutLeft' => __( 'Fade Out Left', PO_LANG ),
		'fadeOutLeftBig' => __( 'Fade Out Left Big', PO_LANG ),
		'fadeOutRight' => __( 'Fade Out Right', PO_LANG ),
		'fadeOutRightBig' => __( 'Fade Out Right Big', PO_LANG ),
		'fadeOutUp' => __( 'Fade Out Up', PO_LANG ),
		'fadeOutUpBig' => __( 'Fade Out Up Big', PO_LANG ),
	),
	__( 'Flippers', PO_LANG ) => array(
		'flipOutX' => __( 'Flip Out X', PO_LANG ),
		'flipOutY' => __( 'Flip Out Y', PO_LANG ),
	),
	__( 'Lightspeed', PO_LANG ) => array(
		'lightSpeedOut' => __( 'Light Speed Out', PO_LANG ),
	),
	__( 'Rotating Exits', PO_LANG ) => array(
		'rotateOut' => __( 'Rotate Out', PO_LANG ),
		'rotateOutDownLeft' => __( 'Rotate Out Down Left', PO_LANG ),
		'rotateOutDownRight' => __( 'Rotate Out Down Right', PO_LANG ),
		'rotateOutUpLeft' => __( 'Rotate Out Up Left', PO_LANG ),
		'rotateOutUpRight' => __( 'Rotate Out Up Right', PO_LANG ),
	),
	__( 'Specials', PO_LANG ) => array(
		'hinge' => __( 'Hinge', PO_LANG ),
		'rollOut' => __( 'Roll Out', PO_LANG ),
	),
	__( 'Zoom Exits', PO_LANG ) => array(
		'zoomOut' => __( 'Zoom Out', PO_LANG ),
		'zoomOutDown' => __( 'Zoom Out Down', PO_LANG ),
		'zoomOutLeft' => __( 'Zoom Out Left', PO_LANG ),
		'zoomOutRight' => __( 'Zoom Out Right', PO_LANG ),
		'zoomOutUp' => __( 'Zoom Out Up', PO_LANG ),
	),
);



?>
<div class="wpmui-grid-12">
	<div class="col-12">
		<label for="po-style">
			<strong>
				<?php _e( 'Select which style you want to use:', PO_LANG ); ?>
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
			<?php foreach ( $styles as $key => $data ) :
				if ( ! isset( $data->deprecated ) ) { $data->deprecated = false; }
				if ( $data->deprecated && $popup->style != $key ) { continue; }
				?>
				<option value="<?php echo esc_attr( $key ); ?>"
					data-old="<?php echo esc_attr( $data->deprecated ); ?>"
					<?php selected( $key, $popup->style ); ?>>
					<?php echo esc_attr( $data->name ); ?>
					<?php if ( $data->deprecated ) : ?>*)<?php endif; ?>
				</option>
			<?php endforeach; ?>
		</select>
	</div>
	<div class="col-5">
		<label>
			<input type="checkbox"
				name="po_no_round_corners"
				<?php checked( $popup->round_corners, false ); ?> />
			<?php _e( 'No rounded corners', PO_LANG ); ?>
		</label>
	</div>
</div>
<?php if ( $popup->deprecated_style ) :
	?>
	<div class="wpmui-grid-12">
		<div class="col-12">
			<p style="margin-top:0"><em><?php _e(
				'*) This style is outdated and does not support all options '.
				'on this page. ' .
				'Once you save your PopUp with a new style you cannot ' .
				'revert to this style!<br />' .
				'Tipp: Use the Preview function to test this PopUp with one ' .
				'of the new styles before saving it.', PO_LANG
			); ?></em></p>
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
			<?php _e( 'Use custom colors', PO_LANG ); ?>
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
		<?php _e( 'Links, button background, heading and subheading', PO_LANG ); ?>
	</div>
	<div class="col-colorpicker inp-row">
		<input type="text"
			class="colorpicker inp-small"
			name="po_color[col2]"
			value="<?php echo esc_attr( $popup->color['col2'] ); ?>" />
		<br />
		<?php _e( 'Button text', PO_LANG ); ?>
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
			<?php _e( 'Use custom size (if selected the PopUp won\'t be responsive)', PO_LANG ); ?>
		</label>
	</div>
</div>
<div class="wpmui-grid-12 chk-custom-size">
	<div class="col-5 inp-row">
		<label for="po-size-width"><?php _e( 'Width:', PO_LANG ); ?></label>
		<input type="text"
			id="po-size-width"
			name="po_size_width"
			class="inp-small"
			value="<?php echo esc_attr( $popup->size['width'] ); ?>"
			placeholder="600px" />
	</div>
	<div class="col-5 inp-row">
		<label for="po-size-height"><?php _e( 'Height:', PO_LANG ); ?></label>
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
			<?php _e( 'Allow page to be scrolled while PopUp is visible', PO_LANG ); ?>
		</label>
	</div>
</div>

<hr />

<div class="wpmui-grid-12">
	<div class="col-6 inp-row">
		<label for="po-animation-in">
			<?php _e( 'PopUp display animation', PO_LANG ); ?>
		</label>
	</div>
	<div class="col-6 inp-row">
		<label for="po-animation-out">
			<?php _e( 'PopUp closing animation', PO_LANG ); ?>
		</label>
	</div>
	<div class="col-6 inp-row">
		<select id="po-animation-in" name="po_animation_in">
			<?php foreach ( $animations_in as $group => $items ) : ?>
				<?php if ( ! empty( $group ) ) : ?>
				<optgroup label="<?php echo esc_attr( $group ); ?>">
				<?php endif; ?>

				<?php foreach ( $items as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>"
						<?php selected( $key, $popup->animation_in ); ?>>
						<?php echo esc_attr( $label ); ?>
					</option>
				<?php endforeach; ?>

				<?php if ( ! empty( $group ) ) : ?>
				</optgroup>
				<?php endif; ?>
			<?php endforeach; ?>
		</select>
	</div>

	<div class="col-6 inp-row">
		<select id="po-animation-out" name="po_animation_out">
			<?php foreach ( $animations_out as $group => $items ) : ?>
				<?php if ( ! empty( $group ) ) : ?>
				<optgroup label="<?php echo esc_attr( $group ); ?>">
				<?php endif; ?>

				<?php foreach ( $items as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>"
						<?php selected( $key, $popup->animation_out ); ?>>
						<?php echo esc_attr( $label ); ?>
					</option>
				<?php endforeach; ?>

				<?php if ( ! empty( $group ) ) : ?>
				</optgroup>
				<?php endif; ?>
			<?php endforeach; ?>
		</select>
	</div>
</div>
