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
			<strong><?php _e( 'Show this Pop Up if the following conditions are met', PO_LANG ); ?></strong>
		</div>
	</div>
	<div class="wpmui-grid-12">
		<div class="col-all-rules">
			<div class="scroller all-rules-box">
				<ul class="all-rules">
					<?php do_action( 'popup-all-rules', $popup ); ?>
				</ul>
			</div>
		</div>
		<div class="col-active-rules">
			<input type="hidden"
				name="po_rule_order"
				id="po-rule-order"
				value="<?php echo esc_attr( implode( ',', $popup->rule ) ); ?>" />

			<div class="scroller active-rules-box">
				<ul class="active-rules">
				<?php
				// Show the forms of inactive rules.
				do_action( 'popup-rule-forms', $popup, false );

				// Then show the forms of active rules in the correct order.
				foreach ( $popup->rule as $ind => $key ) {
					if ( empty ( $key ) ) { continue; }
					do_action( 'popup-rule-forms', $popup, $key );
				}
				?>
				</ul>
			</div>
		</div>
	</div>
</div>
