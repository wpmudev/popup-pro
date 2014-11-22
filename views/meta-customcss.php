<?php
/**
 * Metabox "Custom Styles"
 *
 * @since  4.7.0
 *
 * Used in class-popup-admin.php
 * Available variables: $popup
 */

?>
<div class="wpmui-grid-12">
	<label for="po-custom-css">
		<?php _e( 'Provide custom CSS rules to customize this PopUp', PO_LANG ); ?>
	</label>
</div>
<div class="wpmui-grid-12">
	<textarea name="po_custom_css" id="po-custom-css" style="display: none"><?php
	echo esc_textarea( $popup->custom_css );
	?></textarea>
	<div class="po_css_editor"
		id="po-css-editor"
		data-input="#po-custom-css"
		style="width:100%; height: 20em;"
	><?php
	echo esc_textarea( $popup->custom_css );
	?></div>
</div>
<div class="wpmui-grid-12">
	<?php _e( 'Note: To target this PopUp you need to prefix all rules with <code>#popup</code>, e.g. <code>#popup .wdpu-text { font-family: sans }</code>', PO_LANG ); ?>
</div>