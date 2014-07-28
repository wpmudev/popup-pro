<?php
/**
 * Metabox "Pop Up Content"
 *
 * Used in class-popup-admin.php
 * Available variables: $popup
 */

?>
<div class="content-main">
	<div class="wpmui-grid-12">
		<div class="col-6">
			<label for="po-heading"><h3><?php _e( 'Heading (optional)', PO_LANG ); ?></h3></label>
		</div>
		<div class="col-6">
			<label for="po-subheading"><h3><?php _e( 'Subheading (optional)', PO_LANG ); ?></h3></label>
		</div>
	</div>
	<div class="wpmui-grid-12">
		<div class="col-6">
			<input class="block"
				id="po-heading"
				name="po_heading"
				placeholder="<?php _e( 'Enter your heading here...', PO_LANG ); ?>" />
		</div>
		<div class="col-6">
			<input class="block"
				id="po-subheading"
				name="po_subheading"
				placeholder="<?php _e( 'Enter your subheading here...', PO_LANG ); ?>" />
		</div>
	</div>

	<div class="wpmui-grid-12">
		<label for="po_content">
			<h3 class="main-content"><?php _e( 'Main Pop Up Content', PO_LANG ); ?></h3>
		</label>
	</div>
	<div>
		<?php
		$args = array(
			'textarea_rows' => 4,
			'drag_drop_upload' => true,
		);
		wp_editor( $popup->content, 'po_content', $args );
		?>
	</div>

	<div class="wpmui-grid-12">
		<label for="po-cta">
			<h3><?php _e( 'Call To Action Button (optional)', PO_LANG ); ?></h3>
		</label>
	</div>
	<div class="wpmui-grid-12">
		<div class="col-4">
			<input class="block"
				id="po-cta"
				name="po_cta"
				placeholder="<?php _e( 'Button Label', PO_LANG ); ?>" />
		</div>
		<div class="col-4">
			<input class="block"
				id="po-cta-link"
				name="po_cta_link"
				placeholder="<?php _e( 'Button Link (http://www.example.com)', PO_LANG ); ?>" />
		</div>
	</div>
</div>


<div class="content-image">
	<div class="wpmui-grid-12">
		<label>
			<h3><?php _e( 'Pop Up Feature Image (optional)', PO_LANG ); ?></h3>
		</label>
	</div>
	<div class="wpmui-grid-12">
		<button class="button add_image"
			type="button"
			title="<?php _e( 'Add featured image to Pop Up.', PO_LANG ); ?>">
			<i class="add-image-icon dashicons dashicons-format-image"></i>
			<?php _e( 'Add Image', PO_LANG ); ?>
		</button>
		<div class="dropzone">
			Drop image here
		</div>
	</div>
</div>