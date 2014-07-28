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
		<strong><?php _e( 'When to show the Pop Up:', PO_LANG ); ?></strong>
	</div>
	<div class="col-12">
		<label>
			<input type="radio" />
			<?php _e( 'Appear after', PO_LANG ); ?>
		</label>
		<input type="number" placeholder="10" />
		<select>
			<option><?php _e( 'Seconds', PO_LANG ); ?></option>
			<option><?php _e( 'Minutes', PO_LANG ); ?></option>
		</select>
	</div>
	<div class="col-12">
		<label>
			<input type="radio" />
			<?php _e( 'Appear after', PO_LANG ); ?>
		</label>
		<input type="number" placeholder="25" />
		<?php _e( '% of the page has been scrolled.', PO_LANG ); ?>
	</div>
	<div class="col-12">
		<label>
			<input type="radio" />
			<?php _e( 'Appear after user scrolled past a CSS selector', PO_LANG ); ?>
		</label>
		<input type="text" placeholder="<?php _e( '.class or #id', PO_LANG ); ?>" />
	</div>
</div>

<hr />

<div class="wpmui-grid-12">
	<div class="col-12">
		<strong><?php _e( '"Never see this message again" settings:', PO_LANG ); ?></strong>
	</div>
	<div class="col-12">
		<label>
			<input type="checkbox" />
			<?php _e( 'Add "Never see this message again" link', PO_LANG ); ?>
		</label>
	</div>
	<div class="col-12">
		<label>
			<input type="checkbox" />
			<?php _e( 'Close button acts as "Never see this message again" link', PO_LANG ); ?>
		</label>
	</div>
	<div class="col-12">
		<label>
			<?php
			printf(
				__( 'Expiry time %1$s days (upon expiry, user will see this Pop Up again)', PO_LANG ),
				'<input type="number" placeholder="365" />'
			);
			?>
		</label>
	</div>
</div>

<hr />

<div class="wpmui-grid-12">
	<div class="col-12">
		<strong><?php _e( 'Closing Pop-up conditions', PO_LANG ); ?></strong>
	</div>
	<div class="col-12">
		<label>
			<input type="checkbox" />
			<?php _e( 'Prevent pop-up from closing when clicked outside active area', PO_LANG ); ?>
		</label>
	</div>

</div>