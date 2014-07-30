<?php
/**
 * Metabox "Conditions" (rules)
 *
 * Used in class-popup-admin.php
 * Available variables: $popup
 */

?>
<div class="wpmui-grid-12">
	<div class="col-4">
		<strong><?php _e( 'Available Conditions', PO_LANG ); ?></strong>
	</div>
	<div class="col-8">
		<strong><?php _e( 'Show this Pop Up if the following conditions are met', PO_LANG ); ?></strong>
	</div>
</div>
<div class="wpmui-grid-12">
	<div class="col-4">
		<div class="scroller all-rules-box">
			<ul class="all-rules">
				<?php do_action( 'popup-all-rules', $popup ); ?>
			</ul>
		</div>
	</div>
	<div class="col-8">
		<div class="scroller active-rules-box">
			<ul class="active-rules">
				<?php foreach ( $popup->checks as $ind => $key ) {
					do_action( 'popup-active-rule', $popup, $key, $ind );
				} ?>
			</ul>
		</div>
	</div>
</div>

