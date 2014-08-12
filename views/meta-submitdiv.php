<?php
/**
 * Metabox "submitdiv"
 *
 * Used in class-popup-admin.php
 * Available variables: $popup
 */

$delete_url = get_delete_post_link( $post->ID );
$duplicate_url = add_query_arg( 'do', 'duplicate' );

?>
<div class="submitbox" id="submitpost">
	<?php /* Save/Deactivate/Preview */ ?>
	<div id="minor-publishing">
		<?php // Hidden submit button early on so that the browser chooses the right button when form is submitted with Return key ?>
		<div style="display:none;">
			<?php submit_button( __( 'Save', PO_LANG ), 'button', 'save', false ); ?>
		</div>

		<div id="minor-publishing-actions" class="non-sticky">
			<div class="status">
				<label for="po-status"><?php _e( 'Status:', PO_LANG ); ?></label>
				<div class="status-switch">
					<input type="checkbox"
						name="po_active"
						id="po-status"
						<?php checked( $popup->status, 'active' ); ?>/>
					<label class="status-box" for="po-status">
						<span class="indicator"></span>
						<span class="label-active"><strong><?php _e( 'Active', PO_LANG ); ?></strong></span>
						<span class="label-inactive"><?php _e( 'Inactive', PO_LANG ); ?></span>
					</label>
				</div>
			</div>

			<div class="preview-action">
				<button type="button" class="preview button">
				<i class="dashicons dashicons-visibility"></i>
				<?php _e( 'Preview Pop Up', PO_LANG ); ?>
				</button>
			</div>

			<div class="clear"></div>
		</div>
	</div>

	<?php /* *** Trash/Save/Activate *** */ ?>
	<div id="major-publishing-actions" class="non-sticky">
		<div class="delete-action">
		<?php if ( current_user_can( 'delete_post', $post->ID ) ) : ?>
			<a class="submitdelete deletion" href="<?php echo esc_url( $delete_url ); ?>">
			<?php _e( 'Move to Trash', PO_LANG ); ?>
			</a>
		<?php endif; ?>
		</div>

		<div class="publishing-action">
			<span class="spinner"></span>
			<?php if ( ! empty( $popup->id ) ) : ?>
				<a href="<?php echo esc_url( $duplicate_url ); ?>" class="do-duplicate">
					<?php _e( 'Duplicate', PO_LANG ); ?>
				</a>
			<?php endif; ?>
			<button class="button-primary" id="publish" name="po-action" value="save">
			<?php _e( 'Save', PO_LANG ); ?>
			</button>
		</div>

		<div class="clear"></div>
	</div>

	<?php /* *** Sticky form: Trash/Preview/Save/Activate *** */ ?>
	<div class="sticky-actions" style="display:none">
		<div class="delete-action">
		<?php if ( current_user_can( 'delete_post', $post->ID ) ) : ?>
			<a class="submitdelete deletion" href="<?php echo esc_url( $delete_url ); ?>">
			<?php _e( 'Move to Trash', PO_LANG ); ?>
			</a>
		<?php endif; ?>
		</div>

		<div class="publishing-action">
			<button class="button-primary" name="po-action" value="save">
			<?php _e( 'Save', PO_LANG ); ?>
			</button>
		</div>

		<div class="preview-action">
			<button type="button" class="preview button">
			<i class="dashicons dashicons-visibility"></i>
			<?php _e( 'Preview Pop Up', PO_LANG ); ?>
			</button>
		</div>

		<div class="duplicate-action">
			<span class="spinner"></span>
			<?php if ( ! empty( $popup->id ) ) : ?>
				<a href="<?php echo esc_url( $duplicate_url ); ?>" class="do-duplicate">
					<?php _e( 'Duplicate', PO_LANG ); ?>
				</a>
			<?php endif; ?>
		</div>

		<div class="clear"></div>
	</div>
</div>