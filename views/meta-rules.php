<?php
/**
 * Metabox "Conditions" (rules)
 *
 * Used in class-popup-admin.php
 * Available variables: $popup
 */

?>
<div class="wpmui-loading init-loading">
	<div class="wpmui-grid-12">
		<div class="col-all-rules">
			<strong><?php _e( 'Available Conditions', PO_LANG ); ?></strong>
		</div>
		<div class="col-active-rules">
			<strong><?php _e( 'Show this PopUp if all of the following conditions are met', PO_LANG ); ?></strong>
		</div>
	</div>
	<div class="wpmui-grid-12">
		<div class="col-all-rules">
			<div class="scroller all-rules-box">
				<ul class="all-rules">
					<?php do_action( 'popup-rule-switch', $popup ); ?>
				</ul>
			</div>
		</div>
		<div class="col-active-rules">
			<div class="scroller active-rules-box">
				<ul class="active-rules">
				<?php do_action( 'popup-rule-forms', $popup ); ?>
				</ul>
			</div>
		</div>
	</div>
</div>
