<?php
/**
 * Metabox "PopUp Content"
 *
 * Used in class-popup-admin.php
 * Available variables: $popup
 */

$has_image = ! empty( $popup->image );

?>
<div class="content-main">
	<div class="wpmui-grid-12">
		<div class="col-6">
			<label for="po-heading"><h3><?php _e( 'Heading (optional)', 'popover' ); ?></h3></label>
		</div>
		<div class="col-6">
			<label for="po-subheading"><h3><?php _e( 'Subheading (optional)', 'popover' ); ?></h3></label>
		</div>
	</div>
	<div class="wpmui-grid-12">
		<div class="col-6">
			<input class="block"
				type="text"
				id="po-heading"
				name="po_heading"
				placeholder="<?php _e( 'Enter your heading here...', 'popover' ); ?>"
				value="<?php echo esc_attr( $popup->title ); ?>" />
		</div>
		<div class="col-6">
			<input class="block"
				type="text"
				id="po-subheading"
				name="po_subheading"
				placeholder="<?php _e( 'Enter your subheading here...', 'popover' ); ?>"
				value="<?php echo esc_attr( $popup->subtitle ); ?>" />
		</div>
	</div>

	<div class="wpmui-grid-12">
		<label for="po_content">
			<h3 class="main-content"><?php _e( 'Main PopUp Content', 'popover' ); ?></h3>
		</label>
	</div>
	<div>
		<?php
		$args = array(
			'textarea_rows' => 10,
			'drag_drop_upload' => true,
		);
		wp_editor( $popup->content, 'po_content', $args );
		?>
	</div>

	<div class="wpmui-grid-12">
		<label for="po-cta">
			<h3><?php _e( 'Call To Action Button (optional)', 'popover' ); ?></h3>
		</label>
	</div>
	<div class="wpmui-grid-12">
		<div class="col-4">
			<input class="block"
				type="text"
				id="po-cta"
				name="po_cta"
				placeholder="<?php _e( 'Button Label', 'popover' ); ?>"
				value="<?php echo esc_attr( $popup->cta_label ); ?>" />
		</div>
		<div class="col-4">
			<input class="block"
				type="text"
				id="po-cta-link"
				name="po_cta_link"
				placeholder="<?php _e( 'Button Link (http://www.example.com)', 'popover' ); ?>"
				value="<?php echo esc_attr( $popup->cta_link ); ?>" />
		</div>
		<div class="col-4">
			<input class="block"
				type="text"
				id="po-cta-target"
				name="po_cta_target"
				placeholder="<?php _e( 'Optional Link target', 'popover' ); ?>"
				title="<?php _e( 'Default: _self / To open link in new window use: _blank', 'popover' ); ?>"
				value="<?php echo esc_attr( $popup->cta_target ); ?>" />
		</div>
	</div>
</div>


<div class="content-image">
	<div class="wpmui-grid-12">
		<label>
			<h3><?php _e( 'PopUp Feature Image (optional)', 'popover' ); ?></h3>
		</label>
	</div>
	<div class="wpmui-grid-12">
		<button class="button add_image"
			type="button"
			title="<?php _e( 'Add featured image to PopUp.', 'popover' ); ?>"
			data-title="<?php _e( 'PopUp Featured Image', 'popover' ); ?>"
			data-button="<?php _e( 'Select Image', 'popover' ); ?>" >
			<i class="add-image-icon dashicons dashicons-format-image"></i>
			<?php _e( 'Add Image', 'popover' ); ?>
		</button>

		<input type="hidden"
			name="po_image"
			class="po-image"
			value="<?php echo esc_url( $popup->image ); ?>" />

		<div class="featured-img <?php if ( $has_image ) : ?>has-image<?php endif; ?>">
			<img src="<?php echo esc_url( $popup->image ); ?>"
				class="img-preview"
				<?php if ( ! $has_image ) : ?>
				style="display: none;"
				<?php endif; ?> />

			<span class="lbl-empty"
				<?php if ( $has_image ) : ?>
				style="display: none;"
				<?php endif; ?> >
				<?php _e( '(No image selected)', 'popover' ); ?>
			</span>
			<div class="drop-marker" style="display:none">
				<div class="drop-marker-content" title="<?php _e( 'Drop here', 'popover' ); ?>">
				</div>
			</div>

			<a href="#remove-image" class="reset">
				<i class="dashicons dashicons-dismiss"></i>
				<?php _e( 'Remove image', 'popover' ); ?>
			</a>
		</div>

		<div class="img-pos"
			<?php if ( ! $has_image ) : ?>
			style="display: none;"
			<?php endif; ?> >

			<div>
				<label>
					<input type="checkbox"
						name="po_image_no_mobile"
						<?php checked( $popup->image_mobile, false ); ?>>
					<?php _e( 'Hide image for mobile devices', 'popover' ); ?>
				</label>
			</div>

			<div>
				<label class="option <?php if ( 'left' == $popup->image_pos ) : ?>selected<?php endif; ?>">
					<input type="radio" name="po_image_pos" value="left" <?php checked( 'left' == $popup->image_pos ); ?> />
					<span class="image left">
						<i class="dashicons dashicons-format-image"></i>
					</span>
					<i class="dashicons dashicons-editor-alignleft"></i>
				</label>

				<label class="option <?php if ( 'left' != $popup->image_pos ) : ?>selected<?php endif; ?>">
					<input type="radio" name="po_image_pos" value="right" <?php checked( 'left' != $popup->image_pos ); ?> />
					<i class="dashicons dashicons-editor-alignleft"></i>
					<span class="image right">
						<i class="dashicons dashicons-format-image"></i>
					</span>
				</label>
			</div>
		</div>
	</div>
</div>