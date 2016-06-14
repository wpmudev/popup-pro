<?php
/**
 * Metabox "submitdiv"
 *
 * Used in class-popup-admin.php
 * Available variables: $popup
 */

$delete_url = get_delete_post_link( $post->ID );
$duplicate_url = esc_url_raw( add_query_arg( 'do', 'duplicate' ) );


$warn = ( 0 != IncPopupDatabase::count_active( $post->ID ) );


?>
<div class="submitbox" id="submitpost">
	<?php /* Save/Deactivate/Preview */ ?>
	<div id="minor-publishing">
		<?php // Hidden submit button early on so that the browser chooses the right button when form is submitted with Return key ?>
		<div style="display:none;">
			<?php submit_button( __( 'Save', 'popover' ), 'button', 'save', false ); ?>
		</div>

		<div id="minor-publishing-actions" class="non-sticky">
			<div class="status"
				<?php  if ( $warn ) : ?>
				data-tooltip="<?php _e( 'In the free version you can activate 1 PopUp. When you activate this PopUp then all other PopUps will be deactivated ', 'popover' ); ?>"
				data-class="status-hint"
				data-pos="left"
				data-width="250"
				<?php endif;  ?>
				>
				<div class="status-switch">
					<input type="checkbox"
						name="po_active"
						id="po-status"
						<?php checked( $popup->status, 'active' ); ?>/>
					<label class="status-box" for="po-status">
						<span class="indicator"></span>
						<span class="label-active"><?php _e( 'Status: <strong>Active</strong>', 'popover' ); ?></span>
						<span class="label-inactive"><?php _e( 'Status: Inactive', 'popover' ); ?></span>
					</label>
				</div>
			</div>

			<div class="preview-action">
				<button type="button" class="preview button">
				<i class="dashicons dashicons-visibility"></i>
				<?php _e( 'Preview PopUp', 'popover' ); ?>
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
			<?php _e( 'Move to Trash', 'popover' ); ?>
			</a>
		<?php endif; ?>
		</div>

		<div class="publishing-action">
			<span class="spinner"></span>
			<?php if ( ! empty( $popup->id ) ) : ?>
				<a href="<?php echo esc_url( $duplicate_url ); ?>" class="do-duplicate">
					<?php _e( 'Duplicate', 'popover' ); ?>
				</a>
			<?php endif; ?>
			<input type="hidden" name="po-action" value="save" />
			<button class="button-primary" id="publish" name="publish">
			<?php _e( 'Save', 'popover' ); ?>
			</button>
		</div>

		<div class="clear"></div>
	</div>

	<?php /* *** Sticky form: Trash/Preview/Save/Activate *** */ ?>
	<div class="sticky-actions" style="display:none">
		<div class="delete-action">
		<?php if ( current_user_can( 'delete_post', $post->ID ) ) : ?>
			<a class="submitdelete deletion" href="<?php echo esc_url( $delete_url ); ?>">
			<?php _e( 'Move to Trash', 'popover' ); ?>
			</a>
		<?php endif; ?>
		</div>

		<div class="publishing-action">
			<input type="hidden" name="po-action" value="save" />
			<button class="button-primary" id="publish" name="publish">
			<?php _e( 'Save', 'popover' ); ?>
			</button>
		</div>

		<div class="preview-action">
			<button type="button" class="preview button">
			<i class="dashicons dashicons-visibility"></i>
			<?php _e( 'Preview PopUp', 'popover' ); ?>
			</button>
		</div>

		<div class="duplicate-action">
			<span class="spinner"></span>
			<?php if ( ! empty( $popup->id ) ) : ?>
				<a href="<?php echo esc_url( $duplicate_url ); ?>" class="do-duplicate">
					<?php _e( 'Duplicate', 'popover' ); ?>
				</a>
			<?php endif; ?>
		</div>

		<div class="clear"></div>
	</div>
</div>
