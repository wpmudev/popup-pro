<?php
/**
 * Metabox "Appearance"
 *
 * Used in class-popup-admin.php
 * Available variables: $popup
 */

?>
<div class="wpmui-grid-12">
	<div class="col-12">
		<label><strong><?php _e( 'Select which style you want to use:', PO_LANG ); ?></strong></label>
	</div>
</div>
<div class="wpmui-grid-12">
	<div class="col-7">
		<select class="block">
			<option>Cabriolet</option>
		</select>
	</div>
	<div class="col-5">
		<label>
			<input type="checkbox" />
			<?php _e( 'No rounded corners', PO_LANG ); ?>
		</label>
	</div>
</div>

<div class="wpmui-grid-12">
	<div class="col-12">
		<label>
			<input type="checkbox" />
			<?php _e( 'Use custom colors', PO_LANG ); ?>
		</label>
	</div>
</div>
<div class="wpmui-grid-12">
	<div class="col-5">
		<input>
	</div>
	<div class="col-5">
		<input>
	</div>
</div>
<div class="wpmui-grid-12">
	<div class="col-5">
		<?php _e( 'Links, button background, heading and subheading', PO_LANG ); ?>
	</div>
	<div class="col-5">
		<?php _e( 'Button text', PO_LANG ); ?>
	</div>
</div>

<div class="wpmui-grid-12">
	<div class="col-12">
		<label>
			<input type="checkbox" />
			<?php _e( 'Use custom size (if selected the Pop Up won\'t be responsive)', PO_LANG ); ?>
		</label>
	</div>

</div>
<div class="wpmui-grid-12">
	<div class="col-5">
		<label><?php _e( 'Width:', PO_LANG ); ?></label>
		<input placeholder="600px" />
	</div>
	<div class="col-5">
		<label><?php _e( 'Height:', PO_LANG ); ?></label>
		<input placeholder="300px" />
	</div>
</div>